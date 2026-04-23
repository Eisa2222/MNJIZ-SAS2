<?php

namespace App\Data\Survey;

use App\Enums\Survey\SurveyType;

class SurveyData
{
    public string     $title;
    public SurveyType $type;
    public string     $message_template;
    public ?string    $description;
    public array      $questions = [];

    public function __construct(array $data)
    {
        $this->title            = $data['title'];
        $this->type             = SurveyType::from($data['type']);
        $this->description      = $data['description'] ?? null;
        $this->message_template = $data['message_template'];
        $this->questions        = array_values($data['questions'] ?? []);
    }

    public function toArray(): array
    {
        return [
            'title'            => $this->title,
            'type'             => $this->type->value,
            'description'      => $this->description,
            'message_template' => $this->message_template,
        ];
    }
}
