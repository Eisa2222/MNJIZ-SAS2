<?php

namespace App\Data\OperationsCenter\Contract;

use App\Data\OperationsCenter\Contract\Payment\ContractPaymentData;
use Illuminate\Http\UploadedFile;

class ContractData
{
    public string      $contractName;
    public string      $contractType;
    public ?int        $mainContractId;
    public ?int        $offerId;
    public ?int        $contractManagerId;
    public ?int        $contractStatusId;
    public string      $contractStartDate;
    public ?string     $contractEndDate;
    public string      $expectedClosureDate;
    public ?string     $technicalOffer;
    public ?string     $financialOffer;
    public bool        $isPrivateAndSecret;
    public array       $additionalAttachments;

    public array       $deleteAttachments;
    public array       $existingAttachmentNames;

    public ?string     $supplementPreamble;
    public ?string     $supplementDeclaration;



    /** @var ContractPaymentData[] */
    public array                      $payments;

    /**
     * @param  array<string,mixed>  $data
     */
    public function __construct(array $data)
    {
        $this->contractName           = $data['contract_name'];
        $this->contractType           = $data['contract_type'];
        $this->mainContractId         = isset($data['main_contract_id']) ? (int)$data['main_contract_id'] : null;
        $this->offerId                = isset($data['offer_id']) ? (int)$data['offer_id'] : null;
        $this->contractManagerId      = isset($data['contract_manager_id']) ? (int)$data['contract_manager_id'] : null;
        $this->contractStatusId       = isset($data['contract_status_id']) ? (int)$data['contract_status_id'] : null;
        $this->contractStartDate      = $data['contract_start_date'];
        $this->contractEndDate        = $data['contract_end_date'] ?? null;
        $this->expectedClosureDate    = $data['expected_closure_date'];
        $this->technicalOffer         = $data['technical_offer'] ?? null;
        $this->financialOffer         = $data['financial_offer'] ?? null;
        $this->isPrivateAndSecret     = ! empty($data['is_private_and_secret']);
        $this->additionalAttachments  = $data['additional_attachments'] ?? [];

        $this->deleteAttachments       = $data['delete_attachments'] ?? [];
        $this->existingAttachmentNames = $data['existing_attachment_names'] ?? [];

        $this->supplementPreamble         = $data['supplement_preamble'] ?? null;
        $this->supplementDeclaration      = $data['supplement_terms'] ?? null;



        $this->payments = [];
        foreach ($data['payments'] ?? [] as $payment) {
            $this->payments[] = new ContractPaymentData($payment);
        }
    }

    /**
     * Return as array suitable for mass assignment.
     */
    public function toArray(): array
    {
        return [
            'contract_name'            => $this->contractName,
            'contract_type'            => $this->contractType,
            'main_contract_id'         => $this->mainContractId,
            'offer_id'                 => $this->offerId,
            'contract_manager_id'      => $this->contractManagerId,
            'contract_status_id'       => $this->contractStatusId,
            'contract_start_date'      => $this->contractStartDate,
            'contract_end_date'        => $this->contractEndDate,
            'expected_closure_date'    => $this->expectedClosureDate,
            'technical_offer'          => $this->technicalOffer,
            'financial_offer'          => $this->financialOffer,
            'is_private_and_secret'    => $this->isPrivateAndSecret,
            'additional_attachments'   => $this->additionalAttachments,

            'deleteAttachments'         => $this->deleteAttachments,
            'existingAttachmentNames'   => $this->existingAttachmentNames,

            'payments'                  => array_map(
                fn(ContractPaymentData $p) => $p->toArray(),
                $this->payments
            ),

            'supplement_preamble'      => $this->supplementPreamble,
            'supplement_terms'   => $this->supplementDeclaration,

        ];
    }
}
