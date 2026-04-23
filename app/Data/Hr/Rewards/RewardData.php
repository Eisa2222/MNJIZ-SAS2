<?php

namespace App\Data\Hr\Rewards;

class RewardData
{
    public int        $employee_id;
    public string     $reward_type;
    public float      $amount;
    public \DateTime  $reward_date;
    public ?string    $notes;

    public function __construct(array $data)
    {
        $this->employee_id   = (int)   $data['employee_id'];
        $this->reward_type  =          $data['reward_type'];
        $this->amount        = (float) $data['amount'];
        $this->reward_date  = new \DateTime($data['reward_date']);
        $this->notes         =        $data['notes'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'employee_id'      => $this->employee_id,
            'reward_type'      => $this->reward_type,
            'amount'           => $this->amount,
            'reward_date'      => $this->reward_date->format('Y-m-d'),
            'notes'            => $this->notes,
        ];
    }
}
