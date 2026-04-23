<?php

namespace App\Http\Controllers\GeneralSetting;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\CommonSettingController;
use App\Models\general_setting\SettingsContractStatus;
use Illuminate\Http\Request;

class SettingsContractStatusController extends Controller
{

    public function __construct()
    {
        $this->middleware('can:حالات العقود')->only([
            'index',
            'edit',
            'store',
            'update',
            'destroy',
        ]);
    }

    use CommonSettingController;

    protected $model = SettingsContractStatus::class;

    public function index()
    {
        $model = $this->model;
        $view = 'general_setting.settings_contract_status';
        $route = 'settings-contract-statuses';
        $with = 'contracts';

        $request = request();

        try {
            if ($request->ajax()) {
                $setting = $model::select([
                    'id',
                    'name',
                    'status',
                    'created_at',
                ]);

                return datatables()->of($setting)
                    ->addIndexColumn() // إضافة هذا السطر لتفعيل الترقيم
                    ->addColumn('checkbox', function ($row) {
                        return '<input type="checkbox" class="row-checkbox" value="' . $row->id . '">';
                    })
                    ->addColumn('status', function ($row) {
                        switch ($row->status) {
                            case 'active':
                                $badge = '<span class="cursor-pointer badge bg-success status-toggle" data-id="' . $row->id . '">نشط</span>';
                                break;
                            case 'inactive':
                                $badge = '<span class="cursor-pointer badge bg-danger status-toggle" data-id="' . $row->id . '">غير نشط</span>';
                                break;
                            default:
                                $badge = '<span class="badge bg-secondary">غير معروف</span>';
                                break;
                        }
                        return $badge;
                    })
                    ->addColumn('action', function ($row) use ($route) {
                        $buttons = '<div class="d-flex gap-2">';

                        if ($row->id != 1 && $row->id != 2 && $row->id != 5) {
                            // update this status
                            $buttons .= '
                            <button type="button" class="btn btn-sm text-secondary btn-edit" data-id="' . $row->id . '" data-name="' . htmlspecialchars($row->name, ENT_QUOTES, 'UTF-8') . '">
                                <i class="ti ti-edit"></i>
                            </button>';

                            // delete this status
                            $buttons .= '
                            <button type="button" class="btn btn-sm text-secondary" onclick="confirmDelete(' . $row->id . ')">
                                <i class="ti ti-trash"></i>
                            </button>

                            <form id="delete-form-' . $row->id . '" action="' . route($route . '.destroy', $row->id) . '" method="POST" style="display: none;">
                                ' . csrf_field() . '
                                ' . method_field('DELETE') . '
                            </form>';
                        }else{
                            $buttons = " لا يمكن تعديل هذه الحالة";
                        }

                        $buttons .= '</div>';


                        return $buttons;
                    })
                    ->editColumn('created_at', function ($row) {
                        return $row->hijri_created_at ? $row->hijri_created_at : '';
                    })

                    ->rawColumns(['action', 'status'])
                    ->make(true);
            }

            return view($view, compact('route'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
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
