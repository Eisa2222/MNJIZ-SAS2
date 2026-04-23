@extends('layouts.layoutMaster')

@section('title', 'الحضور و الانصراف')

@section('breadcrumb')
    <li><a href="#">الموارد البشرية</a></li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">الحضور و الانصراف</a>
        <i class="ti ti-star favorite-icon" data-page-name="الحضور و الانصراف" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event,this)"></i>
    </li>
@endsection

{{-- vendor assets --}}
@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss'])
@endsection
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('page-style')
    @vite(['resources/css/dataTable.css'])
@endsection


@section('page-script')
    <script>
        $(function() {

            /* Select2 */
            $('.select2').select2({
                placeholder: e => $(e.element).data('placeholder'),
                allowClear: true,
                width: '100%',
                language: 'ar',
                dir: 'rtl'
            });

            /* إذا أُزيل اختيار الشهر نُرجعه للقيمة الافتراضية */
            const defaultMonthVal = '{{ $currentMonth }}';
            const defaultMonthLabel = '{{ $currentMonthName }}';

            const refreshMonthLabel = () =>
                $('.month-label').text($('#filter-month option:selected').text());

            refreshMonthLabel();

            const table = $('#attendances-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('attendances.data') }}",
                    data: d => {
                        d.status = $('#filter-status').val();
                        d.has_updates = $('#filter-updates').val();
                        d.employee = $('#filter-employee').val();
                        d.month = $('#filter-month').val();
                    }
                },
                columns: [{
                        data: "",
                        defaultContent: "",
                        className: "control",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name',
                        name: 'name',
                        className: 'text-nowrap'
                    },
                    {
                        data: 'date',
                        name: 'date',
                        className: 'table-ellipsis'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        className: 'table-ellipsis'
                    },
                    {
                        data: 'check_in_time',
                        name: 'check_in_time',
                        className: 'table-ellipsis'
                    },
                    {
                        data: 'check_out_time',
                        name: 'check_out_time',
                        className: 'table-ellipsis'
                    },
                    {
                        data: 'working_hours',
                        name: 'working_hours',
                        className: 'table-ellipsis'
                    }, {
                        data: 'has_logs',
                        name: 'has_logs',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
                            if (parseInt(data) > 0) {
                                return '<span class="badge bg-warning text-dark" style="padding: 6px 10px;"><i class="ti ti-history me-1" style="font-size: 12px;"></i><strong>' +
                                    data + '</strong></span>';
                            } else {
                                return '<span class="text-muted">—</span>';
                            }
                        }
                    }, {
                        data: 'violations',
                        name: 'violations',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
                            // إذا لم تكن هناك مخالفات، نعرض علامة فارغة
                            if (data === null) {
                                return '<span class="text-muted">—</span>';
                            }

                            // اختيار الأيقونة والتنسيق حسب نوع المخالفة
                            let icon, colorClass, title, violationDetails;

                            switch (data.type) {
                                case 'delay':
                                    icon = 'ti-clock';
                                    colorClass = 'bg-warning';
                                    title = 'تأخير في الحضور';
                                    violationDetails = row.late_minutes ?
                                        `${row.late_minutes} دقيقة` : '';
                                    break;
                                case 'early_leave':
                                    icon = 'ti-logout';
                                    colorClass = 'bg-danger';
                                    title = 'خروج مبكر قبل انتهاء الدوام';
                                    violationDetails = row.early_leave_minutes ?
                                        `${row.early_leave_minutes} دقيقة` : '';
                                    break;
                                case 'absence':
                                    icon = 'ti-user-off';
                                    colorClass = 'bg-dark';
                                    title = 'غياب';
                                    violationDetails = 'يوم كامل';
                                    break;
                                case 'after_hours':
                                    icon = 'ti-clock-plus';
                                    colorClass = 'bg-info';
                                    title = 'البقاء بعد انتهاء الدوام';
                                    violationDetails = row.overtime_minutes ?
                                        `${row.overtime_minutes} دقيقة` : '';
                                    break;
                            }

                            // إنشاء زر محسن للمخالفة
                            return `
                            <a href="${data.url}"
                             class="violation-badge badge badge-pill ${colorClass} text-white"
                             style="padding: 8px 12px; font-size: 12px; position: relative; border-radius: 20px; display: inline-flex; align-items: center; gap: 5px; text-decoration: none;"
                             data-bs-toggle="tooltip"
                             data-bs-placement="top"
                             title="${title}: ${violationDetails}">
                              <i class="ti ${icon} me-1" style="font-size: 14px;"></i>
                              <span style="font-weight: 600;">${data.text}</span>
                              ${violationDetails ? `<span class="ms-1 badge bg-white text-dark rounded-pill" style="font-size: 10px; padding: 2px 6px; min-width: 24px;">${violationDetails}</span>` : ''}
                             </a>`;
                        }
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                order: [
                    [0, 'desc']
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
                                    ""
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
                }
            });

            /* تحديـث الكروت بعد كل استدعاء */
            table.on('xhr.dt', (e, s, json) => {
                if (json && json.stats) {
                    $('#presentCard').text(json.stats.present_pct + '%');
                    $('#absentCard').text(json.stats.absent_pct + '%');
                    $('#leaveCard').text(json.stats.leave_pct + '%');
                }
            });

            $('#filter-status,#filter-updates,#filter-employee,#filter-month').on('change', function() {
                if (!$('#filter-month').val()) {
                    $('#filter-month').val(defaultMonthVal).trigger('change.select2');
                }
                refreshMonthLabel();
                table.draw();
            });
        });
    </script>
