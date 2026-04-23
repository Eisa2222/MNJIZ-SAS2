{{-- @extends('layouts.layoutMaster')

@section('title', 'الخصوم')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> الخصوم</a>
        <i class="ti ti-star favorite-icon" data-page-name="الخصوم" data-page-url="{{ url()->current() }}"
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
            $('.select2').select2({
                placeholder: function() {
                    return $(this).data('placeholder');
                },
                allowClear: true,
                width: '100%',
                language: 'ar',
                dir: 'rtl'
            });

            var table = $('#setting-table').DataTable({
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
                    url: "{{ route($route . '.index') }}",
                    data: function(d) {
                        d.type = $('#filter-type').val();
                        d.region = $('#filter-region').val();

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
                            return ' <i class="ti ti-rosette-discount-check-filled text-danger" title="خصم"> </i>' +
                                ' ' + data;
                        },
                        data: 'name',
                        name: 'name',
                        className: 'table-ellipsis text-right'
                    },
                    {
                        data: 'email',
                        name: 'email',
                        className: 'table-ellipsis',
                        render: function(data, type, row) {
                            return data ? data : '<span style="color: red;">لا يوجد</span>';
                        },

                    },
                    {
                        data: 'phone',
                        name: 'phone',
                        render: function(data, type, row) {
                            return data ? data : '<span style="color: red;">لا يوجد</span>';
                        },
                    },
                    {
                        data: 'type',
                        name: 'type'
                    },
                    {
                        data: 'settings_region_id',
                        name: 'settings_region_id',
                        className: 'text-nowrap',
                        render: function(data, type, row) {
                            return data ? data : '<span style="color: red;">لا يوجد</span>';
                        },

                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        visible: function() {
                            return {!! auth()->user()->can('تعديل خصم') || auth()->user()->can('حذف خصم') ? 'true' : 'false' !!};
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
                            },
                            {
                                extend: 'excel',
                                text: 'إكسل'
                            },
                            @if (auth()->user()->can('حذف خصم'))
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
                                            'Opponent'
                                        );
                                    }
                                },
                            @endif
                            @if (App\Helpers\SettingsHelper::get('sms_enabled') && auth()->user()->can('إسال SMS للخصوم'))
                                {
                                    text: 'إرسال SMS',
                                    className: 'btn btn-default btn-send-sms',
                                    action: function(e, dt, node, config) {
                                        var selectedIds = [];
                                        var selectedNames = [];
                                        $('.row-checkbox:checked').each(function() {
                                            selectedIds.push($(this).val());
                                            var row = $(this).closest('tr');
                                            var name = row.find('td:eq(2)').text()
                                                .trim();
                                            selectedNames.push(name);
                                        });

                                        if (selectedIds.length === 0) {
                                            toastr.warning('يرجى تحديد خصوم أولاً.');
                                            return;
                                        }
                                        // إزالة الحقول المخفية السابقة
                                        $('input[name="opponent_ids[]"]').remove();
                                        // إضافة حقل مخفي لكل معرف خصم
                                        selectedIds.forEach(function(id) {
                                            $('#send-sms-form').append(
                                                '<input type="hidden" name="opponent_ids[]" value="' +
                                                id + '">');
                                        });
                                        // عرض قائمة الخصوم في المودال
                                        var opponentListHtml = '';
                                        selectedNames.forEach(function(name) {
                                            opponentListHtml += '<li>' + name + '</li>';
                                        });
                                        $('#selected-opponents-list').html(opponentListHtml);
                                        $('#sendSmsModal').modal('show');
                                    }
                                },
                            @endif

                        ]
                    },
                    @can('إضافة خصم')
                        {
                            text: '<i class="fas fa-plus-circle me-1"></i> إضافة خصم جديد ',
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
            $('#filter-type, #filter-region').change(function() {
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

            // كود إرسال الرسائل النصية عبر AJAX
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
                        toastr.success('تم إرسال الرسائل النصية بنجاح.');

                        // إعادة تحميل الجدول
                        $('#setting-table').DataTable().ajax.reload(null, false);
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
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- مودال إرسال SMS -->
    <div class="modal fade" id="sendSmsModal" tabindex="-1" aria-labelledby="sendSmsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="send-sms-form" method="POST" action="{{ route('opponents.sendSms') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="sendSmsModalLabel">إرسال رسالة نصية (SMS) للخصوم المحددين</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="sms-message" class="form-label">الرسالة</label>
                            <textarea class="form-control" id="sms-message" name="message" rows="4" required></textarea>
                        </div>
                        <ul id="selected-opponents-list" style="display: none;">
                            <!-- سيتم عرض قائمة الخصوم المحددين هنا -->
                        </ul>
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
                <div class="col-md-6">
                    <label for="filter-type"> نوع الخصم</label>
                    <select id="filter-type" class="form-control select2" data-placeholder="اختر  نوع الخصم">
                        <option value=""></option>
                        <option value="individual">فرد</option>
                        <option value="company">شخصية اعتبارية</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="filter-region">المدينة </label>
                    <select id="filter-region" class="form-control select2" data-placeholder="اختر  المدينة ">
                        <option value=""></option>
                        @foreach (\App\Models\general_setting\SettingsRegion::select(['id', 'name'])->get() as $region)
                            <option value="{{ $region->id }}">{{ $region->name }}</option>
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
                        <th>
                            الاسم
                        </th>
                        <th>البريد الإلكتروني</th>
                        <th>الجوال </th>
                        <th> النوع</th>
                        <th> المدينة</th>
                        <th> تاريخ الاضافة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- سيتم تعبئة الجدول بواسطة DataTables -->
                </tbody>
            </table>
        </div>
    </div>

@endsection --}}


@extends('layouts.layoutMaster')

@section('title', ' الخصوم')

@section('breadcrumb')
    <li><a href="#">الشؤون القانونية</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> الخصوم</a>
        <i class="ti ti-star favorite-icon" data-page-name="الخصوم" data-page-url="{{ url()->current() }}"
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
    @vite(['resources/assets/js/legal-affairs/opponents/opponents.js'])
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
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">الخصوم</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $totalOpponents }}</h4>
                            </div>
                            <small class="mb-0">إجمالي الخصوم</small>
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
                                <h4 class="mb-0 me-2">{{ $individualOpponents }}</h4>
                            </div>
                            <small class="mb-0">عدد الخصوم الافراد</small>
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
                                <h4 class="mb-0 me-2">{{ $companyOpponents }}</h4>

                            </div>
                            <small class="mb-0">عدد الخصوم الشخصيات الإعتبارية</small>
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
                <div class="col-md-6">
                    <label for="filter-type">نوع الخصم</label>
                    <select id="filter-type" class="form-control select2" data-placeholder="اختر نوع الخصم">
                        <option value=""></option>
                        @foreach ($opponentType as $type)
                            <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="filter-region">المدينة </label>
                    <select id="filter-region" class="form-control select2" data-placeholder="اختر المدينة ">
                        <option value=""></option>
                        @foreach ($settingsRegions as $region)
                            <option value="{{ $region->id }}">{{ $region->name }}</option>
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
                <form id="send-sms-form" method="POST" action="{{ route('legal-affairs.opponents.sendSms') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="sendSmsModalLabel">إرسال رسالة نصية (SMS) للخصوم المحددين</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="sms-message" class="form-label">الرسالة</label>
                            <textarea class="form-control" id="sms-message" name="message" rows="4" required></textarea>
                        </div>
                        <ul id="selected-opponents-list" style="display: none;">
                            <!-- سيتم عرض قائمة الخصوم المحددين هنا -->
                        </ul>
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
                    toastr.success('تم إرسال الرسائل النصية بنجاح.');

                    // إعادة تحميل الجدول
                    $('#setting-table').DataTable().ajax.reload(null, false);
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
