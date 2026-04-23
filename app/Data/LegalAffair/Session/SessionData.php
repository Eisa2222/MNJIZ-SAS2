<?php

namespace App\Data\LegalAffair\Session;

class SessionData
{
    public int        $project_id;
    public int        $lawsuit_id;
    public array      $assigned_to;
    public int        $entity_ranks_id;
    public \DateTime  $session_date;
    public string     $session_time;

    public function __construct(array $data)
    {
        $this->project_id                = $data['project_id'];
        $this->lawsuit_id                = $data['lawsuit_id'];
        $this->assigned_to               = $data['assigned_to'] ?? [];
        $this->entity_ranks_id           = $data['entity_ranks_id'];
        $this->session_date              = new \DateTime($data['session_date']);
        $this->session_time              = $data['session_time'];
    }

    public function toArray(): array
    {
        return [
            'project_id'                => $this->project_id,
            'lawsuit_id'                => $this->lawsuit_id,
            'assigned_to'               => $this->assigned_to,
            'entity_ranks_id'           => $this->entity_ranks_id,
            'session_date'              => $this->session_date->format('Y-m-d'),
            'session_time'              => $this->session_time,
        ];
    }
}
