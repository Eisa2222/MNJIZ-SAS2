<?php


namespace App\Services\Financial\ContractPayment;

use App\Enums\OperationsCenter\Contract\Payment\PaymentStatus;
use App\Models\OperationsCenter\Contract\Contract;
use App\Models\OperationsCenter\Contract\Payment\ContractPayment;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class ContractPaymentService
{
    public function changeStatus(Contract $contract, ContractPayment $payment, PaymentStatus $newStatus, $payment_method = null): void
    {
        abort_unless($payment->contract_id === $contract->id, 404);

        if ($payment->status === PaymentStatus::Paid && $newStatus === PaymentStatus::Paid) {
            throw new InvalidArgumentException('الدفعة تم دفعها مسبقا');
        }
        if ($payment->status === PaymentStatus::Cancelled && $newStatus === PaymentStatus::Cancelled) {
            throw new InvalidArgumentException('الدفعة تم الغاؤها مسبقا');
        }

        $data = [
            'status'     => $newStatus,
            'updated_by' => Auth::id(),
        ];

        if ($newStatus === PaymentStatus::Paid) {
            $data['payment_date']       = now();
            $data['payment_method']     = $payment_method;
            $data['paid_by']            = Auth::id();
            $data['updated_by']            = Auth::id();
        } elseif ($newStatus === PaymentStatus::Cancelled) {
            $data['payment_date']    = null;
            $data['payment_method']  = null;
            $data['paid_by']         = null;
            $data['updated_by']            = Auth::id();
        }

        $payment->update($data);
    }
}
