@extends('layouts.layoutMaster')

@section('title', 'تفاصيل الدعوى')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss'])
    <link href="{{ asset('assets/hijri-date/css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet" />
@endsection

@section('page-style')
    @vite(['resources/assets/vendor/scss/pages/page-user-view.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/app-user-view.js', 'resources/assets/js/app-user-view-account.js', 'resources/assets/js/pages-profile.js', 'resources/assets/js/app-academy-dashboard.js'])

    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.1/moment.min.js"></script>
    <script>
        window.moment = moment;

        function loadHijriDatePicker() {
            // تحميل مكتبة التقويم الهجري بعد التأكد من تحميل مكتبة moment.js
            if (typeof window.moment === 'undefined') {
                console.error('Moment.js is not loaded. Please check the script path.');
                return;
            }

            var script = document.createElement('script');
            script.src = "{{ asset('assets/hijri-date/js/bootstrap-hijri-datetimepickermin.js') }}";
            document.head.appendChild(script);

            // بعد تحميل مكتبة التقويم الهجري، تهيئة التقويم
            script.onload = function() {
                initializeHijriPicker(); // استدعاء تهيئة التقويم
            };
            script.onerror = function() {
                console.error('Failed to load Hijri Datepicker library.');
            };
        }

        function initializeHijriPicker() {
            $(document).ready(function() {
                $(".hijri-picker").hijriDatePicker({
                    hijri: true,
                    showSwitcher: true,
                    useCurrent: false,
                    showClear: true,
                    showTodayButton: true,
                    showClose: true,
                    todayBtn: true,
                    todayHighlight: true,
                    minDate: moment().startOf('day'), // منع اختيار تواريخ قديمة قبل اليوم
                    // format: 'iYYYY-iMM-iDD'
                });
            });
        }

        window.addEventListener('DOMContentLoaded', loadHijriDatePicker);
    </script>
@endsection

