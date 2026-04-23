<?php

namespace App\Data\Hr\Employee;

use App\Enums\Hr\Employee\ContractType;
use App\Enums\Hr\Employee\InsuranceStatus;
use App\Enums\Hr\Employee\KnowledgeArea;
use App\Enums\Hr\Employee\LicenseType;
use App\Enums\Hr\Employee\QualificationDegree;
use App\Enums\Hr\Employee\TrialPeriod;

class EmployeeData
{
    // المعلومات الأساسية
    public string      $national_number;
    public string      $name;
    public string      $nickname;
    public int         $nationality;
    public string      $gender;
    public string      $id_number;
    public ?\DateTime  $birth_date;
    public ?string     $qualification_degree;
    public ?string     $knowledge_area;

    // بيانات التواصل
    public ?string     $personal_email;
    public string      $work_email;
    public string      $mobile;
    public ?string     $address;

    // معلومات الوظيفة
    public int         $hr_status_id;
    public int         $roles;
    public int         $bank_account_type;
    public string      $iban;

    // بيانات الرخصة
    public string      $license_type;
    public ?string     $law_license_number;
    public ?\DateTime  $law_license_end_date;
    public ?string     $training_number;
    public ?\DateTime  $training_end_date;

    // بيانات العقد والتأمينات
    public string      $contract_type;
    public string      $trial_period;
    public ?\DateTime  $contract_start_date;
    public ?\DateTime  $contract_end_date;
    public string      $insurance_status;

    // المعلومات المالية
    public ?float      $basic_salary;
    public ?float      $transportation_allowance;
    public ?float      $housing_allowance;
    public ?float      $other_allowances;

    // معلومات إضافية
    public ?string     $bio;

    // المرفقات
    public ?string     $profile_picture;
    public ?string     $resume;
    public ?string     $qualification_certificate;
    public ?string     $contract_attachment;
    public ?string     $id_attachment;
    public ?string     $bank_account_attachment;
    public ?string     $national_address_attachment;
    public ?string     $signature;
    public ?array      $additional_attachments;

    // حقول النظام
    public ?int        $user_id;
    public ?string     $job_title;

    public function __construct(array $data)
    {
        // المعلومات الأساسية
        $this->national_number        = $data['national_number'];
        $this->name                   = $data['name'];
        $this->nickname               = $data['nickname'];
        $this->nationality            = (int) $data['nationality'];
        $this->gender                 = $data['gender'];
        $this->id_number              = $data['id_number'];
        $this->birth_date             = !empty($data['birth_date']) ? new \DateTime($data['birth_date']) : null;
        $this->qualification_degree   = $data['qualification_degree'] ?? null;
        $this->knowledge_area         = $data['knowledge_area'] ?? null;

        // بيانات التواصل
        $this->personal_email         = $data['personal_email'] ?? null;
        $this->work_email             = $data['work_email'];
        $this->mobile                 = $data['mobile'];
        $this->address                = $data['address'] ?? null;

        // معلومات الوظيفة
        $this->hr_status_id           = (int) $data['hr_status_id'];
        $this->roles                  = (int) $data['roles'];
        $this->bank_account_type      = (int) $data['bank_account_type'];
        $this->iban                   = $data['iban'];

        // بيانات الرخصة
        $this->license_type           = $data['license_type'];
        $this->law_license_number     = $data['law_license_number'] ?? null;
        $this->law_license_end_date   = !empty($data['law_license_end_date']) ? new \DateTime($data['law_license_end_date']) : null;
        $this->training_number        = $data['training_number'] ?? null;
        $this->training_end_date      = !empty($data['training_end_date']) ? new \DateTime($data['training_end_date']) : null;

        // بيانات العقد والتأمينات
        $this->contract_type          = $data['contract_type'];
        $this->trial_period           = $data['trial_period'];
        $this->contract_start_date    = !empty($data['contract_start_date']) ? new \DateTime($data['contract_start_date']) : null;
        $this->contract_end_date      = !empty($data['contract_end_date']) ? new \DateTime($data['contract_end_date']) : null;
        $this->insurance_status       = $data['insurance_status'];

        // المعلومات المالية
        $this->basic_salary           = isset($data['basic_salary']) ? (float) $data['basic_salary'] : null;
        $this->transportation_allowance = isset($data['transportation_allowance']) ? (float) $data['transportation_allowance'] : null;
        $this->housing_allowance      = isset($data['housing_allowance']) ? (float) $data['housing_allowance'] : null;
        $this->other_allowances       = isset($data['other_allowances']) ? (float) $data['other_allowances'] : null;

        // معلومات إضافية
        $this->bio                    = $data['bio'] ?? null;

        // المرفقات
        $this->profile_picture        = $data['profile_picture'] ?? null;
        $this->resume                 = $data['resume'] ?? null;
        $this->qualification_certificate = $data['qualification_certificate'] ?? null;
        $this->contract_attachment    = $data['contract_attachment'] ?? null;
        $this->id_attachment          = $data['id_attachment'] ?? null;
        $this->bank_account_attachment = $data['bank_account_attachment'] ?? null;
        $this->national_address_attachment = $data['national_address_attachment'] ?? null;
        $this->signature              = $data['signature'] ?? null;
        $this->additional_attachments = $data['additional_attachments'] ?? null;

        // حقول النظام
        $this->user_id                = isset($data['user_id']) ? (int) $data['user_id'] : null;
        $this->job_title              = $data['job_title'] ?? null;
    }

