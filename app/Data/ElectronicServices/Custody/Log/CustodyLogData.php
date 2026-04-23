<?php

namespace App\Data\ElectronicServices\Custody\Log;

use App\Enums\ElectronicServices\Custody\Log\CustodyLogAction;

class CustodyLogData
{
    public int                  $custody_request_id;
    public \DateTime            $log_date;
    public CustodyLogAction     $action;


    public function __construct(array $data)
    {
        $this->custody_request_id   = (int)   $data['custody_request_id'];
        $this->log_date             = new \DateTime($data['log_date']);
        $this->action               = $data['action'];
    }

    public function toArray(): array
    {
        return [
            'custody_request_id'    => $this->custody_request_id,
            'log_date'              => $this->log_date->format('Y-m-d H:i:s'),
            'action'                => $this->action,
        ];
    }
}
