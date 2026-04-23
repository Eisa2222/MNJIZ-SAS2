<?php

namespace App\Http\Requests\Qoyod\JournalEntries;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;


class QJournalEntriesRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            // البيانات الأساسية
            'description'           => 'nullable|string|max:1000',
            'date'                  => 'required|date',
            'inventory_id'          => 'required|integer',

            // بيانات السطور
            'journal_entry'                     => 'required|array',
            'journal_entry.lines'               => 'required|array|min:1',
            'journal_entry.lines.*.account_id'  => 'required|integer',
            'journal_entry.lines.*.debit'       => 'required|numeric|min:0|max:999999999.99',
            'journal_entry.lines.*.credit'      => 'required|numeric|min:0|max:999999999.99',
            'journal_entry.lines.*.comment'     => 'nullable|string|max:255',
        ];
    }



    public function messages(): array
    {
        return [
            // رسائل البيانات الأساسية
            'date.required'         => 'التاريخ مطلوب',
            'date.date'             => 'التاريخ غير صحيح',
            'date.before_or_equal'  => 'التاريخ يجب أن يكون اليوم أو قبله',
            'inventory_id.required' => 'الموقع مطلوب',
            'inventory_id.exists'   => 'الموقع المحدد غير موجود',
            'description.max'       => 'الوصف طويل جداً (الحد الأقصى 1000 حرف)',

            // رسائل بيانات السطور
            'journal_entry.required'                    => 'بيانات القيد مطلوبة',
            'journal_entry.lines.required'              => 'يجب إضافة سطر واحد على الأقل',
            'journal_entry.lines.min'                   => 'يجب إضافة سطر واحد على الأقل',
            'journal_entry.lines.*.account_id.required' => 'الحساب مطلوب في جميع السطور',
            'journal_entry.lines.*.account_id.exists'   => 'أحد الحسابات المحددة غير موجود',
            'journal_entry.lines.*.debit.required'      => 'قيمة المدين مطلوبة',
            'journal_entry.lines.*.debit.numeric'       => 'قيمة المدين يجب أن تكون رقم',
            'journal_entry.lines.*.debit.min'           => 'قيمة المدين يجب أن تكون أكبر من أو تساوي صفر',
            'journal_entry.lines.*.debit.max'           => 'قيمة المدين كبيرة جداً',
            'journal_entry.lines.*.credit.required'     => 'قيمة الدائن مطلوبة',
            'journal_entry.lines.*.credit.numeric'      => 'قيمة الدائن يجب أن تكون رقم',
            'journal_entry.lines.*.credit.min'          => 'قيمة الدائن يجب أن تكون أكبر من أو تساوي صفر',
            'journal_entry.lines.*.credit.max'          => 'قيمة الدائن كبيرة جداً',
            'journal_entry.lines.*.comment.max'         => 'التعليق طويل جداً (الحد الأقصى 255 حرف)',
        ];
    }



    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $this->validateJournalEntryLines($validator);
        });
    }

    /**
     * التحقق من منطق سطور القيد
     */
    protected function validateJournalEntryLines(Validator $validator): void
    {
        $lines = $this->input('journal_entry.lines', []);

        foreach ($lines as $index => $line) {
            $debit = (float) ($line['debit'] ?? 0);
            $credit = (float) ($line['credit'] ?? 0);

            // التحقق من أن أحد القيمتين على الأقل أكبر من صفر
            if ($debit <= 0 && $credit <= 0) {
                $validator->errors()->add(
                    "journal_entry.lines.{$index}",
                    "يجب إدخال قيمة في المدين أو الدائن في السطر " . ($index + 1)
                );
            }

            // التحقق من عدم إدخال قيم في كلا الحقلين
            if ($debit > 0 && $credit > 0) {
                $validator->errors()->add(
                    "journal_entry.lines.{$index}",
                    "لا يمكن إدخال قيمة في المدين والدائن معاً في السطر " . ($index + 1)
                );
            }
        }

        // التحقق من توازن القيد
        $this->validateBalance($validator, $lines);
    }

    /**
     * التحقق من توازن القيد
     */
    protected function validateBalance(Validator $validator, array $lines): void
    {
        $totalDebit = 0;
        $totalCredit = 0;
        $hasDebit = false;
        $hasCredit = false;

        foreach ($lines as $line) {
            $debit = (float) ($line['debit'] ?? 0);
            $credit = (float) ($line['credit'] ?? 0);

            if ($debit > 0) {
                $totalDebit += $debit;
                $hasDebit = true;
            }

            if ($credit > 0) {
                $totalCredit += $credit;
                $hasCredit = true;
            }
        }

        // التحقق من وجود مدين ودائن
        if (!$hasDebit) {
            $validator->errors()->add('balance', 'يجب إضافة بند مدين واحد على الأقل');
        }

        if (!$hasCredit) {
            $validator->errors()->add('balance', 'يجب إضافة بند دائن واحد على الأقل');
        }

        // التحقق من التوازن
        if (abs($totalDebit - $totalCredit) > 0.001) {
            $validator->errors()->add(
                'balance',
                "القيد غير متوازن: المدين (" . number_format($totalDebit, 2) .
                    ") لا يساوي الدائن (" . number_format($totalCredit, 2) . ")"
            );
        }
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // تنظيف البيانات قبل التحقق
        if ($this->has('journal_entry.lines')) {
            $lines = $this->input('journal_entry.lines');

            // إزالة السطور الفارغة
            $lines = array_filter($lines, function ($line) {
                return !empty($line['account_id']);
            });

            // إعادة ترقيم المفاتيح
            $lines = array_values($lines);

            $this->merge([
                'journal_entry' => [
                    'lines' => $lines
                ]
            ]);
        }
    }
}
