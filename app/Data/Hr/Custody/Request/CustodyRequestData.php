<?php

namespace App\Data\Hr\Custody\Request;


class CustodyRequestData
{
    public int         $custody_item_id;
    public \DateTime   $start_date;
    public ?\DateTime  $end_date;
    public ?string     $notes;

    public function __construct(array $data)
    {
        $this->custody_item_id  = (int)   $data['custody_item_id'];
        $this->start_date       = new \DateTime($data['start_date']);
        $this->end_date         = isset($data['end_date']) ? new \DateTime($data['end_date']) : null;
        $this->notes            =           $data['notes'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'custody_item_id' => $this->custody_item_id,
            'start_date'      => $this->start_date->format('Y-m-d H:i:s'),
            'end_date'        => $this->end_date?->format('Y-m-d H:i:s'),
            'notes'           => $this->notes,
        ];
    }
}
