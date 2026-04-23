@extends('layouts.layoutMaster')

@section('title', 'تقرير العملاء')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">تقرير العملاء</a>
        <i class="ti ti-star favorite-icon" data-page-name="تقرير العملاء" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/moment/moment.js', 'resources/assets/js/customersReport.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('page-style')
    @vite(['resources/css/dataTable.css'])

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
            /* ارتفاع موحد */
            max-height: 40px;
            /* حد أقصى للارتفاع */
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- قسم الإحصائيات -->
    <div class="row ">
        <!-- حالة العملاء -->
        <div class="col-12 col-xl-6 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="statusCard">

                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">العملاء حسب الحالة</h5>
                            <button type="button" id="exportStatusChartBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- العمود الأول: العنوان وإجمالي العملاء والشارات -->
                        <div class="col-12 col-md-6">
                            <!-- عنوان وإجمالي العملاء -->
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $customers->count() }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي العملاء</p>
                                </div>
                            </div>

                            <!-- الشارات مرتبة كل شارتين في صف -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($charstatusData as $index => $item)
                                    @php
                                        $isLast = $index === $charstatusData->count() - 1;
                                        $isOdd = $charstatusData->count() % 2 !== 0;
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <div id="offersBadge{{ $index }}"
                                            class="badge badge-custom w-100 text-center" style=" cursor: pointer;"
                                            onclick="toggleDatasetOffers({{ $index }})"
                                            data-index="{{ $index }}">
                                            {{ mb_strlen($item['name'], 'UTF-8') > 20 ? mb_substr($item['name'], 0, 20, 'UTF-8') . '...' : $item['name'] }}
                                            ({{ $item['count'] }})
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- العمود الثاني: المخطط -->
                        <div class="col-12 col-md-6 d-flex align-items-center justify-content-center mb-3 mb-md-0">
                            <div style="position: relative; width: 100%; height: 100%;">
                                <canvas id="statusChart" style="width: 100%; height: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- الجنسيات -->
        <div class="col-12 col-xl-6 mb-5 d-flex align-items-stretch">
            <div class="card w-100 " id="exportnationalityCard">
                <div class="card-body">
                    <div class="card-title mb-2 d-flex justify-content-between">
                        <h5 class="mb-0 text-nowrap">العملاء حسب الجنسيات</h5>
                        <button type="button" id="exportnationalityChartBtn"
                            class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                            <span class="tf-icon ti-xs ti ti-download"></span>
                        </button>
                    </div>
                    <div style="position: relative; height: 85%;">
                        <canvas id="nationalityChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- قنوات التسويق -->

        <div class="col-12 col-xl-6 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="marketingChannelCard">
                <div class="card-body">
                    <div class="row h-100">
                        <div class="text-center mb-4">
                            <div class="card-title mb-2 d-flex justify-content-between">
                                <h5 class="mb-0 text-nowrap">العملاء حسب قنوات التسويق </h5>
                                <button type="button" id="exportmarketingChartBtn"
                                    class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                    <span class="tf-icon ti-xs ti ti-download"></span>
                                </button>
                            </div>
                        </div>
                        <!-- العمود الأول: العنوان والشارات -->
                        <div class="col-12 col-md-6 d-flex flex-column ">


                            <!-- الشارات مرتبة كل شارتين في صف -->
                            <div class="row g-1">
                                @foreach ($charMarketingChannel as $index => $item)
                                    @php
                                        $isLast = $index === $charMarketingChannel->count() - 1;
                                        $isOdd = $charMarketingChannel->count() % 2 !== 0;
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <span id="marketingChannelChartBadge{{ $index }}"
                                            class="badge badge-custom w-100 text-center" style=" cursor: pointer;"
                                            onclick="toggleDatasetMarketingChannelChart({{ $index }})"
                                            data-index="{{ $index }}">
                                            {{ mb_strlen($item['name'], 'UTF-8') > 20 ? mb_substr($item['name'], 0, 20, 'UTF-8') . '...' : $item['name'] }}
                                            ({{ $item['count'] }})
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- العمود الثاني: المخطط -->
                        <div class="col-12 col-md-6 d-flex align-items-center justify-content-center">
                            <div style="position: relative; width: 100%; height: 100%;">
                                <canvas id="marketingChannelChart" style="width: 100%; height: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CSS لتحسين مظهر الشارات وتثبيت ارتفاعها -->

        <!-- القطاعات -->
        <div class="col-12 col-xl-6 mb-5 d-flex align-items-stretch">
            <div class="card h-100 w-100" id="exportsectorCard">
                <div class="card-body">
                    <div class="card-title mb-2 d-flex justify-content-between">
                        <h5 class="mb-0 text-nowrap">العملاء حسب القطاعات </h5>
                        <button type="button" id="exportsectorChartBtn"
                            class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                            <span class="tf-icon ti-xs ti ti-download"></span>
                        </button>
                    </div>
                    <div style="position: relative; height: 85%;">
                        <canvas id="sectorChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.charstatusData = @json($charstatusData);
        window.colorsUnique = @json($colorsUnique);
        window.charMarketingChannel = @json($charMarketingChannel);
        window.nationalityLabels = @json($nationalityLabels);
        window.nationalityCounts = @json($nationalityCounts);
        window.sectorLabels = @json($sectorLabels);
        window.sectorCounts = @json($sectorCounts);
    </script>
@endsection

<!-- تحميل مكتبة Chart.js من CDN فقط -->
