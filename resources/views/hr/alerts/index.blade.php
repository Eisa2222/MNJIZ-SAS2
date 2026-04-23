@extends('layouts.layoutMaster')

@section('title', 'تنبيهات التجديدات')

@section('breadcrumb')
    <li><a href="#">الموارد البشرية</a></li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">تنبيهات التجديدات</a>
        <i class="ti ti-star favorite-icon" data-page-name="تنبيهات التجديدات" data-page-url="{{ url()->current() }}"
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
    @vite(['resources/assets/js/hr/alert/alert.js'])
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
        <div class="col-sm-6 col-xl-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">تنبيهات التجديدات</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $totalAlert }}</h4>
                            </div>
                            <small class="mb-0">إجمالي تنبيهات التجديدات</small>
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

        <div class="col-sm-6 col-xl-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">التنبيهات الجديدة</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $newAlert }}</h4>
                            </div>
                            <small class="mb-0"> إجمالي تنبيهات التجديدات الجديدة </small>
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

        <div class="col-sm-6 col-xl-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">تنبيهات التجديدات المكتملة</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $resolvedAlert }}</h4>
                            </div>
                            <small class="mb-0"> إجمالي تنبيهات التجديدات المكتملة</small>

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
                    <label for="filter-employee">الموظف</label>
                    <select id="filter-employee" class="form-control select2" data-placeholder="اختر الموظف">
                        <option value=""></option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="filter-type">نوع التنبيه </label>
                    <select id="filter-type" class="form-control select2" data-placeholder="اختر نوع التنبيه ">
                        <option value=""></option>
                        @foreach ($alertType as $type)
                            <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="filter-status">حالة التنبيه </label>
                    <select id="filter-status" class="form-control select2" data-placeholder="اختر  حالة التنبيه ">
                        <option value=""></option>
                        @foreach ($alertStatus as $status)
                            <option value="{{ $status['id'] }}">{{ $status['name'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <hr class="mt-10">
            {!! $dataTable->table(['class' => 'table table-striped table-bordered w-100'], true) !!}
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // معالج النقر على زر تغيير الحالة
            $(document).on('click', '.toggle-status', function(e) {
                e.preventDefault();

                const button = $(this);
                const url = button.data('url');
                const id = button.data('id');

                // إظهار مؤشر التحميل
                button.prop('disabled', true);
                const originalIcon = button.find('i').attr('class');
                button.find('i').attr('class', 'ti ti-loader animate-spin');

                $.ajax({
                    url: url,
                    type: 'PATCH',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            // إظهار رسالة نجاح
                            toastr.success(response.message);

                            // تحديث الأيقونة حسب الحالة الجديدة
                            if (response.status) {
                                button.removeClass('text-secondary').addClass('text-success');
                                button.attr('title', 'مكتمل');
                            } else {
                                button.removeClass('text-success').addClass('text-secondary');
                                button.attr('title', 'غير مكتمل');
                            }

                            // تحديث DataTable
                            $('#alerts-table').DataTable().ajax.reload(null, false);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        console.error('خطأ:', xhr);
                        toastr.error('حدث خطأ أثناء التحديث');
                    },
                    complete: function() {
                        // إزالة مؤشر التحميل
                        button.prop('disabled', false);
                        button.find('i').attr('class', originalIcon);
                    }
                });
            });
        });
    </script>
@endsection
