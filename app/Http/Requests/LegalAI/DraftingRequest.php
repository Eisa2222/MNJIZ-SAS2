<?php

declare(strict_types=1);

namespace App\Http\Requests\LegalAI;

use App\Data\LegalAI\DraftingData;
use App\Enums\LegalAI\DraftingType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class DraftingRequest extends FormRequest
{

    /*
    |--------------------------------------------------------------------------
    | Get the validation rules that apply to the request.
    |--------------------------------------------------------------------------
    */
    public function authorize(): bool
    {
        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Get the error messages for the defined validation rules.
    |--------------------------------------------------------------------------
    */
    public function rules(): array
    {
        return [
            'drafting_type' => ['required', new Enum(DraftingType::class)],
            'raw_text' => 'nullable|string|max:20000',
            'document' => 'nullable|file|mimes:pdf,doc,docx,txt|max:51200|required_without:raw_text',
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
            'drafting_type.required' => 'نوع الصياغة مطلوب.',
            'drafting_type.enum' => 'نوع الصياغة غير صالح.',
            'raw_text.string' => 'النص يجب أن يكون نصاً.',
            'raw_text.max' => 'النص يجب ألا يتجاوز 20000 حرف.',
            'document.file' => 'الملف يجب أن يكون ملفاً صالحاً.',
            'document.mimes' => 'صيغة الملف يجب أن تكون: pdf, doc, docx, txt.',
            'document.max' => 'حجم الملف يجب ألا يتجاوز 50 ميجابايت.',
            'document.required_without' => 'يرجى إدخال النص أو رفع ملف.',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | إنشاء كائن نقل البيانات (DTO) من بيانات الطلب المتحقق منها.
    |--------------------------------------------------------------------------
    */
    public function toDto(): DraftingData
    {
        return new DraftingData(
            draftingType: $this->input('drafting_type'),
            rawText: $this->input('raw_text'),
            document: $this->file('document')
        );
    }
}
