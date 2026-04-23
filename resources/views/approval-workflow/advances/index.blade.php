@extends('layouts.layoutMaster')

@section('title', 'اعتمادات السلف')

@section('breadcrumb')
    {{-- مسار التنقل البسيط والمطابق --}}
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">اعتمادات السلف</a>
        <i class="ti ti-star favorite-icon" data-page-name="اعتمادات السلف" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event,this)"></i>
    </li>
@endsection

{{-- الأنماط الخاصة بالمكتبات الخارجية (مطابقة تماماً) --}}
@section('vendor-style')
    @vite([
        'resources/assets/css/components/icons.css',
        'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
        'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
        'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
        'resources/assets/vendor/libs/select2/select2.scss'
    ])
@endsection

{{-- السكربتات الخاصة بالمكتبات الخارجية (مطابقة تماماً) --}}
@section('vendor-script')
    @vite([
        'resources/assets/vendor/libs/moment/moment.js',
        'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
        'resources/assets/vendor/libs/select2/select2.js'
    ])
    {!! $dataTable->scripts() !!}
@endsection

{{-- السكربت الخاص بالصفحة --}}
@section('page-script')
    @vite(['resources/assets/js/approval-workflow/advances/index.js'])
@endsection

{{-- أنماط خاصة بالصفحة (مطابقة) --}}
@section('page-style')
    @vite(['resources/css/dataTable.css'])
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection


{{-- المحتوى الرئيسي للصفحة (تصميم مطابق تماماً) --}}
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
                    <label for="filter-advance-type" class="form-label">نوع السلفة</label>
                    <select id="filter-advance-type" class="form-control select2" data-placeholder="الكل">
                        <option value=""></option>
                        @foreach ($advanceTypes as $type)
                            <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <hr class="mx-n4">

            {{-- الجدول --}}
            {!! $dataTable->table(['class' => 'table table-striped table-bordered w-100'], true) !!}
        </div>
    </div>
@endsection
