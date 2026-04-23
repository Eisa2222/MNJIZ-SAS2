@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts.layoutFront')

@section('title', 'السياسات و اللوائح')

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
        <!-- Hero Section: Start -->
        <section id="hero-animation">
            <div id="landingHero" class="section-py landing-hero position-relative">
                {{-- <img src="{{ asset('assets/img/front-pages/backgrounds/hero-bg.png') }}" alt="hero background"
                    class="position-absolute top-0 start-50 translate-middle-x object-fit-cover w-100 h-100" data-speed="1" /> --}}
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
                        <h2 class="hero-sub-title h6  small">
                            لضمان بيئة عمل منظمة وآمنة للجميع<br class="d-none d-lg-block" />
                            يرجى الاطلاع والموافقة على اللوائح التالية
                        </h2>
                    </div>
                </div>
            </div>
        </section>
        <!-- Hero: End -->

        <!-- Policies Section: Start -->
        <section id="landingPolicies" class="py-5 bg-body landing-reviews pb-0">
            <div class="container">
                <!-- Alert -->


                <!-- Policies Cards -->
                <div class="row gy-4 mb-5">
                    @foreach ($policies as $index => $policy)
                        <div class="col-lg-6 col-xl-4">
                            <div class="card h-100 policy-card">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title mb-3 text-primary">{{ $policy->name }}</h5>

                                    <!-- Policy Info -->
                                    <div class="mb-3">
                                        <small class="text-muted d-block">
                                            <i class="ti ti-calendar me-1"></i>
                                            تاريخ الإصدار: {{ $policy->created_at->format('Y-m-d') }}
                                        </small>
                                    </div>

                                    <!-- Actions -->
                                    @if ($policy->file_path)
                                        <div class="d-flex gap-2 justify-content-end">
                                            <!-- زر العرض -->
                                            <a href="{{ asset('storage/' . $policy->file_path) }}"
                                                class="btn btn-outline-info btn-sm " target="_blank">
                                                <i class="ti ti-eye me-1"></i>
                                                عرض
                                            </a>

                                            <!-- زر التحميل -->
                                            <a href="{{ asset('storage/' . $policy->file_path) }}"
                                                class="btn btn-outline-primary btn-sm " download>
                                                <i class="ti ti-download me-1"></i>
                                                تحميل
                                            </a>
                                        </div>
                                    @else
                                        <div class="alert alert-warning alert-sm mb-3">
                                            <i class="ti ti-alert-triangle me-1"></i>
                                            <small>لا يوجد ملف مرفق</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Agreement All Section -->
                <div class="row justify-content-center small">
                    <div class="col-lg-8">
                        <div class="card border-primary">
                            <div class="card-body text-center">
                                <div class="mb-4">
                                    <i class="ti ti-shield-check text-primary" style="font-size: 3rem;"></i>
                                </div>
                                <h6 class="card-title text-primary mb-3">الموافقة على جميع السياسات</h6>

                                <div class="form-check d-inline-block mb-3">
                                    <input class="form-check-input" type="checkbox" id="agree_all">
                                    <label class="form-check-label fw-bold" for="agree_all">
                                        أؤكد أنني قرأت وفهمت وأوافق على جميع السياسات المذكورة أعلاه
                                    </label>
                                </div>

                                <div>
                                    <button type="button" class="btn btn-primary btn-sm" id="agree-all-btn" disabled>
                                        <i class="ti ti-shield-check me-2"></i>
                                        موافقة على الجميع والمتابعة
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Policies Section: End -->

        <!-- Footer Section -->
        <section class="py-3 bg-body">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8 text-center">
                        <div class="alert alert-info small">
                            <i class="ti ti-info-circle me-2"></i>
                            <strong>ملاحظة:</strong> بعد الموافقة على جميع السياسات، ستتمكن من الوصول لجميع خدمات
                            النظام
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const agreeAllBtn = document.getElementById('agree-all-btn');
            const agreeAllCheckbox = document.getElementById('agree_all');


            // تفعيل/إلغاء تفعيل زر الموافقة على الجميع
            agreeAllCheckbox.addEventListener('change', function() {
                agreeAllBtn.disabled = !this.checked;
            });


            // زر الموافقة على الجميع
            agreeAllBtn.addEventListener('click', function() {
                Swal.fire({
                    title: 'تأكيد الموافقة على السياسات',
                    text: 'هل تؤكد موافقتك على السياسات ',
                    icon: 'question',
                    showCancelButton: true,
                    showConfirmButton: true,
                    showDenyButton: false,
                    buttonsStyling: false,
                    customClass: {
                        popup: 'custom-popup',
                        title: 'custom-title',
                        text: 'custom-text',
                        confirmButton: 'btn btn-success custom-confirm',
                        cancelButton: 'btn btn-danger custom-cancel'
                    },
                    confirmButtonText: 'تأكيد',
                    cancelButtonText: 'إلغاء',
                    reverseButtons: false,
                }).then((result) => {
                    if (result.isConfirmed) {
                        agreeAllPolicies();
                    }
                });
            });


            // دالة الموافقة على جميع السياسات
            function agreeAllPolicies() {
                // تعديل النص وإظهار مؤشر التحميل
                const originalText = agreeAllBtn.innerHTML;
                agreeAllBtn.innerHTML = '<i class="spinner-border spinner-border-sm me-2"></i> جاري المعالجة...';
                agreeAllBtn.disabled = true;

                fetch('{{ route('policies.agree') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // عرض رسالة نجاح
                            if (data.redirect) {
                                window.location.href = data.redirect;
                            } else {
                                window.location.reload();
                            }
                        } else {
                            // عرض رسالة خطأ
                            Swal.fire({
                                title: 'خطأ!',
                                text: data.message,
                                icon: 'error',
                                buttonsStyling: false,
                                customClass: {
                                    confirmButton: 'btn btn-danger'
                                },
                                confirmButtonText: 'موافق'
                            });

                            // إعادة تفعيل الزر
                            agreeAllBtn.innerHTML = originalText;
                            agreeAllBtn.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);

                        // عرض رسالة خطأ عام
                        Swal.fire({
                            title: 'خطأ!',
                            text: 'حدث خطأ أثناء معالجة طلبك',
                            icon: 'error',
                            buttonsStyling: false,
                            customClass: {
                                confirmButton: 'btn btn-danger'
                            },
                            confirmButtonText: 'موافق'
                        });

                        // إعادة تفعيل الزر
                        agreeAllBtn.innerHTML = originalText;
                        agreeAllBtn.disabled = false;
                    });
            }

        });
    </script>
@endsection
