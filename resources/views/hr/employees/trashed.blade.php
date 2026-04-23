@extends('layouts.layoutMaster')

@section('title', 'ارشيف الموظفين')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">ارشيف الموظفين</a>
        <i class="ti ti-star favorite-icon" data-page-name="ارشيف الموظفين" data-page-url="{{ url()->current() }}"
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
            var table = $('#trashed-employees-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('hr.employees.trashed') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'job_title',
                        name: 'job_title'
                    },
                    {
                        data: 'work_email',
                        name: 'work_email'
                    },
                    {
                        data: 'mobile',
                        name: 'mobile'
                    },
                    {
                        data: 'deleted_at',
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
        });

        function confirmRestore(id) {
            Swal.fire({
                title: 'هل أنت متأكد من استعادة هذا الموظف؟',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'نعم، استعادة',
                cancelButtonText: 'إلغاء',
                customClass: {
                    confirmButton: 'btn btn-success',
                    cancelButton: 'btn btn-outline-secondary ms-2'
                },
                buttonsStyling: false
            }).then(function(result) {
                if (result.isConfirmed) {
                    $('#restore-form-' + id).submit();
                }
            });
        }

        function confirmForceDelete(id) {
            Swal.fire({
                title: 'هل أنت متأكد من حذف هذا الموظف نهائيًا؟',
                text: "لا يمكن التراجع عن هذه العملية!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'نعم، حذف نهائي',
                cancelButtonText: 'إلغاء',
                customClass: {
                    confirmButton: 'btn btn-danger',
                    cancelButton: 'btn btn-outline-secondary ms-2'
                },
                buttonsStyling: false
            }).then(function(result) {
                if (result.isConfirmed) {
                    $('#force-delete-form-' + id).submit();
                }
            });
        }
    </script>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5>ارشيف الموظفين</h5>
        </div>
        <div class="card-body">
            <table id="trashed-employees-table" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>م</th>
                        <th>الاسم</th>
                        <th>المسمى الوظيفي</th>
                        <th>بريد العمل</th>
                        <th>الجوال</th>
                        <th>تاريخ الحذف</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- سيتم تعبئة الجدول بواسطة DataTables -->
                </tbody>
            </table>
        </div>
    </div>
@endsection
