{{-- resources/views/hr/leave_balances/index.blade.php --}}
@extends('layouts.layoutMaster')

@section('title', 'أرصدة الإجازات')

@section('breadcrumb')
    <li><a href="#"> الموارد البشرية</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> أرصدة الإجازات</a>
        <i class="ti ti-star favorite-icon" data-page-name=" أرصدة الإجازات" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection


@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-style')
    @vite(['resources/css/dataTable.css'])
@endsection

@section('page-script')
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.select2').select2({
                placeholder: function() {
                    return $(this).data('placeholder');
                },
                allowClear: true,
                width: '100%',
                language: 'ar',
                dir: 'rtl'
            });

            var table = $('#leave-balances-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('hr.leave-balances.index') }}",
                    data: function(d) {
                        d.year = $('#filter-year').val();
                        d.employee_id = $('#filter-employee').val();
                    }
                },
                columns: [{
                        data: 'checkbox',
                        name: 'checkbox',
                        orderable: false,
                        searchable: false,
                        className: 'custom-checkbox'
                    },
                    {
                        data: 'employee_profile',
                        name: 'employee_profile',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'employee_name',
                        name: 'employee_name'
                    },
                    {
                        data: 'total_days',
                        name: 'total_days',
                        className: 'text-center'
                    },
                    {
                        data: 'used_days',
                        name: 'used_days',
                        className: 'text-center'
                    },
                    {
                        data: 'remaining_days',
                        name: 'remaining_days',
                        className: 'text-center'
                    },
                    // {
                    //     data: 'action',
                    //     name: 'action',
                    //     orderable: false,
                    //     searchable: false,
                    //     className: 'text-center'
                    // }
                ],
                order: [
                    [2, 'asc']
                ],
                dom: '<"dt-toolbar"' +
                    '<"dt-toolbar-left d-flex flex-column flex-sm-row align-items-start align-items-sm-center"' +
                    'l' +
                    '>' +
                    '<"dt-toolbar-right d-flex flex-column flex-sm-row align-items-end align-items-sm-center justify-content-between"' +
                    'f' +
                    'B' +
                    '>' +
                    '>' +
                    'rt' +
                    '<"row mt-3"' +
                    '<"d-flex flex-column flex-sm-row align-items-center justify-content-between w-100"' +
                    'i' +
                    '<"pagination-wrapper overflow-auto w-100"' +
                    'p' +
                    '>' +
                    '>' +
                    '>',
                buttons: [{
                    extend: 'collection',
                    className: 'btn btn-export',
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
                }
            });

            // Filters
            $('#filter-employee').change(function() {
                table.ajax.reload();
            });

            // Select all checkbox
            $('#select-all').on('click', function() {
                var rows = table.rows({
                    search: 'applied'
                }).nodes();
                $('input.row-checkbox', rows).prop('checked', this.checked);
            });
        });
    </script>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <table id="leave-balances-table" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th class="custom-checkbox">
                            <input type="checkbox" id="select-all">
                        </th>
                        <th></th>
                        <th>الموظف</th>
                        <th>إجمالي الأيام</th>
                        <th>الأيام المستخدمة</th>
                        <th>الأيام المتبقية</th>
                        {{-- <th>الإجراءات</th> --}}
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
@endsection
