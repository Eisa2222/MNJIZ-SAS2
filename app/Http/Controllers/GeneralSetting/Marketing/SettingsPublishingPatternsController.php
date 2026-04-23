<?php

namespace App\Http\Controllers\GeneralSetting\Marketing;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\CommonSettingController;
use App\Models\general_setting\Marketing\SettingsPublishingPattern;
use Illuminate\Http\Request;

class SettingsPublishingPatternsController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:أنماط النشر')->only([
            'index',
            'edit',
            'store',
            'update',
            'destroy',
        ]);
    }

    use CommonSettingController;

    protected $model = SettingsPublishingPattern::class;

    public function index()
    {
        return $this->index_trait($this->model, 'general_setting.marketing.settings_publishing_pattern', 'settings-publishing-patterns');
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
