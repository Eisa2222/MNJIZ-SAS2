@extends('layouts.layoutMaster')

@section('title', 'إضافة موظف ')

@section('breadcrumb')
    <li><a href="#"> الموارد البشرية</a></li>
    <li><a href="{{ route('hr.employees.index') }}"> الموظفين</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> إضافة موظف </a>
        <i class="ti ti-star favorite-icon" data-page-name="إضافة موظف " data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/css/intl-tel.css'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])

    <script>
        $(function() {
            const $licenseTypeSelect = $('#license_type');
            const $contractTypeSelect = $('#contract_type');
            const $nationalitySelect = $('#nationality');
            const SAUDI_NATIONALITY_ID = '1';

            if ($licenseTypeSelect.length) {
                const $lawLicenseFields = $('#law_license_number, #law_license_end_date').closest('.col-md-3');
                const $traineeLicenseFields = $('#training_number, #training_end_date').closest('.col-md-3');

                const toggleLicenseFields = () => {
                    const selected = $licenseTypeSelect.val();
                    const isLawyer = selected === 'lawyer';
                    const isTrainee = selected === 'trainee_lawyer';

                    $lawLicenseFields.toggle(isLawyer).find('input').prop('required', isLawyer);
                    $traineeLicenseFields.toggle(isTrainee).find('input').prop('required', isTrainee);

                    if (!isLawyer) $lawLicenseFields.find('input').val('');
                    if (!isTrainee) $traineeLicenseFields.find('input').val('');
                };
                $licenseTypeSelect.on('change', toggleLicenseFields);
                toggleLicenseFields(); // التنفيذ عند تحميل الصفحة
            }

            if ($contractTypeSelect.length) {
                const $startDateField = $('#contract_start_date').closest('.col-md-3');
                const $endDateField = $('#contract_end_date').closest('.col-md-3');

                const toggleContractDates = () => {
                    const selected = $contractTypeSelect.val();
                    const isSpecific = selected === 'specific';
                    const isNonSpecific = selected === 'non_specific';

                    $startDateField.toggle(isSpecific || isNonSpecific).find('input').prop('required',
                        isSpecific || isNonSpecific);
                    $endDateField.toggle(isSpecific).find('input').prop('required', isSpecific);

                    if (!isSpecific) $endDateField.find('input').val('');
                    if (!isSpecific && !isNonSpecific) $startDateField.find('input').val('');
                };
                $contractTypeSelect.on('change', toggleContractDates);
                toggleContractDates(); // التنفيذ عند تحميل الصفحة
            }
            if ($nationalitySelect.length) {
                const $workLicenseWrapper = $('#work_license_end_date').closest('.col-md-3');
                const $workLicenseInput = $('#work_license_end_date');

                if (!$workLicenseWrapper.length) {
                    return;
                }

                const toggleWorkLicenseField = () => {
                    const selectedNationality = $nationalitySelect.val();

                    if (selectedNationality && selectedNationality !== SAUDI_NATIONALITY_ID) {
                        $workLicenseWrapper.show();
                        $workLicenseInput.prop('required', true);
                    } else {
                        $workLicenseWrapper.hide();
                        $workLicenseInput.prop('required', false).val('');
                    }
                };

                $nationalitySelect.on('change', toggleWorkLicenseField);

                toggleWorkLicenseField();
            }
        });
    </script>
@endsection

@section('page-script')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css" />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"></script>

    @vite(['resources/assets/js/hr/employees/employee-form-wizard.js', 'resources/assets/js/intl-tel-w.js'])