@endsection

@section('content')
    <div class="row g-4 mb-4">
        {{-- الحضور --}}
        <div class="col-6 col-md-6 col-xl-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">الحضور</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 id="presentCard" class="mb-0 me-2">{{ $percentPresent }}%</h4>
                            </div>
                            <small class="text-muted">حضور شهر <span
                                    class="month-label fw-bold">{{ $currentMonthName }}</span></small>
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

        {{-- الغياب --}}
        <div class="col-6 col-md-6 col-xl-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">الغياب</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 id="absentCard" class="mb-0 me-2">{{ $percentAbsent }}%</h4>
                            </div>
                            <small class="text-muted">غياب شهر <span
                                    class="month-label fw-bold">{{ $currentMonthName }}</span></small>
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

        {{-- الإجازات --}}
        <div class="col-12 col-md-12 col-xl-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">الإجازات</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 id="leaveCard" class="mb-0 me-2">{{ $percentLeave }}%</h4>
                            </div>
                            <small class="text-muted">إجازات شهر <span
                                    class="month-label fw-bold">{{ $currentMonthName }}</span></small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="ti ti-user-minus ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- الفلاتر --}}
    <div class="card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="filter-month">الشهر</label>
                    <select id="filter-month" class="form-control select2" data-placeholder="اختر الشهر">
                        {!! $monthOptions !!}
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="filter-employee">الموظف</label>
                    <select id="filter-employee" class="form-control select2" data-placeholder="اختر الموظف">
                        <option value=""></option>
                        {!! $employeeOptions !!}
                    </select>
                </div>

                <!-- تعديل الفلتر من حالة الموظف إلى حالة الحضور -->
                <div class="col-md-3">
                    <label for="filter-status"> الحــالة</label>
                    <select id="filter-status" class="form-control select2" data-placeholder="اختر الحــالة">
                        <option value=""></option>
                        <option value="present">حضور</option>
                        <option value="absent">غياب</option>
                        <option value="leave">إجازة</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="filter-updates">السجلات المحدّثة</label>
                    <select id="filter-updates" class="form-control select2" data-placeholder="اختر نوع السجلات">
                        <option value=""></option>
                        <option value="yes">السجلات المحدّثة</option>
                        <option value="no">السجلات غير المحدّثة</option>
                    </select>
                </div>
            </div>

            <hr class="mt-4">

            {{-- جدول البيانات --}}
            <table id="attendances-table" class="table table-striped table-bordered w-100">
                <thead>
                    <tr>
                        <th></th>
                        <th>#</th>
                        <th>الاسم</th>
                        <th>التاريخ</th>
                        <th>الحالة</th>
                        <th>وقت الدخول</th>
                        <th>وقت الخروج</th>
                        <th>عدد ساعات العمل</th>
                        <th>التحديثات</th>
                        <th>المخالفات</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
@endsection
