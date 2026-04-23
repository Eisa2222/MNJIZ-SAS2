<?php

namespace App\Http\Controllers\Hr\Purchase;

use App\DataTables\Hr\Purchase\PurchaseRequestDataTable;
use App\Enums\ElectronicServices\PurchaseRequests\PurchaseRequestsStatus;
use App\Http\Controllers\Controller;
use App\Models\ElectronicServices\PurchaseRequests\PurchaseRequest;
use App\Models\general_setting\SettingsPurchaseCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PurchaseRequestController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | constructor
    |--------------------------------------------------------------------------
    */
    public function __construct()
    {
        $this->middleware('can:طلبات المشتريات')->only(['index']);
        $this->middleware('can:تغيير حالة الطلب')->only(['updateStatus']);
    }


    public function index(PurchaseRequestDataTable $dataTable)
    {
        try {

            // Statistics
            $statusCounts = PurchaseRequest::select('status')
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



            return $dataTable->render('purchasing_center.purchase_requests.index', compact(
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



    /*
    |--------------------------------------------------------------------------
    | change status
    |--------------------------------------------------------------------------
    */
    public function updateStatus(Request $request)
    {
        try {
            $purchaseRequest = PurchaseRequest::findOrFail($request->request_id);
            $purchaseRequest->status = $request->status;

            // تخزين سبب الرفض
            if ($request->status === 'rejected' && !empty($request->rejection_reason)) {
                $purchaseRequest->rejection_reason = $request->rejection_reason;
            } else {
                $purchaseRequest->rejection_reason = null;
            }

            $purchaseRequest->processed_at = Carbon::now();
            $purchaseRequest->save();



            return response()->json([
                'success' => true,
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
