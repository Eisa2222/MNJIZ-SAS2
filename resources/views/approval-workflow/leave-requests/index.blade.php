@extends('layouts.layoutMaster')
@section('title', 'اعتمادات طلبات الإجازات')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">اعتمادات الإجازات</a>
        <i class="ti ti-star favorite-icon" data-page-name="اعتمادات الإجازات" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event,this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('page-style')
    @vite(['resources/css/dataTable.css'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
    {!! $dataTable->scripts() !!}
@endsection

@section('page-script')
    @vite(['resources/assets/js/approval-workflow/approval-manager.js', 'resources/assets/js/approval-workflow/approval-config.js', 'resources/assets/js/approval-workflow/leave-requests/leave-requests-index.js'])
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            {{-- الفلاتر --}}
            <div class="filters row g-3 mb-3">
                <div class="col-md-6">
                    <label for="filter-leave-type" class="form-label">نوع الإجازة</label>
                    <select id="filter-leave-type" class="form-control select2" data-placeholder="اختر نوع الإجازة">
                        <option value=""></option>
                        @foreach ($leaveTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="filter-employee" class="form-label">الموظف</label>
                    <select id="filter-employee" class="form-control select2" data-placeholder="اختر الموظف">
                        <option value=""></option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <hr class="mt-0 mb-4">

            <div class="table-responsive text-nowrap">
                {!! $dataTable->table(['class' => 'datatables-basic table table-striped table-bordered w-100'], true) !!}
            </div>
        </div>
    </div>
@endsection
