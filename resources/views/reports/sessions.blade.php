@extends('layouts.layoutMaster')

@section('title', 'تقرير الجلسات')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">تقرير الجلسات</a>
        <i class="ti ti-star favorite-icon" data-page-name="تقرير الجلسات" data-page-url="{{ url()->current() }}"
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
        'resources/assets/js/sessionsReport.js', // ملف JS الخارجي الجديد
    ])
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('page-style')
    @vite(['resources/css/dataTable.css']) <!-- ملف CSS الخارجي الجديد -->
@endsection


@section('content')
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
            cursor: pointer;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- قسم الإحصائيات -->
    <div class="row">
        <!-- الجلسات حسب الحالة -->
        <div class="col-12 col-xl-6 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="sessionsByStatusCard">
                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">الجلسات حسب الحالة</h5>
                            <button type="button" id="exportSessionsByStatusBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- العمود الأول: العنوان وإجمالي الجلسات والشارات -->
                        <div class="col-12 col-md-6">
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalSessions }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي الجلسات</p>
                                </div>
                            </div>

                            <!-- الشارات -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($sessionsByStatus as $index => $item)
                                    @php
                                        $isLast = $index === count($sessionsByStatus) - 1;
                                        $isOdd = count($sessionsByStatus) % 2 !== 0;
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <span class="badge-custom" id="badge-sessionsByStatus-{{ $index }}"
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
                                <canvas id="sessionsByStatusChart" style="width: 100%; height: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- الجلسات حسب الأهمية -->
        {{-- <div class="col-12 col-xl-6 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="sessionsByImportanceCard">
                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">الجلسات حسب الأهمية</h5>
                            <button type="button" id="exportSessionsByImportanceBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- العمود الأول: العنوان وإجمالي الجلسات والشارات -->
                        <div class="col-12 col-md-6">
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalSessions }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي الجلسات</p>
                                </div>
                            </div>

                            <!-- الشارات -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($sessionsByImportance as $index => $item)
                                    @php
                                        $isLast = $index === count($sessionsByImportance) - 1;
                                        $isOdd = count($sessionsByImportance) % 2 !== 0;
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <span class="badge-custom" id="badge-sessionsByImportance-{{ $index }}"
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
                                <canvas id="sessionsByImportanceChart" style="width: 100%; height: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}

        <!-- الجلسات حسب نوع الجلسة -->
        <div class="col-12 col-xl-6 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="sessionsByTypeCard">
                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">الجلسات حسب نوع الجلسة</h5>
                            <button type="button" id="exportSessionsByTypeBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- العمود الأول: العنوان وإجمالي الجلسات والشارات -->
                        <div class="col-12 col-md-6">
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalSessions }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي الجلسات</p>
                                </div>
                            </div>

                            <!-- الشارات -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($sessionsByType as $index => $item)
                                    @php
                                        $isLast = $index === count($sessionsByType) - 1;
                                        $isOdd = count($sessionsByType) % 2 !== 0;
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <span class="badge-custom" id="badge-sessionsByType-{{ $index }}"
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
                                <canvas id="sessionsByTypeChart" style="width: 100%; height: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- الجلسات حسب درجة الجهة -->
        <div class="col-12 col-xl-6 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="sessionsByEntityRankCard">
                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">الجلسات حسب درجة الجهة</h5>
                            <button type="button" id="exportSessionsByEntityRankBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- العمود الأول: العنوان وإجمالي الجلسات والشارات -->
                        <div class="col-12 col-md-6">
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalSessions }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي الجلسات</p>
                                </div>
                            </div>

                            <!-- الشارات -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($sessionsByEntityRank as $index => $item)
                                    @php
                                        $isLast = $index === count($sessionsByEntityRank) - 1;
                                        $isOdd = count($sessionsByEntityRank) % 2 !== 0;
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <span class="badge-custom" id="badge-sessionsByEntityRank-{{ $index }}"
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
                                <canvas id="sessionsByEntityRankChart" style="width: 100%; height: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- الجلسات حسب  المكلفين -->
        <div class="col-12 col-xl-6 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="sessionsByAssignedUserCard">
                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">الجلسات حسب  المكلفين (آخر 10)</h5>
                            <button type="button" id="exportSessionsByAssignedUserBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- العمود الأول: العنوان وإجمالي الجلسات والشارات -->
                        <div class="col-12 col-md-6">
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalSessions }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي الجلسات</p>
                                </div>
                            </div>

                            <!-- الشارات -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($sessionsByAssignedUser as $index => $item)
                                    @php
                                        $isLast = $index === count($sessionsByAssignedUser) - 1;
                                        $isOdd = count($sessionsByAssignedUser) % 2 !== 0;
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <span class="badge-custom" id="badge-sessionsByAssignedUser-{{ $index }}"
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
                                <canvas id="sessionsByAssignedUserChart" style="width: 100%; height: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


          <!-- الجلسات حسب شهر الجلسة (هجري) -->
          <div class="col-12 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="sessionsByMonthCard">
                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">الجلسات حسب شهر الجلسة (هجري)</h5>
                            <button type="button" id="exportSessionsByMonthBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- العمود الأول: العنوان وإجمالي الجلسات والشارات -->
                        <div class="col-12 col-md-6">
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalSessions }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي الجلسات</p>
                                </div>
                            </div>

                            <!-- الشارات -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($sessionsByMonth as $index => $item)
                                    @php
                                        $isLast = $index === count($sessionsByMonth) - 1;
                                        $isOdd = count($sessionsByMonth) % 2 !== 0;
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <span class="badge-custom" id="badge-sessionsByMonth-{{ $index }}"
                                            style="background-color: {{ $colorsUnique[$index % count($colorsUnique)] }}">
                                            {{  $item['month'] }} ({{ $item['count'] }})
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- العمود الثاني: المخطط -->
                        <div class="col-12 col-md-6 d-flex align-items-center justify-content-center mb-3 mb-md-0">
                            <div style="position: relative; width: 100%; height: 100%;">
                                <canvas id="sessionsByMonthChart" style="width: 100%; height: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div> <!-- نهاية الصف -->

    <script>
        window.sessionsByStatusData = @json($sessionsByStatus);
        window.sessionsByTypeData = @json($sessionsByType);
        window.sessionsByEntityRankData = @json($sessionsByEntityRank);
        window.sessionsByMonthData = @json($sessionsByMonth);
        window.sessionsByAssignedUserData = @json($sessionsByAssignedUser);
        window.colorsUnique = @json($colorsUnique);
    </script>
@endsection
