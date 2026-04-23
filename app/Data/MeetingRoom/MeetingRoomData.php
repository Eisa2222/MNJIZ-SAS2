<?php

namespace App\Data\MeetingRoom;

class MeetingRoomData
{
    public string  $title;
    public string  $hall;
    public string  $date;
    public string  $from_time;
    public string  $to_time;
    public ?string $notes;
    public array   $participants;

    public function __construct(array $data)
    {
        $this->title        = $data['title'];
        $this->hall         = $data['hall'];
        $this->date         = $data['date'];
        $this->from_time    = $data['from_time'];
        $this->to_time      = $data['to_time'];
        $this->notes        = $data['notes'] ?? null;
        $this->participants = $data['participants'] ?? [];
    }

    public function toArray(): array
    {
        return [
            'title'        => $this->title,
            'hall'         => $this->hall,
            'date'         => $this->date,
            'from_time'    => $this->from_time,
            'to_time'      => $this->to_time,
            'notes'        => $this->notes,
            'participants' => $this->participants,
        ];
    }
}
