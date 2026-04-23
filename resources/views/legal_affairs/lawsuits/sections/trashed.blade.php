@extends('layouts.layoutMaster')

@section('title', 'ارشيف الجلسات')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">ارشيف الجلسات</a>
        <i class="ti ti-star favorite-icon" data-page-name="ارشيف الجلسات" data-page-url="{{ url()->current() }}"
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
            var table = $('#trashed-sessions-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('sessions.trashed') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'id',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'session_name',
                        name: 'session_name'
                    },
                    {
                        data: 'employee_name',
                        name: 'employee.name'
                    },
                    {
                        data: 'lawsuit_name',
                        name: 'lawsuit.name'
                    },
                    {
                        data: 'gregorian_date',
                        name: 'gregorian_date'
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

            // التعامل مع استعادة الجلسة
            window.confirmRestore = function(id) {
                Swal.fire({
                    title: 'هل أنت متأكد من استعادة هذه الجلسة؟',
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
                    if (result.value) {
                        $('#restore-form-' + id).submit();
                    }
                });
            }

            // التعامل مع حذف الجلسة نهائيًا
            window.confirmForceDelete = function(id) {
                Swal.fire({
                    title: 'هل أنت متأكد من حذف هذه الجلسة نهائيًا؟',
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
                    if (result.value) {
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
            <h5> ارشيف الجلسات</h5>
        </div>
        <div class="card-body">
            <table id="trashed-sessions-table" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>م</th>
                        <th>اسم الجلسة</th>
                        <th>المكلف</th>
                        <th>الدعوى</th>
                        <th>التاريخ الميلادي</th>
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
