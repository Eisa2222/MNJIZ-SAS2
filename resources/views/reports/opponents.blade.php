@extends('layouts.layoutMaster')

@section('title', 'تقرير الخصوم')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">تقرير الخصوم</a>
        <i class="ti ti-star favorite-icon" data-page-name="تقرير الخصوم" data-page-url="{{ url()->current() }}"
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
        'resources/assets/js/opponentsReport.js',
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
        <!-- الخصوم حسب النوع -->
        <div class="col-12 col-xl-6 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="opponentsByTypeCard">
                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">الخصوم حسب النوع</h5>
                            <button type="button" id="exportOpponentsByTypeBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- العمود الأول: العنوان وإجمالي الخصوم والشارات -->
                        <div class="col-12 col-md-6">
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalOpponents }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي الخصوم</p>
                                </div>
                            </div>

                            <!-- الشارات -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($opponentsByType as $index => $item)
                                    @php
                                        $isLast = $index === count($opponentsByType) - 1;
                                        $isOdd = count($opponentsByType) % 2 !== 0;
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <span class="badge-custom" id="badge-opponentsByType-{{ $index }}"
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
                                <canvas id="opponentsByTypeChart" style="width: 100%; height: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- الخصوم حسب المدينة -->
        <div class="col-12 col-xl-6 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="opponentsByRegionCard">
                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">الخصوم حسب المدينة</h5>
                            <button type="button" id="exportOpponentsByRegionBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- العمود الأول: العنوان وإجمالي الخصوم والشارات -->
                        <div class="col-12 col-md-6">
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalOpponents }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي الخصوم</p>
                                </div>
                            </div>

                            <!-- الشارات -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($opponentsByRegion as $index => $item)
                                    @php
                                        $isLast = $index === count($opponentsByRegion) - 1;
                                        $isOdd = count($opponentsByRegion) % 2 !== 0;
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <span class="badge-custom" id="badge-opponentsByRegion-{{ $index }}"
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
                                <canvas id="opponentsByRegionChart" style="width: 100%; height: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- الخصوم حسب شهر الإنشاء (هجري) -->
        <div class="col-12 col-xl-6 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="opponentsByCreatedMonthCard">
                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">الخصوم حسب شهر الإنشاء (هجري)</h5>
                            <button type="button" id="exportOpponentsByCreatedMonthBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- العمود الأول: العنوان وإجمالي الخصوم والشارات -->
                        <div class="col-12 col-md-6">
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalOpponents }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي الخصوم</p>
                                </div>
                            </div>

                            <!-- الشارات -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($opponentsByCreatedMonth as $index => $item)
                                    @php
                                        $isLast = $index === count($opponentsByCreatedMonth) - 1;
                                        $isOdd = count($opponentsByCreatedMonth) % 2 !== 0;
                                        // $monthName = \Alkoumi\LaravelHijriDate\Hijri::convertNumberToMonthName($item['month']);
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <span class="badge-custom" id="badge-opponentsByCreatedMonth-{{ $index }}"
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
                                <canvas id="opponentsByCreatedMonthChart" style="width: 100%; height: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- أعلى 10 خصوم حسب عدد الدعاوى -->
        <div class="col-12 col-xl-6 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="opponentsByLawsuitCountCard">
                <div class="card-body">
                    <div class="row">
                        <div class="mb-4">
                            <div class="card-title mb-2 d-flex justify-content-between">
                                <h5 class="mb-0 text-nowrap">أعلى 10 خصوم حسب عدد الدعاوى</h5>
                                <button type="button" id="exportOpponentsByLawsuitCountBtn"
                                    class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                    <span class="tf-icon ti-xs ti ti-download"></span>
                                </button>
                            </div>
                        </div>

                        <!-- العمود الأول: العنوان والشارات -->
                        <div class="col-12 col-md-6">

                            <!-- الشارات -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($opponentsWithLawsuitCounts as $index => $item)
                                    @php
                                        $isLast = $index === count($opponentsWithLawsuitCounts) - 1;
                                        $isOdd = count($opponentsWithLawsuitCounts) % 2 !== 0;
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <span class="badge-custom" id="badge-opponentsByLawsuitCount-{{ $index }}"
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
                                <canvas id="opponentsByLawsuitCountChart" style="width: 100%; height: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div> <!-- نهاية الصف -->

    <script>
        window.opponentsByTypeData = @json($opponentsByType);
        window.opponentsByRegionData = @json($opponentsByRegion);
        window.opponentsByCreatedMonthData = @json($opponentsByCreatedMonth);
        window.opponentsByLawsuitCountData = @json($opponentsWithLawsuitCounts);
        window.colorsUnique = @json($colorsUnique);
    </script>
@endsection
