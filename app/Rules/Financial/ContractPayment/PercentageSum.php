<?php

namespace App\Rules\Financial\ContractPayment;

use App\Enums\OperationsCenter\Contract\Payment\CalculationType;
use Illuminate\Contracts\Validation\Rule;

class PercentageSum implements Rule
{
    public function __construct(private string $field = 'payments') {}

    public function passes($attribute, $value): bool
    {
        $total = 0;
        foreach ($value as $pay) {
            if (($pay['calculation_type'] ?? '') === CalculationType::Percentage->value) {
                $total += floatval($pay['percentage'] ?? 0);
            }
        }
        return $total <= 100;
    }

    public function message(): string
    {
        return 'إجمالي نسب الدفعات لا يجب أن يتجاوز 100٪.';
    }
}
