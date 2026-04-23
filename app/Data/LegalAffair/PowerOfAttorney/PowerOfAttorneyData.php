<?php

namespace App\Data\LegalAffair\PowerOfAttorney;


class PowerOfAttorneyData
{
    public string     $power_name;
    public string     $power_number;
    public array      $agents;
    public array      $customers;
    public \DateTime  $date_issued;
    public ?\DateTime $date_expiry;
    public ?string    $file_attachment;
    public ?string    $notes;

    public function __construct(array $data)
    {
        $this->power_name      = $data['power_name'];
        $this->power_number    = $data['power_number'];
        $this->agents          = $data['agents'] ?? [];
        $this->customers       = $data['customers'] ?? [];
        $this->date_issued     = new \DateTime($data['date_issued']);
        $this->date_expiry     = !empty($data['date_expiry']) ? new \DateTime($data['date_expiry']) : null;
        $this->file_attachment = $data['file_attachment'] ?? null;
        $this->notes           = $data['notes'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'power_name'      => $this->power_name,
            'power_number'    => $this->power_number,
            'agents'          => $this->agents,
            'customers'       => $this->customers,
            'date_issued'     => $this->date_issued->format('Y-m-d'),
            'date_expiry'     => $this->date_expiry?->format('Y-m-d'),
            'file_attachment' => $this->file_attachment,
            'notes'           => $this->notes,
        ];
    }
}
