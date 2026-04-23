@extends('layouts.layoutMaster')

@section('title', 'إعتمادات العروض')

@section('breadcrumb')
    <li><a href="#">طلبات الإعتماد</a></li>

    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> إعتمادات العروض</a>
        <i class="ti ti-star favorite-icon" data-page-name="إعتمادات العروض" data-page-url="{{ url()->current() }}"
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
                    url: "{{ route($route) }}",
                    data: function(d) {
                        d.status = $('#filter-status').val();
                        d.customer = $('#filter-customer').val();
                        d.employee = $('#filter-manager').val();
                    }
                },
                columns: [{
                        data: ""
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
                        data: 'offer_name',
                        name: 'offer_name',
                        className: 'table-ellipsis',
                    },
                    {
                        render: function(data, type, row) {
                            return '<i class="ti ti-rosette-discount-check-filled text-success" title="عميل" ></i> ' +
                                data;
                        },
                        data: 'customer_id',
                        name: 'customer_id',
                        className: 'text-nowrap',
                    },
                    {
                        data: 'relationship_manager_id',
                        name: 'relationship_manager_id',
                        className: 'table-ellipsis'
                    },
                    {
                        data: 'stage_price_offer_id',
                        name: 'stage_price_offer_id',
                        className: 'table-ellipsis'
                    },
                    {
                        data: 'status', // تعريف العمود الجديد
                        name: 'status',
                        className: 'text-nowrap'
                    },
                    {
                        data: 'created_at', // عرض تاريخ الإضافة بدلاً من تاريخ البداية
                        name: 'created_at'
                    },
                    {
                        data: 'id',
                        name: 'offers.id',
                        visible: false
                    }
                ],
                order: [
                    [7, 'desc']
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
                    }, {
                        text: 'حذف المحدد',
                        className: 'btn btn-default btn-delete-selected',
                        action: function(e, dt, node, config) {
                            var selectedIds = [];
                            $('.row-checkbox:checked').each(function() {
                                selectedIds.push($(this).val());
                            });

                            confirmDeleteSelectedmss(selectedIds,
                                "{{ route('mass.delete') }}", 'Offers');
                        }
                    }]
                }],
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
            $('#filter-status, #filter-customer, #filter-manager').change(function() {
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
                    span.css('transform', 'translateX(' + (textWidth - cellWidth) + 'px)');
                }
            }).on('mouseleave', '.table-ellipsis', function() {
                $(this).find('span').css('transform', 'translateX(0)');
            });

        });

        // عند النقر على أيقونة الاعتماد/الإلغاء
        $(document).on('click', '.approval-link', function(e) {
            e.preventDefault();

            let offerId = $(this).data('id');
            let fieldName = $(this).data('field');
            let action = $(this).data('action');
            let link = $(this);

            // رسالة التأكيد حسب نوع الإجراء
            let confirmTitle = (action === 'approve') ? 'هل تريد اعتماد هذه الخطوة؟' : 'هل تريد إلغاء الاعتماد؟';
            let confirmButtonText = (action === 'approve') ? 'اعتماد' : 'إلغاء الاعتماد';

            Swal.fire({
                title: confirmTitle,
                icon: 'warning',
                showCancelButton: true,
                showConfirmButton: true,
                buttonsStyling: false,
                customClass: {
                    popup: 'custom-popup',
                    title: 'custom-title',
                    text: 'custom-text',
                    confirmButton: 'btn btn-success custom-confirm',
                    cancelButton: 'btn btn-danger custom-cancel'
                },
                confirmButtonText: confirmButtonText,
                cancelButtonText: 'إلغاء',
                reverseButtons: false,
            }).then((result) => {
                if (result.isConfirmed) {
                    sendAjaxRequest(action, offerId, fieldName, link);
                }
            });
        });

        // وظيفة لإرسال طلب AJAX
        function sendAjaxRequest(action, offerId, fieldName, link) {
            $.ajax({
                url: '/offers/' + action + '/' + offerId + '/' + fieldName,
                method: 'GET',
                success: function(response) {
                    if (action === 'approve') {
                        // تغيير الأيقونة إلى أخضر
                        link.find('i').removeClass('text-danger').addClass('text-success');
                        link.data('action', 'revoke');
                        toastr.success('تم اعتماد الخطوة بنجاح.');
                    } else if (action === 'revoke') {
                        // تغيير الأيقونة إلى أحمر
                        link.find('i').removeClass('text-success').addClass('text-danger');
                        link.data('action', 'approve');
                        toastr.success('تم إلغاء الاعتماد بنجاح.');
                    }
                },
                error: function(xhr) {
                    let errMsg = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error :
                        'حدث خطأ أثناء تنفيذ الإجراء.';
                    toastr.error(errMsg);
                }
            });
        }
    </script>
@endsection

@section('content')
    {{-- <div class="row g-4 mb-4">
        <!-- بطاقات الإحصائيات -->
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">العروض</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $totalOffers }}</h4>
                            </div>
                            <small class="mb-0">إجمالي العروض</small>
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

        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">العروض المعتمدة</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $waitingClientApproval }}</h4>
                            </div>
                            <small class="mb-0">العروض المعتمدة</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-success">
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
                            <span class="text-heading"> في إنتظار الإعتماد</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $contractStage }}</h4>
                            </div>
                            <small class="mb-0">في إنتظار الإعتماد</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="ti ti-hourglass ti-26px"></i>
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
                            <span class="text-heading">العروض المرفوضة</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $finishedOffers }}</h4>
                            </div>
                            <small class="mb-0">العروض المرفوضة</small>
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
                    <label for="filter-status">مرحلة العرض</label>
                    <select id="filter-status" name="status" class="form-control select2"
                        data-placeholder="اختر مرحلة العرض">
                        <option value=""></option>
                        @foreach (\App\Models\general_setting\SettingsStagePriceOffer::all() as $stage)
                            <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="filter-customer">العميل</label>
                    <select id="filter-customer" class="form-control select2" data-placeholder="اختر العميل">
                        <option value=""></option>
                        @foreach (\App\Models\business_development\Customers::all() as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="filter-manager">مسؤول العلاقات</label>
                    <select id="filter-manager" class="form-control select2" data-placeholder="اختر  مسؤول العلاقات">
                        <option value=""></option>
                        @foreach (\App\Models\Hr\Employees\Employees::all() as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
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
                        <th>اسم العرض</th>
                        <th> العميل </th>
                        <th> مسؤول العلاقات</th>
                        <th>مرحلة العرض</th>
                        <th>حالة الاعتماد</th>
                        <th>تاريخ الإضافة</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- سيتم تعبئة الجدول بواسطة DataTables -->
                </tbody>
            </table>
        </div>
    </div>
@endsection
