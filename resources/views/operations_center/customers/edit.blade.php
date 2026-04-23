@extends('layouts.layoutMaster')

@section('title', 'تعديل عميل')

@section('breadcrumb')
    <li><a href="#"> مركز العمليات </a></li>

    <li><a href="{{ route('operations-center.customers.index') }}">العملاء</a></li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">تعديل بيانات العميل

        </a>
        <i class="ti ti-star favorite-icon" data-page-name="تعديل بيانات العميل" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

<!-- Vendor Styles -->
@section('vendor-style')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.scss', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/css/intl-tel.css'])
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
    @vite(['resources/assets/js/form-wizard-numbered.js', 'resources/assets/js/form-wizard-validation-customer.js', 'resources/assets/js/intl-tel-w.js', 'resources/assets/js/customers.js'])

@endsection

@section('content')
    <div class="row">
        <div class="col-12 mb-6">
            <div id="wizard-validation" class="bs-stepper mt-2">
                <div class="bs-stepper-header">
                    <!-- الخطوة 1 -->
                    <div class="step" data-target="#account-details-validation">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">1</span>
                            <span class="bs-stepper-label mt-1">
                                <span class="bs-stepper-title">معلومات العميل</span>
                                <span class="bs-stepper-subtitle">تعديل المعلومات الأساسية</span>
                            </span>
                        </button>
                    </div>
                    <div class="line"><i class="ti ti-chevron-right"></i></div>
                    <!-- الخطوة 2 -->
                    <div class="step" data-target="#personal-info-validation">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">2</span>
                            <span class="bs-stepper-label">
                                <span class="bs-stepper-title">معلومات الإتصال</span>
                                <span class="bs-stepper-subtitle">تعديل معلومات الإتصال</span>
                            </span>
                        </button>
                    </div>
                    <div class="line"><i class="ti ti-chevron-right"></i></div>
                    <!-- الخطوة 3 -->
                    <div class="step" data-target="#social-links-validation">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">3</span>
                            <span class="bs-stepper-label">
                                <span class="bs-stepper-title">معلومات إضافية</span>
                                <span class="bs-stepper-subtitle">تعديل المعلومات الإضافية</span>
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

                    <form id="wizard-validation-form"
                        action="{{ route('operations-center.customers.update', $customer->id) }}" method="POST"
                        onSubmit="return false" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Account Details -->
                        <div id="account-details-validation" class="content">
                            <div class="row g-3">

                                <!-- حقل اختيار نوع العميل -->
                                <div class="row d-flex flex-row-reverse justify-content-center">
                                    <label class="form-label text-center my-5">نوع العميل</label>

                                    <div class="col-md-3">
                                        <div class="form-check custom-option custom-option-icon">
                                            <label class="form-check-label custom-option-content" for="company">
                                                <span class="custom-option-body">
                                                    <i class="ti ti-user-shield"></i>
                                                    <span class="custom-option-title"> شخصية اعتبارية </span>
                                                    <small>للشركات والمؤسسات و الجهات الحكومية</small>
                                                </span>
                                                <input id="company" name="customer_type" class="form-check-input"
                                                    type="radio"
                                                    {{ old('customer_type', $customer->customer_type->value) == 'company' ? 'checked' : '' }}
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
                                                <input id="individual" name="customer_type" class="form-check-input"
                                                    type="radio"
                                                    {{ old('customer_type', $customer->customer_type->value) == 'individual' ? 'checked' : '' }}
                                                    value="individual">
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 1: اسم العميل و الكنية/رقم السجل التجاري -->
                                <div class="col-sm-6">
                                    <label class="form-label" for="name">الإسم </label>
                                    <input type="text" name="name" id="name" class="form-control"
                                        placeholder="ادخل الإسم" value="{{ old('name', $customer->name) }}" />
                                </div>

                                <div class="col-sm-6" id="title-container">
                                    <label class="form-label" for="title">الكنية</label>
                                    <input type="text" name="title" id="title" class="form-control"
                                        placeholder="ادخل الكنية" value="{{ old('title', $customer->title) }}" />
                                </div>

                                <div class="col-sm-6" id="commercial-registration-container" style="display: none;">
                                    <label class="form-label" for="commercial_registration_number">رقم السجل
                                        التجاري</label>
                                    <input type="text" name="commercial_registration_number"
                                        id="commercial_registration_number" class="form-control numeric-only"
                                        minlength="10" maxlength="10" placeholder="ادخل رقم السجل التجاري"
                                        value="{{ old('commercial_registration_number', $customer->commercial_registration_number) }}" />
                                </div>

                                <!-- Row 2: الجنسية و حالة العميل / الرقم الموحد -->
                                <div class="col-sm-6" id="nationality-container">
                                    <label class="form-label" for="nationality_id">الجنسية</label>
                                    <select class="select2 form-select" id="nationality_id" name="nationality_id"
                                        data-placeholder="اختر الجنسية">
                                        <option value="">اختر الجنسية</option>
                                        @foreach ($countries as $country)
                                            <option value="{{ $country->id }}"
                                                {{ old('nationality_id', $customer->nationality_id) == $country->id ? 'selected' : '' }}>
                                                {{ $country->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-sm-6" id="status-container">
                                    <label class="form-label" for="status_id">حالة العميل</label>
                                    <small class="text-danger">
                                        سيتم ارسال استبيان رضا العملاء تلقائيا للعميل عند اختيار حالة "متعاقد"
                                    </small>
                                    <select class="select2 form-select" id="status_id" name="status_id"
                                        data-placeholder="اختر حالة العميل">
                                        <option value="">اختر حالة العميل</option>
                                        @foreach ($statuses as $status)
                                            <option value="{{ $status->id }}"
                                                {{ old('status_id', $customer->status_id) == $status->id ? 'selected' : '' }}>
                                                {{ $status->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-sm-6">
                                    <label for="department_id" class="form-label"> قسم العميل</label>
                                    <select id="department_id" name="department_id" class="form-select select2"
                                        data-placeholder="اختر  قسم العميل">
                                        <option value=""></option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}"
                                                {{ old('department_id', $customer->department_id) == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-sm-6" id="unified-number-container" style="display: none;">
                                    <label class="form-label" for="unified_number">الرقم الموحد</label>
                                    <input type="text" name="unified_number" id="unified_number"
                                        class="form-control numeric-only" minlength="10" maxlength="10"
                                        placeholder="ادخل الرقم الموحد"
                                        value="{{ old('unified_number', $customer->unified_number) }}" />
                                </div>

                                <div class="col-12 d-flex justify-content-between">
                                    <button class="btn btn-label-secondary btn-prev" disabled>
                                        <i class="ti ti-arrow-left ti-xs me-sm-2 me-0"></i>
                                        <span class="align-middle d-sm-inline-block d-none">السابق</span>
                                    </button>
                                    <button class="btn btn-primary btn-next">
                                        <span class="align-middle d-sm-inline-block d-none me-sm-2">التالي</span>
                                        <i class="ti ti-arrow-right ti-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- Personal Info -->
                        <div id="personal-info-validation" class="content">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="form-label" for="contact_number">رقم الاتصال</label>
                                    <input type="tel" name="contact_number" id="contact_number" class="form-control"
                                        placeholder="ادخل رقم الاتصال"
                                        value="{{ old('contact_number', $customer->contact_number) }}" />
                                    <p class="small" id="message"></p>
                                </div>

                                <div class="col-sm-6">
                                    <label class="form-label" for="email">البريد الإلكتروني</label>
                                    <input type="email" name="email" id="email" class="form-control"
                                        placeholder="ادخل البريد الإلكتروني" aria-label="الرجاء إدخال بريد العميل"
                                        value="{{ old('email', $customer->email) }}" />
                                </div>

                                <div class="col-sm-6">
                                    <label class="form-label" for="address">المدينة</label>
                                    <select class="select2 form-select" id="address" name="address"
                                        data-placeholder="اختر المدينة">
                                        <option value="">اختر المدينة</option>
                                        @foreach ($regions as $region)
                                            <option value="{{ $region->id }}"
                                                {{ old('address', $customer->address) == $region->id ? 'selected' : '' }}>
                                                {{ $region->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- حقل السجل المدني للفرد -->
                                <div class="col-sm-6" id="civil-registry-field">
                                    <label class="form-label" for="civil_registry_number">السجل المدني</label>
                                    <input type="text" name="civil_registry_number" minlength="10" maxlength="10"
                                        id="civil_registry_number" class="form-control numeric-only"
                                        placeholder="ادخل السجل المدني "
                                        value="{{ old('civil_registry_number', $customer->civil_registry_number) }}" />
                                </div>

                                <!-- المفوضين (للمؤسسة) -->
                                <div class="col-12" id="authorizations-container" style="display: none;">
                                    <label class="form-label">المفوضين</label>
                                    <div id="authorizations-wrapper">
                                        @php
                                            $oldAuthorizations = old('authorizations');
                                            $dbAuthorizations = $customer->authorizations; // نفترض لديك علاقة في المودل
                                        @endphp

                                        @if ($oldAuthorizations)
                                            @foreach ($oldAuthorizations as $index => $authorization)
                                                <div class="authorization-item mb-3">
                                                    <div class="row g-3">
                                                        <div class="col-md-3">
                                                            <input type="text"
                                                                name="authorizations[{{ $index }}][name]"
                                                                class="form-control" placeholder="اسم المفوض"
                                                                value="{{ $authorization['name'] }}">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <input type="text"
                                                                name="authorizations[{{ $index }}][id_number]"
                                                                class="form-control" placeholder="رقم الهوية"
                                                                value="{{ $authorization['id_number'] }}">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <input type="text"
                                                                name="authorizations[{{ $index }}][phone]"
                                                                class="form-control" placeholder="رقم الهاتف"
                                                                value="{{ $authorization['phone'] }}">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <input type="email"
                                                                name="authorizations[{{ $index }}][email]"
                                                                class="form-control" placeholder="البريد الإلكتروني"
                                                                value="{{ $authorization['email'] }}">
                                                        </div>
                                                    </div>
                                                    <button type="button"
                                                        class="btn btn-danger btn-sm mt-2 remove-authorization">إزالة</button>
                                                </div>
                                            @endforeach
                                        @else
                                            @foreach ($dbAuthorizations as $index => $authorization)
                                                <div class="authorization-item mb-3">
                                                    <div class="row g-3">
                                                        <div class="col-md-3">
                                                            <input type="text"
                                                                name="authorizations[{{ $index }}][name]"
                                                                class="form-control" placeholder="اسم المفوض"
                                                                value="{{ $authorization->name }}">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <input type="text"
                                                                name="authorizations[{{ $index }}][id_number]"
                                                                class="form-control" placeholder="رقم الهوية"
                                                                value="{{ $authorization->id_number }}">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <input type="text"
                                                                name="authorizations[{{ $index }}][phone]"
                                                                class="form-control" placeholder="رقم الهاتف"
                                                                value="{{ $authorization->phone }}">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <input type="email"
                                                                name="authorizations[{{ $index }}][email]"
                                                                class="form-control" placeholder="البريد الإلكتروني"
                                                                value="{{ $authorization->email }}">
                                                        </div>
                                                    </div>
                                                    <button type="button"
                                                        class="btn btn-danger btn-sm mt-2 remove-authorization">إزالة</button>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <button type="button" class="btn btn-primary btn-sm" id="add-authorization">إضافة
                                        مفوض</button>
                                </div>

                                <div class="col-12 d-flex justify-content-between">
                                    <button class="btn btn-label-secondary btn-prev">
                                        <i class="ti ti-arrow-left ti-xs me-sm-2 me-0"></i>
                                        <span class="align-middle d-sm-inline-block d-none">السابق</span>
                                    </button>
                                    <button class="btn btn-primary btn-next">
                                        <span class="align-middle d-sm-inline-block d-none me-sm-2">التالي</span>
                                        <i class="ti ti-arrow-right ti-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- Social Links -->
                        <div id="social-links-validation" class="content">
                            <div class="row g-3">
                                <!-- مسؤول العلاقات -->
                                <div class="col-sm-6">
                                    <label class="form-label" for="relationship_manager_id">مسؤول العلاقات</label>
                                    <select class="select2 form-select" id="relationship_manager_id"
                                        name="relationship_manager_id" data-placeholder="اختر مسؤول العلاقات">
                                        <option value="">اختر مسؤول العلاقات</option>
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->id }}"
                                                {{ old('relationship_manager_id', $customer->relationship_manager_id) == $employee->id ? 'selected' : '' }}>
                                                {{ $employee->nickname ?? $employee->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label" for="marketing_channel_id">قناة التسويق</label>
                                    <select class="select2 form-select" id="marketing_channel_id"
                                        name="marketing_channel_id" data-placeholder="اختر قناة التسويق">
                                        <option value="">اختر قناة التسويق</option>
                                        @foreach ($marketingChannels as $channel)
                                            <option value="{{ $channel->id }}"
                                                {{ old('marketing_channel_id', $customer->marketing_channel_id) == $channel->id ? 'selected' : '' }}>
                                                {{ $channel->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- قناة التسويق التفصيلية (حقل الموارد البشرية) -->
                                <div class="col-sm-6" id="detailedMarketingChannelContainer" style="display: none;">
                                    <label class="form-label" for="detailed_marketing_channel_id">قناة التسويق
                                        التفصيلية</label>
                                    <select class="select2 form-select" id="detailed_marketing_channel_id"
                                        name="detailed_marketing_channel_id"
                                        data-placeholder="اختر قناة التسويق التفصيلية">
                                        <option value="">اختر قناة التسويق التفصيلية</option>
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->id }}"
                                                {{ old('detailed_marketing_channel_id', $customer->detailed_marketing_channel_id) == $employee->id ? 'selected' : '' }}>
                                                {{ $employee->nickname ?? $employee->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div id="detailedMarketingChannelError" style="color:red; display:none;">يرجى ملء حقل
                                        قناة التسويق التفصيلية</div>
                                </div>
                                <!-- قائمة العملاء -->
                                <div class="col-sm-6" id="clientsContainer" style="display: none;">
                                    <label class="form-label" for="parent_customer_id">العملاء</label>
                                    <select class="select2 form-select" id="parent_customer_id" name="parent_customer_id"
                                        data-placeholder="اختر العميل">
                                        <option value="">اختر العميل</option>
                                        @foreach ($customers as $customerID)
                                            <option value="{{ $customerID->id }}"
                                                {{ old('parent_customer_id', $customer->parent_customer_id) == $customerID->id ? 'selected' : '' }}>
                                                {{ $customerID->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- قائمة مواقع التواصل الاجتماعي -->
                                <div class="col-sm-6" id="socialMediaContainer" style="display: none;">
                                    <label class="form-label" for="social_media_id">مواقع التواصل الاجتماعي</label>
                                    <select class="select2 form-select" id="social_media_id" name="social_media_id"
                                        data-placeholder="اختر موقع التواصل الاجتماعي">
                                        <option value="">اختر موقع التواصل الاجتماعي</option>
                                        @foreach ($socials as $social)
                                            <option value="{{ $social->id }}"
                                                {{ old('social_media_id', $customer->social_media_id) == $social->id ? 'selected' : '' }}>
                                                {{ $social->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- القطاع -->
                                <div class="col-sm-6">
                                    <label class="form-label" for="sector_id">القطاع</label>
                                    <select class="select2 form-select" id="sector_id" name="sector_id"
                                        data-placeholder="اختر القطاع">
                                        <option value="">اختر القطاع</option>
                                        @foreach ($sectors as $sector)
                                            <option value="{{ $sector->id }}"
                                                {{ old('sector_id', $customer->sector_id) == $sector->id ? 'selected' : '' }}>
                                                {{ $sector->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 d-flex justify-content-between">
                                    <button class="btn btn-label-secondary btn-prev"> <i
                                            class="ti ti-arrow-left ti-xs me-sm-2 me-0"></i>
                                        <span class="align-middle d-sm-inline-block d-none">السابق</span>
                                    </button>
                                    <button class="btn btn-primary btn-next btn-submit">حفظ</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <hr class="container-m-nx mb-12">
@endsection
