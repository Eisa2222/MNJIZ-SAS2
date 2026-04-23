@extends('layouts.layoutMaster')

@section('title', 'ارشيف العملاء')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">ارشيف العملاء

        </a>
        <i class="ti ti-star favorite-icon" data-page-name="ارشيف العملاء" data-page-url="{{ url()->current() }}"
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
            var table = $('#trashed-customers-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('customers.trashed') }}",
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
                        data: 'nationality',
                        name: 'nationality'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'deleted_at',
                        name: 'deleted_at'
                    },
                    {
                        data: 'added_by',
                        name: 'added_by'
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
                title: 'هل أنت متأكد من استعادة هذا العميل؟',
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

        function confirmForceDelete(id) {
            Swal.fire({
                title: 'هل أنت متأكد من حذف هذا العميل نهائياً ؟',
                text: "لا يمكن التراجع عن هذا الإجراء!",
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
    </script>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5>ارشيف العملاء</h5>
        </div>
        <div class="card-body">
            <table id="trashed-customers-table" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>م</th>
                        <th>الاسم</th>
                        <th>الجنسية</th>
                        <th>الحالة</th>
                        <th>البريد الإلكتروني</th>
                        <th>تاريخ الحذف</th>
                        <th>أضيف بواسطة</th>
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