@section('content')
    <div class="row g-6 ">
        <!-- اسم الدعوى -->
        <div class="col-12 col-md-3 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-center">
                        <div class="content-center text-truncate">
                            <h5 class="mb-1">
                                رقم الدعوى
                            </h5>
                            <span class="fw-bold text-primary"> {{ $lawsuit->lawsuit_number }}</span>
                        </div>
                        {{-- <span class="badge bg-label-primary rounded-circle p-2">
                            <i class="ti ti-hash ti-lg"></i>
                        </span> --}}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-9 col-xl-9">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row g-2">
                        <!-- تصنيف الدعوى -->

                        <!-- رقم الدعوى -->
                        <div class="col-md-4">

                            <div class="d-flex align-items-center justify-content-between">
                                <div class="content-left text-truncate">
                                    <h5 class="mb-1">
                                        تصنيف الدعوى
                                    </h5>
                                    <span class="fw-bold text-primary">{{ $lawsuit->lawsuit_type->name }}</span>
                                </div>
                                {{-- <span class="badge bg-label-primary rounded-circle p-2">
                                    <i class="ti ti-hash ti-lg"></i>
                                </span> --}}
                            </div>

                        </div>

                        <!-- نوع الدعوى -->
                        <div class="col-md-4">

                            <div class="d-flex align-items-center justify-content-between">
                                <div class="content-left text-truncate">
                                    <h5 class="mb-1">
                                        تاريخ الدعوى
                                    </h5>
                                    <span class="fw-bold text-primary"> {{ $lawsuit->hijri_created }}</span>
                                </div>
                                {{-- <span class="badge bg-label-info rounded-circle p-2">
                                    <i class="ti ti-building ti-lg"></i>
                                </span> --}}

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Navigation Tabs -->
    <div class="row mb-4">

    </div>
    <!-- /Navigation Tabs -->

    <div class="row mt-5">

        <div class="col-12 col-md-3 col-xl-3">
            <div>
                <ul class="nav nav-tabs justify-content-between border-bottom-0 flex-column">
                    <li
                        class="card rounded-0 mb-3 nav-item  shadow-none @if (Route::is('lawsuit_section.lawsuit_subject')) border-end-custom border-primary @endif">
                        <a class="py-3 nav-link fw-bold @if (Route::is('lawsuit_section.lawsuit_subject')) text-primary @endif m-0"
                            href="{{ route('lawsuit_section.lawsuit_subject', $lawsuit->id) }}">
                            <i class="ti ti-book ti-sm me-1_5"></i>
                            <span>موضوع الدعوى</span>
                        </a>
                    </li>

                    <li
                        class="card rounded-0  mb-3 nav-item shadow-none @if (Route::is('lawsuits.memos.index')) border-end-custom border-primary @endif">
                        <a class="py-3 nav-link fw-bold @if (Route::is('lawsuits.memos.index')) text-primary @endif m-0 "
                            href="{{ route('lawsuits.memos.index', $lawsuit->id) }}">
                            <i class="ti ti-file-text ti-sm me-1_5"></i>
                            <span>المذكرات</span>
                        </a>
                    </li>
                    <li
                        class="card rounded-0  mb-3 nav-item shadow-none @if (Route::is('lawsuit_section.lawsuit_parties')) border-end-custom border-primary @endif">
                        <a class="py-3 nav-link fw-bold @if (Route::is('lawsuit_section.lawsuit_parties')) text-primary @endif m-0 "
                            href="{{ route('lawsuit_section.lawsuit_parties', $lawsuit->id) }}">
                            <i class="ti ti-users ti-sm me-1_5"></i>
                            <span>أطراف الدعوى</span>
                        </a>
                    </li>
                    <li
                        class="card rounded-0  mb-3 nav-item shadow-none @if (Route::is('lawsuit_section.sessions')) border-end-custom border-primary @endif">
                        <a class="py-3 nav-link fw-bold @if (Route::is('lawsuit_section.sessions')) text-primary @endif m-0 "
                            href="{{ route('lawsuit_section.sessions', $lawsuit->id) }}">
                            <i class="ti ti-calendar ti-sm me-1_5"></i>
                            <span>الجلسات</span>
                        </a>
                    </li>
                    <li
                        class="card rounded-0  mb-3 nav-item shadow-none @if (Route::is('lawsuit_section.judgments')) border-end-custom border-primary @endif">
                        <a class="py-3 nav-link fw-bold @if (Route::is('lawsuit_section.judgments')) text-primary @endif m-0 "
                            href="{{ route('lawsuit_section.judgments', $lawsuit->id) }}">
                            <i class="ti ti-gavel ti-sm me-1_5"></i>
                            <span>الأحكام</span>
                        </a>
                    </li>
                    <li
                        class="card rounded-0  mb-3 nav-item shadow-none @if (Route::is('lawsuit_section.requests')) border-end-custom border-primary @endif">
                        <a class="py-3 nav-link fw-bold @if (Route::is('lawsuit_section.requests')) text-primary @endif m-0 "
                            href="{{ route('lawsuit_section.requests', $lawsuit->id) }}">
                            <i class="ti ti-list-check ti-sm me-1_5"></i>
                            <span>الطلبات</span>
                        </a>
                    </li>
                    <li
                        class="card rounded-0  mb-3 nav-item shadow-none @if (Route::is('lawsuit_section.decisions')) border-end-custom border-primary @endif">
                        <a class="py-3 nav-link fw-bold @if (Route::is('lawsuit_section.decisions')) text-primary @endif m-0 "
                            href="{{ route('lawsuit_section.decisions', $lawsuit->id) }}">
                            <i class="ti ti-clipboard-check  ti-sm me-1_5"></i>
                            <span>القرارات</span>
                        </a>
                    </li>
                    <li
                        class="card rounded-0  mb-3 nav-item shadow-none @if (Route::is('lawsuit_section.attachments')) border-end-custom border-primary @endif">
                        <a class="py-3 nav-link fw-bold @if (Route::is('lawsuit_section.attachments')) text-primary @endif m-0 "
                            href="{{ route('lawsuit_section.attachments', $lawsuit->id) }}">
                            <i class="ti ti-files ti-sm me-1_5"></i>
                            <span>المرفقات</span>
                        </a>
                    </li>
                    <li
                        class="card rounded-0  mb-3 nav-item shadow-none @if (Route::is('lawsuit_section.notes')) border-end-custom border-primary @endif">
                        <a class="py-3 nav-link fw-bold @if (Route::is('lawsuit_section.notes')) text-primary @endif m-0"
                            href="{{ route('lawsuit_section.notes', $lawsuit->id) }}">
                            <i class="ti ti-note ti-sm me-1_5"></i>
                            <span>الملاحظات</span>
                        </a>
                    </li>
                    <li
                        class="card rounded-0  mb-3 nav-item shadow-none @if (Route::is('lawsuit_section.tasks')) border-end-custom border-primary @endif">
                        <a class="py-3 nav-link fw-bold @if (Route::is('lawsuit_section.tasks')) text-primary @endif m-0"
                            href="{{ route('lawsuit_section.tasks', $lawsuit->id) }}">
                            <i class="ti ti-checklist ti-sm me-1_5"></i>
                            <span>المهام</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- User Sidebar -->
        <div class="col-12 col-md-9 col-xl-9 order-1 order-md-0 mb-5">
            <div class="row g-6">
                <!-- Options -->
                @yield('sections')
            </div>
        </div>
    </div>
@endsection