    /**
     * تحويل البيانات إلى array
     */
    public function toArray(): array
    {
        return [
            // المعلومات الأساسية
            'national_number'               => $this->national_number,
            'name'                          => $this->name,
            'nickname'                      => $this->nickname,
            'nationality'                   => $this->nationality,
            'gender'                        => $this->gender,
            'id_number'                     => $this->id_number,
            'birth_date'                    => $this->birth_date?->format('Y-m-d'),
            'qualification_degree'          => $this->qualification_degree,
            'knowledge_area'                => $this->knowledge_area,

            // بيانات التواصل
            'personal_email'                => $this->personal_email,
            'work_email'                    => $this->work_email,
            'mobile'                        => $this->mobile,
            'address'                       => $this->address,

            // معلومات الوظيفة
            'hr_status_id'                  => $this->hr_status_id,
            'roles'                         => $this->roles,
            'bank_account_type'             => $this->bank_account_type,
            'iban'                          => $this->iban,

            // بيانات الرخصة
            'license_type'                  => $this->license_type,
            'law_license_number'            => $this->law_license_number,
            'law_license_end_date'          => $this->law_license_end_date?->format('Y-m-d'),
            'training_number'               => $this->training_number,
            'training_end_date'             => $this->training_end_date?->format('Y-m-d'),

            // بيانات العقد والتأمينات
            'contract_type'                 => $this->contract_type,
            'trial_period'                  => $this->trial_period,
            'contract_start_date'           => $this->contract_start_date?->format('Y-m-d'),
            'contract_end_date'             => $this->contract_end_date?->format('Y-m-d'),
            'insurance_status'              => $this->insurance_status,

            // المعلومات المالية
            'basic_salary'                  => $this->basic_salary,
            'transportation_allowance'      => $this->transportation_allowance,
            'housing_allowance'             => $this->housing_allowance,
            'other_allowances'              => $this->other_allowances,

            // معلومات إضافية
            'bio'                           => $this->bio,

            // المرفقات
            'profile_picture'               => $this->profile_picture,
            'resume'                        => $this->resume,
            'qualification_certificate'    => $this->qualification_certificate,
            'contract_attachment'           => $this->contract_attachment,
            'id_attachment'                 => $this->id_attachment,
            'bank_account_attachment'       => $this->bank_account_attachment,
            'national_address_attachment'   => $this->national_address_attachment,
            'signature'                     => $this->signature,
            'additional_attachments'        => $this->additional_attachments,

            // حقول النظام
            'user_id'                       => $this->user_id,
            'job_title'                     => $this->job_title,
        ];
    }

    /**
     * تحويل البيانات إلى array للمستخدم
     */
    public function toUserArray(): array
    {
        return [
            'name'                 => $this->name,
            'email'                => $this->work_email,
            'phone'                => $this->mobile,
            'nationality'          => $this->nationality,
            'image'                => $this->profile_picture,
            'status'               => 'active',
            'must_change_password' => false,
        ];
    }
}