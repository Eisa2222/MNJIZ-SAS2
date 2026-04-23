@extends('layouts.layoutMaster')

@section('title', 'تقرير الوكالات')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">تقرير الوكالات</a>
        <i class="ti ti-star favorite-icon" data-page-name="تقرير الوكالات" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
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
        'resources/assets/js/powerOfAttorneyReport.js',
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
        <!-- إجمالي الوكالات -->
        <!-- الوكالات حسب الحالة -->
        <div class="col-12 col-xl-6 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="powersByStatusCard">
                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">الوكالات حسب الحالة</h5>
                            <button type="button" id="exportPowersByStatusBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- العمود الأول: العنوان وإجمالي الوكالات والشارات -->
                        <div class="col-12 col-md-6">
                            <!-- عنوان وإجمالي الوكالات -->
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalPowers }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي الوكالات</p>
                                </div>
                            </div>

                            <!-- الشارات مرتبة كل شارتين في صف -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($powersByStatus as $index => $item)
                                    @php
                                        $isLast = $index === count($powersByStatus) - 1;
                                        $isOdd = count($powersByStatus) % 2 !== 0;
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <span class="badge-custom" id="badge-powersByStatus-{{ $index }}"
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
                                <canvas id="powersByStatusChart" style="width: 100%; height: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>





        <!-- الوكالات حسب الوقت المتبقي -->
        <div class="col-12 col-xl-6 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="powersByTimeRemainingCard">
                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">الوكالات حسب الوقت المتبقي</h5>
                            <button type="button" id="exportPowersByTimeRemainingBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- العمود الأول: العنوان وإجمالي الوكالات والشارات -->
                        <div class="col-12 col-md-6">
                            <!-- عنوان وإجمالي الوكالات -->
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalPowers }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي الوكالات</p>
                                </div>
                            </div>

                            <!-- الشارات مرتبة كل شارتين في صف -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($powersByTimeRemaining as $index => $item)
                                    @php
                                        $isLast = $index === count($powersByTimeRemaining) - 1;
                                        $isOdd = count($powersByTimeRemaining) % 2 !== 0;
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <span class="badge-custom" id="badge-powersByTimeRemaining-{{ $index }}"
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
                                <canvas id="powersByTimeRemainingChart" style="width: 100%; height: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

         <!-- الوكالات حسب شهر الإصدار (هجري) -->
         <div class="col-12 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="powersByIssuedMonthCard">
                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">الوكالات حسب شهر الإصدار (هجري)</h5>
                            <button type="button" id="exportPowersByIssuedMonthBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- العمود الأول: العنوان وإجمالي الوكالات والشارات -->
                        <div class="col-12 col-md-6">
                            <!-- عنوان وإجمالي الوكالات -->
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalPowers }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي الوكالات</p>
                                </div>
                            </div>

                            <!-- الشارات مرتبة كل شارتين في صف -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($powersByIssuedMonth as $index => $item)
                                    @php
                                        $isLast = $index === count($powersByIssuedMonth) - 1;
                                        $isOdd = count($powersByIssuedMonth) % 2 !== 0;
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <span class="badge-custom" id="badge-powersByIssuedMonth-{{ $index }}"
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
                                <canvas id="powersByIssuedMonthChart" style="width: 100%; height: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div> <!-- نهاية الصف -->

    <script>
        window.powersByStatusData = @json($powersByStatus);
        window.powersByIssuedMonthData = @json($powersByIssuedMonth);
        window.powersByTimeRemainingData = @json($powersByTimeRemaining);
        window.colorsUnique = @json($colorsUnique);
    </script>
@endsection
