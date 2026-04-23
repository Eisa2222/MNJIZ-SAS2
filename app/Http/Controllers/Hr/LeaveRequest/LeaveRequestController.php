<?php

namespace App\Http\Controllers\Hr\LeaveRequest;

use App\Http\Controllers\Controller;
use App\Models\ElectronicServices\LeaveRequests\LeaveRequest;
use App\Models\general_setting\SettingsLeaveType;
use App\Models\Hr\Employees\Employees;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Illuminate\Validation\Rules\Can;

class LeaveRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:طلبات الإجازات')->only(['index']);
    }

    /*
    |--------------------------------------------------------------------------
    |  Index
    |--------------------------------------------------------------------------
    | جميع الطلبات
    */
    public function index(Request $request)
    {
        $query = LeaveRequest::with(['employee', 'leaveType'])
            ->select('leave_requests.*');

        if ($request->filled('filter_leave_type')) {
            $query->where('leave_type_id', $request->filter_leave_type);
        }
        if ($request->filled('filter_employee')) {
            $query->where('employee_id', $request->filter_employee);
        }
        if ($request->filled('filter_status')) {
            $query->where('status', $request->filter_status);
        }
        if ($request->ajax()) {
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('employee_name', function ($row) {
                    if ($row->employee) {
                        return $row->employee->name;
                    }
                    return '—';
                })
                ->addColumn('leave_type_name', function ($row) {
                    if (!auth()->user()->can('إعتماد الإجازات')) {
                        return $row->leaveType ? $row->leaveType->name : '—';
                    }

                    $url = route('hr.leave-requests.show', $row->id);

                    if ($row->leaveType) {
                        return '<a href="' . $url . '" onclick="event.stopPropagation();">' . e($row->leaveType->name) . '</a>';
                    }

                    return '--';
                })

                ->editColumn('start_date', function ($row) {
                    return \Carbon\Carbon::parse($row->start_date)->format('Y-m-d');
                })
                ->editColumn('end_date', function ($row) {
                    return \Carbon\Carbon::parse($row->end_date)->format('Y-m-d');
                })
                ->editColumn('status', function ($row) {
                    $statusBadge = sprintf(
                        '<span class="badge bg-%s">%s</span>',
                        $row->status->color(),
                        $row->status->label()
                    );
                    return $statusBadge;
                })
                ->rawColumns(['leave_type_name', 'status'])
                ->make(true);
        }

        $leaveTypes = SettingsLeaveType::where('is_global', false)
            ->orderBy('id', 'desc')
            ->get(['id', 'name']);
        $employees = Employees::active()->select('id', 'name', 'nickname')->get();


        return view('hr.leave_requests.index', compact('leaveTypes', 'employees'));
    }


    /*
    |--------------------------------------------------------------------------
    |  Show
    |--------------------------------------------------------------------------
    | عرض تفاصيل طلب الإجازة
    */
    public function show($id)
    {
        $leaveRequest = LeaveRequest::with([
            'employee',
            'leaveType',
            'attachments',
            'createdBy',
            'updatedBy',
            'approvalRequest.requestLevels.employee'
        ])->findOrFail($id);

        $approvalStages = [];
        if ($leaveRequest->approvalRequest) {
            $approvalStages = $leaveRequest->approvalRequest->getApprovalStages();
        }
        $statusConfig = $leaveRequest->getStatusConfig();

        return view('hr.leave_requests.show', compact('leaveRequest', 'approvalStages', 'statusConfig'));
    }
}
