{{-- @extends('layouts.layoutMaster')

@section('title', 'قائمة الوكالات')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> الوكالات</a>
        <i class="ti ti-star favorite-icon" data-page-name="الوكالات" data-page-url="{{ url()->current() }}"
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

            var table = $('#power-attorney-table').DataTable({
                // كود تحريك النص
                "createdRow": function(row, data, dataIndex) {
                    // البحث فقط عن الخلايا التي تحتوي على فئة 'table-ellipsis' وتغليف النص داخل span
                    $(row).find('td.table-ellipsis').each(function() {
                        var cellText = $(this).text();
                        $(this).html('<div class="cell-content"><span>' + cellText +
                            '</span></div>');
                        // تغليف النص داخل عنصر span فقط في الحقول التي تحتوي على 'table-ellipsis'
                    });
                },
                // كود تحريك النص
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('power-attorney.index') }}",
                    data: function(d) {
                        d.status = $('#filter-status').val();
                        d.customer = $('#filter-customer').val();
                        d.attorney = $('#filter-attorney').val();
                    }
                },
                columns: [{
                        data: ""
                    }, {
                        data: 'checkbox',
                        name: 'checkbox',
                        orderable: false,
                        searchable: false,
                        className: 'custom-checkbox',
                    },
                    {
                        data: 'power_name',
                        name: 'power_name',
                        className: 'table-ellipsis',

                    },
                    {
                        data: 'power_number',
                        name: 'power_number',

                    },
                    {
                        data: 'customers',
                        name: 'customers',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'agents',
                        name: 'agents',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'date_issued',
                        name: 'date_issued'
                    },
                    {
                        data: 'date_expiry',
                        name: 'date_expiry',
                        render: function(data, type, row) {
                            return data ? data : '<span style="color: red;">لا يوجد</span>';
                        },
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        visible: function() {
                            return {!! auth()->user()->can('تعديل وكالة') || auth()->user()->can('حذف وكالة') ? 'true' : 'false' !!};
                        }()
                    }, {
                        data: 'id',
                        name: 'id',
                        visible: false
                    }
                ],
                order: [
                    [10, 'desc']
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

                            @if (auth()->user()->can('حذف وكالة'))
                                {
                                    text: 'حذف المحدد',
                                    className: 'btn btn-default btn-delete-selected',
                                    action: function(e, dt, node, config) {
                                        var selectedIds = [];
                                        $('.row-checkbox:checked').each(function() {
                                            selectedIds.push($(this).val());
                                        });

                                        confirmDeleteSelectedmss(selectedIds,
                                            "{{ route('mass.delete') }}",
                                            'PowerOfAttorney'
                                        ); // هنا نرسل اسم الموديل
                                    }
                                }
                            @endif

                        ]
                    },

                    @can('إضافة وكالة')
                        {
                            text: '<i class="fas fa-plus-circle me-1"></i> إضافة وكالة جديدة',
                            className: 'btn btn-primary btn-add',
                            action: function(e, dt, node, config) {
                                window.location.href = "{{ route('power-attorney.create') }}";
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

            // تحديث الجدول عند تغيير الفلاتر
            $('#filter-status, #filter-customer, #filter-attorney').change(function() {
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
                var span = $(this).find('span');
                var textWidth = span.width();
                var cellWidth = $(this).width();
                if (textWidth > cellWidth) {
                    // تحريك النص من اليسار إلى اليمين بما يتناسب مع النص العربي
                    span.css('transform', 'translateX(' + (textWidth - cellWidth) + 'px)');
                }
            }).on('mouseleave', '.table-ellipsis', function() {
                $(this).find('span').css('transform', 'translateX(0)');
            });
            // كود تحريك النص


            // التعامل مع تغيير الحالة
            $(document).on('click', '.change-status', function(e) {
                e.preventDefault();
                var powerAttorneyId = $(this).data('id');
                var currentStatus = $(this).data('status');

                $('#modal_project_id').val(powerAttorneyId);
                $('#modal_status').val(currentStatus);
                $('#statusModal').modal('show');

                // تحديث مسار النموذج
                $('#status-form').attr('action', '/power-attorney/' + powerAttorneyId + '/update-status');
            });


            // التعامل مع زر عرض العملاء
            $(document).on('click', '.view-customers', function() {
                var customers = $(this).data('customers');
                var list = $('#customersList');
                list.empty(); // مسح القائمة السابقة

                if (customers) {
                    // تقسيم الأسماء إلى قائمة وعرضها
                    var customerArray = customers.split(', ');
                    customerArray.forEach(function(customer) {
                        list.append(
                            '<li class="list-group-item"><i class="ti ti-rosette-discount-check-filled text-success mx-1" title="عميل"></i>' +
                            customer + '</li>');
                    });
                } else {
                    list.append('<li class="list-group-item">لا يوجد عملاء</li>');
                }

                // عرض المودال
                var customersModal = new bootstrap.Modal(document.getElementById('customersModal'));
                customersModal.show();
            });

            // التعامل مع زر عرض الوكلاء
            $(document).on('click', '.view-agents', function() {
                var agents = $(this).data('agents');
                var list = $('#agentsList');
                list.empty(); // مسح القائمة السابقة

                if (agents) {
                    // تقسيم الأسماء إلى قائمة وعرضها
                    var agentArray = agents.split(', ');
                    agentArray.forEach(function(agent) {
                        list.append('<li class="list-group-item">' + agent + '</li>');
                    });
                } else {
                    list.append('<li class="list-group-item">لا يوجد وكلاء</li>');
                }

                // عرض المودال
                var agentsModal = new bootstrap.Modal(document.getElementById('agentsModal'));
                agentsModal.show();
            });



        });
    </script>
