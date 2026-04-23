@extends('layouts.layoutMaster')

@section('title', 'سجلات الرسائل المرسلة')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center"><a href="{{ route('messageLogs.index') }}">سجلات الرسائل</a>
        <i class="ti ti-star favorite-icon" data-page-name="سجل الرسائل" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
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
            $('.select2').select2({
                placeholder: function() {
                    return $(this).data('placeholder');
                },
                allowClear: true,
                width: '100%',
                language: 'ar',
                dir: 'rtl'
            });

            var table = $('#message-logs-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('messageLogs.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'sender',
                        name: 'sender',
                        className: 'text-nowrap'
                    },
                    {
                        data: 'message_text',
                        name: 'message_text',
                        className: 'table-ellipsis'
                    },
                    {
                        data: 'platform',
                        name: 'platform',
                        className: 'text-nowrap'
                    },
                    {
                        data: 'recipients_buttons',
                        name: 'recipients_buttons',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        className: 'text-nowrap'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [5, 'desc']
                ],
                language: {
                    url: "{{ asset('assets/json/ar.json') }}"
                },
                responsive: true,
                createdRow: function(row, data, dataIndex) {
                    $(row).find('td.table-ellipsis').each(function() {
                        var cellText = $(this).text();
                        $(this).html('<div class="cell-content"><span>' + cellText +
                            '</span></div>');
                    });
                }
            });

            // التعامل مع أزرار عرض المستلمين
            $(document).on('click', '.view-recipients', function() {
                var logId = $(this).data('id');
                var type = $(this).data('type');

                // إجراء AJAX لجلب المستلمين
                $.ajax({
                    url: "{{ route('messageLogs.getRecipients', ['id' => ':id', 'type' => ':type']) }}"
                        .replace(':id', logId)
                        .replace(':type', type),
                    method: 'GET',
                    success: function(response) {
                        var recipients = response.recipients;
                        var list = $('#' + type + 'List');
                        list.empty(); // مسح القائمة السابقة

                        if (recipients.length > 0) {
                            recipients.forEach(function(recipient) {
                                var item = '';
                                switch (type) {
                                    case 'customer':
                                        item =
                                            '<li class="list-group-item"><i class="ti ti-user-check text-success mx-1"></i>' +
                                            recipient.name + ' (رقم الاتصال: ' +
                                            recipient.contact_number + ')</li>';
                                        break;
                                    case 'employee':
                                        item =
                                            '<li class="list-group-item"><i class="ti ti-user text-info mx-1"></i>' +
                                            recipient.name + ' (رقم الجوال: ' +
                                            recipient.mobile + ')</li>';
                                        break;
                                    case 'opponent':
                                        item =
                                            '<li class="list-group-item"><i class="ti ti-user-x text-danger mx-1"></i>' +
                                            recipient.name + ' (رقم الاتصال: ' +
                                            recipient.phone + ')</li>';
                                        break;
                                    default:
                                        item =
                                            '<li class="list-group-item">غير معروف</li>';
                                }
                                list.append(item);
                            });
                        } else {
                            list.append('<li class="list-group-item">لا يوجد مستلمين</li>');
                        }

                        // عرض المودال المناسب بناءً على نوع المستلم
                        var modalId = '';
                        switch (type) {
                            case 'customer':
                                modalId = 'customersModal';
                                break;
                            case 'employee':
                                modalId = 'employeesModal';
                                break;
                            case 'opponent':
                                modalId = 'opponentsModal';
                                break;
                            default:
                                modalId = 'recipientsModal';
                        }

                        var recipientsModal = new bootstrap.Modal(document.getElementById(
                            modalId));
                        recipientsModal.show();
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'خطأ!',
                            text: xhr.responseJSON.error ||
                                'حدث خطأ أثناء جلب المستلمين.',
                            icon: 'error',
                            confirmButtonText: 'حسناً'
                        });
                    }
                });
            });

            // تحديد الكل
            $('#select-all').on('click', function() {
                var rows = table.rows({
                    'search': 'applied'
                }).nodes();
                $('input[type="checkbox"]', rows).prop('checked', this.checked);
            });
        });
    </script>
@endsection

@section('content')
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">إجمالي الرسائل</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $totalMessages }}</h4>
                            </div>
                            <small class="mb-0">جميع الرسائل المرسلة</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="ti ti-message-circle ti-26px"></i>
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
                            <span class="text-heading">رسائل الموظفين</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $employeeMessagesCount }}</h4>
                            </div>
                            <small class="mb-0">رسائل مرسلة للموظفين</small>
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

        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">رسائل العملاء</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $customerMessagesCount }}</h4>
                            </div>
                            <small class="mb-0">رسائل مرسلة للعملاء</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-secondary">
                                <i class="ti ti-user-check ti-26px"></i>
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
                            <span class="text-heading">رسائل الخصوم</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $opponentMessagesCount }}</h4>
                            </div>
                            <small class="mb-0">رسائل مرسلة للخصوم</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-danger">
                                <i class="ti ti-user-x ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table id="message-logs-table" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>المرسل</th>
                        <th>نص الرسالة</th>
                        <th>منصة الرسالة</th>
                        <th>المستلمون</th>
                        <th>تاريخ الإرسال</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- سيتم تعبئة البيانات بواسطة DataTables -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- مودال عرض العملاء -->
    <div class="modal fade" id="customersModal" tabindex="-1" aria-labelledby="customersModalLabel" aria-hidden="true"
        style="z-index: 9999">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">قائمة العملاء</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                    <ul id="customerList" class="list-group">
                        <!-- سيتم ملؤها بواسطة JavaScript -->
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- مودال عرض الموظفين -->
    <div class="modal fade" id="employeesModal" tabindex="-1" aria-labelledby="employeesModalLabel" aria-hidden="true"
        style="z-index: 9999">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">قائمة الموظفين</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                    <ul id="employeeList" class="list-group">
                        <!-- سيتم ملؤها بواسطة JavaScript -->
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- مودال عرض الخصوم -->
    <div class="modal fade" id="opponentsModal" tabindex="-1" aria-labelledby="opponentsModalLabel" aria-hidden="true"
        style="z-index: 9999">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">قائمة الخصوم</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                    <ul id="opponentList" class="list-group">
                        <!-- سيتم ملؤها بواسطة JavaScript -->
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- مودال عام للمستلمين (اختياري) -->
    <div class="modal fade" id="recipientsModal" tabindex="-1" aria-labelledby="recipientsModalLabel"
        aria-hidden="true" style="z-index: 9999">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">قائمة المستلمين</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                    <ul id="recipientsList" class="list-group">
                        <!-- سيتم ملؤها بواسطة JavaScript -->
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
