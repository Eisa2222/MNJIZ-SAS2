@extends('layouts.layoutMaster')

@section('title', 'إعدادات النماذج')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> إعدادات النماذج </a>
        <i class="ti ti-star favorite-icon" data-page-name="إعدادات النماذج " data-page-url="{{ url()->current() }}"
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
    <script>
        $(document).ready(function() {
            // تهيئة DataTable مع تأكيد أن الـ URL يعيد البيانات بشكل صحيح
            var table = $('#setting-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route($route . '.index') }}", // تأكد من أن هذا الـRoute يعيد البيانات
                    type: "GET" // نوع الطلب
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
                }, {
                    data: 'name',
                    name: 'name'
                }, {
                    data: 'status',
                    name: 'status'
                }, {
                    data: 'created_at',
                    name: 'created_at'
                }, {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }],
                order: [
                    [3, 'desc']
                ], // ترتيب حسب العمود الثالث
                dom: '<"dt-toolbar"' +
                    '<"dt-toolbar-left"' +
                    'l' +
                    '>' +
                    '<"dt-toolbar-right"' +
                    'f' +
                    'B' +
                    '>' +
                    '>' +
                    'rt' +
                    '<"row"' +
                    '<"col-12 d-flex align-items-center justify-content-between"' +
                    'i' +
                    'p' +
                    '>' +
                    '>',
                buttons: [{
                        extend: 'collection',
                        className: 'btn btn-export btn',
                        text: 'الاجراءات',
                        buttons: [{
                                extend: 'copy',
                                text: 'نسخ'
                            }, {
                                extend: 'excel',
                                text: 'إكسل'
                            },

                            // {
                            //     text: 'حذف المحدد',
                            //     className: 'btn btn-default btn-delete-selected',
                            //     action: function(e, dt, node, config) {
                            //         var selectedIds = [];
                            //         $('.row-checkbox:checked').each(function() {
                            //             selectedIds.push($(this).val());
                            //         });

                            //         confirmDeleteSelected(selectedIds,
                            //             "{{ route($route . '.massDelete') }}");
                            //     }
                            // }

                        ]
                    },
                    // @can('إضافة اعداد')
                   
                    //     {
                    //         text: '<i class="fas fa-plus-circle me-1"></i> إضافة نموذج جديد',
                    //         className: 'btn btn-primary btn-add',
                    //         action: function(e, dt, node, config) {
                    //             window.location.href = "{{ route($route . '.create') }}";
                    //         }
                    //     }
                    // @endcan

                ],
                language: {
                    url: "{{ asset('assets/json/ar.json') }}" // ملف الترجمة للعربية
                },
                responsive: true, // لتفعيل الجداول المتجاوبة
            });

            // تحديد الكل
            $('#select-all').on('click', function() {
                var rows = $('#setting-table').DataTable().rows({
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
                        <th class="custom-checkbox"><input type="checkbox" id="select-all"></th> <!-- Checkbox تحديد الكل -->
                        {{-- <th>م</th> --}}
                        <th>اسم النموذج</th>
                        <th>الحالة</th>
                        <th>تاريخ الإضافة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- سيتم تعبئة الجدول بواسطة DataTables -->
                </tbody>
            </table>
        </div>
    </div>
    <script>
        // التعامل مع زر التعديل لفتح المودال وملء البيانات
        $(document).on('click', '.btn-edit', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var editForm = $('#editSettingForm');

            var actionUrl = "{{ route($route . '.update', ':id') }}".replace(':id', id);

            editForm.attr('action', actionUrl);
            $('#modalSettingNameEdit').val(name);
            $('#editSectionModal').modal('show');
        });


        // التعامل مع تغيير الحالة عند النقر على الشارة
        $(document).on('click', '.status-toggle', function() {
            var badge = $(this);
            var id = badge.data('id');

            $.ajax({
                url: "{{ route($route . '.editStatus', ':id') }}".replace(':id', id),
                method: 'get',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        if (response.status === 'active') {
                            badge.removeClass('bg-danger').addClass('bg-success').text('نشط');
                        } else if (response.status === 'inactive') {
                            badge.removeClass('bg-success').addClass('bg-danger').text('غير نشط');
                        }
                        toastr.success('تم تغيير الحالة بنجاح.');
                    } else {
                        toastr.error('فشل في تغيير الحالة.');
                    }
                },
                error: function(xhr) {
                    toastr.error('حدث خطأ أثناء تغيير الحالة.');
                }
            });
        });
    </script>
@endsection
