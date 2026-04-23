@extends('layouts.layoutMaster')

@section('title', 'تقرير المشاريع')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">تقرير المشاريع</a>
        <i class="ti ti-star favorite-icon" data-page-name="تقرير المشاريع" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
            'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
            'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
            'resources/assets/vendor/libs/select2/select2.scss',
            'resources/assets/vendor/libs/@form-validation/form-validation.scss',
            'resources/assets/vendor/libs/animate-css/animate.scss',
            'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite([
        'resources/assets/vendor/libs/moment/moment.js',
        'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
        'resources/assets/vendor/libs/select2/select2.js',
        'resources/assets/vendor/libs/@form-validation/popular.js',
        'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
        'resources/assets/vendor/libs/@form-validation/auto-focus.js',
        'resources/assets/vendor/libs/cleavejs/cleave.js',
        'resources/assets/vendor/libs/cleavejs/cleave-phone.js',
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
        'resources/assets/js/projectsReport.js',
    ])
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('page-style')
    @vite(['resources/css/dataTable.css'])
@endsection

@section('content')
    <!-- محتوى التقرير -->
    <style>
        .badge-custom {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
            color: #fff;
            border-radius: 5px;
            text-align: center;
            font-size: 0.8rem;
            white-space: normal;
            word-wrap: break-word;
            word-break: break-word;
            min-height: 40px;
            max-height: 40px;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- قسم الإحصائيات -->
    <div class="row">
        <!-- المشاريع حسب الحالة -->
        <div class="col-12 col-xl-6 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="projectsByStatusCard">
                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">المشاريع حسب الحالة</h5>
                            <button type="button" id="exportProjectsByStatusBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- العمود الأول: العنوان وإجمالي المشاريع والشارات -->
                        <div class="col-12 col-md-6">
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalProjects }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي المشاريع</p>
                                </div>
                            </div>

                            <!-- الشارات -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($projectsByStatus as $index => $item)
                                    @php
                                        $isLast = $index === count($projectsByStatus) - 1;
                                        $isOdd = count($projectsByStatus) % 2 !== 0;
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <span class="badge-custom" id="badge-projectsByStatus-{{ $index }}"
                                            style="background-color: {{ $colorsUnique[$index % count($colorsUnique)] }}">
                                            {{ $item['project_status']['name'] ?? 'غير محدد' }} ({{ $item['count'] }})
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- العمود الثاني: المخطط -->
                        <div class="col-12 col-md-6 d-flex align-items-center justify-content-center mb-3 mb-md-0">
                            <div style="position: relative; width: 100%; height: 100%;">
                                <canvas id="projectsByStatusChart" style="width: 100%; height: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- المشاريع حسب مدير المشروع -->
        <div class="col-12 col-xl-6 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="projectsByManagerCard">
                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">المشاريع حسب مدير المشروع (آخر 10)</h5>
                            <button type="button" id="exportProjectsByManagerBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- العمود الأول: العنوان وإجمالي المشاريع والشارات -->
                        <div class="col-12 col-md-6">
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalProjects }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي المشاريع</p>
                                </div>
                            </div>

                            <!-- الشارات -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($projectsByManager as $index => $item)
                                    @php
                                        $isLast = $index === count($projectsByManager) - 1;
                                        $isOdd = count($projectsByManager) % 2 !== 0;
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <span class="badge-custom" id="badge-projectsByManager-{{ $index }}"
                                            style="background-color: {{ $colorsUnique[$index % count($colorsUnique)] }}">
                                            {{ mb_strlen($item['name'], 'UTF-8') > 15 ? mb_substr($item['name'], 0, 15, 'UTF-8') . '...' : $item['name'] }}
                                            ({{ $item['count'] }})
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- العمود الثاني: المخطط -->
                        <div class="col-12 col-md-6 d-flex align-items-center justify-content-center mb-3 mb-md-0">
                            <div style="position: relative; width: 100%; height: 100%;">
                                <canvas id="projectsByManagerChart" style="width: 100%; height: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- المشاريع حسب العقد -->
        <div class="col-12 col-xl-6 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="projectsByContractCard">
                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">المشاريع حسب العقد (آخر 10)</h5>
                            <button type="button" id="exportProjectsByContractBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- العمود الأول: العنوان وإجمالي المشاريع والشارات -->
                        <div class="col-12 col-md-6">
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalProjects }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي المشاريع</p>
                                </div>
                            </div>

                            <!-- الشارات -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($projectsByContract as $index => $item)
                                    @php
                                        $isLast = $index === count($projectsByContract) - 1;
                                        $isOdd = count($projectsByContract) % 2 !== 0;
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <span class="badge-custom" id="badge-projectsByContract-{{ $index }}"
                                            style="background-color: {{ $colorsUnique[$index % count($colorsUnique)] }}">

                                            {{ mb_strlen($item['name'], 'UTF-8') > 15 ? mb_substr($item['name'], 0, 15, 'UTF-8') . '...' : $item['name'] }} ({{ $item['count'] }})

                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- العمود الثاني: المخطط -->
                        <div class="col-12 col-md-6 d-flex align-items-center justify-content-center mb-3 mb-md-0">
                            <div style="position: relative; width: 100%; height: 100%;">
                                <canvas id="projectsByContractChart" style="width: 100%; height: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        <!-- المشاريع حسب الوقت المتبقي -->
        <div class="col-12 col-xl-6 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="projectsByTimeRemainingCard">
                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">المشاريع حسب الوقت المتبقي</h5>
                            <button type="button" id="exportProjectsByTimeRemainingBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- العمود الأول: العنوان وإجمالي المشاريع والشارات -->
                        <div class="col-12 col-md-6">
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalProjects }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي المشاريع</p>
                                </div>
                            </div>

                            <!-- الشارات -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($projectsByTimeRemaining as $index => $item)
                                    @php
                                        $isLast = $index === count($projectsByTimeRemaining) - 1;
                                        $isOdd = count($projectsByTimeRemaining) % 2 !== 0;
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <span class="badge-custom" id="badge-projectsByTimeRemaining-{{ $index }}"
                                            style="background-color: {{ $colorsUnique[$index % count($colorsUnique)] }}">
                                            {{ $item['name'] }} ({{ $item['count'] }})
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- العمود الثاني: المخطط -->
                        <div class="col-12 col-md-6 d-flex align-items-center justify-content-center mb-3 mb-md-0">
                            <div style="position: relative; width: 100%; height: 100%;">
                                <canvas id="projectsByTimeRemainingChart" style="width: 100%; height: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

          <!-- المشاريع حسب شهر البدء (هجري) -->
          <div class="col-12  mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="projectsByStartMonthCard">
                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">المشاريع حسب شهر البدء (هجري)</h5>
                            <button type="button" id="exportProjectsByStartMonthBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- العمود الأول: العنوان وإجمالي المشاريع والشارات -->
                        <div class="col-12 col-md-6">
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalProjects }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي المشاريع</p>
                                </div>
                            </div>

                            <!-- الشارات -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($projectsByStartMonth as $index => $item)
                                    @php
                                        $isLast = $index === count($projectsByStartMonth) - 1;
                                        $isOdd = count($projectsByStartMonth) % 2 !== 0;
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <span class="badge-custom" id="badge-projectsByStartMonth-{{ $index }}"
                                            style="background-color: {{ $colorsUnique[$index % count($colorsUnique)] }}">
                                            {{ $item['month'] }} ({{ $item['count'] }})
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- العمود الثاني: المخطط -->
                        <div class="col-12 col-md-6 d-flex align-items-center justify-content-center mb-3 mb-md-0">
                            <div style="position: relative; width: 100%; height: 100%;">
                                <canvas id="projectsByStartMonthChart" style="width: 100%; height: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div> <!-- نهاية الصف -->

    <script>
        window.projectsByStatusData = @json($projectsByStatus);
        window.projectsByManagerData = @json($projectsByManager);
        window.projectsByContractData = @json($projectsByContract);
        window.projectsByStartMonthData = @json($projectsByStartMonth);
        window.projectsByTimeRemainingData = @json($projectsByTimeRemaining);
        window.colorsUnique = @json($colorsUnique);
    </script>
@endsection
