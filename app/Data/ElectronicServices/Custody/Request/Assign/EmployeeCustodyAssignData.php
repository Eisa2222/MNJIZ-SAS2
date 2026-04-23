<?php

namespace App\Data\ElectronicServices\Custody\Request\Assign;


class EmployeeCustodyAssignData
{
    public int                      $custody_item_id;
    public ?string                  $notes;

    public function __construct(array $data)
    {
        $this->custody_item_id  = (int)   $data['custody_item_id'];
        $this->notes            = $data['notes'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'custody_item_id'   => $this->custody_item_id,
            'notes'             => $this->notes,
        ];
    }
}
