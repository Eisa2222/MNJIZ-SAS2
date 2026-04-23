<?php

namespace App\Data\OperationsCenter\Contract\Payment;

class ContractPaymentData
{
    public ?int   $id;
    public string  $calculationType;
    public string  $paymentBatchType;
    public ?float  $percentage;
    public ?float  $fixedAmount;
    public string  $dueDate;

    /**
     * @param  array<string,mixed>  $data
     */
    public function __construct(array $data)
    {
        $this->id                = isset($data['id']) ? (int)$data['id'] : null;
        $this->calculationType   = $data['calculation_type'];
        $this->paymentBatchType  = $data['payment_batch_type'];
        $this->percentage        = isset($data['percentage'])       ? (float) $data['percentage']       : null;
        $this->fixedAmount       = isset($data['fixed_amount'])     ? (float) $data['fixed_amount']     : null;
        $this->dueDate           = $data['due_date'];
    }

    public function toArray(): array
    {
        return [
            'id'                 => $this->id,
            'calculation_type'   => $this->calculationType,
            'payment_batch_type' => $this->paymentBatchType,
            'percentage'         => $this->percentage,
            'fixed_amount'       => $this->fixedAmount,
            'due_date'           => $this->dueDate,
        ];
    }
}
