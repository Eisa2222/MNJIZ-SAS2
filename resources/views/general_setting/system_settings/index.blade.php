@extends('layouts.layoutMaster')

@section('title', 'إعدادات النظام')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">إعدادات النظام
        </a>
        <i class="ti ti-star favorite-icon" data-page-name="إعدادات النظام" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

<!-- Vendor Styles -->
@section('vendor-style')
    @vite(['resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/css/tab.css'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/js/tab.js', 'resources/assets/js/system-settings.js'])
@endsection
@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('content')



    <div class="row">
        <div class="col-12 col-md-3 col-xl-3">
            <div>
                <ul class="nav nav-tabs justify-content-between border-bottom-0 flex-column">
                    <li class="card rounded-0 mb-3 nav-item shadow-none">
                        <a class="py-3 nav-link fw-bold active m-0" data-bs-toggle="tab" href="#generalSettings">
                            <!-- أيقونة الإعدادات العامة -->
                            <i class="ti ti-settings ti-sm me-1_5"></i>
                            <span class="align-middle">الإعدادات العامة</span>
                        </a>
                    </li>

                    <li class="card rounded-0 mb-3 nav-item shadow-none">
                        <a class="py-3 nav-link fw-bold m-0" data-bs-toggle="tab" href="#attachmentsSettings">
                            <!-- أيقونة الإعدادات العامة -->
                            <i class="ti ti-paperclip ti-sm me-1_5"></i>
                            <span class="align-middle"> مرفقات المنشأة </span>
                        </a>
                    </li>

                    <li class="card rounded-0 mb-3 nav-item shadow-none">
                        <a class="py-3 nav-link fw-bold m-0" data-bs-toggle="tab" href="#printSettings">
                            <!-- أيقونة إعدادات الطباعة -->
                            <i class="ti ti-printer ti-sm me-1_5"></i>
                            <span class="align-middle">الإعدادات الخاصة بالطباعة</span>
                        </a>
                    </li>
                    <li class="card rounded-0 mb-3 nav-item shadow-none">
                        <a class="py-3 nav-link fw-bold m-0" data-bs-toggle="tab" href="#microsoft">
                            <!-- أيقونة إعدادات Microsoft Graph -->
                            <i class="ti ti-brand-windows ti-sm me-1_5"></i>
                            <span class="align-middle">إعدادات Microsoft Graph</span>
                        </a>
                    </li>
                    <li class="card rounded-0 mb-3 nav-item shadow-none">
                        <a class="py-3 nav-link fw-bold m-0" data-bs-toggle="tab" href="#whatsapp">
                            <!-- أيقونة إعدادات WhatsApp -->
                            <i class="ti ti-brand-whatsapp ti-sm me-1_5"></i>
                            <span class="align-middle">إعدادات Whatsapp</span>
                        </a>
                    </li>
                    <li class="card rounded-0 mb-3 nav-item shadow-none">
                        <a class="py-3 nav-link fw-bold m-0" data-bs-toggle="tab" href="#SmsSettings">
                            <!-- أيقونة إعدادات SMS -->
                            <i class="ti ti-message ti-sm me-1_5"></i>
                            <span class="align-middle">إعدادات SMS</span>
                        </a>
                    </li>
                    <li class="card rounded-0 mb-3 nav-item shadow-none">
                        <a class="py-3 nav-link fw-bold m-0" data-bs-toggle="tab" href="#chatGpt">
                            <!-- أيقونة إعدادات OpenAI  -->
                            <i class="ti ti-robot ti-sm me-1_5"></i>
                            <span class="align-middle">إعدادات OpenAI </span>
                        </a>
                    </li>

                    <li class="card rounded-0 mb-3 nav-item shadow-none">
                        <a class="py-3 nav-link fw-bold m-0" data-bs-toggle="tab" href="#biometric">
                            <i class="ti ti-scan ti-sm me-1_5"></i>
                            <span class="align-middle">إعدادات جهاز البصمة</span>
                        </a>
                    </li>

                    <li class="card rounded-0 mb-3 nav-item shadow-none">
                        <a class="py-3 nav-link fw-bold m-0" data-bs-toggle="tab" href="#human_resources">
                            <i class="ti ti-users ti-sm me-1_5"></i>
                            <span class="align-middle">إعدادات الموارد البشرية</span>
                        </a>
                    </li>



                    <li class="card rounded-0 mb-3 nav-item shadow-none">
                        <a class="py-3 nav-link fw-bold m-0" data-bs-toggle="tab" href="#qoyodSettings">
                            <i class="ti ti-adjustments-code ti-sm me-1_5"></i>
                            <span class="align-middle">إعدادات نظام قيود </span>
                        </a>
                    </li>

                    <li class="card rounded-0 mb-3 nav-item shadow-none">
                        <a class="py-3 nav-link fw-bold m-0" data-bs-toggle="tab" href="#supportSettings">
                            <i class="ti ti-headset ti-sm me-1_5"></i>
                            <span class="align-middle">إعدادات الدعم الفني</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="col-12 col-md-9 col-xl-9 order-1 order-md-0 mb-5">
            <div class="tab-content p-0">
                @include('general_setting.system_settings.partials.general_settings')
                @include('general_setting.system_settings.partials.attachments_settings')
                @include('general_setting.system_settings.partials.print_settings')
                @include('general_setting.system_settings.partials.microsoft_settings')
                @include('general_setting.system_settings.partials.whatsapp_settings')
                @include('general_setting.system_settings.partials.sms_settings')
                @include('general_setting.system_settings.partials.openai_settings')
                @include('general_setting.system_settings.partials.biostation_settings')
                @include('general_setting.system_settings.partials.human_resources_settings')
                @include('general_setting.system_settings.partials.qoyod_settings')
                @include('general_setting.system_settings.partials.support_settings')
            </div>
        </div>
    </div>

@endsection
