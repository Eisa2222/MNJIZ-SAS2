<?php

namespace App\Http\Controllers\Hr\Alerts;


use App\DataTables\Hr\Alerts\AlertsDataTable;
use App\Enums\Hr\Alert\AlertStatus;
use App\Enums\Hr\Alert\AlertType;
use App\Http\Controllers\Controller;
use App\Models\Hr\Alert\Alert;
use App\Models\Hr\Employees\Employees;
use App\Services\HR\Alerts\AlertService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AlertController extends Controller
{
    private $page = "hr.alerts";


    public function __construct()
    {
        $this->middleware('can:تنبيهات التجديدات');
    }

    public function index(AlertsDataTable $dataTable, Request $request)
    {
        try {
            // Statistics
            $statusCounts = Alert::query()
                ->select('status')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $totalAlert     = array_sum($statusCounts);
            $newAlert       = $statusCounts[AlertStatus::New->value] ?? 0;
            $resolvedAlert  = $statusCounts[AlertStatus::Resolved->value] ?? 0;

            // // Filters
            $employees      = Employees::active()->select('id', 'name', 'nickname')->get();
            $alertStatus    = AlertStatus::options();
            $alertType      = AlertType::options();

            return $dataTable->render($this->page . '.index', compact(
                // Statistics
                'totalAlert',
                'newAlert',
                'resolvedAlert',
                // Filters
                'employees',
                'alertStatus',
                'alertType'
            ));
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    }


    public function toggleStatus($id)
    {
        try {
            $alert = Alert::findOrFail($id);
            $alert->status = AlertStatus::Resolved;
            $alert->updated_by = Auth()->user()->employee->id;
            $alert->save();

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث الحالة ',
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء التحديث'
            ], 500);
        }
    }
}
