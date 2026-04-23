<?php

namespace App\Http\Controllers\Qoyod\Vendors;

use App\DataTables\Qoyod\Vendors\QVendorsDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Qoyod\Vendors\QVendorsRequest;
use App\Services\Qoyod\Contracts\Resources\VendorResourceInterface;
use App\Services\Qoyod\Exceptions\QoyodRequestException;
use App\Services\Qoyod\Presenters\Purchases\Bills\BillPresenter;
use App\Services\Qoyod\Presenters\Receipts\ReceiptsPresenter;
use Carbon\Carbon;

class QVendorsController extends Controller
{
    /**
     * ============================================================================ 
     *   @param  VendorResourceInterface  $vendors
     * ============================================================================
     */
    public function __construct(
        private VendorResourceInterface     $vendors,
        private ReceiptsPresenter           $receiptsPresenter,
        private BillPresenter               $billPresenter
    ) {
        $this->middleware('can:صلاحيات منصة قيود');
    }


    /*
    |============================================================================
    | index
    |============================================================================
    */
    public function index(QVendorsDataTable $dataTable)
    {
        return $dataTable->render('qoyod.vendors.index');
    }

    /*
    |============================================================================
    | create
    |============================================================================
    */
    public function create()
    {
        return view('qoyod.vendors.create');
    }


    /*
    |============================================================================
    | store
    |============================================================================
    */
    public function store(QVendorsRequest $request)
    {
        //Validation
        $data = $request->validated();

        try {
            $this->vendors->create($data);
            return redirect()->route('qoyod.vendors.index')->with('success', 'تم إضافة المورد  بنجاح');
        } catch (QoyodRequestException $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'api_error' => 'فشل في إضافة المورد، الرجاء المحاولة مرة أخرى. التفاصيل: ' . $e->getMessage()
                ]);
        }
    }



    /*
    |============================================================================
    | show
    |============================================================================
    */
    public function show($id)
    {

        try {
            $resp        = $this->vendors->find($id);
            $vendor      = $resp['contact'] ?? null;
            // فواتير المشتريات
            $bills       = $this->billPresenter->getBillsByVendor($id);
            // $count         = $bills->count();                                   // e.g. 271
            // $totalAmount   = $bills->sum(fn($b) => (float) $b['total']);         // e.g. 54,978.76
            // $totalPaid     = $bills->sum(fn($b) => (float) $b['paid_amount']);   // sum of all “paid_amount” values

            // // 2) Only “Approved” bills count toward any outstanding due/overdue
            // $approved      = $bills->filter(fn($b) => $b['status'] === 'Approved');
            // $totalDue      = $approved->sum(fn($b) => (float) $b['due_amount']); // e.g. 0.00
            // $overdueAmount = $approved
            //     ->filter(fn($b) => Carbon::parse($b['due_date'])->lt(Carbon::today()))
            //     ->sum(fn($b) => (float) $b['due_amount']);                     // e.g. 0.00

            // // 3) Closing balance: total invoiced minus total paid
            // $closingBalance = $totalAmount - $totalPaid;                       // e.g. 0.00

            // $receipts       = $this->receiptsPresenter->getReceiptsByVendorId($id);

            // $countReceipts     = $receipts->count();                                // total number of receipts
            // $totalReceived     = $receipts->sum(fn($r) => (float) $r['amount']);    // sum of all receipt amounts

            // // 3) Calculate total allocated across all receipts
            // $totalAllocated = $receipts
            //     ->flatMap(fn($r) => $r['allocations'])                              // flatten all allocations
            //     ->sum(fn($a) => (float) $a['amount']);                              // sum of all allocated amounts

            // // 4) Unallocated (available) amount
            // $totalUnallocated  = $totalReceived - $totalAllocated;

            // // 5) Optionally, count receipts with any allocations vs none
            // $allocatedCount    = $receipts
            //     ->filter(fn($r) => collect($r['allocations'])->isNotEmpty())
            //     ->count();
            // $unallocatedCount  = $countReceipts - $allocatedCount;

            // dd([
            //     'count' => $bills,
            // ]);

            if ($vendor == null) {
                abort(404);
            }
        } catch (\Exception $e) {
            abort(404, 'المنتج غير موجود');
        }

        return view('qoyod.vendors.show', compact('vendor', 'bills'));
    }


    /*
    |============================================================================
    | edit
    |============================================================================
    */
    public function edit(string $id)
    {
        try {
            $resp   = $this->vendors->find($id);
            $vendor = $resp['contact'] ?? null;

            return view('qoyod.vendors.edit', compact('vendor'));
        } catch (QoyodRequestException $e) {
            return redirect()
                ->route('qoyod.vendors.index')
                ->withErrors(['api_error' => $e->getMessage()]);
        }
    }



    /*
    |============================================================================
    | update
    |============================================================================
    */
    public function update(QVendorsRequest $request, string $id)
    {
        //Validation
        $data = $request->validated();

        try {
            $this->vendors->update($id, $data);
            return redirect()->route('qoyod.vendors.index')->with('success', 'تم تحديث بيانات المورد بنجاح');
        } catch (QoyodRequestException $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'api_error' => 'فشل في تحديث بيانات المورد: ' . $e->getMessage()
                ]);
        }
    }



    /*
    |============================================================================
    |============================================================================
    |                             other method
    |============================================================================
    |============================================================================
    */
    public function getVendors()
    {
        $allVendors = $this->vendors->all();
        $vendors = $allVendors['vendors'] ?? null;
        return $vendors;
    }
}
