@extends('layouts.layoutMaster')

@section('title', 'تقرير الموظفين')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">تقرير الموظفين</a>
        <i class="ti ti-star favorite-icon" data-page-name="تقرير الموظفين" data-page-url="{{ url()->current() }}"
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
        'resources/assets/js/employeesReport.js',
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

    <!-- محتوى التقرير -->
    <div class="row">
        <!-- الموظفين حسب الجنسية -->
        <div class="col-12 col-xl-6 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="employeesByNationalityCard">
                <div class="card-body">
                    <div class="">
                        <!-- العمود الأول: العنوان وإجمالي الموظفين والشارات -->
                        {{-- <div class="col-12 col-md-6"> --}}
                            <div class="mb-4">
                                <div class="card-title mb-2 d-flex justify-content-between">
                                    <h5 class="mb-0 text-nowrap">الموظفين حسب الجنسية</h5>
                                    <button type="button" id="exportEmployeesByNationalityBtn"
                                        class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                        <span class="tf-icon ti-xs ti ti-download"></span>
                                    </button>
                                </div>
                                {{-- <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalEmployees }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي الموظفين</p>
                                </div> --}}
                            </div>

                            {{-- <!-- الشارات -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($employeesByNationality as $index => $item)
                                    @php
                                        $isLast = $index === count($employeesByNationality) - 1;
                                        $isOdd = count($employeesByNationality) % 2 !== 0;
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <span class="badge-custom" id="badge-employeesByNationality-{{ $index }}"
                                            style="background-color: {{ $colorsUnique[$index % count($colorsUnique)] }}">
                                            {{ $item['name'] }} ({{ $item['count'] }})
                                        </span>
                                    </div>
                                @endforeach
                            </div> --}}
                        {{-- </div> --}}

                        <!-- العمود الثاني: المخطط -->
                        {{-- <div class="col-12 col-md-6 d-flex align-items-center justify-content-center mb-3 mb-md-0"> --}}
                            <div style="position: relative; height: 85%;">
                                <canvas id="employeesByNationalityChart" style="width: 100%; height: 100%;"></canvas>
                            </div>
                        {{-- </div> --}}
                    </div>
                </div>
            </div>
        </div>

        <!-- الموظفين حسب المسمى الوظيفي -->
        <div class="col-12 col-xl-6 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="employeesByJobTitleCard">
                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">الموظفين حسب المسمى الوظيفي</h5>
                            <button type="button" id="exportEmployeesByJobTitleBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- العمود الأول: العنوان وإجمالي الموظفين والشارات -->
                        <div class="col-12 col-md-6">
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalEmployees }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي الموظفين</p>
                                </div>
                            </div>

                            <!-- الشارات -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($employeesByJobTitle as $index => $item)
                                    @php
                                        $isLast = $index === count($employeesByJobTitle) - 1;
                                        $isOdd = count($employeesByJobTitle) % 2 !== 0;
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <span class="badge-custom" id="badge-employeesByJobTitle-{{ $index }}"
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
                                <canvas id="employeesByJobTitleChart" style="width: 100%; height: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- الموظفين حسب حالة الموارد البشرية -->
        <div class="col-12 col-xl-6 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="employeesByHRStatusCard">
                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">الموظفين حسب حالة الموارد البشرية</h5>
                            <button type="button" id="exportEmployeesByHRStatusBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- العمود الأول: العنوان وإجمالي الموظفين والشارات -->
                        <div class="col-12 col-md-6">
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalEmployees }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي الموظفين</p>
                                </div>
                            </div>

                            <!-- الشارات -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($employeesByHRStatus as $index => $item)
                                    @php
                                        $isLast = $index === count($employeesByHRStatus) - 1;
                                        $isOdd = count($employeesByHRStatus) % 2 !== 0;
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <span class="badge-custom" id="badge-employeesByHRStatus-{{ $index }}"
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
                                <canvas id="employeesByHRStatusChart" style="width: 100%; height: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- الموظفين حسب تصنيف الموارد البشرية -->
        <div class="col-12 col-xl-6 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="employeesByHRClassificationCard">
                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">الموظفين حسب تصنيف الموارد البشرية</h5>
                            <button type="button" id="exportEmployeesByHRClassificationBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- العمود الأول: العنوان وإجمالي الموظفين والشارات -->
                        <div class="col-12 col-md-6">
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalEmployees }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي الموظفين</p>
                                </div>
                            </div>

                            <!-- الشارات -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($employeesByHRClassification as $index => $item)
                                    @php
                                        $isLast = $index === count($employeesByHRClassification) - 1;
                                        $isOdd = count($employeesByHRClassification) % 2 !== 0;
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <span class="badge-custom" id="badge-employeesByHRClassification-{{ $index }}"
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
                                <canvas id="employeesByHRClassificationChart" style="width: 100%; height: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- الموظفين حسب مجال المعرفة -->
        <div class="col-12 col-xl-6 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="employeesByKnowledgeAreaCard">
                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">الموظفين حسب مجال المعرفة</h5>
                            <button type="button" id="exportEmployeesByKnowledgeAreaBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- العمود الأول: العنوان وإجمالي الموظفين والشارات -->
                        <div class="col-12 col-md-6">
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalEmployees }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي الموظفين</p>
                                </div>
                            </div>

                            <!-- الشارات -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($employeesByKnowledgeArea as $index => $item)
                                    @php
                                        $isLast = $index === count($employeesByKnowledgeArea) - 1;
                                        $isOdd = count($employeesByKnowledgeArea) % 2 !== 0;
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <span class="badge-custom" id="badge-employeesByKnowledgeArea-{{ $index }}"
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
                                <canvas id="employeesByKnowledgeAreaChart" style="width: 100%; height: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- الموظفين حسب نوع الرخصة -->
        <div class="col-12 col-xl-6 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="employeesByLicenseTypeCard">
                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">الموظفين حسب نوع الرخصة</h5>
                            <button type="button" id="exportEmployeesByLicenseTypeBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- العمود الأول: العنوان وإجمالي الموظفين والشارات -->
                        <div class="col-12 col-md-6">
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalEmployees }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي الموظفين</p>
                                </div>
                            </div>

                            <!-- الشارات -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($employeesByLicenseType as $index => $item)
                                    @php
                                        $isLast = $index === count($employeesByLicenseType) - 1;
                                        $isOdd = count($employeesByLicenseType) % 2 !== 0;
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <span class="badge-custom" id="badge-employeesByLicenseType-{{ $index }}"
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
                                <canvas id="employeesByLicenseTypeChart" style="width: 100%; height: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- الموظفين حسب الدرجة العلمية -->
        <div class="col-12 col-xl-6 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="employeesByQualificationDegreeCard">
                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">الموظفين حسب الدرجة العلمية</h5>
                            <button type="button" id="exportEmployeesByQualificationDegreeBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- العمود الأول: العنوان وإجمالي الموظفين والشارات -->
                        <div class="col-12 col-md-6">
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalEmployees }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي الموظفين</p>
                                </div>
                            </div>

                            <!-- الشارات -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($employeesByQualificationDegree as $index => $item)
                                    @php
                                        $isLast = $index === count($employeesByQualificationDegree) - 1;
                                        $isOdd = count($employeesByQualificationDegree) % 2 !== 0;
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <span class="badge-custom" id="badge-employeesByQualificationDegree-{{ $index }}"
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
                                <canvas id="employeesByQualificationDegreeChart" style="width: 100%; height: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

          <!-- الموظفين حسب حالة التأمين -->
          <div class="col-12 col-xl-6 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="employeesByInsuranceStatusCard">
                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">الموظفين حسب حالة التأمين</h5>
                            <button type="button" id="exportEmployeesByInsuranceStatusBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- العمود الأول: العنوان وإجمالي الموظفين والشارات -->
                        <div class="col-12 col-md-6">
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalEmployees }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي الموظفين</p>
                                </div>
                            </div>

                            <!-- الشارات -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($employeesByInsuranceStatus as $index => $item)
                                    @php
                                        $isLast = $index === count($employeesByInsuranceStatus) - 1;
                                        $isOdd = count($employeesByInsuranceStatus) % 2 !== 0;
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <span class="badge-custom" id="badge-employeesByInsuranceStatus-{{ $index }}"
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
                                <canvas id="employeesByInsuranceStatusChart" style="width: 100%; height: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- الموظفين حسب شهر الميلاد (هجري) -->
        <div class="col-12 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="employeesByBirthMonthCard">
                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">الموظفين حسب شهر الميلاد (هجري)</h5>
                            <button type="button" id="exportEmployeesByBirthMonthBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- العمود الأول: العنوان وإجمالي الموظفين والشارات -->
                        <div class="col-12 col-md-6">
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalEmployees }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي الموظفين</p>
                                </div>
                            </div>

                            <!-- الشارات -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($employeesByBirthMonth as $index => $item)
                                    @php
                                        $isLast = $index === count($employeesByBirthMonth) - 1;
                                        $isOdd = count($employeesByBirthMonth) % 2 !== 0;
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <span class="badge-custom" id="badge-employeesByBirthMonth-{{ $index }}"
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
                                <canvas id="employeesByBirthMonthChart" style="width: 100%; height: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- الموظفين حسب شهر بدء العقد (هجري) -->
        <div class="col-12 mb-5 d-flex align-items-stretch">
            <div class="card w-100" id="employeesByContractStartMonthCard">
                <div class="card-body">
                    <div class="row">
                        <div class="card-title mb-2 d-flex justify-content-between">
                            <h5 class="mb-0 text-nowrap">الموظفين حسب شهر بدء العقد (هجري)</h5>
                            <button type="button" id="exportEmployeesByContractStartMonthBtn"
                                class="export-btn btn btn-icon btn-sm btn-primary waves-effect waves-light">
                                <span class="tf-icon ti-xs ti ti-download"></span>
                            </button>
                        </div>
                        <!-- العمود الأول: العنوان وإجمالي الموظفين والشارات -->
                        <div class="col-12 col-md-6">
                            <div class="mb-4">

                                <div class="chart-statistics mb-2">
                                    <h3 class="card-title mb-0 text-primary">{{ $totalEmployees }}</h3>
                                    <p class="text-muted text-nowrap mb-0">إجمالي الموظفين</p>
                                </div>
                            </div>

                            <!-- الشارات -->
                            <div class="row g-1 align-items-stretch">
                                @foreach ($employeesByContractStartMonth as $index => $item)
                                    @php
                                        $isLast = $index === count($employeesByContractStartMonth) - 1;
                                        $isOdd = count($employeesByContractStartMonth) % 2 !== 0;
                                    @endphp
                                    <div class="{{ $isLast && $isOdd ? 'col-12 mb-2' : 'col-6 mb-2' }}">
                                        <span class="badge-custom" id="badge-employeesByContractStartMonth-{{ $index }}"
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
                                <canvas id="employeesByContractStartMonthChart" style="width: 100%; height: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>



    </div> <!-- نهاية الصف -->

    <script>
        window.employeesByNationalityData = @json($employeesByNationality);
        window.employeesByJobTitleData = @json($employeesByJobTitle);
        window.employeesByHRStatusData = @json($employeesByHRStatus);
        window.employeesByHRClassificationData = @json($employeesByHRClassification);
        window.employeesByKnowledgeAreaData = @json($employeesByKnowledgeArea);
        window.employeesByLicenseTypeData = @json($employeesByLicenseType);
        window.employeesByQualificationDegreeData = @json($employeesByQualificationDegree);
        window.employeesByBirthMonthData = @json($employeesByBirthMonth);
        window.employeesByContractStartMonthData = @json($employeesByContractStartMonth);
        window.employeesByInsuranceStatusData = @json($employeesByInsuranceStatus);
        window.colorsUnique = @json($colorsUnique);
    </script>
@endsection
