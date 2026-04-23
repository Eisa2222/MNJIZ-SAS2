<?php

namespace App\Http\Controllers\Qoyod\Purchases\BillPayments;

use App\DataTables\Qoyod\Purchases\BillPayments\QBillPaymentsDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Qoyod\Purchases\BillPayments\QBillPaymentsRequest;
use App\Services\Qoyod\Contracts\Resources\BillPaymentResourceInterface;
use App\Services\Qoyod\Exceptions\QoyodRequestException;
use Illuminate\Support\Facades\Log;

class QBillPaymentsController extends Controller
{
    /**
     * ============================================================================ 
     *   @param  BillPaymentResourceInterface  $bill_payments
     * ============================================================================
     */
    public function __construct(private BillPaymentResourceInterface $bill_payments)
    {
        $this->middleware('can:صلاحيات منصة قيود');
    }

    /*
    |============================================================================
    | index
    |============================================================================
    */
    public function index(QBillPaymentsDataTable $dataTable)
    {
        return $dataTable->render('qoyod.purchases.bill_payments.index');
    }


    /*
    |============================================================================
    | store
    |============================================================================
    */
    public function store(QBillPaymentsRequest $request)
    {
        //Validation
        $data = $request->validated();

        try {
            $this->bill_payments->create($data);
            return redirect()->route('qoyod.bills.index')->with('success', 'تم دفع فاتورة المشتريات  بنجاح');
        } catch (QoyodRequestException $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'api_error' => 'فشل في دفع فاتورة المشتريات الرجاء المحاولة مرة أخرى. التفاصيل: ' . $e->getMessage()
                ]);
        }
    }
}
