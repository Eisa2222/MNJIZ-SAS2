@extends('layouts.layoutMaster')

@section('title', 'الدعاوى')

@section('breadcrumb')

    <li><a href="#">الدعاوى</a>
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
                    url: "{{ route($route . '.approvals') }}",
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
                    }, {
                        data: 'name',
                        name: 'name',
                        className: 'text-nowrap',



                    }, {
                        data: 'lawsuit_number',
                        name: 'lawsuit_number',
                        className: 'table-ellipsis',

                    }, {
                        data: 'project_id',
                        name: 'project_id',
                        className: 'text-nowrap',


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
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
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
                        className: 'btn btn-export btn',
                        text: 'الإجراءات',
                        buttons: [{
                                extend: 'copy',
                                text: 'نسخ'
                            }, {
                                extend: 'excel',
                                text: 'إكسل'
                            }, {
                                text: 'حذف المحدد',
                                className: 'btn btn-default btn-delete-selected',
                                action: function(e, dt, node, config) {
                                    var selectedIds = [];
                                    $('.row-checkbox:checked').each(function() {
                                        selectedIds.push($(this).val());
                                    });

                                    confirmDeleteSelectedmss(selectedIds,
                                        "{{ route('mass.delete') }}", 'Lawsuit'
                                    ); // هنا نرسل اسم الموديل
                                }
                            }

                        ]
                    }

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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.1/moment.min.js"></script>
    <script>
        window.moment = moment;

        function loadHijriDatePicker() {
            // تحميل مكتبة التقويم الهجري بعد التأكد من تحميل مكتبة moment.js
            if (typeof window.moment === 'undefined') {
                console.error('Moment.js is not loaded. Please check the script path.');
                return;
            }

            var script = document.createElement('script');
            script.src = "{{ asset('assets/hijri-date/js/bootstrap-hijri-datetimepickermin.js') }}";
            document.head.appendChild(script);

            // بعد تحميل مكتبة التقويم الهجري، تهيئة التقويم
            script.onload = function() {
                initializeHijriPicker(); // استدعاء تهيئة التقويم
            };
            script.onerror = function() {
                console.error('Failed to load Hijri Datepicker library.');
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
                    minDate: moment().startOf('day'), // منع اختيار تواريخ قديمة قبل اليوم
                    // format: 'iYYYY-iMM-iDD'
                });
            });
        }

        window.addEventListener('DOMContentLoaded', loadHijriDatePicker);
    </script>

@endsection
<!-- Vendor Scripts -->

@section('content')
    {{-- <div class="row g-4 mb-4">

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

    </div> --}}

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
                        <th>المشروع</th>
                        {{-- <th> المدعى </th> --}}
                        {{-- <th> المدعى عليه </th> --}}
                        <th> نوع الدعوى </th>
                        <th>الحالة</th>
                        <th>الإجراء</th>

                    </tr>
                </thead>
                <tbody>
                    <!-- سيتم تعبئة الجدول بواسطة DataTables -->
                </tbody>
            </table>
        </div>
    </div>
    {{-- لتغيير الحالة --}}
    <script>
        $(document).on('click', '.status-toggle', function() {
            var badge = $(this);
            var id = badge.data('id');

            // رسالة التأكيد باستخدام SweetAlert
            Swal.fire({
                html: `
                    <span>
                        هل تريد تغيير حالة هذه الدعوى؟
                        <i class="fas fa-exclamation-circle" data-bs-toggle="tooltip" title="ستُغيَّر حالة المشروع الخاص بهذه الدعوى إلى مكتمل إذا أُغلِقت جميع الدعاوى المرتبطة به، وإلى جاري إذا تم تحويل حالة الدعوى إلى نشطة"></i>
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
                                    badge.removeClass('bg-danger').addClass('bg-success').text(
                                        'نشط');
                                } else if (response.status === 'inactive') {
                                    badge.removeClass('bg-success').addClass('bg-danger').text(
                                        'مغلق');
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
                }
            });
        });

        document.addEventListener('DOMContentLoaded', () => {

            const offcanvas = document.getElementById('taskOffcanvas');
            const form = document.getElementById('taskForm');
            const taskNameInput = document.getElementById('taskName');

            // إعادة تعيين الحقول عند فتح الشاشة الجانبية
            offcanvas.addEventListener('show.bs.offcanvas', (event) => {
                const button = event.relatedTarget; // الزر الذي فتح الشاشة الجانبية
                const taskName = button.getAttribute('data-task-name');
                // تحديد ما إذا كانت المهمة جديدة أم لا

                // تفريغ الحقول وإعادة القيم الافتراضية
                // تفريغ الحقول وإعادة القيم الافتراضية
                form.reset(); // يعيد تعيين القيم الافتراضية
                $('.select2').val(null).trigger('change'); // Set value to null and trigger change event

                taskNameInput.value = taskName; // تعيين اسم المهمة


                if (taskName === 'مهمة جديدة') {
                    taskNameInput.removeAttribute('disabled'); // جعل الحقل قابلًا للتعديل
                } else {
                    taskNameInput.setAttribute('readonly', true); // جعل الحقل غير قابل للتعديل
                }
            });

        });

        // Example starter JavaScript for disabling form submissions if there are invalid fields
        (function() {
            'use strict'

            // Fetch all the forms we want to apply custom Bootstrap validation styles to
            var forms = document.querySelectorAll('.needs-validation')

            // Loop over them and prevent submission
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
