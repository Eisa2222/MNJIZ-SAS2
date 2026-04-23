<?php

namespace App\Http\Requests\OperationsCenter\Offers;

use Illuminate\Foundation\Http\FormRequest;

class OfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {

        return [
            'offer_name'                        => ['required', 'string', 'max:255'],
            'customer_id'                       => ['required', 'integer', 'exists:customers,id'],
            'start_date'                        => ['required', 'date'],
            'technical_offer'                   => ['nullable', 'string'],
            'financial_offer'                   => ['nullable', 'string'],
            'is_private_and_secret'             => ['nullable', 'boolean'],
        ];
    }


    public function messages(): array
    {
        return [
            'offer_name.required'            => 'حقل اسم العرض مطلوب.',
            'offer_name.string'              => 'حقل اسم العرض يجب أن يكون نصًا.',
            'offer_name.max'                 => 'اسم العرض لا يمكن أن يتجاوز 255 حرفًا.',

            'customer_id.required'           => 'حقل العميل مطلوب.',
            'customer_id.integer'            => 'يجب أن يكون معرف العميل رقمًا صحيحًا.',
            'customer_id.exists'             => 'العميل المحدد غير موجود في النظام.',

            'start_date.required'            => 'حقل تاريخ البدء مطلوب.',
            'start_date.date'                => 'حقل تاريخ البدء يجب أن يكون تاريخًا صحيحًا.',

            'is_private_and_secret.boolean'  => 'حقل السري/الخاص يجب أن يكون صحيحاً أو خاطئاً.',

            'technical_offer.string'         => 'حقل العرض الفني يجب أن يكون نصًا.',

            'financial_offer.string'         => 'حقل العرض المالي يجب أن يكون نصًا.',

        ];
    }



    protected function prepareForValidation(): void
    {
        // تحويل القيم الثنائية إلى boolean
        if ($this->has('is_private_and_secret')) {
            $this->merge([
                'is_private_and_secret' => filter_var($this->is_private_and_secret, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}
