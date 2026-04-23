@extends('layouts.layoutMaster')

@section('title', ' المشتريات')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> المشتريات</a>
        <i class="ti ti-star favorite-icon" data-page-name=" المشتريات" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js'])
@endsection
@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('page-style')
    @vite(['resources/css/dataTable.css'])
@endsection

@section('page-script')
    <script>
        $(document).ready(function() {
            // تهيئة Select2
            $('.select2').select2({
                placeholder: function() {
                    return $(this).data('placeholder');
                },
                allowClear: true,
                width: '100%',
                language: 'ar',
                dir: 'rtl'
            });

            var table = $('#employees-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('purchasing-center.purchase-requests.invoices.index') }}",
                    data: function(d) {
                        // جمع قيم الفلاتر
                        d.date_range = $('#filter-date-range').val();
                        d.category_id = $('#filter-category').val();
                        d.user_id = $('#filter-user').val();
                        d.amount_operator = $('#filter-amount-operator').val();
                        d.amount_value = $('#filter-amount-value').val();
                        d.amount_value2 = $('#filter-amount-value2').val();
                        d.date_from = $('#filter-date-from').val();
                        d.date_to = $('#filter-date-to').val();
                    },
                    error: function(xhr, error, thrown) {
                        // Improved error handling
                        Swal.fire({
                            icon: 'error',
                            title: 'خطأ في تحميل البيانات',
                            text: 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة مرة أخرى.',
                            confirmButtonText: 'موافق'
                        });
                    }
                },

                columns: [{
                        data: null,
                        defaultContent: '',
                        className: 'control',
                        orderable: false
                    },
                    {
                        data: 'checkbox',
                        name: 'checkbox',
                        orderable: false,
                        searchable: false,
                        className: 'custom-checkbox',
                    },
                    {
                        data: 'invoice_number',
                        name: 'invoice_number',
                        className: 'text-start',
                        render: function(data, type, full) {
                            return '<span class="fw-medium">' + (data ? data : '-') + '</span>';
                        }
                    },
                    {
                        data: 'total_amount',
                        name: 'total_amount',
                    },
                    {
                        data: 'invoice_date',
                        name: 'invoice_date',
                    },
                    {
                        data: 'user_id',
                        name: 'user_id',
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        visible: function() {
                            return {!! auth()->user()->can('تعديل مشتريات') || auth()->user()->can('حذف مشتريات') ? 'true' : 'false' !!};
                        }()
                    },
                    {
                        data: 'id',
                        name: 'id',
                        visible: false
                    }
                ],
                order: [
                    [7, 'desc']
                ],

                dom: '<"dt-toolbar"' +
                    '<"dt-toolbar-left d-flex flex-column flex-sm-row align-items-start align-items-sm-center"' +
                    'l' +
                    '>' +
                    '<"dt-toolbar-right d-flex flex-column flex-sm-row align-items-end align-items-sm-center justify-content-between"' +
                    'f' +
                    'B' +
                    '>' +
                    '>' +
                    'rt' +
                    '<"row mt-3"' +
                    '<"d-flex flex-column flex-sm-row align-items-center justify-content-between w-100"' +
                    'i' +
                    '<"pagination-wrapper overflow-auto w-100"' +
                    'p' +
                    '>' +
                    '>' +
                    '>',
                buttons: [{
                        extend: 'collection',
                        className: 'btn btn-export btn-sm',
                        text: 'الإجراءات',
                        buttons: [{
                                extend: 'copy',
                                text: 'نسخ'
                            },
                            {
                                extend: 'excel',
                                text: 'إكسل'
                            },
                            @can('حذف مشتريات')
                                {
                                    text: 'حذف المحدد',
                                    className: 'btn btn-default btn-delete-selected',
                                    action: function(e, dt, node, config) {
                                        var selectedIds = [];
                                        $('.row-checkbox:checked').each(function() {
                                            selectedIds.push($(this).val());
                                        });

                                        confirmDeleteSelectedmss(selectedIds,
                                            "{{ route('mass.delete') }}", 'Invoice'
                                        );
                                    }
                                },
                            @endcan
                        ]
                    },
                    @can('إضافة مشتريات')
                        {
                            text: '<i class="fas fa-plus-circle me-1"></i> إضافة مشتريات ',
                            className: 'btn btn-primary btn-add',
                            action: function(e, dt, node, config) {
                                window.location.href =
                                    "{{ route('purchasing-center.purchase-requests.invoices.create') }}";
                            }
                        }
                    @endcan
                ],

                language: {
                    url: "{{ asset('assets/json/ar.json') }}"
                },
                orderCellsTop: true,
                columnDefs: [{
                    className: "control",
                    orderable: false,
                    targets: 0,
                    render: function(data, type, full, meta) {
                        return "";
                    },
                }, ],
                responsive: {
                    details: {
                        display: $.fn.dataTable.Responsive.display.modal({
                            header: function(row) {
                                var data = row.data();
                                return "التفاصيل";
                            },
                        }),
                        type: "column",
                        renderer: function(api, rowIdx, columns) {
                            var data = $.map(columns, function(col, i) {
                                return col.title !==
                                    "" // ? Do not show row in modal popup if title is blank (for check box)
                                    ?
                                    '<tr data-dt-row="' +
                                    col.rowIndex +
                                    '" data-dt-column="' +
                                    col.columnIndex +
                                    '">' +
                                    "<td>" +
                                    col.title +
                                    ":" +
                                    "</td> " +
                                    "<td>" +
                                    col.data +
                                    "</td>" +
                                    "</tr>" :
                                    "";
                            }).join("");

                            return data ?
                                $('<table class="table"/><tbody />').append(data) :
                                false;
                        },
                    },
                },
            });
            // تحديد الكل
            $('#select-all').on('click', function() {
                var rows = table.rows({
                    'search': 'applied'
                }).nodes();
                $('input[type="checkbox"]', rows).prop('checked', this.checked);
            });

            // تهيئة Flatpickr للتواريخ
            $('.flatpickr-date').flatpickr({
                dateFormat: "Y-m-d",
                locale: "ar"
            });

            // إظهار/إخفاء حقل القيمة الثانية عند اختيار "بين" في فلتر المبلغ
            $('#filter-amount-operator').on('change', function() {
                if ($(this).val() === 'between') {
                    $('#filter-amount-value2').removeClass('d-none');
                } else {
                    $('#filter-amount-value2').addClass('d-none');
                }
            });

            // إظهار/إخفاء حقول الفترة المخصصة
            $('#filter-date-range').on('change', function() {
                if ($(this).val() === 'custom') {
                    $('#custom-date-range').removeClass('d-none');
                } else {
                    $('#custom-date-range').addClass('d-none');
                }
            });

            // تطبيق الفلاتر عند النقر على زر التطبيق
            $('#apply-filters').on('click', function() {
                table.draw();
            });

            // إعادة تعيين الفلاتر
            $('#reset-filters').on('click', function() {
                $('.select2').val(null).trigger('change');
                $('#filter-amount-value').val('');
                $('#filter-amount-value2').val('').addClass('d-none');
                $('#filter-amount-operator').val('greater_than');
                $('#filter-date-from, #filter-date-to').val('');
                $('#custom-date-range').addClass('d-none');
                table.draw();
            });
        });
    </script>
