<?php

namespace App\Data\Hr\Custody\Request\Return;

use App\Enums\ElectronicServices\Custody\Requests\CustodyRequestType;

class CustodyReturnData
{
    public int      $employee_id;
    public int      $parent_request_id;
    public ?string  $return_status;
    public ?string  $notes;

    public function __construct(array $data)
    {
        $this->employee_id          = (int)   $data['employee_id'];
        $this->parent_request_id    = (int)   $data['parent_request_id'];
        $this->return_status        =         $data['return_status'] ?? null;
        $this->notes                =         $data['notes'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'employee_id'           => $this->employee_id,
            'parent_request_id'     => $this->parent_request_id,
            'return_status'         => $this->return_status,
            'notes'                 => $this->notes,
        ];
    }
}
