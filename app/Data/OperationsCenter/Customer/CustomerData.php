<?php

namespace App\Data\OperationsCenter\Customer;

use App\Enums\OperationsCenter\Customer\CustomerType;
use Beta\Microsoft\Graph\Model\Customer;
use Illuminate\Support\Arr;

class CustomerData
{
    public CustomerType  $customerType;
    public string  $name;
    public ?string $title;
    public ?string $commercialRegistrationNumber;
    public ?int    $nationalityId;
    public ?int    $statusId;
    public ?string $unifiedNumber;
    public ?string $contact_number;
    public ?string $email;
    public ?int    $address;
    public ?string $civilRegistryNumber;
    public ?int    $relationshipManagerId;
    public ?int    $marketingChannelId;
    public ?int    $detailedMarketingChannelId;
    public ?int    $parentCustomerId;
    public ?int    $socialMediaId;
    public ?int    $sectorId;
    public array   $authorizations;

    public ?int    $department_id;



    public function __construct(array $data)
    {
        $this->customerType                   = CustomerType::tryFrom(Arr::get($data, 'customer_type'));

        $this->name                          = $data['name'];
        $this->title                         = $data['title'] ?? null;
        $this->commercialRegistrationNumber  = $data['commercial_registration_number'] ?? null;
        $this->nationalityId                 = isset($data['nationality_id']) ? (int) $data['nationality_id'] : null;
        $this->statusId                      = isset($data['status_id']) ? (int) $data['status_id'] : null;
        $this->unifiedNumber                 = $data['unified_number'] ?? null;
        $this->contact_number                 = $data['contact_number'] ?? null;
        $this->email                         = $data['email'] ?? null;
        $this->address                       = isset($data['address']) ? (int) $data['address'] : null;
        $this->civilRegistryNumber           = $data['civil_registry_number'] ?? null;
        $this->relationshipManagerId         = isset($data['relationship_manager_id']) ? (int) $data['relationship_manager_id'] : null;
        $this->marketingChannelId            = isset($data['marketing_channel_id']) ? (int) $data['marketing_channel_id'] : null;
        $this->detailedMarketingChannelId    = isset($data['detailed_marketing_channel_id']) ? (int) $data['detailed_marketing_channel_id'] : null;
        $this->parentCustomerId              = isset($data['parent_customer_id']) ? (int) $data['parent_customer_id'] : null;
        $this->socialMediaId                 = isset($data['social_media_id']) ? (int) $data['social_media_id'] : null;
        $this->sectorId                      = isset($data['sector_id']) ? (int) $data['sector_id'] : null;
        $this->authorizations                = $data['authorizations'] ?? [];

        $this->department_id                      = isset($data['department_id']) ? (int) $data['department_id'] : null;
    }


    public function toArray(): array
    {
        return [
            'customer_type'                  => $this->customerType,
            'name'                           => $this->name,
            'title'                          => $this->title,
            'commercial_registration_number' => $this->commercialRegistrationNumber,
            'nationality_id'                 => $this->nationalityId,
            'status_id'                      => $this->statusId,
            'unified_number'                 => $this->unifiedNumber,
            'contact_number'                 => $this->contact_number,
            'email'                          => $this->email,
            'address'                        => $this->address,
            'civil_registry_number'          => $this->civilRegistryNumber,
            'relationship_manager_id'        => $this->relationshipManagerId,
            'marketing_channel_id'           => $this->marketingChannelId,
            'detailed_marketing_channel_id'  => $this->detailedMarketingChannelId,
            'parent_customer_id'             => $this->parentCustomerId,
            'social_media_id'                => $this->socialMediaId,
            'sector_id'                      => $this->sectorId,
            'authorizations'                 => $this->authorizations,

            'department_id'                   => $this->department_id,

        ];
    }
}
