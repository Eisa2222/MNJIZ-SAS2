<?php

namespace App\Http\Controllers\ElectronicServices\PurchaseRequests;

use App\Data\ElectronicServices\PurchaseRequests\EmployeePurchaseRequestsData;
use App\DataTables\ElectronicServices\PurchaseRequests\EmployeePurchaseRequestDataTable;
use App\Enums\ElectronicServices\PurchaseRequests\PurchaseRequestsStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ElectronicServices\PurchaseRequests\StorePurchaseRequest;
use App\Models\ElectronicServices\PurchaseRequests\PurchaseRequest;
use App\Models\general_setting\SettingsPurchaseCategory;
use App\Services\ElectronicServices\PurchaseRequests\EmployeePurchaseRequestsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EmployeePurchaseRequestController extends Controller
{
    private $route = "account.electronic-services.purchase-requests";
    private $page   = "electronic_services.purchase_requests";

    public function __construct(private EmployeePurchaseRequestsService $service)
    {
        $this->middleware('can:طلبات المشتريات الخاصة بي')->only(['index']);
        $this->middleware('can:إضافة طلب مشتريات')->only(['create', 'store']);
        $this->middleware('can:تعديل طلب المشتريات')->only(['edit', 'update']);
        $this->middleware('can:حذف طلب المشتريات')->only(['destroy']);
    }


    public function index(EmployeePurchaseRequestDataTable $dataTable)
    {
        try {

            // Statistics
            $statusCounts = PurchaseRequest::where('employee_id', Auth::user()->employee->id)
                ->select('status')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $totalRequests = array_sum($statusCounts);
            $pendingRequests = $statusCounts[PurchaseRequestsStatus::Pending->value] ?? 0;
            $approvedRequests = $statusCounts[PurchaseRequestsStatus::Approved->value] ?? 0;
            $rejectedRequests = $statusCounts[PurchaseRequestsStatus::Rejected->value] ?? 0;

            // Filters
            $requestCategory = SettingsPurchaseCategory::select(['id', 'name'])->get();
            $requestStatus   = PurchaseRequestsStatus::options();



            return $dataTable->render($this->page . '.index', compact(
                // Statistics
                'totalRequests',
                'pendingRequests',
                'approvedRequests',
                'rejectedRequests',
                // Filters
                'requestCategory',
                'requestStatus'
            ));
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    }


    public function create()
    {
        $purchase_category = SettingsPurchaseCategory::where('status', 'active')->select(['id', 'name'])->get();
        return view('electronic_services.purchase_requests.create', compact('purchase_category'));
    }


    public function store(StorePurchaseRequest $request)
    {
        try {
            $dto = new EmployeePurchaseRequestsData($request->validated());

            $this->service->createRequest($dto);

            return redirect()->route($this->route . '.index')->with('success', 'تم إضافة الطلب بنجاح.');
        } catch (\Exception $e) {

            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }


    public function show(PurchaseRequest $purchaseRequest)
    {
        return view('electronic_services.purchase_requests.show', compact('purchaseRequest'));
    }


    public function edit(PurchaseRequest $purchaseRequest)
    {
        if (!$purchaseRequest->status->canEditOrDelete()) {
            abort(404);
        }

        $purchase_category = SettingsPurchaseCategory::where('status', 'active')->select(['id', 'name'])->get();
        return view('electronic_services.purchase_requests.edit', compact('purchase_category', 'purchaseRequest'));
    }


    public function update(StorePurchaseRequest $request, PurchaseRequest $purchaseRequest)
    {
        try {
            $dto = new EmployeePurchaseRequestsData($request->validated());

            $this->service->updateRequest($purchaseRequest->id, $dto);

            return redirect()->route($this->route . '.index')->with('success', 'تم تحديث الطلب بنجاح.');
        } catch (\Exception $e) {

            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }


    public function destroy(PurchaseRequest $purchaseRequest)
    {
        if (!$purchaseRequest->status->canEditOrDelete()) {
            abort(404);
        }

        $purchaseRequest->delete();
        return redirect()->back()->with('success', 'تم حذف الطلب بنجاح.');
    }
}
