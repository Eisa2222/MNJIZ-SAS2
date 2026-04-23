<?php

namespace App\Services\Marketing\CampaignManagement\CampaignResult;

use App\Contracts\ErrorHandlerInterface;
use App\Data\Marketing\CampaignManagement\CampaignResult\CampaignResultData;
use App\Models\Marketing\CampaignManagement\CampaignResult\CampaignResult;
use Illuminate\Support\Facades\DB;

class CampaignResultService
{
    public function __construct(
        private ErrorHandlerInterface $errorHandler
    ) {}

    public function createOrUpdateCampaignResult(CampaignResultData $dto): CampaignResult
    {
        return $this->errorHandler->execute(function () use ($dto) {
            return DB::transaction(function () use ($dto) {
                $existing = CampaignResult::where('campaign_id', $dto->campaign_id)
                    ->whereDate('report_date', $dto->report_date->format('Y-m-d'))
                    ->first();

                $data = [
                    'spend'             => $dto->spend,
                    'impressions'       => $dto->impressions,
                    'clicks'            => $dto->clicks,
                    'ctr'               => $dto->ctr,
                    'cpc'               => $dto->cpc,
                    'conversions'       => $dto->conversions,
                    'conversion_value'  => $dto->conversion_value,
                    'roas'              => $dto->roas,
                    'notes'             => $dto->notes,
                ];

                if ($existing) {
                    $existing->update(array_merge($data, [
                        'updated_by' => auth()->id(),
                    ]));
                    return $existing;
                }

                return CampaignResult::create(array_merge($data, [
                    'campaign_id' => $dto->campaign_id,
                    'report_date' => $dto->report_date->format('Y-m-d'),
                    'created_by'  => auth()->id(),
                ]));
            });
        }, 'حدث خطأ أثناء حفظ نتائج الحملة');
    }
}
