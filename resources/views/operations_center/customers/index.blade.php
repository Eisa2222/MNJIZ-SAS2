{{-- @extends('layouts.layoutMaster')

@section('title', 'قائمة العملاء')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> العملاء
        </a>
        <i class="ti ti-star favorite-icon" data-page-name="العملاء" data-page-url="{{ url()->current() }}"
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
            // تهيئة Select2 لحقول الفلترة
            $('.select2').select2({
                placeholder: function() {
                    return $(this).data('placeholder');
                },
                allowClear: true,
                width: '100%',
                language: 'ar',
                dir: 'rtl'
            });

            var table = $('#customers-table').DataTable({
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
                    url: "{{ route('customers.index') }}",
                    data: function(d) {
                        d.customer_type = $('#filter-customer-type').val();
                        d.marketing_channel = $('#filter-marketing_channel').val();
                        d.sector = $('#filter-sector').val();
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
                        render: function(data, type, row) {
                            // دمج النص مع الأيقونة
                            return '<i class="ti ti-rosette-discount-check-filled text-success" title="عميل"></i> ' +
                                data;
                        },
                        data: 'name',
                        name: 'name',
                        className: 'table-ellipsis text-right',
                    },
                    {
                        data: 'customer_type',
                        name: 'customer_type',
                        width: '10%',
                        render: function(data, type, row) {
                            return data === 'individual' ? '<span>فرد</span>' :
                                '<span>شخصية اعتبارية</span>';
                        }
                    }, {
                        data: 'relationship_manager',
                        name: 'relationship_manager',
                        className: 'text-nowrap',
                        className: 'table-ellipsis',
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        width: '10%'
                    },
                    {
                        data: 'added_by',
                        name: 'added_by',
                        className: 'table-ellipsis',
                    },
                    {
                        data: 'action',
                        name: 'action',
                        width: "5%",
                        orderable: false,
                        searchable: false,
                        visible: function() {
                            return {!! auth()->user()->can('تعديل عميل') || auth()->user()->can('حذف عميل') ? 'true' : 'false' !!};
                        }()
                    }
                ],
                order: [
                    [5, 'desc']
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
                            @if (auth()->user()->can('حذف عميل'))
                                {
                                    text: 'حذف المحدد',
                                    className: 'btn btn-default btn-delete-selected',
                                    action: function(e, dt, node, config) {
                                        var selectedIds = [];
                                        $('.row-checkbox:checked').each(function() {
                                            selectedIds.push($(this).val());
                                        });

                                        confirmDeleteSelectedmss(selectedIds,
                                            "{{ route('mass.delete') }}", 'Customers');
                                    }
                                },
                            @endif
                            @if (App\Helpers\SettingsHelper::get('sms_enabled') && auth()->user()->can('إسال SMS للعملاء'))
                                {
                                    text: 'إرسال SMS',
                                    className: 'btn btn-default btn-send-sms',
                                    action: function(e, dt, node, config) {
                                        // جمع معرفات العملاء المحددين
                                        var selectedIds = [];
                                        var selectedNames = [];
                                        $('.row-checkbox:checked').each(function() {
                                            selectedIds.push($(this).val());
                                            var row = $(this).closest('tr');
                                            var name = row.find('td:eq(2)').text()
                                                .trim(); // افتراض أن عمود الاسم هو الثالث
                                            selectedNames.push(name);
                                        });

                                        if (selectedIds.length === 0) {
                                            toastr.warning('يرجى تحديد عملاء اولاً.');
                                            return;
                                        }

                                        // إزالة الحقول المخفية السابقة
                                        $('input[name="customer_ids[]"]').remove();

                                        // إضافة حقل مخفي لكل معرف عميل
                                        selectedIds.forEach(function(id) {
                                            $('#send-sms-form').append(
                                                '<input type="hidden" name="customer_ids[]" value="' +
                                                id + '">');
                                        });

                                        // عرض قائمة العملاء في المودال
                                        var customerListHtml = '';
                                        selectedNames.forEach(function(name) {
                                            customerListHtml += '<li>' + name + '</li>';
                                        });
                                        $('#selected-customers-list').html(customerListHtml);

                                        // فتح المودال
                                        $('#sendSmsModal').modal('show');
                                    }
                                },
                            @endif
                        ]
                    },
                    @can('إضافة عميل')
                        {
                            text: '<i class="fas fa-plus-circle me-1"></i> إضافة عميل جديد',
                            className: 'btn btn-primary btn-add',
                            action: function(e, dt, node, config) {
                                window.location.href = "{{ route('customers.create') }}";
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
                                    "</tr>" :
                                    "";
                            }).join("");

                            return data ?
                                $('<table class="table"/><tbody />').append(data) :
                                false;
                        }
                    },
                },
            });

            // تحديث الجدول عند تغيير الفلاتر
            $('#filter-customer-type, #filter-marketing_channel, #filter-sector').change(function() {
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

            // التعامل مع إرسال النموذج عبر AJAX
            $('#send-sms-form').on('submit', function(e) {
                e.preventDefault();

                var form = $(this)[0];
                var formData = new FormData(form);

                $.ajax({
                    url: form.action,
                    method: form.method,
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        // عرض مؤشر التحميل أو تعطيل زر الإرسال
                        $('#send-sms-form button[type="submit"]').prop('disabled', true).html(
                            '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> جارٍ الإرسال...'
                        );
                    },
                    success: function(response) {
                        // إعادة تعيين النموذج وإغلاق المودال
                        $('#send-sms-form')[0].reset();
                        $('#sendSmsModal').modal('hide');

                        // إعادة تمكين زر الإرسال
                        $('#send-sms-form button[type="submit"]').prop('disabled', false).text(
                            'إرسال الرسالة');

                        // عرض رسالة نجاح باستخدام toastr
                        toastr.success('تم إرسال الرسالة النصية بنجاح.');

                        // إعادة تحميل الجدول
                        $('#customers-table').DataTable().ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        // إعادة تمكين زر الإرسال
                        $('#send-sms-form button[type="submit"]').prop('disabled', false).text(
                            'إرسال الرسالة');

                        // عرض رسائل الخطأ باستخدام toastr
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            var errors = xhr.responseJSON.errors;
                            var errorMessages = '';
                            $.each(errors, function(key, value) {
                                errorMessages += value + '<br>';
                            });

                            toastr.error(errorMessages, 'فشل الإرسال');
                        } else {
                            toastr.error(
                                'حدث خطأ أثناء إرسال الرسالة النصية. يرجى المحاولة لاحقًا.',
                                'حدث خطأ');
                        }
                    }
                });
            });
        });
    </script>


