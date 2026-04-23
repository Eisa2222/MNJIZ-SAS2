<?php

namespace App\Http\Requests\LegalAI;

use App\Data\LegalAI\SummarizeData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SummarizeDocumentRequest extends FormRequest
{

    /*
    |--------------------------------------------------------------------------
    | Determine if the user is authorized to make this request.
    |--------------------------------------------------------------------------
    */
    public function authorize(): bool
    {
        return true;
    }


    /*
    |--------------------------------------------------------------------------
    | Get the validation rules that apply to the request.
    |--------------------------------------------------------------------------
    */
    public function rules(): array
    {
        return [
            'document' => 'required|file|mimes:pdf,doc,docx,txt|max:51200',
            'summary_type' => ['sometimes', 'string', Rule::in(['detailed', 'short'])],
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Get the error messages for the defined validation rules.
    |--------------------------------------------------------------------------
    */
    public function messages(): array
    {
        return [
            'document.required' => 'يجب إرفاق ملف.',
            'document.file' => 'يجب أن يكون المرفق ملفاً صالحاً.',
            'document.mimes' => 'امتداد الملف غير مدعوم. الامتدادات المسموح بها: pdf, doc, docx, txt.',
            'document.max' => 'حجم الملف يتجاوز الحد الأقصى المسموح به (50 ميجابايت).',
            'summary_type.in' => 'نوع التلخيص المحدد غير صالح.',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | إنشاء كائن نقل البيانات (DTO) من بيانات الطلب المتحقق منها.
    |--------------------------------------------------------------------------
    */
    public function toDto(): SummarizeData
    {
        return new SummarizeData(
            document: $this->file('document'),
            summaryType: $this->input('summary_type', 'detailed')
        );
    }
}
