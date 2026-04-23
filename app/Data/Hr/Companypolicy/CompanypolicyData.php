<?php

namespace App\Data\Hr\Companypolicy;

class CompanypolicyData
{
    public array $name = [];
    public array $file = [];
    public array $is_mandatory = [];

    public function __construct(array $data)
    {
        $this->name         = $data['name'] ?? [];
        $this->file         = $data['file'] ?? [];
        $this->is_mandatory = $data['is_mandatory'] ?? [];
    }

    public function toArray(): array
    {
        return [
            'name'          => $this->name,
            'file'          => $this->file,
            'is_mandatory'  => $this->is_mandatory,
        ];
    }
}
