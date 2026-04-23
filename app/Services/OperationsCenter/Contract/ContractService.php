<?php

namespace App\Services\OperationsCenter\Contract;

use App\Data\OperationsCenter\Contract\ContractData;
use App\Enums\OperationsCenter\Contract\ContractType;
use App\Enums\OperationsCenter\Contract\Payment\CalculationType;
use App\Enums\OperationsCenter\Contract\Payment\PaymentStatus;
use App\Jobs\Microsoft\Onedrive\UpdateUploadFileJob;
use App\Jobs\Microsoft\Onedrive\UploadFileJob;
use App\Models\OperationsCenter\Contract\Contract;
use App\Models\OperationsCenter\Contract\ContractAttachment;
use App\Models\OperationsCenter\Offer\Offers;
use App\Services\Microsoft\Onedrive\Onedrive;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ContractService
{
    public function __construct(private Onedrive $onedrive) {}


    public function createContract(ContractData $dto): Contract
    {
        return DB::transaction(function () use ($dto) {
            try {

                if ($dto->contractType === ContractType::Main->value) {
                    $this->validateOfferHasNoDeletedContract($dto->offerId);
                }

                $contractData = $this->prepareContractData($dto);

                $contract = Contract::create($contractData);

                $this->createPayments($contract, $dto->payments);

                $this->handleAttachmentsInBackground($contract, $dto->additionalAttachments ?? []);

                return $contract;
            } catch (\Exception $e) {
                throw $e;
            }
        });
    }


    public function updateContract(string $id, ContractData $dto): void
    {
        DB::transaction(function () use ($id, $dto) {
            try {
                $contract = Contract::findOrFail($id);
                $this->validateContractUpdate($contract, $dto);

                $contractData = $this->prepareContractData($dto, $contract);

                $contract->update($contractData);

                $this->syncPayments($contract, $dto->payments);

                $this->handleAttachmentUpdatesInBackground($contract, $dto);

                // Log::info('Contract updated successfully', ['contract_id' => $contract->id]);
            } catch (\Exception $e) {
                // Log::error('Failed to update contract', [
                //     'contract_id' => $id,
                //     'error' => $e->getMessage()
                // ]);
                throw $e;
            }
        });
    }

    /**
     * Delete a contract if it has no related records.
     *
     * @param string $id
     * @throws ValidationException
     */
    public function deleteContract(string $id): void
    {
        DB::transaction(function () use ($id) {
            $contract = Contract::findOrFail($id);
            $this->validateContractDeletion($contract);

            try {
                $contract->delete();
                // Log::info('Contract deleted successfully', ['contract_id' => $id]);
            } catch (\Exception $e) {
                // Log::error('Failed to delete contract', [
                //     'contract_id' => $id,
                //     'error' => $e->getMessage()
                // ]);
                throw $e;
            }
        });
    }


    /*
    |============================================================================
    |============================================================================
    |                        Private Helper Methods
    |============================================================================
    |============================================================================
    */
    // لتحديد هل هي متاخرة ام مجدولة
    private function statusByDate(string $dueDate)
    {
        return Carbon::parse($dueDate)->isPast()
            ? PaymentStatus::Late
            : PaymentStatus::Scheduled;
    }

    /**
     * Prepare contract data based on contract type.
     *
     * @param ContractData $dto
     * @param Contract|null $existingContract
     * @return array
     */
    private function prepareContractData(ContractData $dto, ?Contract $existingContract = null): array
    {
        $baseData   = $dto->toArray();
        $userId     = Auth::user()->employee->id ?? null;

        if ($existingContract) {
            $baseData['updated_by'] = $userId;
        } else {
            $baseData['created_by'] = $userId;
        }

        if ($dto->contractType === ContractType::Main->value) {
            return $this->prepareMainContractData($dto, $baseData);
        } else {
            return $this->prepareSupplementaryContractData($dto, $baseData, $existingContract);
        }
    }

    /**
     * Prepare main contract data.
     *
     * @param ContractData $dto
     * @param array $baseData
     * @return array
     */
    private function prepareMainContractData(ContractData $dto, array $baseData): array
    {
        $offer = Offers::findOrFail($dto->offerId);

        $offer->update([
            'technical_offer' => $dto->technicalOffer,
            'financial_offer' => $dto->financialOffer,
        ]);

        return $baseData + [
            'customer_id'                   => $offer->customer_id,
            'relationship_manager_id'       => $offer->relationship_manager_id,
            'contract_number'               => str_replace('Q', 'C', $offer->offer_number),
            'supplementary_technical_offer' => null,
            'supplementary_financial_offer' => null,

            'supplement_preamble'           => null,
            'supplement_terms'              => null,

        ];
    }

    /**
     * Prepare supplementary contract data.
     *
     * @param ContractData $dto
     * @param array $baseData
     * @param Contract|null $existingContract
     * @return array
     */
    private function prepareSupplementaryContractData(ContractData $dto, array $baseData, ?Contract $existingContract = null): array
    {
        $mainContract = Contract::findOrFail($dto->mainContractId);

        // إذا كان عقد موجود ولم يتغير العقد الرئيسي، احتفظ بالرقم الموجود
        if ($existingContract && $existingContract->main_contract_id == $dto->mainContractId) {
            $contractNumber = $existingContract->contract_number;
        } else {
            // إذا كان عقد جديد أو تغير العقد الرئيسي، أنشئ رقم جديد
            $contractNumber = $this->generateSupplementaryContractNumber($mainContract);
        }


        return $baseData + [
            'customer_id'                   => $mainContract->customer_id,
            'relationship_manager_id'       => $mainContract->relationship_manager_id,
            'contract_number'               => $contractNumber,
            'supplementary_technical_offer' => $dto->technicalOffer,
            'supplementary_financial_offer' => $dto->financialOffer,

            'supplement_preamble'           => $dto->supplementPreamble,
            'supplement_terms'        => $dto->supplementDeclaration,
        ];
    }

    /**
     * Generate supplementary contract number.
     *
     * @param Contract $mainContract
     * @return string
     */
    private function generateSupplementaryContractNumber(Contract $mainContract): string
    {
        $suffix = $mainContract->supplementaryContracts->count() + 1;
        return "{$mainContract->contract_number}-{$suffix}";
    }

    /**
     * Validate contract update constraints.
     *
     * @param Contract $contract
     * @param ContractData $dto
     * @throws ValidationException
     */
    private function validateContractUpdate(Contract $contract, ContractData $dto): void
    {
        if (
            $contract->contract_type === 'main' &&
            $dto->contractType === 'supplementary' &&
            $contract->supplementaryContracts()->exists()
        ) {
            throw ValidationException::withMessages([
                'contract' => ['لا يمكن تعديل العقد الرئيسي إلى عقد ملحق لأنه يحتوي على عقود فرعية.']
            ]);
        }
    }

    /**
     * Validate contract deletion constraints.
     *
     * @param Contract $contract
     * @throws ValidationException
     */
    private function validateContractDeletion(Contract $contract): void
    {
        $hasProject = $contract->projects()->exists();
        $hasSupplementaryContracts = $contract->supplementaryContracts()->exists();

        if ($hasProject || $hasSupplementaryContracts) {
            throw ValidationException::withMessages([
                'contract' => ['لا يمكن حذف العقد لارتباطه بسجلات أخرى.']
            ]);
        }
    }



    private function handleAttachmentsInBackground(Contract $contract, array $attachments): void
    {
        if (empty($attachments)) {
            return;
        }

        try {
            $attachmentRecords = [];

            foreach ($attachments as $attachment) {
                $file       = $attachment['file'];
                $ext        = $file->getClientOriginalExtension();
                $fileName   = "{$attachment['name']}.{$ext}";
                $path       = $file->store('uploads/attachments/contract', 'public');

                $contractAttachment = ContractAttachment::create([
                    'contract_id'   => $contract->id,
                    'name'          => $fileName,
                    'attachment'    => $path,
                ]);

                $attachmentRecords[] = [
                    'attachment_id' => $contractAttachment->id,
                    'file_path'     => storage_path("app/{$path}"),
                    'file_name'     => $fileName,
                ];
            }

            UploadFileJob::dispatch(
                Auth::id(),
                $contract->id,
                $attachmentRecords
            );
        } catch (\Exception $e) {
            Log::error('Failed to queue attachment processing', [
                'contract_id' => $contract->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }



    /**
     * Handle attachment updates in background.
     *
     * @param Contract $contract
     * @param ContractData $dto
     */
    private function handleAttachmentUpdatesInBackground(Contract $contract, ContractData $dto): void
    {

        try {
            $changedNames = [];
            foreach ($dto->existingAttachmentNames ?? [] as $attId => $new) {
                $old = ContractAttachment::find($attId)?->name;
                if ($old && $old !== $new && $new !== '') {
                    $changedNames[$attId] = $new;
                }
            }

            /* ❷ ابـنِ المصفوفة مرّة واحدة بكامل المفاتيح */
            $updateData = [
                'contract_id'               => $contract->id,
                'delete_attachments'        => $dto->deleteAttachments        ?? [],
                'existing_attachment_names' => $changedNames,                 // لا تنسَه!
                'new_attachments'           => [],
            ];

            // Process new attachments
            if ($dto->additionalAttachments) {
                $newAttachments = [];
                foreach ($dto->additionalAttachments as $name) {
                    $file           = $name['file'];
                    $fileName       = $name['name'] . '.' . $file->getClientOriginalExtension();
                    $storedFilePath = $file->store('uploads/attachments/contract', 'public');


                    // Create placeholder record
                    $attachment = ContractAttachment::create([
                        'contract_id'   => $contract->id,
                        'name'          => $fileName,
                        'attachment'    => $storedFilePath,

                    ]);

                    $newAttachments[] = [
                        'attachment_id' => $attachment->id,
                        'file_path'     => storage_path('app/' . $storedFilePath),
                        'file_name'     => $fileName,
                    ];
                }
                $updateData['new_attachments'] = $newAttachments;
            }

            // Dispatch background job to process all updates
            UpdateUploadFileJob::dispatch(
                Auth::id(),
                $updateData
            );
        } catch (\Exception $e) {
            Log::error('Failed to queue attachment updates', [
                'contract_id' => $contract->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }




    /**
     * Create payments for a contract.
     *
     * @param Contract $contract
     * @param array $paymentDtos
     */
    private function createPayments(Contract $contract, array $paymentDtos): void
    {
        foreach ($paymentDtos as $paymentDto) {
            $contract->contractPayments()->create([
                'calculation_type'      => $paymentDto->calculationType,
                'payment_batch_type'    => $paymentDto->paymentBatchType,
                'percentage'            => $paymentDto->percentage,
                'fixed_amount'          => $paymentDto->fixedAmount,
                'currency'              => 'SAR',
                'due_date'              => $paymentDto->dueDate,
                'status'                => $this->statusByDate($paymentDto->dueDate),
                'created_by'            => Auth::id(),
            ]);
        }
    }

    /**
     * Sync payments for a contract.
     *
     * @param Contract $contract
     * @param array $paymentDtos
     */
    private function syncPayments(Contract $contract, array $paymentDtos): void
    {
        if (empty($paymentDtos)) {
            $contract->scheduledPayments()->delete();
            return;
        }

        $seen   = [];
        $userId = Auth::id();

        DB::transaction(function () use ($contract, $paymentDtos, &$seen, $userId) {
            foreach ($paymentDtos as $dto) {
                $data       = $this->preparePaymentData($contract, $dto, $userId);
                $payment    = $this->createOrUpdatePayment($contract, $dto, $data, $userId);
                $seen[]     = $payment->id;
            }

            $contract->scheduledPayments()->whereNotIn('id', $seen)->delete();
        });
    }

    /**
     * Prepare payment data.
     *
     * @param Contract $contract
     * @param $dto
     * @param int $userId
     * @return array
     */
    private function preparePaymentData(Contract $contract, $dto, int $userId): array
    {
        $data = [
            'contract_id'           => $contract->id,
            'calculation_type'      => $dto->calculationType,
            'payment_batch_type'    => $dto->paymentBatchType,
            'due_date'              => $dto->dueDate,
            'status'                => $this->statusByDate($dto->dueDate),
        ];

        if ($dto->calculationType === CalculationType::Percentage->value) {
            $data['percentage']     = $dto->percentage;
            $data['fixed_amount']   = null;
        } else {
            $data['percentage']     = null;
            $data['fixed_amount']   = $dto->fixedAmount;
        }

        return $data;
    }

    /**
     * Create or update payment.
     *
     * @param Contract $contract
     * @param $dto
     * @param array $data
     * @param int $userId
     * @return mixed
     */
    private function createOrUpdatePayment(Contract $contract, $dto, array $data, int $userId)
    {
        if ($dto->id && $contract->scheduledPayments()->where('id', $dto->id)->exists()) {
            $data['updated_by'] = $userId;
            $payment = $contract->scheduledPayments()->where('id', $dto->id)->first();
            $payment->update($data);
            return $payment;
        } else {
            $data['created_by'] = $userId;
            return $contract->scheduledPayments()->create($data);
        }
    }


    private function validateOfferHasNoDeletedContract(int $offerId): void
    {
        $exists = Contract::withTrashed()
            ->where('offer_id', $offerId)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'offer_id' => [
                    'لا يمكن إنشاء عقد جديد لهذا العرض لأن هناك عقدًا سابقًا (محذوفًا أو قائمًا). يرجى مراجعة العقود.'
                ]
            ]);
        }
    }
}
