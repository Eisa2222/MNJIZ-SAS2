<?php

namespace App\Http\Controllers\Qoyod\Invoices;

use App\DataTables\Qoyod\Invoices\QInvoicesDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Qoyod\Invoices\QInvoicesRequest;
use App\Services\Qoyod\Contracts\Resources\CustomerResourceInterface;
use App\Services\Qoyod\Contracts\Resources\InventoryResourceInterface;
use App\Services\Qoyod\Contracts\Resources\InvoiceResourceInterface;
use App\Services\Qoyod\Exceptions\QoyodRequestException;
use App\Services\Qoyod\Presenters\Invoices\InvoicePresenter;
use App\Services\Qoyod\Presenters\Products\ProductPresenter;

class QInvoicesController extends Controller
{
    /**
     * ============================================================================ 
     *   @param  InvoiceResourceInterface  $invoices
     * ============================================================================
     */
    public function __construct(
        private InvoiceResourceInterface        $invoices,
        private ProductPresenter                $productPresenter,
        private CustomerResourceInterface       $customers,
        private InventoryResourceInterface      $inventories,
        private InvoicePresenter                $invoicePresenter,
    ) {
        $this->middleware('can:صلاحيات منصة قيود');
    }

    /*
    |============================================================================
    | index
    |============================================================================
    */
    public function index(QInvoicesDataTable $dataTable)
    {
        return $dataTable->render('qoyod.invoices.index');
    }


    /*
    |============================================================================
    | create
    |============================================================================
    */
    public function create()
    {
        // المنتجات
        $products    = $this->productPresenter->getFilterProducts('type', 'Service'); //المنتجات 

        // العملاء
        $customersResponse        = $this->customers->all();
        $customers                = $customersResponse['customers'];

        // الموقع
        $inventoriesResponse    = $this->inventories->all();
        $inventories            = $inventoriesResponse['inventories'];

        return view('qoyod.invoices.create', compact('products', 'customers', 'inventories'));
    }



    /*
    |============================================================================
    | store
    |============================================================================
    */
    public function store(QInvoicesRequest $request)
    {
        //Validation
        $data = $request->validated();

        $data['payment_method'] = 1;
        $data['line_items'] = array_values($data['line_items']);

        try {
            $this->invoices->create($data);
            return redirect()->route('qoyod.invoices.index')->with('success', 'تم إضافة فاتورة المبيعات  بنجاح');
        } catch (QoyodRequestException $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'api_error' => 'فشل في إضافة فاتورة المبيعات الرجاء المحاولة مرة أخرى. التفاصيل: ' . $e->getMessage()
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
            $resp = $this->invoices->find($id);
            $allInvoice = $this->invoicePresenter->getInvoicesWithRelatedNames($resp);
            $invoice = $allInvoice['invoice'] ?? null;

            if (! $invoice) {
                abort(404);
            }

            $lineItems = $invoice['line_items'] ?? [];

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

            $invoice['subtotal']      = $subtotal;
            $invoice['tax_amount']    = $taxAmount;
            $invoice['total_discount'] = $totalDiscount;
        } catch (\Exception $e) {
            abort(404);
        }

        return view('qoyod.invoices.show', compact('invoice'));
    }



    /*
    |============================================================================
    | destroy
    |============================================================================
    */
    public function destroy($id)
    {
        try {
            $this->invoices->delete($id);
            return redirect()->route('qoyod.invoices.index')->with('success', 'تم حذف فاتورة المبيعات  بنجاح');
        } catch (QoyodRequestException $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'api_error' => 'فشل في حذف فاتورة المبيعات الرجاء المحاولة مرة أخرى. التفاصيل: ' . $e->getMessage()
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
    public function getInvoicesByCustomerName($contact_id)
    {
        // الفواتير 
        $allInvoice = $this->invoicePresenter->getFilterInvoices('contact_id', $contact_id);
        return $allInvoice;
    }

    public function getInvoiceLineItems($invoice)
    {
        $allInvoice = $this->invoices->find($invoice);
        return $allInvoice;
    }
}
