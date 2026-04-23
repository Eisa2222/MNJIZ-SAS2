@extends('layouts.layoutMaster')

@section('title', 'تعديل بيانات الخصم')

@section('breadcrumb')
    <li><a href="#">الشؤون القانونية</a></li>

    <li><a href="{{ route('legal-affairs.opponents.index') }}">الخصوم</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">تعديل بيانات الخصم</a>
        <i class="ti ti-star favorite-icon" data-page-name="تعديل بيانات الخصم" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

<!-- Vendor Styles -->
@section('vendor-style')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.scss', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/css/intl-tel.css'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.js', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js'])
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

<!-- Page Scripts -->
@section('page-script')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"></script>

    {{-- نفس ملفات JS المستخدمة في صفحة الإنشاء؛ تفترض أن لديها نفس منطق التحقق --}}
    @vite(['resources/assets/js/opponents-validation.js', 'resources/assets/js/intl-tel-w.js'])

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const typeInputs = document.querySelectorAll('input[name="type"]');

            const commercialRegistrationContainer = document.getElementById('commercial-registration-container');
            const unifiedNumberContainer = document.getElementById('unified-number-container');
            const authorizationsContainer = document.getElementById('authorizations-container');

            const commercialRegistrationInput = document.getElementById('commercial_registration');
            const unifiedNumberInput = document.getElementById('unified_number');
            const authorizationsWrapper = document.getElementById('authorizations-wrapper');

            const identityNumberInput = document.getElementById('identity_number');

            function toggleFields() {
                const selectedType = document.querySelector('input[name="type"]:checked').value;

                if (selectedType === 'individual') {
                    // إخفاء حقول الشركة
                    commercialRegistrationContainer.style.display = 'none';
                    unifiedNumberContainer.style.display = 'none';
                    authorizationsContainer.style.display = 'none';

                    // إظهار حقل رقم الهوية
                    identityNumberInput.parentElement.style.display = 'block';

                    // تفريغ حقول الشركة عند إخفائها
                    commercialRegistrationInput.value = '';
                    unifiedNumberInput.value = '';
                    if (authorizationsWrapper) {
                        authorizationsWrapper.innerHTML = '';
                    }

                } else if (selectedType === 'company') {
                    // إظهار حقول الشركة
                    commercialRegistrationContainer.style.display = 'block';
                    unifiedNumberContainer.style.display = 'block';
                    authorizationsContainer.style.display = 'block';

                    // إخفاء حقل رقم الهوية
                    identityNumberInput.parentElement.style.display = 'none';
                    // تفريغ قيمة رقم الهوية
                    identityNumberInput.value = '';
                }
            }

            // عند تغيير نوع الخصم
            typeInputs.forEach(input => {
                input.addEventListener('change', toggleFields);
            });

            // عند تحميل الصفحة
            toggleFields();
        });
    </script>

