<?php

namespace App\Http\Controllers\SystemAdministration\ActivityLog;

use App\DataTables\SystemAdministration\ActivityLog\ActivityLogDataTable;
use App\Http\Controllers\Controller;
use App\Models\Hr\Employees\Employees;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\Facades\DataTables;

class ActivityLogController extends Controller
{

    public function __construct()
    {
        $this->middleware('can:سجل النشاطات')->only([
            'index',
            'show',
        ]);
    }


    /*
    |============================================================================
    | Index
    |============================================================================
    */
    public function index(ActivityLogDataTable $dataTable)
    {
        try {
            $employees = Employees::select('id', 'name', 'nickname', 'user_id')->get();

            return $dataTable->render('system_administration.activity_logs.index', compact(
                // Filters
                'employees',
            ));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | index
    |--------------------------------------------------------------------------
    */
    public function index_old(Request $request)
    {
        if ($request->ajax()) {
            $activities = Activity::with('causer', 'subject')
                ->select(['id', 'log_name', 'description', 'subject_id', 'subject_type', 'causer_id', 'causer_type', 'properties', 'created_at']);

            // تطبيق الفلاتر إذا كانت موجودة
            if ($request->user_id) {
                $activities->where('causer_id', $request->user_id);
            }

            if ($request->date_from) {
                $activities->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->date_to) {
                $activities->whereDate('created_at', '<=', $request->date_to);
            }

            return DataTables::of($activities)
                ->addIndexColumn()
                ->addColumn('causer', function ($row) {
                    // return $row->causer ? $row->causer->name : '-';
                })
                ->editColumn('created_at', function ($row) {
                    return \Carbon\Carbon::parse($row->created_at)
                        ->locale('ar') // تعيين اللغة العربية
                        ->diffForHumans(); // تحويل التاريخ إلى صيغة نسبية
                })
                ->addColumn('action', function ($row) {
                    // return '<button class="btn btn-sm btn-primary view-details" data-id="' . $row->id . '">عرض التفاصيل</button>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $employees = Employees::select('id', 'name', 'nickname', 'user_id')->get();

        return view('system_administration.activity_logs.index', compact('employees'));
    }


    /*
    |--------------------------------------------------------------------------
    | show
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        $activity = Activity::findOrFail($id);
        $html = view('system_administration.activity_logs.details', compact('activity'))->render();

        return response()->json(['html' => $html]);
    }
}
