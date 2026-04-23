<?php

namespace App\Http\Requests\GeneralSetting;

use Illuminate\Foundation\Http\FormRequest;

class SettingsViolationRequest extends FormRequest
{

    /*
    |============================================================================
    | authorize
    |============================================================================
    */
    public function authorize(): bool
    {
        return true;
    }

    /*
    |============================================================================
    | Rules
    |============================================================================
    */
    public function rules(): array
    {
        return [
            'description'                       => 'required|string|max:255',
            'settings_violation_category_id'    => 'required|exists:settings_violation_categories,id',
            'penalty_first'                     => 'required|string',
            'penalty_second'                    => 'nullable|string',
            'penalty_third'                     => 'nullable|string',
            'penalty_fourth'                    => 'nullable|string',
            'extra_deduction'                   => 'nullable|string|max:255',

            'violation_type'                    => 'required|in:delay,early_leave,absence,after_hours,other',
            'duration_unit'                     => 'nullable|required_unless:violation_type,other|in:minutes,hours,days',
            'duration_from'                     => 'nullable|required_unless:violation_type,other|integer|min:0',
            'duration_to'                       => 'nullable|integer|gte:duration_from',
        ];
    }

    /*
    |============================================================================
    | Messages
    |============================================================================
    */
    public function messages()
    {
        return [
            'description.required'                      => 'حقل الوصف مطلوب.',
            'description.string'                        => 'يجب أن يكون الوصف نصاً.',
            'description.max'                           => 'لا يجب أن يتجاوز الوصف 255 حرفاً.',

            'settings_violation_category_id.required'   => 'حقل تصنيف المخالفة مطلوب.',
            'settings_violation_category_id.exists'     => 'التصنيف المحدد غير صالح.',

            'penalty_first.required'                    => 'حقل الجزاء أول مرة مطلوب.',
            'penalty_first.string'                      => 'يجب أن يكون الجزاء أول مرة نصاً.',

            'penalty_second.string'                     => 'يجب أن يكون الجزاء ثاني مرة نصاً.',
            'penalty_third.string'                      => 'يجب أن يكون الجزاء ثالث مرة نصاً.',
            'penalty_fourth.string'                     => 'يجب أن يكون الجزاء رابع مرة نصاً.',

            'extra_deduction.string'                    => 'يجب أن تكون الملاحظات نصاً.',
            'extra_deduction.max'                       => 'لا يجب أن تتجاوز الملاحظات 255 حرفاً.',

            'violation_type.required'                   => 'حقل نوع المخالفة مطلوب.',
            'violation_type.in'                         => 'نوع المخالفة المحدد غير صالح.',

            'duration_unit.required_unless'             => 'حقل وحدة المدة مطلوب إلا إذا اخترت "أخرى".',
            'duration_unit.in'                          => 'وحدة المدة المحددة غير صالحة.',

            'duration_from.required_unless'             => 'حقل المدة من مطلوب إلا إذا اخترت "أخرى".',
            'duration_from.integer'                     => 'حقل المدة من يجب أن يكون عدداً صحيحاً.',
            'duration_from.min'                         => 'لا يمكن أن تكون قيمة المدة من أقل من 0.',

            'duration_to.integer'                       => 'حقل المدة إلى يجب أن يكون عدداً صحيحاً.',
            'duration_to.gte'                           => 'يجب أن تكون قيمة المدة إلى أكبر أو مساوية لقيمة المدة من.',
        ];
    }
}
