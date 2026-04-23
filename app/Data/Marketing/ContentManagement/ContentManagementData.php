<?php

namespace App\Data\Marketing\ContentManagement;

use App\Enums\Marketing\ContentManagement\ContentStatus;
use App\Enums\Marketing\ContentManagement\MediaType;
use App\Enums\Marketing\ContentManagement\PublicationStatus;
use App\Enums\Marketing\ContentManagement\PublishType;
use App\Enums\Marketing\ContentManagement\RecurringType;
use App\Enums\Shared\WeekDay;
use DateTime;
use Illuminate\Support\Arr;

class ContentManagementData
{
    public ?int           $content_type_id;
    public ?string        $content_text;
    public ?int           $publishing_pattern_id;
    public ?int           $content_purpose_id;
    public array          $social_ids;
    public ContentStatus  $status;
    public PublicationStatus  $publication_status;
    public ?MediaType     $media_type;
    public ?string        $media;
    public ?DateTime      $publication_date;

    public ?PublishType   $publish_type;
    public ?DateTime      $one_time_at;
    public ?RecurringType $recurring_type;
    public ?string        $publish_time;
    public ?int           $month_day;
    public ?array         $week_days;
    public ?DateTime      $start_date;
    public ?DateTime      $end_date;
    public bool           $is_active;

    public function __construct(array $data)
    {
        $this->content_type_id          = Arr::get($data, 'content_type_id');
        $this->content_text             = Arr::get($data, 'content_text');
        $this->publishing_pattern_id    = Arr::get($data, 'publishing_pattern_id');
        $this->content_purpose_id       = Arr::get($data, 'content_purpose_id');
        $this->social_ids               = Arr::get($data, 'socials', []);
        $this->status                   = ContentStatus::tryFrom(Arr::get($data, 'status')) ?? ContentStatus::Pending;
        $this->publication_status       = PublicationStatus::tryFrom(Arr::get($data, 'publication_status')) ?? PublicationStatus::Draft;
        $this->media_type               = MediaType::tryFrom(Arr::get($data, 'media_type'));
        $this->media                    = Arr::get($data, 'media');
        $this->publication_date         = ($date = Arr::get($data, 'publication_date')) ? new DateTime($date) : null;

        $this->publish_type             = PublishType::tryFrom(Arr::get($data, 'publish_type'));
        $this->one_time_at              = ($date = Arr::get($data, 'one_time_at')) ? new DateTime($date) : null;
        $this->recurring_type           = RecurringType::tryFrom(Arr::get($data, 'recurring_type'));
        $this->publish_time             = Arr::get($data, 'publish_time');
        $this->month_day                = Arr::get($data, 'month_day') ? (int)Arr::get($data, 'month_day') : null;
        $this->week_days                = Arr::get($data, 'week_days');
        $this->start_date               = ($date = Arr::get($data, 'start_date')) ? new DateTime($date) : null;
        $this->end_date                 = ($date = Arr::get($data, 'end_date')) ? new DateTime($date) : null;
        $this->is_active                = Arr::get($data, 'is_active', false);

    }

    public function toArray(): array
    {
        return [
            'content_type_id'           => $this->content_type_id,
            'content_text'              => $this->content_text,
            'publishing_pattern_id'     => $this->publishing_pattern_id,
            'content_purpose_id'        => $this->content_purpose_id,
            'social_ids'                => $this->social_ids,
            'status'                    => $this->status->value,
            'publication_status'        => $this->publication_status->value,
            'media_type'                => $this->media_type?->value,
            'media'                     => $this->media,
            'publication_date'          => $this->publication_date?->format('Y-m-d H:i:s'),

            'publish_type'              => $this->publish_type?->value,
            'one_time_at'               => $this->one_time_at?->format('Y-m-d H:i:s'),
            'recurring_type'            => $this->recurring_type?->value,
            'publish_time'              => $this->publish_time,
            'month_day'                 => $this->month_day,
            'week_days'                 => $this->week_days, 
            'start_date'                => $this->start_date?->format('Y-m-d'),
            'end_date'                  => $this->end_date?->format('Y-m-d'),
            'is_active'                 => $this->is_active,
        ];
    }
}
