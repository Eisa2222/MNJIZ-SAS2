<?php

namespace App\Http\Requests\OperationsCenter\Contract;

use App\Enums\OperationsCenter\Contract\ContractType;
use App\Enums\OperationsCenter\Contract\Payment\CalculationType;
use App\Enums\OperationsCenter\Contract\Payment\PaymentBatchType;
use App\Enums\OperationsCenter\Contract\Payment\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\Financial\ContractPayment\PercentageSum;


class ContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'contract_name'                     => ['required', 'string', 'max:255'],
            'contract_type'                     => ['required', Rule::in(ContractType::values())],

            'main_contract_id'                  => [
                'nullable',
                'required_if:contract_type,' . ContractType::Supplementary->value,
                'exists:contracts,id'
            ],

            'offer_id'                          => [
                'nullable',
                'required_if:contract_type,' . ContractType::Main->value,
                'exists:offers,id'
            ],

            'contract_manager_id'               => ['nullable', 'exists:employees,id'],
            'contract_status_id'                => ['nullable', 'exists:settings_contract_statuses,id'],
            'contract_start_date'               => ['required',],
            'contract_end_date'                 => ['nullable',  'after_or_equal:contract_start_date'],
            'expected_closure_date'             => ['required',  'after_or_equal:contract_start_date'],
            'technical_offer'                   => ['nullable', 'string'],
            'financial_offer'                   => ['nullable', 'string'],
            'additional_attachments.*.name'     => ['required', 'string', 'max:255'],
            'additional_attachments.*.file'     => ['required', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:51200'],
            'is_private_and_secret'             => ['nullable', 'boolean'],


            'existing_attachment_names.*'       => 'required|string|max:255',
            'attachment_names.*'                => 'nullable|string|max:255',
            'attachment_files.*'                => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:51200',
            'delete_attachments.*'              => 'nullable|exists:contract_attachments,id',

            'payments'                          => ['nullable', 'array', new PercentageSum],

            'payments.*.id'                     => ['nullable', 'integer', 'exists:contract_payments,id'],

            'payments.*.calculation_type'       => ['required', Rule::in(CalculationType::values())],

            'payments.*.payment_batch_type'     => ['required', Rule::in(PaymentBatchType::values())],


            'payments.*.percentage'              => [
                'required_if:payments.*.calculation_type,' . CalculationType::Percentage->value,
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],

            'payments.*.fixed_amount'            => [
                'required_if:payments.*.calculation_type,' . CalculationType::Fixed->value,
                'nullable',
                'numeric',
                'min:0',
            ],

            'payments.*.due_date'                => ['required', 'date',],


            'supplement_preamble'                       => ['nullable', 'string'],
            'supplement_terms'                    => ['nullable', 'string'],

        ];
    }


    public function messages(): array
    {
        return [
            'contract_name.required'                    => 'حقل اسم العقد مطلوب.',
            'contract_name.string'                      => 'حقل اسم العقد يجب أن يكون نصاً.',
            'contract_name.max'                         => 'حقل اسم العقد يجب ألا يتجاوز 255 حرفاً.',

            'contract_type.required'                    => 'حقل نوع العقد مطلوب.',
            'contract_type.in'                          => 'نوع العقد المحدد غير صالح.',

            'main_contract_id.required_if'              => 'حقل العقد الرئيسي مطلوب عندما يكون نوع العقد "ملحق".',
            'main_contract_id.exists'                   => 'العقد الرئيسي المحدد غير موجود.',

            'offer_id.required_if'                      => 'حقل العرض مطلوب عندما يكون نوع العقد "رئيسي".',
            'offer_id.exists'                           => 'العرض المحدد غير موجود.',

            'contract_manager_id.exists'                => 'مسؤول العقد المحدد غير موجود.',

            'contract_status_id.exists'                 => 'حالة العقد المحددة غير موجودة.',

            'contract_start_date.required'              => 'حقل تاريخ بداية العقد مطلوب.',
            'contract_start_date.date'                  => 'حقل تاريخ بداية العقد يجب أن يكون تاريخاً صالحاً.',
            'contract_end_date.date'                    => 'حقل تاريخ نهاية العقد يجب أن يكون تاريخاً صالحاً.',
            'contract_end_date.after_or_equal'          => 'تاريخ نهاية العقد لا يمكن أن يكون قبل تاريخ البداية.',
            'expected_closure_date.required'            => 'حقل التاريخ المتوقع للإغلاق مطلوب.',
            'expected_closure_date.date'                => 'حقل التاريخ المتوقع للإغلاق يجب أن يكون تاريخاً صالحاً.',
            'expected_closure_date.after_or_equal'      => 'التاريخ المتوقع للإغلاق لا يمكن أن يكون قبل تاريخ البداية.',

            'technical_offer.string'         => 'حقل العرض الفني يجب أن يكون نصًا.',

            'financial_offer.string'         => 'حقل العرض المالي يجب أن يكون نصًا.',

            'additional_attachments.*.name.required'    => 'حقل اسم المرفق مطلوب.',
            'additional_attachments.*.name.string'      => 'اسم المرفق يجب أن يكون نصاً.',
            'additional_attachments.*.name.max'         => 'اسم المرفق يجب ألا يتجاوز 255 حرفاً.',
            'additional_attachments.*.file.required'    => 'حقل ملف المرفق مطلوب.',
            'additional_attachments.*.file.file'        => 'حقل ملف المرفق يجب أن يكون ملفاً.',
            'additional_attachments.*.file.mimes'       => 'صيغة ملف المرفق غير مدعومة. (jpg, jpeg, png, pdf, doc, docx).',
            'additional_attachments.*.file.max'         => 'حجم ملف المرفق يجب ألا يتجاوز 5 ميغابايت.',

            'is_private_and_secret.boolean'             => 'حقل السري/الخاص يجب أن يكون صحيحاً أو خاطئاً.',


            // payment
            'payments.array'                            => 'صيغة الدفعات غير صحيحة.',

            'payments.*.calculation_type.required'      => 'نوع الحساب (نسبة أو ثابت) مطلوب لكل دفعة.',
            'payments.*.calculation_type.in'            => 'نوع الحساب المحدد غير صالح.',

            'payments.*.percentage.required_if'         => 'حقل النسبة مطلوب عندما يكون نوع الحساب “نسبة”.',
            'payments.*.percentage.numeric'             => 'حقل النسبة يجب أن يكون قيمة رقمية.',
            'payments.*.percentage.min'                 => 'حقل النسبة لا يمكن أن يكون أقل من 0%.',
            'payments.*.percentage.max'                 => 'حقل النسبة لا يمكن أن يتجاوز 100%.',

            'payments.*.fixed_amount.required_if'       => 'حقل المبلغ مطلوب عندما يكون نوع الحساب “مبلغ ثابت”.',
            'payments.*.fixed_amount.numeric'           => 'حقل المبلغ يجب أن يكون قيمة رقمية.',
            'payments.*.fixed_amount.min'               => 'حقل المبلغ لا يمكن أن يكون قيمة سالبة.',

            'payments.*.due_date.required'              => 'حقل تاريخ الاستحقاق مطلوب لكل دفعة.',
            'payments.*.due_date.date'                  => 'حقل تاريخ الاستحقاق يجب أن يكون تاريخاً صالحاً.',
        ];
    }



    protected function prepareForValidation(): void
    {
        // تحويل القيم الثنائية إلى boolean
        if ($this->has('is_private_and_secret')) {
            $this->merge([
                'is_private_and_secret' => filter_var(
                    $this->is_private_and_secret,
                    FILTER_VALIDATE_BOOLEAN
                ),
            ]);
        }
    }
}
