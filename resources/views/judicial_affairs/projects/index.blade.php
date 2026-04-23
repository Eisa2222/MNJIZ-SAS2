@extends('layouts.layoutMaster')

@section('title', 'قائمة المشاريع')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> المشاريع</a>
        <i class="ti ti-star favorite-icon" data-page-name="المشاريع" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/shepherd/shepherd.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/shepherd/shepherd.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
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
    @vite('resources/assets/js/tour_project_index.js')
    <script>
        $(document).ready(function() {
            $('.select2').each(function() {
                $(this).select2({
                    placeholder: function() {
                        return $(this).data('placeholder');
                    },
                    allowClear: true,
                    width: '100%',
                    language: 'ar',
                    dir: 'rtl',
                    dropdownParent: $(this).closest(
                        '#statusModal, #card-project')
                });
            });

            var table = $('#projects-table').DataTable({
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
                    url: "{{ route('projects.index') }}",
                    data: function(d) {
                        d.new_status_id = $('#filter-status').val();
                        d.contract_id = $('#filter-contract_id').val();
                        d.employee = $('#filter-employee').val();
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
                        data: 'project_number',
                        name: 'project_number',
                        width: '250px'
                    },
                    {
                        data: 'project_name',
                        name: 'project_name',
                        width: '250px'
                    },
                    {
                        data: 'manager_user_id',
                        name: 'manager_user_id',
                        width: '250px'
                    },
                    {
                        data: 'start_date',
                        name: 'start_date',
                        width: '150px'
                    },
                    {
                        data: 'new_status_id',
                        name: 'new_status_id',
                        width: '150px'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        width: '150px'

                    },
                    {
                        data: 'id',
                        name: 'id',
                        visible: false
                    }
                ],
                order: [
                    [8, 'desc']
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
                            }, {
                                extend: 'excel',
                                text: 'إكسل'
                            },
                            @if (auth()->user()->can('حذف مشروع'))
                                {
                                    text: 'حذف المحدد',
                                    className: 'btn btn-default btn-delete-selected',
                                    action: function(e, dt, node, config) {
                                        var selectedIds = [];
                                        $('.row-checkbox:checked').each(function() {
                                            selectedIds.push($(this).val());
                                        });

                                        confirmDeleteSelectedmss(selectedIds,
                                            "{{ route('mass.delete') }}", 'Project'
                                        );
                                    }
                                }
                            @endif

                        ]
                    },

                    @can('إضافة مشروع')
                        {
                            text: '<i class="fas fa-plus-circle me-1"></i> إضافة مشروع جديد',
                            className: 'btn btn-primary btn-add',
                            action: function(e, dt, node, config) {
                                window.location.href = "{{ route('projects.create') }}";
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
            $('#filter-status, #filter-contract_id, #filter-employee').change(function() {
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

            $(document).on('click', '.change-status', function(e) {
                e.preventDefault();
                var projectId = $(this).data('id');
                var currentStatus = $(this).data('status');

                // تعيين معرف المشروع في الحقل المخفي
                $('#modal_project_id').val(projectId);

                // تعيين الحالة الحالية في Select2
                $('#modal_status').val(currentStatus).trigger('change');

                // عرض المودال
                $('#statusModal').modal('show');

                // // تحديث مسار النموذج
                // $('#status-form').attr('action', '/projects/' + projectId + '/update-status');
            });


            // التحقق عند الضغط على زر تغيير الحالة
            $('#status-form').on('submit', function(e) {
                e.preventDefault();

                var selectedStatus = $('#modal_status').val();
                var projectId = $('#modal_project_id').val();

                if (!selectedStatus) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'تحذير',
                        text: 'يرجى اختيار حالة المشروع قبل المتابعة.',
                        confirmButtonText: 'موافق'
                    });
                    return;
                }

                if (selectedStatus == '5') {
                    checkActiveCases(projectId, function(hasActiveCases) {
                        $('#statusModal').modal('hide');
                        if (hasActiveCases) {
                            Swal.fire({
                                icon: 'error',
                                title: 'لا يمكن تغيير الحالة',
                                text: 'لا يمكن إلغاء أو إغلاق المشروع لوجود دعاوى نشطة داخله.',
                                allowOutsideClick: true,
                                showConfirmButton: false,
                                showCancelButton: false,
                                customClass: {
                                    popup: 'custom-popup',
                                    title: 'custom-title',
                                    text: 'custom-text',
                                },
                            });
                        } else {
                            updateProjectStatus(projectId, selectedStatus);
                        }
                    });
                } else {
                    updateProjectStatus(projectId, selectedStatus);
                }
            });

            // دالة تحديث الحالة باستخدام AJAX
            function updateProjectStatus(projectId, statusId) {
                $.ajax({
                    url: '/employees/projects/' + projectId + '/update-status',
                    method: 'POST',
                    data: {
                        '_token': $('meta[name="csrf-token"]').attr('content'),
                        '_method': 'PUT',
                        'new_status_id': statusId
                    },
                    success: function(response) {
                        $('#statusModal').modal('hide');

                        // تحديث صف المشروع في الجدول فقط بدون إعادة تحميل الجدول بالكامل
                        var table = $('#projects-table').DataTable();
                        table.ajax.reload(null,
                            false); // الوسيط الثاني (false) يحافظ على الصفحة الحالية

                        // عرض رسالة نجاح
                        toastr.success(response.message || 'تم تحديث حالة المشروع بنجاح');
                    },
                    error: function(xhr) {
                        $('#statusModal').modal('hide');
                        Swal.fire({
                            icon: 'error',
                            title: 'خطأ',
                            text: xhr.responseJSON?.message ||
                                'حدث خطأ أثناء تحديث حالة المشروع.',
                            confirmButtonText: 'موافق'
                        });
                    }
                });
            }

            function checkActiveCases(projectId, callback) {
                $.ajax({
                    url: '/employees/projects/' + projectId + '/check-active-cases',
                    method: 'GET',
                    success: function(response) {
                        callback(response.hasActiveCases);
                    },
                    error: function() {
                        $('#statusModal').modal('hide');
                        Swal.fire({
                            icon: 'error',
                            title: 'خطأ',
                            text: 'حدث خطأ أثناء التحقق من الدعاوى النشطة.',
                            confirmButtonText: 'موافق'
                        });
                    }
                });
            }

        });
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.1/moment.min.js"></script>
    <script>
        window.moment = moment;

        function loadHijriDatePicker() {
            var script = document.createElement('script');
            script.src = "{{ asset('assets/hijri-date/js/bootstrap-hijri-datetimepickermin.js') }}";
            document.head.appendChild(script);

            script.onload = function() {
                initializeHijriPicker();
            };

        }

        function initializeHijriPicker() {
            $(document).ready(function() {
                $(".hijri-picker").hijriDatePicker({
                    hijri: true,
                    showSwitcher: true,
                    useCurrent: false,
                    showClear: true,
                    showTodayButton: true,
                    showClose: true,
                    todayBtn: true,
                    todayHighlight: true,
                    minDate: moment().startOf('day'),
                });
            });
        }

        window.addEventListener('DOMContentLoaded', loadHijriDatePicker);
    </script>
