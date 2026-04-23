@extends('layouts.layoutMaster')

@section('title', 'ارشيف العروض')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">ارشيف العروض</a>
        <i class="ti ti-star favorite-icon" data-page-name="ارشيف العروض" data-page-url="{{ url()->current() }}"
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
            var table = $('#trashed-offers-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('operations-center.offers.trashed') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'id',
                        orderable: true,
                        searchable: false
                    },
                    {
                        data: 'offer_name',
                        name: 'offer_name'
                    },
                    {
                        data: 'stage_price_offer_id',
                        name: 'stage_price_offer_id'
                    },
                    {
                        data: 'customer_id',
                        name: 'customer_id'
                    },
                    {
                        data: 'relationship_manager_id',
                        name: 'relationship_manager_id'
                    },
                    {
                        data: 'start_date',
                        name: 'start_date'
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
                    className: 'btn btn-export btn',
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
                responsive: true,
            });
        });

        function confirmRestore(id) {
            Swal.fire({
                title: 'هل أنت متأكد من استعادة هذا العرض',
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
                title: 'هل أنت متأكد من حذف هذا العرض نهائياً ؟',
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
            <h5>ارشيف العروض </h5>
        </div>
        <div class="card-body">
            <table id="trashed-offers-table" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>م </th>
                        <th>اسم العرض</th>
                        <th>مرحلة العرض</th>
                        <th>العميل</th>
                        <th>مسؤول العلاقات</th>
                        <th>تاريخ البداية</th>
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
