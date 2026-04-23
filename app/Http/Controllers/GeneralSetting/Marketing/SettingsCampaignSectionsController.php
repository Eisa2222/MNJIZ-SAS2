<?php

namespace App\Http\Controllers\GeneralSetting\Marketing;

use App\DataTables\GeneralSetting\Marketing\SettingsCampaignSectionDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\GeneralSetting\Marketing\CampaignSection\SettingsCampaignSectionRequest;
use App\Models\general_setting\Marketing\SettingsCampaignSection;
use Illuminate\Http\Request;

class SettingsCampaignSectionsController extends Controller
{

    public function __construct()
    {
        $this->middleware('can:أقسام الحملات')->only([
            'index',
            'edit',
            'store',
            'update',
            'destroy',
        ]);
    }

    /*
    |============================================================================
    |============================================================================
    |                               Title
    |============================================================================
    |============================================================================
    */
    private const ROUTE = 'settings-campaign-sections';
    private const VIEW  = 'general_setting.marketing.campaign_sections';
    private const MODEL = SettingsCampaignSection::class;


    public function index(SettingsCampaignSectionDataTable $dataTable, Request $request)
    {
        try {
            if ($request->ajax()) return $dataTable->ajax();

            return $dataTable->render(self::VIEW . '.index', [
                'route' => self::ROUTE
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    }


    public function store(SettingsCampaignSectionRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        $section = self::MODEL::create($data);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'تمت الإضافة بنجاح.',
                'data'    => $section,
            ]);
        }

        return redirect()->back()->with('success', 'تمت الإضافة بنجاح.');
    }

    public function update(SettingsCampaignSectionRequest $request, $id)
    {
        $data = $request->validated();

        $section = self::MODEL::findOrFail($id);
        $section->update([
            'name'  => $data['name'],
            'color' => $data['color'],
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'تم التعديل بنجاح.'
            ]);
        }


        return redirect()->back()->with('success', 'تم التعديل بنجاح.');
    }

    public function destroy(Request $request, $id)
    {
        $section = self::MODEL::findOrFail($id);
        $section->delete();

        return redirect()->back()->with('success', 'تم الحذف بنجاح.');
    }


    public function edit($id) // لتغيير حالة الاعدادات
    {
        $setting = self::MODEL::findOrFail($id);

        // تغيير الحالة
        if ($setting->status === 'active') {
            $setting->status = 'inactive';
        } else {
            $setting->status = 'active';
        }

        $setting->save();

        return response()->json([
            'success' => true,
            'status' => $setting->status,
        ]);
    }
}