@endsection

@section('content')


    <div class="row g-4 mb-4">

        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">إجمالي الوكالات</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $powerAttorney }}</h4>
                            </div>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-primary">
                                <!-- أيقونة تمثل مجموعة من المباني أو الوكالات -->
                                <i class="ti ti-building ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">الوكالات النشطة</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $powerAttorney_active }}</h4>
                            </div>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-success">
                                <!-- أيقونة تمثل النشاط أو التحقق -->
                                <i class="ti ti-check ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">الوكالات المنتهية</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $powerAttorney_expired }}</h4>
                            </div>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="ti ti-clock ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">الوكالات الملغية</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $powerAttorney_revoked }}</h4>
                            </div>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-danger">
                                <!-- أيقونة تمثل الإلغاء أو العلامة الخاطئة -->
                                <i class="ti ti-x ti-26px"></i>
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
            <div class="filters row g-3">
                <div class="col-md-4">
                    <label for="filter-status">الحالة </label>
                    <select id="filter-status" class="form-control select2" data-placeholder="اختر الحالة ">
                        <option value=""></option>
                        <option value="active">سارية</option>
                        <option value="expired">منتهية</option>
                        <option value="revoked">ملغاه</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="filter-customer">العميل</label>
                    <select id="filter-customer" class="form-control select2" data-placeholder="اختر العميل">
                        <option value=""></option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="filter-attorney">الوكيل</label>
                    <select id="filter-attorney" class="form-control select2" data-placeholder="اختر الوكيل">
                        <option value=""></option>
                        @foreach ($attorneys as $attorney)
                            <option value="{{ $attorney->id }}">{{ $attorney->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <hr class="mt-10">


            <table id="power-attorney-table" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th></th>
                        <th class="custom-checkbox">
                            <input type="checkbox" id="select-all">
                        </th>
                        <th>اسم الوكالة</th>
                        <th>رقم الوكالة</th>
                        <th>العملاء</th> <!-- Updated -->
                        <th>الوكلاء</th> <!-- Updated -->
                        <th>تاريخ الإصدار</th>
                        <th>تاريخ الانتهاء</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- سيتم تعبئة الجدول بواسطة DataTables -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- مودال تغيير الحالة -->
    <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true"
        style="z-index: 9999">
        <div class="modal-dialog">
            <form id="status-form" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">تغيير حالة الوكالة</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="project_id" id="modal_project_id">
                        <div class="mb-3">
                            <label for="modal_status" class="form-label">اختر الحالة الجديدة</label>
                            <select name="status" id="modal_status" class="form-select">
                                <option value="active">سارية</option>
                                <option value="revoked">ملغاة</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                        <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <!-- مودال عرض العملاء -->
    <div class="modal fade" id="customersModal" tabindex="-1" aria-labelledby="customersModalLabel"
        style="z-index: 9999" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">قائمة العملاء</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                    <ul id="customersList" class="list-group">
                        <!-- سيتم ملؤها بواسطة JavaScript -->
                    </ul>
                </div>

            </div>
        </div>
    </div>

    <!-- مودال عرض الوكلاء -->
    <div class="modal fade" id="agentsModal" tabindex="-1" aria-labelledby="agentsModalLabel" aria-hidden="true"
        style="z-index: 9999">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">قائمة الوكلاء</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                    <ul id="agentsList" class="list-group">
                        <!-- سيتم ملؤها بواسطة JavaScript -->
                    </ul>
                </div>

            </div>
        </div>
    </div>

    <script>
        function expiredStatus(id) {
            Swal.fire({
                title: 'تعديل الحالة غير مسموح',
                text: "لا يمكن تعديل حالة الوكالة لأنها منتهية الصلاحية.",
                icon: 'warning',
                showCancelButton: false, // يعرض زر الإلغاء
                showConfirmButton: false, // يعرض زر التأكيد
                showDenyButton: false, // لا يعرض زر الرفض
                buttonsStyling: false, // لتعطيل التنسيق الافتراضي للأزرار
                customClass: {
                    popup: 'custom-popup', // تخصيص شكل النافذة
                    title: 'custom-title', // تخصيص شكل العنوان
                    text: 'custom-text', // تخصيص شكل النص
                    cancelButton: 'btn btn-danger custom-cancel' // تخصيص زر الإلغاء
                },
                cancelButtonText: 'حسنا',
                reverseButtons: false, // لعكس ترتيب الأزرار إذا رغبت
            });
        }


        $(document).ready(function() {


        });
    </script>
@endsection --}}




