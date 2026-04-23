{{-- resources/views/approval-workflow/unified/index.blade.php --}}
@extends('layouts.layoutMaster')

@section('title', 'نظام الاعتمادات الموحد')

@section('breadcrumb')
<li class="breadcrumb-item d-flex align-items-center">
    <a href="#">نظام الاعتمادات الموحد</a>
    <i class="ti ti-star favorite-icon" data-page-name="نظام الاعتمادات" data-page-url="{{ url()->current() }}"
        onclick="toggleFavorite(event,this)"></i>
</li>
@endsection

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss'
])
@endsection

@section('page-style')
@vite(['resources/css/dataTable.css'])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/moment/moment.js',
'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js'
])
{!! $dataTable->scripts() !!}
@endsection

@section('page-script')
@vite([
'resources/assets/js/approval-workflow/unified/unified-approval-index.js'
])
@endsection

@section('toastr')
@vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('content')
{{-- الإحصائيات العامة --}}
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div class="content-left">
                        <span class="text-heading">إجمالي الطلبات</span>
                        <div class="d-flex align-items-center my-1">
                            <h4 class="mb-0 me-2">{{ $statistics['total'] }}</h4>
                        </div>
                        <small class="mb-0">جميع طلبات الاعتماد</small>
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
                        <span class="text-heading">قيد الانتظار</span>
                        <div class="d-flex align-items-center my-1">
                            <h4 class="mb-0 me-2">{{ $statistics['pending'] }}</h4>
                        </div>
                        <small class="mb-0">طلبات تحتاج موافقتك</small>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-label-warning">
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
                        <span class="text-heading">معتمدة</span>
                        <div class="d-flex align-items-center my-1">
                            <h4 class="mb-0 me-2">{{ $statistics['approved'] }}</h4>
                        </div>
                        <small class="mb-0">طلبات معتمدة</small>
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
                        <span class="text-heading">مرفوضة</span>
                        <div class="d-flex align-items-center my-1">
                            <h4 class="mb-0 me-2">{{ $statistics['rejected'] }}</h4>
                        </div>
                        <small class="mb-0">طلبات مرفوضة</small>
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
{{-- الجدول الرئيسي --}}
<div class="card">
    <div class="card-body">
        {{-- الفلاتر --}}
        <div class="filters row g-3 mb-3">
            <div class="col-md-3">
                <label for="filter-type" class="form-label">نوع الطلب</label>
                <select id="filter-type" class="form-control select2" data-placeholder="اختر نوع الطلب">
                    <option value=""></option>
                    @foreach($flowTypes as $key => $name)
                    <option value="{{ $key }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label for="filter-employee" class="form-label">مقدم الطلب</label>
                <select id="filter-employee" class="form-control select2" data-placeholder="اختر مقدم الطلب">
                    <option value=""></option>
                    @foreach($employees as $employee)
                    <option value="{{ $employee->user_id }}">{{ $employee->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label for="filter-date-from" class="form-label">من تاريخ</label>
                <input type="date" id="filter-date-from" class="form-control">
            </div>

            <div class="col-md-3">
                <label for="filter-date-to" class="form-label">إلى تاريخ</label>
                <input type="date" id="filter-date-to" class="form-control">
            </div>
        </div>

        <hr class="mt-10">

        {{-- الجدول --}}
        <div class="table-responsive text-nowrap">
            {!! $dataTable->table(['class' => 'datatables-basic table table-striped table-bordered w-100'], true) !!}
        </div>
    </div>
</div>
@endsection