<?php

namespace App\Data\Hr\Companypolicy;

use Illuminate\Http\UploadedFile;

class CompanypolicyUpdateData
{
    public string $name;
    public ?UploadedFile $file;
    public bool $is_mandatory;

    public function __construct(array $data)
    {
        $this->name = $data['name'];
        $this->file = $data['file'] ?? null;
        $this->is_mandatory = (bool) ($data['is_mandatory'] ?? false);
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
