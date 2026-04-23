@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts.layoutFrontPublic')

@section('title', $employee->raw_name)

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/nouislider/nouislider.scss', 'resources/assets/vendor/libs/swiper/swiper.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('page-style')
    @vite(['resources/css/toastr.css', 'resources/assets/vendor/scss/pages/front-page-landing.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/nouislider/nouislider.js', 'resources/assets/vendor/libs/swiper/swiper.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/front-page-landing.js'])
@endsection

@section('content')
    <div data-bs-spy="scroll" class="scrollspy-example">
        <section id="hero-animation">
            <div id="landingHero" class="section-py landing-hero position-relative">
                <div class="container">
                    <div class="hero-text-box text-center position-relative">
                        <div class="mb-4">
                            <span class="app-brand-logo demo">
                                <img src="{{ App\Helpers\SettingsHelper::get('image') ? asset('storage/' . App\Helpers\SettingsHelper::get('image')) : asset('assets/img/branding/Alburhan-Logo.png') }}"
                                    alt="Logo" height="150">
                            </span>
                        </div>
                        <h1 class="text-primary hero-title display-6 fw-extrabold">
                            {{ App\Helpers\SettingsHelper::get('office_name') }}
                        </h1>
                    </div>
                </div>
            </div>
        </section>


        <section id="landingPolicies" class="py-5 bg-body landing-reviews">
            <div class="container">
                <!-- Employee Header -->
                <div class="py-5">
                    <div class="container">
                        <div class="row justify-content-center text-center">
                            <div class="col-lg-8">
                                <!-- Employee Photo -->
                                <div class="employee-avatar mb-4">
                                    @if ($employee->profile_picture)
                                        <img src="{{ asset('storage/' . $employee->profile_picture) }}"
                                            alt="{{ $employee->raw_name }}">
                                    @else
                                        <div class="avatar-placeholder">
                                            {{ substr($employee->raw_name, 0, 1) }}
                                        </div>
                                    @endif
                                </div>

                                <!-- Employee Name & Position -->
                                <h1 class="display-6 fw-bold mb-3 text-primary">{{ $employee->raw_name ?? 'اسم الموظف' }}
                                </h1>
                                <h4 class="text-muted mb-4">{{ $employee->job_title ?? 'المنصب الوظيفي' }}</h4>

                                <!-- Professional Badge -->
                                <div class="mb-4">
                                    <span class="badge bg-primary rounded-pill px-3 py-2">
                                        <i class="ti ti-briefcase me-1"></i>
                                        عضو فريق {{ App\Helpers\SettingsHelper::get('office_name') }}
                                    </span>
                                </div>

                                <!-- Professional Quote or Description -->
                                <div class="mb-0">
                                    <p class="lead text-muted mb-0">
                                        "نسعى لتقديم أفضل الخدمات المهنية والاستشارية<br>
                                        بأعلى معايير الجودة والاحترافية"
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Information Section -->
                <section class="py-5">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-10">


                                <!-- Professional Information -->
                                <div class="row mt-5">
                                    <div class="col-12">
                                        <div class="professional-info card border-0 shadow-sm">
                                            <div class="card-body p-4">
                                                <div class="row align-items-center">
                                                    <div class="col-md-8">


                                                        <div class="row">
                                                            <!-- المنصب الوظيفي -->
                                                            @if ($employee->job_title)
                                                                <div class="col-sm-6 mb-3">
                                                                    <div class="info-item-simple">
                                                                        <small class="text-muted d-block">المنصب
                                                                            الوظيفي</small>
                                                                        <span
                                                                            class="fw-semibold">{{ $employee->job_title }}</span>
                                                                    </div>
                                                                </div>
                                                            @endif

                                                            <!-- نوع الترخيص المهني -->
                                                            @if ($employee->license_type && $employee->license_type->value !== 'no_license')
                                                                <div class="col-sm-6 mb-3">
                                                                    <div class="info-item-simple">
                                                                        <small class="text-muted d-block">نوع
                                                                            الترخيص</small>
                                                                        <span class="fw-semibold">
                                                                            {{ $employee->license_type->label() }}
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            @endif

                                                            <!-- المؤهل العلمي -->
                                                            @if ($employee->qualification_degree)
                                                                <div class="col-sm-6 mb-3">
                                                                    <div class="info-item-simple">
                                                                        <small class="text-muted d-block">المؤهل
                                                                            العلمي</small>
                                                                        <span class="fw-semibold">
                                                                            {{ $employee->qualification_degree->label() }}
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            @endif

                                                            <!-- مجال المعرفة -->
                                                            @if ($employee->knowledge_area)
                                                                <div class="col-sm-6 mb-3">
                                                                    <div class="info-item-simple">
                                                                        <small class="text-muted d-block">مجال
                                                                            التخصص</small>
                                                                        <span class="fw-semibold">
                                                                            {{ $employee->knowledge_area->label() }}
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            @endif

                                                            <!-- عضو في الفريق منذ -->
                                                            @if ($employee->created_at)
                                                                <div class="col-sm-6 mb-3">
                                                                    <div class="info-item-simple">
                                                                        <small class="text-muted d-block">عضو في الفريق
                                                                            منذ</small>
                                                                        <span
                                                                            class="fw-semibold">{{ $employee->created_at->format('Y') }}</span>
                                                                    </div>
                                                                </div>
                                                            @endif


                                                        </div>


                                                    </div>

                                                    <div class="col-md-4 text-center">
                                                        <div class="company-logo-section">
                                                            <img src="{{ App\Helpers\SettingsHelper::get('image') ? asset('storage/' . App\Helpers\SettingsHelper::get('image')) : asset('assets/img/branding/Alburhan-Logo.png') }}"
                                                                alt="Company Logo" class="company-logo mb-2">
                                                            <small
                                                                class="text-muted d-block">{{ App\Helpers\SettingsHelper::get('office_name') }}</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="qualifications-card card border-0 shadow-sm">
                                            <div class="card-body p-4">
                                                <h5 class="card-title text-primary mb-4">
                                                    <i class="ti ti-award me-2"></i>
                                                    المؤهلات والتخصصات
                                                </h5>

                                                <div class="row">
                                                    @if ($employee->qualification_degree)
                                                        <div class="col-md-4 mb-3">
                                                            <div
                                                                class="qualification-item text-center p-3 bg-light rounded">
                                                                <i class="ti ti-school text-primary mb-2"
                                                                    style="font-size: 2rem;"></i>
                                                                <h6 class="mb-1">المؤهل العلمي</h6>
                                                                <small class="text-muted">
                                                                    {{ $employee->qualification_degree->label() }}
                                                                </small>
                                                            </div>
                                                        </div>
                                                    @endif

                                                    @if ($employee->license_type && $employee->license_type->value !== 'no_license')
                                                        <div class="col-md-4 mb-3">
                                                            <div
                                                                class="qualification-item text-center p-3 bg-light rounded">
                                                                <i class="ti ti-certificate text-success mb-2"
                                                                    style="font-size: 2rem;"></i>
                                                                <h6 class="mb-1">الترخيص المهني</h6>
                                                                <small class="text-muted">
                                                                    {{ $employee->license_type->label() }}

                                                                </small>
                                                            </div>
                                                        </div>
                                                    @endif

                                                    @if ($employee->knowledge_area)
                                                        <div class="col-md-4 mb-3">
                                                            <div
                                                                class="qualification-item text-center p-3 bg-light rounded">
                                                                <i class="ti ti-brain text-info mb-2"
                                                                    style="font-size: 2rem;"></i>
                                                                <h6 class="mb-1">مجال التخصص</h6>
                                                                <small class="text-muted">
                                                                    {{ $employee->knowledge_area->label() }}
                                                                </small>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>



                                <!-- Call to Action -->
                                <div class="text-center mt-5">
                                    <div class="cta-section p-4 bg-light rounded-3">
                                        <h4 class="text-primary mb-3">هل تحتاج للمساعدة؟</h4>
                                        <p class="text-muted mb-4">
                                            نحن هنا لخدمتكم وتقديم أفضل الاستشارات والحلول المهنية
                                        </p>

                                        @if ($employee->mobile)
                                            <div class="quick-contact-buttons">
                                                <a href="tel:{{ $employee->mobile }}" class="btn btn-primary me-3">
                                                    <i class="ti ti-phone me-1"></i>
                                                    اتصل الآن
                                                </a>
                                                <a href="https://wa.me/{{ str_replace(['+', ' ', '-'], '', $employee->mobile) }}"
                                                    class="btn btn-outline-success" target="_blank">
                                                    <i class="ti ti-brand-whatsapp me-1"></i>
                                                    واتس آب
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </section>

        <!-- تحسينات بسيطة وأنيقة -->

        <!-- تحسين العرض مع إضافة shadows ناعمة -->
        <style>
            /* 1. تحسين الصور والأفاتار */
            .employee-avatar {
                width: 150px;
                height: 150px;
                border-radius: 50%;
                border: 4px solid rgba(var(--bs-primary-rgb), 0.15);
                margin: 0 auto;
                position: relative;
                overflow: hidden;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
                transition: all 0.3s ease;
            }


            .employee-avatar img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                transition: transform 0.3s ease;
            }

            .employee-avatar:hover img {
                transform: scale(1.05);
            }

            .avatar-placeholder {
                width: 100%;
                height: 100%;
                background: linear-gradient(135deg, var(--bs-primary) 0%, var(--bs-secondary) 100%);
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 48px;
                font-weight: bold;
                color: white;
            }

            /* 2. تحسين الكروت */
            .info-card,
            .professional-info,
            .qualifications-card {
                border-radius: 16px !important;
                border: none !important;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08) !important;
                transition: all 0.3s ease;
            }


            /* 3. تحسين الأزرار */
            .btn {
                border-radius: 12px;
                font-weight: 600;
                padding: 12px 24px;
                transition: all 0.3s ease;
                border: none;
            }

            .btn-primary {
                background: linear-gradient(135deg, var(--bs-primary) 0%, #caa349 100%);
                box-shadow: 0 4px 15px rgba(var(--bs-primary-rgb), 0.3);
            }

            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(var(--bs-primary-rgb), 0.4);
                background: linear-gradient(135deg, #caa349 0%, var(--bs-primary) 100%);
            }


            .btn-outline-success {
                border: 2px solid #25d366;
                color: #25d366;
                background: transparent;
            }


            /* 4. تحسين Typography */
            .display-6 {
                font-weight: 800;
                letter-spacing: -0.5px;
            }

            .text-primary {
                background: linear-gradient(135deg, var(--bs-primary) 0%, #caa349 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }

            /* 5. تحسين الـ badges */
            .badge {
                padding: 8px 16px;
                border-radius: 20px;
                font-weight: 600;
                font-size: 0.85rem;
            }

            .badge.bg-primary {
                background: linear-gradient(135deg, var(--bs-primary) 0%, #caa349 100%) !important;
                box-shadow: 0 2px 8px rgba(var(--bs-primary-rgb), 0.3);
            }

            .qualification-item:hover i {
                transform: scale(1.1);
            }

            /* 7. تحسين info items */
            .info-item-simple {
                padding: 1rem 0;
                border-bottom: 1px solid #f0f0f0;
                transition: all 0.3s ease;
            }



            .info-item-simple:last-child {
                border-bottom: none;
            }

            .info-label {
                font-size: 0.85rem;
                color: #666;
                font-weight: 500;
                margin-bottom: 4px;
            }

            .info-value {
                font-size: 1rem;
                color: #333;
                font-weight: 600;
            }

            /* 8. تحسين CTA section */
            .cta-section {
                border: 2px dashed rgba(var(--bs-primary-rgb), 0.2);
                border-radius: 16px;
                background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
                position: relative;
                overflow: hidden;
            }



            @keyframes subtle-move {
                0% {
                    transform: translate(-50%, -50%) rotate(0deg);
                }

                100% {
                    transform: translate(-50%, -50%) rotate(360deg);
                }
            }

            /* 9. تحسين company logo */
            .company-logo {
                max-width: 80px;
                max-height: 80px;
                object-fit: contain;
                filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.1));
                transition: all 0.3s ease;
            }


            /* 10. تحسين الانتقالات العامة */
            * {
                scroll-behavior: smooth;
            }

            .card {
                backdrop-filter: blur(10px);
                background: rgba(255, 255, 255, 0.95);
            }

            /* 11. تحسين المسافات */
            .section-py {
                padding: 4rem 0;
            }

            .hero-tagline {
                font-size: 1.1rem;
                font-weight: 500;
                color: #666;
                margin-top: 1rem;
                line-height: 1.6;
            }

            /* 12. تحسين responsive */
            @media (max-width: 768px) {
                .employee-avatar {
                    width: 120px;
                    height: 120px;
                }

                .avatar-placeholder {
                    font-size: 36px;
                }

                .btn {
                    padding: 10px 20px;
                    font-size: 0.9rem;
                }

                .qualification-item {
                    margin-bottom: 1rem;
                }


            }

            /* 13. تحسين focus states للـ accessibility */
            .btn:focus,
            .card:focus {
                outline: 2px solid var(--bs-primary);
                outline-offset: 2px;
            }

            /* 14. تحسين loading states */
            .card-body {
                position: relative;
            }



            /* 15. تحسين ألوان النصوص */
            h1,
            h2,
            h3,
            h4,
            h5,
            h6 {
                color: #2c3e50;
                font-weight: 700;
            }

            p,
            .text-muted {
                color: #6c757d;
                line-height: 1.6;
            }

            /* إضافة تأثير subtle للعناصر التفاعلية */
            [onclick],
            button,
            .btn,
            a {
                cursor: pointer;
                user-select: none;
            }

            [onclick]:active,
            button:active,
            .btn:active {
                transform: scale(0.98);
            }
        </style>


    </div>


@endsection
