<?php

namespace App\Http\Controllers\LegalAffair\PowerOfAttorney;


use App\Data\LegalAffair\PowerOfAttorney\PowerOfAttorneyData;
use App\DataTables\LegalAffairs\PowerOfAttorney\PowerOfAttorneyDataTable;
use App\Enums\LegalAffair\PowerOfAttorney\PowerOfAttorneyStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\LegalAffair\PowerOfAttorney\StorePowerAttorneyRequest;
use App\Http\Requests\LegalAffair\PowerOfAttorney\UpdatePowerAttorneyRequest;
use App\Models\OperationsCenter\Customer\Customers;
use App\Models\Hr\Employees\Employees;
use App\Models\LegalAffair\PowerOfAttorney\PowerOfAttorney;
use App\Services\LegalAffair\PowerOfAttorney\PowerOfAttorneyService;
use Illuminate\Support\Facades\Log;

class PowerOfAttorneyController extends Controller
{
    private $route = "legal-affairs.power-attorney";
    private $page  = "legal_affairs.power-attorney";

    public function __construct(private PowerOfAttorneyService $service)
    {
        $this->middleware(function ($request, $next) {
            if ($request->user()->can('كل الوكالات') || $request->user()->can('الوكالات الخاصة بي')) {
                return $next($request);
            }
            abort(403);
        })->only(['index', 'show']);

        $this->middleware('can:إضافة وكالة')->only(['create', 'store']);
        $this->middleware('can:تعديل وكالة')->only(['edit', 'update']);
        $this->middleware('can:حذف وكالة')->only(['destroy']);
    }


    public function index(PowerOfAttorneyDataTable $dataTable)
    {
        $query = PowerOfAttorney::query();

        try {

            if (auth()->user()->can('الوكالات الخاصة بي') && !auth()->user()->can('كل الوكالات')) {
                $query->where('created_by', auth()->id());
            }
            // Statistics
            $statusCounts = $query
                ->select('status')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $totalPowerAttorney         = array_sum($statusCounts);
            $activePowerAttorney        = $typeCounts[PowerOfAttorneyStatus::Active->value] ?? 0;
            $expiredPowerAttorney       = $typeCounts[PowerOfAttorneyStatus::Expired->value] ?? 0;
            $revokedPowerAttorney       = $typeCounts[PowerOfAttorneyStatus::Revoked->value] ?? 0;

            // Filters
            $PowerAttorneyStatus        = PowerOfAttorneyStatus::options();
            $customers                  = Customers::select(['id', 'name'])->get();
            $employees                  = Employees::active()->select(['id', 'name', 'nickname'])->get();


            return $dataTable->render($this->page . '.index', [

                'route'                 => $this->route,
                'totalPowerAttorney'    => $totalPowerAttorney,
                'activePowerAttorney'   => $activePowerAttorney,
                'expiredPowerAttorney'  => $expiredPowerAttorney,
                'revokedPowerAttorney'  => $revokedPowerAttorney,

                'PowerAttorneyStatus'   => $PowerAttorneyStatus,
                'customers'             => $customers,
                'employees'             => $employees,

            ]);
        } catch (\Exception $e) {
            Log::error($e);

            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    }

    public function create()
    {
        $customers = Customers::select(['id', 'name'])->get();
        $employees = Employees::active()->select('id', 'name', 'nickname')->get();

        return view($this->page . '.create', compact('customers', 'employees'));
    }


    public function store(StorePowerAttorneyRequest $request)
    {
        try {
            $dto = new PowerOfAttorneyData($request->validated());

            $this->service->create($dto, $request->file('file_attachment'));

            return redirect()->route($this->route . '.index')
                ->with('success', 'تم إضافة الوكالة بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function show(string $id)
    {
        $powerAttorney = PowerOfAttorney::findOrFail($id);
        return view($this->page . '.show', compact('powerAttorney'));
    }

    public function edit($id)
    {
        $powerAttorney = PowerOfAttorney::findOrFail($id);

        $customers      = Customers::select(['id', 'name'])->get();
        $employees      = Employees::active()->select('id', 'name', 'nickname')->get();
        return view($this->page . '.edit', compact('customers', 'employees', 'powerAttorney'));
    }


    public function update(UpdatePowerAttorneyRequest $request, $id)
    {
        try {
            $dto = new PowerOfAttorneyData($request->validated());

            $this->service->update($id, $dto, $request->file('file_attachment'));

            return redirect()->route($this->route . '.index')->with('success', 'تم تحديث الوكالة بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->service->delete($id);

            return redirect()->route($this->route . '.index')->with('success', 'تم حذف الوكالة بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /*
    |============================================================================
    |============================================================================
    |                             API
    |============================================================================
    |============================================================================
    */
    public function updateStatus($id)
    {
        try {
            $result = $this->service->updateStatus($id);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
