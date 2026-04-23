@extends('layouts.layoutMaster')

@section('title', 'طلبات الإجازات')

@section('breadcrumb')
<li><a href="#">الخدمات الإلكترونية</a></li>
<li class="breadcrumb-item d-flex align-items-center"><a href="#"> طلبات الإجازات</a>
    <i class="ti ti-star favorite-icon" data-page-name="طلبات الإجازات" data-page-url="{{ url()->current() }}"
        onclick="toggleFavorite(event, this)"></i>
</li>
@endsection

@section('vendor-style')
@vite(['resources/assets/css/components/icons.css',
'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/@form-validation/form-validation.scss',
'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/js/custom/select-all.js', 'resources/assets/vendor/libs/moment/moment.js',
'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js',
'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/cleavejs/cleave.js',
'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
{!! $dataTable->scripts() !!}
@endsection

@section('page-script')
@vite(['resources/assets/js/electronic-services/leave-requests/leave-requests.js'])
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
                        <span class="text-heading">طلبات الإجازات</span>
                        <div class="d-flex align-items-center my-1">
                            <h4 class="mb-0 me-2">{{ $totalRequests }}</h4>
                        </div>
                        <small class="mb-0">إجمالي طلبات الإجازات</small>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-label-warning">
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
                        <span class="text-heading">بانتظار الموافقة</span>
                        <div class="d-flex align-items-center my-1">
                            <h4 class="mb-0 me-2">{{ $pendingRequests }}</h4>
                        </div>
                        <small class="mb-0"> إجمالي الطلبات الجديدة </small>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-label-primary">
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
                        <span class="text-heading"> المعتمدة</span>
                        <div class="d-flex align-items-center my-1">
                            <h4 class="mb-0 me-2">{{ $approvedRequests }}</h4>
                        </div>
                        <small class="mb-0"> إجمالي الطلبات المعتمدة</small>

                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-label-success">
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
                        <span class="text-heading"> المرفوضة</span>
                        <div class="d-flex align-items-center my-1">
                            <h4 class="mb-0 me-2">{{ $rejectedRequests }}</h4>
                        </div>
                        <small class="mb-0"> إجمالي الطلبات المرفوضة</small>
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
            <div class="col-md-6">
                <label for="filter-type">نوع الإجازة</label>
                <select id="filter-type" class="form-control select2" data-placeholder="اختر الموظف">
                    <option value=""></option>
                    @foreach ($leaveTypes as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label for="filter-status">حالة الطلب</label>
                <select id="filter-status" class="form-control select2" data-placeholder="اختر  حالة الطلب">
                    <option value=""></option>
                    @foreach ($requestStatus as $status)
                    <option value="{{ $status['id'] }}">{{ $status['name'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <hr class="mt-10">
        {!! $dataTable->table(['class' => 'table table-striped table-bordered w-100'], true) !!}
    </div>
</div>


@endsection