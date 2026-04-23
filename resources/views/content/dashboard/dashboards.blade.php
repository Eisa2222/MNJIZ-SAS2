@extends('layouts.layoutMaster')

@section('title', 'لوحة التحكم - التحليلات القانونية')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/apex-charts/apex-charts.scss', 'resources/assets/vendor/libs/shepherd/shepherd.scss', 'resources/assets/vendor/libs/swiper/swiper.scss', 'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.scss'])
@endsection
@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection
@section('page-style')
    @vite(['resources/assets/vendor/scss/pages/cards-advance.scss'])
@endsection

@section('vendor-script')
    <script>
        window.appUrls = {
            taskComplate: "{{ route('organization-center.tasks.toggle-completion', ':task') }}",
            stepComplate: "{{ route('organization-center.tasks.steps.toggle-completion', ':stepId') }}",
            stepApproval: "{{ route('organization-center.tasks.steps.toggle-approval', ':stepId') }}",
        };
    </script>
    @vite(['resources/assets/js/organization-center/tasks/task-profile.js', 'resources/assets/vendor/libs/apex-charts/apexcharts.js', 'resources/assets/vendor/libs/shepherd/shepherd.js', 'resources/assets/vendor/libs/swiper/swiper.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/tour_dashboard.js', 'resources/assets/js/dashboards-analytics.js', 'resources/assets/js/datatable-settings.js', 'resources/assets/js/newNotification.js'])
@endsection

