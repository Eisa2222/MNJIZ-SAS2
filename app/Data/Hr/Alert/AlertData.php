<?php

namespace App\Data\Hr\Alert;


class AlertData
{
    public int        $employee_id;
    public string     $type;

    public function __construct(array $data)
    {
        $this->employee_id   = (int)   $data['employee_id'];
        $this->type          =         $data['type'];
    }

    public function toArray(): array
    {
        return [
            'employee_id'    => $this->employee_id,
            'type'           => $this->type,
        ];
    }
}
