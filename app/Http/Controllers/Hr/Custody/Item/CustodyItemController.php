<?php

namespace App\Http\Controllers\Hr\Custody\Item;

use App\Data\Hr\Custody\Item\CustodyItemData;
use App\Data\Hr\Custody\Item\UpdateCustodyItemData;
use App\Data\Hr\Custody\Request\Assign\CustodyAssignData;
use App\Data\Hr\Custody\Request\Return\CustodyReturnData;
use App\DataTables\Hr\Custody\Item\CustodyItemDataTable;
use App\Enums\ElectronicServices\Custody\Requests\CustodyReturnStatus;
use App\Enums\Hr\Custody\Item\CustodyUseStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Hr\Custody\Item\StoreCustodyItemRequest;
use App\Http\Requests\Hr\Custody\Item\UpdateCustodyItemRequest;
use App\Http\Requests\Hr\Custody\Request\Assign\StoreAssignRequest;
use App\Http\Requests\Hr\Custody\Request\Return\StoreReturnRequest;
use App\Models\general_setting\HR\Custody\SettingsAssetCategory;
use App\Models\general_setting\HR\Custody\SettingsStorageLocation;
use App\Models\Hr\Custody\Item\CustodyItem;
use App\Services\HR\Custody\Item\CustodyItemService;
use App\Services\HR\Custody\Request\CustodyRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\FacadesLog;

class CustodyItemController extends Controller
{
    public function __construct(private CustodyItemService $service, private CustodyRequestService $requestService)
    {
        $this->middleware(function ($request, $next) {
            if ($request->user()->can('إدارة الاصول')) {
                return $next($request);
            }
            abort(403);
        })->only(['index', 'show']);

        $this->middleware('can:إضافة اصل')->only(['create', 'store']);
        $this->middleware('can:تعديل اصل')->only(['edit', 'update']);
        $this->middleware('can:حذف اصل')->only(['destroy']);
    }

    /*
    |============================================================================
    | Index
    |============================================================================
    */
    public function index(CustodyItemDataTable $dataTable, Request $request)
    {
        try {
            if ($request->ajax()) return $dataTable->ajax();

            // Statistics
            $statusCounts = CustodyItem::query()
                ->select('use_status')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('use_status')
                ->pluck('count', 'use_status')
                ->toArray();

            $totalItem          = array_sum($statusCounts);
            $availableItem      = $statusCounts[CustodyUseStatus::Available->value] ?? 0;
            $InUseItem          = $statusCounts[CustodyUseStatus::InUse->value] ?? 0;
            $MaintenanceItem    = $statusCounts[CustodyUseStatus::Maintenance->value] ?? 0;

            // Filters
            $assetCategory     = SettingsAssetCategory::select(['id', 'name'])->orderBy('id', 'desc')->get();
            $storageLocation   = SettingsStorageLocation::select(['id', 'name'])->orderBy('id', 'desc')->get();
            $statusOptions     = CustodyUseStatus::options();

            //



            return $dataTable->render('hr.custody.items.index', compact(
                // Statistics
                'totalItem',
                'availableItem',
                'InUseItem',
                'MaintenanceItem',
                // Filters
                'assetCategory',
                'storageLocation',
                'statusOptions',

            ));
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    }


    /*
    |============================================================================
    | create
    |============================================================================
    */
    public function create()
    {
        $assetCategory     = SettingsAssetCategory::where('status', 'active')->select(['id', 'name'])->orderBy('id', 'desc')->get();
        $storageLocation   = SettingsStorageLocation::where('status', 'active')->select(['id', 'name'])->orderBy('id', 'desc')->get();

        return view('hr.custody.items.create', compact(
            'assetCategory',
            'storageLocation',
        ));
    }

    /*
    |============================================================================
    | store
    |============================================================================
    */
    public function store(StoreCustodyItemRequest $request)
    {
        try {
            $dto = new CustodyItemData($request->validated());

            $this->service->createCustodyItem($dto);

            return redirect()->route('hr.custody.items.index')->with('success', 'تم إضافة الاصل بنجاح.');
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }


    /*
    |============================================================================
    | show
    |============================================================================
    */
    public function show(CustodyItem $item)
    {
        return view('hr.custody.items.show', compact('item'));
    }



    /*
    |============================================================================
    | edit
    |============================================================================
    */
    public function edit(CustodyItem $item)
    {
        $assetCategory     = SettingsAssetCategory::where('status', 'active')->select(['id', 'name'])->orderBy('id', 'desc')->get();
        $storageLocation   = SettingsStorageLocation::where('status', 'active')->select(['id', 'name'])->orderBy('id', 'desc')->get();
        $statusOptions     = CustodyUseStatus::options();

        return view('hr.custody.items.edit', compact(
            'item',
            'assetCategory',
            'storageLocation',
            'statusOptions'
        ));
    }


    /*
    |============================================================================
    | Update
    |============================================================================
    */
    public function update(UpdateCustodyItemRequest $request, CustodyItem $item)
    {
        try {
            $dto = new UpdateCustodyItemData($request->validated());

            $this->service->updateCustodyItem($item->id, $dto);

            return redirect()->route('hr.custody.items.index')->with('success', 'تم تحديث الاصل بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }


    /*
    |============================================================================
    | Delete
    |============================================================================
    */
    public function destroy(CustodyItem $item)
    {
        try {
            $this->service->deleteCustodyItem($item->id);

            return redirect()->route('hr.custody.items.index')->with('success', 'تم حذف الاصل بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }


    /*
    |============================================================================
    |============================================================================
    |                            Assign
    |============================================================================
    |============================================================================
    */
    public function assign_item(StoreAssignRequest $request)
    {
        try {
            $dto = new CustodyAssignData($request->validated());
            $this->requestService->createAssign($dto);

            return response()->json([
                'success' => true,
                'message' => 'تم إسناد الاصل للموظف بنجاح'
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إسناد الاصل: ' . $e->getMessage()
            ], 500);
        }
    }

    public function return_item(StoreReturnRequest $request)
    {
        try {
            $dto = new CustodyReturnData($request->validated());
            $this->requestService->createReturn($dto);

            return response()->json([
                'success' => true,
                'message' => 'تم إرجاع الاصل بنجاح'
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إرجاع الاصل: ' . $e->getMessage()
            ], 500);
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
                'location_id' => 'required|exists:settings_storage_locations,id',
            ]);

            // الحصول على العهد المتاحة للتصنيف والمرجعية المحددين
            $availableItems = CustodyItem::where('asset_category_id', $request->category_id)
                ->where('storage_location_id', $request->location_id)
                ->where('use_status', CustodyUseStatus::Available)
                ->select(['id', 'name', 'serial_number', 'use_status'])
                ->get();

            // إذا كان هناك عهدة حالية، تحقق إذا كانت تنتمي لنفس التصنيف والمرجعية
            if ($request->has('include_current') && $request->include_current) {
                $currentItem = CustodyItem::where('id', $request->include_current)
                    ->where('asset_category_id', $request->category_id) // نفس التصنيف
                    ->where('storage_location_id', $request->location_id) // نفس المرجعية
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
                'location_id' => $request->location_id,
                'include_current' => $request->include_current,
                'items' => $availableItems->toArray()
            ]);

            return response()->json([
                'success' => true,
                'data' => $availableItems->values(),
                'debug' => [
                    'total_items' => $availableItems->count(),
                    'category_id' => $request->category_id,
                    'location_id' => $request->location_id,
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
