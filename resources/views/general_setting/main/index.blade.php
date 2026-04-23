@extends('layouts.layoutMaster')

@section('title', 'الإعدادات العامة')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> الإعدادات العامة</a>
        <i class="ti ti-star favorite-icon" data-page-name="الإعدادات العامة" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/js/custom/select-all.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection


@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection


@section('content')
    <div class="row g-4 mb-5">

        <div class="col-12">
            <div class="card">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3">
                            <span class="avatar-initial rounded-circle bg-label-primary">
                                <i class="ti ti-settings ti-md"></i>
                            </span>
                        </div>
                        <div>
                            <p class="mb-0">الإعدادات العامة</p>
                            <small class="text-muted mb-0">
                                ضبط هوية المنصة، إعداداتها الافتراضية، وخياراتها العامة.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- إعدادات النظام -->
        @canany([
            'إعدادات النظام',
            'النماذج',
            'الصلاحيات الوظيفية',
            'إدارة الإعتمادات',
            'سجل النشاطات',
            'سجل
            الرسائل',
            ])
            <div class="col-xl-4 col-lg-6 col-md-6">
                <div class="card h-100 setting-card" style="cursor: pointer;" onclick="navigateToSettings('system')">
                    <div class="card-body text-center py-4">
                        <div class="avatar avatar-lg mx-auto mb-3">
                            <span class="avatar-initial rounded-circle bg-label-primary">
                                <i class="ti ti-server-2 ti-lg"></i>
                            </span>
                        </div>
                        <h6 class="mb-2">إعدادات النظام</h6>
                        <small class="text-muted mb-0">إعدادات عامة للنظام و النماذج</small>
                        <div class="mt-3">
                            <span class="badge bg-label-primary">عام</span>
                        </div>
                    </div>

                </div>
            </div>
        @endcanany

        <!-- إعدادات مركز العمليات -->
        @canany(['اقسام المشاريع والقضايا', 'حالات العقود', 'المنتجات', 'حالات العميل', 'قنوات التسويق', 'القطاعات',
            'المدن', 'قائمة الدول', 'مواقع التواصل', 'قائمة البنوك'])
            <div class="col-xl-4 col-lg-6 col-md-6">
                <div class="card h-100 setting-card" style="cursor: pointer;" onclick="navigateToSettings('operations')">
                    <div class="card-body text-center py-4">
                        <div class="avatar avatar-lg mx-auto mb-3">
                            <span class="avatar-initial rounded-circle bg-label-info">
                                <i class="ti ti-building-warehouse ti-lg"></i>
                            </span>
                        </div>
                        <h6 class="mb-2">إعدادات مركز العمليات</h6>
                        <small class="text-muted mb-0">اقسام وحالات المشاريع و العقود</small>
                        <div class="mt-3">
                            <span class="badge bg-label-info">عمليات</span>
                        </div>
                    </div>

                </div>
            </div>
        @endcanany

        <!-- إعدادات الموارد البشرية -->
        @canany([
            'حالات الموارد البشرية',
            'تصنيف الموارد البشرية',
            'تصنيف المخالفات',
            'أنواع المخالفات',
            'أنواع
            الإجازات',
            'تصنيفات الاصول',
            'مرجعية الاصول',
            ])
            <div class="col-xl-4 col-lg-6 col-md-6">
                <div class="card h-100 setting-card" style="cursor: pointer;" onclick="navigateToSettings('hr')">
                    <div class="card-body text-center py-4">
                        <div class="avatar avatar-lg mx-auto mb-3">
                            <span class="avatar-initial rounded-circle bg-label-success">
                                <i class="ti ti-users ti-lg"></i>
                            </span>
                        </div>
                        <h6 class="mb-2">إعدادات الموارد البشرية</h6>
                        <small class="text-muted mb-0">تصنيفات وحالات الموارد البشرية</small>
                        <div class="mt-3">
                            <span class="badge bg-label-success">موارد بشرية</span>
                        </div>
                    </div>

                </div>
            </div>
        @endcanany

        <!-- إعدادات التسويق -->
        @canany([
            'أنواع الحملات والمحتوى',
            'أنماط النشر',
            'أهداف الحملات والمحتوى',
            'أقسام الحملات',
            'الجمهور
            المستهدف',
            ])
            <div class="col-xl-4 col-lg-6 col-md-6">
                <div class="card h-100 setting-card" style="cursor: pointer;" onclick="navigateToSettings('marketing')">
                    <div class="card-body text-center py-4">
                        <div class="avatar avatar-lg mx-auto mb-3">
                            <span class="avatar-initial rounded-circle bg-label-warning">
                                <i class="ti ti-speakerphone ti-lg"></i>
                            </span>
                        </div>
                        <h6 class="mb-2">إعدادات التسويق</h6>
                        <small class="text-muted mb-0">انواع واقسام الحملات و المحتوى</small>
                        <div class="mt-3">
                            <span class="badge bg-label-warning">تسويق</span>
                        </div>
                    </div>

                </div>
            </div>
        @endcanany

        <!-- إعدادات الشؤون القانونية -->
        @canany([
            'المحكمة',
            'درجة الجهة',
            'التصنيفات الرئيسية',
            'التصنيفات الفرعية',
            'أنواع الدعاوى',
            'أنواع
            الجلسات',
            'أنواع الاحكام',
            ])
            <div class="col-xl-4 col-lg-6 col-md-6">
                <div class="card h-100 setting-card" style="cursor: pointer;" onclick="navigateToSettings('legal')">
                    <div class="card-body text-center py-4">
                        <div class="avatar avatar-lg mx-auto mb-3">
                            <span class="avatar-initial rounded-circle bg-label-danger">
                                <i class="ti ti-scale ti-lg"></i>
                            </span>
                        </div>
                        <h6 class="mb-2">إعدادات الشؤون القانونية</h6>
                        <small class="text-muted mb-0">انوع الدعاوى والجلسات</small>
                        <div class="mt-3">
                            <span class="badge bg-label-danger">قانونية</span>
                        </div>
                    </div>

                </div>
            </div>
        @endcanany

        <!-- إعدادات مركز المشتريات -->
        @canany(['تصنيف المشتريات'])
            <div class="col-xl-4 col-lg-6 col-md-6">
                <div class="card h-100 setting-card" style="cursor: pointer;" onclick="navigateToSettings('procurement')">
                    <div class="card-body text-center py-4">
                        <div class="avatar avatar-lg mx-auto mb-3">
                            <span class="avatar-initial rounded-circle bg-label-secondary">
                                <i class="ti ti-shopping-cart ti-lg"></i>
                            </span>
                        </div>
                        <h6 class="mb-2">إعدادات مركز المشتريات</h6>
                        <small class="text-muted mb-0"> المشتريات و التصنيفات الخاصة بها </small>
                        <div class="mt-3">
                            <span class="badge bg-label-secondary">مشتريات</span>
                        </div>
                    </div>

                </div>
            </div>
        @endcanany


    </div>


    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <script>
        function navigateToSettings(key) {
            const urls = {
                system: "{{ route('general-settings.system') }}",
                operations: "{{ route('general-settings.operations') }}",
                hr: "{{ route('general-settings.hr') }}",
                marketing: "{{ route('general-settings.marketing') }}",
                legal: "{{ route('general-settings.legal') }}",
                procurement: "{{ route('general-settings.purchasing') }}"
            };

            if (urls[key]) {
                window.location.href = urls[key];
            } else {
                console.warn('no url defined for', key);
            }
        }
    </script>

@endsection
