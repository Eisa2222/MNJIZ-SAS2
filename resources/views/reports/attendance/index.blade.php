@extends('layouts.layoutMaster')

@section('title', 'تقرير الحضور والغياب حسب أيام الأسبوع')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">تقرير الحضور حسب</a>
        <i class="ti ti-star favorite-icon" data-page-name="تقرير الحضور" data-page-url="{{ url()->current() }}"
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

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">تقرير الحضور والغياب خلال فترة معينة</h5>
            <div class="row align-items-end">
                <div class="col-md-4 mb-3">
                    <label for="report-employee-id" class="form-label">اختر الموظف</label>
                    <select id="report-employee-id" class="form-control select2">
                        <option value="">الكل</option>
                        @foreach ($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="report-date-from" class="form-label">من تاريخ</label>
                    <input type="date" id="report-date-from" class="form-control">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="report-date-to" class="form-label">إلى تاريخ</label>
                    <input type="date" id="report-date-to" class="form-control">
                </div>
                <div class="col-md-2 mb-3">
                    <button id="btn-download-report" class="btn btn-primary w-100">
                        <i class="ti ti-download me-1"></i> تحميل التقرير
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- ===================================================================== -->
    <!-- الجدول: يعرض كل الموظفين + أزرار الإجراءات في عمود واحد -->
    <!-- ===================================================================== -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">
                تقارير الحضور والغياب للموظفين خلال الشهر الحالي
            </h5>
            <div class="table-responsive">
                <table id="attendance-report-table" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th></th>
                            <th>اسم الموظف</th>
                            <th>رقم الموظف</th>
                            <th>المسمى الوظيفي</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- تعبئته عبر DataTables Ajax -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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

            // ====== زر "تحميل" للتقرير المفلتر ======
            document.getElementById('btn-download-report').addEventListener('click', function() {
                var empId = document.getElementById('report-employee-id').value;
                var dateFrom = document.getElementById('report-date-from').value;
                var dateTo = document.getElementById('report-date-to').value;

                var url = "{{ route('reports.attendance.download') }}" +
                    '?employee_id=' + empId +
                    '&date_from=' + dateFrom +
                    '&date_to=' + dateTo;

                window.location = url;
            });

            // ====== تهيئة DataTables للجدول ======
            // ====== تهيئة DataTables للجدول ======
            var table = $('#attendance-report-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('reports.attendance.table-data') }}",
                    data: function(d) {
                        d.employee_id = $('#report-employee-id').val();
                        d.date_from = $('#report-date-from').val();
                        d.date_to = $('#report-date-to').val();
                    }
                },
                columns: [{
                        data: 'profile_picture',
                        name: 'profile_picture',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'employee_name',
                        name: 'employee_name', // تأكد من تطابق الاسم مع الاسم المستخدم في الكنترولر
                        orderable: true, // السماح بالفرز
                        searchable: true // السماح بالبحث
                    },
                    {
                        data: 'employee_number',
                        name: 'employee_number', // تأكد من تطابق الاسم مع الاسم المستخدم في الكنترولر
                        className: 'text-center',
                        orderable: true,
                        searchable: true,
                        render: function(data, type, row) {
                            return data ? data : '';
                        }
                    },
                    {
                        data: 'job_title',
                        name: 'job_title', // تأكد من تطابق الاسم مع الاسم المستخدم في الكنترولر
                        orderable: true,
                        searchable: true,
                        render: function(data, type, row) {
                            return data ? data : '';
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                order: [
                    [2, 'asc'] // ترتيب تصاعدي حسب رقم الموظف
                ],
                // تأكد من وجود معلومات البحث والفرز
                searching: true,
                ordering: true,
                language: {
                    url: "{{ asset('assets/json/ar.json') }}"
                },
                responsive: {
                    details: {
                        display: $.fn.dataTable.Responsive.display.modal({
                            header: function(row) {
                                var data = row.data();
                                return "تفاصيل الموظف: " + (data.employee_name || '');
                            }
                        }),
                        type: "column",
                        renderer: function(api, rowIdx, columns) {
                            var data = $.map(columns, function(col, i) {
                                return col.title !== "" && col.columnIndex > 0 ?
                                    // تجنب عمود الصورة
                                    '<tr data-dt-row="' + col.rowIndex + '" data-dt-column="' +
                                    col.columnIndex + '">' +
                                    "<td>" + col.title + ":" + "</td> " +
                                    "<td>" + col.data + "</td>" +
                                    "</tr>" : "";
                            }).join("");

                            return data ? $('<table class="table"/><tbody />').append(data) : false;
                        }
                    }
                }
            });
        });
    </script>
@endsection
