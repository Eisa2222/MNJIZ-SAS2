@extends('layouts.layoutMaster')

@section('title', 'تقرير أرصدة الإجازات')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">تقارير الإجازات</a>
        <i class="ti ti-star favorite-icon" data-page-name="تقارير الإجازات" data-page-url="{{ url()->current() }}"
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

            var table = $('#leave-balance-table').DataTable({
                "createdRow": function(row, data, dataIndex) {
                    $(row).find('td.table-ellipsis').each(function() {
                        var cellContent = $(this).html();
                        if ($(this).find('a').length > 0) {
                            var linkElement = $(this).find('a');
                            var linkText = linkElement.text();
                            linkElement.html('<div class="cell-content"><span>' + linkText +
                                '</span></div>');
                        } else {
                            $(this).html('<div class="cell-content"><span>' + cellContent +
                                '</span></div>');
                        }
                    });
                },

                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('reports.leave-reports.index') }}",
                    data: function(d) {
                        d.year = $('#filter-year').val();
                        d.filter_employee = $('#filter-employee').val();
                        d.filter_leave_type = $('#filter-leave-type').val();
                    }
                },
                columns: [{
                        data: 'checkbox',
                        name: 'checkbox',
                        orderable: false,
                        searchable: false,
                        className: 'custom-checkbox'
                    },
                    {
                        data: 'employee_profile',
                        name: 'employee_profile',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
                            // تعيين مسار الصورة
                            var imageUrl = row.profile_picture && row.profile_picture_exists ?
                                row.profile_picture :
                                "{{ asset('assets/img/branding/Alburhan-Logo.png') }}"; // صورة افتراضية

                            return '<img src="' + imageUrl +
                                '" alt="Profile" class="rounded-circle" width="40" height="40">';
                        }
                    },
                    {
                        data: 'employee_name',
                        name: 'employee_name',
                        className: 'table-ellipsis',
                        render: function(data, type, row) {
                            return '<span>' + data + '</span>';
                        }
                    },
                    {
                        data: 'year',
                        name: 'year',
                        className: 'text-center',
                        render: function(data, type, row) {
                            return '<span class="fw-semibold">' + data + '</span>';
                        }
                    },
                    {
                        data: 'total_days',
                        name: 'total_days',
                        className: 'text-center',
                        render: function(data, type, row) {
                            return '<span class="fw-semibold">' + data + ' يوم</span>';
                        }
                    },
                    {
                        data: 'used_days',
                        name: 'used_days',
                        className: 'text-center',
                        render: function(data, type, row) {
                            return '<span class="text-danger fw-semibold">' + data + ' يوم</span>';
                        }
                    },
                    {
                        data: 'remaining_days',
                        name: 'remaining_days',
                        className: 'text-center',
                        render: function(data, type, row) {
                            return '<span class="text-success fw-semibold">' + data + ' يوم</span>';
                        }
                    },
                    {
                        data: 'usage_percentage',
                        name: 'usage_percentage',
                        className: 'text-center',
                        orderable: false,
                        render: function(data, type, row) {
                            var percentage = 0;
                            if (typeof data === 'string' && data.includes('%')) {
                                var match = data.match(/(\d+(?:\.\d+)?)%/);
                                if (match) {
                                    percentage = parseFloat(match[1]);
                                }
                            } else if (typeof data === 'number') {
                                percentage = data;
                            }

                            var colorClass = 'success';
                            if (percentage >= 80) {
                                colorClass = 'danger';
                            } else if (percentage >= 50) {
                                colorClass = 'warning';
                            }

                            var html = '<div class="progress-container">';
                            html += '<div class="progress">';
                            html += '<div class="progress-bar progress-bar-' + colorClass +
                                '" role="progressbar" ';
                            html += 'style="width: ' + percentage + '%" ';
                            html += 'aria-valuenow="' + percentage + '" ';
                            html += 'aria-valuemin="0" aria-valuemax="100"></div>';
                            html += '</div>';
                            html += '<span class="progress-percentage text-' + colorClass + '">' +
                                percentage + '%</span>';
                            html += '</div>';

                            return html;
                        }
                    }
                ],
                order: [
                    [2, 'asc'] // الترتيب حسب اسم الموظف
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
                        },
                        {
                            extend: 'excel',
                            text: 'إكسل'
                        },
                        {
                            text: 'تصدير المحدد',
                            action: function(e, dt, node, config) {
                                let selected = $('.row-checkbox:checked').map(function() {
                                    return this.value;
                                }).get();
                                if (!selected.length) {
                                    toastr.warning('يرجى تحديد صف واحد على الأقل ');
                                    return false;
                                }
                                let form = $('<form>', {
                                    method: 'POST',
                                    action: "{{ route('reports.leave-reports.export') }}"
                                });
                                form.append('@csrf');
                                selected.forEach(id => form.append($('<input>', {
                                    type: 'hidden',
                                    name: 'ids[]',
                                    value: id
                                })));
                                form.appendTo('body').submit();
                            }
                        }
                    ]
                }],
                language: {
                    url: "{{ asset('assets/json/ar.json') }}"
                },
                orderCellsTop: true,
                // إزالة columnDefs المتعارضة
                responsive: {
                    details: {
                        display: $.fn.dataTable.Responsive.display.modal({
                            header: function(row) {
                                var data = row.data();
                                return "تفاصيل رصيد الإجازة";
                            },
                        }),
                        type: "column",
                        renderer: function(api, rowIdx, columns) {
                            var data = $.map(columns, function(col, i) {
                                // تجاهل عمود checkbox في modal
                                if (col.title === "") return "";

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

            // تحديد الكل
            $('#select-all').on('click', function() {
                var checked = this.checked;
                $('input.row-checkbox', table.rows({
                    search: 'applied'
                }).nodes()).prop('checked', checked);
            });

            // تحديث الجدول عند تغيير الفلاتر
            $('#filter-year, #filter-employee, #filter-leave-type').change(function() {
                table.ajax.reload();
            });

            // كود تحريك النص
            $(document).on('mouseenter', '.table-ellipsis', function() {
                var spanDom = $(this).find('span').get(0);
                if (!spanDom) return;

                var textWidth = spanDom.scrollWidth;
                var cellWidth = $(this).width();

                if (textWidth > cellWidth) {
                    const distance = textWidth - cellWidth;
                    const speed = 30;
                    const duration = distance / speed;

                    $(spanDom).css({
                        'transform': `translateX(${distance}px)`,
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

    <div class="card">
        <div class="card-body">
            <div class="filters row mb-3">
                <div class="col-md-6">
                    <label for="filter-year">السنة</label>
                    <select id="filter-year" name="year" class="form-control select2" data-placeholder="اختر السنة">
                        <option value="">كل السنوات</option>
                        @foreach ($years as $yearOption)
                            <option value="{{ $yearOption }}"
                                {{ request('year') == (string) $yearOption ? 'selected' : '' }}>
                                {{ $yearOption }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="filter-employee">الموظف</label>
                    <select id="filter-employee" class="form-control select2" data-placeholder="اختر الموظف">
                        <option value=""></option>
                        @foreach ($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <table id="leave-balance-table" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th class="custom-checkbox">
                            <input type="checkbox" id="select-all">
                        </th>
                        <th></th>
                        <th>الموظف</th>
                        <th>السنة</th>
                        <th>الرصيد الكلي</th>
                        <th>المستخدم</th>
                        <th>المتبقي</th>
                        <th>نسبة الاستخدام</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- سيتم تعبئته بواسطة DataTables --}}
                </tbody>
            </table>
        </div>
    </div>
@endsection
