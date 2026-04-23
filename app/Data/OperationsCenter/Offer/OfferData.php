<?php

namespace App\Data\OperationsCenter\Offer;

class OfferData
{
    public string      $offerName;
    public int         $customerId;
    public string      $startDate;
    public ?string     $technicalOffer;
    public ?string     $financialOffer;
    public bool        $isPrivateAndSecret;

    /**
     * @param  array<string,mixed>  $data
     */
    public function __construct(array $data)
    {
        $this->offerName             = $data['offer_name'];
        $this->customerId            = (int) $data['customer_id'];
        $this->startDate             = $data['start_date'];
        $this->isPrivateAndSecret    = ! empty($data['is_private_and_secret']);
        $this->technicalOffer        = $data['technical_offer'] ?? null;
        $this->financialOffer        = $data['financial_offer'] ?? null;
    }

    /**
     * Return as array suitable for mass assignment.
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
