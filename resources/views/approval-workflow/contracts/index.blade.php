@extends('layouts.layoutMaster')

@section('title', 'اعتمادات العقود')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">اعتمادات العقود</a>
        <i class="ti ti-star favorite-icon" data-page-name="اعتمادات العقود" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event,this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite([
        'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
        'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
        'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
        'resources/assets/vendor/libs/select2/select2.scss',
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
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
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'
    ])
    {!! $dataTable->scripts() !!}
@endsection

@section('page-script')
    @vite([
        'resources/assets/js/approval-workflow/contracts/contracts-index.js'
    ])
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="table-responsive text-nowrap">
                {!! $dataTable->table(['class' => 'datatables-basic table table-striped table-bordered w-100'], true) !!}
            </div>
        </div>
    </div>
@endsection