@section('content')

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="row g-3" id="content-area">

        @canany(['كل الجلسات', 'الجلسات الخاصة بي', 'كل الدعاوى', 'الدعاوى الخاصة بي', 'كل المشاريع', 'المشاريع الخاصة بي',
            'الإعتماد الفني للمشاريع'])
            <div
                class="col-lg-12  {{ auth()->user()->canany(['كل الجلسات', 'الجلسات الخاصة بي', 'كل الدعاوى', 'الدعاوى الخاصة بي', 'الإعتماد الفني للمشاريع'])
                    ? 'col-xxl-6'
                    : 'col-xxl-12' }}">
                <div class="swiper-container swiper-container-horizontal swiper swiper-card-advance-bg"
                    id="swiper-with-pagination-cards">
                    <div class="swiper-wrapper">
                        @canany(['كل الدعاوى', 'الدعاوى الخاصة بي'])
                            <!-- تحليلات الدعاوى -->
                            <div class="swiper-slide">
                                <div class="row">
                                    <div class="col-12">
                                        <h5 class="text-white mb-0">تحليلات الدعاوى</h5>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-7 col-md-9 col-12 order-2 order-md-1 pt-md-9">
                                            <h6 class="text-white mt-0 mt-md-3 mb-4">إحصاءات الدعاوى</h6>
                                            <div class="row">
                                                <div class="col-6">
                                                    <ul class="list-unstyled mb-0">
                                                        <li class="d-flex mb-4 align-items-center">
                                                            <p class="mb-0 fw-medium me-2 website-analytics-text-bg">
                                                                {{ $totalLawsuit }}</p>
                                                            <p class="mb-0">إجمالي الدعاوى</p>
                                                        </li>
                                                        <li class="d-flex mb-4 align-items-center">
                                                            <p class="mb-0 fw-medium me-2 website-analytics-text-bg">
                                                                {{ round($commercialPercentage) }}%</p>
                                                            <p class="mb-0"> التجارية</p>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div class="col-6">
                                                    <ul class="list-unstyled mb-0">
                                                        <li class="d-flex mb-4 align-items-center">
                                                            <p class="mb-0 fw-medium me-2 website-analytics-text-bg">
                                                                {{ round($civilPercentage) }}%</p>
                                                            <p class="mb-0"> المدنية</p>
                                                        </li>
                                                        <li class="d-flex mb-4 align-items-center">
                                                            <p class="mb-0 fw-medium me-2 website-analytics-text-bg">
                                                                {{ round($criminalPercentage) }}%</p>
                                                            <p class="mb-0"> الجنائية</p>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-5 col-md-3 col-12 order-1 order-md-2 my-4 my-md-0 text-center">
                                            <img src="{{ asset('alburhan.png') }}" alt="تحليلات الموقع" height="150"
                                                class="card-website-analytics-img">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endcan

                        @canany(['كل الجلسات', 'الجلسات الخاصة بي'])
                            <!-- تحليلات الجلسات -->
                            <div class="swiper-slide">
                                <div class="row">
                                    <div class="col-12">
                                        <h5 class="text-white mb-0">تحليلات الجلسات</h5>
                                    </div>
                                    <div class="col-lg-7 col-md-9 col-12 order-2 order-md-1 pt-md-9">
                                        <h6 class="text-white mt-0 mt-md-3 mb-4">إحصاءات الجلسات</h6>
                                        <div class="row">
                                            <div class="col-6">
                                                <ul class="list-unstyled mb-0">
                                                    <li class="d-flex mb-4 align-items-center">
                                                        <p class="mb-0 fw-medium me-2 website-analytics-text-bg">
                                                            {{ $sessions }}</p>
                                                        <p class="mb-0">اجمالي الجلسات </p>
                                                    </li>

                                                    <li class="d-flex mb-4 align-items-center">
                                                        <p class="mb-0 fw-medium me-2 website-analytics-text-bg">
                                                            {{ $pendingSessions }}</p>
                                                        <p class="mb-0"> بانتظار ضبط الجلسة</p>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="col-6">
                                                <ul class="list-unstyled mb-0">
                                                    <li class="d-flex mb-4 align-items-center">
                                                        <p class="mb-0 fw-medium me-2 website-analytics-text-bg">
                                                            {{ $activeSessions }}</p>
                                                        <p class="mb-0"> النشطة</p>
                                                    </li>

                                                    <li class="d-flex mb-4 align-items-center">
                                                        <p class="mb-0 fw-medium me-2 website-analytics-text-bg">
                                                            {{ $closedSessions }}</p>
                                                        <p class="mb-0"> المغلقة</p>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-5 col-md-3 col-12 order-1 order-md-2 my-4 my-md-0 text-center">
                                        <img src="{{ asset('alburhan.png') }}" alt="تحليلات الجلسات" height="150"
                                            class="card-website-analytics-img">
                                    </div>
                                </div>
                            </div>
                        @endcan

                        @canany(['كل المشاريع', 'المشاريع الخاصة بي', 'الإعتماد الفني للمشاريع'])
                            <!-- تحليلات المشاريع -->
                            <div class="swiper-slide">
                                <div class="row">
                                    <div class="col-12">
                                        <h5 class="text-white mb-0">تحليلات المشاريع</h5>
                                    </div>
                                    <div class="col-lg-7 col-md-9 col-12 order-2 order-md-1 pt-md-9">
                                        <h6 class="text-white mt-0 mt-md-3 mb-4">إحصاءات المشاريع</h6>
                                        <div class="row">
                                            <div class="col-6">
                                                <ul class="list-unstyled mb-0">
                                                    <li class="d-flex mb-4 align-items-center">
                                                        <p class="mb-0 fw-medium me-2 website-analytics-text-bg">
                                                            {{ $totalProjects }}</p>
                                                        <p class="mb-0">إجمالي المشاريع</p>
                                                    </li>
                                                    <li class="d-flex mb-4 align-items-center">
                                                        <p class="mb-0 fw-medium me-2 website-analytics-text-bg">
                                                            {{ round($newPercentage) }}%</p>
                                                        <p class="mb-0"> الجديدة</p>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="col-6">
                                                <ul class="list-unstyled mb-0">
                                                    <li class="d-flex mb-4 align-items-center">
                                                        <p class="mb-0 fw-medium me-2 website-analytics-text-bg">
                                                            {{ round($onTrackPercentage) }}%</p>
                                                        <p class="mb-0"> في المسار</p>
                                                    </li>
                                                    <li class="d-flex mb-4 align-items-center">
                                                        <p class="mb-0 fw-medium me-2 website-analytics-text-bg">
                                                            {{ round($closePercentage) }}%</p>
                                                        <p class="mb-0"> المغلقة </p>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-5 col-md-3 col-12 order-1 order-md-2 my-4 my-md-0 text-center">
                                        <img src="{{ asset('alburhan.png') }}" alt="تحليلات المشاريع" height="150"
                                            class="card-website-analytics-img">
                                    </div>
                                </div>
                            </div>
                        @endcan

                    </div>
                    <div class="swiper-pagination"></div>
                </div>
            </div>
        @endcan

        <!--/ التحليلات الرئيسية  -->
        @canany(['كل الدعاوى', 'الدعاوى الخاصة بي'])
            <div
                class="col-12 col-md-6 {{ auth()->user()->canany(['كل الجلسات', 'الجلسات الخاصة بي'])? 'col-xxl-3': 'col-xxl-6' }}">
                <div class="card h-100">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">
                            <p class="mb-0 text-body">نظرة على الدعاوى</p>
                            <p class="card-text fw-medium text-success">
                            </p>
                        </div>
                        <h4 class="card-title mb-1">
                            {{ $totalLawsuit }} دعوى
                        </h4>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-between flex-wrap pb-2">
                        <div id="lawsuitChartContainer" class="w-100" style="height: 150px; flex-shrink: 0;">
                            <canvas id="lawsuitChart"></canvas>
                        </div>
                    </div>
                    {{-- <div class="card-footer px-1">
                        <div class="d-flex justify-content-center gap-2 flex-wrap">
                            @foreach ($categories as $index => $category)
                                <span id="lawsuit" class="badge" style=" cursor: pointer;"
                                    onclick="toggleDataset({{ $index }})" data-index="{{ $index }}">
                                    {{ $category->department_contract_cases->name ?? 0 }} ({{ $category->count }})
                                </span>
                            @endforeach
                        </div>
                    </div> --}}
                </div>
            </div>

            <script>
                // جلب بيانات المجموعات من Blade
                const categories = @json($chartData);

                // استخراج الأسماء والأعداد
                const labels = categories.map(cat => cat.name);
                const data = categories.map(cat => cat.count);

                // تعريف الألوان في JavaScript
                const colors = @json($colorsUnique);

                // تعيين الألوان بناءً على الفهرس
                const backgroundColors = labels.map((_, index) => colors[index % colors.length]);

                const ctx = document.getElementById('lawsuitChart').getContext('2d');
                const lawsuitChart = new Chart(ctx, {
                    type: 'doughnut', // يمكنك تغيير نوع المخطط حسب رغبتك
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data,
                            backgroundColor: backgroundColors,
                            // يمكنك إضافة المزيد من الخصائص هنا حسب الحاجة
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false // إخفاء الأسطورة
                            },
                            tooltip: {
                                enabled: true // تمكين التلميحات إذا كنت ترغب
                            }
                        },
                        // خيارات إضافية حسب الحاجة
                    }
                });

                // تحديث ألوان الـ Badges بناءً على الألوان في المخطط
                document.querySelectorAll('#lawsuit').forEach((badge, index) => {
                    badge.style.backgroundColor = backgroundColors[index];
                });

                // دالة للتحكم في إظهار أو إخفاء جزء من المخطط عند النقر على الـ Badge
                function toggleDataset(index) {
                    const meta = lawsuitChart.getDatasetMeta(0);
                    meta.data[index].hidden = !meta.data[index].hidden;
                    lawsuitChart.update();

                    // تحديث لون الـ Badge عند إخفاء أو إظهار المجموعة
                    const badge = document.querySelector(`#lawsuit[data-index="${index}"]`);
                    if (meta.data[index].hidden) {
                        badge.style.opacity = 0.5;
                    } else {
                        badge.style.opacity = 1;
                    }
                }
            </script>
        @endcan

        @canany(['كل الجلسات', 'الجلسات الخاصة بي'])
            <div
                class="col-12 col-md-6 {{ auth()->user()->canany(['كل الدعاوى', 'الدعاوى الخاصة بي'])? 'col-xxl-3': 'col-xxl-6' }}">
                <div class="card h-100">
                    <div class="card-header">
                        <div class="d-flex justify-content-between">
                            <p class="mb-0 text-body">نظرة على الجلسات</p>
                            <p class="card-text fw-medium text-success">
                                <!-- يمكن عرض النص المطلوب هنا -->
                            </p>
                        </div>
                        <h4 class="card-title mb-1">
                            {{ $sessions }} جلسة
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-4">
                                <div class="d-flex gap-2 align-items-center mb-2">
                                    <span class="badge bg-label-success p-1 rounded">
                                        <i class="ti ti-check ti-sm"></i>
                                    </span>
                                    <p class="mb-0">نشطة</p>
                                </div>
                                <h5 class="mb-0 pt-1">{{ round($activePercentage, 2) }}%</h5>
                                <small class="text-muted">{{ $activeSessions }} جلسة</small>
                            </div>
                            <div class="col-4">
                                <div class="divider divider-vertical">
                                    <div class="divider-text">
                                        <span class="badge-divider-bg bg-label-secondary">VS</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="d-flex gap-2 justify-content-end align-items-center mb-2">
                                    <p class="mb-0">مغلقة</p>
                                    <span class="badge bg-label-primary p-1 rounded">
                                        <i class="ti ti-lock ti-sm"></i>
                                    </span>
                                </div>
                                <h5 class="mb-0 pt-1">{{ round($closedPercentage, 2) }}%</h5>
                                <small class="text-muted">{{ $closedSessions }} جلسة</small>
                            </div>
                        </div>

                        <div class="d-flex align-items-center mt-4">
                            <div class="progress w-100" style="height: 10px;">
                                <div class="progress-bar bg-success" style="width: {{ $activePercentage }}%;"
                                    role="progressbar" aria-valuenow="{{ $activePercentage }}" aria-valuemin="0"
                                    aria-valuemax="100">
                                </div>
                                <div class="progress-bar bg-primary" style="width: {{ $closedPercentage }}%;"
                                    role="progressbar" aria-valuenow="{{ $closedPercentage }}" aria-valuemin="0"
                                    aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endcan


        <!-- آخر 5 مهام مسندة -->
        @canany(['المهام الخاصة بي'])
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header pb-3 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1">آخر 5 مهام مسندة</h5>
                            <small class="card-subtitle text-muted">عرض أحدث المهام المسندة إليك</small>
                        </div>
                        <a href="{{ route('organization-center.tasks.my-tasks') }}" class="btn btn-sm btn-primary">
                            كل مهامي
                        </a>
                    </div>

                    <div class="border-1 border-light border-dashed mb-2"></div>

                    <div class="card-body">
                        <div class="card-body px-0 py-0" id="task-list">

                            @if ($tasks->count() > 0)
                                @foreach ($tasks as $item)
                                    <div class="d-flex border p-2 align-items-center rounded-3 mb-2 small"
                                        id="taskInfo_{{ $item->id }}">

                                        <a href="{{ route('organization-center.tasks.show', $item->id) }}"
                                            class="border-end px-2 me-10 small w-50">
                                            {{ \Illuminate\Support\Str::limit($item->task_name, 100, '...') }}
                                        </a>

                                        <span class="badge bg-{{ $item->status->color() }}">
                                            {{ $item->status->label() }}
                                        </span>

                                        <div class="ms-auto d-flex align-items-center" dir="rtl">
                                            <div class="px-1">
                                                <input class="form-check-input custom-item task-complete-checkbox"
                                                    type="checkbox" name="task_completed" id="task_{{ $item->id }}"
                                                    title="إكمال المهمة"
                                                    {{ $item->status->value == 'complete' ? 'checked' : '' }}
                                                    {{ !$item->assignedUsers->contains('id', auth()->id()) || $item->status->value !== 'in_progress' ? 'disabled' : '' }}
                                                    data-task-id="{{ $item->id }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="task-steps ms-4 mb-4" id="taskSteps_{{ $item->id }}">
                                        @php
                                            $inProgressSteps = $item->steps
                                                ->filter(function ($step) {
                                                    return $step->status->value === 'in_progress' &&
                                                        $step->assignedUsers->contains('id', auth()->id());
                                                })
                                                ->sortBy('step_order');
                                        @endphp

                                        @if ($inProgressSteps->count() > 0)
                                            <div class="steps-timeline">
                                                @foreach ($inProgressSteps as $step)
                                                    <div
                                                        class="d-flex justify-content-between border p-2 align-items-center rounded-3 mb-2 small">

                                                        <div class="step-number d-flex">
                                                            <span class="badge rounded-pill bg-warning">
                                                                {{ $step->step_order }}
                                                            </span>
                                                            <!-- Step Details -->
                                                            <div class="step-details ">
                                                                <span
                                                                    class="step-name fw-bold small mx-2">{{ $step->name }}</span>
                                                            </div>
                                                        </div>

                                                        <span class="badge bg-{{ $step->status->color() }}">
                                                            {{ $step->status->label() }}
                                                        </span>

                                                        @if ($step->needs_approval)
                                                            <div class="d-flex justify-content-between">

                                                                <button class="btn btn-sm btn-primary step-approval-btn mx-2"
                                                                    style="font-size:12px;"
                                                                    data-step-id="{{ $step->id }}" data-action="approve"
                                                                    title="اعتماد">
                                                                    <small>اعتماد</small>
                                                                </button>

                                                                <button class="btn btn-sm btn-secondary step-approval-btn"
                                                                    style="font-size:12px;"
                                                                    data-step-id="{{ $step->id }}" data-action="reject">
                                                                    <small>رفض</small>
                                                                </button>
                                                            </div>
                                                        @else
                                                            <div class="px-1"><small>
                                                                    <input
                                                                        class="form-check-input custom-item step-complete-checkbox"
                                                                        type="checkbox" name="step_complete"
                                                                        id="step_complete_{{ $step->id }}"
                                                                        title="إكمال الخطوة"
                                                                        data-step-id="{{ $step->id }}">
                                                                </small></div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center my-3">
                                    <p class="text-muted">لا يوجد مهام حاليا </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- موديل سبب الرفض -->
                <div class="modal fade" id="rejectReasonModal" tabindex="-1" aria-labelledby="rejectReasonModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="rejectReasonModalLabel">سبب الرفض</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="إغلاق"></button>
                            </div>
                            <div class="modal-body">
                                <textarea id="rejectReasonText" class="form-control" placeholder="أدخل سبب الرفض هنا"></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" id="confirmRejectBtn" class="btn btn-danger">رفض</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endcan
        <!-- آخر 5 مهام مسندة -->


        @canany(['كل الجلسات', 'الجلسات الخاصة بي'])
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header pb-3 d-flex justify-content-between">
                        <div class="card-title m-0 me-2">
                            <h5 class="mb-1">الجلسات</h5>
                            <small class="card-subtitle">عرض اقرب 5 جلسات </small>
                        </div>
                    </div>
                    <div class="border-1 border-light border-dashed mb-1"></div>

                    <div class="card-body p-0">
                        @if ($allsessions->count() > 0)
                            <table class="table table-striped table-sm mb-0" style="table-layout: fixed; width: 100%;">
                                <colgroup>
                                    <col style="width: 33.33%;">
                                    <col style="width: 33.33%;">
                                    <col style="width: 33.33%;">
                                </colgroup>
                                <thead style="padding: 0px 2px !important; ">
                                    <tr class="text-center">
                                        <th class="py-2 px-3">الجلسة</th>
                                        <th class="py-2 px-3">التاريخ و الوقت</th>
                                        <th class="py-2 px-3">المكلفين</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($allsessions as $session)
                                        <tr class="text-center">
                                            <td class="py-2 px-5 text-truncate">
                                                <a href="{{ route('legal-affairs.sessions.show', $session->id) }}">
                                                    <div class="d-flex align-items-center">

                                                        <div class="badge bg-label-secondary me-4 rounded p-1_5">
                                                            <i class="ti ti-calendar ti-md"></i>
                                                        </div>
                                                        <small class="mb-0 project-name text-truncate"
                                                            title="{{ $session->session_name }}">
                                                            {{ $session->session_name }}
                                                        </small>

                                                    </div>
                                                </a>
                                            </td>

                                            <td class="py-2 px-5 text-truncate">
                                                <small class="mb-0 text-truncate">
                                                    {{ $session->session_date->format('Y-m-d') . ' - ' . $session->session_time->format('H:i') }}
                                                </small>
                                            </td>

                                            <td class="py-2 px-5 text-truncate">
                                                @php
                                                    $displayCount = min(5, $session->assignedUsers->count());
                                                    $remaining = $session->assignedUsers->count() - $displayCount;
                                                @endphp

                                                <div class="d-flex justify-content-center gap-1">
                                                    @forelse($session->assignedUsers->take($displayCount) as $assignee)
                                                        @php
                                                            $profilePicture = $assignee->employee?->profile_picture
                                                                ? asset(
                                                                    'storage/' . $assignee->employee->profile_picture,
                                                                )
                                                                : asset('assets/img/avatars/1.png');
                                                        @endphp

                                                        <img class="rounded-circle border border-white"
                                                            style="width: 28px; height: 28px; margin-left: -8px;"
                                                            src="{{ $profilePicture }}"
                                                            title="{{ $assignee->employee?->name ?? 'مستخدم' }}"
                                                            alt="{{ $assignee->employee?->name ?? 'مستخدم' }}">
                                                    @empty
                                                        <span class="badge bg-label-secondary">لا يوجد</span>
                                                    @endforelse

                                                    @if ($remaining > 0)
                                                        <span class="badge bg-label-primary ms-1"
                                                            title="{{ $assignedUsers->skip($displayCount)->pluck('employee.name')->filter()->implode('، ') }}">
                                                            +{{ $remaining }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>


                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="text-center">لا يوجد جلسات حاليا</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center justify-content-between flex-wrap">
                        <div class="d-flex flex-column flex-grow-1 text-center">
                            <div class="card-title mb-2">
                                <h5 class="mb-0 text-nowrap">إحصائيات نوع الجلسات</h5>
                            </div>
                            <div class="chart-statistics mb-2">
                                <h3 class="card-title mb-0 text-primary">{{ $sessions }}</h3>
                                <p class="text-muted text-nowrap mb-0">إجمالي الجلسات</p>
                            </div>
                        </div>
                        @if ($sessions > 0)
                            <!-- تكبير حجم المخطط -->
                            <div id="sessionChartContainerType" class="w-100" style="height: 150px;">
                                <canvas id="sessionChartType"></canvas>
                            </div>
                        @endif
                    </div>
                    @if ($sessions > 0)
                        <div class="card-footer">
                            <div class="d-flex justify-content-center gap-2 flex-wrap">
                                @foreach ($sessionType as $index => $category)
                                    <span id="sessionTypeBadge{{ $index }}" class="badge" style=" cursor: pointer;"
                                        onclick="toggleDatasetSessionType({{ $index }})"
                                        data-index="{{ $index }}">
                                        {{ $category['name'] }} ({{ $category['count'] }})
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <p class="text-center">لا يوجد جلسات حاليا</p>
                    @endif
                </div>
            </div>

            @if ($sessions > 0)
                <script>
                    // تأكد من أن معرف المخطط فريد لتجنب التضارب
                    const categoriesSessionType = @json($sessionType);

                    const labelsSessionType = categoriesSessionType.map(cat => cat.name);
                    const dataSessionType = categoriesSessionType.map(cat => cat.count);

                    const colorsSessionType = @json($colorsUnique);
                    const backgroundColorsSessionType = labelsSessionType.map((_, index) => colorsSessionType[index % colorsSessionType
                        .length]);

                    const ctxSessionType = document.getElementById('sessionChartType').getContext('2d');
                    const sessionChartType = new Chart(ctxSessionType, {
                        type: 'doughnut',
                        data: {
                            labels: labelsSessionType,
                            datasets: [{
                                data: dataSessionType,
                                backgroundColor: backgroundColorsSessionType,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    enabled: true
                                }
                            },
                        }
                    });

                    // تعيين ألوان الـ badges بناءً على ألوان المخطط
                    document.querySelectorAll('[id^="sessionTypeBadge"]').forEach((badge, index) => {
                        badge.style.backgroundColor = backgroundColorsSessionType[index];
                    });

                    // دالة لتبديل رؤية البيانات في المخطط النوعي
                    function toggleDatasetSessionType(index) {
                        const meta = sessionChartType.getDatasetMeta(0);
                        meta.data[index].hidden = !meta.data[index].hidden;
                        sessionChartType.update();

                        const badge = document.querySelector(`#sessionTypeBadge${index}`);
                        if (meta.data[index].hidden) {
                            badge.style.opacity = 0.5;
                        } else {
                            badge.style.opacity = 1;
                        }
                    }
                </script>
            @endif
        @endcan

        <!-- العملاء-->
        @canany(['كل العملاء', 'العملاء الخاصين بي'])
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header pb-3 d-flex justify-content-between">
                        <div class="card-title m-0 me-2">
                            <h5 class="mb-1"> العملاء</h5>
                            <small class="card-subtitle">عرض اخر 5 عملاء تم اضافتهم </small>
                        </div>
                    </div>
                    <div class="border-1 border-light border-dashed mb-1"></div>

                    <div class="card-body p-0">
                        @if ($customers->count() > 0)
                            <table class="table table-striped table-sm mb-0" style="table-layout: fixed; width: 100%;">
                                <colgroup>
                                    <col style="width: 30%;">
                                    <col style="width: 25%;">
                                    <col style="width: 25%;">
                                    <col style="width: 20%;">
                                </colgroup>
                                <thead style="padding: 0px 2px !important; ">
                                    <tr class="text-center">
                                        <th class="py-2 px-3">اسم العميل</th>
                                        <th class="py-2 px-3">نوع العميل</th>
                                        <th class="py-2 px-3">اضيف بواسطة</th>
                                        <th class="py-2 px-3"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($customers as $customer)
                                        <tr class="text-center">
                                            <td class="py-2 px-5 text-truncate">
                                                <a href="{{ route('operations-center.customers.show', $customer->id) }}">
                                                    <div class="d-flex align-items-center">
                                                        <i class="ti ti-rosette-discount-check-filled text-success mx-2 py-1"
                                                            title="عميل"></i>
                                                        <small class="mb-0  text-truncate" title="{{ $customer->name }}">
                                                            {{ $customer->name }}
                                                        </small>
                                                    </div>
                                                </a>
                                            </td>

                                            <td class="py-2 px-5 text-truncate">
                                                <small class="mb-0 text-truncate">
                                                    {{ $customer->customer_type->label() }}
                                                </small>
                                            </td>

                                            <td class="py-2 px-5 text-truncate">
                                                @isset($customer->createdBy)
                                                    <a href="{{ route('account.employee.profile', $customer->createdBy->employee->id) }}"
                                                        class="mb-0 text-truncate"
                                                        title="{{ $customer->createdBy->employee->name ?? '' }}">
                                                        <small class="mb-0 text-truncate">
                                                            {{ $customer->createdBy->employee->name ?? '' }}
                                                        </small>
                                                    </a>
                                                @endisset

                                            </td>
                                            <td class="py-2 px-5 text-truncate">
                                                <div class="dropdown">
                                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="ti ti-dots-vertical"></i>
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item waves-effect"
                                                            href="sms:{{ $customer->contact_number ?? '' }}">
                                                            <i class="ti ti-message me-1"></i> إرسال رسالة
                                                        </a>
                                                        <a class="dropdown-item waves-effect"
                                                            href="https://wa.me/{{ $customer->contact_number ?? '' }}">
                                                            <i class="ti ti-brand-whatsapp me-1"></i> واتساب
                                                        </a>
                                                        <a class="dropdown-item waves-effect"
                                                            href="mailto:{{ $customer->email ?? '' }}">
                                                            <i class="ti ti-mail me-1"></i> بريد إلكتروني
                                                        </a>

                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="text-center">لا يوجد عملاء حاليا</p>
                        @endif

                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center justify-content-between flex-wrap">
                        <div class="d-flex flex-column flex-grow-1 text-center">
                            <div class="card-title mb-2">
                                <h5 class="mb-0 text-nowrap">إحصائيات العملاء</h5>
                            </div>
                            <div class="chart-statistics mb-2">
                                <h3 class="card-title mb-0 text-primary">{{ $totalCustomers }}</h3>
                                <p class="text-muted text-nowrap mb-0">إجمالي العملاء</p>
                            </div>
                        </div>
                        @if ($customers->count() > 0)
                            <!-- تكبير حجم المخطط -->
                            <div id="customerCanvas" class="w-100" style="height: 150px;">
                                <canvas id="customerChartId"></canvas>
                            </div>
                        @endif

                    </div>
                    @if ($customers->count() > 0)
                        <div class="card-footer">
                            <div class="d-flex justify-content-center gap-2 flex-wrap">
                                @foreach ($charCustomers as $index => $item)
                                    <span id="customerBadge{{ $index }}" class="badge" style=" cursor: pointer;"
                                        onclick="toggleDatasetCustomers({{ $index }})"
                                        data-index="{{ $index }}">
                                        {{ $item['name'] }} ({{ $item['count'] }})
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <p class="text-center">لا يوجد عملاء حاليا</p>
                    @endif
                </div>
            </div>
            @if ($customers->count() > 0)
                <script>
                    // تأكد من أن معرف المخطط فريد لتجنب التضارب
                    const customers = @json($charCustomers);

                    const labelsCustomers = customers.map(cat => cat.name);
                    const dataCustomer = customers.map(cat => cat.count);

                    const colorsCustomer = @json($colorsUnique);
                    const backgroundColorCustomer = labelsCustomers.map((_, index) => colorsCustomer[index % colorsCustomer.length]);

                    const ctxCustomer = document.getElementById('customerChartId').getContext('2d');
                    const customerChartId = new Chart(ctxCustomer, {
                        type: 'doughnut',
                        data: {
                            labels: labelsCustomers,
                            datasets: [{
                                data: dataCustomer,
                                backgroundColor: backgroundColorCustomer,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    enabled: true
                                }
                            },
                        }
                    });

                    // تعيين ألوان الـ badges بناءً على ألوان المخطط
                    document.querySelectorAll('[id^="customerBadge"]').forEach((badge, index) => {
                        badge.style.backgroundColor = backgroundColorCustomer[index];
                    });

                    // دالة لتبديل رؤية البيانات في المخطط الأهمي
                    function toggleDatasetCustomers(index) {
                        const meta = customerChartId.getDatasetMeta(0);
                        meta.data[index].hidden = !meta.data[index].hidden;
                        customerChartId.update();

                        const badge = document.querySelector(`#customerBadge${index}`);
                        if (meta.data[index].hidden) {
                            badge.style.opacity = 0.5;
                        } else {
                            badge.style.opacity = 1;
                        }
                    }
                </script>
            @endif
        @endcan
        <!-- العملاء-->



        <!-- العروض  -->
        @canany(['كل العروض', 'العروض الخاصة بي'])

            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header pb-3 d-flex justify-content-between">
                        <div class="card-title m-0 me-2">
                            <h5 class="mb-1"> العروض</h5>
                            <small class="card-subtitle">عرض اقرب 5 عروض </small>
                        </div>
                    </div>
                    <div class="border-1 border-light border-dashed mb-1"></div>
                    <div class="card-body p-0">
                        @if ($offers->count() > 0)
                            <table class="table table-striped table-sm mb-0" style="table-layout: fixed; width: 100%;">
                                <colgroup>
                                    <col style="width: 33.33%;">
                                    <col style="width: 33.33%;">
                                    <col style="width: 33.33%;">

                                </colgroup>
                                <thead style="padding: 0px 2px !important; ">
                                    <tr class="text-center">
                                        <th class="py-2 px-3">اسم العرض</th>
                                        <th class="py-2 px-3">حالة العرض</th>
                                        <th class="py-2 px-3">تاريخ بداية العرض</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($offers as $offer)
                                        <tr class="text-center">
                                            <td class="py-2 px-5 text-truncate">

                                                <small class="mb-0 text-truncate" title="{{ $offer->offer_name }}">
                                                    <a href="{{ route('operations-center.offers.show', $offer->id) }}">
                                                        {{ $offer->offer_name }}
                                                    </a>
                                                </small>

                                            </td>
                                            <td class="py-2 px-5 text-truncate">
                                                <small class="mb-0 text-truncate">
                                                    {{ $offer->status->label() ?? 'لا توجد مرحلة حاليا' }}

                                                </small>
                                            </td>

                                            <td class="py-2 px-5">
                                                <small class="mb-0 text-truncate" title="{{ $offer->time_remaining }}">
                                                    {{ $offer->time_remaining }}
                                                </small>
                                            </td>

                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="text-center">لا يوجد عروض حاليا</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center justify-content-between flex-wrap">
                        <div class="d-flex flex-column flex-grow-1 text-center">
                            <div class="card-title mb-2">
                                <h5 class="mb-0 text-nowrap">إحصائيات العروض حسب الحالة</h5>
                            </div>
                            <div class="chart-statistics mb-2">
                                <h3 class="card-title mb-0 text-primary">{{ $totalOffers }}</h3>
                                <p class="text-muted text-nowrap mb-0">إجمالي العروض</p>
                            </div>
                        </div>
                        @if ($offers->count() > 0)
                            <!-- تكبير حجم المخطط -->
                            <div id="offersCanvas" class="w-100" style="height: 150px;">
                                <canvas id="offersChartId"></canvas>
                            </div>
                        @endif

                    </div>
                    @if ($offers->count() > 0)
                        <div class="card-footer">
                            <div class="d-flex justify-content-center gap-2 flex-wrap">
                                @foreach ($charOffers as $index => $item)
                                    <span id="offersBadge{{ $index }}" class="badge" style=" cursor: pointer;"
                                        onclick="toggleDatasetOffers({{ $index }})" data-index="{{ $index }}">
                                        {{ $item['name'] }} ({{ $item['count'] }})
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <p class="text-center">لا يوجد عروض حاليا</p>
                    @endif
                </div>
            </div>
            @if ($offers->count() > 0)
                <script>
                    // تأكد من أن معرف المخطط فريد لتجنب التضارب
                    const Offers = @json($charOffers);

                    const labelsOffers = Offers.map(cat => cat.name);
                    const dataOffers = Offers.map(cat => cat.count);

                    const colorsOffers = @json($colorsUnique);
                    const backgroundColorOffers = labelsOffers.map((_, index) => colorsOffers[index % colorsOffers.length]);

                    const ctxOffers = document.getElementById('offersChartId').getContext('2d');
                    const offersChartId = new Chart(ctxOffers, {
                        type: 'doughnut',
                        data: {
                            labels: labelsOffers,
                            datasets: [{
                                data: dataOffers,
                                backgroundColor: backgroundColorOffers,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    enabled: true
                                }
                            },
                        }
                    });

                    // تعيين ألوان الـ badges بناءً على ألوان المخطط
                    document.querySelectorAll('[id^="offersBadge"]').forEach((badge, index) => {
                        badge.style.backgroundColor = backgroundColorOffers[index];
                    });

                    // دالة لتبديل رؤية البيانات في المخطط الأهمي
                    function toggleDatasetOffers(index) {
                        const meta = offersChartId.getDatasetMeta(0);
                        meta.data[index].hidden = !meta.data[index].hidden;
                        offersChartId.update();

                        const badge = document.querySelector(`#offersBadge${index}`);
                        if (meta.data[index].hidden) {
                            badge.style.opacity = 0.5;
                        } else {
                            badge.style.opacity = 1;
                        }
                    }
                </script>
            @endif

        @endcan


        <!--  العقود  -->
        @canany(['كل العقود', 'العقود الخاصة بي'])
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header pb-3 d-flex justify-content-between">
                        <div class="card-title m-0 me-2">
                            <h5 class="mb-1"> العقود</h5>
                            <small class="card-subtitle">عرض اقرب 5 عقود </small>
                        </div>
                    </div>
                    <div class="border-1 border-light border-dashed mb-1"></div>
                    <div class="card-body p-0">
                        @if ($contracts->count() > 0)
                            <table class="table table-striped table-sm mb-0" style="table-layout: fixed; width: 100%;">
                                <colgroup>
                                    <col style="width: 33.33%;">
                                    <col style="width: 33.33%;">
                                    <col style="width: 33.33%;">

                                </colgroup>
                                <thead style="padding: 0px 2px !important; ">
                                    <tr class="text-center">
                                        <th class="py-2 px-3">اسم العقد</th>
                                        <th class="py-2 px-3">العميل</th>
                                        <th class="py-2 px-3">تاريخ بداية العقد</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($contracts as $contract)
                                        <tr class="text-center">
                                            <td class="py-2 px-5 text-truncate">

                                                <small class="mb-0 text-truncate" title="{{ $contract->contract_name }}">
                                                    <a href="{{ route('operations-center.contracts.show', $contract->id) }}">
                                                        {{ $contract->contract_name }}
                                                    </a>
                                                </small>

                                            </td>
                                            <td class="py-2 px-5 text-truncate">
                                                <small class="mb-0 text-truncate">
                                                    @if ($contract->contract_type == 'main' && $contract->customer_id)
                                                        <a
                                                            href="{{ route('operations-center.customers.show', $contract->customer_id) }}">
                                                            {{ $contract->customer->name }}
                                                        </a>
                                                    @elseif($contract->contract_type == 'supplementary' && $contract->mainContract->customer)
                                                        <a
                                                            href="{{ route('operations-center.customers.show', $contract->customer_id) }}">
                                                            {{ $contract->mainContract->customer->name }}
                                                        </a>
                                                    @else
                                                        غير محدد
                                                    @endif

                                                </small>
                                            </td>

                                            <td class="py-2 px-5">
                                                <small class="mb-0 text-truncate" title="{{ $contract->time_remaining }}">
                                                    {{ $contract->time_remaining }}
                                                </small>
                                            </td>

                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="text-center">لا يوجد عقود حاليا</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center justify-content-between flex-wrap">
                        <div class="d-flex flex-column flex-grow-1 text-center">
                            <div class="card-title mb-2">
                                <h5 class="mb-0 text-nowrap">إحصائيات العقود حسب الحالة</h5>
                            </div>
                            <div class="chart-statistics mb-2">
                                <h3 class="card-title mb-0 text-primary">{{ $totalContracts }}</h3>
                                <p class="text-muted text-nowrap mb-0">إجمالي العقود</p>
                            </div>
                        </div>
                        @if ($contracts->count() > 0)
                            <!-- تكبير حجم المخطط -->
                            <div id="ContractCanvas" class="w-100" style="height: 150px;">
                                <canvas id="ContractsChartId"></canvas>
                            </div>
                        @endif

                    </div>
                    @if ($contracts->count() > 0)
                        <div class="card-footer">
                            <div class="d-flex justify-content-center gap-2 flex-wrap">
                                @foreach ($charContracts as $index => $item)
                                    <span id="ContractsBadge{{ $index }}" class="badge" style=" cursor: pointer;"
                                        onclick="toggleDatasetContract({{ $index }})"
                                        data-index="{{ $index }}">
                                        {{ $item['name'] }} ({{ $item['count'] }})
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <p class="text-center">لا يوجد عقود حاليا</p>
                    @endif
                </div>
            </div>

            @if ($contracts->count() > 0)
                <script>
                    // تأكد من أن معرف المخطط فريد لتجنب التضارب
                    const Contracts = @json($charContracts);

                    const labelsContracts = Contracts.map(cat => cat.name);
                    const dataContracts = Contracts.map(cat => cat.count);

                    const colorsContracts = @json($colorsUnique);
                    const backgroundColorContracts = labelsContracts.map((_, index) => colorsContracts[index % colorsContracts.length]);

                    const ctxContracts = document.getElementById('ContractsChartId').getContext('2d');
                    const ContractsChartId = new Chart(ctxContracts, {
                        type: 'doughnut',
                        data: {
                            labels: labelsContracts,
                            datasets: [{
                                data: dataContracts,
                                backgroundColor: backgroundColorContracts,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    enabled: true
                                }
                            },
                        }
                    });

                    // تعيين ألوان الـ badges بناءً على ألوان المخطط
                    document.querySelectorAll('[id^="ContractsBadge"]').forEach((badge, index) => {
                        badge.style.backgroundColor = backgroundColorContracts[index];
                    });

                    // دالة لتبديل رؤية البيانات في المخطط الأهمي
                    function toggleDatasetContract(index) {
                        const meta = ContractsChartId.getDatasetMeta(0);
                        meta.data[index].hidden = !meta.data[index].hidden;
                        ContractsChartId.update();

                        const badge = document.querySelector(`#ContractsBadge${index}`);
                        if (meta.data[index].hidden) {
                            badge.style.opacity = 0.5;
                        } else {
                            badge.style.opacity = 1;
                        }
                    }
                </script>
            @endif
        @endcan
        <!--  العقود  -->

        <!-- المشاريع -->
        @canany(['كل المشاريع', 'المشاريع الخاصة بي', 'الإعتماد الفني للمشاريع'])

            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header pb-3 d-flex justify-content-between">
                        <div class="card-title m-0 me-2">
                            <h5 class="mb-1">المشاريع</h5>
                            <small class="card-subtitle">عرض آخر 5 مشاريع </small>
                        </div>
                    </div>
                    <div class="border-1 border-light border-dashed mb-1"></div>
                    <div class="card-body p-0">
                        @if ($projects->count() > 0)
                            <table class="table table-striped table-sm mb-0" style="table-layout: fixed; width: 100%;">
                                <colgroup>
                                    <col style="width: 33.33%;">
                                    <col style="width: 33.33%;">
                                    <col style="width: 33.33%;">
                                </colgroup>
                                <thead style="padding: 0px 2px !important; ">
                                    <tr class="text-center">
                                        <th class="py-2 px-3">المشروع</th>
                                        <th class="py-2 px-3">مدير المشروع</th>
                                        <th class="py-2 px-3">التقدم</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($projects as $project)
                                        <tr class="text-center">
                                            <td class="py-2 px-5 text-truncate">
                                                <a href="{{ route('projects.show', $project->id) }}">
                                                    <div class="d-flex align-items-center">
                                                        <div class="badge bg-label-secondary me-4 rounded p-1_5">
                                                            <i class="ti ti-folder ti-md"></i>
                                                        </div>
                                                        <small class="mb-0 project-name text-truncate"
                                                            title="{{ $project->project_name }}">
                                                            {{ $project->project_name }}
                                                        </small>

                                                    </div>
                                                </a>
                                            </td>

                                            <td class="small py-2 px-5 text-truncate">
                                                @if ($project->manager_user)
                                                    <a href="{{ route('account.employee.profile', $project->manager_user->employee->id) }}"
                                                        class="text-truncate"
                                                        title="{{ $project->manager_user->employee->name }}">
                                                        {{ $project->manager_user->employee->name }}
                                                    </a>
                                                @else
                                                    <span>لم يتم تعيين المدير بعد</span>
                                                @endif
                                            </td>
                                            <td class="small py-2 px-5">
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1 me-2"
                                                        style="height: 8px; border-radius: 5px;">
                                                        <div class="progress-bar" role="progressbar"
                                                            style="width: {{ $project->progress }}%; background-color: {{ $project->progress >= 75 ? '#d1ae74' : ($project->progress >= 50 ? '#f5b341' : '#644208') }};"
                                                            aria-valuenow="{{ $project->progress }}" aria-valuemin="0"
                                                            aria-valuemax="100">
                                                        </div>
                                                    </div>
                                                    <span style="font-size: 0.9rem;">{{ $project->progress }}%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="text-center my-3">لا يوجد مشاريع حاليا</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center justify-content-between flex-wrap">
                        <div class="d-flex flex-column flex-grow-1 text-center">
                            <div class="card-title mb-2">
                                <h5 class="mb-0 text-nowrap">إحصائيات المشاريع</h5>
                            </div>
                            <div class="chart-statistics mb-2">
                                <h3 class="card-title mb-0 text-primary">{{ $totalProjects }}</h3>
                                <a href="{{ route('projects.index') }}">
                                    <p class="text-muted text-nowrap mb-0">إجمالي المشاريع</p>
                                </a>
                            </div>
                        </div>
                        @if ($totalProjects > 0)
                            <div id="projectChartContainer" class="w-100" style="height: 150px;">
                                <canvas id="projectChartUnique"></canvas>
                            </div>
                        @endif
                    </div>
                    @if ($totalProjects > 0)
                        <div class="card-footer">
                            <div class="d-flex justify-content-center gap-2 flex-wrap">
                                @foreach ($charProjects as $index => $category)
                                    <span id="project" class="badge" style="cursor: pointer;"
                                        onclick="toggleDatasetUnique({{ $index }})"
                                        data-index="{{ $index }}">
                                        {{ $category['name'] }} ({{ $category['count'] }})
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <p class="text-center">لا يوجد مشاريع حاليا</p>
                    @endif
                </div>
            </div>

            @if ($totalProjects > 0)
                <script>
                    const categoriesUnique = @json($charProjects);

                    const labelsUnique = categoriesUnique.map(cat => cat.name);
                    const dataUnique = categoriesUnique.map(cat => cat.count);

                    const colorsUnique = @json($colorsUnique);
                    const backgroundColorsUnique = labelsUnique.map((_, index) => colorsUnique[index % colorsUnique.length]);

                    const ctxUnique = document.getElementById('projectChartUnique').getContext('2d');
                    const projectChartUnique = new Chart(ctxUnique, {
                        type: 'doughnut',
                        data: {
                            labels: labelsUnique,
                            datasets: [{
                                data: dataUnique,
                                backgroundColor: backgroundColorsUnique,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    enabled: true
                                }
                            },
                        }
                    });

                    document.querySelectorAll('#project').forEach((badge, index) => {
                        badge.style.backgroundColor = backgroundColorsUnique[index];
                    });

                    function toggleDatasetUnique(index) {
                        const meta = projectChartUnique.getDatasetMeta(0);
                        meta.data[index].hidden = !meta.data[index].hidden;
                        projectChartUnique.update();

                        const badge = document.querySelector(`#project[data-index="${index}"]`);
                        badge.style.opacity = meta.data[index].hidden ? 0.5 : 1;
                    }
                </script>
            @endif
        @endcan
        <!-- المشاريع -->

        <!-- الخصوم -->
        @canany(['كل الخصوم', 'الخصوم الخاصين بي'])

            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header pb-3 d-flex justify-content-between">
                        <div class="card-title m-0 me-2">
                            <h5 class="mb-1"> الخصوم</h5>
                            <small class="card-subtitle">عرض اخر 5 خصوم تم اضافتهم </small>
                        </div>
                    </div>
                    <div class="border-1 border-light border-dashed mb-1"></div>
                    <div class="card-body p-0">
                        @if ($opponents->count() > 0)
                            <table class="table table-striped table-sm mb-0" style="table-layout: fixed; width: 100%;">
                                <colgroup>
                                    <col style="width: 30%;">
                                    <col style="width: 25%;">
                                    <col style="width: 25%;">
                                    <col style="width: 20%;">
                                </colgroup>
                                <thead style="padding: 0px 2px !important; ">
                                    <tr class="text-center">
                                        <th class="py-2 px-3">اسم الخصم</th>
                                        <th class="py-2 px-3">نوع الخصم</th>
                                        <th class="py-2 px-3">اضيف بواسطة</th>
                                        <th class="py-2 px-3"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($opponents as $opponent)
                                        <tr class="text-center">
                                            <td class="py-2 px-5 text-truncate">
                                                <a href="{{ route('legal-affairs.opponents.show', $opponent->id) }}">
                                                    <div class="d-flex align-items-center">
                                                        <i class="ti ti-rosette-discount-check-filled text-danger mx-2 py-1"
                                                            title="عميل"></i>
                                                        <small class="mb-0  text-truncate" title="{{ $opponent->name }}">
                                                            {{ $opponent->name }}
                                                        </small>
                                                    </div>
                                                </a>
                                            </td>

                                            <td class="py-2 px-5 text-truncate">
                                                <small class="mb-0 text-truncate">
                                                    {{ $opponent->type->label() }}
                                                </small>
                                            </td>

                                            <td class="py-2 px-5 text-truncate">
                                                @isset($opponent->createdBy)
                                                    <a href="{{ route('account.employee.profile', $opponent->createdBy->employee->id) }}"
                                                        class="mb-0 text-truncate"
                                                        title="{{ $opponent->createdBy->employee->name ?? '' }}">
                                                        <small class="mb-0 text-truncate">
                                                            {{ $opponent->createdBy->employee->name ?? '' }}
                                                        </small>
                                                    </a>
                                                @endisset

                                            </td>
                                            <td class="py-2 px-5 text-truncate">
                                                <div class="dropdown">
                                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="ti ti-dots-vertical"></i>
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item waves-effect"
                                                            href="sms:{{ $opponent->contact_number ?? '' }}">
                                                            <i class="ti ti-message me-1"></i> إرسال رسالة
                                                        </a>
                                                        <a class="dropdown-item waves-effect"
                                                            href="https://wa.me/{{ $opponent->contact_number ?? '' }}">
                                                            <i class="ti ti-brand-whatsapp me-1"></i> واتساب
                                                        </a>
                                                        <a class="dropdown-item waves-effect"
                                                            href="mailto:{{ $opponent->email ?? '' }}">
                                                            <i class="ti ti-mail me-1"></i> بريد إلكتروني
                                                        </a>

                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            {{-- <table class="table table-striped table-sm mb-0" style="table-layout: fixed; width: 100%;">
                                <colgroup>
                                    <col style="width: 50%;">
                                    <col style="width: 25%;">
                                    <col style="width: 25%;">

                                </colgroup>
                                <thead style="padding: 0px 2px !important; ">
                                    <tr class="text-c">
                                        <th class="py-2 px-3">اسم الخصم</th>
                                        <th class="py-2 px-3">النوع</th>
                                        <th class="py-2 px-3"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($opponents as $opponent)
                                        <tr class="text-c">
                                            <td class="py-2 px-5 text-truncate">

                                                <h6 class="mb-0 text-truncate" title="{{ $opponent->name }}">

                                                    <img src="{{ asset('assets/img/branding/Alburhan-Logo.png') }}"
                                                        alt="Profile" class="rounded-circle" width="40" height="40">

                                                    {{ $opponent->name }}
                                                </h6>

                                            </td>
                                            <td class="py-2 px-5 text-truncate">
                                                <h6 class="mb-0 text-truncate">
                                                    {{ $opponent->type == 'individual' ? 'فرد' : 'شخصية اعتبارية' }}

                                                </h6>
                                            </td>

                                            <td class="py-2 px-5 text-truncate">
                                                <div class="dropdown">
                                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="ti ti-dots-vertical"></i>
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item waves-effect"
                                                            href="sms:{{ $opponent->phone ?? '' }}">
                                                            <i class="ti ti-message me-1"></i> إرسال رسالة
                                                        </a>
                                                        <a class="dropdown-item waves-effect"
                                                            href="https://wa.me/{{ $opponent->phone ?? '' }}">
                                                            <i class="ti ti-brand-whatsapp me-1"></i> واتساب
                                                        </a>
                                                        <a class="dropdown-item waves-effect"
                                                            href="mailto:{{ $opponent->email ?? '' }}">
                                                            <i class="ti ti-mail me-1"></i> بريد إلكتروني
                                                        </a>

                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table> --}}
                        @else
                            <p class="text-center">لا يوجد خصوم حاليا</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center justify-content-between flex-wrap">
                        <div class="d-flex flex-column flex-grow-1 text-center">
                            <div class="card-title mb-2">
                                <h5 class="mb-0 text-nowrap">إحصائيات الخصوم</h5>
                            </div>
                            <div class="chart-statistics mb-2">
                                <h3 class="card-title mb-0 text-primary">{{ $totalOppents }}</h3>
                                <p class="text-muted text-nowrap mb-0">إجمالي الخصوم</p>
                            </div>
                        </div>
                        @if ($opponents->count() > 0)
                            <!-- تكبير حجم المخطط -->
                            <div id="oppentsCanvas" class="w-100" style="height: 150px;">
                                <canvas id="oppentsChartId"></canvas>
                            </div>
                        @endif

                    </div>
                    @if ($opponents->count() > 0)
                        <div class="card-footer">
                            <div class="d-flex justify-content-center gap-2 flex-wrap">
                                @foreach ($charOpponents as $index => $item)
                                    <span id="oppentsBadge{{ $index }}" class="badge" style=" cursor: pointer;"
                                        onclick="toggleDatasetOppents({{ $index }})"
                                        data-index="{{ $index }}">
                                        {{ $item['name'] }} ({{ $item['count'] }})
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <p class="text-center">لا يوجد خصوم حاليا</p>
                    @endif
                </div>
            </div>

            @if ($opponents->count() > 0)
                <script>
                    // تأكد من أن معرف المخطط فريد لتجنب التضارب
                    const oppents = @json($charOpponents);

                    const labelsOppents = oppents.map(cat => cat.name);
                    const dataOppents = oppents.map(cat => cat.count);

                    const colorsOppents = @json($colorsUnique);
                    const backgroundColorOppents = labelsOppents.map((_, index) => colorsOppents[index % colorsOppents.length]);

                    const ctxOppents = document.getElementById('oppentsChartId').getContext('2d');
                    const oppentsChartId = new Chart(ctxOppents, {
                        type: 'doughnut',
                        data: {
                            labels: labelsOppents,
                            datasets: [{
                                data: dataOppents,
                                backgroundColor: backgroundColorOppents,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    enabled: true
                                }
                            },
                        }
                    });

                    // تعيين ألوان الـ badges بناءً على ألوان المخطط
                    document.querySelectorAll('[id^="oppentsBadge"]').forEach((badge, index) => {
                        badge.style.backgroundColor = backgroundColorOppents[index];
                    });

                    // دالة لتبديل رؤية البيانات في المخطط الأهمي
                    function toggleDatasetOppents(index) {
                        const meta = oppentsChartId.getDatasetMeta(0);
                        meta.data[index].hidden = !meta.data[index].hidden;
                        oppentsChartId.update();

                        const badge = document.querySelector(`#oppentsBadge${index}`);
                        if (meta.data[index].hidden) {
                            badge.style.opacity = 0.5;
                        } else {
                            badge.style.opacity = 1;
                        }
                    }
                </script>
            @endif
        @endcan
        <!-- الخصوم -->



        <!--  الوكالات  -->
        @canany(['كل الوكالات', 'الوكالات الخاصة بي'])

            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header pb-3 d-flex justify-content-between">
                        <div class="card-title m-0 me-2">
                            <h5 class="mb-1"> الوكالات</h5>
                            <small class="card-subtitle">عرض اخر 5 وكالات</small>
                        </div>
                    </div>
                    <div class="border-1 border-light border-dashed mb-1"></div>
                    <div class="card-body p-0">
                        @if ($powerOfAttorney->count() > 0)
                            <table class="table table-striped table-sm mb-0" style="table-layout: fixed; width: 100%;">
                                <colgroup>
                                    <col style="width: 33.33%;">
                                    <col style="width: 33.33%;">
                                    <col style="width: 33.33%;">

                                </colgroup>
                                <thead style="padding: 0px 2px !important; ">
                                    <tr class="text-center">
                                        <th class="py-2 px-3">اسم الوكالة</th>
                                        <th class="py-2 px-3">رقم الوكالة</th>
                                        <th class="py-2 px-3">الحالة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($powerOfAttorney as $item)
                                        <tr class="text-center">
                                            <td class="py-2 px-5 text-truncate small">
                                                <a href="{{ route('legal-affairs.power-attorney.show', $item->id) }}">
                                                    {{ $item->power_name }}
                                                </a>
                                            </td>

                                            <td class="py-2 px-5 text-truncate">
                                                <small class="mb-0 text-truncate" title="{{ $item->power_number }}">
                                                    {{ $item->power_number }}

                                                </small>
                                            </td>

                                            <td class="small py-2 px-5">
                                                {{ $item->status->label() }}
                                            </td>

                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="text-center">لا يوجد وكالات حاليا</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center justify-content-between flex-wrap">
                        <div class="d-flex flex-column flex-grow-1 text-center">
                            <div class="card-title mb-2">
                                <h5 class="mb-0 text-nowrap">إحصائيات الوكالات حسب الحالة</h5>
                            </div>
                            <div class="chart-statistics mb-2">
                                <h3 class="card-title mb-0 text-primary">{{ $totalPowerOfAttorney }}</h3>
                                <p class="text-muted text-nowrap mb-0">إجمالي الوكالات</p>
                            </div>
                        </div>
                        @if ($powerOfAttorney->count() > 0)
                            <!-- تكبير حجم المخطط -->
                            <div id="PowerOfAttorneyCanvas" class="w-100" style="height: 150px;">
                                <canvas id="PowerOfAttorneyChartId"></canvas>
                            </div>
                        @endif

                    </div>

                    @if ($powerOfAttorney->count() > 0)
                        <div class="card-footer">
                            <div class="d-flex justify-content-center gap-2 flex-wrap">
                                @foreach ($charPowerOfAttorney as $index => $item)
                                    <span id="PowerOfAttorneyBadge{{ $index }}" class="badge"
                                        style=" cursor: pointer;" onclick="toggleDatasetPower({{ $index }})"
                                        data-index="{{ $index }}">
                                        {{ $item['name'] }} ({{ $item['count'] }})
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <p class="text-center">لا يوجد وكالات حاليا</p>
                    @endif
                </div>
            </div>

            @if ($powerOfAttorney->count() > 0)
                <script>
                    // تأكد من أن معرف المخطط فريد لتجنب التضارب
                    const PowerOfAttorney = @json($charPowerOfAttorney);

                    const labelsPowerOfAttorney = PowerOfAttorney.map(cat => cat.name);
                    const dataPowerOfAttorney = PowerOfAttorney.map(cat => cat.count);

                    const colorsPowerOfAttorney = @json($colorsUnique);
                    const backgroundColorPowerOfAttorney = labelsPowerOfAttorney.map((_, index) => colorsPowerOfAttorney[index %
                        colorsPowerOfAttorney.length]);

                    const ctxPowerOfAttorney = document.getElementById('PowerOfAttorneyChartId').getContext('2d');
                    const PowerOfAttorneyChartId = new Chart(ctxPowerOfAttorney, {
                        type: 'doughnut',
                        data: {
                            labels: labelsPowerOfAttorney,
                            datasets: [{
                                data: dataPowerOfAttorney,
                                backgroundColor: backgroundColorPowerOfAttorney,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    enabled: true
                                }
                            },
                        }
                    });

                    // تعيين ألوان الـ badges بناءً على ألوان المخطط
                    document.querySelectorAll('[id^="PowerOfAttorneyBadge"]').forEach((badge, index) => {
                        badge.style.backgroundColor = backgroundColorPowerOfAttorney[index];
                    });

                    // دالة لتبديل رؤية البيانات في المخطط الأهمي
                    function toggleDatasetPower(index) {
                        const meta = PowerOfAttorneyChartId.getDatasetMeta(0);
                        meta.data[index].hidden = !meta.data[index].hidden;
                        PowerOfAttorneyChartId.update();

                        const badge = document.querySelector(`#PowerOfAttorneyBadge${index}`);
                        if (meta.data[index].hidden) {
                            badge.style.opacity = 0.5;
                        } else {
                            badge.style.opacity = 1;
                        }
                    }
                </script>
            @endif
        @endcan
        <!--  الوكالات  -->

        {{-- الدعاوى --}}
        @canany(['كل الدعاوى', 'الدعاوى الخاصة بي'])

            {{-- الدعاوى  --}}
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header pb-3 d-flex justify-content-between">
                        <div class="card-title m-0 me-2">
                            <h5 class="mb-1">الدعاوى</h5>
                            <small class="card-subtitle">عرض اخر 5 دعوى نشطة </small>
                        </div>
                    </div>
                    <div class="border-1 border-light border-dashed mb-1"></div>
                    @if ($lawsuits->count() > 0)
                        <div class="card-body p-0">
                            <table class="table table-striped table-sm mb-0" style="table-layout: fixed; width: 100%;">
                                <colgroup>
                                    <col style="width: 25%;">
                                    <col style="width: 25%;">
                                    <col style="width: 25%;">
                                </colgroup>
                                <thead style="padding: 0px 2px !important; ">
                                    <tr class="text-center">
                                        <th class="py-2 px-3">الدعوى</th>
                                        <th class="py-2 px-3"> الجلسات</th>
                                        <th class="py-2 px-3">رقم الدعوى</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($lawsuits as $lawsuit)
                                        <tr class="text-center">
                                            <td class="small py-2 px-5 text-truncate"><a
                                                    href="{{ route('legal-affairs.lawsuits.show', $lawsuit->id) }}">
                                                    <div class="d-flex align-items-center">

                                                        <div class="badge bg-label-secondary me-3 rounded p-1_5">
                                                            <i class="ti ti-gavel ti-md"></i>
                                                        </div>
                                                        <small class="mb-0 project-name text-truncate"
                                                            title="{{ $lawsuit->name }}">
                                                            {{ $lawsuit->name }}
                                                        </small>

                                                    </div>
                                                </a>
                                            </td>

                                            <td class="small py-2 px-5 text-truncate">
                                                <small class="mb-0 text-truncate" title="عدد الجلسات">
                                                    @if ($lawsuit->sessions->count() > 0)
                                                        <a href="{{ route('legal-affairs.lawsuits.show', $lawsuit->id) . '#sessions' }}"
                                                            class="mb-0 text-truncate ">
                                                            {{ $lawsuit->sessions->count() }} جلسة
                                                        </a>
                                                    @else
                                                        لا يوجد جلسات
                                                    @endif

                                                </small>
                                            </td>

                                            <td class="small py-2 px-5 text-truncate">
                                                <small class="mb-0 text-truncate" title="{{ $lawsuit->lawsuit_number }}">
                                                    {{ $lawsuit->lawsuit_number }}
                                                </small>
                                            </td>

                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center">لا يوجد دعاوى حاليا</p>
                    @endif
                </div>
            </div>
            {{-- الدعاوى  --}}
            <!-- مخطط توزيع الدعاوى حسب قسم التصنيف -->
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">

                    </div>
                    @if ($lawsuits->count() > 0)
                        <div class="card-body d-flex justify-content-center align-items-center">
                            <!-- تأكد من تحديد ارتفاع مناسب -->
                            <div id="lawsuitDepartmentBarChart" class="h-100 w-100 mt-5" style="max-width: 800px; "></div>
                        </div>
                    @else
                        <p class="text-center">لا يوجد دعاوى حاليا</p>
                    @endif

                </div>
            </div>
            @if ($lawsuits->count() > 0)
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // مخطط توزيع الدعاوى بناءً على قسم العقد
                        const lawsuitDepartmentCounts =
                            @json($lawsuitStatusCounts); // تأكد من تمرير $lawsuitStatusCounts للمشاريع في الـ Controller

                        const departmentIds = Object.keys(lawsuitDepartmentCounts);
                        const departmentCounts = departmentIds.map(id => lawsuitDepartmentCounts[id].total);
                        const departmentNames = departmentIds.map(id => lawsuitDepartmentCounts[id]
                            .name); // الحصول على أسماء الأقسام

                        // يمكنك تعيين الألوان لقسم العقد حسب الحاجة
                        const departmentColors = @json($colorsUnique);

                        // تعيين الألوان بناءً على أقسام العقد


                        // إعدادات مخطط الأعمدة
                        const barOptions = {
                            chart: {
                                height: 380,
                                type: 'bar',
                                toolbar: {
                                    show: false
                                }
                            },
                            colors: departmentColors,
                            plotOptions: {
                                bar: {
                                    horizontal: false,
                                    columnWidth: '55%',
                                    startingShape: 'rounded', // حواف مدببة
                                    borderRadius: 20, // تعيين نصف قطر الحواف المدببة
                                    // distributed: true // تفعيل توزيع الألوان على كل عمود
                                },
                            },
                            dataLabels: {
                                enabled: true,
                                formatter: function(val) {
                                    return val;
                                },
                                style: {
                                    colors: ['#333333']
                                }
                            },
                            stroke: {
                                show: true,
                                width: 2,
                                colors: ['transparent']
                            },
                            series: [{
                                name: 'عدد الدعاوى',
                                data: departmentCounts
                            }],
                            xaxis: {
                                categories: departmentNames,
                                title: {
                                    text: 'أقسام العقود',
                                    style: {
                                        fontSize: '16px',
                                        fontWeight: 'bold',
                                        color: '#333333'
                                    }
                                },
                                labels: {
                                    style: {
                                        fontSize: '14px',
                                        colors: '#333333'
                                    }
                                }
                            },
                            yaxis: {
                                title: {
                                    style: {
                                        fontSize: '16px',
                                        fontWeight: 'bold',
                                        color: '#333333'
                                    }
                                },
                                labels: {
                                    style: {
                                        fontSize: '14px',
                                        colors: '#333333'
                                    }
                                }
                            },
                            fill: {
                                opacity: 1
                            },
                            tooltip: {
                                y: {

                                }
                            },
                            title: {
                                text: 'توزيع الدعاوى حسب التصنيف',
                                align: 'center',
                                margin: 20,
                                offsetX: 0,
                                offsetY: 0,
                                style: {
                                    fontSize: '20px',
                                    fontWeight: 'bold',
                                    color: '#333333'
                                }
                            },
                            legend: {
                                show: false
                            },
                            grid: {
                                borderColor: '#e7e7e7',
                                padding: {
                                    top: 0,
                                    bottom: 0
                                }
                            }
                        };

                        const barChart = new ApexCharts(document.querySelector("#lawsuitDepartmentBarChart"), barOptions);
                        barChart.render();
                    });
                </script>
            @endif

        @endcan

        <!-- الموظفين -->
        @canany(['كل الموظفين'])

            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header pb-3 d-flex justify-content-between">
                        <div class="card-title m-0 me-2">
                            <h5 class="mb-1"> الموظفين</h5>
                            <small class="card-subtitle">عرض اخر 5 موظفين تم اضافتهم </small>
                        </div>
                    </div>
                    <div class="border-1 border-light border-dashed mb-1"></div>

                    <div class="card-body p-0">
                        @if ($employees->count() > 0)
                            <table class="table table-striped table-sm mb-0" style="table-layout: fixed; width: 100%;">
                                <colgroup>
                                    <col style="width: 8%;">
                                    <col style="width: 22.5%;">
                                    <col style="width: 22.5%;">
                                    <col style="width: 22.5%;">
                                    <col style="width: 10%;">
                                </colgroup>
                                <thead style="padding: 0px 2px !important; ">
                                    <tr class="text-center">
                                        <th class="py-2 px-3"> </th>
                                        <th class="py-2 px-3">اسم الموظف</th>
                                        <th class="py-2 px-3">اللقب</th>
                                        <th class="py-2 px-3">المسمى الوظيفي</th>
                                        <th class="py-2 px-3"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($employees as $employee)
                                        <tr class="text-center">
                                            <td class="">

                                                @if ($employee->profile_picture)
                                                    <img src="{{ asset('storage/' . $employee->profile_picture) }}"
                                                        alt="Profile" class="rounded-circle" width="25" height="25">
                                                @else
                                                    <img src="{{ asset('assets/img/branding/Alburhan-Logo.png') }}"
                                                        alt="Profile" class="rounded-circle" width="25" height="25">
                                                @endif

                                            </td>

                                            <td class="py-2 px-5 text-truncate">
                                                <a href="{{ route('account.employee.profile', $employee->id) }}">
                                                    <small class="mb-0 text-truncate"
                                                        title="{{ $employee->getRawOriginal('name') }}">
                                                        {{ $employee->getRawOriginal('name') }}
                                                    </small>
                                                </a>
                                            </td>

                                            <td class="py-2 px-5 text-truncate">
                                                <small class="mb-0 text-truncate">
                                                    {{ $employee->name }}
                                                </small>
                                            </td>
                                            <td class="py-2 px-5 text-truncate">
                                                <small class="mb-0 text-truncate">
                                                    {{ $employee->user->roles->first()?->name }}
                                                </small>

                                            <td class="py-2 px-5 text-truncate">
                                                <div class="dropdown">
                                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="ti ti-dots-vertical"></i>
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item waves-effect"
                                                            href="sms:{{ $employee->mobile ?? '0000000000' }}">
                                                            <i class="ti ti-message me-1"></i> إرسال رسالة
                                                        </a>
                                                        <a class="dropdown-item waves-effect"
                                                            href="https://wa.me/{{ $employee->mobile ?? '0000000000' }}">
                                                            <i class="ti ti-brand-whatsapp me-1"></i> واتساب
                                                        </a>
                                                        <a class="dropdown-item waves-effect"
                                                            href="mailto:{{ $employee->personal_email ?? 'email@example.com' }}">
                                                            <i class="ti ti-mail me-1"></i> بريد إلكتروني
                                                        </a>
                                                        <a class="dropdown-item waves-effect" href="javascript:void(0);"
                                                            title="إضافة مهمة">
                                                            <i class="ti ti-plus me-1"></i> إضافة مهمة
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="text-center">لا يوجد موظفين حاليا</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center justify-content-between flex-wrap">
                        <div class="d-flex flex-column flex-grow-1 text-center">
                            <div class="card-title mb-2">
                                <h5 class="mb-0 text-nowrap">إحصائيات الحضور والغياب</h5>
                            </div>
                        </div>
                        @if ($employees->count() > 0)
                            <!-- تكبير حجم المخطط -->
                            <div id="attendanceChartContainer" class="w-100" style="height: 150px;">
                                <canvas id="attendanceChart"></canvas>
                            </div>
                        @endif

                    </div>
                    @if ($employees->count() > 0)
                        <div class="card-footer">
                            <div class="d-flex justify-content-center gap-2 flex-wrap">
                                <!-- Badge للحضور -->
                                <span id="attendanceBadge0" class="badge" style="cursor: pointer;"
                                    onclick="toggleDatasetAttendance(0)" data-index="0">
                                    الحضور (70)
                                </span>
                                <!-- Badge للغياب -->
                                <span id="attendanceBadge1" class="badge" style="cursor: pointer;"
                                    onclick="toggleDatasetAttendance(1)" data-index="1">
                                    الغياب (10)
                                </span>
                            </div>
                        </div>
                    @else
                        <p class="text-center">لا يوجد موظفين حاليا</p>
                    @endif
                </div>
            </div>
            @if ($employees->count() > 0)
                <script>
                    // تعريف التسميات والبيانات الثابتة
                    const labelsAttendance = ['حضور', 'غياب'];
                    const dataAttendance = [70, 10];

                    // تحديد الألوان للفئات
                    const backgroundColorsAttendance = @json($colorsUnique); // يمكنك تعديل الألوان حسب رغبتك

                    // الحصول على سياق الرسم البياني
                    const ctxAttendance = document.getElementById('attendanceChart').getContext('2d');
                    const attendanceChart = new Chart(ctxAttendance, {
                        type: 'doughnut',
                        data: {
                            labels: labelsAttendance,
                            datasets: [{
                                data: dataAttendance,
                                backgroundColor: backgroundColorsAttendance,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false // إخفاء الأسطورة إذا كنت لا تحتاجها
                                },
                                tooltip: {
                                    enabled: true // تمكين التلميحات عند المرور بالفأرة
                                }
                            },
                        }
                    });

                    // تعيين ألوان الـ badges بناءً على ألوان المخطط
                    document.querySelectorAll('[id^="attendanceBadge"]').forEach((badge, index) => {
                        if (index < backgroundColorsAttendance.length) {
                            badge.style.backgroundColor = backgroundColorsAttendance[index];
                        }
                    });

                    // دالة لتبديل رؤية البيانات في المخطط الأهمي
                    function toggleDatasetAttendance(index) {
                        const meta = attendanceChart.getDatasetMeta(0);
                        meta.data[index].hidden = !meta.data[index].hidden;
                        attendanceChart.update();

                        const badge = document.querySelector(`#attendanceBadge${index}`);
                        if (meta.data[index].hidden) {
                            badge.style.opacity = 0.5;
                        } else {
                            badge.style.opacity = 1;
                        }
                    }
                </script>
            @endif

        @endcan
        <!-- الموظفين -->

    </div>
    <!--- للمهام و الخطوات  -->

@endsection
