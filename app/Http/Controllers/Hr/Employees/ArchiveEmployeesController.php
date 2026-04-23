<?php

namespace App\Http\Controllers\Hr\Employees;

use App\DataTables\Hr\Employee\ArchiveEmployeesDataTable;
use App\Http\Controllers\Controller;
use App\Models\general_setting\SettingsHrStatus;
use App\Models\Hr\Employees\Employees;
use App\Services\HR\Employee\EmployeeService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;


class ArchiveEmployeesController extends Controller
{
    private $route = "hr.employees.archive";
    private $page = "hr.employees.archive";

    public function __construct(private EmployeeService $employeeService)
    {
        $this->middleware(function ($request, $next) {
            if ($request->user()->can('كل الموظفين')) {
                return $next($request);
            }
            abort(403);
        })->only(['index', 'show']);

        $this->middleware('can:إضافة موظف')->only(['create', 'store']);
        $this->middleware('can:تعديل موظف')->only(['edit', 'update']);
        $this->middleware('can:حذف موظف')->only(['destroy']);
    }


    /*
    |============================================================================
    | Index
    |============================================================================
    */
    public function index(ArchiveEmployeesDataTable $dataTable)
    {
        try {

            $statusCounts = Employees::with('user.roles')->whereHas('user', function ($query) {
                $query->where('status', 'inactive');
            })->select('hr_status_id')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('hr_status_id')
                ->pluck('count', 'hr_status_id')
                ->toArray();

            $totalEmployees                 =  array_sum($statusCounts);
            $unavailableEmployees           = $statusCounts[4] ?? 0;
            $availableEmployees             = $statusCounts[3] ?? 0;
            $partiallyAvailableEmployees    = $statusCounts[2] ?? 0;
            $currentEmployees               = $statusCounts[1] ?? 0;

            // Filters
            $hrStatus   = SettingsHrStatus::select('id', 'name')->get();
            $roles      = Role::select('id', 'name')->get();

            return $dataTable->render($this->page . '.index', compact(
                'totalEmployees',
                'unavailableEmployees',
                'availableEmployees',
                'partiallyAvailableEmployees',
                'currentEmployees',


                'hrStatus',
                'roles',
            ));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    }


    public function restore($employeeId)
    {
        try {
            $this->employeeService->restore($employeeId);
            return response()->json(['success' => 'تم استعادة الموظف من الأرشيف'], 200);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage() ?: 'حدث خطأ ما'], 500);
        }
    }
}
