<?php

namespace App\Http\Controllers\GeneralSetting;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\CommonSettingController;
use App\Models\general_setting\SettingsHRClassification;
use Illuminate\Http\Request;


class SettingsHRClassificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:تصنيف الموارد البشرية')->only([
            'index',
            'edit',
            'store',
            'update',
            'destroy',
        ]);
    }

    use CommonSettingController;

    protected $model = SettingsHRClassification::class;

    public function index()
    {
        return $this->index_trait($this->model,'general_setting.settings_hr_classifications','settings-hr_classification');
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
