<?php

namespace Database\Seeders;

use App\Models\TemplateVariables;
use Illuminate\Database\Seeder;

class TemplateVariableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $variables = [
            // حقول العقد الأساسية
            ['type' => 'contracts',  'name' => 'اسم العقد', 'placeholder' => '{{contract_name}}'],
            ['type' => 'contracts',  'name' => 'رقم العقد', 'placeholder' => '{{contract_number}}'],
            ['type' => 'contracts',  'name' => 'اسم العميل', 'placeholder' => '{{customer_name}}'],

            ['type' => 'contracts',  'name' => 'السجل المدني الخاص بالعميل', 'placeholder' => '{{civil_registry}}'],
            ['type' => 'contracts',  'name' => 'عنوان العميل', 'placeholder' => '{{address}}'],
            ['type' => 'contracts',  'name' => 'رقم جوال العميل', 'placeholder' => '{{contact_number}}'],
            ['type' => 'contracts',  'name' => 'البريد الالكتروني للعميل', 'placeholder' => '{{email}}'],
            ['type' => 'contracts',  'name' => 'جنسية العميل', 'placeholder' => '{{nationality}}'],


            ['type' => 'contracts',  'name' => 'تاريخ الإغلاق المتوقع', 'placeholder' => '{{expected_closure_date}}'],
            ['type' => 'contracts',  'name' => 'تاريخ بداية العقد', 'placeholder' => '{{contract_start_date}}'],
            ['type' => 'contracts',  'name' => 'تاريخ نهاية العقد', 'placeholder' => '{{contract_end_date}}'],

            ['type' => 'contracts',  'name' => 'يوم بداية العقد', 'placeholder' => '{{contract_start_day}}'],
            ['type' => 'contracts',  'name' => 'يوم نهاية العقد', 'placeholder' => '{{contract_end_day}}'],

            ['type' => 'contracts',  'name' => 'العرض الفني', 'placeholder' => '{{technical_offer}}'],
            ['type' => 'contracts',  'name' => 'العرض المالي', 'placeholder' => '{{financial_offer}}'],

            // حقول العقد الأساسية
            ['type' => 'supplementary_contract',  'name' => 'اسم  العقد الملحق', 'placeholder' => '{{contract_name}}'],
            ['type' => 'supplementary_contract',  'name' => 'رقم العقد الملحق', 'placeholder' => '{{contract_number}}'],
            ['type' => 'supplementary_contract',  'name' => 'اسم العميل', 'placeholder' => '{{customer_name}}'],

            ['type' => 'supplementary_contract',  'name' => 'السجل المدني الخاص بالعميل', 'placeholder' => '{{civil_registry}}'],
            ['type' => 'supplementary_contract',  'name' => 'عنوان العميل', 'placeholder' => '{{address}}'],
            ['type' => 'supplementary_contract',  'name' => 'رقم جوال العميل', 'placeholder' => '{{contact_number}}'],
            ['type' => 'supplementary_contract',  'name' => 'البريد الالكتروني للعميل', 'placeholder' => '{{email}}'],
            ['type' => 'supplementary_contract',  'name' => 'جنسية العميل', 'placeholder' => '{{nationality}}'],

            ['type' => 'supplementary_contract',  'name' => 'تاريخ بداية العقد الملحق', 'placeholder' => '{{contract_start_date}}'],
            ['type' => 'supplementary_contract',  'name' => 'تاريخ نهاية العقد الملحق', 'placeholder' => '{{contract_end_date}}'],

            ['type' => 'supplementary_contract',  'name' => 'يوم بداية العقد الملحق', 'placeholder' => '{{contract_start_day}}'],
            ['type' => 'supplementary_contract',  'name' => 'يوم نهاية العقد الملحق', 'placeholder' => '{{contract_end_day}}'],

            ['type' => 'supplementary_contract',  'name' => 'العرض الفني', 'placeholder' => '{{technical_offer}}'],
            ['type' => 'supplementary_contract',  'name' => 'العرض المالي', 'placeholder' => '{{financial_offer}}'],
            ['type' => 'supplementary_contract',  'name' => 'تمهيد ملحق العقد', 'placeholder' => '{{supplement_preamble}}'],
            ['type' => 'supplementary_contract',  'name' => 'بنود ملحق العقد', 'placeholder' => '{{supplement_terms}}'],
            ['type' => 'supplementary_contract',  'name' => 'رقم العقد الرئيسي', 'placeholder' => '{{main_contract_number}}'],
            ['type' => 'supplementary_contract',  'name' => 'تاريخ بداية العقد الرئيسي', 'placeholder' => '{{main_contract_start_date}}'],



            // حقول العرض الأساسية
            ['type' => 'offers',  'name' => 'رقم العرض', 'placeholder' => '{{offer_number}}'],
            ['type' => 'offers',  'name' => 'الموضوع', 'placeholder' => '{{offer_name}}'],
            ['type' => 'offers',  'name' => 'اسم العميل', 'placeholder' => '{{customer_name}}'], // اسم العميل بدلاً من ID
            ['type' => 'offers',  'name' => 'تاريخ بداية العرض', 'placeholder' => '{{start_date}}'],
            ['type' => 'offers',  'name' => 'العرض الفني', 'placeholder' => '{{technical_offer}}'],
            ['type' => 'offers',  'name' => 'العرض المالي', 'placeholder' => '{{financial_offer}}'],


            // التعريف بالراتب
            ['type' => 'salary_definition',  'name' => 'الجهة الطالبة', 'placeholder' => '{{recipient}}'],
            ['type' => 'salary_definition',  'name' => 'اسم الموظف', 'placeholder' => '{{employee_name}}'],
            ['type' => 'salary_definition',  'name' => 'رقم الهوية', 'placeholder' => '{{id_number}}'],
            ['type' => 'salary_definition',  'name' => 'الجنسية', 'placeholder' => '{{nationality}}'],
            ['type' => 'salary_definition',  'name' => 'المسمى الوظيفي', 'placeholder' => '{{job_title}}'],
            ['type' => 'salary_definition',  'name' => 'تاريخ بداية العقد', 'placeholder' => '{{contract_start_date}}'],
            ['type' => 'salary_definition',  'name' => 'الراتب الأساسي', 'placeholder' => '{{basic_salary}}'],
            ['type' => 'salary_definition',  'name' => 'بدل السكن', 'placeholder' => '{{housing_allowance}}'],
            ['type' => 'salary_definition',  'name' => 'بدل النقل', 'placeholder' => '{{transportation_allowance}}'],
            ['type' => 'salary_definition',  'name' => 'بدلات أخرى', 'placeholder' => '{{other_allowances}}'],
            ['type' => 'salary_definition',  'name' => 'إجمالي الراتب', 'placeholder' => '{{total_salary}}'],
            ['type' => 'salary_definition',  'name' => 'الرقم الوظيفي', 'placeholder' => '{{national_number}}'],
            ['type' => 'salary_definition',  'name' => 'رقم الحساب البنكي', 'placeholder' => '{{iban}}'],



            // التعريف بتثبيت راتب
            ['type' => 'salary_fixation',  'name' => 'الجهة الطالبة', 'placeholder' => '{{recipient}}'],
            ['type' => 'salary_fixation',  'name' => 'اسم الموظف', 'placeholder' => '{{employee_name}}'],
            ['type' => 'salary_fixation',  'name' => 'رقم الهوية', 'placeholder' => '{{id_number}}'],
            ['type' => 'salary_fixation',  'name' => 'الجنسية', 'placeholder' => '{{nationality}}'],
            ['type' => 'salary_fixation',  'name' => 'المسمى الوظيفي', 'placeholder' => '{{job_title}}'],
            ['type' => 'salary_fixation',  'name' => 'تاريخ بداية العقد', 'placeholder' => '{{contract_start_date}}'],
            ['type' => 'salary_fixation',  'name' => 'الرقم الوظيفي', 'placeholder' => '{{national_number}}'],
            ['type' => 'salary_fixation',  'name' => 'رقم الحساب البنكي', 'placeholder' => '{{iban}}'],


            // افادة تدريب
            ['type' => 'training_certificate',  'name' => 'الجهة الطالبة', 'placeholder' => '{{recipient}}'],
            ['type' => 'training_certificate',  'name' => 'اسم المتدرب', 'placeholder' => '{{employee_name}}'],
            ['type' => 'training_certificate',  'name' => 'رقم الهوية', 'placeholder' => '{{id_number}}'],
            ['type' => 'training_certificate',  'name' => 'رقم التدريب', 'placeholder' => '{{training_number}}'],
            ['type' => 'training_certificate',  'name' => 'تاريخ بداية التدريب', 'placeholder' => '{{contract_start_date}}'],


            // إخلاء طرف
            ['type' => 'clearance_certificates',  'name' => 'اسم الموظف', 'placeholder' => '{{employee_name}}'],
            ['type' => 'clearance_certificates',  'name' => 'رقم الهوية', 'placeholder' => '{{id_number}}'],
            // ['type' => 'clearance_certificates',  'name' => 'نوع التعاقد', 'placeholder' => '{{nationality}}'],
            ['type' => 'clearance_certificates',  'name' => 'تاريخ بداية العقد', 'placeholder' => '{{contract_start_date}}'],
            ['type' => 'clearance_certificates',  'name' => 'تاريخ نهاية العقد', 'placeholder' => '{{contract_end_date}}'],
            ['type' => 'clearance_certificates',  'name' => 'اسم المنشأة', 'placeholder' => '{{office_name}}'],



            // حقل تاريخ اليوم
            ['type' => 'all',  'name' => 'تاريخ اليوم', 'placeholder'  => '{{current_date}}'],
            ['type' => 'all',  'name' => 'اليوم',        'placeholder'  => '{{day}}'],
        ];


        foreach ($variables as $variable) {
            TemplateVariables::create($variable);
        }
    }
}