@endsection

@section('content')
    <div id="top_card" class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">المشاريع</span>
                            <div class="d-flex align-items-center my-1">

                                <h4 class="mb-0 me-2">{{ $totalProjects }}</h4>
                            </div>
                            <small class="mb-0">إجمالي المشاريع</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="ti ti-briefcase ti-26px"></i>
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
                            <span class="text-heading">المشاريع الجديدة</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $new_projects }}</h4>
                            </div>
                            <small class="mb-0"> المشاريع الجديدة</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="ti ti-rocket ti-26px"></i>
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
                            <span class="text-heading">المشاريع في المسار</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $on_track_projects }}</h4>
                            </div>
                            <small class="mb-0"> المشاريع في المسار</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="ti ti-route ti-26px"></i>
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
                            <span class="text-heading">المشاريع المغلقة</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $closed_projects }}</h4>
                            </div>
                            <small class="mb-0"> المشاريع المغلقة</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-danger">
                                <i class="ti ti-archive ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- جدول المشاريع -->
    <div class="card" id="card-project">
        <div class="card-body">
            <div class="filters row g-3">
                <div class="col-md-4">
                    <label for="filter-status">حالة المشروع</label>
                    <select id="filter-status" class="form-control select2" data-placeholder="اختر حالة المشروع">
                        <option value=""></option>
                        @foreach ($contract_status as $contract)
                            <option value="{{ $contract->id }}">{{ $contract->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="filter-contract_id">العقد</label>
                    <select id="filter-contract_id" class="form-control select2" data-placeholder="اختر العقد">
                        <option value=""></option>
                        @foreach ($contracts as $contract)
                            <option value="{{ $contract->id }}">{{ $contract->contract_name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- مدير المشروع -->
                <div class="col-md-4">
                    <label for="filter-employee">مدير المشروع</label>
                    <select id="filter-employee" class="form-control select2" data-placeholder="اختر مدير المشروع">
                        <option value=""></option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->user_id }}">{{ $employee->name }}</option>
                        @endforeach

                    </select>
                </div>
            </div>
            <hr class="mt-10">
            <table id="projects-table" class="table table-striped table-bordered text-center">
                <thead>
                    <tr>
                        <th></th>
                        <th class="custom-checkbox">
                            <input type="checkbox" id="select-all">
                        </th>
                        <th>رقم المشروع</th>
                        <th>اسم المشروع</th>
                        <th>مدير المشروع</th>
                        <th>تاريخ البدء</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- سيتم تعبئة البيانات بواسطة DataTables -->
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true"
        style="z-index: 9999">
        <div class="modal-dialog">
            <form id="status-form" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">تغيير حالة المشروع</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="project_id" id="modal_project_id">
                        <div class="mb-3">
                            <label for="modal_status" class="form-label">اختر الحالة الجديدة</label>
                            <select name="new_status_id" data-placeholder="اختر حالة المشروع" required id="modal_status"
                                class="form-select select2">
                                <option value=""></option>
                                @foreach ($contract_status as $contract)
                                    <option value="{{ $contract->id }}">{{ $contract->name }}</option>
                                @endforeach
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

    <script>
        (function() {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function(form) {
                    form.addEventListener('submit', function(event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>

@endsection