@extends('layouts.layoutMaster')

@section('title', ' الوكالات')

@section('breadcrumb')
    <li><a href="#">الشؤون القانونية</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> الوكالات</a>
        <i class="ti ti-star favorite-icon" data-page-name="الوكالات" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/css/components/icons.css', 'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/js/custom/select-all.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
    {!! $dataTable->scripts() !!}
@endsection

@section('page-script')
    @vite(['resources/assets/js/legal-affairs/power-attorney/power-attorney.js'])
@endsection


@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('page-style')
    @vite(['resources/css/dataTable.css'])
@endsection


@section('content')
    <div class="row g-4 mb-4">
        <!-- إجمالي الخصوم -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">الوكالات</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $totalPowerAttorney }}</h4>
                            </div>
                            <small class="mb-0">إجمالي الوكالات</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="ti ti-file-certificate ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">الوكالات النشطة</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $activePowerAttorney }}</h4>
                            </div>
                            <small class="mb-0">إجمالي الوكالات النشطة</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="ti ti-circle-check ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">الوكالات الملغية</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $revokedPowerAttorney }}</h4>
                            </div>
                            <small class="mb-0">إجمالي الوكالات الملغية</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="ti ti-circle-x ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">الوكالات المنتهية</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $expiredPowerAttorney }}</h4>
                            </div>
                            <small class="mb-0">إجمالي الوكالات المنتهية</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-danger">
                                <i class="ti ti-clock-x ti-26px"></i>
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
                    <label for="filter-status">الحالة </label>
                    <select id="filter-status" class="form-control select2" data-placeholder="اختر الحالة ">
                        <option value=""></option>
                        @foreach ($PowerAttorneyStatus as $type)
                            <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="filter-customer">العميل</label>
                    <select id="filter-customer" class="form-control select2" data-placeholder="اختر العميل">
                        <option value=""></option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="filter-employee">الوكيل</label>
                    <select id="filter-employee" class="form-control select2" data-placeholder="اختر الوكيل">
                        <option value=""></option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <hr class="mt-10">
            {!! $dataTable->table(['class' => 'table table-striped table-bordered w-100'], true) !!}
        </div>
    </div>


    <!-- مودال عرض العملاء -->
    <div class="modal fade" id="customersModal" tabindex="-1" aria-labelledby="customersModalLabel" style="z-index: 9999"
        aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">قائمة العملاء</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                    <ul id="customersList" class="list-group">
                        <!-- سيتم ملؤها بواسطة JavaScript -->
                    </ul>
                </div>

            </div>
        </div>
    </div>

    <!-- مودال عرض الوكلاء -->
    <div class="modal fade" id="agentsModal" tabindex="-1" aria-labelledby="agentsModalLabel" aria-hidden="true"
        style="z-index: 9999">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">قائمة الوكلاء</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                    <ul id="agentsList" class="list-group">
                        <!-- سيتم ملؤها بواسطة JavaScript -->
                    </ul>
                </div>

            </div>
        </div>
    </div>


    <script>
        // $(document).on('click', '.change-status', function(e) {
        //     e.preventDefault();
        //     var powerAttorneyId = $(this).data('id');
        //     var currentStatus = $(this).data('status');

        //     $('#modal_project_id').val(powerAttorneyId);
        //     $('#modal_status').val(currentStatus);
        //     $('#statusModal').modal('show');

        //     // تحديث مسار النموذج
        //     $('#status-form').attr('action', 'legal-affairs/power-attorney/' + powerAttorneyId + '/update-status');
        // });

        $(document).on('click', '.status-toggle', function() {
            var badge = $(this);
            var id = badge.data('id');

            $.ajax({
                url: "{{ route($route . '.update-status', ':id') }}".replace(':id', id),
                method: 'put',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        if (response.status === 'active') {
                            badge.removeClass('bg-danger').addClass('bg-success').text('نشط');
                        } else if (response.status === 'revoked') {
                            badge.removeClass('bg-success').addClass('bg-danger').text('ملغي');
                        }
                        toastr.success('تم تغيير الحالة بنجاح.');
                    } else {
                        toastr.error('فشل في تغيير الحالة.');
                    }
                },
                error: function(xhr) {
                    toastr.error('حدث خطأ أثناء تغيير الحالة.');
                }
            });
        });

        $(document).on('click', '.view-customers', function() {
            var customers = $(this).data('customers');
            var list = $('#customersList');
            list.empty();

            if (customers) {
                var customerArray = customers.split(', ');
                customerArray.forEach(function(customer) {
                    list.append(
                        '<li class="list-group-item"><i class="ti ti-rosette-discount-check-filled text-success mx-1" title="عميل"></i>' +
                        customer + '</li>');
                });
            } else {
                list.append('<li class="list-group-item">لا يوجد عملاء</li>');
            }

            var customersModal = new bootstrap.Modal(document.getElementById('customersModal'));
            customersModal.show();
        });

        $(document).on('click', '.view-agents', function() {
            var agents = $(this).data('agents');
            var list = $('#agentsList');
            list.empty();

            if (agents) {
                var agentArray = agents.split(', ');
                agentArray.forEach(function(agent) {
                    list.append('<li class="list-group-item">' + agent + '</li>');
                });
            } else {
                list.append('<li class="list-group-item">لا يوجد وكلاء</li>');
            }

            var agentsModal = new bootstrap.Modal(document.getElementById('agentsModal'));
            agentsModal.show();
        });
    </script>
@endsection
