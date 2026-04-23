<?php

namespace App\Http\Controllers\ElectronicServices\CustodyRequests;

use App\Data\ElectronicServices\Custody\Request\Assign\EmployeeCustodyAssignData;
use App\Data\ElectronicServices\CustodyRequests\EmployeeCustodyRequestData;
use App\Data\ElectronicServices\Custody\Request\Return\EmployeeCustodyReturnData;
use App\DataTables\ElectronicServices\CustodyRequests\EmployeeCustodyRequestDataTable;
use App\Enums\ElectronicServices\Custody\Requests\CustodyRequestStatus;
use App\Enums\ElectronicServices\Custody\Requests\CustodyRequestType;
use App\Enums\ElectronicServices\Custody\Requests\CustodyReturnStatus;
use App\Enums\Hr\Custody\Item\CustodyUseStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ElectronicServices\CustodyRequests\Assign\StoreAssignRequest;
use App\Http\Requests\ElectronicServices\CustodyRequests\Return\StoreReturnRequest;
use App\Http\Requests\ElectronicServices\CustodyRequests\CustodyRequests;
use App\Models\ElectronicServices\Custody\Request\CustodyRequest;
use App\Models\general_setting\HR\Custody\SettingsAssetCategory;
use App\Models\general_setting\HR\Custody\SettingsStorageLocation;
use App\Models\Hr\Custody\Item\CustodyItem;
use App\Services\ElectronicServices\Custody\Request\EmployeeCustodyRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EmployeeCustodyRequestController extends Controller
{
    private $route = "account.electronic-services.custody-requests";

    public function __construct(private EmployeeCustodyRequestService $service) {}

    public function index(EmployeeCustodyRequestDataTable $dataTable)
    {
        try {

            // Statistics
            $statusCounts = CustodyRequest::where('employee_id', Auth::user()->employee->id)
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
            $custodyItems    = CustodyItem::select(['id', 'name'])->get();
            $requestTypes    = CustodyRequestType::options();
            $requestStatus   = CustodyRequestStatus::options();


            return $dataTable->render('electronic_services.custody_requests.index', compact(
                // Statistics
                'totalRequests',
                'pendingRequests',
                'approvedRequests',
                'rejectedRequests',
                // Filters
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
    |============================================================================
    |                            Assign
    |============================================================================
    |============================================================================
    */
    public function assign_create()
    {
        $assetCategory   = SettingsAssetCategory::where('status', 'active')->select(['id', 'name'])->orderBy('id', 'desc')->get();
        $storageLocation = SettingsStorageLocation::where('status', 'active')->select(['id', 'name'])->orderBy('id', 'desc')->get();

        return view('electronic_services.custody_requests.assign.create', compact('assetCategory', 'storageLocation'));
    }

    public function assign_store(StoreAssignRequest $request)
    {
        try {
            $dto = new EmployeeCustodyAssignData($request->validated());

            $this->service->createAssign($dto);

            return redirect()->route($this->route . '.index')->with('success', 'تم إضافة طلب العهدة بنجاح.');
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function assign_edit(int $id)
    {
        $custody_request = CustodyRequest::assign()->pending()->where('id', $id)->forEmployee(Auth::user()->employee->id)->findOrFail($id);

        if (!$custody_request->status->canEditOrDelete()) {
            return redirect()->route($this->route . '.index')->with('error', 'لا يمكن تعديل الطلب بعد اعتماده أو رفضه.');
        }

        $assetCategory   = SettingsAssetCategory::where('status', 'active')->select(['id', 'name'])->orderBy('id', 'desc')->get();
        $storageLocation = SettingsStorageLocation::where('status', 'active')->select(['id', 'name'])->orderBy('id', 'desc')->get();

        return view('electronic_services.custody_requests.assign.edit', compact('custody_request', 'assetCategory', 'storageLocation'));
    }

    public function assign_update(StoreAssignRequest $request, int $id)
    {
        try {
            $dto = new EmployeeCustodyAssignData($request->validated());

            $this->service->updateAssign($id, $dto);

            return redirect()->route($this->route . '.index')->with('success', 'تم تحديث طلب العهدة بنجاح.');
        } catch (\Throwable $e) {
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
        $assignRequests = CustodyRequest::assign()->approved()->forEmployee(Auth::user()->employee->id)->get();

        if ($assignRequests->isEmpty()) {
            return redirect()->route($this->route . '.index')->with('error', 'لا يوجد لديك عهدة معتمدة لإرجاعها.');
        }

        $statusOptions     = CustodyReturnStatus::options();

        return view('electronic_services.custody_requests.return.create', compact('assignRequests', 'statusOptions'));
    }

    public function return_store(StoreReturnRequest $request)
    {
        try {
            $dto = new EmployeeCustodyReturnData($request->validated());

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
        $custody_request = CustodyRequest::return()->pending()->where('id', $id)->forEmployee(Auth::user()->employee->id)->findOrFail($id);

        if (!$custody_request->status->canEditOrDelete()) {
            return redirect()->route($this->route . '.index')->with('error', 'لا يمكن تعديل الطلب بعد اعتماده أو رفضه.');
        }

        $assignRequests = CustodyRequest::assign()->approved()->forEmployee(Auth::user()->employee->id)->get();
        $statusOptions  = CustodyReturnStatus::options();

        return view('electronic_services.custody_requests.return.edit', compact('custody_request', 'assignRequests', 'statusOptions'));
    }

    public function return_update(StoreReturnRequest $request, int $id)
    {
        try {
            $dto = new EmployeeCustodyReturnData($request->validated());

            $this->service->updateReturn($id, $dto);

            return redirect()->route($this->route . '.index')->with('success', 'تم تحديث طلب إرجاع العهدة بنجاح.');
        } catch (\Throwable $e) {
            Log::error($e);
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    //show
    public function show(CustodyRequest $custody_request)
    {
        $custody_request->load([
            'item.assetCategory',
            'createdBy',
            'updatedBy',
            'approvalRequest.requestLevels.employee',
        ]);

        // -- Approval --//
        $approvalStages = $custody_request->approvalRequest ? $custody_request->approvalRequest->getApprovalStages() : [];
        $isAutoApproved = empty($approvalStages) && ($custody_request->status === CustodyRequestStatus::Approved);
        // -- Approval --//
        return view('electronic_services.custody_requests.show', compact(
            'custody_request',
            'approvalStages',
            'isAutoApproved'
        ));
    }



    public function destroy(CustodyRequest $custody_request)
    {
        try {
            $this->service->delete($custody_request->id);

            return redirect()->route($this->route . '.index')->with('success', 'تم حذف الطلب بنجاح');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }


    /*
    |============================================================================
    |============================================================================
    |                               API
    |============================================================================
    |============================================================================
    */
    public function filter(Request $request)
    {
        try {
            $request->validate([
                'category_id' => 'required|exists:settings_asset_categories,id',
            ]);

            // الحصول على العهد المتاحة للتصنيف والمرجعية المحددين
            $availableItems = CustodyItem::where('asset_category_id', $request->category_id)
                ->where('use_status', CustodyUseStatus::Available)
                ->select(['id', 'name', 'serial_number', 'use_status'])
                ->get();

            // إذا كان هناك عهدة حالية، تحقق إذا كانت تنتمي لنفس التصنيف والمرجعية
            if ($request->has('include_current') && $request->include_current) {
                $currentItem = CustodyItem::where('id', $request->include_current)
                    ->where('asset_category_id', $request->category_id) // نفس التصنيف
                    ->select(['id', 'name', 'serial_number', 'use_status'])
                    ->first();

                // إذا وُجدت العهدة الحالية وكانت تنتمي لنفس التصنيف والمرجعية
                if ($currentItem) {
                    // تحقق إذا كانت موجودة بالفعل في النتائج (إذا كانت متاحة)
                    $exists = $availableItems->where('id', $currentItem->id)->count() > 0;

                    // إذا لم تكن موجودة (أي أنها مستخدمة)، أضفها
                    if (!$exists) {
                        $availableItems->push($currentItem);
                    }
                }
            }

            Log::info('Final items:', [
                'available_count' => $availableItems->count(),
                'category_id' => $request->category_id,
                'include_current' => $request->include_current,
                'items' => $availableItems->toArray()
            ]);

            return response()->json([
                'success' => true,
                'data' => $availableItems->values(),
                'debug' => [
                    'total_items' => $availableItems->count(),
                    'category_id' => $request->category_id,
                    'include_current' => $request->include_current,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Filter error:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في جلب العهد: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }
}
