<?php

namespace App\Http\Controllers\GeneralSetting;

use App\DataTables\SettingsDepartmentContractCaseDataTable;
use App\DataTables\UsersDataTable;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\CommonSettingController;
use App\Models\general_setting\SettingsDepartmentContractCase as GeneralSettingSettingsDepartmentContractCase;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SettingsDepartmentContractCaseController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Constructor for Project and Case Departments Settings
    |--------------------------------------------------------------------------
    | This section contains the constructor method for the SettingsDepartmentContractCaseController.
    | It defines middleware for various actions to ensure proper permissions for managing
    | project and case departments settings.
    */
    public function __construct()
    {
        $this->middleware('can:اقسام المشاريع والقضايا')->only([
            'index',
            'edit',
            'store',
            'update',
            'destroy',
        ]);
    }

    use CommonSettingController;

    protected $model = GeneralSettingSettingsDepartmentContractCase::class;

    public function index()
    {
        return $this->index_trait($this->model, 'general_setting.settings_department_contract_case', 'settings-departments-contracts', 'department_contract_cases');
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
