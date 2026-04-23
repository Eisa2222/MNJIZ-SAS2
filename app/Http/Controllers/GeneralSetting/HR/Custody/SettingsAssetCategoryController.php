<?php

namespace App\Http\Controllers\GeneralSetting\HR\Custody;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\CommonSettingController;
use App\Models\general_setting\HR\Custody\SettingsAssetCategory;
use Illuminate\Http\Request;

class SettingsAssetCategoryController extends Controller
{

    public function __construct()
    {
        $this->middleware('can:تصنيفات الاصول')->only([
            'index',
            'edit',
            'store',
            'update',
            'destroy',
        ]);
    }

    use CommonSettingController;

    protected $model = SettingsAssetCategory::class;

    public function index()
    {
        return $this->index_trait($this->model, 'general_setting.hr.custody.settings_asset_category', 'settings-asset-category');
    }

    public function edit($id) // لتغيير حالة الاعدادات
    {
        return $this->toggleStatus_trait($this->model, $id);
    }

    public function store(Request $request)
    {
        return $this->store_trait($request, $this->model);
    }

    public function update(Request $request, $id)
    {
        return $this->update_trait($request, $this->model, $id);
    }

    public function destroy($id)
    {
        return $this->destroy_trait($this->model, $id);
    }
}