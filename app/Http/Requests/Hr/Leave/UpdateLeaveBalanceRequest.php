<?php

namespace App\Http\Requests\hr\Leave;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class UpdateLeaveBalanceRequest extends FormRequest
{
    /*
    |--------------------------------------------------------------------------
    | Determine if the user is authorized to make this request.
    |--------------------------------------------------------------------------
    */
    public function authorize()
    {
        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Get the validation rules that apply to the request.
    |--------------------------------------------------------------------------
    */
    public function rules()
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'total_days'  => 'required|numeric|min:0',
            'used_days'   => 'required|numeric|min:0',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Get custom messages for validator errors.
    |--------------------------------------------------------------------------
    */
    public function messages()
    {
        return [
            'employee_id.required' => 'يجب تحديد الموظف',
            'employee_id.exists' => 'الموظف المحدد غير موجود في السجلات',
            'total_days.required' => 'إجمالي الأيام مطلوب',
            'total_days.numeric' => 'إجمالي الأيام يجب أن يكون رقمًا',
            'total_days.min' => 'إجمالي الأيام يجب أن يكون صفر أو أكبر',
            'used_days.required' => 'الأيام المستخدمة مطلوبة',
            'used_days.numeric' => 'الأيام المستخدمة يجب أن تكون رقمًا',
            'used_days.min' => 'الأيام المستخدمة يجب أن تكون صفر أو أكبر',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Prepare the data for validation.
    |--------------------------------------------------------------------------
    */
    protected function prepareForValidation()
    {
        $this->merge([
            'year' => Carbon::now()->year,
        ]);
    }
}
