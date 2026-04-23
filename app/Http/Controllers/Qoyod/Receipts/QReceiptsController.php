<?php

namespace App\Http\Controllers\Qoyod\Receipts;

use App\DataTables\Qoyod\Receipts\QReceiptsDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Qoyod\Receipts\QReceiptsRequest;
use App\Services\Qoyod\Contracts\Resources\InventoryResourceInterface;
use App\Services\Qoyod\Contracts\Resources\ReceiptResourceInterface;
use App\Services\Qoyod\Exceptions\QoyodRequestException;
use App\Services\Qoyod\Presenters\Accounts\AccountsPresenter;
use App\Services\Qoyod\Presenters\Receipts\ReceiptsPresenter;

class QReceiptsController extends Controller
{
    /**
     * ============================================================================ 
     *   @param  ReceiptResourceInterface  $receipts
     * ============================================================================
     */
    public function __construct(
        private ReceiptResourceInterface     $receipts,
        private InventoryResourceInterface   $inventories,
        private ReceiptsPresenter            $receiptsPresenter,   // 
        private AccountsPresenter            $accountPresenter   // الحسابات

    ) {
        $this->middleware('can:صلاحيات منصة قيود');
    }

    /*
    |============================================================================
    | index
    |============================================================================
    */
    public function index(QReceiptsDataTable $dataTable)
    {
        return $dataTable->render('qoyod.receipts.index');
    }


    /*
    |============================================================================
    | create
    |============================================================================
    */
    public function create()
    {
        $accounts = $this->accountPresenter->getFilterAccounts('parent_type', 'Asset');

        // الموقع
        $inventoriesResponse    = $this->inventories->all();
        $inventories            = $inventoriesResponse['inventories'];

        return view('qoyod.receipts.create', compact('accounts', 'inventories'));
    }



    /*
    |============================================================================
    | store
    |============================================================================
    */
    public function store(QReceiptsRequest $request)
    {
        //Validation
        $data = $request->validated();

        try {
            $this->receipts->create($data);
            return redirect()->route('qoyod.receipts.index')->with('success', 'تم إضافة الايصال  بنجاح');
        } catch (QoyodRequestException $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'api_error' => 'فشل في إضافة الايصال الرجاء المحاولة مرة أخرى. التفاصيل: ' . $e->getMessage()
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
            $resp = $this->receipts->find($id);
            $receipt = $this->receiptsPresenter->getReceiptWithAccountAndCustomer($resp);


            $allocations = $receipt['allocations'] ?? [];

            $total       = 0; // الإجمالي 

            foreach ($allocations as $item) {
                $amount   = $item['amount'] ?? 0;

                $total    += $amount;
            }

            $receipt['total']      = $total;
            if (! $receipt) {
                abort(404);
            }
        } catch (\Exception $e) {
            abort(404);
        }

        return view('qoyod.receipts.show', compact('receipt'));
    }



    /*
    |============================================================================
    | destroy
    |============================================================================
    */
    public function destroy($id)
    {
        try {
            $this->receipts->delete($id);
            return redirect()->route('qoyod.receipts.index')->with('success', 'تم حذف  الإيصال  بنجاح');
        } catch (QoyodRequestException $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'api_error' => 'فشل في حذف  الإيصال الرجاء المحاولة مرة أخرى. التفاصيل: ' . $e->getMessage()
                ]);
        }
    }
}
