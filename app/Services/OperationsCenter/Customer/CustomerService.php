<?php

namespace App\Services\OperationsCenter\Customer;


use App\Contracts\ErrorHandlerInterface;
use App\Data\OperationsCenter\Customer\CustomerData;
use App\Enums\OperationsCenter\Customer\CustomerType;
use App\Enums\Survey\SurveyType;
use App\Models\OperationsCenter\Customer\Customers;
use App\Services\SMS\SurveySmsService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CustomerService
{
    public function __construct(private ErrorHandlerInterface $errorHandler, private SurveySmsService $surveySmsService) {}

    public function createCustomer(CustomerData $dto)
    {
        $this->errorHandler->execute(function () use ($dto) {
            return DB::transaction(function () use ($dto) {
                $customer = new Customers([
                    'customer_type'                  => $dto->customerType,
                    'name'                           => $dto->name,
                    'title'                          => $dto->title,
                    'commercial_registration_number' => $dto->commercialRegistrationNumber,
                    'nationality_id'                 => $dto->nationalityId,
                    'status_id'                      => $dto->statusId,
                    'unified_number'                 => $dto->unifiedNumber,
                    'contact_number'                 => $dto->contact_number,
                    'email'                          => $dto->email,
                    'address'                        => $dto->address,
                    'civil_registry_number'          => $dto->civilRegistryNumber,
                    'relationship_manager_id'        => $dto->relationshipManagerId,
                    'marketing_channel_id'           => $dto->marketingChannelId,
                    'detailed_marketing_channel_id'  => $dto->detailedMarketingChannelId,
                    'parent_customer_id'             => $dto->parentCustomerId,
                    'social_media_id'                => $dto->socialMediaId,
                    'sector_id'                      => $dto->sectorId,
                    'created_by'                     => Auth::user()->id,

                    'department_id'                    => $dto->department_id,

                ]);

                $customer->save();

                // إضافة المفوضين إذا كان نوع العميل مؤسسة
                if ($dto->customerType === CustomerType::Company && !empty($dto->authorizations)) {
                    foreach ($dto->authorizations as $authorization) {
                        $customer->authorizations()->create($authorization);
                    }
                }

                return $customer;
            });
        }, 'حدث خطأ أثناء حفظ العميل');
    }

    public function updateCustomer(int $id, CustomerData $dto): void
    {
        $this->errorHandler->execute(function () use ($id, $dto) {
            return DB::transaction(function () use ($id, $dto) {
                $customer = Customers::findOrFail($id);

                // معالجة تغيير نوع العميل
                $this->handleCustomerTypeChange($customer, $dto);

                $customer->update([
                    'customer_type'                   => $dto->customerType,
                    'name'                           => $dto->name,
                    'title'                          => $dto->title,
                    'commercial_registration_number' => $dto->commercialRegistrationNumber,
                    'nationality_id'                 => $dto->nationalityId,
                    'status_id'                      => $dto->statusId,
                    'unified_number'                 => $dto->unifiedNumber,
                    'contact_number'                 => $dto->contact_number,
                    'email'                          => $dto->email,
                    'address'                        => $dto->address,
                    'civil_registry_number'          => $dto->civilRegistryNumber,
                    'relationship_manager_id'        => $dto->relationshipManagerId,
                    'marketing_channel_id'           => $dto->marketingChannelId,
                    'detailed_marketing_channel_id'  => $dto->detailedMarketingChannelId,
                    'parent_customer_id'             => $dto->parentCustomerId,
                    'social_media_id'                => $dto->socialMediaId,
                    'sector_id'                      => $dto->sectorId,
                    'updated_by'                     => Auth::user()->id,

                    'department_id'                    => $dto->department_id,


                ]);

                // إدارة المفوضين للمؤسسات
                $this->handleAuthorizations($customer, $dto);

                // if ($customer->status->name == "متعاقد") {
                //     $this->surveySmsService->sendSurveyByType(SurveyType::Contract, [$customer->id]);
                // }

                return $customer;
            });
        }, 'حدث خطأ أثناء تحديث بيانات العميل');
    }

    /**
     * حذف العميل
     */
    public function deleteCustomer(int $id): bool
    {
        return $this->errorHandler->execute(function () use ($id) {
            return DB::transaction(function () use ($id) {
                $customer = Customers::findOrFail($id);

                // التحقق من إمكانية الحذف
                $this->validateCustomerDeletion($customer);

                return $customer->delete();
            });
        }, 'حدث خطأ أثناء حذف العميل');
    }


    /**
     * إرسال رسائل SMS للعملاء
     */
    public function sendSmsToCustomers(array $customerIds, string $message): array
    {
        return $this->errorHandler->execute(function () use ($customerIds, $message) {
            $customers = Customers::whereIn('id', $customerIds)->get();
            $failedNumbers = [];
            $successfulRecipients = [];

            foreach ($customers as $customer) {
                if ($customer->contact_number) {
                    $formattedNumber = ltrim($customer->contact_number, '0');

                    // هنا يجب استدعاء دالة الإرسال الفعلية
                    $sent = $this->sendSMS($message, $formattedNumber);

                    if ($sent) {
                        $successfulRecipients[] = [
                            'type' => 'customer',
                            'id' => $customer->id,
                        ];
                    } else {
                        $failedNumbers[] = $formattedNumber;
                    }
                }
            }

            // تسجيل الرسالة في جدول message_logs
            $this->logMessage($message, $successfulRecipients);

            return [
                'successful' => $successfulRecipients,
                'failed' => $failedNumbers
            ];
        }, 'حدث خطأ أثناء إرسال الرسائل');
    }

    /*
    |============================================================================
    |                          Private methods
    |============================================================================
    */

    /**
     * معالجة تغيير نوع العميل
     */
    private function handleCustomerTypeChange(Customers $customer, CustomerData $dto): void
    {
        // إذا تم التغيير من مؤسسة إلى فرد
        if ($customer->customer_type === CustomerType::Company && $dto->customerType === CustomerType::Individual) {
            $customer->commercial_registration_number = null;
            $customer->unified_number = null;
            $customer->authorizations()->delete();
        }

        // إذا تم التغيير من فرد إلى مؤسسة
        if ($customer->customer_type === CustomerType::Individual && $dto->customerType === CustomerType::Company) {
            $customer->title = null;
            $customer->civil_registry_number = null;
        }
    }

    /**
     * إدارة المفوضين
     */
    private function handleAuthorizations(Customers $customer, CustomerData $dto): void
    {
        if ($dto->customerType === CustomerType::Company) {
            // حذف المفوضين الحاليين
            $customer->authorizations()->delete();

            // إضافة المفوضين الجدد
            if (!empty($dto->authorizations)) {
                foreach ($dto->authorizations as $authorization) {
                    $customer->authorizations()->create($authorization);
                }
            }
        } else {
            // إذا تم تغيير النوع إلى فرد، احذف جميع المفوضين
            $customer->authorizations()->delete();
        }
    }

    /**
     * التحقق من إمكانية حذف العميل
     */
    private function validateCustomerDeletion(Customers $customer): void
    {
        $hasContracts = $customer->contracts()->exists();
        $hasOffers = $customer->offers()->exists();
        $lawsuitsAsPlaintiff = $customer->lawsuitsAsPlaintiff()->exists();
        $lawsuitsAsDefendant = $customer->lawsuitsAsDefendant()->exists();

        if ($hasContracts || $hasOffers || $lawsuitsAsPlaintiff || $lawsuitsAsDefendant) {
            throw ValidationException::withMessages([
                'customer' => 'لا يمكن حذف العميل لارتباطه بسجلات أخرى.'
            ]);
        }
    }
}
