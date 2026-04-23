<?php

namespace App\Http\Requests\Hr\Employee;

use App\Enums\Hr\Employee\ContractType;
use App\Enums\Hr\Employee\InsuranceStatus;
use App\Enums\Hr\Employee\KnowledgeArea;
use App\Enums\Hr\Employee\LicenseType;
use App\Enums\Hr\Employee\QualificationDegree;
use App\Enums\Hr\Employee\TrialPeriod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    /*
    |--------------------------------------------------------------------------
    | authorize
    |--------------------------------------------------------------------------
    */
    public function authorize(): bool
    {
        return true;
    }


    /*
    |--------------------------------------------------------------------------
    | rules
    |--------------------------------------------------------------------------
    */
    public function rules(): array
    {
        $rules = [
            'name'                              => ['required', 'string', 'max:255'],
            'nickname'                          => ['required', 'string', 'max:50'],
            'nationality'                       => ['required', 'exists:settings_countries,id'],
            'gender'                            => ['required', Rule::in(['male', 'female'])],
            'id_number'                         => ['required', 'digits:10', Rule::unique('employees', 'id_number')], // رقم الهوية او الإقامة
            'birth_date'                        => ['nullable', 'date'],
            'qualification_degree'              => ['nullable', Rule::in(QualificationDegree::values())],
            'knowledge_area'                    => ['nullable', Rule::in(KnowledgeArea::values())],

            // بيانات التواصل
            'personal_email'                    => ['nullable', 'email', 'max:255'],
            'work_email'                        => ['required', 'email', 'max:255', Rule::unique('employees', 'work_email'), Rule::unique('users', 'email'),],
            'mobile'                            => ['required', 'string', 'max:15'],
            'address'                           => ['nullable', 'string', 'max:255'],

            // حالة الموظف
            'hr_status_id'                      => ['required', 'exists:settings_hr_statuses,id'],
            'roles'                             => ['required', 'exists:roles,id'], // المسمى الوظيفي
            'bank_account_type'                 => ['required', 'exists:settings_banks,id'],
            'iban'                              => ['required', 'string', 'size:24', 'regex:/^SA[0-9A-Z]{22}$/'],

            // بيانات الرخصة
            'license_type'                      => ['required', Rule::in(LicenseType::values())],
            'law_license_number'                => ['nullable', 'digits_between:5,10'],
            'law_license_end_date'              => ['nullable', 'date'],
            'training_number'                   => ['nullable', 'digits_between:5,10'],
            'training_end_date'                 => ['nullable', 'date'],

            // بيانات العقد و التأمينات
            'contract_type'                     => ['required', Rule::in(ContractType::values())],
            'trial_period'                      => ['required', Rule::in(TrialPeriod::values())],
            'contract_start_date'               => ['nullable', 'date'],
            'contract_end_date'                 => ['nullable', 'date', 'after_or_equal:contract_start_date'],
            'insurance_status'                  => ['required', Rule::in(InsuranceStatus::values())],

            'basic_salary'                      => ['nullable', 'numeric', 'min:0', 'min:0', 'max:1000000'],
            'transportation_allowance'          => ['nullable', 'numeric', 'min:0', 'min:0', 'max:1000000'],
            'housing_allowance'                 => ['nullable', 'numeric', 'min:0', 'min:0', 'max:1000000'],
            'other_allowances'                  => ['nullable', 'numeric', 'min:0', 'min:0', 'max:1000000'],

            'bio'                               => ['nullable', 'string', 'max:2000'],

            // تاريخ نهاية رخصة العمل
            'work_license_end_date'             => ['nullable', 'date'],

            // المرفقات
            'profile_picture'                   => ['nullable', 'image',    'mimes:jpg,jpeg,png', 'max:2048'],     // الصورة الشخصية
            'resume'                            => ['nullable', 'file',     'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:5120'],  // السيرة الذاتية
            'qualification_certificate'         => ['nullable', 'file',     'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:5120'],  // شهادة المؤهل
            'contract_attachment'               => ['nullable', 'file',     'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:5120'],  //  عقد العمل
            'id_attachment'                     => ['nullable', 'file',     'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:5120'],  // الهوية
            'bank_account_attachment'           => ['nullable', 'file',     'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:5120'],  // الحساب البنكي
            'national_address_attachment'       => ['nullable', 'file',     'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:5120'],  // العنوان الوطني
            'signature'                         => ['nullable', 'image',    'mimes:jpg,jpeg,png', 'max:2048'],      // التوقيع

            // مرفقات إضافية
            'additional_attachments'            => ['nullable', 'array'],
            'additional_attachments.*.name'     => ['required_with:additional_attachments.*.file', 'string', 'max:255'],
            'additional_attachments.*.file'     => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:5120'],

        ];

        // قواعد حسب نوع الرخصة
        $licenseType = $this->input('license_type');

        if ($licenseType === 'lawyer') {
            $rules['law_license_number'][]      = 'required';
            $rules['law_license_end_date'][]    = 'required';
        } elseif ($licenseType === 'trainee_lawyer') {
            $rules['training_number'][]         = 'required';
            $rules['training_end_date'][]       = 'required';
        }

        // قواعد حسب نوع العقد
        $contractType = $this->input('contract_type');

        if ($contractType === 'specific') {
            $rules['contract_start_date'][]     = 'required';
            $rules['contract_end_date'][]       = 'required';
        } elseif ($contractType === 'non_specific') {
            $rules['contract_start_date'][]     = 'required';
        }

        $nationality = $this->input('nationality');

        if ($nationality != 1) {
            $rules['work_license_end_date'][]     = 'required';
        }


        return $rules;
    }


    /*
    |--------------------------------------------------------------------------
    | messages
    |--------------------------------------------------------------------------
    */
    public function messages(): array
    {
        return [
            // الاسم
            'name.required'                     => 'حقل الاسم مطلوب.',
            'name.string'                       => 'يجب أن يكون حقل الاسم نصاً.',
            'name.max'                          => 'لا يجوز أن يتجاوز الاسم 255 حرفاً.',

            // اللقب
            'nickname.required'                 => 'حقل اللقب مطلوب.',
            'nickname.string'                   => 'يجب أن يكون اللقب نصاً.',
            'nickname.max'                      => 'لا يجوز أن يتجاوز اللقب 50 حرفاً.',

            // الجنسية
            'nationality.required'              => 'حقل الجنسية مطلوب.',
            'nationality.exists'                => 'الجنسية المحددة غير صحيحة.',

            // النوع
            'gender.required'                   => 'حقل النوع مطلوب.',
            'gender.in'                         => 'قيمة النوع المختارة غير صحيحة.',

            // رقم الهوية
            'id_number.required'                => 'حقل رقم الهوية أو الإقامة مطلوب.',
            'id_number.digits'                  => 'يجب أن يتكون رقم الهوية أو الإقامة من 10 أرقام.',
            'id_number.unique'                  => 'رقم الهوية أو الإقامة مستخدم بالفعل.',

            // تاريخ الميلاد
            'birth_date.date'                   => 'يجب أن يكون تاريخ الميلاد تاريخاً صالحاً.',

            // درجة المؤهل
            'qualification_degree.in'           => 'درجة المؤهل المحددة غير صحيحة.',

            // الجانب المعرفي
            'knowledge_area.in'                 => 'الجانب المعرفي المحدد غير صحيح.',

            // البريد الإلكتروني الشخصي
            'personal_email.email'              => 'يجب أن يكون البريد الإلكتروني الشخصي عنوان بريد إلكتروني صحيح.',
            'personal_email.max'                => 'لا يجوز أن يتجاوز البريد الإلكتروني الشخصي 255 حرفاً.',

            // بريد العمل
            'work_email.required'               => 'حقل بريد العمل مطلوب.',
            'work_email.email'                  => 'يجب أن يكون بريد العمل عنوان بريد إلكتروني صحيح.',
            'work_email.max'                    => 'لا يجوز أن يتجاوز بريد العمل 255 حرفاً.',
            'work_email.unique'                 => 'بريد العمل مستخدم بالفعل.',

            // رقم الجوال
            'mobile.required'                   => 'رقم الجوال مطلوب.',
            'mobile.string'                     => 'يجب أن يكون رقم الجوال نصاً.',
            'mobile.max'                        => 'يجب ألا يتجاوز رقم الجوال 15 رقماً.',

            // العنوان
            'address.string'                    => 'يجب أن يكون العنوان نصاً.',
            'address.max'                       => 'لا يجوز أن يتجاوز العنوان 255 حرفاً.',

            // حالة الموظف
            'hr_status_id.required'             => 'حقل حالة الموظف مطلوب.',
            'hr_status_id.exists'               => 'حالة الموظف المحددة غير صحيحة.',

            // المسمى الوظيفي
            'roles.required'                    => 'حقل المسمى الوظيفي مطلوب.',
            'roles.exists'                      => 'المسمى الوظيفي المحدد غير صحيح.',

            // نوع الحساب البنكي
            'bank_account_type.required'        => 'حقل نوع الحساب البنكي مطلوب.',
            'bank_account_type.exists'          => 'نوع الحساب البنكي المحدد غير صحيح.',

            // رقم الآيبان
            'iban.required'                     => 'حقل رقم الآيبان مطلوب.',
            'iban.string'                       => 'يجب أن يكون رقم الآيبان نصاً.',
            'iban.size'                         => 'يجب أن يتكون رقم الآيبان من 24 حرفاً.',
            'iban.regex'                        => 'صيغة رقم الآيبان غير صحيحة (يجب أن يبدأ بـ SA متبوعاً بـ 22 حرف).',

            // نوع الرخصة
            'license_type.required'             => 'نوع الرخصة مطلوب.',
            'license_type.in'                   => 'نوع الرخصة المحدد غير صحيح.',

            // رقم رخصة المحاماة
            'law_license_number.required'       => 'رقم رخصة المحاماة مطلوب للمحامي.',
            'law_license_number.digits_between' => 'رقم رخصة المحاماة يجب أن يكون بين 5 و10 أرقام.',

            // تاريخ انتهاء رخصة المحاماة
            'law_license_end_date.required'     => 'تاريخ انتهاء رخصة المحاماة مطلوب للمحامي.',
            'law_license_end_date.date'         => 'يجب أن يكون تاريخ انتهاء رخصة المحاماة تاريخاً صالحاً.',

            // رقم رخصة التدريب
            'training_number.required'          => 'رقم رخصة التدريب مطلوب للمحامي المتدرب.',
            'training_number.digits_between'    => 'رقم رخصة التدريب يجب أن يكون بين 5 و10 أرقام.',

            // تاريخ انتهاء رخصة التدريب
            'training_end_date.required'        => 'تاريخ انتهاء رخصة التدريب مطلوب للمحامي المتدرب.',
            'training_end_date.date'            => 'يجب أن يكون تاريخ انتهاء رخصة التدريب تاريخاً صالحاً.',

            // نوع العقد
            'contract_type.required'            => 'نوع العقد مطلوب.',
            'contract_type.in'                  => 'نوع العقد المحدد غير صحيح.',

            // فترة التجربة
            'trial_period.required'             => 'فترة التجربة مطلوبة.',
            'trial_period.in'                   => 'فترة التجربة المحددة غير صحيحة.',

            // تاريخ بداية العقد
            'contract_start_date.required'      => 'تاريخ بداية العقد مطلوب.',
            'contract_start_date.date'          => 'يجب أن يكون تاريخ بداية العقد تاريخاً صالحاً.',

            // تاريخ نهاية العقد
            'contract_end_date.required'        => 'تاريخ نهاية العقد مطلوب للعقد المحدد.',
            'contract_end_date.date'            => 'يجب أن يكون تاريخ نهاية العقد تاريخاً صالحاً.',
            'contract_end_date.after_or_equal'  => 'يجب أن يكون تاريخ نهاية العقد مساوياً أو بعد تاريخ بداية العقد.',

            // حالة التأمينات
            'insurance_status.required'         => 'حقل حالة التأمينات مطلوب.',
            'insurance_status.in'               => 'حالة التأمينات المحددة غير صحيحة.',

            // الراتب الأساسي
            'basic_salary.numeric'              => 'يجب أن يكون الراتب الأساسي رقماً.',
            'basic_salary.min'                  => 'لا يجوز أن يكون الراتب الأساسي أقل من 0.',
            'basic_salary.max'                  => 'لا يجوز أن يتجاوز الراتب الأساسي 1,000,000 ريال.',

            // بدل النقل
            'transportation_allowance.numeric'  => 'يجب أن يكون بدل النقل رقماً.',
            'transportation_allowance.min'      => 'لا يجوز أن يكون بدل النقل أقل من 0.',
            'transportation_allowance.max'      => 'لا يجوز أن يتجاوز بدل النقل 1,000,000 ريال.',

            // بدل السكن
            'housing_allowance.numeric'         => 'يجب أن يكون بدل السكن رقماً.',
            'housing_allowance.min'             => 'لا يجوز أن يكون بدل السكن أقل من 0.',
            'housing_allowance.max'             => 'لا يجوز أن يتجاوز بدل السكن 1,000,000 ريال.',

            // البدلات الأخرى
            'other_allowances.numeric'          => 'يجب أن تكون البدلات الأخرى رقماً.',
            'other_allowances.min'              => 'لا يجوز أن تكون البدلات الأخرى أقل من 0.',
            'other_allowances.max'              => 'لا يجوز أن تتجاوز البدلات الأخرى 1,000,000 ريال.',

            // النبذة التعريفية
            'bio.string'                        => 'يجب أن تكون النبذة التعريفية نصاً.',
            'bio.max'                           => 'يجب ألا تتجاوز النبذة التعريفية 2000 حرف.',

            // تاريخ نهاية رخصة العمل
            'work_license_end_date.required'      => 'تاريخ نهاية رخصة العمل مطلوب.',
            'work_license_end_date.date'          => 'يجب أن يكون تاريخ نهاية رخصة العمل تاريخاً صالحاً.',

            // الصورة الشخصية
            'profile_picture.image'             => 'يجب أن تكون الصورة الشخصية صورة.',
            'profile_picture.mimes'             => 'يجب أن تكون الصورة الشخصية من نوع: jpg, jpeg, png.',
            'profile_picture.max'               => 'لا يجوز أن تتجاوز الصورة الشخصية 2 ميغابايت.',

            // السيرة الذاتية
            'resume.file'                       => 'يجب أن تكون السيرة الذاتية ملفاً.',
            'resume.mimes'                      => 'يجب أن تكون السيرة الذاتية من نوع: jpg, jpeg, png, pdf, doc, docx.',
            'resume.max'                        => 'لا يجوز أن تتجاوز السيرة الذاتية 5 ميغابايت.',

            // شهادة المؤهل
            'qualification_certificate.file'    => 'يجب أن يكون ملف شهادة المؤهل ملفاً.',
            'qualification_certificate.mimes'   => 'يجب أن تكون شهادة المؤهل من نوع: jpg, jpeg, png, pdf, doc, docx.',
            'qualification_certificate.max'     => 'لا يجوز أن تتجاوز شهادة المؤهل 5 ميغابايت.',

            // عقد العمل
            'contract_attachment.file'          => 'يجب أن يكون عقد العمل ملفاً.',
            'contract_attachment.mimes'         => 'يجب أن يكون عقد العمل من نوع: jpg, jpeg, png, pdf, doc, docx.',
            'contract_attachment.max'           => 'لا يجوز أن يتجاوز عقد العمل 5 ميغابايت.',

            // مرفق الهوية
            'id_attachment.file'                => 'يجب أن تكون الهوية ملفاً.',
            'id_attachment.mimes'               => 'يجب أن تكون الهوية من نوع: jpg, jpeg, png, pdf, doc, docx.',
            'id_attachment.max'                 => 'لا يجوز أن تتجاوز الهوية 5 ميغابايت.',

            // الحساب البنكي
            'bank_account_attachment.file'      => 'يجب أن يكون الحساب البنكي ملفاً.',
            'bank_account_attachment.mimes'     => 'يجب أن يكون الحساب البنكي من نوع: jpg, jpeg, png, pdf, doc, docx.',
            'bank_account_attachment.max'       => 'لا يجوز أن يتجاوز الحساب البنكي 5 ميغابايت.',

            // العنوان الوطني
            'national_address_attachment.file'  => 'يجب أن يكون العنوان الوطني ملفاً.',
            'national_address_attachment.mimes' => 'يجب أن يكون العنوان الوطني من نوع: jpg, jpeg, png, pdf, doc, docx.',
            'national_address_attachment.max'   => 'لا يجوز أن يتجاوز العنوان الوطني 5 ميغابايت.',

            // التوقيع
            'signature.image'                   => 'يجب أن يكون التوقيع صورة.',
            'signature.mimes'                   => 'يجب أن يكون التوقيع من نوع: jpg, jpeg, png.',
            'signature.max'                     => 'لا يجوز أن يتجاوز التوقيع 2 ميغابايت.',

            // المرفقات الإضافية
            'additional_attachments.array'                  => 'يجب أن تكون المرفقات الإضافية مصفوفة.',
            'additional_attachments.*.name.required_with'   => 'حقل اسم المرفق الإضافي مطلوب عند إضافة ملف.',
            'additional_attachments.*.name.string'          => 'يجب أن يكون اسم المرفق الإضافي نصاً.',
            'additional_attachments.*.name.max'             => 'لا يجوز أن يتجاوز اسم المرفق الإضافي 255 حرفاً.',
            'additional_attachments.*.file.file'            => 'يجب أن يكون المرفق الإضافي ملفاً.',
            'additional_attachments.*.file.mimes'           => 'يجب أن تكون المرفقات الإضافية من نوع: jpg, jpeg, png, pdf, doc, docx.',
            'additional_attachments.*.file.max'             => 'لا يجوز أن تتجاوز المرفقات الإضافية 5 ميغابايت.',
        ];
    }
}
