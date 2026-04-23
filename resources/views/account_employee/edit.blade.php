@extends('layouts.layoutMaster')

@section('title', 'تعديل بيانات الموظف')


@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">تعديل الملف الشخصي </a>
        <i class="ti ti-star favorite-icon" data-page-name="تعديل الملف الشخصي" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/css/intl-tel.css'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"></script>
    @vite(['resources/assets/js/account_employee/edit-wizard.js', 'resources/assets/js/intl-tel-w.js'])
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
                    <div class="step" data-target="#attachments">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">3</span>
                            <span class="bs-stepper-label mt-1">
                                <span class="bs-stepper-title">المرفقات</span>
                                <span class="bs-stepper-subtitle">إدارة المرفقات</span>
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
                    <form id="employee-form" action="{{ route('account.employee.profile.store', $employee->id) }}"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('POST')
                        <div id="personal-info" class="content">
                            <div class="row g-3">


                                <div class="col-md-4">
                                    <label class="form-label">اللقب</label>
                                    <input type="hidden" name="fields[0][name]" value="nickname">
                                    <input type="text" name="fields[0][value]" class="form-control"
                                        value="{{ old('nickname', $employee->nickname) }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">تاريخ الميلاد</label>
                                    <input type="hidden" name="fields[1][name]" value="birth_date">
                                    <input type="date" name="fields[1][value]" class="form-control"
                                        value="{{ old('birth_date', optional($employee->birth_date)->format('Y-m-d')) }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">درجة المؤهل</label>
                                    <input type="hidden" name="fields[2][name]" value="qualification_degree">
                                    <select name="fields[2][value]" class="form-select select2">
                                        <option value=""></option>
                                        @foreach ($qualificationDegree as $d)
                                            <option value="{{ $d['id'] }}"
                                                {{ old('qualification_degree', $employee->qualification_degree?->value) == $d['id'] ? 'selected' : '' }}>
                                                {{ $d['name'] }}
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
                                {{-- البريد الإلكتروني الشخصي --}}
                                <div class="col-md-4">
                                    <label class="form-label">البريد الإلكتروني الشخصي</label>
                                    <input type="hidden" name="fields[3][name]" value="personal_email">
                                    <input type="email" name="fields[3][value]" class="form-control"
                                        value="{{ old('personal_email', $employee->personal_email) }}"
                                        placeholder="name@example.com">
                                </div>

                                {{-- رقم الجوال --}}
                                <div class="col-md-4">
                                    <label class="form-label">رقم الجوال</label>
                                    <input type="hidden" name="fields[4][name]" value="mobile">
                                    <input id="contact_number" type="tel" name="fields[4][value]" class="form-control"
                                        value="{{ old('mobile', $employee->mobile) }}" placeholder="05xxxxxxxx">
                                </div>

                                {{-- العنوان --}}
                                <div class="col-md-4">
                                    <label class="form-label">العنوان</label>
                                    <input type="hidden" name="fields[5][name]" value="address">
                                    <input type="text" name="fields[5][value]" class="form-control"
                                        value="{{ old('address', $employee->address) }}"
                                        placeholder="المدينة – الحي – الشارع">
                                </div>

                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button class="btn btn-primary btn-next"> <span
                                        class="align-middle d-sm-inline-block d-none me-sm-2">التالي</span> <i
                                        class="ti ti-arrow-right ti-xs"></i></button>
                            </div>
                        </div>

                        <div id="attachments" class="content">
                            {{-- =======================  قسم المرفقات  ======================= --}}
                            <div class="row g-3">


                                {{-- السيرة الذاتية --}}
                                <div class="col-md-3">
                                    <label class="form-label">السيرة الذاتية</label>
                                    <input type="file" name="attachments[resume]" class="form-control">
                                    @if ($employee->resume)
                                        <small class="form-text text-muted">
                                            الملف الحالي: <a href="{{ Storage::url($employee->resume) }}"
                                                target="_blank">عرض</a>
                                        </small>
                                    @endif
                                </div>

                                {{-- شهادة المؤهل --}}
                                <div class="col-md-3">
                                    <label class="form-label">مرفق شهادة المؤهل</label>
                                    <input type="file" name="attachments[qualification_certificate]"
                                        class="form-control">
                                    @if ($employee->qualification_certificate)
                                        <small class="form-text text-muted">
                                            الملف الحالي: <a
                                                href="{{ Storage::url($employee->qualification_certificate) }}"
                                                target="_blank">عرض</a>
                                        </small>
                                    @endif
                                </div>

                                {{-- عقد العمل --}}
                                <div class="col-md-3">
                                    <label class="form-label">مرفق عقد العمل</label>
                                    <input type="file" name="attachments[contract_attachment]" class="form-control">
                                    @if ($employee->contract_attachment)
                                        <small class="form-text text-muted">
                                            الملف الحالي: <a href="{{ Storage::url($employee->contract_attachment) }}"
                                                target="_blank">عرض</a>
                                        </small>
                                    @endif
                                </div>

                                {{-- الهوية --}}
                                <div class="col-md-3">
                                    <label class="form-label">مرفق الهوية</label>
                                    <input type="file" name="attachments[id_attachment]" class="form-control">
                                    @if ($employee->id_attachment)
                                        <small class="form-text text-muted">
                                            الملف الحالي: <a href="{{ Storage::url($employee->id_attachment) }}"
                                                target="_blank">عرض</a>
                                        </small>
                                    @endif
                                </div>

                                {{-- الحساب البنكي --}}
                                <div class="col-md-3">
                                    <label class="form-label">مرفق الحساب البنكي</label>
                                    <input type="file" name="attachments[bank_account_attachment]"
                                        class="form-control">
                                    @if ($employee->bank_account_attachment)
                                        <small class="form-text text-muted">
                                            الملف الحالي: <a href="{{ Storage::url($employee->bank_account_attachment) }}"
                                                target="_blank">عرض</a>
                                        </small>
                                    @endif
                                </div>

                                {{-- العنوان الوطني --}}
                                <div class="col-md-3">
                                    <label class="form-label">مرفق العنوان الوطني</label>
                                    <input type="file" name="attachments[national_address_attachment]"
                                        class="form-control">
                                    @if ($employee->national_address_attachment)
                                        <small class="form-text text-muted">
                                            الملف الحالي: <a
                                                href="{{ Storage::url($employee->national_address_attachment) }}"
                                                target="_blank">عرض</a>
                                        </small>
                                    @endif
                                </div>

                                {{-- التوقيع --}}
                                <div class="col-md-3">
                                    <label class="form-label">مرفق التوقيع</label>
                                    <input type="file" name="attachments[signature]" class="form-control">
                                    @if ($employee->signature)
                                        <small class="form-text text-muted">
                                            التوقيع الحالي: <a href="{{ Storage::url($employee->signature) }}"
                                                target="_blank">عرض</a>
                                        </small>
                                    @endif
                                </div>

                            </div>
                            {{-- =====================  /انتهى  ===================== --}}

                            <div class="d-flex justify-content-between mt-4">
                                <button class="btn btn-label-secondary btn-prev"> <i
                                        class="ti ti-arrow-left ti-xs me-sm-2 me-0"></i>
                                    <span class="align-middle d-sm-inline-block d-none">السابق</span>
                                </button>
                                <button class="btn btn-primary btn-submit">تحديث</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
