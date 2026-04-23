<?php

namespace App\Data\Hr\Custody\Item;

use App\Enums\Hr\Custody\Item\CustodyItemStatus;

class CustodyItemData
{
    public string $name;
    public array $serial_numbers; // تغيير إلى مصفوفة
    public ?float $price;
    public int $asset_category_id;
    public int $storage_location_id;
    public ?string $description;


    public function __construct(array $data)
    {
        $this->name                 = (string) $data['name'];
        $this->serial_numbers       = $this->sanitizeSerialNumbers($data['serial_numbers'] ?? []);
        $this->price                = isset($data['price']) ? (float) $data['price'] : null;
        $this->asset_category_id    = (int) $data['asset_category_id'];
        $this->storage_location_id  = (int) $data['storage_location_id'];
        $this->description          = $data['description'] ?? null;
    }

    private function sanitizeSerialNumbers(array $serialNumbers): array
    {
        // تنظيف الأرقام التسلسلية وإزالة الفارغة
        return array_filter(
            array_map('trim', $serialNumbers),
            fn($serial) => !empty($serial)
        );
    }

    public function toArray(): array
    {
        return [
            'name'                  => $this->name,
            'serial_numbers'        => $this->serial_numbers,
            'price'                 => $this->price,
            'asset_category_id'     => $this->asset_category_id,
            'storage_location_id'   => $this->storage_location_id,
            'description'           => $this->description,
        ];
    }

    // دالة مساعدة للحصول على عدد الأصول التي سيتم إنشاؤها
    public function getItemsCount(): int
    {
        return count($this->serial_numbers);
    }

    // دالة مساعدة للحصول على بيانات أصل واحد بناءً على الفهرس
    public function getItemData(int $index): array
    {
        if (!isset($this->serial_numbers[$index])) {
            throw new \InvalidArgumentException("Serial number at index {$index} does not exist");
        }

        return [
            'name'                  => $this->name,
            'serial_number'         => $this->serial_numbers[$index],
            'price'                 => $this->price,
            'asset_category_id'     => $this->asset_category_id,
            'storage_location_id'   => $this->storage_location_id,
            'description'           => $this->description,
        ];
    }
}
