<?php

namespace App\Http\Controllers\Marketing\CampaignManagement;

use App\Data\Marketing\CampaignManagement\CampaignManagementData;
use App\DataTables\Marketing\CampaignManagement\CampaignManagementDataTable;
use App\Enums\Marketing\CampaignManagement\CampaignStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Marketing\CampaignManagement\CampaignManagementRequest;
use App\Models\general_setting\Marketing\SettingsCampaignSection;
use App\Models\general_setting\Marketing\SettingsContentPurpose;
use App\Models\general_setting\Marketing\SettingsContentType;
use App\Models\general_setting\Marketing\SettingsDisplayLocation;
use App\Models\general_setting\Marketing\SettingsTargetAudience;
use App\Models\general_setting\SettingsSessionType;
use App\Models\general_setting\SettingsSocial;
use App\Models\Marketing\CampaignManagement\CampaignManagement;
use App\Services\Marketing\CampaignManagement\CampaignManagementService;
use Illuminate\Http\Request;

class CampaignManagementController extends Controller
{
    public function __construct(
        private CampaignManagementService $service,
    ) {}

    /*
    |============================================================================
    | Index
    |============================================================================
    */
    public function index(CampaignManagementDataTable $dataTable, Request $request)
    {
        try {
            if ($request->ajax()) return $dataTable->ajax();

            // // Statistics
            $statusCounts = CampaignManagement::query()
                ->select('status')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $totalCampaign          = array_sum($statusCounts);
            $pendingCampaign        = $statusCounts[CampaignStatus::Pending->value] ?? 0;
            $approvedCampaign       = $statusCounts[CampaignStatus::Approved->value] ?? 0;
            $rejectedCampaign       = $statusCounts[CampaignStatus::Rejected->value] ?? 0;
            // Filters
            $contentTypes           = SettingsContentType::select('id', 'name')->get();
            $campaignSection        = SettingsCampaignSection::select('id', 'name')->get();
            $campaignStatus         = CampaignStatus::cases();

            return $dataTable->render('marketing.campaign_management.index', compact(
                'totalCampaign',
                'pendingCampaign',
                'approvedCampaign',
                'rejectedCampaign',

                'contentTypes',
                'campaignSection',
                'campaignStatus',
            ));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    }


    /*
    |============================================================================
    | create
    |============================================================================
    */
    public function create()
    {

        $contentTypes        = SettingsContentType::where('status', 'active')->select(['id', 'name'])->get();         // نوع الإعلان
        $campaignSections    = SettingsCampaignSection::where('status', 'active')->select(['id', 'name'])->get();     // قسم الحملة
        $contentPurposes     = SettingsContentPurpose::where('status', 'active')->select(['id', 'name'])->get();      // هدف الحملة
        $socialPlatforms     = SettingsSocial::where('status', 'active')->select(['id', 'name'])->get();              // المنصات
        $targetAudiences     = SettingsTargetAudience::where('status', 'active')->select(['id', 'name'])->get();      // جمهور الاستهداف


        return view('marketing.campaign_management.create', compact(
            'contentTypes',
            'campaignSections',
            'contentPurposes',
            'socialPlatforms',
            'targetAudiences',
        ));
    }

    /*
    |============================================================================
    | store
    |============================================================================
    */
    public function store(CampaignManagementRequest $request)
    {
        try {
            $data = new CampaignManagementData($request->validated());

            $this->service->createCampaign($data);

            return redirect()->route('marketing.campaign-management.index')->with('success', 'تم إضافة الحملة بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }


    /*
    |============================================================================
    | show
    |============================================================================
    */
    public function show(CampaignManagement $campaign_management)
    {
        return view('marketing.campaign_management.show', compact('campaign_management'));
    }



    /*
    |============================================================================
    | edit
    |============================================================================
    */
    public function edit(CampaignManagement $campaign_management)
    {

        $contentTypes        = SettingsContentType::where('status', 'active')->select(['id', 'name'])->get();         // نوع الإعلان
        $campaignSections    = SettingsCampaignSection::where('status', 'active')->select(['id', 'name'])->get();     // قسم الحملة
        $contentPurposes     = SettingsContentPurpose::where('status', 'active')->select(['id', 'name'])->get();      // هدف الحملة
        $socialPlatforms     = SettingsSocial::where('status', 'active')->select(['id', 'name'])->get();              // المنصات
        $targetAudiences     = SettingsTargetAudience::where('status', 'active')->select(['id', 'name'])->get();      // جمهور الاستهداف

        return view('marketing.campaign_management.edit',  compact(
            'campaign_management',
            'contentTypes',
            'campaignSections',
            'contentPurposes',
            'socialPlatforms',
            'targetAudiences',
        ));
    }


    /*
    |============================================================================
    | Update
    |============================================================================
    */
    public function update(CampaignManagementRequest $request, CampaignManagement $campaign_management)
    {
        try {
            $data = new CampaignManagementData($request->validated());

            $this->service->updateCampaign($campaign_management->id, $data);

            return redirect()->route('marketing.campaign-management.index')->with('success', 'تم تحديث الحملة بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }


    /*
    |============================================================================
    | Delete
    |============================================================================
    */
    public function destroy(int $id)
    {
        try {
            $this->service->deleteCampaign($id);

            return redirect()->route('marketing.campaign-management.index')->with('success', 'تم حذف الحملة بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
}
