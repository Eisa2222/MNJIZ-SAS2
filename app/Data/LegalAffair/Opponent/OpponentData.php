<?php

namespace App\Data\LegalAffair\Opponent;

use App\Enums\LegalAffair\Opponent\OpponentType;
use Illuminate\Support\Arr;

class OpponentData
{
    public string  $name;
    public ?string $email;
    public ?string $contact_number;
    public ?string $bio;
    public ?int    $settingsRegionId;
    public OpponentType  $type;
    public ?string $commercialRegistration;
    public ?string $unifiedNumber;
    public ?string $identityNumber;
    public array   $authorizations;


    public function __construct(array $data)
    {
        $this->name                   = $data['name'];
        $this->email                  = $data['email'] ?? null;
        $this->contact_number         = $data['contact_number'] ?? null;
        $this->bio                    = $data['bio'] ?? null;
        $this->settingsRegionId       = isset($data['settings_region_id']) ? (int) $data['settings_region_id'] : null;
        $this->type                   = OpponentType::tryFrom(Arr::get($data, 'type'));
        $this->commercialRegistration = $data['commercial_registration'] ?? null;
        $this->unifiedNumber          = $data['unified_number'] ?? null;
        $this->identityNumber         = $data['identity_number'] ?? null;
        $this->authorizations         = $data['authorizations'] ?? [];
    }

    public function toArray(): array
    {
        return [
            'name'                    => $this->name,
            'email'                   => $this->email,
            'contact_number'          => $this->contact_number,
            'bio'                     => $this->bio,
            'settings_region_id'      => $this->settingsRegionId,
            'type'                    => $this->type,
            'commercial_registration' => $this->commercialRegistration,
            'unified_number'          => $this->unifiedNumber,
            'identity_number'         => $this->identityNumber,
            'authorizations'          => $this->authorizations,

        ];
    }
}