@endsection

@section('content')
    <div class="row g-4 mb-4">
        <!-- إجمالي العملاء -->
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">العملاء</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $totalCustomers }}</h4>
                            </div>
                            <small class="mb-0">إجمالي العملاء</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="ti ti-users ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- العملاء المهتمون -->
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">العملاء المهتمون</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $interestedCustomers }}</h4>
                            </div>
                            <small class="mb-0">عدد العملاء المهتمين</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="ti ti-user-check ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- العملاء المتعاقدين -->
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">العملاء المتعاقدين</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $contractedCustomers }}</h4>

                            </div>
                            <small class="mb-0">عدد العملاء المتعاقدين</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="ti ti-user-plus ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- العملاء المستبعدين -->
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">العملاء المستبعدين</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $excludedCustomers }}</h4>
                            </div>
                            <small class="mb-0">عدد العملاء المستبعدين</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-danger">
                                <i class="ti ti-user-off ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- مودال إرسال SMS -->
    <div class="modal fade" id="sendSmsModal" tabindex="-1" aria-labelledby="sendSmsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="send-sms-form" method="POST" action="{{ route('customers.sendSms') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="sendSmsModalLabel">إرسال رسالة نصية (SMS) للعملاء المحددين</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="sms-message" class="form-label">الرسالة</label>
                            <textarea class="form-control" id="sms-message" name="message" rows="4" required></textarea>
                        </div>
                        <input type="hidden" id="selected-customer-ids" name="customer_ids">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                        <button type="submit" class="btn btn-primary">إرسال الرسالة</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="filters row g-3">
                <!-- نوع العميل -->
                <div class="col-md-4">
                    <label for="filter-customer-type">نوع العميل</label>
                    <select id="filter-customer-type" class="form-control select2" data-placeholder="اختر نوع العميل">
                        <option value=""></option>
                        <option value="individual">فرد</option>
                        <option value="company">شخصية اعتبارية</option>
                    </select>
                </div>
                <!-- قناة التسويق -->
                <div class="col-md-4">
                    <label for="filter-marketing_channel">قناة التسويق</label>
                    <select id="filter-marketing_channel" class="form-control select2"
                        data-placeholder="اختر قناة التسويق">
                        <option value=""></option>
                        @foreach ($marketingChannels as $channel)
                            <option value="{{ $channel->id }}">{{ $channel->name }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- القطاع -->
                <div class="col-md-4">
                    <label for="filter-sector">القطاع</label>
                    <select id="filter-sector" class="form-control select2" data-placeholder="اختر القطاع">
                        <option value=""></option>
                        @foreach ($sectors as $sector)
                            <option value="{{ $sector->id }}">{{ $sector->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <hr class="mt-10">
            <table id="customers-table" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th></th>
                        <th class="custom-checkbox">
                            <input type="checkbox" id="select-all">
                        </th>
                        <th>الاسم</th>
                        <th>نوع العميل</th>
                        <th>مسؤول العلاقة</th>
                        <th>تاريخ الإضافة</th>
                        <th>أضيف بواسطة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- سيتم تعبئة البيانات بواسطة DataTables -->
                </tbody>
            </table>
        </div>
    </div>
@endsection --}}

@extends('layouts.layoutMaster')

@section('title', ' العملاء')

@section('breadcrumb')
    <li><a href="#"> مركز العمليات </a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> العملاء</a>
        <i class="ti ti-star favorite-icon" data-page-name="العملاء" data-page-url="{{ url()->current() }}"
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
    @vite(['resources/assets/js/operations-center/customer/customer.js'])
@endsection


@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('page-style')
    @vite(['resources/css/dataTable.css'])
@endsection


@section('content')
    <div class="row g-4 mb-4">
        <!-- إجمالي العملاء -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">العملاء</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $totalCustomers }}</h4>
                            </div>
                            <small class="mb-0">إجمالي العملاء</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="ti ti-users ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">الافراد</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $individualCustomers }}</h4>
                            </div>
                            <small class="mb-0">عدد العملاء الافراد</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="ti ti-user ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">الشخصيات الإعتبارية</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $companyCustomers }}</h4>

                            </div>
                            <small class="mb-0">عدد العملاء الشخصيات الإعتبارية</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="ti ti-user-shield ti-26px"></i>
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
                    <label for="filter-type">نوع العميل</label>
                    <select id="filter-type" class="form-control select2" data-placeholder="اختر نوع العميل">
                        <option value=""></option>
                        @foreach ($customerType as $type)
                            <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="filter-status">حالة العميل</label>
                    <select id="filter-status" class="form-control select2" data-placeholder="اختر حالة العميل">
                        <option value=""></option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->id }}">{{ $status->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="filter-manager">مسؤول العلاقات</label>
                    <select id="filter-manager" class="form-control select2" data-placeholder="اختر  مسؤول العلاقات">
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


    <!-- مودال إرسال SMS -->
    <div class="modal fade" id="sendSmsModal" tabindex="-1" aria-labelledby="sendSmsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="send-sms-form" method="POST" action="{{ route('operations-center.customers.sendSms') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="sendSmsModalLabel">إرسال رسالة نصية (SMS) للعملاء المحددين</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="sms-message" class="form-label">الرسالة</label>
                            <textarea class="form-control" id="sms-message" name="message" rows="4" required></textarea>
                        </div>
                        <input type="hidden" id="selected-customer-ids" name="customer_ids">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                        <button type="submit" class="btn btn-primary">إرسال الرسالة</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $('#send-sms-form').on('submit', function(e) {
            e.preventDefault();

            var form = $(this)[0];
            var formData = new FormData(form);

            $.ajax({
                url: form.action,
                method: form.method,
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    // عرض مؤشر التحميل أو تعطيل زر الإرسال
                    $('#send-sms-form button[type="submit"]').prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> جارٍ الإرسال...'
                    );
                },
                success: function(response) {
                    // إعادة تعيين النموذج وإغلاق المودال
                    $('#send-sms-form')[0].reset();
                    $('#sendSmsModal').modal('hide');

                    // إعادة تمكين زر الإرسال
                    $('#send-sms-form button[type="submit"]').prop('disabled', false).text(
                        'إرسال الرسالة');

                    // عرض رسالة نجاح باستخدام toastr
                    toastr.success('تم إرسال الرسالة النصية بنجاح.');

                    // إعادة تحميل الجدول
                    $('#customers-table').DataTable().ajax.reload(null, false);
                },
                error: function(xhr) {
                    // إعادة تمكين زر الإرسال
                    $('#send-sms-form button[type="submit"]').prop('disabled', false).text(
                        'إرسال الرسالة');

                    // عرض رسائل الخطأ باستخدام toastr
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        var errors = xhr.responseJSON.errors;
                        var errorMessages = '';
                        $.each(errors, function(key, value) {
                            errorMessages += value + '<br>';
                        });

                        toastr.error(errorMessages, 'فشل الإرسال');
                    } else {
                        toastr.error(
                            'حدث خطأ أثناء إرسال الرسالة النصية. يرجى المحاولة لاحقًا.',
                            'حدث خطأ');
                    }
                }
            });
        });
    </script>



@endsection
