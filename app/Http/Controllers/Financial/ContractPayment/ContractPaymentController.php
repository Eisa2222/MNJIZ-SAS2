<?php

namespace App\Http\Controllers\Financial\ContractPayment;

use App\DataTables\Financial\ContractPayment\ContractPaymentDataTable;
use App\Enums\Financial\ContractPayment\ContractPaymentsState;
use App\Enums\OperationsCenter\Contract\Payment\PaymentMethod;
use App\Enums\OperationsCenter\Contract\Payment\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\OperationsCenter\Customer\Customers;
use App\Models\OperationsCenter\Contract\Contract;
use App\Models\OperationsCenter\Contract\Payment\ContractPayment;
use App\Services\Financial\ContractPayment\ContractPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ContractPaymentController extends Controller
{
    public function __construct(private ContractPaymentService $service) {}


    public function index(ContractPaymentDataTable $dataTable)
    {
        try {

            // Statistics
            $statusCounts = ContractPayment::query()
                ->select('status')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $totalPayment       = array_sum($statusCounts);
            $scheduledPayment   = $statusCounts[PaymentStatus::Scheduled->value] ?? 0;
            $paidPayment        = $statusCounts[PaymentStatus::Paid->value] ?? 0;
            $latePayment        = $statusCounts[PaymentStatus::Late->value] ?? 0;
            $cancelledPayment   = $statusCounts[PaymentStatus::Cancelled->value] ?? 0;

            // Filters
            $customers          = Customers::select('id', 'name')->get();
            $contracts          = Contract::select('id', 'contract_name')->get();


            return $dataTable->render('financial.contract_payments.index', compact(
                'totalPayment',
                'scheduledPayment',
                'paidPayment',
                'latePayment',
                'cancelledPayment',
                // Filters
                'customers',
                'contracts'

            ));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    }


    public function show(Contract $contract)
    {
        $paymentMethods = PaymentMethod::options();
        return view('financial.contract_payments.show',  compact('contract', 'paymentMethods'));
    }


    public function pay(Contract $contract, ContractPayment $payment, Request $request)
    {
        try {
            $this->service->changeStatus($contract, $payment, PaymentStatus::Paid, $request['payment_method']);
            return back()->with('success', 'تم الدفع بنجاح.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function cancel(Contract $contract, ContractPayment $payment)
    {
        try {
            $this->service->changeStatus($contract, $payment, PaymentStatus::Cancelled);
            return back()->with('success', 'تم إلغاء الدفعة.');
        } catch (\Throwable $e) {
            return back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }
}