@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div id="wizard-validation" class="bs-stepper mt-2">
                <!-- Header of Steps -->
                <div class="bs-stepper-header">
                    <div class="step" data-target="#basic-info">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">1</span>
                            <span class="bs-stepper-label mt-1">
                                <span class="bs-stepper-title">معلومات الخصم</span>
                                <span class="bs-stepper-subtitle">تعديل المعلومات الأساسية</span>
                            </span>
                        </button>
                    </div>
                    <div class="line"><i class="ti ti-chevron-right"></i></div>
                    <div class="step" data-target="#additional-info-2">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">2</span>
                            <span class="bs-stepper-label">
                                <span class="bs-stepper-title">معلومات الإتصال</span>
                                <span class="bs-stepper-subtitle">تحديث معلومات الإتصال</span>
                            </span>
                        </button>
                    </div>
                </div>
                <!-- Body of Steps -->
                <div class="bs-stepper-content">
                    <!-- عرض الأخطاء إن وجدت -->
                    @if ($errors->any())
                        <div class="alert alert-danger mt-2">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Form -->
                    <form id="power-form" action="{{ route('legal-affairs.opponents.update', $opponent->id) }}"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- Step 1: Basic Info --}}
                        <div id="basic-info" class="content dstepper-block">
                            <div class="row g-3">
                                <!-- حقل اختيار نوع الخصم -->
                                <div class="row d-flex flex-row-reverse justify-content-center">
                                    <label class="form-label text-center my-5">نوع الخصم</label>

                                    <div class="col-md-3">
                                        <div class="form-check custom-option custom-option-icon">
                                            <label class="form-check-label custom-option-content" for="company">
                                                <span class="custom-option-body">
                                                    <i class="ti ti-user-shield"></i>
                                                    <span class="custom-option-title"> شخصية اعتبارية </span>
                                                    <small>للشركات والمؤسسات و الجهات الحكومية</small>
                                                </span>
                                                <input id="company" name="type" class="form-check-input" type="radio"
                                                    {{ old('type', $opponent->type->value) == 'company' ? 'checked' : '' }}
                                                    value="company">
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-check custom-option custom-option-icon">
                                            <label class="form-check-label custom-option-content" for="individual">
                                                <span class="custom-option-body">
                                                    <i class="ti ti-user"></i>
                                                    <span class="custom-option-title">فرد</span>
                                                    <small>مخصص للأفراد </small>
                                                </span>
                                                <input id="individual" name="type" class="form-check-input"
                                                    type="radio"
                                                    {{ old('type', $opponent->type->value) == 'individual' ? 'checked' : '' }}
                                                    value="individual">
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- اسم الخصم -->
                                <div class="col-md-6">
                                    <label class="form-label" for="name">الاسم</label>
                                    <input type="text" name="name" id="name" class="form-control"
                                        placeholder="أدخل الاسم" value="{{ old('name', $opponent->name) }}" />
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="contact_number">رقم الاتصال</label>
                                    <input type="tel" name="contact_number" id="contact_number" class="form-control"
                                        placeholder="ادخل رقم الاتصال"
                                        value="{{ old('contact_number', $opponent->contact_number) }}" />
                                    <p class="small" id="message"></p>
                                </div>
                                <!-- البريد الإلكتروني -->
                                <div class="col-md-6">
                                    <label class="form-label" for="email">البريد الإلكتروني</label>
                                    <input type="email" name="email" id="email" class="form-control"
                                        placeholder="ادخل البريد الإلكتروني" value="{{ old('email', $opponent->email) }}" />
                                </div>
                                <!-- المدينة -->
                                <div class="col-md-6">
                                    <label class="form-label" for="settings_region_id">المدينة</label>
                                    <select class="select2 form-select" id="settings_region_id" name="settings_region_id"
                                        data-placeholder="اختر المدينة">
                                        <option value="">اختر المدينة</option>
                                        @foreach ($settings_regions as $region)
                                            <option value="{{ $region->id }}"
                                                {{ old('settings_region_id', $opponent->settings_region_id) == $region->id ? 'selected' : '' }}>
                                                {{ $region->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                            </div>
                            <!-- أزرار الانتقال بين الخطوات -->
                            <div class="d-flex justify-content-between mt-4">
                                <button class="btn btn-label-secondary btn-prev" disabled>
                                    <i class="ti ti-arrow-left ti-xs me-sm-2 me-0"></i>
                                    <span class="align-middle d-sm-inline-block d-none">السابق</span>
                                </button>
                                <button type="button" class="btn btn-primary btn-next">
                                    <span class="align-middle d-sm-inline-block d-none me-sm-2">التالي</span>
                                    <i class="ti ti-arrow-right ti-xs"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Step 2: Additional Info --}}
                        <div id="additional-info-2" class="content">
                            <div class="row g-3">
                                <!-- رقم الهوية -->
                                <div class="col-md-6">
                                    <label class="form-label" for="identity_number">رقم الهوية</label>
                                    <input type="tel" id="identity_number" name="identity_number"
                                        class="form-control"
                                        value="{{ old('identity_number', $opponent->identity_number) }}" />
                                </div>
                                <!-- رقم السجل التجاري -->
                                <div class="col-md-6" id="commercial-registration-container">
                                    <label class="form-label" for="commercial_registration">رقم السجل التجاري</label>
                                    <input type="text" name="commercial_registration" id="commercial_registration"
                                        class="form-control" placeholder="ادخل رقم السجل التجاري"
                                        value="{{ old('commercial_registration', $opponent->commercial_registration) }}" />
                                </div>
                                <!-- الرقم الموحد -->
                                <div class="col-md-6" id="unified-number-container">
                                    <label class="form-label" for="unified_number">الرقم الموحد</label>
                                    <input type="tel" name="unified_number" id="unified_number" class="form-control"
                                        placeholder="ادخل الرقم الموحد"
                                        value="{{ old('unified_number', $opponent->unified_number) }}" />
                                </div>

                                <!-- حقول المفوضين للمؤسسة -->
                                <div class="col-12" id="authorizations-container">
                                    <label class="form-label">المفوضين</label>
                                    <div id="authorizations-wrapper">
                                        <!-- في حال وجود old authorizations من محاولة تعديل سابقة فاشلة -->
                                        @php
                                            $oldAuthorizations = old('authorizations');
                                            $dbAuthorizations = $opponent->authorizations;
                                        @endphp

                                        @if ($oldAuthorizations)
                                            @foreach ($oldAuthorizations as $index => $authorization)
                                                <div class="authorization-item mb-3">
                                                    <div class="row g-3">
                                                        <div class="col-sm-3">
                                                            <input type="text"
                                                                name="authorizations[{{ $index }}][name]"
                                                                class="form-control" placeholder="اسم المفوض"
                                                                value="{{ $authorization['name'] }}" required>
                                                        </div>
                                                        <div class="col-sm-3">
                                                            <input type="text"
                                                                name="authorizations[{{ $index }}][identity_number]"
                                                                class="form-control" placeholder="رقم الهوية"
                                                                value="{{ $authorization['identity_number'] }}">
                                                        </div>
                                                        <div class="col-sm-3">
                                                            <input type="tel"
                                                                name="authorizations[{{ $index }}][phone]"
                                                                class="form-control" placeholder="رقم الهاتف"
                                                                value="{{ $authorization['phone'] }}">
                                                        </div>
                                                        <div class="col-sm-3">
                                                            <input type="email"
                                                                name="authorizations[{{ $index }}][email]"
                                                                class="form-control" placeholder="البريد الإلكتروني"
                                                                value="{{ $authorization['email'] }}">
                                                        </div>
                                                    </div>
                                                    <button type="button"
                                                        class="btn btn-danger btn-sm mt-2 remove-authorization">
                                                        إزالة
                                                    </button>
                                                </div>
                                            @endforeach
                                        @else
                                            <!-- عرض المفوضين الفعليين من قاعدة البيانات -->
                                            @foreach ($dbAuthorizations as $index => $authorization)
                                                <div class="authorization-item mb-3">
                                                    <div class="row g-3">
                                                        <div class="col-sm-3">
                                                            <input type="text"
                                                                name="authorizations[{{ $index }}][name]"
                                                                class="form-control" placeholder="اسم المفوض"
                                                                value="{{ $authorization->name }}" required>
                                                        </div>
                                                        <div class="col-sm-3">
                                                            <input type="text"
                                                                name="authorizations[{{ $index }}][identity_number]"
                                                                class="form-control" placeholder="رقم الهوية"
                                                                value="{{ $authorization->identity_number }}">
                                                        </div>
                                                        <div class="col-sm-3">
                                                            <input type="tel"
                                                                name="authorizations[{{ $index }}][phone]"
                                                                class="form-control" placeholder="رقم الهاتف"
                                                                value="{{ $authorization->phone }}">
                                                        </div>
                                                        <div class="col-sm-3">
                                                            <input type="email"
                                                                name="authorizations[{{ $index }}][email]"
                                                                class="form-control" placeholder="البريد الإلكتروني"
                                                                value="{{ $authorization->email }}">
                                                        </div>
                                                    </div>
                                                    <button type="button"
                                                        class="btn btn-danger btn-sm mt-2 remove-authorization">
                                                        إزالة
                                                    </button>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <!-- زر إضافة مفوض جديد -->
                                    <button type="button" class="btn btn-primary btn-sm" id="add-authorization">إضافة
                                        مفوض</button>
                                </div>

                                <!-- النبذة -->
                                <div class="col-md-12">
                                    <label for="bio" class="form-label">نبذة</label>
                                    <textarea class="form-control" id="bio" name="bio" rows="3" placeholder="نبذة تعريفية عن الخصم">{{ old('bio', $opponent->bio) }}</textarea>
                                </div>
                            </div>
                            <!-- أزرار الانتقال والخروج -->
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-label-secondary btn-prev">
                                    <i class="ti ti-arrow-left ti-xs me-sm-2 me-0"></i>
                                    <span class="align-middle d-sm-inline-block d-none">السابق</span>
                                </button>
                                <button type="submit" class="btn btn-primary btn-submit">
                                    تحديث
                                </button>
                            </div>
                        </div>

                    </form><!-- end form -->
                </div>
            </div>
        </div>
    </div>
@endsection
