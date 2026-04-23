@extends('layouts.layoutMaster')

@section('title', 'اعتمادات مسيرات الرواتب')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">اعتمادات مسيرات الرواتب</a>
        <i class="ti ti-star favorite-icon" data-page-name="اعتمادات الرواتب" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event,this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite([
        'resources/assets/css/components/icons.css',
        'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
        'resources/assets/vendor/libs/select2/select2.scss'
    ])
@endsection

@section('vendor-script')
    @vite([
        'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
        'resources/assets/vendor/libs/select2/select2.js'
    ])
    {!! $dataTable->scripts() !!}
@endsection

{{-- @section('page-script')
    @vite(['resources/assets/js/approval-workflow/wps-payrolls/index.js'])
@endsection --}}

@section('page-style')
    @vite(['resources/css/dataTable.css'])
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            {!! $dataTable->table(['class' => 'table table-striped table-bordered w-100'], true) !!}
        </div>
    </div>
@endsection
