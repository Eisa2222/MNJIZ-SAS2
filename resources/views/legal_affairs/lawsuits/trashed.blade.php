@extends('layouts.layoutMaster')

@section('title', 'ارشيف الدعاوى')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">ارشيف الدعاوى</a>
        <i class="ti ti-star favorite-icon" data-page-name="ارشيف الدعاوى" data-page-url="{{ url()->current() }}"
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
            var table = $('#trashed-lawsuits-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('lawsuits.trashed') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'id',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name',
                        name: 'name',
                        render: function(data, type, row) {
                            if (type === 'display') {
                                var url = "{{ route('lawsuits.show', ':id') }}".replace(':id', row
                                    .id);
                                return '<a href="' + url + '">' + data + '</a>';
                            }
                            return data;
                        }
                    },
                    {
                        data: 'lawsuit_number',
                        name: 'lawsuit_number'
                    },
                    {
                        data: 'department_contract_cases_id',
                        name: 'department_contract_cases_id',
                        render: function(data) {
                            return data ? data : 'غير متوفر';
                        }
                    },
                    {
                        data: 'project_id',
                        name: 'project_id',
                        render: function(data) {
                            return data ? data : 'غير متوفر';
                        }
                    },
                    {
                        data: 'deleted_at',
                        name: 'deleted_at',
                        render: function(data, type, row) {
                            return data ? moment(data).format('DD/MM/YYYY') : 'غير متوفر';
                        }
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

            // التعامل مع استعادة الدعوى
            window.confirmRestore = function(id) {
                Swal.fire({
                    title: 'هل أنت متأكد من استعادة هذه الدعوى',
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
                    if (result.isConfirmed) {
                        $('#restore-form-' + id).submit();
                    }
                });
            }

            // التعامل مع حذف الدعوى نهائيًا
            window.confirmForceDelete = function(id) {
                Swal.fire({
                    title: 'هل أنت متأكد من حذف هذه الدعوى نهائيًا؟',
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
                    if (result.isConfirmed) {
                        $('#force-delete-form-' + id).submit();
                    }
                });
            }
        });
    </script>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5>ارشيف الدعاوى</h5>
        </div>
        <div class="card-body">
            <table id="trashed-lawsuits-table" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>م</th>
                        <th>اسم الدعوى</th>
                        <th>رقم الدعوى</th>
                        <th>نوع الدعوى</th>
                        <th>المشروع</th>
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
