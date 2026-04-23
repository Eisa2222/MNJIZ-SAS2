@extends('layouts.layoutMaster')

@section('title', 'تفاصيل الدعوى')

@section('breadcrumb')
    <li><a href="#">الشؤون القانونية</a></li>

    <li><a href="{{ route('legal-affairs.lawsuits.index') }}"> الدعاوى</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> تفاصيل الدعوى</a>
        <i class="ti ti-star favorite-icon" data-page-name="تفاصيل الدعوى" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection
@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss'])
    <link href="{{ asset('assets/hijri-date/css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet" />
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
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js', 'resources/assets/css/tasks.css'])
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
    <div class="row g-3">
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

    <div class="row g-3 mt-1">
        <style>
            .nav-tabs .nav-link,
            .nav-pills .nav-link {
                justify-content: start;
                margin-bottom: 10px
            }

            .nav-tabs .nav-link.active,
            .nav-tabs .nav-link.active:hover,
            .nav-tabs .nav-link.active:focus {
                box-shadow: none;
                border-left: 5px solid;
            }
        </style>
        <div class="col-12 col-md-3 col-xl-3">
            <div>
                <ul class="nav nav-tabs justify-content-between border-bottom-0 flex-column">
                    <li class="card rounded-0 mb-3 nav-item shadow-none">
                        <a class="py-3 nav-link fw-bold active m-0" data-bs-toggle="tab" href="#lawsuit_subject">
                            <i class="ti ti-book ti-sm me-1_5"></i>
                            <span>موضوع الدعوى</span>
                        </a>
                    </li>

                    <li class="card rounded-0 mb-3 nav-item shadow-none">
                        <a class="py-3 nav-link fw-bold  m-0" data-bs-toggle="tab" href="#memos">
                            <i class="ti ti-file-text ti-sm me-1_5"></i>
                            <span>المذكرات</span>
                        </a>
                    </li>
                    <li class="card rounded-0 mb-3 nav-item shadow-none">
                        <a class="py-3 nav-link fw-bold  m-0" data-bs-toggle="tab" href="#lawsuit_parties">
                            <i class="ti ti-users ti-sm me-1_5"></i>
                            <span>أطراف الدعوى</span>
                        </a>
                    </li>
                    <li class="card rounded-0 mb-3 nav-item shadow-none">
                        <a class="py-3 nav-link fw-bold  m-0" data-bs-toggle="tab" href="#sessions">
                            <i class="ti ti-calendar ti-sm me-1_5"></i>
                            <span>الجلسات</span>
                        </a>
                    </li>
                    <li class="card rounded-0 mb-3 nav-item shadow-none">
                        <a class="py-3 nav-link fw-bold  m-0" data-bs-toggle="tab" href="#judgments">
                            <i class="ti ti-gavel ti-sm me-1_5"></i>
                            <span>الأحكام</span>
                        </a>
                    </li>
                    <li class="card rounded-0 mb-3 nav-item shadow-none">
                        <a class="py-3 nav-link fw-bold  m-0" data-bs-toggle="tab" href="#requests">
                            <i class="ti ti-list-check ti-sm me-1_5"></i>
                            <span>الطلبات</span>
                        </a>
                    </li>
                    <li class="card rounded-0 mb-3 nav-item shadow-none">
                        <a class="py-3 nav-link fw-bold  m-0" data-bs-toggle="tab" href="#decisions">
                            <i class="ti ti-clipboard-check  ti-sm me-1_5"></i>
                            <span>القرارات</span>
                        </a>
                    </li>
                    <li class="card rounded-0 mb-3 nav-item shadow-none">
                        <a class="py-3 nav-link fw-bold  m-0" data-bs-toggle="tab" href="#attachments">
                            <i class="ti ti-files ti-sm me-1_5"></i>
                            <span>المرفقات</span>
                        </a>
                    </li>
                    <li class="card rounded-0 mb-3 nav-item shadow-none">
                        <a class="py-3 nav-link fw-bold  m-0" data-bs-toggle="tab" href="#notes">
                            <i class="ti ti-note ti-sm me-1_5"></i>
                            <span>الملاحظات</span>
                        </a>
                    </li>
                    <li class="card rounded-0 mb-3 nav-item shadow-none">
                        <a class="py-3 nav-link fw-bold  m-0" data-bs-toggle="tab" href="#tasks">
                            <i class="ti ti-checklist ti-sm me-1_5"></i>
                            <span>المهام</span>
                        </a>
                    </li>

                    <li class="card rounded-0 mb-3 nav-item shadow-none">
                        <a class="py-3 nav-link fw-bold  m-0" data-bs-toggle="tab" href="#meeting" id="meeting-tab"
                            data-target="meetings">
                            <i class="ti ti-checklist ti-sm me-1_5"></i>
                            <span>الإجتماعات</span>
                        </a>
                    </li>

                    <li class="card rounded-0 mb-3 nav-item shadow-none">
                        <a class="py-3 nav-link fw-bold  m-0" data-bs-toggle="tab" href="#history" id="history-tab"
                            data-target="history">
                            <i class="ti ti-history ti-sm me-1_5"></i>
                            <span>سجل النشاطات</span>
                        </a>
                    </li>

                </ul>
            </div>
        </div>

        <!-- User Sidebar -->
        <div class="col-12 col-md-9 col-xl-9 order-1 order-md-0 mb-5">
            <div class="tab-content p-0">
                <div class="tab-pane fade show active" id="lawsuit_subject">
                    @include('legal_affairs.lawsuits.sections.lawsuit_subject')
                </div>
            </div>

            <div class="tab-content p-0">
                <div class="tab-pane fade  " id="memos">
                    @include('legal_affairs.lawsuits.sections.memos')
                </div>
            </div>

            <div class="tab-content p-0">
                <div class="tab-pane fade  " id="lawsuit_parties">
                    @include('legal_affairs.lawsuits.sections.lawsuit_parties')
                </div>
            </div>

            <div class="tab-content p-0">
                <div class="tab-pane fade  " id="sessions">
                    @include('legal_affairs.lawsuits.sections.sessions')
                </div>
            </div>

            <div class="tab-content p-0">
                <div class="tab-pane fade  " id="judgments">
                    @include('legal_affairs.lawsuits.sections.judgments')
                </div>
            </div>

            <div class="tab-content p-0">
                <div class="tab-pane fade  " id="requests">
                    @include('legal_affairs.lawsuits.sections.requests')
                </div>
            </div>

            <div class="tab-content p-0">
                <div class="tab-pane fade  " id="decisions">
                    @include('legal_affairs.lawsuits.sections.decisions')
                </div>
            </div>

            <div class="tab-content p-0">
                <div class="tab-pane fade  " id="attachments">
                    @include('legal_affairs.lawsuits.sections.attachments')
                </div>
            </div>

            <div class="tab-content p-0">
                <div class="tab-pane fade  " id="notes">
                    @include('legal_affairs.lawsuits.sections.notes')
                </div>
            </div>

            <div class="tab-content p-0">
                <div class="tab-pane fade  " id="tasks">
                    @include('legal_affairs.lawsuits.sections.tasks')
                </div>
            </div>

            <div class="tab-content p-0">
                <div class="tab-pane fade  " id="meeting">
                    @include('legal_affairs.lawsuits.sections.meeting')
                </div>
            </div>

            <div class="tab-content p-0">
                <div class="tab-pane fade  " id="history">
                    @include('legal_affairs.lawsuits.sections.history')
                </div>
            </div>

        </div>
    </div>

@endsection
