@extends('layouts.layoutMaster')

@section('title', 'تقرير العقود')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">تقرير العقود</a>
        <i class="ti ti-star favorite-icon" data-page-name="تقرير العقود" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/js/contractsReport.js'])
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

        <!-- العقود حسب حالة البدء -->
        <div class="col-12 col-xl-6 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="contractsByStartStatusCard">

                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">العقود حسب حالة البدء</h5>
                            <button type="button" id="exportContractsByStartStatusBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- العمود الأول: العنوان وإجمالي العقود والشارات -->
                        <div class="col-12 col-md-6">
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalContracts }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي العقود</p>
                                </div>
                            </div>

                            <!-- شارات الحالة للعقود (بدأت/لم تبدأ) -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($contractsByStartStatus as $index => $item)
                                    <div class="col-6 mb-2">
                                        <span class="badge badge-custom w-100 text-center"
                                            id="badge-contractsByStartStatus-{{ $index }}"
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
                                <canvas id="contractsByStartStatusChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- العقود حسب حالة العقد -->
        <div class="col-12 col-xl-6 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="contractsByStatusCard">
                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">العقود حسب الحالة</h5>
                            <button type="button" id="exportContractsByStatusBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- العمود الأول: العنوان وإجمالي العقود والشارات -->
                        <div class="col-12 col-md-6">
                            <!-- عنوان وإجمالي العقود -->
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalContracts }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي العقود</p>
                                </div>
                            </div>

                            <!-- الشارات مرتبة كل شارتين في صف -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($contractsByStatus as $index => $item)
                                    @php
                                        $isLast = $index === $contractsByStatus->count() - 1;
                                        $isOdd = $contractsByStatus->count() % 2 !== 0;
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <span class="badge-custom" id="badge-contractsByStatus-{{ $index }}"
                                            style="background-color: {{ $colorsUnique[$index] ?? '#000000' }}">
                                            {{ mb_strlen($item['name'], 'UTF-8') > 20 ? mb_substr($item['name'], 0, 20, 'UTF-8') . '...' : $item['name'] }}
                                            ({{ $item['count'] }})
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- العمود الثاني: المخطط -->
                        <div class="col-12 col-md-6 d-flex align-items-center justify-content-center mb-3 mb-md-0">
                            <div style="position: relative; width: 100%; height: 100%;">
                                <canvas id="contractsByStatusChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- العقود حسب مسؤول العقد -->
        <div class="col-12 col-xl-6 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="contractsByManagerCard">
                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">العقود حسب مسؤول العقد (آخر 10)</h5>
                            <button type="button" id="exportContractsByManagerBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- العمود الأول: العنوان وإجمالي العقود والشارات -->
                        <div class="col-12 col-md-6">
                            <!-- عنوان وإجمالي العقود -->
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalContracts }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي العقود</p>

                                </div>
                            </div>

                            <!-- الشارات مرتبة كل شارتين في صف -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($contractsByManager as $index => $manager)
                                    @php
                                        $isLast = $index === $contractsByManager->count() - 1;
                                        $isOdd = $contractsByManager->count() % 2 !== 0;
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <span class="badge-custom" id="badge-contractsByManager-{{ $index }}"
                                            style="background-color: {{ $colorsUnique[$index % count($colorsUnique)] }}; "
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
                                <canvas id="contractsByManagerChart" style="width: 100%; height: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- العقود حسب العميل -->
        <div class="col-12 col-xl-6 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="contractsByCustomerCard">
                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">العقود حسب العميل (آخر 10)</h5>
                            <button type="button" id="exportContractsByCustomerBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- العمود الأول: العنوان وإجمالي العقود والشارات -->
                        <div class="col-12 col-md-6">
                            <!-- عنوان وإجمالي العقود -->
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalContracts }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي العقود</p>
                                </div>
                            </div>

                            <!-- الشارات مرتبة كل شارتين في صف -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($contractsByCustomer as $index => $customer)
                                    @php
                                        $isLast = $index === $contractsByCustomer->count() - 1;
                                        $isOdd = $contractsByCustomer->count() % 2 !== 0;
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <span class="badge-custom" id="badge-contractsByCustomer-{{ $index }}"
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
                                <canvas id="contractsByCustomerChart" style="width: 100%; height: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- إضافة المخططات الجديدة -->

        <!-- العقود حسب شهر الإغلاق المتوقع (الشهور الهجرية) -->
        <div class="col-12 col-xl-6 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="contractsByExpectedClosureMonthCard">
                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">العقود حسب شهر الإغلاق المتوقع (هجري)</h5>
                            <button type="button" id="exportContractsByExpectedClosureMonthBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- العمود الأول: العنوان وإجمالي العقود والشارات -->
                        <div class="col-12 col-md-6">
                            <!-- عنوان وإجمالي العقود -->
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalContracts }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي العقود</p>
                                </div>
                            </div>

                            <!-- الشارات مرتبة كل شارتين في صف -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($contractsByExpectedClosureMonth as $index => $item)
                                    @php
                                        $isLast = $index === count($contractsByExpectedClosureMonth) - 1;
                                        $isOdd = count($contractsByExpectedClosureMonth) % 2 !== 0;
                                        // $monthName = \Alkoumi\LaravelHijriDate\Hijri::convertNumberToMonthName($item['month']);
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <span class="badge-custom"
                                            id="badge-contractsByExpectedClosureMonth-{{ $index }}"
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
                                <canvas id="contractsByExpectedClosureMonthChart"
                                    style="width: 100%; height: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- العقود حسب الوقت المتبقي -->
        <div class="col-12 col-xl-6 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="contractsByTimeRemainingCard">
                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">العقود حسب الوقت المتبقي</h5>
                            <button type="button" id="exportContractsByTimeRemainingBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- العمود الأول: العنوان وإجمالي العقود والشارات -->
                        <div class="col-12 col-md-6">
                            <!-- عنوان وإجمالي العقود -->
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalContracts }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي العقود</p>
                                </div>
                            </div>

                            <!-- الشارات مرتبة كل شارتين في صف -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($contractsByTimeRemaining as $index => $item)
                                    @php
                                        $isLast = $index === count($contractsByTimeRemaining) - 1;
                                        $isOdd = count($contractsByTimeRemaining) % 2 !== 0;
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <span class="badge-custom"
                                            id="badge-contractsByTimeRemaining-{{ $index }}"
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
                                <canvas id="contractsByTimeRemainingChart" style="width: 100%; height: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- العقود حسب العرض (آخر 10 عروض) -->
        <div class="col-12 col-xl-6 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="contractsByOfferCard">
                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">العقود حسب العرض (آخر 10)</h5>
                            <button type="button" id="exportContractsByOfferBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- العمود الأول: العنوان وإجمالي العقود والشارات -->
                        <div class="col-12 col-md-6">
                            <!-- عنوان وإجمالي العقود -->
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalContracts }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي العقود</p>
                                </div>
                            </div>

                            <!-- الشارات مرتبة كل شارتين في صف -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($contractsByOffer as $index => $offer)
                                    @php
                                        $isLast = $index === count($contractsByOffer) - 1;
                                        $isOdd = count($contractsByOffer) % 2 !== 0;
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <span class="badge-custom" id="badge-contractsByOffer-{{ $index }}"
                                            style="background-color: {{ $colorsUnique[$index % count($colorsUnique)] }}">
                                            {{ mb_strlen($offer['name'], 'UTF-8') > 15 ? mb_substr($offer['name'], 0, 15, 'UTF-8') . '...' : $offer['name'] }}
                                            ({{ $offer['count'] }})
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- العمود الثاني: المخطط -->
                        <div class="col-12 col-md-6 d-flex align-items-center justify-content-center mb-3 mb-md-0">
                            <div style="position: relative; width: 100%; height: 100%;">
                                <canvas id="contractsByOfferChart" style="width: 100%; height: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- العقود حسب مسؤول العقد العلاقة -->
        <div class="col-12 col-xl-6 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="contractsByRelationshipManagerCard">
                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">العقود حسب مسؤول العلاقة (آخر 10)</h5>
                            <button type="button" id="exportContractsByRelationshipManagerBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- العمود الأول: العنوان وإجمالي العقود والشارات -->
                        <div class="col-12 col-md-6">
                            <!-- عنوان وإجمالي العقود -->
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalContracts }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي العقود</p>

                                </div>
                            </div>

                            <!-- الشارات مرتبة كل شارتين في صف -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($contractsByRelationshipManager as $index => $manager)
                                    @php
                                        $isLast = $index === $contractsByRelationshipManager->count() - 1;
                                        $isOdd = $contractsByRelationshipManager->count() % 2 !== 0;
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <span class="badge-custom"
                                            id="badge-contractsByRelationshipManager-{{ $index }}"
                                            style="background-color: {{ $colorsUnique[$index % count($colorsUnique)] }}; ">
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
                                <canvas id="contractsByRelationshipManagerChart"
                                    style="width: 100%; height: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div> <!-- نهاية الصف -->

    <script>
        window.contractsByStartStatusData = @json($contractsByStartStatus);
        window.contractsByStatusData = @json($contractsByStatus);
        window.contractsByManagerData = @json($contractsByManager);
        window.contractsByCustomerData = @json($contractsByCustomer);
        window.contractsByExpectedClosureMonthData = @json($contractsByExpectedClosureMonth);
        window.contractsByTimeRemainingData = @json($contractsByTimeRemaining);
        window.contractsByOfferData = @json($contractsByOffer);
        window.contractsByRelationshipManagerData = @json($contractsByRelationshipManager);
        window.colorsUnique = @json($colorsUnique);
    </script>
@endsection
