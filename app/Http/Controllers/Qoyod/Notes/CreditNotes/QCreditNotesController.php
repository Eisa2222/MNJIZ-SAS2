<?php

namespace App\Http\Controllers\Qoyod\Notes\CreditNotes;

use App\DataTables\Qoyod\Notes\CreditNotes\QCreditNotesDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Qoyod\Notes\CreditNotes\QCreditNotesRequest;
use App\Services\Qoyod\Contracts\Resources\CreditNoteResourceInterface;
use App\Services\Qoyod\Contracts\Resources\CustomerResourceInterface;
use App\Services\Qoyod\Contracts\Resources\InventoryResourceInterface;
use App\Services\Qoyod\Contracts\Resources\InvoiceResourceInterface;
use App\Services\Qoyod\Exceptions\QoyodRequestException;
use App\Services\Qoyod\Presenters\Invoices\InvoicePresenter;
use App\Services\Qoyod\Presenters\Products\ProductPresenter;
use Illuminate\Support\Facades\Log;

class QCreditNotesController extends Controller
{
    /**
     * ============================================================================ 
     *   @param  CreditNoteResourceInterface  $credit_notes
     * ============================================================================
     */
    public function __construct(
        private CreditNoteResourceInterface $credit_notes,
        private InvoiceResourceInterface        $invoices,
        private ProductPresenter                $productPresenter,
        private CustomerResourceInterface       $customers,
        private InventoryResourceInterface      $inventories,
        private InvoicePresenter                $invoicePresenter,
    ) {$this->middleware('can:صلاحيات منصة قيود');}

    /*
    |============================================================================
    | index
    |============================================================================
    */
    public function index(QCreditNotesDataTable $dataTable)
    {
        return $dataTable->render('qoyod.notes.credit_notes.index');
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

        return view('qoyod.notes.credit_notes.create', compact('products', 'customers', 'inventories'));
    }



    /*
    |============================================================================
    | store
    |============================================================================
    */
    public function store(QCreditNotesRequest $request)
    {
        //Validation
        $data = $request->validated();

        // طريقة الدفع
        $data['payment_method'] = 1;
        $data['line_items'] = array_values($data['line_items']);

        try {
            $this->credit_notes->create($data);
            return redirect()->route('qoyod.credit-notes.index')->with('success', 'تم إضافة إشعار الدائن بنجاح');
        } catch (QoyodRequestException $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'api_error' => 'فشل في إضافة إشعار الدائنالرجاء المحاولة مرة أخرى. التفاصيل: ' . $e->getMessage()
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
            $resp = $this->credit_notes->find($id);
            $allInvoice = $this->invoicePresenter->getInvoicesWithRelatedNames($resp);
            $invoice = $allInvoice['note'] ?? null;

            if (! $invoice) {
                abort(404);
            }

            $lineItems = $invoice['line_items'] ?? [];

            $subtotal       = 0; // الإجمالي قبل الضريبة
            $taxAmount      = 0; // مجموع قيم الضريبة لكل بند
            $totalDiscount  = 0; // مجموع قيم الخصم لكل بند

            foreach ($lineItems as $item) {
                $quantity   = $item['quantity']             ?? 0;
                $unitPrice  = $item['price']                ?? 0;
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

        return view('qoyod.notes.credit_notes.show', compact('invoice'));
    }




    /*
    |============================================================================
    | destroy
    |============================================================================
    */
    public function destroy($id)
    {
        try {
            $this->credit_notes->delete($id);
            return redirect()->route('qoyod.credit-notes.index')->with('success', 'تم حذف  إشعار الدائن  بنجاح');
        } catch (QoyodRequestException $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'api_error' => 'فشل في حذف  إشعار الدائن الرجاء المحاولة مرة أخرى. التفاصيل: ' . $e->getMessage()
                ]);
        }
    }
}
