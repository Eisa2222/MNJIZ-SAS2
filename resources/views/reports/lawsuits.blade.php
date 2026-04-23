@extends('layouts.layoutMaster')

@section('title', 'تقرير الدعاوى')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">تقرير الدعاوى</a>
        <i class="ti ti-star favorite-icon" data-page-name="تقرير الدعاوى" data-page-url="{{ url()->current() }}"
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
        'resources/assets/js/lawsuitsReport.js', // ملف JS الخارجي الجديد
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
        <!-- الدعاوى حسب الحالة -->
        <div class="col-12 col-xl-6 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="lawsuitsByStatusCard">
                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">الدعاوى حسب الحالة</h5>
                            <button type="button" id="exportLawsuitsByStatusBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- العمود الأول: العنوان وإجمالي الدعاوى والشارات -->
                        <div class="col-12 col-md-6">
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalLawsuits }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي الدعاوى</p>
                                </div>
                            </div>

                            <!-- الشارات -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($lawsuitsByStatus as $index => $item)
                                    @php
                                        $isLast = $index === count($lawsuitsByStatus) - 1;
                                        $isOdd = count($lawsuitsByStatus) % 2 !== 0;
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <span class="badge-custom" id="badge-lawsuitsByStatus-{{ $index }}"
                                            style="background-color: {{ $colorsUnique[$index % count($colorsUnique)] }}">
                                            {{-- {{ $item['name'] }} ({{ $item['count'] }}) --}}
                                            {{ mb_strlen($item['name'], 'UTF-8') > 15 ? mb_substr($item['name'], 0, 15, 'UTF-8') . '...' : $item['name'] }} ({{ $item['count'] }})

                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- العمود الثاني: المخطط -->
                        <div class="col-12 col-md-6 d-flex align-items-center justify-content-center mb-3 mb-md-0">
                            <div style="position: relative; width: 100%; height: 100%;">
                                <canvas id="lawsuitsByStatusChart" style="width: 100%; height: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- الدعاوى حسب المحكمة  -->
        <div class="col-12 col-xl-6 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="lawsuitsByMainCourtCard">
                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">الدعاوى حسب المحكمة </h5>
                            <button type="button" id="exportLawsuitsByMainCourtBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- نفس الهيكلية مع تعديل المتغيرات -->
                        <!-- العمود الأول: العنوان وإجمالي الدعاوى والشارات -->
                        <div class="col-12 col-md-6">
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalLawsuits }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي الدعاوى</p>
                                </div>
                            </div>

                            <!-- الشارات -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($lawsuitsByMainCourt as $index => $item)
                                    @php
                                        $isLast = $index === count($lawsuitsByMainCourt) - 1;
                                        $isOdd = count($lawsuitsByMainCourt) % 2 !== 0;
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <span class="badge-custom" id="badge-lawsuitsByMainCourt-{{ $index }}"
                                            style="background-color: {{ $colorsUnique[$index % count($colorsUnique)] }}">
                                            {{-- {{ $item['name'] }} ({{ $item['count'] }}) --}}
                                            {{ mb_strlen($item['name'], 'UTF-8') > 15 ? mb_substr($item['name'], 0, 15, 'UTF-8') . '...' : $item['name'] }} ({{ $item['count'] }})

                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- العمود الثاني: المخطط -->
                        <div class="col-12 col-md-6 d-flex align-items-center justify-content-center mb-3 mb-md-0">
                            <div style="position: relative; width: 100%; height: 100%;">
                                <canvas id="lawsuitsByMainCourtChart" style="width: 100%; height: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- الدعاوى حسب المدينة -->
        <div class="col-12 col-xl-6 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="lawsuitsByRegionCard">
                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">الدعاوى حسب المدينة</h5>
                            <button type="button" id="exportLawsuitsByRegionBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- العمود الأول: العنوان وإجمالي الدعاوى والشارات -->
                        <div class="col-12 col-md-6">
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalLawsuits }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي الدعاوى</p>
                                </div>
                            </div>

                            <!-- الشارات -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($lawsuitsByRegion as $index => $item)
                                    @php
                                        $isLast = $index === count($lawsuitsByRegion) - 1;
                                        $isOdd = count($lawsuitsByRegion) % 2 !== 0;
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <span class="badge-custom" id="badge-lawsuitsByRegion-{{ $index }}"
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
                                <canvas id="lawsuitsByRegionChart" style="width: 100%; height: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- الدعاوى حسب نوع الدعوى -->
        <div class="col-12 col-xl-6 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="lawsuitsByTypeCard">
                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">الدعاوى حسب نوع الدعوى (آخر 10)</h5>
                            <button type="button" id="exportLawsuitsByTypeBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- العمود الأول: العنوان وإجمالي الدعاوى والشارات -->
                        <div class="col-12 col-md-6">
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalLawsuits }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي الدعاوى</p>
                                </div>
                            </div>

                            <!-- الشارات -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($lawsuitsByType as $index => $item)
                                    @php
                                        $isLast = $index === count($lawsuitsByType) - 1;
                                        $isOdd = count($lawsuitsByType) % 2 !== 0;
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <span class="badge-custom" id="badge-lawsuitsByType-{{ $index }}"
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
                                <canvas id="lawsuitsByTypeChart" style="width: 100%; height: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- الدعاوى حسب التصنيف الرئيسي -->
        <div class="col-12 col-xl-6 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="lawsuitsByCategoryCard">
                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">الدعاوى حسب التصنيف الرئيسي (آخر 10)</h5>
                            <button type="button" id="exportLawsuitsByCategoryBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- العمود الأول: العنوان وإجمالي الدعاوى والشارات -->
                        <div class="col-12 col-md-6">
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalLawsuits }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي الدعاوى</p>
                                </div>
                            </div>

                            <!-- الشارات -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($lawsuitsByCategory as $index => $item)
                                    @php
                                        $isLast = $index === count($lawsuitsByCategory) - 1;
                                        $isOdd = count($lawsuitsByCategory) % 2 !== 0;
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <span class="badge-custom" id="badge-lawsuitsByCategory-{{ $index }}"
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
                                <canvas id="lawsuitsByCategoryChart" style="width: 100%; height: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        <!-- الدعاوى حسب المستخدم -->
        <div class="col-12 col-xl-6 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="lawsuitsByUserCard">
                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">الدعاوى حسب المستخدم (آخر 10)</h5>
                            <button type="button" id="exportLawsuitsByUserBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- العمود الأول: العنوان وإجمالي الدعاوى والشارات -->
                        <div class="col-12 col-md-6">
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalLawsuits }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي الدعاوى</p>
                                </div>
                            </div>

                            <!-- الشارات -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($lawsuitsByUser as $index => $item)
                                    @php
                                        $isLast = $index === count($lawsuitsByUser) - 1;
                                        $isOdd = count($lawsuitsByUser) % 2 !== 0;
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <span class="badge-custom" id="badge-lawsuitsByUser-{{ $index }}"
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
                                <canvas id="lawsuitsByUserChart" style="width: 100%; height: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

          <!-- الدعاوى حسب شهر الإنشاء (هجري) -->
          <div class="col-12 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="lawsuitsByCreatedMonthCard">
                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">الدعاوى حسب شهر الإنشاء (هجري)</h5>
                            <button type="button" id="exportLawsuitsByCreatedMonthBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- العمود الأول: العنوان وإجمالي الدعاوى والشارات -->
                        <div class="col-12 col-md-6">
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalLawsuits }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي الدعاوى</p>
                                </div>
                            </div>

                            <!-- الشارات -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($lawsuitsByCreatedMonth as $index => $item)
                                    @php
                                        $isLast = $index === count($lawsuitsByCreatedMonth) - 1;
                                        $isOdd = count($lawsuitsByCreatedMonth) % 2 !== 0;
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <span class="badge-custom" id="badge-lawsuitsByCreatedMonth-{{ $index }}"
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
                                <canvas id="lawsuitsByCreatedMonthChart" style="width: 100%; height: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div> <!-- نهاية الصف -->

    <script>
        window.lawsuitsByStatusData = @json($lawsuitsByStatus);
        window.lawsuitsByMainCourtData = @json($lawsuitsByMainCourt);
        window.lawsuitsByRegionData = @json($lawsuitsByRegion);
        window.lawsuitsByTypeData = @json($lawsuitsByType);
        window.lawsuitsByCategoryData = @json($lawsuitsByCategory);
        window.lawsuitsByCreatedMonthData = @json($lawsuitsByCreatedMonth);
        window.lawsuitsByUserData = @json($lawsuitsByUser);
        window.colorsUnique = @json($colorsUnique);
    </script>
@endsection
