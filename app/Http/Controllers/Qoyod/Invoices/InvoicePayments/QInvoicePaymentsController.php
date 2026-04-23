<?php

namespace App\Http\Controllers\Qoyod\Invoices\InvoicePayments;

use App\DataTables\Qoyod\Invoices\InvoicePayments\QInvoicePaymentsDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Qoyod\Invoices\InvoicePayments\QInvoicePaymentsRequest;
use App\Services\Qoyod\Contracts\Resources\InvoicePaymentResourceInterface;
use App\Services\Qoyod\Exceptions\QoyodRequestException;
use App\Services\Qoyod\Presenters\Invoices\InvoicePresenter;

class QInvoicePaymentsController extends Controller
{
    /**
     * ============================================================================ 
     *   @param  InvoicePaymentResourceInterface  $invoice_payments
     * ============================================================================
     */
    public function __construct(
        private InvoicePaymentResourceInterface $invoice_payments
    ) {
        $this->middleware('can:صلاحيات منصة قيود');
    }

    /*
    |============================================================================
    | index
    |============================================================================
    */
    public function index(QInvoicePaymentsDataTable $dataTable)
    {
        return $dataTable->render('qoyod.invoices.invoice_payments.index');
    }


    /*
    |============================================================================
    | store
    |============================================================================
    */
    public function store(QInvoicePaymentsRequest $request)
    {
        //Validation
        $data = $request->validated();

        try {
            $this->invoice_payments->create($data);
            return redirect()->route('qoyod.invoices.index')->with('success', 'تم دفع فاتورة المبيعات  بنجاح');
        } catch (QoyodRequestException $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'api_error' => 'فشل في دفع فاتورة المبيعات الرجاء المحاولة مرة أخرى. التفاصيل: ' . $e->getMessage()
                ]);
        }
    }
}
