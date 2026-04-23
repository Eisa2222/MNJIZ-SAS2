<?php

namespace App\Data\Marketing\CampaignManagement;

use App\Enums\Marketing\CampaignManagement\CampaignStatus;
use DateTime;

class CampaignManagementData
{
    public ?string           $campaign_name;
    public ?int              $content_type_id;
    public ?int              $campaign_section_id;
    public ?int              $content_purpose_id;
    public ?int              $social_id;
    public ?float            $budget;
    public ?DateTime         $start_date;
    public ?DateTime         $end_date;
    public ?int              $target_audience_id;
    public CampaignStatus    $status;
    public ?string           $text;

    public function __construct(array $data)
    {
        $this->campaign_name         = $data['campaign_name']         ?? null;
        $this->content_type_id       = $data['content_type_id']       ?? null;
        $this->campaign_section_id   = $data['campaign_section_id']   ?? null;
        $this->content_purpose_id    = $data['content_purpose_id']    ?? null;
        $this->social_id             = $data['social_id']             ?? null;
        $this->budget                = isset($data['budget']) ? (float) $data['budget'] : null;
        $this->start_date            = isset($data['start_date']) ? new DateTime($data['start_date']) : null;
        $this->end_date              = isset($data['end_date']) ? new DateTime($data['end_date']) : null;
        $this->target_audience_id    = $data['target_audience_id']    ?? null;
        $this->status                = CampaignStatus::Pending;
        $this->text                  = $data['text']                  ?? null;
    }

    public function toArray(): array
    {
        return [
            'campaign_name'         => $this->campaign_name,
            'content_type_id'       => $this->content_type_id,
            'campaign_section_id'   => $this->campaign_section_id,
            'content_purpose_id'    => $this->content_purpose_id,
            'social_id'             => $this->social_id,
            'budget'                => $this->budget,
            'start_date'            => $this->start_date?->format('Y-m-d'),
            'end_date'              => $this->end_date?->format('Y-m-d'),
            'target_audience_id'    => $this->target_audience_id,
            'status'                => $this->status->value,
            'text'                  => $this->text,
        ];
    }
}
