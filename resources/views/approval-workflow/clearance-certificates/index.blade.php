{{-- resources/views/approval-workflow/clearance-certificates/index.blade.php --}}
@extends('layouts.layoutMaster')

@section('title', 'اعتمادات إخلاء الطرف')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">اعتمادات إخلاء الطرف</a>
        <i class="ti ti-star favorite-icon" data-page-name="اعتماد إخلاء الطرف" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event,this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/css/components/icons.css', 'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
    {!! $dataTable->scripts() !!}
@endsection

@section('page-script')
    @vite(['resources/assets/js/approval-workflow/clearance-certificates/index.js'])
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('page-style')
    @vite(['resources/css/dataTable.css'])
@endsection

@section('content')
    {{-- الفلاتر والجدول --}}
    <div class="card">
        <div class="card-body">
            {!! $dataTable->table(['class' => 'table table-striped table-bordered w-100'], true) !!}
        </div>
    </div>
@endsection
