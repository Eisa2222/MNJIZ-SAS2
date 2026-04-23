<?php

namespace App\Http\Controllers\Hr\Custody\Request;

use App\Data\Hr\Custody\Request\Assign\CustodyAssignData;
use App\Data\Hr\Custody\Request\Return\CustodyReturnData;
use App\DataTables\Hr\Custody\Request\CustodyRequestDataTable;
use App\Enums\ElectronicServices\Custody\Requests\CustodyRequestStatus;
use App\Enums\ElectronicServices\Custody\Requests\CustodyRequestType;
use App\Enums\ElectronicServices\Custody\Requests\CustodyReturnStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Hr\Custody\Request\Assign\StoreAssignRequest;
use App\Http\Requests\Hr\Custody\Request\Return\StoreReturnRequest;
use App\Models\ElectronicServices\Custody\Request\CustodyRequest;
use App\Models\general_setting\HR\Custody\SettingsAssetCategory;
use App\Models\general_setting\HR\Custody\SettingsStorageLocation;
use App\Models\Hr\Custody\Item\CustodyItem;
use App\Models\Hr\Employees\Employees;
use App\Services\HR\Custody\Request\CustodyRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CustodyRequestController extends Controller
{
    private $route = "hr.custody.requests";


    public function __construct(private CustodyRequestService $service)
    {
        $this->middleware('can:طلبات العهد')->only(['index', 'show']);
        $this->middleware('can:إضافة عهدة')->only(['assign_create', 'return_create', 'assign_store', 'return_store']);
        $this->middleware('can:تعديل عهدة')->only(['assign_edit', 'return_edit', 'assign_update', 'return_update']);
        $this->middleware('can:حذف عهدة')->only(['destroy']);
    }
    /*
    |============================================================================
    | Index
    |============================================================================
    */
    public function index(CustodyRequestDataTable $dataTable, Request $request)
    {
        try {
            if ($request->ajax()) return $dataTable->ajax();

            $statusCounts = CustodyRequest::query()
                ->select('status')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $totalRequests = array_sum($statusCounts);
            $pendingRequests = $statusCounts[CustodyRequestStatus::Pending->value] ?? 0;
            $approvedRequests = $statusCounts[CustodyRequestStatus::Approved->value] ?? 0;
            $rejectedRequests = $statusCounts[CustodyRequestStatus::Rejected->value] ?? 0;

            // Filters
            $employees       = Employees::active()->select('id', 'name', 'nickname')->get();
            $custodyItems    = CustodyItem::select(['id', 'name'])->get();
            $requestTypes    = CustodyRequestType::options();
            $requestStatus   = CustodyRequestStatus::options();


            return $dataTable->render('hr.custody.requests.index', compact(
                // Statistics
                'totalRequests',
                'pendingRequests',
                'approvedRequests',
                'rejectedRequests',
                // Filters
                'employees',
                'custodyItems',
                'requestTypes',
                'requestStatus'
            ));
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    }


    /*
    |============================================================================
    | show
    |============================================================================
    */
    public function show(CustodyRequest $request)
    {
        $request->load([
            'item.assetCategory',
            'item.storageLocation',
            'employee',
            'createdBy',
            'updatedBy',
            'approvalRequest.requestLevels.employee',
        ]);

        // -- Approval --//
        $approvalStages = $request->approvalRequest ? $request->approvalRequest->getApprovalStages() : [];
        $isAutoApproved = empty($approvalStages) && ($request->status === CustodyRequestStatus::Approved);
        // -- Approval --//

        return view('hr.custody.requests.show', compact('request', 'approvalStages', 'isAutoApproved'));
    }


    /*
    |============================================================================
    |============================================================================
    |                            Assign
    |============================================================================
    |============================================================================
    */
    public function assign_create()
    {
        $assetCategory   = SettingsAssetCategory::where('status', 'active')->select(['id', 'name'])->orderBy('id', 'desc')->get();
        $storageLocation = SettingsStorageLocation::where('status', 'active')->select(['id', 'name'])->orderBy('id', 'desc')->get();
        $employees       = Employees::active()->select('id', 'name', 'nickname')->get();

        return view('hr.custody.requests.assign.create', compact(
            'assetCategory',
            'storageLocation',
            'employees'
        ));
    }


    public function assign_store(StoreAssignRequest $request)
    {
        try {
            $dto = new CustodyAssignData($request->validated());

            $this->service->createAssign($dto);

            return redirect()->route($this->route . '.index')->with('success', 'تم إضافة طلب العهدة بنجاح.');
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function assign_edit(int $id)
    {
        $custody_request = CustodyRequest::assign()->pending()->where('id', $id)->firstOrFail();

        if (!$custody_request->status->canEditOrDelete()) {
            return redirect()->route($this->route . '.index')->with('error', 'لا يمكن تعديل الطلب بعد اعتماده أو رفضه.');
        }

        $assetCategory   = SettingsAssetCategory::where('status', 'active')->select(['id', 'name'])->orderBy('id', 'desc')->get();
        $storageLocation = SettingsStorageLocation::where('status', 'active')->select(['id', 'name'])->orderBy('id', 'desc')->get();
        $employees       = Employees::active()->select('id', 'name', 'nickname')->get();


        return view('hr.custody.requests.assign.edit', compact(
            'custody_request',
            'assetCategory',
            'storageLocation',
            'employees'
        ));
    }

    public function assign_update(StoreAssignRequest $request, int $id)
    {
        try {
            $dto = new CustodyAssignData($request->validated());

            $this->service->updateAssign($id, $dto);

            return redirect()->route($this->route . '.index')->with('success', 'تم تحديث طلب العهدة بنجاح.');
        } catch (\Throwable $e) {
            Log::error($e);
            return back()->withInput()->with('error', $e->getMessage());
        }
    }


    /*
    |============================================================================
    |============================================================================
    |                             Return
    |============================================================================
    |============================================================================
    */
    public function return_create()
    {
        $employees       = Employees::active()->select('id', 'name', 'nickname')->get();
        $statusOptions   = CustodyReturnStatus::options();


        return view('hr.custody.requests.return.create', compact('employees', 'statusOptions'));
    }

    public function return_store(StoreReturnRequest $request)
    {
        try {
            $dto = new CustodyReturnData($request->validated());

            $this->service->createReturn($dto);

            return redirect()->route($this->route . '.index')->with('success', 'تم إضافة طلب إرجاع عهدة بنجاح.');
        } catch (\Throwable $e) {
            Log::error($e);
            return back()->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function return_edit(int $id)
    {
        $custody_request = CustodyRequest::return()->pending()->where('id', $id)->firstOrFail();

        if (!$custody_request->status->canEditOrDelete()) {
            return redirect()->route($this->route . '.index')->with('error', 'لا يمكن تعديل الطلب بعد اعتماده أو رفضه.');
        }


        $employees = Employees::active()->select('id', 'name', 'nickname')->get();

        return view('hr.custody.requests.return.edit', compact('custody_request', 'employees'));
    }

    public function return_update(StoreReturnRequest $request, int $id)
    {
        try {
            $dto = new CustodyReturnData($request->validated());

            $this->service->updateReturn($id, $dto);

            return redirect()->route($this->route . '.index')->with('success', 'تم تحديث طلب إرجاع العهدة بنجاح.');
        } catch (\Throwable $e) {
            Log::error($e);
            return back()->withInput()->with('error', $e->getMessage());
        }
    }



    public function destroy(int $id)
    {
        try {

            $this->service->delete($id);

            return redirect()->route($this->route . '.index')->with('success', 'تم حذف الطلب  بنجاح.');
        } catch (\Throwable $e) {
            Log::error($e);
            return back()->withInput()->with('error', $e->getMessage());
        }
    }


    /*
    |============================================================================
    |============================================================================
    |                            API
    |============================================================================
    |============================================================================
    */
    public function approvedRequests(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
        ]);

        $assignments = CustodyRequest::assign()
            ->approved()
            ->where('employee_id', $request->employee_id)
            ->with('item') // لتحميل علاقة العنصر
            ->get()
            ->map(fn($r) => [
                'id'   => $r->id,
                'text' => $r->item->name . ' (' . $r->item->serial_number . ')'
            ]);

        return response()->json($assignments);
    }
}
