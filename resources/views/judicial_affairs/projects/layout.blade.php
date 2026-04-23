@extends('layouts.layoutMaster')

@section('title', 'تفاصيل المشروع')

@section('breadcrumb')
    <li><a href="{{ route('projects.index') }}"> المشاريع</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">{{ Str::limit($project->project_name, 25) }} </a>
        <i class="ti ti-star favorite-icon" data-page-name="تفاصيل المشروع" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('content')

    <div class="row g-3 mb-3">
        <!-- اسم الدعوى -->
        <div class="col-12 col-md-3 col-xl-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center">
                        <div class="content-center text-truncate">
                            <h5 class="mb-1">
                                عدد الدعاوى
                            </h5>
                            <span class="fw-bold text-primary ">{{ $project->lawsuits->count() }} </span>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-9 col-xl-9">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row g-2 text-center">
                        <!-- تصنيف الدعوى -->
                        <div class="col-md-4">

                            <div class="d-flex align-items-center justify-content-center">
                                <div class="content-center text-truncate">
                                    <h5 class="mb-1">
                                        عدد الجلسات
                                    </h5>
                                    <span class="fw-bold text-primary">
                                        {{ $project->lawsuits->sum(fn($lawsuit) => $lawsuit->sessions->count()) }}
                                    </span>
                                </div>
                                {{-- <span class="badge bg-label-warning rounded-circle p-2">
                                <i class="ti ti-briefcase ti-lg"></i>
                            </span> --}}

                            </div>
                        </div>
                        <!-- رقم الدعوى -->
                        <div class="col-md-4">

                            <div class="d-flex align-items-center justify-content-center">
                                <div class="content-center text-truncate">
                                    <h5 class="mb-1">
                                        مدير المشروع
                                    </h5>
                                    @if ($project->manager_user)
                                        <a
                                            href="{{ route('account.employee.profile', $project->manager_user->employee->id) }}">

                                            <span
                                                class="fw-bold text-primary">{{ $project->manager_user->employee->name ?? 'لا يوجد' }}</span>
                                        </a>
                                    @else
                                        <span class="fw-bold text-primary">لم يتم تعيينه بعد</span>
                                    @endif

                                </div>
                                {{-- <span class="badge bg-label-primary rounded-circle p-2">
                                <i class="ti ti-hash ti-lg"></i>
                            </span> --}}
                            </div>

                        </div>

                        <!-- نوع الدعوى -->
                        <div class="col-md-4">

                            <div class="d-flex align-items-center justify-content-center">
                                <div class="content-center text-truncate">
                                    <h5 class="mb-1">
                                        تاريخ البدء
                                    </h5>
                                    <span class="fw-bold text-primary">{{ $project->start_date ?? 'لا يوجد' }} </span>
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
    <div class="row">
        <style>
            .nav-tabs .nav-link,
            .nav-pills .nav-link {
                justify-content: start;
                margin-bottom: 10px
            }

            .border-end-custom {
                border-left: 5px solid;
                /* اللون الأساسي */
            }
        </style>
        <div class="col-12 col-md-3 col-xl-3">
            <div>
                <ul class="nav nav-tabs justify-content-between border-bottom-0 flex-column">
                    <li
                        class="card rounded-0 mb-3 nav-item  shadow-none @if (Route::is('projects.show')) border-end-custom border-primary @endif">
                        <a class="py-3 nav-link fw-bold @if (Route::is('projects.show')) text-primary @endif m-0"
                            href="{{ route('projects.show', $project->id) }}">
                            <i class="ti ti-discount-2 ti-sm me-1_5"></i>
                            <span class="align-middle">تفاصيل المشروع</span>

                        </a>
                    </li>

                    <li
                        class="card rounded-0 mb-3 nav-item shadow-none @if (Route::is('projects.teams')) border-end-custom border-primary @endif">
                        <a class="py-3 nav-link fw-bold position-relative @if (Route::is('projects.teams')) text-primary @endif m-0"
                            href="{{ $project->manager_user_id != null ? route('projects.teams', $project->id) : 'javascript:void(0);' }}"
                            onclick="checkProjectStatus(event, {{ $project->manager_user_id != null ? 'true' : 'false' }})">
                            <i class="ti ti-users-group ti-sm me-1_5"></i>
                            <span class="align-middle">فريق المشروع</span>
                            @if ($project->manager_user_id == null)
                                <span class="position-absolute top-50 end-0 translate-middle-y p-1 "><i
                                        class="ti ti-alert-octagon text-danger"></i></span>
                            @endif
                        </a>
                    </li>
                    <li
                        class="card rounded-0 mb-3 nav-item shadow-none @if (Route::is('projects.lawsuits')) border-end-custom border-primary @endif">
                        <a class="py-3 nav-link fw-bold position-relative @if (Route::is('projects.lawsuits')) text-primary @endif m-0"
                            href="{{ $project->manager_user_id != null ? route('projects.lawsuits', $project->id) : 'javascript:void(0);' }}"
                            onclick="checkProjectStatus(event, {{ $project->manager_user_id != null ? 'true' : 'false' }})">
                            <i class="ti ti-scale ti-sm me-1_5"></i>
                            <span class="align-middle">تفاصيل الدعاوى</span>
                            @if ($project->manager_user_id == null)
                                <span class="position-absolute top-50 end-0 translate-middle-y p-1 "><i
                                        class="ti ti-alert-octagon text-danger"></i></span>
                            @endif
                        </a>
                    </li>
                    <li
                        class="card rounded-0 mb-3 nav-item shadow-none @if (Route::is('project.tasks')) border-end-custom border-primary @endif">
                        <a class="py-3 nav-link fw-bold position-relative @if (Route::is('project.tasks')) text-primary @endif m-0"
                            href="{{ $project->manager_user_id != null ? route('project.tasks', $project->id) : 'javascript:void(0);' }}"
                            onclick="checkProjectStatus(event, {{ $project->manager_user_id != null ? 'true' : 'false' }})">
                            <i class="ti ti-checklist ti-sm me-1_5"></i>
                            <span class="align-middle">المهام</span>
                            @if ($project->manager_user_id == null)
                                <span class="position-absolute top-50 end-0 translate-middle-y p-1 "><i
                                        class="ti ti-alert-octagon text-danger"></i></span>
                            @endif
                        </a>
                    </li>

                    <li
                        class="card rounded-0 mb-3 nav-item shadow-none @if (Route::is('project.meeting')) border-end-custom border-primary @endif">
                        <a class="py-3 nav-link fw-bold position-relative @if (Route::is('project.meeting')) text-primary @endif m-0"
                            href="{{ $project->manager_user_id != null ? route('project.meeting', $project->id) : 'javascript:void(0);' }}"
                            onclick="checkProjectStatus(event, {{ $project->manager_user_id != null ? 'true' : 'false' }})">
                            <i class="ti ti-video ti-sm me-1_5"></i>
                            <span class="align-middle">الاجتماعات</span>
                            @if ($project->manager_user_id == null)
                                <span class="position-absolute top-50 end-0 translate-middle-y p-1 "><i
                                        class="ti ti-alert-octagon text-danger"></i></span>
                            @endif
                        </a>
                    </li>

                </ul>
            </div>
        </div>

        <div class="col-12 col-md-9 col-xl-9 order-1 order-md-0 mb-5">
            <div class="row g-6">
                <!-- Options -->
                @yield('sections')
            </div>
        </div>
    </div>

    <script>
        function checkProjectStatus(event, isComplete) {
            if (!isComplete) {
                event.preventDefault(); // منع الانتقال إلى الرابط
                Swal.fire({
                    icon: 'warning',
                    title: 'المشروع غير مكتمل',
                    text: 'يجب ان يتم استكمال المشروع بواسطة مدير الشؤون الفنية لتفعيل هذا الخيار.',
                    showCancelButton: false, // يعرض زر الإلغاء
                    showConfirmButton: false, // يعرض زر التأكيد
                    showDenyButton: false, // لا يعرض زر الرفض
                    buttonsStyling: false, // لتعطيل التنسيق الافتراضي للأزرار
                    customClass: {
                        popup: 'custom-popup', // تخصيص شكل النافذة
                        title: 'custom-title', // تخصيص شكل العنوان
                        text: 'custom-text', // تخصيص شكل النص
                    },

                });
            }
        }
    </script>

@endsection
