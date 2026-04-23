<?php

namespace App\Services\Marketing\CampaignManagement;

use App\Contracts\ErrorHandlerInterface;
use App\Data\Marketing\CampaignManagement\CampaignManagementData;
use App\Models\Marketing\CampaignManagement\CampaignManagement;
use Illuminate\Support\Facades\DB;

class CampaignManagementService
{
    public function __construct(
        private ErrorHandlerInterface $errorHandler
    ) {}


    // create
    public function createCampaign(CampaignManagementData $dto): CampaignManagement
    {
        return $this->errorHandler->execute(function () use ($dto) {
            return DB::transaction(function () use ($dto) {
                return CampaignManagement::create([
                    'campaign_name'         => $dto->campaign_name,
                    'content_type_id'       => $dto->content_type_id,
                    'campaign_section_id'   => $dto->campaign_section_id,
                    'content_purpose_id'    => $dto->content_purpose_id,
                    'social_id'             => $dto->social_id,
                    'budget'                => $dto->budget,
                    'start_date'            => $dto->start_date?->format('Y-m-d'),
                    'end_date'              => $dto->end_date?->format('Y-m-d'),
                    'target_audience_id'    => $dto->target_audience_id,
                    'status'                => $dto->status,
                    'text'                  => $dto->text,
                    'created_by'            => auth()->id(),
                ]);
            });
        }, 'حدث خطأ أثناء إنشاء الحملة');
    }


    public function updateCampaign(int $id, CampaignManagementData $dto): CampaignManagement
    {
        return $this->errorHandler->execute(function () use ($id, $dto) {
            return DB::transaction(function () use ($id, $dto) {
                $campaign = CampaignManagement::findOrFail($id);

                $campaign->update([
                    'campaign_name'         => $dto->campaign_name,
                    'content_type_id'       => $dto->content_type_id,
                    'campaign_section_id'   => $dto->campaign_section_id,
                    'content_purpose_id'    => $dto->content_purpose_id,
                    'social_id'             => $dto->social_id,
                    'budget'                => $dto->budget,
                    'start_date'            => $dto->start_date?->format('Y-m-d'),
                    'end_date'              => $dto->end_date?->format('Y-m-d'),
                    'target_audience_id'    => $dto->target_audience_id,
                    'status'                => $dto->status,
                    'text'                  => $dto->text,
                    'updated_by'            => auth()->id(),
                ]);

                return $campaign;
            });
        }, 'حدث خطأ أثناء تحديث الحملة');
    }



    public function deleteCampaign(int $id): bool
    {
        return $this->errorHandler->execute(function () use ($id) {
            return DB::transaction(function () use ($id) {
                $campaign = CampaignManagement::findOrFail($id);

                return $campaign->delete();
            });
        }, 'حدث خطأ أثناء حذف الحملة');
    }
}
