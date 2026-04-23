@extends('layouts.layoutMaster')

@section('title', 'ارشيف المشاريع')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">ارشيف المشاريع</a>
        <i class="ti ti-star favorite-icon" data-page-name="ارشيف المشاريع" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
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
            var table = $('#trashed-projects-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('projects.trashed') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'project_name_link',
                        name: 'project_name'
                    },
                    {
                        data: 'customer_name',
                        name: 'customer.name'
                    },
                    {
                        data: 'manager_name',
                        name: 'manager.name'
                    },
                    {
                        data: 'start_date',
                        name: 'start_date'
                    },
                   
                    {
                        data: 'deleted_at_formatted',
                        name: 'deleted_at'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                dom: '<"dt-toolbar"<"dt-toolbar-left"l><"dt-toolbar-right"fB>>rt<"row"<"col-12 d-flex align-items-center justify-content-between"ip>>',
                buttons: [{
                    extend: 'collection',
                    className: 'btn btn-export btn-sm',
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
                responsive: true
            });

            // التعامل مع استعادة المشروع
            window.confirmRestore = function(id) {
                Swal.fire({
                    title: 'هل أنت متأكد من استعادة هذا المشروع',
                    icon: 'warning',
                    showCancelButton: true,
                    showConfirmButton: true,
                    showDenyButton: false,
                    buttonsStyling: false,
                    customClass: {
                        popup: 'custom-popup',
                        title: 'custom-title',
                        text: 'custom-text',
                        confirmButton: 'btn btn-success custom-confirm',
                        cancelButton: 'btn btn-danger custom-cancel'
                    },
                    confirmButtonText: 'تأكيد',
                    cancelButtonText: 'إلغاء',
                    reverseButtons: false,
                }).then((result) => {
                    if (result.value) {
                        $('#restore-form-' + id).submit();
                    }
                });
            }

            // التعامل مع حذف المشروع نهائيًا
            window.confirmForceDelete = function(id) {
                Swal.fire({
                    title: 'هل أنت متأكد من حذف هذا المشروع نهائيًا؟',
                    text: "لا يمكن التراجع عن هذه العملية!",
                    icon: 'warning',
                    showCancelButton: true,
                    showConfirmButton: true,
                    showDenyButton: false,
                    buttonsStyling: false,
                    customClass: {
                        popup: 'custom-popup',
                        title: 'custom-title',
                        text: 'custom-text',
                        confirmButton: 'btn btn-success custom-confirm',
                        cancelButton: 'btn btn-danger custom-cancel'
                    },
                    confirmButtonText: 'تأكيد',
                    cancelButtonText: 'إلغاء',
                    reverseButtons: false,
                }).then((result) => {
                    if (result.value) {
                        $('#force-delete-form-' + id).submit();
                    }
                });
            }
            $(document).on('click', '.project-details', function(e) {
                e.preventDefault();
                var projectId = $(this).data('id');
                $.ajax({
                    url: '/projects/' + projectId,
                    method: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        $('#modal_project_name').text(data.project_name);
                        $('#modal_customer_name').text(data.customer_name);
                        $('#modal_manager_name').text(data.manager_name);
                        $('#modal_start_date').text(moment(data.start_date).format('LL'));
                        $('#modal_description').text(data.description);
                        $('#modal_duration').text(data.duration);

                        $('#modal_attachments').empty();
                        if (data.attachments && data.attachments.length > 0) {
                            data.attachments.forEach(function(attachment) {
                                var listItem = '<li><a href="' + attachment.url +
                                    '" target="_blank">' + attachment.name +
                                    '</a></li>';
                                $('#modal_attachments').append(listItem);
                            });
                        } else {
                            $('#modal_attachments').append('<li>لا توجد مرفقات</li>');
                        }
                        $('#projectModal').modal('show');
                    },
                    error: function() {
                        alert('حدث خطأ أثناء جلب تفاصيل المشروع.');
                    }
                });
            });
        });
    </script>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5>ارشيف المشاريع</h5>
        </div>
        <div class="card-body">
            <table id="trashed-projects-table" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>م</th>
                        <th>اسم المشروع</th>
                        <th>العميل</th>
                        <th>مدير المشروع</th>
                        <th>تاريخ البدء</th>
                        <th>تاريخ الحذف</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- سيتم تعبئة البيانات بواسطة DataTables -->
                </tbody>
            </table>
        </div>
    </div>
@endsection