@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div id="wizard-validation" class="bs-stepper mt-2">
                <div class="bs-stepper-header">
                    <!-- مراحل النموذج -->
                    <div class="step" data-target="#personal-info">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">1</span>
                            <span class="bs-stepper-label mt-1">
                                <span class="bs-stepper-title">المعلومات الشخصية</span>
                                <span class="bs-stepper-subtitle">البيانات الشخصية للموظف</span>
                            </span>
                        </button>
                    </div>
                    <div class="line"><i class="ti ti-chevron-right"></i></div>
                    <div class="step" data-target="#job-info">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">2</span>
                            <span class="bs-stepper-label mt-1">
                                <span class="bs-stepper-title">معلومات الوظيفة</span>
                                <span class="bs-stepper-subtitle">تفاصيل الوظيفة والتعاقد</span>
                            </span>
                        </button>
                    </div>
                    <div class="line"><i class="ti ti-chevron-right"></i></div>
                    <div class="step" data-target="#attachments">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">3</span>
                            <span class="bs-stepper-label mt-1">
                                <span class="bs-stepper-title">المرفقات</span>
                                <span class="bs-stepper-subtitle">رفع المرفقات المطلوبة</span>
                            </span>
                        </button>
                    </div>
                </div>
                <div class="bs-stepper-content">
                    @if ($errors->any())
                        <div class="alert alert-danger mt-2">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form id="employee-form" action="{{ route('hr.employees.store') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div id="personal-info" class="content">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">الرقم الوظيفي </label>
                                    <input type="text" disabled class="form-control numeric-only"
                                        value="{{ $nextNationalNumber }}" />
                                </div>

                                <div class="col-md-3">
                                    <label for="name" class="form-label">الاسم</label>
                                    <input type="text" id="name" name="name" class="form-control"
                                        value="{{ old('name') }}" required />
                                </div>

                                <div class="col-md-3">
                                    <label for="nickname" class="form-label">اللقب</label>
                                    <input type="text" id="nickname" name="nickname" class="form-control"
                                        value="{{ old('nickname') }}" required />
                                </div>

                                <div class="col-md-3">
                                    <label for="nationality" class="form-label">الجنسية</label>
                                    <select id="nationality" name="nationality" class="form-select select2" required
                                        data-placeholder="اختر الجنسية">
                                        <option value=""></option>
                                        @foreach ($countries as $country)
                                            <option value="{{ $country->id }}"
                                                {{ old('nationality') == $country->id ? 'selected' : '' }}>
                                                {{ $country->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label for="gender" class="form-label">اسم الجنس</label>
                                    <select id="gender" name="gender" class="form-select select2"
                                        data-placeholder="اختر اسم الجنس" required>
                                        <option value=""></option>
                                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>ذكر</option>
                                        <option value="female"{{ old('gender') == 'female' ? 'selected' : '' }}>
                                            أنثى</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label for="id_number" class="form-label">رقم الهوية أو الإقامة</label>
                                    <input type="text" minlength="10" maxlength="10" id="id_number" name="id_number"
                                        class="form-control numeric-only" value="{{ old('id_number') }}" required />
                                </div>

                                <div class="col-md-3">
                                    <label for="birth_date" class="form-label">تاريخ الميلاد</label>
                                    <input type="date" id="birth_date" name="birth_date" class="form-control"
                                        value="{{ old('birth_date') }}" />
                                </div>

                                <div class="col-md-3">
                                    <label for="qualification_degree" class="form-label">درجة المؤهل</label>
                                    <select id="qualification_degree" name="qualification_degree"
                                        class="form-select select2" data-placeholder="اختر درجة المؤهل">
                                        <option value=""></option>
                                        @foreach ($qualificationDegree as $item)
                                            <option value="{{ $item['id'] }}"
                                                {{ old('qualification_degree') == $item['id'] ? 'selected' : '' }}>
                                                {{ $item['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label for="knowledge_area" class="form-label">الجانب المعرفي</label>
                                    <select id="knowledge_area" name="knowledge_area" class="form-select select2"
                                        data-placeholder="اختر الجانب المعرفي">
                                        <option value=""></option>
                                        @foreach ($knowledgeArea as $item)
                                            <option value="{{ $item['id'] }}"
                                                {{ old('knowledge_area') == $item['id'] ? 'selected' : '' }}>
                                                {{ $item['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- -->
                                <div class="border-1 border-light border-dashed my-5"></div>
                                <p class="text-muted text-center my-0">
                                    بيانات التواصل
                                </p>
                                <div class="border-1 border-light border-dashed my-5"></div>
                                <!-- -->

                                <div class="col-md-3">
                                    <label for="personal_email" class="form-label">البريد الإلكتروني الشخصي</label>
                                    <input type="email" id="personal_email" name="personal_email" class="form-control"
                                        value="{{ old('personal_email') }}" />
                                </div>

                                <div class="col-md-3">
                                    <label for="work_email" class="form-label">بريد العمل</label>
                                    <input type="email" id="work_email" name="work_email" class="form-control"
                                        value="{{ old('work_email') }}" required />
                                </div>

                                <div class="col-md-3">
                                    <label for="mobile" class="form-label">الجوال</label>
                                    <input type="tel" id="contact_number" name="mobile" class="form-control"
                                        value="{{ old('mobile') }}" />
                                </div>

                                <div class="col-md-3">
                                    <label for="address" class="form-label">العنوان</label>
                                    <input type="text" id="address" name="address" class="form-control"
                                        value="{{ old('address') }}" />
                                </div>

                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button class="btn btn-primary btn-next"> <span
                                        class="align-middle d-sm-inline-block d-none me-sm-2">التالي</span> <i
                                        class="ti ti-arrow-right ti-xs"></i></button>
                            </div>
                        </div>
                        <div id="job-info" class="content">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label for="hr_status_id" class="form-label">حالة الموظف</label>
                                    <select id="hr_status_id" name="hr_status_id" class="form-select select2" required
                                        data-placeholder="اختر حالة الموظف">
                                        <option value=""></option>
                                        @foreach ($statuses as $statuse)
                                            <option value="{{ $statuse->id }}"
                                                {{ old('hr_status_id') == $statuse->id ? 'selected' : '' }}>
                                                {{ $statuse->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- الأدوار -->
                                <div class="col-md-3">
                                    <label for="roles" class="form-label">المسمى الوظيفي</label>
                                    <select id="roles" name="roles" class="form-select select2"
                                        data-placeholder="اختر المسمى الوظيفي">
                                        <option value=""></option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id }}">
                                                {{ $role->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label for="bank_account_type" class="form-label"> الحساب البنكي</label>
                                    <select id="bank_account_type" name="bank_account_type" class="form-select select2"
                                        data-placeholder="اختر البنك ">
                                        <option value=""></option>
                                        @foreach ($banks as $bank)
                                            <option value="{{ $bank->id }}"
                                                {{ old('bank_account_type') == $bank->id ? 'selected' : '' }}>
                                                {{ $bank->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label for="iban" class="form-label">IBAN</label>
                                    <input type="text" id="iban" name="iban" class="form-control"
                                        value="{{ old('iban') }}" />
                                </div>

                                <!-- -->
                                <div class="border-1 border-light border-dashed my-5"></div>
                                <p class="text-muted text-center my-0">
                                    بيانات الرخصة
                                </p>
                                <div class="border-1 border-light border-dashed my-5"></div>
                                <!-- -->

                                <div class="col-md-3">
                                    <label for="license_type" class="form-label">يتطلب رخصة</label>
                                    <select id="license_type" name="license_type" class="form-select select2"
                                        data-placeholder="اختر الرخصة" required>
                                        <option value=""></option>
                                        @foreach ($licenseType as $type)
                                            <option value="{{ $type['id'] }}"
                                                {{ old('license_type') == $type['id'] ? 'selected' : '' }}>
                                                {{ $type['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label for="law_license_number" class="form-label">رقم رخصة المحاماة</label>
                                    <input type="text" id="law_license_number" name="law_license_number"
                                        class="form-control numeric-only" value="{{ old('law_license_number') }}" />
                                </div>

                                <div class="col-md-3">
                                    <label for="law_license_end_date" class="form-label">تاريخ انتهاء رخصة
                                        المحاماه</label>
                                    <input type="date" id="law_license_end_date" name="law_license_end_date"
                                        class="form-control" value="{{ old('law_license_end_date') }}" />
                                </div>

                                <div class="col-md-3">
                                    <label for="training_number" class="form-label">رقم رخصة التدريب</label>
                                    <input type="text" id="training_number" name="training_number"
                                        class="form-control numeric-only" value="{{ old('training_number') }}" />
                                </div>

                                <div class="col-md-3">
                                    <label for="training_end_date" class="form-label">تاريخ انتهاء رخصة التدريب</label>
                                    <input type="date" id="training_end_date" name="training_end_date"
                                        class="form-control" value="{{ old('training_end_date') }}" />
                                </div>

                                <!-- -->
                                <div class="border-1 border-light border-dashed my-5"></div>
                                <p class="text-muted text-center my-0">
                                    بيانات العقد و التأمينات
                                </p>
                                <div class="border-1 border-light border-dashed my-5"></div>
                                <!-- -->

                                <div class="col-md-3">
                                    <label for="contract_type" class="form-label">نوع العقد</label>
                                    <select id="contract_type" name="contract_type" class="form-select select2"
                                        data-placeholder="اختر نوع العقد">
                                        <option value=""></option>
                                        @foreach ($contractType as $type)
                                            <option value="{{ $type['id'] }}"
                                                {{ old('contract_type') == $type['id'] ? 'selected' : '' }}>
                                                {{ $type['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label for="trial_period" class="form-label">فترة التجربة</label>
                                    <select id="trial_period" name="trial_period" class="form-select select2"
                                        data-placeholder="اختر فترة التجربة" required>
                                        <option value=""></option>
                                        @foreach ($trialPeriod as $type)
                                            <option value="{{ $type['id'] }}"
                                                {{ old('trial_period') == $type['id'] ? 'selected' : '' }}>
                                                {{ $type['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- حقل تاريخ بداية العقد - مخفي افتراضياً -->
                                <div class="col-md-3" id="contract_start_date_wrapper" style="display: none;">
                                    <label for="contract_start_date" class="form-label">تاريخ بداية العقد</label>
                                    <input type="date" id="contract_start_date" name="contract_start_date"
                                        class="form-control" value="{{ old('contract_start_date') }}" />
                                </div>

                                <!-- حقل تاريخ نهاية العقد - مخفي افتراضياً -->
                                <div class="col-md-3" id="contract_end_date_wrapper" style="display: none;">
                                    <label for="contract_end_date" class="form-label">تاريخ نهاية العقد</label>
                                    <input type="date" id="contract_end_date" name="contract_end_date"
                                        class="form-control" value="{{ old('contract_end_date') }}" />
                                </div>

                                <div class="col-md-3">
                                    <label for="work_license_end_date" class="form-label">تاريخ نهاية رخصة العمل </label>
                                    <input type="date" id="work_license_end_date" name="work_license_end_date"
                                        class="form-control" value="{{ old('work_license_end_date') }}" />
                                </div>

                                <div class="col-md-3">
                                    <label for="insurance_status" class="form-label">حالة التأمينات</label>
                                    <select id="insurance_status" name="insurance_status" class="form-select select2"
                                        data-placeholder="اختر حالة التأمينات" required>
                                        <option value=""></option>
                                        @foreach ($insuranceStatus as $status)
                                            <option value="{{ $status['id'] }}"
                                                {{ old('insurance_status') == $status['id'] ? 'selected' : '' }}>
                                                {{ $status['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>


                                {{-- <input type="hidden" name="has_insurance" value="0">
                                <div class="col-md-3">
                                    <label class="form-label">مشمول بالتأمين؟</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" name="has_insurance" type="checkbox"
                                            id="has_insurance" value="1"
                                            {{ old('has_insurance', $employee->has_insurance) ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="has_insurance">
                                            نعم، يخضع الموظف للتأمين
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        بتفعيل هذا الخيار سيتم خصم قيمة التأمين من مرتب الموظف
                                    </small>
                                    @error('has_insurance')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div> --}}

                                {{-- <div class="col-md-3" id="insurance_percentage_wrapper">
                                    <label for="insurance_percentage" class="form-label">نسبة التأمينات</label>
                                    <input type="text" min="0" max="100" step="0.01"
                                        data-original="{{ $employee->insurance_percentage }}" id="insurance_percentage"
                                        name="insurance_percentage" class="form-control numeric-only"
                                        value="{{ old('insurance_percentage', $employee->insurance_percentage) }}"
                                        disabled />

                                    <small>
                                        هذه النسبة تم تحديدها مسبقا من اعدادات النظام
                                        <strong class="text-danger cursor-pointer" id="enable_insurance_percentage">اضغط
                                            هنا</strong> لتعديلها يدويا
                                    </small>

                                </div> --}}


                                <div class="col-md-3">
                                    <label for="basic_salary" class="form-label">الراتب الأساسي</label>
                                    <input type="text" step="0.01" id="basic_salary" name="basic_salary"
                                        class="form-control numeric-only" value="{{ old('basic_salary') }}" />
                                </div>

                                <div class="col-md-3">
                                    <label for="transportation_allowance" class="form-label">بدل النقل</label>
                                    <input type="text" step="0.01" id="transportation_allowance"
                                        name="transportation_allowance" class="form-control  numeric-only"
                                        value="{{ old('transportation_allowance') }}" />
                                </div>

                                <div class="col-md-3">
                                    <label for="housing_allowance" class="form-label">بدل السكن</label>
                                    <input type="text" step="0.01" id="housing_allowance" name="housing_allowance"
                                        class="form-control numeric-only" value="{{ old('housing_allowance') }}" />
                                </div>

                                <div class="col-md-3">
                                    <label for="other_allowances" class="form-label">بدلات أخرى</label>
                                    <input type="text" step="0.01" id="other_allowances" name="other_allowances"
                                        class="form-control numeric-only" value="{{ old('other_allowances') }}" />
                                </div>

                                <div class="col-md-12">
                                    <label for="bio" class="form-label">نبذة تعريفية</label>
                                    <textarea id="bio" name="bio" class="form-control" rows="4">{{ old('bio') }}</textarea>
                                </div>

                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button class="btn btn-label-secondary btn-prev"> <i
                                        class="ti ti-arrow-left ti-xs me-sm-2 me-0"></i>
                                    <span class="align-middle d-sm-inline-block d-none">السابق</span>
                                </button>
                                <button class="btn btn-primary btn-next"> <span
                                        class="align-middle d-sm-inline-block d-none me-sm-2">التالي</span> <i
                                        class="ti ti-arrow-right ti-xs"></i></button>
                            </div>
                        </div>
                        <div id="attachments" class="content">
                            <div class="row g-3">
                                <!-- حقول المرفقات -->
                                <div class="col-md-3">
                                    <label for="profile_picture" class="form-label">الصورة الرسمية</label>
                                    <input type="file" id="profile_picture" name="profile_picture"
                                        class="form-control" />
                                </div>
                                <div class="col-md-3">
                                    <label for="resume" class="form-label">السيرة الذاتية</label>
                                    <input type="file" id="resume" name="resume" class="form-control" />

                                </div>
                                <div class="col-md-3">
                                    <label for="qualification_certificate" class="form-label">مرفق شهادة المؤهل</label>
                                    <input type="file" id="qualification_certificate" name="qualification_certificate"
                                        class="form-control" />

                                </div>
                                <div class="col-md-3">
                                    <label for="contract_attachment" class="form-label">مرفق عقد العمل</label>
                                    <input type="file" id="contract_attachment" name="contract_attachment"
                                        class="form-control" />

                                </div>
                                <div class="col-md-3">
                                    <label for="id_attachment" class="form-label">مرفق الهوية</label>
                                    <input type="file" id="id_attachment" name="id_attachment"
                                        class="form-control" />

                                </div>
                                <div class="col-md-3">
                                    <label for="bank_account_attachment" class="form-label">مرفق الحساب البنكي</label>
                                    <input type="file" id="bank_account_attachment" name="bank_account_attachment"
                                        class="form-control" />

                                </div>
                                <div class="col-md-3">
                                    <label for="national_address_attachment" class="form-label">
                                        مرفق العنوان الوطني
                                    </label>
                                    <input type="file" id="national_address_attachment"
                                        name="national_address_attachment" class="form-control" />

                                </div>

                                <div class="col-md-3">
                                    <label for="national_address_attachment" class="form-label"> مرفق التوقيع</label>
                                    <input type="file" id="signature" name="signature" class="form-control" />

                                </div>

                                <!-- مرفقات إضافية -->
                                <div class="col-12 mt-3">
                                    <label class="form-label">مرفقات إضافية</label>
                                    <div id="additional-attachments-container">
                                    </div>
                                    <button type="button" id="add-attachment" class="btn btn-secondary mt-2">
                                        إضافة مرفق جديد
                                    </button>
                                    <input type="hidden" name="deleted_attachments" id="deleted-attachments"
                                        value="">
                                </div>


                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button class="btn btn-label-secondary btn-prev"> <i
                                        class="ti ti-arrow-left ti-xs me-sm-2 me-0"></i>
                                    <span class="align-middle d-sm-inline-block d-none">السابق</span>
                                </button>
                                <button class="btn btn-primary btn-submit">حفظ</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
