<?php

namespace App\Http\Controllers\Marketing\CampaignManagement\CampaignResults;

use App\Data\Marketing\CampaignManagement\CampaignResult\CampaignResultData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Marketing\CampaignManagement\CampaignResult\CampaignResultRequest;
use App\Models\Marketing\CampaignManagement\CampaignManagement;
use App\Services\Marketing\CampaignManagement\CampaignResult\CampaignResultService;

class CampaignResultController extends Controller
{

    public function __construct(
        private CampaignResultService $service,
    ) {}
    /*
    |============================================================================
    | create
    |============================================================================
    */
    public function create(CampaignManagement $campaign_management)
    {
        $campaign_result = $campaign_management->result()->first(); 

        return view('marketing.campaign_management.result.create', compact('campaign_management','campaign_result'));
    }

    /*
    |============================================================================
    | store
    |============================================================================
    */
    public function store(CampaignResultRequest $request, CampaignManagement $campaign_management)
    {
        try {

            $data = new CampaignResultData([
                ...$request->validated(),
                'campaign_id' => $campaign_management->id,
            ]);

            $this->service->createOrUpdateCampaignResult($data);

            return redirect()->route('marketing.campaign-management.show', $campaign_management->id)->with('success', 'تم إضافة نتائج الحملة بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
}
