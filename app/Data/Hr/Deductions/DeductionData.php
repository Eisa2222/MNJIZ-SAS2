<?php

namespace App\Data\Hr\Deductions;

class DeductionData
{
    public int        $employee_id;
    public string     $deduction_type;
    public float      $amount;
    public \DateTime  $deduction_date;
    public ?string    $notes;

    public function __construct(array $data)
    {
        $this->employee_id   = (int)   $data['employee_id'];
        $this->deduction_type  =       $data['deduction_type'];
        $this->amount        = (float) $data['amount'];
        $this->deduction_date  = new \DateTime($data['deduction_date']);
        $this->notes         =         $data['notes'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'employee_id'      => $this->employee_id,
            'deduction_type'      => $this->deduction_type,
            'amount'           => $this->amount,
            'deduction_date'      => $this->deduction_date->format('Y-m-d'),
            'notes'            => $this->notes,
        ];
    }
}
