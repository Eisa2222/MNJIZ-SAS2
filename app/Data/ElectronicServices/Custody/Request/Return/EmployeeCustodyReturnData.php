<?php

namespace App\Data\ElectronicServices\Custody\Request\Return;

use App\Enums\ElectronicServices\Custody\Requests\CustodyReturnStatus;

class EmployeeCustodyReturnData
{
    public int                      $parent_request_id;
    public ?string                  $return_status;
    public ?string                  $notes;

    public function __construct(array $data)
    {
        $this->parent_request_id  = (int)   $data['parent_request_id'];
        $this->return_status      =         $data['return_status'] ?? null;
        $this->notes              =         $data['notes'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'parent_request_id'     => $this->parent_request_id,
            'return_status'         => $this->return_status,
            'notes'                 => $this->notes,
        ];
    }
}
