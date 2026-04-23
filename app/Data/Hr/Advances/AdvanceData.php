<?php

namespace App\Data\Hr\Advances;

class AdvanceData
{
    public int        $employee_id;
    public string     $advance_type;
    public float      $amount;
    public \DateTime  $advance_date;
    public ?\DateTime $due_date;
    public ?string    $notes;

    public function __construct(array $data)
    {
        $this->employee_id   = (int)   $data['employee_id'];
        $this->advance_type  =         $data['advance_type'];
        $this->amount        = (float) $data['amount'];
        $this->advance_date  = new \DateTime($data['advance_date']);
        $this->due_date      = !empty($data['due_date']) ? new \DateTime($data['due_date']) : null;
        $this->notes         =        $data['notes'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'employee_id'      => $this->employee_id,
            'advance_type'     => $this->advance_type,
            'amount'           => $this->amount,
            'advance_date'     => $this->advance_date->format('Y-m-d'),
            'due_date'         => $this->due_date?->format('Y-m-d'),
            'notes'            => $this->notes,
        ];
    }
}