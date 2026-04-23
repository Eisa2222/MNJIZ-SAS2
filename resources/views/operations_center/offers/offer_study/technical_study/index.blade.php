    @extends('layouts.layoutMaster')

    @section('title', 'الدراسة الفنية')

    @section('breadcrumb')
        <li><a href="#"> مركز العمليات </a></li>
        <li><a href="#"> دراسات العروض </a></li>
        <li class="breadcrumb-item d-flex align-items-center"><a href="#"> الدراسة الفنية</a>
            <i class="ti ti-star favorite-icon" data-page-name="الدراسة الفنية" data-page-url="{{ url()->current() }}"
                onclick="toggleFavorite(event, this)"></i>
        </li>
    @endsection

    @section('vendor-style')
        @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
    @endsection

    @section('vendor-script')
        @vite(['resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
        {!! $dataTable->scripts() !!}

        <script>
            $(function() {
                var table = $('#offers-technical-study-table').DataTable();

                $('#select-all').on('change', function() {
                    $('#offers-technical-study-table tbody input.row-checkbox')
                        .prop('checked', this.checked);
                });

                $('#offers-technical-study-table tbody').on('change', 'input.row-checkbox', function() {
                    var allBoxes = $('#offers-technical-study-table tbody input.row-checkbox');
                    var checkedBoxes = allBoxes.filter(':checked');

                    $('#select-all').prop('checked', allBoxes.length === checkedBoxes.length);
                });
            });
        </script>

    @endsection

    @section('toastr')
        @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
    @endsection

    @section('page-style')
        @vite(['resources/css/dataTable.css'])
    @endsection





    @section('content')
        <div class="row g-4 mb-4">
            <!-- بطاقات الإحصائيات -->
            <div class="col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">العروض</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">{{ $totalOffers }}</h4>
                                </div>
                                <small class="mb-0">إجمالي العروض</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="ti ti-stack ti-26px"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">انتظار موافقة العميل</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">{{ $waitingClientApproval }}</h4>
                                </div>
                                <small class="mb-0">انتظار موافقة العميل</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="ti ti-hourglass ti-26px"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">مرحلة التعاقد</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">{{ $contractStage }}</h4>
                                </div>
                                <small class="mb-0">مرحلة التعاقد</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class="ti ti-file-check ti-26px"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">العروض المنتهية</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">{{ $finishedOffers }}</h4>
                                </div>
                                <small class="mb-0">العروض المنتهية</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-danger">
                                    <i class="ti ti-circle-x ti-26px"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="filters row g-3">
                    <div class="col-md-4">
                        <label for="filter-status">مرحلة العرض</label>
                        <select id="filter-status" name="status" class="form-control select2"
                            data-placeholder="اختر مرحلة العرض">
                            <option value=""></option>
                            @foreach ($stages_price_offer as $stage)
                                <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="filter-customer">العميل</label>
                        <select id="filter-customer" class="form-control select2" data-placeholder="اختر العميل">
                            <option value=""></option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="filter-manager">مسؤول العلاقات</label>
                        <select id="filter-manager" class="form-control select2" data-placeholder="اختر  مسؤول العلاقات">
                            <option value=""></option>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <hr class="mt-10">
                {!! $dataTable->table(['class' => 'table table-striped table-bordered w-100'], true) !!}
            </div>
        </div>
    @endsection
