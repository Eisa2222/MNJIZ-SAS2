@extends('layouts.layoutMaster')

@section('title', $wps_payroll->reference)

@section('breadcrumb')
    <li><a href="#">طلبات الإعتماد</a></li>
    <li><a href="{{ route('accreditation-requests.wps-requests.index') }}">إعتمادات مسيرات الرواتب</a></li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">{{ $wps_payroll->reference }}</a>
        <i class="ti ti-star favorite-icon" data-page-name="{{ $wps_payroll->reference }}"
            data-page-url="{{ url()->current() }}" onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js'])
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('sweetalert-cdn')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endsection

@section('page-style')
    @vite(['resources/css/dataTable.css'])
@endsection

@section('page-script')
    @yield('sweetalert-cdn')
    <script>
        $(document).ready(function() {
            // تهيئة جدول DataTable
            var table = $('#table').DataTable({
                "createdRow": function(row, data, dataIndex) {
                    $(row).find('td.table-ellipsis').each(function() {
                        var cellText = $(this).text();
                        $(this).html('<div class="cell-content"><span>' + cellText +
                            '</span></div>');
                    });
                },
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('accreditation-requests.wps-requests.show', $wps_payroll->id) }}",
                },
                columns: [{
                        data: "",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'employee_id',
                        name: 'employee_id'
                    },
                    {
                        data: 'basic',
                        name: 'basic'
                    },
                    {
                        data: 'transport',
                        name: 'transport'
                    },
                    {
                        data: 'housing',
                        name: 'housing'
                    },
                    {
                        data: 'other',
                        name: 'other'
                    },
                    {
                        data: 'insurance',
                        name: 'insurance'
                    },
                    {
                        data: 'deductions',
                        name: 'deductions'
                    },
                    {
                        data: 'incentives',
                        name: 'incentives'
                    },
                    {
                        data: 'net',
                        name: 'net'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'action',
                        name: 'action'
                    }
                ],
                order: [
                    [1, 'asc']
                ],
                dom: '<"dt-toolbar"<' +
                    '"dt-toolbar-left d-flex flex-column flex-sm-row align-items-start align-items-sm-center"l>' +
                    '<"dt-toolbar-right d-flex flex-column flex-sm-row align-items-end align-items-sm-center justify-content-between"fB>>' +
                    'rt' +
                    '<"row mt-3"<"d-flex flex-column flex-sm-row align-items-center justify-content-between w-100"i>' +
                    '<"pagination-wrapper overflow-auto w-100"p>>',
                buttons: [{
                    extend: 'collection',
                    className: 'btn btn-export btn-sm d-none',
                    text: 'الإجراءات',
                    buttons: [{
                            extend: 'copy',
                            text: 'نسخ'
                        },
                        {
                            extend: 'excel',
                            text: 'إكسل'
                        }
                    ]
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
                                return "التفاصيل";
                            },
                        }),
                        type: "column",
                        renderer: function(api, rowIdx, columns) {
                            var data = $.map(columns, function(col) {
                                return col.title !== "" ?
                                    '<tr data-dt-row="' + col.rowIndex +
                                    '" data-dt-column="' + col.columnIndex +
                                    '"><td>' + col.title + ":</td> <td>" +
                                    col.data + "</td></tr>" : "";
                            }).join("");
                            return data ?
                                $('<table class="table"/><tbody />').append(data) :
                                false;
                        },
                    },
                },
            });

            // أزرار الاعتماد/إلغاء الاعتماد
            $(document).on('click', '.approval-action', function(e) {
                e.preventDefault();
                let payrollId = $(this).data('id');
                let name = $(this).data('name');
                let action = $(this).data('action');
                let url = '';
                let titleText = '';
                let confirmBtn = '';

                if (action === 'approve') {
                    url =
                        "{{ route('accreditation-requests.wps-requests.approve', ['id' => '__ID__', 'name' => '__NAME__']) }}";
                    url = url.replace('__ID__', payrollId).replace('__NAME__', name);
                    titleText = 'هل تريد اعتماد مسير الرواتب؟';
                    confirmBtn = 'اعتماد';
                } else if (action === 'revoke') {
                    url =
                        "{{ route('accreditation-requests.wps-requests.revoke', ['id' => '__ID__', 'name' => '__NAME__']) }}";
                    url = url.replace('__ID__', payrollId).replace('__NAME__', name);
                    titleText = 'هل تريد إلغاء الاعتماد؟';
                    confirmBtn = 'إلغاء الاعتماد';
                }

                Swal.fire({
                    title: titleText,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: confirmBtn,
                    cancelButtonText: 'إلغاء',
                    customClass: {
                        confirmButton: 'btn btn-primary me-3',
                        cancelButton: 'btn btn-secondary'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            method: 'GET',
                            success: function(response) {
                                if (response.success) {
                                    toastr.success(response.success);
                                    setTimeout(() => {
                                        window.location.reload();
                                    }, 1500);
                                } else {
                                    Swal.fire('خطأ', response.error, 'error');
                                }
                            },
                            error: function(xhr) {
                                let err = xhr.responseJSON && xhr.responseJSON.error ?
                                    xhr.responseJSON.error :
                                    'حدث خطأ أثناء تنفيذ الإجراء.';
                                Swal.fire('خطأ', err, 'error');
                            }
                        });
                    }
                });
            });

            // زر الرفض
            $(document).on('click', '.reject-action', function(e) {
                e.preventDefault();
                let payrollId = $(this).data('id');
                let name = $(this).data('name');
                let url =
                    "{{ route('accreditation-requests.wps-requests.reject', ['id' => '__ID__', 'name' => '__NAME__']) }}";
                url = url.replace('__ID__', payrollId).replace('__NAME__', name);

                Swal.fire({
                    title: 'أدخل سبب الرفض',
                    input: 'textarea',
                    inputPlaceholder: 'سبب الرفض...',
                    showCancelButton: true,
                    confirmButtonText: 'رفض',
                    cancelButtonText: 'إلغاء',
                    customClass: {
                        confirmButton: 'btn btn-danger me-3',
                        cancelButton: 'btn btn-secondary'
                    },
                    buttonsStyling: false,
                    inputValidator: (value) => {
                        if (!value) return 'يجب إدخال سبب الرفض';
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            data: {
                                rejection_reason: result.value
                            },
                            success: function(response) {
                                if (response.success) {
                                    toastr.success(response.success);
                                    setTimeout(() => {
                                        window.location.reload();
                                    }, 1500);
                                } else {
                                    Swal.fire('خطأ', response.error, 'error');
                                }
                            },
                            error: function(xhr) {
                                let err = xhr.responseJSON && xhr.responseJSON.error ?
                                    xhr.responseJSON.error :
                                    'حدث خطأ أثناء تنفيذ الإجراء.';
                                Swal.fire('خطأ', err, 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection

@section('content')

    {{-- جدول بيانات المسير --}}
    <div class="card">
        <div class="card-header">
            <div class="alert alert-warning text-center" role="alert">
                يُرجى التأكد من مراجعة جميع البيانات جيدًا قبل إعتماد المسير
            </div>
        </div>
        <div class="card-body">
            <table id="table" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th></th>
                        <th>الموظف</th>
                        <th>الراتب الاساسي</th>
                        <th>بدل النقل</th>
                        <th>بدل السكن</th>
                        <th>بدلات اخرى</th>
                        <th>التأمينات</th>
                        <th>الاستقطاعات</th>
                        <th>الحوافز</th>
                        <th>الصافي</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    {{-- مراحل الاعتمادات --}}
    <div class="card mb-4 mt-4">
        <div class="card-header">
            <h5 class="text-primary fw-bold mb-0">مراحل الاعتمادات</h5>
        </div>
        <div class="card-body">
            @if (count($approvers) === 0)
                <p class="text-muted">لا توجد معلومات اعتماد بعد.</p>
            @else
                <div class="d-flex align-items-center justify-content-center">
                    @foreach ($approvers as $approver)
                        <div class="d-flex flex-column align-items-center mx-3">
                            <div class="rounded-circle {{ $approver['status_color'] }}
                                        d-flex align-items-center justify-content-center text-white"
                                style="width: 50px; height: 50px;">
                                {!! $approver['icon'] !!}
                            </div>
                            <span class="mt-2 fw-bold">{{ $approver['name'] }}</span>
                            <small class="text-muted">{{ $approver['label'] }}</small>
                        </div>
                        @if (!$loop->last)
                            <div class="flex-grow-1 border-top {{ $approver['line_color'] }}" style="height: 2px;"></div>
                        @endif
                    @endforeach
                </div>

                @if ($hasRejected)
                    <div class="alert alert-danger mt-4">
                        <h6 class="mb-1">تم رفض المسير</h6>
                        <p class="mb-0">تم الرفض بواسطة: {{ $rejectionApproverName }}</p>
                        @if ($rejectionReason)
                            <p class="mb-0">سبب الرفض: {{ $rejectionReason }}</p>
                        @endif
                    </div>
                @endif

                @if ($wps_payroll->status !== $STATUS_REJECTED && $hasApprovers)
                    <div class="d-flex flex-wrap gap-3 justify-content-center mt-4">
                        @if (($canApprove || auth()->user()->can('إعتماد مسيرات الرواتب')) && $currentApproval)
                            <button class="btn btn-primary approval-action" data-id="{{ $wps_payroll->id }}"
                                data-name="{{ $currentApproval['field'] }}" data-action="approve">
                                <i class="ti ti-check me-1"></i>اعتماد
                            </button>
                            <button class="btn btn-danger reject-action" data-id="{{ $wps_payroll->id }}"
                                data-name="{{ $currentApproval['field'] }}">
                                <i class="ti ti-x me-1"></i>رفض
                            </button>
                        @endif
                        @if (($canRevoke || auth()->user()->can('إعتماد مسيرات الرواتب')) && $lastApprovedField && !$hasRejected)
                            <button class="btn btn-warning approval-action" data-id="{{ $wps_payroll->id }}"
                                data-name="{{ $lastApprovedField }}" data-action="revoke">
                                <i class="ti ti-rotate me-1"></i>إلغاء الاعتماد
                            </button>
                        @endif
                    </div>
                @endif
            @endif
        </div>
    </div>

@endsection
