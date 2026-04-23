<?php

namespace App\Data\Hr\ViolationsPenalties;


class ViolationsPenaltiesData
{
    public int                 $employee_id;
    public int                 $settings_violation_id;
    public \DateTime           $violation_date;
    public bool                $is_appealable;
    public int                 $appeal_days;
    public ?string             $notes;

    public function __construct(array $data)
    {
        $this->employee_id             = (int)   $data['employee_id'];
        $this->settings_violation_id   = (int)   $data['settings_violation_id'];
        $this->violation_date          = new \DateTime($data['violation_date']);
        $this->is_appealable           = (bool)  ($data['is_appealable'] ?? false);
        $this->appeal_days             = isset($data['appeal_days'])
            ? (int)$data['appeal_days']
            : 0;
        $this->notes                   = $data['notes'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'employee_id'             => $this->employee_id,
            'settings_violation_id'   => $this->settings_violation_id,
            'violation_date'          => $this->violation_date->format('Y-m-d H:i:s'),
            'is_appealable'           => $this->is_appealable,
            'appeal_days'             => $this->appeal_days,
            'notes'                   => $this->notes,
        ];
    }
}
