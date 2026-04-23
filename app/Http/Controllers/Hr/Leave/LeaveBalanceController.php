<?php

namespace App\Http\Controllers\Hr\Leave;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hr\Leave\UpdateLeaveBalanceRequest;
use Illuminate\Http\Request;
use App\Models\Hr\LeaveBalance;
use App\Models\Hr\Employees\Employees;
use App\Models\Hr\LeaveBalanceLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;

class LeaveBalanceController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Construct
    |--------------------------------------------------------------------------
    */
    public function __construct()
    {
        $this->middleware('can:أرصدة الإجازات');
    }

    /*
    |--------------------------------------------------------------------------
    | Leave Balance Index
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $currentYear = Carbon::today()->year;
        if ($request->ajax()) {

            $query = LeaveBalance::with('employee')
                ->where('year', $currentYear)
                ->orderBy('employee_id');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('checkbox', fn($row) =>
                '<input type="checkbox" class="row-checkbox" value="' . $row->id . '">')
                ->addColumn('employee_profile', function ($row) {
                    $default_image = asset('assets/img/branding/Alburhan-Logo.png');

                    if ($row->employee->profile_picture) {
                        $image_path = storage_path('app/public/' . $row->employee->profile_picture);
                        $image_exists = file_exists($image_path);
                        $url = $image_exists
                            ? asset('storage/' . $row->employee->profile_picture)
                            : $default_image;
                    } else {
                        $url = $default_image;
                    }

                    return '<img src="' . $url . '" class="rounded-circle" width="40" height="40">';
                })
                ->addColumn('employee_name', function ($row) {
                    $detailsUrl = route('hr.leave-balances.show', $row->id);
                    return '<a href="' . $detailsUrl . '" class="text-decoration-none fw-bold text-primary">' . e($row->employee->rawName) . '</a>';
                })
                ->filterColumn('employee_name', function ($query, $keyword) {
                    $query->whereHas('employee', function ($q) use ($keyword) {
                        $q->where('name', 'like', '%' . $keyword . '%')
                            ->orWhere('nickname', 'like', '%' . $keyword . '%');
                    });
                })

                ->editColumn('total_days', function ($row) {
                    return is_int($row->total_days) ? $row->total_days : number_format($row->total_days, 4);
                })
                ->editColumn('used_days', function ($row) {
                    return is_int($row->used_days) ? $row->used_days : number_format($row->used_days, 4);
                })
                ->editColumn('remaining_days', function ($row) {
                    return is_int($row->remaining_days) ? $row->remaining_days : number_format($row->remaining_days, 4);
                })
                // ->addColumn('action', function ($row) {
                //     $editUrl = route('hr.leave-balances.edit', $row->id);

                //     $buttons = '<div class="d-flex justify-content-center">';

                //     // زر التعديل فقط
                //     $buttons .= '<a href="' . $editUrl . '" class="btn btn-sm text-secondary mx-1" title="تعديل">
                //                     <i class="ti ti-edit"></i>
                //                 </a>';

                //     $buttons .= '</div>';

                //     return $buttons;
                // })
                ->rawColumns(['checkbox', 'employee_profile', 'employee_name'])
                ->make(true);
        }

        $years     = LeaveBalance::select('year')->distinct()->orderBy('year', 'desc')->pluck('year');
        $employees = Employees::active()->select('id', 'name', 'nickname')->orderBy('name')->get();

        return view('hr.leave_balances.index', compact('years', 'employees'));
    }

    /*
    |--------------------------------------------------------------------------
    | Edit Leave Balance
    |--------------------------------------------------------------------------
    */
    // public function edit($id)
    // {
    //     $balance = LeaveBalance::findOrFail($id);
    //     $currentYear = Carbon::now()->year;

    //     if ($balance->year != $currentYear) {
    //         return redirect()->route('hr.leave-balances.index')
    //             ->with('error', 'لا يمكن تعديل أرصدة الإجازات إلا للسنة الحالية (' . $currentYear . ')');
    //     }

    //     return view('hr.leave_balances.edit', compact('balance'));
    // }

    /*
    |--------------------------------------------------------------------------
    | Update Leave Balance
    |--------------------------------------------------------------------------
    */
    // public function update(UpdateLeaveBalanceRequest $request, $id)
    // {
    //     $balance = LeaveBalance::findOrFail($id);
    //     $currentYear = Carbon::now()->year;

    //     // التحقق من أن الرصيد للسنة الحالية
    //     if ($balance->year != $currentYear) {
    //         return redirect()->route('hr.leave-balances.index')
    //             ->with('error', 'لا يمكن تعديل أرصدة الإجازات إلا للسنة الحالية (' . $currentYear . ')');
    //     }

    //     $remaining_days = $request->total_days - $request->used_days;

    //     $oldData = [
    //         'total_days' => $balance->total_days,
    //         'used_days' => $balance->used_days,
    //         'remaining_days' => $balance->remaining_days,
    //     ];

    //     $balance->update([
    //         'total_days'     => $request->total_days,
    //         'used_days'      => $request->used_days,
    //         'remaining_days' => $remaining_days,
    //     ]);

    //     LeaveBalanceLog::create([
    //         'leave_balance_id' => $balance->id,
    //         'employee_id' => $balance->employee_id,
    //         'year' => $balance->year,
    //         'user_id' => Auth::id(),
    //         'action' => 'manual_update',
    //         'old_total_days' => $oldData['total_days'],
    //         'new_total_days' => $request->total_days,
    //         'old_used_days' => $oldData['used_days'],
    //         'new_used_days' => $request->used_days,
    //         'old_remaining_days' => $oldData['remaining_days'],
    //         'new_remaining_days' => $remaining_days,
    //         'notes' => 'تم تحديث رصيد الإجازة يدوياً'
    //     ]);

    //     return redirect()->route('hr.leave-balances.index')
    //         ->with('success', 'تم تحديث رصيد الإجازة بنجاح');
    // }

    /*
    |--------------------------------------------------------------------------
    | Show Leave Balance Details
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        $balance = LeaveBalance::with('employee')->findOrFail($id);

        // جلب سجل النشاطات
        $logs = LeaveBalanceLog::where('leave_balance_id', $id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('hr.leave_balances.show', compact('balance', 'logs'));
    }
}
