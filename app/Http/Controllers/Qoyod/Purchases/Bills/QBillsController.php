<?php

namespace App\Http\Controllers\Qoyod\Purchases\Bills;

use App\DataTables\Qoyod\Purchases\Bills\QBillsDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Qoyod\Purchases\Bills\QBillsRequest;
use App\Services\Qoyod\Contracts\Resources\BillResourceInterface;
use App\Services\Qoyod\Contracts\Resources\InventoryResourceInterface;
use App\Services\Qoyod\Contracts\Resources\VendorResourceInterface;
use App\Services\Qoyod\Exceptions\QoyodRequestException;
use App\Services\Qoyod\Presenters\Products\ProductPresenter;
use App\Services\Qoyod\Presenters\Purchases\Bills\BillPresenter;
use Illuminate\Support\Facades\Log;

class QBillsController extends Controller
{
    /**
     * ============================================================================ 
     *   @param  BillResourceInterface  $bills
     * ============================================================================
     */
    public function __construct(
        private BillResourceInterface           $bills,
        private ProductPresenter                $productPresenter,
        private VendorResourceInterface         $vendors,
        private InventoryResourceInterface      $inventories,
        private BillPresenter                   $billPresenter,


    ) {
        $this->middleware('can:صلاحيات منصة قيود');
    }
    /*
    |============================================================================
    | index
    |============================================================================
    */
    public function index(QBillsDataTable $dataTable)
    {
        return $dataTable->render('qoyod.purchases.bills.index');
    }


    /*
    |============================================================================
    | create
    |============================================================================
    */
    public function create()
    {
        // المنتجات
        $products    = $this->productPresenter->getFilterProducts('type', 'Expense'); //المنتجات 

        // الموردين
        $vendorsResponse        = $this->vendors->all();
        $vendors                = $vendorsResponse['vendors'];

        // الموقع
        $inventoriesResponse    = $this->inventories->all();
        $inventories            = $inventoriesResponse['inventories'];

        return view('qoyod.purchases.bills.create', compact('products', 'vendors', 'inventories'));
    }


    /*
    |============================================================================
    | store
    |============================================================================
    */
    public function store(QBillsRequest $request)
    {
        //Validation
        $data = $request->validated();

        $data['line_items'] = array_values($data['line_items']);

        try {
            $this->bills->create($data);
            return redirect()->route('qoyod.bills.index')->with('success', 'تم إضافة فاتورة المشتريات  بنجاح');
        } catch (QoyodRequestException $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'api_error' => 'فشل في إضافة فاتورة المشتريات الرجاء المحاولة مرة أخرى. التفاصيل: ' . $e->getMessage()
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
            $resp = $this->bills->find($id);
            $allPurchaseOrder = $this->billPresenter->getPurchaseOrdersWithInventoryName($resp);
            $purchaseOrder = $allPurchaseOrder['bill'] ?? null;

            if (! $purchaseOrder) {
                abort(404);
            }

            $lineItems = $purchaseOrder['line_items'] ?? [];

            $subtotal       = 0; // الإجمالي قبل الضريبة
            $taxAmount      = 0; // مجموع قيم الضريبة لكل بند
            $totalDiscount  = 0; // مجموع قيم الخصم لكل بند

            foreach ($lineItems as $item) {
                $quantity   = $item['quantity']             ?? 0;
                $unitPrice  = $item['unit_price']           ?? 0;
                $discount   = $item['discount_amount']      ?? 0;
                $vatValue   = $item['unrounded_vat_value']  ?? 0;
                $lineTotal  = $item['total']                ?? 0;

                $lineSubtotal = ($quantity * $unitPrice) - $discount;

                // الاجمالي قبل الضريبة
                $subtotal    += $lineSubtotal;

                // اجمالي الضريبة
                $taxAmount   += $vatValue;

                // اجمالي الخصم
                $totalDiscount += $discount;
            }

            $purchaseOrder['subtotal']      = $subtotal;
            $purchaseOrder['tax_amount']    = $taxAmount;
            $purchaseOrder['total_discount'] = $totalDiscount;
        } catch (\Exception $e) {
            abort(404);
        }

        return view('qoyod.purchases.bills.show', compact('purchaseOrder'));
    }


    /*
    |============================================================================
    | destroy
    |============================================================================
    */
    public function destroy($id)
    {
        try {
            $this->bills->delete($id);
            return redirect()->route('qoyod.bills.index')->with('success', 'تم حذف فاتورة المشتريات  بنجاح');
        } catch (QoyodRequestException $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'api_error' => 'فشل في حذف فاتورة المشتريات الرجاء المحاولة مرة أخرى. التفاصيل: ' . $e->getMessage()
                ]);
        }
    }



    /*
    |============================================================================
    |============================================================================
    |                         other method
    |============================================================================
    |============================================================================
    */
    public function getBillsByvendorName($contact_id)
    {
        // الفواتير 
        $allBills = $this->billPresenter->getBillsByVendor($contact_id);
        return $allBills;
    }
}
