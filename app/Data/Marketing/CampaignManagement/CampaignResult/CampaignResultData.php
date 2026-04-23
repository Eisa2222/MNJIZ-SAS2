<?php

namespace App\Data\Marketing\CampaignManagement\CampaignResult;

use DateTime;

class CampaignResultData
{
    public int      $campaign_id;
    public DateTime $report_date;
    public ?float   $spend;
    public ?int     $impressions;
    public ?int     $clicks;
    public ?float   $ctr;
    public ?float   $cpc;
    public ?int     $conversions;
    public ?float   $conversion_value;
    public ?float   $roas;
    public ?string  $notes;

    public function __construct(array $data)
    {
        $this->campaign_id      = $data['campaign_id'];
        $this->report_date      = new DateTime($data['report_date']);
        $this->spend            = isset($data['spend']) ? (float) $data['spend'] : null;
        $this->impressions      = isset($data['impressions']) ? (int) $data['impressions'] : null;
        $this->clicks           = isset($data['clicks']) ? (int) $data['clicks'] : null;
        $this->ctr              = isset($data['ctr']) ? (float) $data['ctr'] : null;
        $this->cpc              = isset($data['cpc']) ? (float) $data['cpc'] : null;
        $this->conversions      = isset($data['conversions']) ? (int) $data['conversions'] : null;
        $this->conversion_value = isset($data['conversion_value']) ? (float) $data['conversion_value'] : null;
        $this->roas             = isset($data['roas']) ? (float) $data['roas'] : null;
        $this->notes            = $data['notes'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'campaign_id'      => $this->campaign_id,
            'report_date'      => $this->report_date->format('Y-m-d'),
            'spend'            => $this->spend,
            'impressions'      => $this->impressions,
            'clicks'           => $this->clicks,
            'ctr'              => $this->ctr,
            'cpc'              => $this->cpc,
            'conversions'      => $this->conversions,
            'conversion_value' => $this->conversion_value,
            'roas'             => $this->roas,
            'notes'            => $this->notes,
        ];
    }
}
