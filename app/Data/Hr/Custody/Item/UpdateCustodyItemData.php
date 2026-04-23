<?php

namespace App\Data\Hr\Custody\Item;

use App\Enums\Hr\Custody\Item\CustodyUseStatus;

class UpdateCustodyItemData
{

    public string $name;
    public ?string $serial_number;
    public ?float $price;
    public int $asset_category_id;
    public int $storage_location_id;
    public CustodyUseStatus $use_status;
    public ?string $description;



    public function __construct(array $data)
    {
        $this->name                 = (string) $data['name'];
        $this->serial_number        = $data['serial_number'] ?? null;
        $this->price                = isset($data['price']) ? (float) $data['price'] : null;
        $this->asset_category_id    = (int) $data['asset_category_id'];
        $this->storage_location_id  = (int) $data['storage_location_id'];
        $this->use_status               = CustodyUseStatus::from($data['use_status'] ?? 'available');
        $this->description          = $data['description'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'name'                  => $this->name,
            'serial_number'         => $this->serial_number,
            'price'                 => $this->price,
            'asset_category_id'     => $this->asset_category_id,
            'storage_location_id'   => $this->storage_location_id,
            'use_status'            => $this->use_status->value,
            'description'           => $this->description,
        ];
    }
}
