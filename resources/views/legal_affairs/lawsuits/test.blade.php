@extends('layouts.layoutMaster')

@section('title', 'تفاصيل الدعوى')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/apex-charts/apex-charts.scss',
'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss'
])
@endsection

@section('page-style')
@vite('resources/assets/vendor/scss/pages/app-logistics-dashboard.scss')
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/apex-charts/apexcharts.js',
'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'
])
@endsection

@section('page-script')
@vite('resources/assets/js/app-logistics-dashboard.js')
@endsection

@section('content')
<div class="row g-6">
    <!-- Card Border Shadow -->
    <div class="col-lg-3 col-sm-6">
        <div class="card card-border-shadow-primary h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar me-4">
                        <span class="avatar-initial rounded bg-label-primary">
                            <i class='ti ti-calendar  ti-28px'></i>
                        </span>
                    </div>
                    <h6 class="mb-0">42</h6>
                </div>
                <p class="mb-1">عدد الجلسات</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6">
        <div class="card card-border-shadow-warning h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar me-4">
                        <span class="avatar-initial rounded bg-label-warning">
                            <i class='ti ti-user ti-28px'></i>
                        </span>
                    </div>
                    <h6 class="mb-0">{{ $lawsuit->project->manager->name }}</h6>
                </div>
                <p class="mb-1">مدير المشروع</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6">
        <div class="card card-border-shadow-danger h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar me-4">
                        <span class="avatar-initial rounded bg-label-danger">
                            <i class='tf-icons ti ti-gavel ti-28px'></i>
                        </span>
                    </div>
                    <h6 class="mb-0">{{ $lawsuit->opponent->name }}</h6>
                </div>
                <p class="mb-1">الخصم</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6">
        <div class="card card-border-shadow-info h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar me-4">
                        <span class="avatar-initial rounded bg-label-info">
                            <i class='ti ti-scale ti-28px'></i>
                        </span>
                    </div>
                    <h6 class="mb-0">{{ $lawsuit->lawsuit_number }}</h6>
                </div>
                <p class="mb-1">رقم الدعوى</p>
            </div>
        </div>
    </div>
    <!--/ Card Border Shadow -->


    <!-- Vehicles overview -->
    <div class="col-12 col-md-6">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="card-title mb-0">
                    <h5 class="m-0 me-2">تفاصيل الدعوى</h5>
                </div>

            </div>
            <div class="card-body">

                <table class="table card-table">
                    <tbody class="table-border-bottom-0">
                        <!-- اسم الدعوى -->
                        <tr>
                            <td class="w-50 ps-0">
                                <div class="d-flex justify-content-start align-items-center">
                                    <div class="me-2">
                                        <i class='ti ti-gavel ti-lg text-heading'></i>
                                    </div>
                                    <h6 class="mb-0 fw-normal">اسم الدعوى</h6>
                                </div>
                            </td>
                            <td class="text-end pe-0 text-nowrap">
                                <h6 class="mb-0">{{ $lawsuit->name }}</h6>
                            </td>
                        </tr>
                        <!-- رقم الدعوى -->
                        <tr>
                            <td class="w-50 ps-0">
                                <div class="d-flex justify-content-start align-items-center">
                                    <div class="me-2">
                                        <i class='ti ti-number ti-lg text-heading'></i>
                                    </div>
                                    <h6 class="mb-0 fw-normal">رقم الدعوى</h6>
                                </div>
                            </td>
                            <td class="text-end pe-0 text-nowrap">
                                <h6 class="mb-0">{{ $lawsuit->lawsuit_number }}</h6>
                            </td>
                        </tr>
                        <!-- التصنيف -->
                        <tr>
                            <td class="w-50 ps-0">
                                <div class="d-flex justify-content-start align-items-center">
                                    <div class="me-2">
                                        <i class='ti ti-category ti-lg text-heading'></i>
                                    </div>
                                    <h6 class="mb-0 fw-normal">التصنيف</h6>
                                </div>
                            </td>
                            <td class="text-end pe-0 text-nowrap">
                                <h6 class="mb-0">{{ $lawsuit->department_contract_cases->name }}</h6>
                            </td>
                        </tr>
                        <!-- المشروع -->
                        <tr>
                            <td class="w-50 ps-0">
                                <div class="d-flex justify-content-start align-items-center">
                                    <div class="me-2">
                                        <i class='ti ti-briefcase ti-lg text-heading'></i>
                                    </div>
                                    <h6 class="mb-0 fw-normal">المشروع</h6>
                                </div>
                            </td>
                            <td class="text-end pe-0 text-nowrap">
                                <h6 class="mb-0">{{ $lawsuit->project->project_name }}</h6>
                            </td>
                        </tr>
                        <!-- الجهة -->
                        <tr>
                            <td class="w-50 ps-0">
                                <div class="d-flex justify-content-start align-items-center">
                                    <div class="me-2">
                                        <i class='ti ti-building ti-lg text-heading'></i>
                                    </div>
                                    <h6 class="mb-0 fw-normal">الجهة</h6>
                                </div>
                            </td>
                            <td class="text-end pe-0 text-nowrap">
                                <h6 class="mb-0">{{ $lawsuit->entitie->name }}</h6>
                            </td>
                        </tr>
                        <!-- المحكمة الأم -->
                        <tr>
                            <td class="w-50 ps-0">
                                <div class="d-flex justify-content-start align-items-center">
                                    <div class="me-2">
                                        <i class='ti ti-scale ti-lg text-heading'></i>
                                    </div>
                                    <h6 class="mb-0 fw-normal">المحكمة الأم</h6>
                                </div>
                            </td>
                            <td class="text-end pe-0 text-nowrap">
                                <h6 class="mb-0">{{ $lawsuit->main_court->name }}</h6>
                            </td>
                        </tr>
                        <!-- درجة الجهة -->
                        <tr>
                            <td class="w-50 ps-0">
                                <div class="d-flex justify-content-start align-items-center">
                                    <div class="me-2">
                                        <i class='ti ti-star ti-lg text-heading'></i>
                                    </div>
                                    <h6 class="mb-0 fw-normal">درجة الجهة</h6>
                                </div>
                            </td>
                            <td class="text-end pe-0 text-nowrap">
                                <h6 class="mb-0">{{ $lawsuit->entity_rank->name }}</h6>
                            </td>
                        </tr>
                        <!-- المدينة -->
                        <tr>
                            <td class="w-50 ps-0">
                                <div class="d-flex justify-content-start align-items-center">
                                    <div class="me-2">
                                        <i class='ti ti-map ti-lg text-heading'></i>
                                    </div>
                                    <h6 class="mb-0 fw-normal">المدينة</h6>
                                </div>
                            </td>
                            <td class="text-end pe-0 text-nowrap">
                                <h6 class="mb-0">{{ $lawsuit->region->name }}</h6>
                            </td>
                        </tr>
                        <!-- الدائرة -->
                        <tr>
                            <td class="w-50 ps-0">
                                <div class="d-flex justify-content-start align-items-center">
                                    <div class="me-2">

                                    </div>
                                    <h6 class="mb-0 fw-normal">الدائرة</h6>
                                </div>
                            </td>
                            <td class="text-end pe-0 text-nowrap">
                                <h6 class="mb-0">{{ $lawsuit->circle ?? 'لا يوجد' }}</h6>
                            </td>
                        </tr>
                        <!-- صحيفة الدعوى -->
                        <tr>
                            <td class="w-50 ps-0">
                                <div class="d-flex justify-content-start align-items-center">
                                    <div class="me-2">
                                        <i class='ti ti-attachment ti-lg text-heading'></i>
                                    </div>
                                    <h6 class="mb-0 fw-normal">صحيفة الدعوى</h6>
                                </div>
                            </td>
                            <td class="text-end pe-0 text-nowrap">
                                @if($lawsuit->lawsuit_attachment)
                                <a href="{{ Storage::url($lawsuit->lawsuit_attachment) }}" target="_blank"
                                    class="btn btn-sm btn-primary">
                                    عرض الصحيفة
                                </a>
                                @else
                                <span>لا يوجد صحيفة</span>
                                @endif
                            </td>
                        </tr>
                        <!-- برهاننا -->
                        <tr>
                            <td class="w-50 ps-0">
                                <div class="d-flex justify-content-start align-items-center">
                                    <div class="me-2">
                                        <i class='ti ti-proof ti-lg text-heading'></i>
                                    </div>
                                    <h6 class="mb-0 fw-normal">برهاننا</h6>
                                </div>
                            </td>
                            <td class="text-end pe-0 text-nowrap">
                                <h6 class="mb-0">{{ $lawsuit->our_proof ?? 'لا يوجد' }}</h6>
                            </td>
                        </tr>
                        <!-- برهان الخصم -->
                        <tr>
                            <td class="w-50 ps-0">
                                <div class="d-flex justify-content-start align-items-center">
                                    <div class="me-2">
                                        <i class='ti ti-opponent-proof ti-lg text-heading'></i>
                                    </div>
                                    <h6 class="mb-0 fw-normal">برهان الخصم</h6>
                                </div>
                            </td>
                            <td class="text-end pe-0 text-nowrap">
                                <h6 class="mb-0">{{ $lawsuit->opponent_proof ?? 'لا يوجد' }}</h6>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!--/ Vehicles overview -->

    <!-- Vehicles overview -->
    <div class="col-12 col-md-6">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="card-title mb-0">
                    <h5 class="m-0 me-2">تفاصيل المشروع</h5>
                </div>

            </div>
            <div class="card-body">

                <table class="table card-table">
                    <tbody class="table-border-bottom-0">
                        <!-- اسم المشروع -->
                        <tr>
                            <td class="w-50 ps-0">
                                <div class="d-flex justify-content-start align-items-center">
                                    <div class="me-2">
                                        <i class='ti ti-building ti-lg text-heading'></i>
                                    </div>
                                    <h6 class="mb-0 fw-normal">اسم المشروع</h6>
                                </div>
                            </td>
                            <td class="text-end pe-0 text-nowrap">
                                <h6 class="mb-0">{{ $project->project_name }}</h6>
                            </td>
                        </tr>
                        <!-- العميل -->
                        <tr>
                            <td class="w-50 ps-0">
                                <div class="d-flex justify-content-start align-items-center">
                                    <div class="me-2">
                                        <i class='ti ti-user ti-lg text-heading'></i>
                                    </div>
                                    <h6 class="mb-0 fw-normal">العميل</h6>
                                </div>
                            </td>
                            <td class="text-end pe-0 text-nowrap">
                                <h6 class="mb-0">{{ $project->customer->name }}</h6>
                            </td>
                        </tr>
                        <!-- مدير المشروع -->
                        <tr>
                            <td class="w-50 ps-0">
                                <div class="d-flex justify-content-start align-items-center">
                                    <div class="me-2">
                                        <i class='ti ti-user-plus ti-lg text-heading'></i>
                                    </div>
                                    <h6 class="mb-0 fw-normal">مدير المشروع</h6>
                                </div>
                            </td>
                            <td class="text-end pe-0 text-nowrap">
                                <h6 class="mb-0">{{ $project->manager->name }}</h6>
                            </td>
                        </tr>
                        <!-- تاريخ البدء -->
                        <tr>
                            <td class="w-50 ps-0">
                                <div class="d-flex justify-content-start align-items-center">
                                    <div class="me-2">
                                        <i class='ti ti-calendar-start ti-lg text-heading'></i>
                                    </div>
                                    <h6 class="mb-0 fw-normal">تاريخ البدء</h6>
                                </div>
                            </td>
                            <td class="text-end pe-0 text-nowrap">
                                <h6 class="mb-0">{{ $project->start_date }}</h6>
                            </td>
                        </tr>
                        <!-- تاريخ الإغلاق -->
                        <tr>
                            <td class="w-50 ps-0">
                                <div class="d-flex justify-content-start align-items-center">
                                    <div class="me-2">
                                        <i class='ti ti-calendar-end ti-lg text-heading'></i>
                                    </div>
                                    <h6 class="mb-0 fw-normal">تاريخ الإغلاق</h6>
                                </div>
                            </td>
                            <td class="text-end pe-0 text-nowrap">
                                <h6 class="mb-0">{{ $project->end_date ? $project->end_date: 'لا يوجد' }}</h6>
                            </td>
                        </tr>
                        <!-- مدة المشروع -->
                        <tr>
                            <td class="w-50 ps-0">
                                <div class="d-flex justify-content-start align-items-center">
                                    <div class="me-2">
                                        <i class='ti ti-clock ti-lg text-heading'></i>
                                    </div>
                                    <h6 class="mb-0 fw-normal">مدة المشروع (أيام)</h6>
                                </div>
                            </td>
                            <td class="text-end pe-0 text-nowrap">
                                <h6 class="mb-0">{{ $project->duration ?? 'لا يوجد' }}</h6>
                            </td>
                        </tr>
                        <!-- مرفق العقد -->
                        <tr>
                            <td class="w-50 ps-0">
                                <div class="d-flex justify-content-start align-items-center">
                                    <div class="me-2">
                                        <i class='ti ti-attachment ti-lg text-heading'></i>
                                    </div>
                                    <h6 class="mb-0 fw-normal">مرفق العقد</h6>
                                </div>
                            </td>
                            <td class="text-end pe-0 text-nowrap">
                                @if($project->contract_attachment)
                                    <a href="{{ Storage::url($project->contract_attachment) }}" target="_blank" class="btn btn-sm btn-primary">
                                        عرض المرفق
                                    </a>
                                @else
                                    <span>لا يوجد مرفق</span>
                                @endif
                            </td>
                        </tr>
                        <!-- وصف المشروع -->
                        <tr>
                            <td class="w-50 ps-0">
                                <div class="d-flex justify-content-start align-items-center">
                                    <div class="me-2">
                                        <i class='ti ti-description ti-lg text-heading'></i>
                                    </div>
                                    <h6 class="mb-0 fw-normal">وصف المشروع</h6>
                                </div>
                            </td>
                            <td class="text-end pe-0 text-nowrap">
                                <h6 class="mb-0">{{ $project->description ?? 'لا يوجد' }}</h6>
                            </td>
                        </tr>
                        <!-- اسم الخصم -->
                        <tr>
                            <td class="w-50 ps-0">
                                <div class="d-flex justify-content-start align-items-center">
                                    <div class="me-2">
                                        <i class='ti ti-gavel ti-lg text-heading'></i>
                                    </div>
                                    <h6 class="mb-0 fw-normal">اسم الخصم</h6>
                                </div>
                            </td>
                            <td class="text-end pe-0 text-nowrap">
                                <h6 class="mb-0">{{ $project->opponent_name ?? 'لا يوجد' }}</h6>
                            </td>
                        </tr>
                        <!-- إجمالي المطالبة -->
                        <tr>
                            <td class="w-50 ps-0">
                                <div class="d-flex justify-content-start align-items-center">
                                    <div class="me-2">
                                        <i class='ti ti-currency-dollar ti-lg text-heading'></i>
                                    </div>
                                    <h6 class="mb-0 fw-normal">إجمالي المطالبة</h6>
                                </div>
                            </td>
                            <td class="text-end pe-0 text-nowrap">
                                <h6 class="mb-0">{{ number_format($project->total_claim, 2) }} ريال</h6>
                            </td>
                        </tr>
                        <!-- الإغلاق التعاقدي -->
                        <tr>
                            <td class="w-50 ps-0">
                                <div class="d-flex justify-content-start align-items-center">
                                    <div class="me-2">
                                        <i class='ti ti-lock ti-lg text-heading'></i>
                                    </div>
                                    <h6 class="mb-0 fw-normal">الإغلاق التعاقدي</h6>
                                </div>
                            </td>
                            <td class="text-end pe-0 text-nowrap">
                                <h6 class="mb-0">{{ $project->contractual_closure ? $project->contractual_closure->format('Y-m-d') : 'لا يوجد' }}</h6>
                            </td>
                        </tr>
                        <!-- رقم إثبات الخصم -->
                        <tr>
                            <td class="w-50 ps-0">
                                <div class="d-flex justify-content-start align-items-center">
                                    <div class="me-2">
                                        <i class='ti ti-proof ti-lg text-heading'></i>
                                    </div>
                                    <h6 class="mb-0 fw-normal">رقم إثبات الخصم</h6>
                                </div>
                            </td>
                            <td class="text-end pe-0 text-nowrap">
                                <h6 class="mb-0">{{ $project->opponent_proof_number ?? 'لا يوجد' }}</h6>
                            </td>
                        </tr>
                        <!-- حالة المشروع -->
                        <tr>
                            <td class="w-50 ps-0">
                                <div class="d-flex justify-content-start align-items-center">
                                    <div class="me-2">
                                        <i class='ti ti-status ti-lg text-heading'></i>
                                    </div>
                                    <h6 class="mb-0 fw-normal">حالة المشروع</h6>
                                </div>
                            </td>
                            <td class="text-end pe-0 text-nowrap">
                                <h6 class="mb-0">
                                    @switch($project->status)
                                        @case('ongoing')
                                            <span class="badge bg-info">جاري التنفيذ</span>
                                            @break
                                        @case('completed')
                                            <span class="badge bg-success">مكتمل</span>
                                            @break
                                        @case('postponed')
                                            <span class="badge bg-warning">مؤجل</span>
                                            @break
                                        @case('canceled')
                                            <span class="badge bg-danger">ملغي</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">غير محدد</span>
                                    @endswitch
                                </h6>
                            </td>
                        </tr>
                        <!-- المستخدم -->
                        <tr>
                            <td class="w-50 ps-0">
                                <div class="d-flex justify-content-start align-items-center">
                                    <div class="me-2">
                                        <i class='ti ti-user-circle ti-lg text-heading'></i>
                                    </div>
                                    <h6 class="mb-0 fw-normal">المستخدم</h6>
                                </div>
                            </td>
                            <td class="text-end pe-0 text-nowrap">
                                <h6 class="mb-0">{{ $project->user ? $project->user->name : 'لا يوجد' }}</h6>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!--/ Vehicles overview -->

    <!-- Reasons for delivery exceptions -->
    <div class="col-xxl-4 col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="card-title mb-0">
                    <h5 class="m-0 me-2">Reasons for delivery exceptions</h5>
                </div>
                <div class="dropdown">
                    <button class="btn btn-text-secondary rounded-pill text-muted border-0 p-2 me-n1" type="button"
                        id="deliveryExceptions" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="ti ti-dots-vertical ti-md text-muted"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="deliveryExceptions">
                        <a class="dropdown-item" href="javascript:void(0);">Select All</a>
                        <a class="dropdown-item" href="javascript:void(0);">Refresh</a>
                        <a class="dropdown-item" href="javascript:void(0);">Share</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="deliveryExceptionsChart"></div>
            </div>
        </div>
    </div>
    <!--/ Reasons for delivery exceptions -->
    <!-- Orders by Countries -->
    <div class="col-xxl-4 col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="card-title mb-0">
                    <h5 class="mb-1">Orders by Countries</h5>
                    <p class="card-subtitle">62 deliveries in progress</p>
                </div>
                <div class="dropdown">
                    <button class="btn btn-text-secondary rounded-pill text-muted border-0 p-2 me-n1" type="button"
                        id="salesByCountryTabs" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="ti ti-dots-vertical ti-md text-muted"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="salesByCountryTabs">
                        <a class="dropdown-item" href="javascript:void(0);">Select All</a>
                        <a class="dropdown-item" href="javascript:void(0);">Refresh</a>
                        <a class="dropdown-item" href="javascript:void(0);">Share</a>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="nav-align-top">
                    <ul class="nav nav-tabs nav-fill rounded-0 timeline-indicator-advanced" role="tablist">
                        <li class="nav-item">
                            <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
                                data-bs-target="#navs-justified-new" aria-controls="navs-justified-new"
                                aria-selected="true">New</button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                data-bs-target="#navs-justified-link-preparing"
                                aria-controls="navs-justified-link-preparing" aria-selected="false">Preparing</button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                data-bs-target="#navs-justified-link-shipping"
                                aria-controls="navs-justified-link-shipping" aria-selected="false">Shipping</button>
                        </li>
                    </ul>
                    <div class="tab-content border-0  mx-1">
                        <div class="tab-pane fade show active" id="navs-justified-new" role="tabpanel">
                            <ul class="timeline mb-0">
                                <li class="timeline-item ps-6 border-left-dashed">
                                    <span
                                        class="timeline-indicator-advanced timeline-indicator-success border-0 shadow-none">
                                        <i class='ti ti-circle-check'></i>
                                    </span>
                                    <div class="timeline-event ps-1">
                                        <div class="timeline-header">
                                            <small class="text-success text-uppercase">sender</small>
                                        </div>
                                        <h6 class="my-50">Myrtle Ullrich</h6>
                                        <p class="text-body mb-0">101 Boulder, California(CA), 95959</p>
                                    </div>
                                </li>
                                <li class="timeline-item ps-6 border-transparent">
                                    <span
                                        class="timeline-indicator-advanced timeline-indicator-primary border-0 shadow-none">
                                        <i class='ti ti-map-pin'></i>
                                    </span>
                                    <div class="timeline-event ps-1">
                                        <div class="timeline-header">
                                            <small class="text-primary text-uppercase">Receiver</small>
                                        </div>
                                        <h6 class="my-50">Barry Schowalter</h6>
                                        <p class="text-body mb-0">939 Orange, California(CA), 92118</p>
                                    </div>
                                </li>
                            </ul>
                            <div class="border-1 border-light border-top border-dashed my-4"></div>
                            <ul class="timeline mb-0">
                                <li class="timeline-item ps-6 border-left-dashed">
                                    <span
                                        class="timeline-indicator-advanced timeline-indicator-success border-0 shadow-none">
                                        <i class='ti ti-circle-check'></i>
                                    </span>
                                    <div class="timeline-event ps-1">
                                        <div class="timeline-header">
                                            <small class="text-success text-uppercase">sender</small>
                                        </div>
                                        <h6 class="my-50">Veronica Herman</h6>
                                        <p class="text-body mb-0">162 Windsor, California(CA), 95492</p>
                                    </div>
                                </li>
                                <li class="timeline-item ps-6 border-transparent">
                                    <span
                                        class="timeline-indicator-advanced timeline-indicator-primary border-0 shadow-none">
                                        <i class='ti ti-map-pin'></i>
                                    </span>
                                    <div class="timeline-event ps-1">
                                        <div class="timeline-header">
                                            <small class="text-primary text-uppercase">Receiver</small>
                                        </div>
                                        <h6 class="my-50">Helen Jacobs</h6>
                                        <p class="text-body mb-0">487 Sunset, California(CA), 94043</p>
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <div class="tab-pane fade" id="navs-justified-link-preparing" role="tabpanel">
                            <ul class="timeline mb-0">
                                <li class="timeline-item ps-6 border-left-dashed">
                                    <span
                                        class="timeline-indicator-advanced timeline-indicator-success border-0 shadow-none">
                                        <i class='ti ti-circle-check'></i>
                                    </span>
                                    <div class="timeline-event ps-1">
                                        <div class="timeline-header">
                                            <small class="text-success text-uppercase">sender</small>
                                        </div>
                                        <h6 class="my-50">Barry Schowalter</h6>
                                        <p class="text-body mb-0">939 Orange, California(CA), 92118</p>
                                    </div>
                                </li>
                                <li class="timeline-item ps-6 border-transparent border-left-dashed">
                                    <span
                                        class="timeline-indicator-advanced timeline-indicator-primary border-0 shadow-none">
                                        <i class='ti ti-map-pin'></i>
                                    </span>
                                    <div class="timeline-event ps-1">
                                        <div class="timeline-header">
                                            <small class="text-primary text-uppercase">Receiver</small>
                                        </div>
                                        <h6 class="my-50">Myrtle Ullrich</h6>
                                        <p class="text-body mb-0">101 Boulder, California(CA), 95959 </p>
                                    </div>
                                </li>
                            </ul>
                            <div class="border-1 border-light border-top border-dashed my-4"></div>
                            <ul class="timeline mb-0">
                                <li class="timeline-item ps-6 border-left-dashed">
                                    <span
                                        class="timeline-indicator-advanced timeline-indicator-success border-0 shadow-none">
                                        <i class='ti ti-circle-check'></i>
                                    </span>
                                    <div class="timeline-event ps-1">
                                        <div class="timeline-header">
                                            <small class="text-success text-uppercase">sender</small>
                                        </div>
                                        <h6 class="my-50">Veronica Herman</h6>
                                        <p class="text-body mb-0">162 Windsor, California(CA), 95492</p>
                                    </div>
                                </li>
                                <li class="timeline-item ps-6 border-transparent">
                                    <span
                                        class="timeline-indicator-advanced timeline-indicator-primary border-0 shadow-none">
                                        <i class='ti ti-map-pin'></i>
                                    </span>
                                    <div class="timeline-event ps-1">
                                        <div class="timeline-header">
                                            <small class="text-primary text-uppercase">Receiver</small>
                                        </div>
                                        <h6 class="my-50">Helen Jacobs</h6>
                                        <p class="text-body mb-0">487 Sunset, California(CA), 94043</p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="tab-pane fade" id="navs-justified-link-shipping" role="tabpanel">
                            <ul class="timeline mb-0">
                                <li class="timeline-item ps-6 border-left-dashed">
                                    <span
                                        class="timeline-indicator-advanced timeline-indicator-success border-0 shadow-none">
                                        <i class='ti ti-circle-check'></i>
                                    </span>
                                    <div class="timeline-event ps-1">
                                        <div class="timeline-header">
                                            <small class="text-success text-uppercase">sender</small>
                                        </div>
                                        <h6 class="my-50">Veronica Herman</h6>
                                        <p class="text-body mb-0">101 Boulder, California(CA), 95959</p>
                                    </div>
                                </li>
                                <li class="timeline-item ps-6 border-transparent">
                                    <span
                                        class="timeline-indicator-advanced timeline-indicator-primary border-0 shadow-none">
                                        <i class='ti ti-map-pin'></i>
                                    </span>
                                    <div class="timeline-event ps-1">
                                        <div class="timeline-header">
                                            <small class="text-primary text-uppercase">Receiver</small>
                                        </div>
                                        <h6 class="my-50">Barry Schowalter</h6>
                                        <p class="text-body mb-0">939 Orange, California(CA), 92118</p>
                                    </div>
                                </li>
                            </ul>
                            <div class="border-1 border-light border-top border-dashed my-4"></div>
                            <ul class="timeline mb-0">
                                <li class="timeline-item ps-6 border-left-dashed">
                                    <span
                                        class="timeline-indicator-advanced timeline-indicator-success border-0 shadow-none">
                                        <i class='ti ti-circle-check'></i>
                                    </span>
                                    <div class="timeline-event ps-1">
                                        <div class="timeline-header">
                                            <small class="text-success text-uppercase">sender</small>
                                        </div>
                                        <h6 class="my-50">Myrtle Ullrich</h6>
                                        <p class="text-body mb-0">162 Windsor, California(CA), 95492 </p>
                                    </div>
                                </li>
                                <li class="timeline-item ps-6 border-transparent">
                                    <span
                                        class="timeline-indicator-advanced timeline-indicator-primary border-0 shadow-none">
                                        <i class='ti ti-map-pin'></i>
                                    </span>
                                    <div class="timeline-event ps-1">
                                        <div class="timeline-header">
                                            <small class="text-primary text-uppercase">Receiver</small>
                                        </div>
                                        <h6 class="my-50">Helen Jacobs</h6>
                                        <p class="text-body mb-0">487 Sunset, California(CA), 94043</p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--/ Orders by Countries -->

    <!-- On route vehicles Table -->

    <div class="col-12 order-5">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="card-title mb-0">
                    <h5 class="m-0 me-2">On route vehicles</h5>
                </div>
                <div class="dropdown">
                    <button class="btn btn-text-secondary rounded-pill text-muted border-0 p-2 me-n1" type="button"
                        id="routeVehicles" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="ti ti-dots-vertical ti-md text-muted"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="routeVehicles">
                        <a class="dropdown-item" href="javascript:void(0);">Select All</a>
                        <a class="dropdown-item" href="javascript:void(0);">Refresh</a>
                        <a class="dropdown-item" href="javascript:void(0);">Share</a>
                    </div>
                </div>
            </div>
            <div class="card-datatable table-responsive">
                <table class="dt-route-vehicles table table-sm">
                    <thead>
                        <tr>
                            <th></th>
                            <th></th>
                            <th>location</th>
                            <th>starting route</th>
                            <th>ending route</th>
                            <th>warnings</th>
                            <th class="w-20">progress</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!--/ On route vehicles Table -->
</div>

@endsection
