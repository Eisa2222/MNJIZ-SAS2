<?php

namespace App\Http\Controllers\Qoyod\Purchases\PurchaseOrders;

use App\DataTables\Qoyod\Purchases\PurchaseOrders\QPurchaseOrdersDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Qoyod\Purchases\PurchaseOrders\QPurchaseOrdersRequest;
use App\Services\Qoyod\Contracts\Resources\InventoryResourceInterface;
use App\Services\Qoyod\Contracts\Resources\ProductResourceInterface;
use App\Services\Qoyod\Contracts\Resources\PurchaseOrdersResourceInterface;
use App\Services\Qoyod\Contracts\Resources\VendorResourceInterface;
use App\Services\Qoyod\Exceptions\QoyodRequestException;
use App\Services\Qoyod\Presenters\Products\ProductPresenter;
use App\Services\Qoyod\Presenters\Purchases\PurchaseOrders\PurchaseOrderPresenter;

class QPurchaseOrdersController extends Controller
{
    /**
     * ============================================================================ 
     *   @param  PurchaseOrdersResourceInterface  $purchase_orders
     * ============================================================================
     */

    public function __construct(
        protected   ProductResourceInterface        $products,
        private     PurchaseOrdersResourceInterface $purchase_orders,
        private     ProductPresenter                $productPresenter,
        private     VendorResourceInterface         $vendors,
        private     InventoryResourceInterface      $inventories,
        private     PurchaseOrderPresenter          $purchaseOrderPresenter
    ) {
        $this->middleware('can:صلاحيات منصة قيود');
    }

    /*
    |============================================================================
    | index
    |============================================================================
    */
    public function index(QPurchaseOrdersDataTable $dataTable)
    {
        return $dataTable->render('qoyod.purchases.purchase_orders.index');
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

        return view('qoyod.purchases.purchase_orders.create', compact('products', 'vendors', 'inventories'));
    }


    /*
    |============================================================================
    | store
    |============================================================================
    */
    public function store(QPurchaseOrdersRequest $request)
    {
        //Validation
        $data = $request->validated();

        $data['line_items'] = array_values($data['line_items']);

        // dd($data);

        try {
            $this->purchase_orders->create($data);
            return redirect()->route('qoyod.purchase-orders.index')->with('success', 'تم إضافة أمر الشراء  بنجاح');
        } catch (QoyodRequestException $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'api_error' => 'فشل في إضافة أمر الشراء الرجاء المحاولة مرة أخرى. التفاصيل: ' . $e->getMessage()
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
            $resp = $this->purchase_orders->find($id);
            $allPurchaseOrder = $this->purchaseOrderPresenter->getPurchaseOrdersWithInventoryName($resp);
            $purchaseOrder = $allPurchaseOrder['order'] ?? null;

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

        return view('qoyod.purchases.purchase_orders.show', compact('purchaseOrder'));
    }
}
