<?php

namespace App\Http\Controllers\Hr\Companypolicy;

use App\Data\Hr\Companypolicy\CompanypolicyData;
use App\Data\Hr\Companypolicy\CompanypolicyUpdateData;
use App\DataTables\Hr\CompanyPolicy\CompanyPolicyDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Hr\CompanyPolicy\CompanyPolicyRequest;
use App\Http\Requests\Hr\CompanyPolicy\CompanyPolicyUpdateRequest;
use App\Models\Hr\CompanyPolicy\CompanyPolicy;
use App\Services\HR\CompanyPolicy\CompanyPolicyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CompanypolicyController extends Controller
{
    private $route = "hr.company-policy";
    private $page  = "hr.company_policy";


    public function __construct(private CompanyPolicyService $service)
    {

        $this->middleware('can:اللوائح و السياسات')->only(['index', 'show']);
        $this->middleware('can:إضافة اللوائح و السياسات')->only(['create', 'store']);
        $this->middleware('can:تعديل اللوائح و السياسات')->only(['edit', 'update']);
        $this->middleware('can:حذف اللوائح و السياسات')->only(['destroy']);
    }


    public function index(CompanyPolicyDataTable $dataTable, Request $request)
    {
        try {
            // Statistics
            $mandatoryCounts = CompanyPolicy::query()
                ->select('is_mandatory')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('is_mandatory')
                ->pluck('count', 'is_mandatory')
                ->toArray();

            $totalDoc       = array_sum($mandatoryCounts);
            $isMandatory    = $mandatoryCounts[1] ?? 0;  // true = 1
            $notMandatory   = $mandatoryCounts[0] ?? 0;  // false = 0

            // // // Filters
            // $employees      = Employees::active()->select('id', 'name', 'nickname')->get();
            // $alertStatus    = AlertStatus::options();
            // $alertType      = AlertType::options();

            return $dataTable->render($this->page . '.index', compact(
                // Statistics
                'totalDoc',
                'isMandatory',
                'notMandatory',
                // Filters
                // 'employees',
                // 'alertStatus',
                // 'alertType'
            ));
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    }


    public function create()
    {
        return view($this->page . '.create');
    }


    public function store(CompanyPolicyRequest $request)
    {
        try {
            $dto = new CompanypolicyData($request->validated());

            $this->service->create($dto);

            return redirect()->route($this->route . '.index')->with('success', 'تمت الاضافة بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }


    public function edit(CompanyPolicy $company_policy)
    {
        return view($this->page . '.edit', compact('company_policy'));
    }


    public function update(CompanyPolicyUpdateRequest $request, int $id)
    {
        try {
            $dto = new CompanypolicyUpdateData($request->validated());

            $this->service->update($id, $dto);

            return redirect()->route($this->route . '.index')->with('success', 'تمت الاضافة بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }


    public function destroy(CompanyPolicy $company_policy)
    {
        try {
            $this->service->delete($company_policy->id);

            return redirect()->route($this->route . '.index')->with('success', 'تم الحذف بنجاح.');
        } catch (\Exception $e) {
            Log::error('Company Doc Delete Error: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
}
