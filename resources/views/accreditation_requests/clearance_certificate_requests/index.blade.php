@extends('layouts.layoutMaster')

@section('title', 'إعتماد إخلاء الطرف')

@section('breadcrumb')
    <li><a href="{{ route('account.electronic-services.violations-penalties.index') }}"> طلبات الإعتماد</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">إعتماد إخلاء الطرف </a>
        <i class="ti ti-star favorite-icon" data-page-name="إعتماد إخلاء الطرف" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection


@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('page-style')
    @vite(['resources/css/dataTable.css'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/form-validation.js'])
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

            var table = $('#table').DataTable({
                "createdRow": function(row, data, dataIndex) {
                    $(row).find('td.table-ellipsis').each(function() {
                        var cellContent = $(this)
                            .html(); // استخدام .html() للحفاظ على الهيكل الأصلي
                        // تحقق مما إذا كان يحتوي على رابط
                        if ($(this).find('a').length > 0) {
                            // إذا كان يحتوي على رابط، لف النص داخل الرابط نفسه
                            var linkElement = $(this).find('a');
                            var linkText = linkElement.text();
                            linkElement.html('<div class="cell-content"><span>' + linkText +
                                '</span></div>');
                        } else {
                            // إذا لم يكن هناك رابط، لف النص كالمعتاد
                            $(this).html('<div class="cell-content"><span>' + cellContent +
                                '</span></div>');
                        }
                    });
                },

                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('accreditation-requests.clearance-certificate.index') }}",
                    data: function(d) {
                        d.status = $('#filter-status').val();
                        d.user = $('#filter-user').val();
                    }
                },
                columns: [{
                        data: "",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'checkbox',
                        name: 'checkbox',
                        orderable: false,
                        searchable: false,
                        className: 'custom-checkbox',
                        render: function(data, type, full, meta) {
                            return '<input type="checkbox" class="row-checkbox" value="' + full.id +
                                '">';
                        }
                    },
                    {
                        data: 'user_name',
                        name: 'user_name',
                        width: '150px',

                    },
                    {
                        data: 'reason',
                        name: 'reason',
                        width: '250px',

                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        width: '50px',
                    },
                    {
                        data: 'status',
                        name: 'status',
                        width: '50px',

                    },
                    {
                        data: 'action',
                        name: 'action',
                        width: '50px',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [3, 'desc']
                ],
                dom: '<"dt-toolbar"' +
                    '<"dt-toolbar-left d-flex flex-column flex-sm-row align-items-start align-items-sm-center" l >' +
                    '<"dt-toolbar-right d-flex flex-column flex-sm-row align-items-end align-items-sm-center justify-content-between" f B >' +
                    '>' +
                    'rt' +
                    '<"row mt-3"' +
                    '<"d-flex flex-column flex-sm-row align-items-center justify-content-between w-100" i <"pagination-wrapper overflow-auto w-100" p >' +
                    '>' +
                    '>',
                buttons: [{
                    extend: 'collection',
                    className: 'btn btn-export btn',
                    text: 'الإجراءات',
                    buttons: [{
                        extend: 'copy',
                        text: 'نسخ'
                    }, {
                        extend: 'excel',
                        text: 'إكسل'
                    }, ]
                }, ],

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
                }],
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
                                return col.title !== "" ?
                                    '<tr data-dt-row="' + col.rowIndex + '" data-dt-column="' +
                                    col.columnIndex + '">' +
                                    "<td>" + col.title + ":" + "</td> " +
                                    "<td>" + col.data + "</td>" +
                                    "</tr>" : "";
                            }).join("");

                            return data ? $('<table class="table"/><tbody />').append(data) : false;
                        },
                    },
                },
            });


            // تحديث الجدول عند تغيير الفلاتر
            $('#filter-status, #filter-user').change(function() {
                table.draw();
            });

            // تحديد الكل
            $('#select-all').on('click', function() {
                var rows = table.rows({
                    'search': 'applied'
                }).nodes();
                $('input[type="checkbox"]', rows).prop('checked', this.checked);
            });


            // كود تحريك النص
            $(document).on('mouseenter', '.table-ellipsis', function() {
                var spanDom = $(this).find('span').get(0);
                var textWidth = spanDom.scrollWidth;
                var cellWidth = $(this).width();

                if (textWidth > cellWidth) {
                    // مقدار الحركة الفعلي
                    const distance = textWidth - cellWidth;
                    // معدل السرعة (بكسل في الثانية)
                    const speed = 30; // يمكنك تغييره

                    // المدة اللازمة = المسافة / السرعة
                    const duration = distance / speed; // بالثواني

                    $(spanDom).css({
                        'transform': `translateX(${distance}px)`,
                        // نجعل التحول يحدث على مدى 'duration' ثوانٍ
                        'transition': `transform ${duration}s linear`
                    });
                }
            }).on('mouseleave', '.table-ellipsis', function() {
                $(this).find('span').css('transform', 'translateX(0)');
            });

        });
    </script>
@endsection

@section('content')
    <div class="row g-4 mb-4">
        <!-- بطاقات الإحصائيات -->
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body d-flex align-items-start justify-content-between">
                    <div>
                        <span class="text-heading">الطلبات</span>
                        <div class="d-flex align-items-center my-1">
                            <h4 class="mb-0 me-2">{{ $total }}</h4>
                        </div>
                        <small class="mb-0">إجمالي الطلبات</small>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-label-primary">
                            <i class="ti ti-files ti-26px"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body d-flex align-items-start justify-content-between">
                    <div>
                        <span class="text-heading">قيد الانتظار</span>
                        <div class="d-flex align-items-center my-1">
                            <h4 class="mb-0 me-2">{{ $pending }}</h4>
                        </div>
                        <small class="mb-0">الطلبات قيد الانتظار</small>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-label-warning">
                            <i class="ti ti-clock ti-26px"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body d-flex align-items-start justify-content-between">
                    <div>
                        <span class="text-heading">معتمدة</span>
                        <div class="d-flex align-items-center my-1">
                            <h4 class="mb-0 me-2">{{ $approved }}</h4>
                        </div>
                        <small class="mb-0">الطلبات المعتمدة</small>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-label-success">
                            <i class="ti ti-circle-check ti-26px"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body d-flex align-items-start justify-content-between">
                    <div>
                        <span class="text-heading">مرفوضة</span>
                        <div class="d-flex align-items-center my-1">
                            <h4 class="mb-0 me-2">{{ $rejected }}</h4>
                        </div>
                        <small class="mb-0">الطلبات المرفوضة</small>
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


    <div class="card">
        <div class="card-body">
            <div class="filters row g-3">
                <div class="col-md-6">
                    <label for="filter-user">الموظف</label>
                    <select id="filter-user" class="form-control select2" data-placeholder="اختر الموظف">
                        <option value=""></option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="filter-status"> حالة الطلب</label>
                    <select id="filter-status" class="form-control select2" data-placeholder="اختر حالة الطلب">
                        <option value=""></option>
                        @foreach ($statuses as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <hr class="mt-10">
            <table id="table" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th></th>
                        <th class="custom-checkbox">
                            <input type="checkbox" id="select-all">
                        </th>
                        <th>الموظف</th>
                        <th>السبب</th>
                        <th>تاريخ الطلب</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
@endsection
