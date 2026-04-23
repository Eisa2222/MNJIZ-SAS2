<?php

namespace App\Http\Controllers\GeneralSetting;

use App\Http\Controllers\Controller;
use App\Models\general_setting\SettingsLeaveType;
use App\Http\Requests\GeneralSetting\LeaveTypeRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;

class SettingsLeaveTypeController extends Controller
{
    protected $model = SettingsLeaveType::class;
    protected $route = 'settings-leave-types';

    public function __construct()
    {
        $this->middleware('can:أنواع الإجازات')->only([
            'index',
            'store',
            'update',
            'destroy',
            'toggleStatus',
            'massDelete',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $leaveTypes = $this->model::select([
                    'id',
                    'name',
                    'days',
                    'is_paid',
                    'status',
                    'start_date',
                    'end_date',
                    'created_at'
                ]);

                return DataTables::of($leaveTypes)
                    ->addIndexColumn()
                    ->addColumn('checkbox', function ($row) {
                        return '<input type="checkbox" class="row-checkbox" value="' . $row->id . '">';
                    })
                    ->addColumn('status', function ($row) {
                        switch ($row->status) {
                            case 'active':
                                return '<span class="cursor-pointer badge bg-success status-toggle" data-id="' . $row->id . '">نشط</span>';
                            case 'inactive':
                                return '<span class="cursor-pointer badge bg-danger status-toggle" data-id="' . $row->id . '">غير نشط</span>';
                            default:
                                return '<span class="badge bg-secondary">غير معروف</span>';
                        }
                    })
                    ->addColumn('is_paid', function ($row) {
                        return $row->is_paid
                            ? '<span class="badge bg-info">مدفوعة</span>'
                            : '<span class="badge bg-warning">غير مدفوعة</span>';
                    })
                    ->addColumn('validity', function ($row) {
                        // إذا لم تُحدد فترة
                        if (!$row->start_date && !$row->end_date) {
                            return '<span class="badge bg-success">غير محدودة</span>';
                        }
                        // بناء النص
                        $text = '';
                        if ($row->start_date) {
                            $text .= 'من ' . Carbon::parse($row->start_date)->locale('ar')->isoFormat('D MMMM YYYY');
                        }
                        if ($row->end_date) {
                            $text .= $row->start_date ? ' إلى ' : '';
                            $text .= Carbon::parse($row->end_date)->locale('ar')->isoFormat('D MMMM YYYY');
                        }
                        // فحص الصلاحية الحالية
                        $now = Carbon::now();
                        $valid = true;
                        if ($row->start_date && Carbon::parse($row->start_date)->gt($now)) {
                            $valid = false;
                        }
                        if ($row->end_date && Carbon::parse($row->end_date)->lt($now)) {
                            $valid = false;
                        }
                        $badgeClass = $valid ? 'bg-success' : 'bg-warning';

                        return '<span class="badge ' . $badgeClass . '">' . $text . '</span>';
                    })
                    ->addColumn('action', function ($row) {
                        $buttons  = '<div class="d-flex gap-2">';
                        // زر التعديل ينتقل الى صفحة edit منفصلة
                        $editUrl  = route('settings-leave-types.edit', $row->id);
                        $buttons .= '<a href="' . $editUrl . '" class="btn btn-sm text-secondary" title="تعديل">
                                         <i class="ti ti-edit"></i>
                                     </a>';
                        // زر الحذف كما في كودك الأصلي
                        $buttons .= '<button type="button" class="btn btn-sm text-secondary btn-delete" onclick="confirmDelete(' . $row->id . ')">
                                         <i class="ti ti-trash"></i>
                                     </button>
                                     <form id="delete-form-' . $row->id . '" action="' . route($this->route . '.destroy', $row->id) . '" method="POST" style="display: none;">
                                         ' . csrf_field() . '
                                         ' . method_field('DELETE') . '
                                     </form>';
                        $buttons .= '</div>';

                        return $buttons;
                    })
                    ->editColumn('created_at', function ($row) {
                        return $row->created_at
                            ? Carbon::parse($row->created_at)->locale('ar')->isoFormat('D MMMM YYYY')
                            : '';
                    })
                    ->rawColumns(['checkbox', 'status', 'is_paid', 'validity', 'action'])
                    ->make(true);
            }

            // عرض الصفحة العادية
            $route = $this->route;
            return view('general_setting.settings_leave_types.index', compact('route'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات: ' . $e->getMessage());
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        return view('general_setting.settings_leave_types.create');
    }

    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    | تخزين نوع إجازة جديد.
    */
    public function store(LeaveTypeRequest $request)
    {
        try {
            $is_paid = isset($request->is_paid) ? $request->is_paid : false;

            $this->model::create([
                'name' => $request->name,
                'days' => $request->days,
                'is_paid' => $is_paid,
                'is_deductible'         => $request->is_deductible ? 1 : 0,
                'is_global' => $request->is_global ? 1 : 0,
                'count_weekends' => $request->has('count_weekends') ? 1 : 0,
                'gender_applicability' => $request->gender_applicability,
                'min_service_years'    => (int) $request->input('min_service_years', 0),
                'has_attachments' => $request->has_attachments ? 1 : 0,
                'attachment_description' => $request->has('has_attachments') ? $request->attachment_description : null,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'max_requests' => (int)$request->max_requests ?? 0,
                'service_years_threshold'  => (int)$request->input('service_years_threshold', 0),
                'days_after_threshold'     => (int)$request->input('days_after_threshold', 0),
                'advance_notice_days'     => (int)$request->input('advance_notice_days', 0),
                'leave_unit_type' => $request->leave_unit_type,
                'is_carry_forwardable' => $request->has('is_carry_forwardable'),
                'user_id' => Auth::id(),
            ]);

            return redirect()->route($this->route . '.index')->with('success', 'تم إضافة نوع الإجازة بنجاح.');
        } catch (\Exception $e) {
            return redirect()->route($this->route . '.index')->with('error', 'حدث خطأ أثناء إضافة نوع الإجازة: ' . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */
    public function edit($id)
    {
        $leaveType = SettingsLeaveType::findOrFail($id);
        return view('general_setting.settings_leave_types.edit', compact('leaveType'));
    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    | تحديث نوع إجازة موجود.
    */
    public function update(LeaveTypeRequest $request, $id)
    {
        $leaveType = $this->model::findOrFail($id);

        try {
            $data = [
                'name'                  => $request->name,
                'days'                  => $request->days,
                'is_paid'               => $request->has('is_paid'),
                'is_deductible'         => $request->has('is_deductible'),
                'is_global'             => $request->has('is_global'),
                'count_weekends'        => $request->has('count_weekends') ? 1 : 0,
                'gender_applicability'  => $request->gender_applicability,
                'min_service_years'     => $request->input('min_service_years') !== null && $request->input('min_service_years') !== '' ? (int) $request->input('min_service_years') : 0,
                'has_attachments'       => $request->has_attachments ? 1 : 0,
                'attachment_description' => $request->has('has_attachments') ? $request->attachment_description : null,
                'start_date'            => $request->start_date,
                'end_date'              => $request->end_date,
                'max_requests'           => (int)$request->max_requests ?? 0,
                'service_years_threshold'  => (int)$request->input('service_years_threshold', 0),
                'days_after_threshold'     => (int)$request->input('days_after_threshold', 0),
                'advance_notice_days'     => (int)$request->input('advance_notice_days', 0),
                'leave_unit_type' => $request->leave_unit_type,
                'is_carry_forwardable' => $request->has('is_carry_forwardable'),
                'user_id'               => Auth::id(),
            ];

            $leaveType->update($data);

            return redirect()->route('settings-leave-types.index')->with('success', 'تم تحديث نوع الإجازة بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء تحديث نوع الإجازة: ' . $e->getMessage())->withInput();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Destroy
    |--------------------------------------------------------------------------
    | حذف نوع إجازة موجود.
    */
    public function destroy($id)
    {
        $leaveType = $this->model::findOrFail($id);
        try {
            $leaveType->delete();
            return redirect()->back()->with('success', 'تم حذف نوع الإجازة بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء حذف نوع الإجازة.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Toggle Status
    |--------------------------------------------------------------------------
    | تغيير حالة نوع الإجازة.
    */
    public function toggleStatus($id)
    {
        $leaveType = $this->model::findOrFail($id);

        $leaveType->status = $leaveType->status === 'active' ? 'inactive' : 'active';
        $leaveType->save();

        return response()->json([
            'success' => true,
            'status' => $leaveType->status,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Mass Delete
    |--------------------------------------------------------------------------
    | حذف أنواع الإجازات المحددة جماعيًا.
    */
    public function massDelete(Request $request)
    {
        $ids = $request->ids;

        if (!$ids) {
            return response()->json(['error' => 'لم يتم تحديد أي عناصر للحذف.'], 400);
        }

        try {
            $this->model::whereIn('id', $ids)->delete();
            return response()->json(['success' => 'تم حذف العناصر المحددة بنجاح.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'حدث خطأ أثناء حذف العناصر.'], 500);
        }
    }
}
