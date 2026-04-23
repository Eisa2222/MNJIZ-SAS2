{{-- @extends('layouts.layoutMaster')

@section('title', 'الدعاوى')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">الدعاوى</a>
        <i class="ti ti-star favorite-icon" data-page-name="الدعاوى" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
    <link href="{{ asset('assets/hijri-date/css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/hijri-date/css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet" />
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
                dir: 'rtl',

            });

            var table = $('#setting-table').DataTable({

                // كود تحريك النص
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
                // كود تحريك النص


                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route($route . '.index') }}",
                    data: function(d) {
                        d.project = $('#filter-project').val();
                        d.department = $('#filter-department').val();
                        d.court = $('#filter-court').val();
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
                        render: function(data, type, full, meta) {
                            return '<input type="checkbox" class="row-checkbox" value="' + full.id +
                                '">';
                        }
                    },
                    {
                        data: 'name',
                        name: 'name',
                        className: 'table-ellipsis',
                    },
                    {
                        data: 'lawsuit_number',
                        name: 'lawsuit_number',

                    },
                    //  {
                    //     data: 'project_id',
                    //     name: 'project_id',
                    //     className: 'table-ellipsis',


                    // },
                    {
                        data: 'plaintiff_id',
                        name: 'plaintiff_id',
                        orderable: false,
                        className: 'nowrap',

                    },
                    {
                        data: 'defendant_id',
                        name: 'defendant_id',
                        orderable: false,
                        className: 'nowrap',

                    },
                    {
                        data: 'lawsuit_type_id',
                        name: 'lawsuit_type_id',
                        className: 'table-ellipsis',

                    },

                    {
                        data: 'lawsuit_status',
                        name: 'lawsuit_status'
                    },
                    // {
                    //     data: 'created_at',
                    //     name: 'created_at'
                    // },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        visible: function() {
                            return {!! auth()->user()->can('تعديل دعوى') || auth()->user()->can('حذف دعوى') ? 'true' : 'false' !!};
                        }()
                    }, {
                        data: 'id',
                        name: 'id',
                        visible: false
                    }
                ],
                order: [
                    [9, 'desc']
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
                        className: 'btn btn-export btn',
                        text: 'الإجراءات',
                        buttons: [{
                                extend: 'copy',
                                text: 'نسخ'
                            }, {
                                extend: 'excel',
                                text: 'إكسل'
                            },
                            @if (auth()->user()->can('حذف دعوى'))
                                {
                                    text: 'حذف المحدد',
                                    className: 'btn btn-default btn-delete-selected',
                                    action: function(e, dt, node, config) {
                                        var selectedIds = [];
                                        $('.row-checkbox:checked').each(function() {
                                            selectedIds.push($(this).val());
                                        });

                                        confirmDeleteSelectedmss(selectedIds,
                                            "{{ route('mass.delete') }}", 'Lawsuit'
                                        );
                                    }
                                }
                            @endif

                        ]
                    },
                    @can('إضافة دعوى')
                        {
                            text: '<i class="fas fa-plus-circle me-1"></i> إضافة دعوى جديدة ',
                            className: 'btn btn-primary btn-add',
                            action: function(e, dt, node, config) {
                                window.location.href = "{{ route($route . '.create') }}";
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
            $('#filter-project, #filter-department, #filter-court').change(function() {
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

        });
    </script>


@endsection
<!-- Vendor Scripts -->

@section('content')
    <div class="row g-4 mb-4">

        <!-- إجمالي الدعاوى -->
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">إجمالي الدعاوى</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $totalLawsuits }}</h4>
                            </div>
                            <small class="mb-0">إجمالي الدعاوى</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-info">
                                <!-- أيقونة إجمالي الدعاوى -->
                                <i class="ti ti-gavel ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- دعاوى المحاكم -->
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">محكمة الاحوال الشخصية</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $personal_status }}</h4>
                            </div>
                            <small class="mb-0"> محكمة الاحوال الشخصية</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="ti ti-gavel ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- دعاوى جهات الضبط -->
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">محكمة التنفيذ</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $execution_court }}</h4>
                            </div>
                            <small class="mb-0"> محكمة التنفيذ </small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-secondary">
                                <i class="ti ti-gavel ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- دعاوى الجهات الإدارية -->
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">المحكمة العليا</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $high_court }}</h4>
                            </div>
                            <small class="mb-0"> المحكمة العليا</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-danger">
                                <i class="ti ti-gavel ti-26px"></i>
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
                    <label for="filter-project">المشروع </label>
                    <select id="filter-project" class="form-control select2" data-placeholder="اختر المشروع ">
                        <option value=""></option>
                        @foreach (\App\Models\judicial_affairs\Project::select(['id', 'project_name'])->get() as $stage)
                            <option value="{{ $stage->id }}">{{ $stage->project_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="filter-department">نوع الدعوى</label>
                    <select id="filter-department" class="form-control select2" data-placeholder="اختر نوع الدعوى">
                        <option value=""></option>
                        @foreach (\App\Models\general_setting\SettingsLawsuitsType::all() as $setting)
                            <option value="{{ $setting->id }}">{{ $setting->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="filter-court"> المحكمة</label>
                    <select id="filter-court" class="form-control select2" data-placeholder="اختر   المحكمة">
                        <option value=""></option>
                        @foreach (\App\Models\general_setting\SettingsMainCourt::all() as $setting)
                            <option value="{{ $setting->id }}">{{ $setting->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <hr class="mt-10">

            <table id="setting-table" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th></th>
                        <th class="custom-checkbox">
                            <input type="checkbox" id="select-all">
                        </th>
                        <th>اسم الدعوى</th>
                        <th>رقم الدعوى</th>
                        <th> المدعى </th>
                        <th> المدعى عليه </th>
                        <th> نوع الدعوى </th>
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


    <!-- مودال عرض المدعين -->
    <div class="modal fade" id="plaintiffsModal" tabindex="-1" aria-labelledby="plaintiffsModalLabel" aria-hidden="true"
        style="z-index: 9999">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="plaintiffsModalLabel">المدعون</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-striped table-bordered">
                        <tbody id="plaintiffsModalBody">
                            <!-- سيتم تعبئة أسماء المدعين مع الأيقونات عبر جافاسكربت -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- مودال عرض المدعى عليهم -->
    <div class="modal fade" id="defendantsModal" tabindex="-1" aria-labelledby="defendantsModalLabel"
        aria-hidden="true" style="z-index: 9999">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="defendantsModalLabel">المدعى عليهم</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-striped table-bordered">
                        <tbody id="defendantsModalBody">
                            <!-- سيتم تعبئة أسماء المدعى عليهم مع الأيقونات عبر جافاسكربت -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    <script>
        function showPlaintiffs(button) {
            // جلب قيمة الخاصية data-plaintiffs (وهي مصفوفة JSON)
            var data = button.getAttribute('data-plaintiffs');
            // تحويل النص JSON إلى كائن جافاسكربت
            var plaintiffs = JSON.parse(data);

            var plaintiffsModalBody = document.getElementById('plaintiffsModalBody');
            // إفراغ محتوى الـtbody قبل تعبئته
            plaintiffsModalBody.innerHTML = '';

            // تكرار على عناصر المصفوفة وإضافة صف لكل مدعي
            plaintiffs.forEach(function(plaintiff) {
                var row = document.createElement('tr');
                var cell = document.createElement('td');

                // بناء الـHTML حسب نوع plaintiff.type
                var iconHtml = '';
                if (plaintiff.type === 'customer') {
                    iconHtml = "<i class='ti ti-rosette-discount-check-filled text-success' title='عميل'></i>";
                } else {
                    iconHtml = "<i class='ti ti-rosette-discount-check-filled text-danger' title='خصم'></i>";
                }

                // دمج الأيقونة واسم المدعي في خلية واحدة
                cell.innerHTML = iconHtml + ' ' + plaintiff.name;
                row.appendChild(cell);
                plaintiffsModalBody.appendChild(row);
            });

            // إظهار المودال
            var plaintiffsModal = new bootstrap.Modal(document.getElementById('plaintiffsModal'));
            plaintiffsModal.show();
        }

        function showDefendants(button) {
            // نفس المنطق ولكن مع data-defendants
            var data = button.getAttribute('data-defendants');
            var defendants = JSON.parse(data);

            var defendantsModalBody = document.getElementById('defendantsModalBody');
            defendantsModalBody.innerHTML = '';

            defendants.forEach(function(defendant) {
                var row = document.createElement('tr');
                var cell = document.createElement('td');

                var iconHtml = '';
                if (defendant.type === 'customer') {
                    iconHtml = "<i class='ti ti-rosette-discount-check-filled text-success' title='عميل'></i>";
                } else {
                    iconHtml = "<i class='ti ti-rosette-discount-check-filled text-danger' title='خصم'></i>";
                }

                cell.innerHTML = iconHtml + ' ' + defendant.name;
                row.appendChild(cell);
                defendantsModalBody.appendChild(row);
            });

            var defendantsModal = new bootstrap.Modal(document.getElementById('defendantsModal'));
            defendantsModal.show();
        }
    </script>


    <script>
        $(document).on('click', '.status-toggle', function() {
            var badge = $(this);
            var id = badge.data('id');

            // رسالة التأكيد باستخدام SweetAlert
            Swal.fire({
                html: `
                <span>
                    هل تريد تغيير حالة هذه الدعوى؟
                </span>
            `,
                icon: 'warning',
                showCancelButton: true, // يعرض زر الإلغاء
                showConfirmButton: true, // يعرض زر التأكيد
                showDenyButton: false, // لا يعرض زر الرفض
                buttonsStyling: false, // لتعطيل التنسيق الافتراضي للأزرار
                customClass: {
                    popup: 'custom-popup', // تخصيص شكل النافذة
                    title: 'custom-title', // تخصيص شكل العنوان
                    text: 'custom-text', // تخصيص شكل النص
                    confirmButton: 'btn btn-success custom-confirm', // تخصيص زر التأكيد
                    cancelButton: 'btn btn-danger custom-cancel' // تخصيص زر الإلغاء
                },
                confirmButtonText: 'تأكيد',
                cancelButtonText: 'إلغاء',
                reverseButtons: false, // لعكس ترتيب الأزرار إذا رغبت

            }).then((result) => {
                if (result.isConfirmed) {
                    // إذا تم تأكيد العملية، قم بإجراء الطلب
                    $.ajax({
                        url: "{{ route('lawsuits.toggleStatus', ':id') }}".replace(':id', id),
                        method: 'get',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                if (response.status === 'active') {
                                    // إزالة فئات الأزرار القديمة وإضافة الفئة الجديدة
                                    badge.removeClass('bg-danger bg-primary').addClass(
                                        'bg-success').text('نشط');
                                } else if (response.status === 'inactive') {
                                    badge.removeClass('bg-success bg-primary').addClass(
                                        'bg-danger').text('مغلق');
                                }
                                // تحديث الجدول أو أي عناصر أخرى إذا لزم الأمر

                                toastr.success('تم تغيير الحالة بنجاح.');
                            } else {
                                toastr.error('فشل في تغيير الحالة.');
                            }
                        },
                        error: function(xhr) {
                            toastr.error('حدث خطأ أثناء تغيير الحالة.');
                        }
                    });
                }
            });
        });
    </script>

@endsection --}}



@extends('layouts.layoutMaster')

@section('title', ' الدعاوى')

@section('breadcrumb')
    <li><a href="#">الشؤون القانونية</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> الدعاوى</a>
        <i class="ti ti-star favorite-icon" data-page-name="الدعاوى" data-page-url="{{ url()->current() }}"
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
    @vite(['resources/assets/js/legal-affairs/lawsuits/lawsuits.js'])
@endsection


@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('page-style')
    @vite(['resources/css/dataTable.css'])
@endsection


@section('content')
    <div class="row g-4 mb-4">
        <!-- إجمالي الدعاوى -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">الدعاوى</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $totalLawsuits }}</h4>
                            </div>
                            <small class="mb-0">إجمالي الدعاوى</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="ti ti-gavel ti-26px"></i>
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
                            <span class="text-heading">الدعاوى النشطة</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $activeLawsuits }}</h4>
                            </div>
                            <small class="mb-0">إجمالي الدعاوى النشطة</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="ti ti-checks ti-26px"></i>
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
                            <span class="text-heading">الدعاوى المغلقة</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $inActiveLawsuits }}</h4>
                            </div>
                            <small class="mb-0">إجمالي الدعاوى المغلقة</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-secondary">
                                <i class="ti ti-clock-pause ti-26px"></i>
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
                            <span class="text-heading">الجلسات النشطة</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $sessions }}</h4>
                            </div>
                            <small class="mb-0">إجمالي الجلسات النشطة </small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="ti ti-calendar-event ti-26px"></i>
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
                    <label for="filter-project">المشروع </label>
                    <select id="filter-project" class="form-control select2" data-placeholder="اختر المشروع ">
                        <option value=""></option>
                        @foreach ($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->project_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="filter-type">نوع الدعوى</label>
                    <select id="filter-type" class="form-control select2" data-placeholder="اختر نوع الدعوى">
                        <option value=""></option>
                        @foreach ($lawsuitsType as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="filter-court"> المحكمة</label>
                    <select id="filter-court" class="form-control select2" data-placeholder="اختر   المحكمة">
                        <option value=""></option>
                        @foreach ($mainCourt as $court)
                            <option value="{{ $court->id }}">{{ $court->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <hr class="mt-10">
            {!! $dataTable->table(['class' => 'table table-striped table-bordered w-100'], true) !!}
        </div>
    </div>

    <!-- مودال عرض المدعين -->
    <div class="modal fade" id="plaintiffsModal" tabindex="-1" aria-labelledby="plaintiffsModalLabel" aria-hidden="true"
        style="z-index: 9999">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="plaintiffsModalLabel">المدعون</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-striped table-bordered">
                        <tbody id="plaintiffsModalBody">
                            <!-- سيتم تعبئة أسماء المدعين مع الأيقونات عبر جافاسكربت -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- مودال عرض المدعى عليهم -->
    <div class="modal fade" id="defendantsModal" tabindex="-1" aria-labelledby="defendantsModalLabel"
        aria-hidden="true" style="z-index: 9999">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="defendantsModalLabel">المدعى عليهم</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-striped table-bordered">
                        <tbody id="defendantsModalBody">
                            <!-- سيتم تعبئة أسماء المدعى عليهم مع الأيقونات عبر جافاسكربت -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    <script>
        function showPlaintiffs(button) {
            // جلب قيمة الخاصية data-plaintiffs (وهي مصفوفة JSON)
            var data = button.getAttribute('data-plaintiffs');
            // تحويل النص JSON إلى كائن جافاسكربت
            var plaintiffs = JSON.parse(data);

            var plaintiffsModalBody = document.getElementById('plaintiffsModalBody');
            // إفراغ محتوى الـtbody قبل تعبئته
            plaintiffsModalBody.innerHTML = '';

            // تكرار على عناصر المصفوفة وإضافة صف لكل مدعي
            plaintiffs.forEach(function(plaintiff) {
                var row = document.createElement('tr');
                var cell = document.createElement('td');

                // بناء الـHTML حسب نوع plaintiff.type
                var iconHtml = '';
                if (plaintiff.type === 'customer') {
                    iconHtml = "<i class='ti ti-rosette-discount-check-filled text-success' title='عميل'></i>";
                } else {
                    iconHtml = "<i class='ti ti-rosette-discount-check-filled text-danger' title='خصم'></i>";
                }

                // دمج الأيقونة واسم المدعي في خلية واحدة
                cell.innerHTML = iconHtml + ' ' + plaintiff.name;
                row.appendChild(cell);
                plaintiffsModalBody.appendChild(row);
            });

            // إظهار المودال
            var plaintiffsModal = new bootstrap.Modal(document.getElementById('plaintiffsModal'));
            plaintiffsModal.show();
        }

        function showDefendants(button) {
            // نفس المنطق ولكن مع data-defendants
            var data = button.getAttribute('data-defendants');
            var defendants = JSON.parse(data);

            var defendantsModalBody = document.getElementById('defendantsModalBody');
            defendantsModalBody.innerHTML = '';

            defendants.forEach(function(defendant) {
                var row = document.createElement('tr');
                var cell = document.createElement('td');

                var iconHtml = '';
                if (defendant.type === 'customer') {
                    iconHtml = "<i class='ti ti-rosette-discount-check-filled text-success' title='عميل'></i>";
                } else {
                    iconHtml = "<i class='ti ti-rosette-discount-check-filled text-danger' title='خصم'></i>";
                }

                cell.innerHTML = iconHtml + ' ' + defendant.name;
                row.appendChild(cell);
                defendantsModalBody.appendChild(row);
            });

            var defendantsModal = new bootstrap.Modal(document.getElementById('defendantsModal'));
            defendantsModal.show();
        }
    </script>


    <script>
        $(document).on('click', '.status-toggle', function() {
            var badge = $(this);
            var id = badge.data('id');

            var currentStatus = badge.text().trim(); // أو يمكن استخدام data attribute للحالة

            // تحديد النص حسب الحالة
            var alertText = '';
            if (currentStatus === 'نشط' || badge.hasClass('bg-success')) {
                alertText = 'سيتم ارسال الاستبيان للعميل بعد اغلاق الدعوى';
            }

            Swal.fire({
                title: 'هل تريد تغيير حالة هذه الدعوى؟',
                text: alertText,
                icon: 'warning',
                showCancelButton: true,
                showConfirmButton: true,
                showDenyButton: false, // لا يعرض زر الرفض
                buttonsStyling: false, // لتعطيل التنسيق الافتراضي للأزرار
                customClass: {
                    popup: 'custom-popup', // تخصيص شكل النافذة
                    title: 'custom-title', // تخصيص شكل العنوان
                    text: 'custom-text', // تخصيص شكل النص
                    confirmButton: 'btn btn-success custom-confirm', // تخصيص زر التأكيد
                    cancelButton: 'btn btn-danger custom-cancel' // تخصيص زر الإلغاء
                },
                confirmButtonText: 'تأكيد',
                cancelButtonText: 'إلغاء',
                reverseButtons: false,

            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('legal-affairs.lawsuits.toggleStatus', ':id') }}".replace(
                            ':id', id),
                        method: 'get',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                if (response.status === 'active') {
                                    // إزالة فئات الأزرار القديمة وإضافة الفئة الجديدة
                                    badge.removeClass('bg-danger bg-primary').addClass(
                                        'bg-success').text('نشط');
                                } else if (response.status === 'inactive') {
                                    badge.removeClass('bg-success bg-primary').addClass(
                                        'bg-danger').text('مغلق');
                                }
                                // تحديث الجدول أو أي عناصر أخرى إذا لزم الأمر

                                toastr.success('تم تغيير الحالة بنجاح.');
                            } else {
                                toastr.error('فشل في تغيير الحالة.');
                            }
                        },
                        error: function(xhr) {
                            let errorMessage = 'حدث خطأ غير متوقع';

                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            } else if (xhr.responseText) {
                                try {
                                    const response = JSON.parse(xhr.responseText);
                                    errorMessage = response.message || errorMessage;
                                } catch (e) {
                                    errorMessage = xhr.responseText;
                                }
                            }

                            toastr.error(errorMessage);
                        }
                    });
                }
            });
        });
    </script>
@endsection
