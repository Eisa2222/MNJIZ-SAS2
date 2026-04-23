<?php

namespace App\Http\Controllers\GeneralSetting;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\CommonSettingController;
use App\Models\general_setting\SettingsPurchaseCategory;
use Illuminate\Http\Request;

class SettingsPurchaseCategoryController extends Controller
{

    public function __construct()
    {
        $this->middleware('can:تصنيف المشتريات')->only([
            'index',
            'store',
            'update',
            'destroy',
            'edit',
        ]);
    }

    use CommonSettingController;

    protected $model = SettingsPurchaseCategory::class;

    public function index()
    {
        return $this->index_trait($this->model,'general_setting.settings_purchase_category','settings-purchase-category');
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
