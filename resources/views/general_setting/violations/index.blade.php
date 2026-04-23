@extends('layouts.layoutMaster')

@section('title', 'إعدادات أنواع المخالفات')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">إعدادات أنواع المخالفات</a>
        <i class="ti ti-star favorite-icon" data-page-name="إعدادات أنواع المخالفات" data-page-url="{{ url()->current() }}"
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

@section('page-script')
    @vite(['resources/assets/js/form-validation.js'])
    <script>
        $(document).ready(function() {
            // تهيئة Select2 لتحديد التصنيف
            $('.select2').select2({
                placeholder: function() {
                    return $(this).data('placeholder');
                },
                allowClear: true,
                width: '100%',
                language: 'ar',
                dir: 'rtl'
            });

            var table = $('#setting-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route($route . '.index') }}",
                },
                columns: [{
                        data: 'checkbox',
                        name: 'checkbox',
                        orderable: false,
                        searchable: false,
                        className: 'custom-checkbox',
                        render: function(data, type, full, meta) {
                            return '<input type="checkbox" class="row-checkbox" value="' + full.id +
                                '">';
                        }
                    },
                    {
                        data: 'description',
                        name: 'description',
                        width: '25%'
                    },
                    {
                        data: 'penalty_first',
                        name: 'penalty_first',
                        width: '15%',
                    },
                    {
                        data: 'penalty_second',
                        name: 'penalty_second',
                        width: '15%',
                    },
                    {
                        data: 'penalty_third',
                        name: 'penalty_third',
                        width: '15%',
                    },
                    {
                        data: 'penalty_fourth',
                        name: 'penalty_fourth',
                        width: '15%',
                    },
                    {
                        data: 'extra_deduction',
                        name: 'extra_deduction',
                        width: '15%',
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        title: 'الإجراءات'
                    }
                ],
                order: [
                    [7, 'desc']
                ],
                dom: '<"dt-toolbar"' +
                    '<"dt-toolbar-left"l>' +
                    '<"dt-toolbar-right"fB>' +
                    '>' +
                    'rt' +
                    '<"row"<"col-12 d-flex align-items-center justify-content-between"ip>>',
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
                            },
                            {
                                extend: 'print',
                                text: 'طباعة'
                            },
                            {
                                text: 'حذف المحدد',
                                className: 'btn btn-default btn-delete-selected',
                                action: function(e, dt, node, config) {
                                    var selectedIds = [];
                                    $('.row-checkbox:checked').each(function() {
                                        selectedIds.push($(this).val());
                                    });
                                    confirmDeleteSelectedmss(selectedIds,
                                        "{{ route('mass.delete') }}", 'SettingsViolation');
                                }
                            }
                        ]
                    },
                    {
                        text: '<i class="fas fa-plus-circle me-1"></i> إضافة نوع جديد',
                        className: 'btn btn-primary btn-add',
                        action: function(e, dt, node, config) {
                            window.location.href = "{{ route($route . '.create') }}";
                        }
                    }
                ],
                language: {
                    url: "{{ asset('assets/json/ar.json') }}"
                },
                responsive: true,
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
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif



    <div class="card">
        <div class="card-body">
            <table id="setting-table" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th class="custom-checkbox">
                            <input type="checkbox" id="select-all">
                        </th>
                        <th>نوع المخالفة</th>
                        <th>أول مرة</th>
                        <th>ثاني مرة</th>
                        <th>ثالثة مرة</th>
                        <th>رابع مرة</th>
                        <th>ملاحظات إضافية</th>
                        {{-- <th>تاريخ الإضافة</th> --}}
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
