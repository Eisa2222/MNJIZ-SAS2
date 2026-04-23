<?php

namespace App\Data\Hr\Custody\Request\Assign;

class CustodyAssignData
{
    public int         $employee_id;
    public int         $custody_item_id;
    public ?string     $notes;


    public function __construct(array $data)
    {
        $this->employee_id      = (int)   $data['employee_id'];
        $this->custody_item_id  = (int)   $data['custody_item_id'];
        $this->notes            =         $data['notes'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'employee_id'       => $this->employee_id,
            'custody_item_id'   => $this->custody_item_id,
            'notes'             => $this->notes,

        ];
    }
}
