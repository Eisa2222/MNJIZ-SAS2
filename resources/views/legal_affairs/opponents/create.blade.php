@extends('layouts.layoutMaster')

@section('title', 'إضافة خصم ')

@section('breadcrumb')
    <li><a href="#">الشؤون القانونية</a></li>

    <li><a href="{{ route('legal-affairs.opponents.index') }}">الخصوم</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">إضافة خصم </a>
        <i class="ti ti-star favorite-icon" data-page-name="إضافة خصم " data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.scss', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/css/intl-tel.css'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/js/legal-affairs/opponents/create.js', 'resources/assets/vendor/libs/bs-stepper/bs-stepper.js', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js'])
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection
@section('page-script')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"></script>
    @vite(['resources/assets/js/opponents-validation.js', 'resources/assets/js/intl-tel-w.js'])
@endsection


@section('content')
    <div class="row">
        <div class="col-12">
            <div id="wizard-validation" class="bs-stepper mt-2">
                <div class="bs-stepper-header">
                    <div class="step" data-target="#basic-info">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">1</span>
                            <span class="bs-stepper-label mt-1">
                                <span class="bs-stepper-title">معلومات الخصم</span>
                                <span class="bs-stepper-subtitle">إدخال معلومات الخصم الأساسية</span>
                            </span>
                        </button>
                    </div>
                    <div class="line"><i class="ti ti-chevron-right"></i></div>
                    <div class="step" data-target="#additional-info-2">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">2</span>
                            <span class="bs-stepper-label">
                                <span class="bs-stepper-title">معلومات الإتصال</span>
                                <span class="bs-stepper-subtitle">إضافة معلومات الإتصال</span>
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
                    <form id="power-form" action="{{ route('legal-affairs.opponents.store') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
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
                                                    {{ old('type', 'company') == 'company' ? 'checked' : '' }}
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
                                                    {{ old('type', 'individual') == 'individual' ? 'checked' : '' }}
                                                    value="individual">
                                            </label>
                                        </div>
                                    </div>
                                </div>


                                <!-- Row 1: اسم الخصم و رقم الاتصال -->
                                <div class="col-md-6">
                                    <label class="form-label" for="name">الاسم</label>
                                    <input type="text" name="name" id="name" class="form-control"
                                        placeholder="أدخل الاسم" value="{{ old('name') }}" />
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="contact_number">رقم الاتصال</label>
                                    <input type="tel" name="contact_number" id="contact_number" class="form-control"
                                        placeholder="ادخل رقم الاتصال" value="{{ old('contact_number') }}" />
                                    <p class="small" id="message"></p>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="email">البريد الإلكتروني</label>
                                    <input type="email" name="email" id="email" class="form-control"
                                        placeholder="ادخل البريد الإلكتروني" aria-label="الرجاء إدخال بريد الخصم"
                                        value="{{ old('email') }}" />
                                </div>

                                <!-- Row 2: المدينة -->
                                <div class="col-md-6">
                                    <label class="form-label" for="settings_region_id">المدينة</label>
                                    <select class="select2 form-select" id="settings_region_id" name="settings_region_id"
                                        data-placeholder="اختر المدينة">
                                        <option value="">اختر المدينة</option>
                                        @foreach ($settings_regions as $region)
                                            <option value="{{ $region->id }}"
                                                {{ old('settings_region_id') == $region->id ? 'selected' : '' }}>
                                                {{ $region->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>


                            </div>

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
                        <div id="additional-info-2" class="content">
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label class="form-label" for="identity_number">رقم الهوية</label>
                                    <input type="tel" id="identity_number" name="identity_number"
                                        class="form-control" value="{{ old('identity_number') }}" />
                                </div>
                                <!-- رقم السجل التجاري -->
                                <div class="col-md-6" id="commercial-registration-container">
                                    <label class="form-label" for="commercial_registration">رقم السجل
                                        التجاري</label>
                                    <input type="text" name="commercial_registration" id="commercial_registration"
                                        class="form-control" placeholder="ادخل رقم السجل التجاري"
                                        value="{{ old('commercial_registration') }}" />
                                </div>

                                <!-- الرقم الموحد -->
                                <div class="col-md-6" id="unified-number-container">
                                    <label class="form-label" for="unified_number">الرقم الموحد</label>
                                    <input type="tel" name="unified_number" id="unified_number" class="form-control"
                                        placeholder="ادخل الرقم الموحد" value="{{ old('unified_number') }}" />
                                </div>

                                <!-- حقول المفوضين للمؤسسة -->
                                <div class="col-12" id="authorizations-container">
                                    <label class="form-label">المفوضين</label>
                                    <div id="authorizations-wrapper">
                                        <!-- هنا سيتم إضافة حقول المفوضين ديناميكيًا -->
                                        @if (old('authorizations'))
                                            @foreach (old('authorizations') as $index => $authorization)
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
                                                                value="{{ $authorization['identity_number'] }}" required>
                                                        </div>
                                                        <div class="col-sm-3">
                                                            <input type="tel"
                                                                name="authorizations[{{ $index }}][phone]"
                                                                class="form-control" placeholder="رقم الجوال"
                                                                value="{{ $authorization['phone'] }}" required>
                                                        </div>
                                                        <div class="col-sm-3">
                                                            <input type="email"
                                                                name="authorizations[{{ $index }}][email]"
                                                                class="form-control" placeholder="البريد الإلكتروني"
                                                                value="{{ $authorization['email'] }}" required>
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

                                <div class="col-md-12">
                                    <label for="bio" class="form-label">نبذة</label>
                                    <textarea class="form-control " id="bio" name="bio" rows="3" placeholder="نبذة تعريفية عن الخصم">{{ old('bio') }}</textarea>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-label-secondary btn-prev">
                                    <i class="ti ti-arrow-left ti-xs me-sm-2 me-0"></i>
                                    <span class="align-middle d-sm-inline-block d-none">السابق</span>
                                </button>
                                <button type="submit" class="btn btn-primary btn-submit">حفظ</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