@endsection

@section('content')
    <!-- إحصائيات المشتريات -->
    <div class="row g-4 mb-4">
        <!--  الفواتير -->
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">الفواتير</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $totalInvoices ?? 0 }}</h4>
                            </div>
                            <small class="mb-0 text-muted"> عدد الفواتير</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="ti ti-receipt ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--  المشتريات -->
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">قيمة المشتريات</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ number_format($totalAmount ?? 0) }}</h4>
                            </div>
                            <small class="mb-0"> قيمة المشتريات (ريال)</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="ti ti-cash ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- مشتريات الشهر الحالي -->
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">مشتريات الشهر</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ number_format($monthlyAmount ?? 0) }}</h4>
                            </div>
                            <small class="mb-0"> مشتريات الشهر الحالي</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="ti ti-calendar ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- أكثر فئة مشتريات -->
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">أكثر فئة مشتريات</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2 fs-6 fw-bold">{{ $topCategoryName ?? 'لا يوجد' }}</h4>
                            </div>
                            <small class="mb-0">الفئة الأكثر شراءً</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="ti ti-category ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- الجدول -->
    <div class="card">
        <div class="card-body">
            <!-- الفلاتر -->
            <div class="filters row mb-4">
                <div class="col-md-3">
                    <label for="filter-date-range">الفترة الزمنية</label>
                    <select id="filter-date-range" class="form-control select2" data-placeholder="اختر الفترة الزمنية">
                        <option value=""></option>
                        <option value="today">اليوم</option>
                        <option value="week">هذا الأسبوع</option>
                        <option value="month">هذا الشهر</option>
                        <option value="quarter">هذا الربع</option>
                        <option value="year">هذه السنة</option>
                        <option value="custom">فترة مخصصة</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="filter-category">التصنيف</label>
                    <select id="filter-category" class="form-control select2" data-placeholder="اختر  التصنيف">
                        <option value=""></option>
                        @foreach ($purchase_category as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="filter-user">تمت بواسطة</label>
                    <select id="filter-user" class="form-control select2" data-placeholder="اختر المستخدم">
                        <option value=""></option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filter-amount">مبلغ الفاتورة</label>
                    <div class="input-group">
                        <select id="filter-amount-operator" class="form-control">
                            <option value="greater_than">أكبر من</option>
                            <option value="less_than">أقل من</option>
                            <option value="between">بين</option>
                        </select>
                        <input type="number" id="filter-amount-value" class="form-control" placeholder="القيمة">
                        <input type="number" id="filter-amount-value2" class="form-control d-none"
                            placeholder="القيمة الثانية">
                    </div>
                </div>
            </div>

            <!-- قسم الفترة المخصصة (يظهر عند اختيار "فترة مخصصة") -->
            <div id="custom-date-range" class="row mb-4 d-none">
                <div class="col-md-6">
                    <label for="filter-date-from">من تاريخ</label>
                    <input type="date" id="filter-date-from" class="form-control flatpickr-date">
                </div>
                <div class="col-md-6">
                    <label for="filter-date-to">إلى تاريخ</label>
                    <input type="date" id="filter-date-to" class="form-control flatpickr-date">
                </div>
            </div>

            <!-- زر تطبيق الفلاتر وإعادة تعيين -->
            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-end">
                    <button id="reset-filters" class="btn btn-outline-secondary me-2">
                        <i class="ti ti-refresh me-1"></i>إعادة تعيين
                    </button>
                    <button id="apply-filters" class="btn btn-primary">
                        <i class="ti ti-filter me-1"></i>تطبيق الفلاتر
                    </button>
                </div>
            </div>

            <hr class="mt-0">

            <table id="employees-table" class="table table-striped table-bordered ">
                <thead>
                    <tr>
                        <th></th>
                        <th class="custom-checkbox">
                            <input type="checkbox" id="select-all">
                        </th>
                        <th>رقم الفاتورة</th>
                        <th>المجموع</th>
                        <th>تاريخ الإضافة</th>
                        <th>أضيف بواسطة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>


@endsection
