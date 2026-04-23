@extends('layouts.layoutMaster')

@section('title', 'تقرير العروض')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">تقرير العروض</a>
        <i class="ti ti-star favorite-icon" data-page-name="تقرير العروض" data-page-url="{{ url()->current() }}"
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
        'resources/assets/js/offersReport.js', // ملف JS الخارجي الجديد
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
            /* ارتفاع موحد */
            max-height: 40px;
            /* حد أقصى للارتفاع */
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- قسم الإحصائيات -->
    <div class="row">
        <!-- إجمالي العروض -->
        <!-- العروض حسب حالة البدء -->
        <div class="col-12 col-xl-6 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="offersByStartStatusCard">

                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">العروض حسب حالة البدء</h5>
                            <button type="button" id="exportOffersByStartStatusBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- العمود الأول: العنوان وإجمالي العروض والشارات -->
                        <div class="col-12 col-md-6">
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalOffers }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي العروض</p>
                                </div>
                            </div>

                            <!-- شارات الحالة للعروض (بدأت/لم تبدأ) -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($offersByStartStatus as $index => $item)
                                    <div class="col-6 mb-2">
                                        <span class="badge badge-custom w-100 text-center"
                                            id="badge-offersByStartStatus-{{ $index }}"
                                            style="background-color: {{ $colorsUnique[$index] ?? '#7065ea' }};">
                                            {{ $item['name'] }} ({{ $item['count'] }})
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- العمود الثاني: المخطط -->
                        <div class="col-12 col-md-6 d-flex align-items-center justify-content-center mb-3 mb-md-0">
                            <div style="position: relative; width: 100%; height: 100%;">
                                <canvas id="offersByStartStatusChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- العروض حسب مرحلة العرض -->
        {{-- <div class="col-12 col-xl-6 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="offersByStageCard">
                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">العروض حسب مرحلة العرض</h5>
                            <button type="button" id="exportOffersByStageBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- العمود الأول: العنوان وإجمالي العملاء والشارات -->
                        <div class="col-12 col-md-6">
                            <!-- عنوان وإجمالي العملاء -->
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalOffers }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي العروض</p>
                                </div>
                            </div>


                        </div>

                        <!-- العمود الثاني: المخطط -->
                        <div class="col-12 col-md-6 d-flex align-items-center justify-content-center mb-3 mb-md-0">
                            <div style="position: relative; width: 100%; height: 100%;">
                                <canvas id="offersByStageChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}

        <!-- العروض حسب مدير العلاقة -->
        <div class="col-12 col-xl-6 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="offersByManagerCard">
                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">العروض حسب مدير العلاقة (آخر 10)</h5>
                            <button type="button" id="exportOffersByManagerBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- العمود الأول: العنوان وإجمالي العروض والشارات -->
                        <div class="col-12 col-md-6">
                            <!-- عنوان وإجمالي العروض -->
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalOffers }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي العروض</p>

                                </div>
                            </div>

                            <!-- الشارات مرتبة كل شارتين في صف -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($offersByManager as $index => $manager)
                                    @php
                                        $isLast = $index === $offersByManager->count() - 1;
                                        $isOdd = $offersByManager->count() % 2 !== 0;
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <span class="badge-custom" id="badge-offersByManager-{{ $index }}"
                                            style="background-color: {{ $colorsUnique[$index] ?? '#000000' }}; "
                                            onclick="toggleManager({{ $index }})">
                                            {{ mb_strlen($manager['name'], 'UTF-8') > 15 ? mb_substr($manager['name'], 0, 15, 'UTF-8') . '...' : $manager['name'] }}
                                            ({{ $manager['count'] }})
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- العمود الثاني: المخطط -->
                        <div class="col-12 col-md-6 d-flex align-items-center justify-content-center mb-3 mb-md-0">
                            <div style="position: relative; width: 100%; height: 100%;">
                                <canvas id="offersByManagerChart" style="width: 100%; height: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- العروض حسب العميل -->
        <div class="col-12 c">
            <div class="card w-100" id="offersByCustomerCard">
                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">العروض حسب العميل (آخر 10)</h5>
                            <button type="button" id="exportOffersByCustomerBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- العمود الأول: العنوان وإجمالي العروض والشارات -->
                        <div class="col-12 col-md-6">
                            <!-- عنوان وإجمالي العروض -->
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalOffers }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي العروض</p>
                                </div>
                            </div>

                            <!-- الشارات مرتبة كل شارتين في صف -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($offersByCustomer as $index => $customer)
                                    @php
                                        $isLast = $index === $offersByCustomer->count() - 1;
                                        $isOdd = $offersByCustomer->count() % 2 !== 0;
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <span class="badge-custom" id="badge-offersByCustomer-{{ $index }}"
                                            style="background-color: {{ $colorsUnique[$index % count($colorsUnique)] }}"
                                            onclick="toggleCustomer({{ $index }})">

                                            {{ mb_strlen($customer['name'], 'UTF-8') > 15 ? mb_substr($customer['name'], 0, 15, 'UTF-8') . '...' : $customer['name'] }}
                                            ({{ $customer['count'] }})
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- العمود الثاني: المخطط -->
                        <div class="col-12 col-md-6 d-flex align-items-center justify-content-center mb-3 mb-md-0">
                            <div style="position: relative; width: 100%; height: 100%;">
                                <canvas id="offersByCustomerChart" style="width: 100%; height: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <script>
        window.offersByStartStatusData = @json($offersByStartStatus);
        window.offersByManagerData = @json($offersByManager);
        window.offersByCustomerData = @json($offersByCustomer);
        window.colorsUnique = @json($colorsUnique);
    </script>
@endsection
