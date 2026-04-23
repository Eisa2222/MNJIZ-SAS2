@extends('layouts.layoutMaster')

@section('title', 'اعتمادات المكافآت')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">اعتمادات المكافآت</a>
        <i class="ti ti-star favorite-icon" data-page-name="اعتمادات المكافآت" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event,this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/css/components/icons.css', 'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js'])
    {!! $dataTable->scripts() !!}
@endsection

@section('page-script')
    @vite(['resources/assets/js/approval-workflow/rewards/index.js'])
@endsection

@section('page-style')
    @vite(['resources/css/dataTable.css'])
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            {{-- الفلاتر --}}
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="filter-employee" class="form-label">الموظف</label>
                    <select id="filter-employee" class="form-control select2" data-placeholder="الكل">
                        <option value=""></option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="filter-reward-type" class="form-label">نوع المكافأة</label>
                    <select id="filter-reward-type" class="form-control select2" data-placeholder="الكل">
                        <option value=""></option>
                        @foreach ($rewardTypes as $type)
                            <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <hr class="mx-n4">
            {!! $dataTable->table(['class' => 'table table-striped table-bordered w-100'], true) !!}
        </div>
    </div>
@endsection
