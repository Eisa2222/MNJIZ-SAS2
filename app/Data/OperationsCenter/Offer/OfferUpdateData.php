<?php

namespace App\Data\OperationsCenter\Offer;

class OfferUpdateData
{
    public string   $offerName;
    public int      $customerId;
    public string   $startDate;
    public ?string  $technicalOffer;
    public ?string  $financialOffer;
    public bool     $isPrivateAndSecret;
    public bool     $resetApprovals;
    public bool     $fromShow;

    /**
     * @param  array<string,mixed>  
     */
    public function __construct(array $data)
    {
        $this->offerName             = $data['offer_name'];
        $this->customerId            = (int) $data['customer_id'];
        $this->startDate             = $data['start_date'];
        $this->technicalOffer        = $data['technical_offer'] ?? null;
        $this->financialOffer        = $data['financial_offer'] ?? null;
        $this->isPrivateAndSecret    = ! empty($data['is_private_and_secret']);
    }


    /**
     * Convert the data to an array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'offer_name'               => $this->offerName,
            'customer_id'              => $this->customerId,
            'start_date'               => $this->startDate,
            'technical_offer'          => $this->technicalOffer,
            'financial_offer'          => $this->financialOffer,
            'is_private_and_secret'    => $this->isPrivateAndSecret,
        ];
    }
}
