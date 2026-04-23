@extends('layouts.layoutMaster')

@section('title', 'سجل النشاطات')

@section('breadcrumb')
    <li><a href="#">إدارة النظام</a></li>

    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> سجل النشاطات</a>
        <i class="ti ti-star favorite-icon" data-page-name="سجل النشاطات" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/js/custom/select-all.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
    {!! $dataTable->scripts() !!}
@endsection

@section('page-script')
    <script>
        window.appUrls = {
            action: "{{ route('activity_logs.show', ['id' => ':id']) }}"
        };
    </script>

    @vite(['resources/assets/js/system-administration/activity-logs/activity-logs.js'])
@endsection



@section('page-style')
    @vite(['resources/css/dataTable.css'])
@endsection

@section('content')



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
                <!-- فلتر المستخدم -->
                <div class="col-md-4">
                    <label for="filter-user">المستخدم</label>
                    <select id="filter-user" class="form-control select2" data-placeholder="اختر المستخدم">
                        <option value=""></option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->user->id }}">{{ $employee->raw_name }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- فلتر تاريخ البداية -->
                <div class="col-md-4">
                    <label for="filter-from">من تاريخ</label>
                    <input type="date" id="filter-from" class="form-control">
                </div>
                <!-- فلتر تاريخ النهاية -->
                <div class="col-md-4">
                    <label for="filter-to">إلى تاريخ</label>
                    <input type="date" id="filter-to" class="form-control">
                </div>
            </div>

            <hr class="mt-10">

            {!! $dataTable->table(['class' => 'table table-striped table-bordered w-100'], true) !!}
        </div>
    </div>

    <!-- مودال التفاصيل -->
    <div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تفاصيل النشاط</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                </div>
            </div>
        </div>
    </div>

@endsection
