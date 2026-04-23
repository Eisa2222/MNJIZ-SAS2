<?php

declare(strict_types=1);

namespace App\Http\Requests\LegalAI;

use App\Data\LegalAI\PrecedentData;
use Illuminate\Foundation\Http\FormRequest;

class PrecedentRequest extends FormRequest
{

    /*
    |--------------------------------------------------------------------------
    | Authorize
    |--------------------------------------------------------------------------
    */
    public function authorize(): bool
    {
        return true;
    }


    /*
    |--------------------------------------------------------------------------
    | Rules
    |--------------------------------------------------------------------------
    */
    public function rules(): array
    {
        return [
            'query_text' => 'nullable|string|max:10000',
            'document' => 'nullable|file|mimes:pdf,doc,docx,txt|max:51200|required_without:query_text',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Message
    |--------------------------------------------------------------------------
    */
    public function messages(): array
    {
        return [
            'query_text.string' => 'يجب أن يكون نص الاستفسار عبارة عن نص.',
            'query_text.max' => 'يجب ألا يتجاوز نص الاستفسار 10000 حرف.',
            'document.file' => 'يجب أن يكون الملف المرفق ملفاً صالحاً.',
            'document.mimes' => 'يجب أن يكون الملف من نوع: pdf, doc, docx, txt.',
            'document.max' => 'يجب ألا يتجاوز حجم الملف 50 ميجابايت.',
            'document.required_without' => 'يرجى إرفاق ملف أو إدخال نص الاستفسار.',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | TO DTO
    |--------------------------------------------------------------------------
    */
    public function toDto(): PrecedentData
    {
        return new PrecedentData(
            queryText: $this->input('query_text'),
            document: $this->file('document')
        );
    }
}
