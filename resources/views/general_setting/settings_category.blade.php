{{-- resources/views/settings.blade.php --}}
@extends('layouts.layoutMaster')

@section('title', 'إعدادات التصنيفات')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> إعدادات التصنيفات
        </a>
        <i class="ti ti-star favorite-icon" data-page-name="إعدادات التصنيفات" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection


@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    <!-- تحميل jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- تحميل Bootstrap Bundle JS لتحسين مظهر المودالات وغيرها -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <!-- تحميل Sortable.js -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <!-- تحميل SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- تحميل Toastr -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <!-- ملفات JavaScript الإضافية الخاصة بك عبر Vite -->
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('page-style')
    @vite(['resources/css/dataTable.css'])
    <style>
        /* تأكد من أن قائمة Select2 تظهر فوق المودال */
        .select2-container {
            z-index: 1060 !important;
            /* أعلى من Bootstrap modal z-index الذي هو 1050 */
        }
    </style>
@endsection

@section('page-script')
    @vite(['resources/assets/js/form-validation.js'])
    <script>
        $(document).ready(function() {
            // تهيئة DataTable
            var table = $('#setting-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('settings-category.index') }}",
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
                        data: 'name',
                        name: 'name',
                        orderable: false

                    }, {
                        data: 'status',
                        name: 'status',
                        orderable: false

                    }, {
                        data: 'created_at',
                        name: 'created_at',
                        orderable: false

                    }, {
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
                            }, {
                                extend: 'excel',
                                text: 'إكسل'
                            },
                            {
                                extend: 'print',
                                text: 'طباعة'
                            }, {
                                text: 'حذف المحدد',
                                className: 'btn btn-delete-selected',
                                action: function(e, dt, node, config) {
                                    var selectedIds = [];
                                    $('.row-checkbox:checked').each(function() {
                                        selectedIds.push($(this).val());
                                    });

                                    if (selectedIds.length > 0) {
                                        confirmDeleteSelected(selectedIds,
                                            "{{ route('settings-category.massDelete') }}");
                                    } else {
                                        toastr.warning('يرجى تحديد عنصر على الأقل للحذف.');
                                    }
                                }
                            }
                        ]
                    },
                    {
                        text: '<i class="fas fa-plus-circle me-1"></i> إضافة تصنيف جديد',
                        className: 'btn btn-primary btn-add',
                        action: function(e, dt, node, config) {
                            $('#addSectionModal').modal('show');
                        }
                    }
                ],
                language: {
                    url: "{{ asset('assets/json/ar.json') }}"
                },
                responsive: true,
                createdRow: function(row, data, dataIndex) {
                    $(row).attr('data-id', data.id).addClass('sortable-row');
                }
            });


            // تهيئة Sortable.js على tbody
            var sortable = Sortable.create(document.querySelector('#setting-table tbody'), {
                animation: 150,
                handle: '.sortable-handle', // يجب إضافة عنصر يحمل هذا الكلاس ليكون قابل للسحب
                onEnd: function(evt) {
                    var order = [];
                    $('#setting-table tbody tr').each(function(index) {
                        order.push({
                            id: $(this).data('id'),
                            position: index + 1
                        });
                    });


                    // إرسال الترتيب الجديد إلى السيرفر عبر AJAX
                    $.ajax({
                        url: "{{ route('settings-category.reorder') }}",
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            order: order
                        },
                        success: function(response) {
                            if (response.success) {
                                toastr.success(response.message);
                                table.ajax.reload(null,
                                    false); // إعادة تحميل الجدول بدون إعادة الصفحة
                            } else {
                                toastr.error(response.message);
                            }
                        },
                        error: function(xhr) {
                            toastr.error('حدث خطأ أثناء حفظ الترتيب.');

                        }
                    });
                }

            });

            // إضافة عنصر sortable-handle داخل كل صف عند تحميل البيانات
            table.on('draw', function() {
                $('#setting-table tbody tr').each(function() {
                    if ($(this).find('.sortable-handle').length === 0) {
                        $(this).find('td').first().append(
                            '<span class="sortable-handle ms-2" title="حرك العنصر لاعادة الترتيب" style="cursor: move;"><i class="fas fa-arrows"></i></span>'
                        );
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


            // إرسال نموذج الإضافة عبر AJAX
            $('#addSectionModal form').submit(function(e) {
                e.preventDefault();
                var form = $(this);
                var url = form.attr('action');
                var method = form.attr('method');
                var data = form.serialize();

                $.ajax({
                    url: url,
                    method: method,
                    data: data,
                    success: function(response) {
                        if (response.success) {
                            $('#addSectionModal').modal('hide');
                            form.trigger('reset');
                            // إزالة الحقول الإضافية بعد الإضافة الناجحة
                            $('#category-names-container').html(`
                            <div class="mb-3">
                                <label class="form-label" for="categoryName">اسم التصنيف الرئيسي <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="names[]" required />
                                <div class="valid-feedback">تم التحقق بنجاح!</div>
                                <div class="invalid-feedback">اسم التصنيف الرئيسي مطلوب.</div>
                            </div>
                        `);
                            // إعادة تحميل الـ DataTable
                            table.ajax.reload(null, false);
                            toastr.success(response.message);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                toastr.error(value[0]);
                            });
                        } else {
                            toastr.error('حدث خطأ أثناء حفظ البيانات.');
                        }
                    }
                });
            });

            // إرسال نموذج التعديل عبر AJAX
            $('#editSectionModal form').submit(function(e) {
                e.preventDefault();
                var form = $(this);
                var url = form.attr('action');
                var method = form.attr('method');
                var data = form.serialize();

                $.ajax({
                    url: url,
                    method: method,
                    data: data,
                    success: function(response) {
                        if (response.success) {
                            $('#editSectionModal').modal('hide');
                            // إعادة تحميل الـ DataTable
                            table.ajax.reload(null, false);
                            toastr.success(response.message);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                toastr.error(value[0]);
                            });
                        } else {
                            toastr.error('حدث خطأ أثناء تحديث البيانات.');
                        }
                    }
                });
            });

            // التعامل مع زر التعديل لفتح المودال وملء البيانات
            $(document).on('click', '.btn-edit', function() {
                var id = $(this).data('id');
                var editForm = $('#editSettingForm');
                var modal = $('#editSectionModal');

                $.ajax({
                    url: "{{ route('settings-category.edit', ':id') }}".replace(':id', id),
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            editForm.attr('action',
                                "{{ route('settings-category.update', ':id') }}".replace(
                                    ':id', id));
                            // تعيين قيمة اسم التصنيف في الحقل
                            $('#modalSettingNameEdit').val(response.data.name);

                            // فتح المودال
                            modal.modal('show');
                        } else {
                            toastr.error('فشل في جلب بيانات التصنيف.');
                        }
                    },
                    error: function(xhr) {
                        toastr.error('حدث خطأ أثناء جلب بيانات التصنيف.');
                    }
                });
            });

            // التعامل مع زر الحذف عبر AJAX
            $(document).on('click', '.btn-delete', function() {
                var id = $(this).data('id');

                Swal.fire({
                    title: 'هل أنت متأكد من عملية الحذف؟',
                    text: "لا يمكن التراجع عن هذا الإجراء!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'تأكيد',
                    cancelButtonText: 'إلغاء',
                    reverseButtons: false,
                    customClass: {
                        popup: 'custom-popup',
                        title: 'custom-title',
                        text: 'custom-text',
                        confirmButton: 'btn btn-success custom-confirm',
                        cancelButton: 'btn btn-danger custom-cancel'
                    },
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('settings-category.destroy', ':id') }}".replace(
                                ':id', id),
                            method: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    table.ajax.reload(null, false);
                                    toastr.success(response.message);
                                } else {
                                    toastr.error(response.message);
                                }
                            },
                            error: function(xhr) {
                                toastr.error('حدث خطأ أثناء حذف التصنيف.');
                            }
                        });
                    }
                });
            });

            // تغيير الحالة
            $(document).on('click', '.status-toggle', function() {
                var badge = $(this);
                var id = badge.data('id');

                $.ajax({
                    url: "{{ route('settings-category.toggleStatus', ':id') }}".replace(':id', id),
                    method: 'GET',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            if (response.status === 'active') {
                                badge.removeClass('bg-danger').addClass('bg-success').text(
                                    'نشط');
                            } else if (response.status === 'inactive') {
                                badge.removeClass('bg-success').addClass('bg-danger').text(
                                    'غير نشط');
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

            // إضافة حقول جديدة لإدخال تصنيفات متعددة
            $('#add-category-btn').click(function(e) {
                e.preventDefault();
                var container = $('#category-names-container');
                var newField = `
                <div class="mb-3 category-field">
                    <label class="form-label">اسم التصنيف الرئيسي <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" class="form-control" name="names[]" required />
                        <button class="btn btn-danger remove-category-field" type="button"><i class="ti ti-trash"></i></button>
                    </div>
                    <div class="valid-feedback">تم التحقق بنجاح!</div>
                    <div class="invalid-feedback">اسم التصنيف الرئيسي مطلوب.</div>
                </div>
            `;
                container.append(newField);
            });

            // إزالة حقل تصنيف معين
            $(document).on('click', '.remove-category-field', function() {
                $(this).closest('.category-field').remove();
            });
        });
    </script>
@endsection

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Add Category Modal -->
    <div class="modal fade" id="addSectionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <!-- زيادة حجم المودال إلى lg -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">إضافة تصنيفات رئيسية</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addCategoryForm" class="needs-validation" novalidate
                        action="{{ route('settings-category.store') }}" method="POST">
                        @csrf

                        <div id="category-names-container">
                            <!-- الحقل الأول للإدخال -->
                            <div class="mb-3">
                                <label class="form-label" for="categoryName">اسم التصنيف الرئيسي <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="names[]" required />
                                <div class="valid-feedback">تم التحقق بنجاح!</div>
                                <div class="invalid-feedback">اسم التصنيف الرئيسي مطلوب.</div>
                            </div>
                        </div>

                        <!-- زر لإضافة حقول جديدة -->
                        <div class="mb-3">
                            <button id="add-category-btn" class="btn btn-secondary" type="button">
                                <i class="ti ti-plus"></i> إضافة تصنيف آخر
                            </button>
                        </div>

                        <!-- زر الحفظ -->
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">حفظ التصنيفات</button>
                            <button type="reset" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!--/ Add Category Modal -->

    <!-- Edit Category Modal -->
    <div class="modal fade" id="editSectionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <!-- زيادة حجم المودال -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تعديل بيانات التصنيف</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editSettingForm" class="needs-validation" novalidate method="POST">
                        @csrf
                        @method('PUT')

                        <!-- تعديل اسم التصنيف الرئيسي -->
                        <div class="mb-3">
                            <label class="form-label" for="modalSettingNameEdit">اسم التصنيف الرئيسي <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="modalSettingNameEdit" name="name" required />
                            <div class="valid-feedback">تم التحقق بنجاح!</div>
                            <div class="invalid-feedback">اسم التصنيف الرئيسي مطلوب.</div>
                        </div>

                        <!-- زر الحفظ -->
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">تحديث</button>
                            <button type="reset" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!--/ Edit Category Modal -->

    <div class="card">
        <div class="card-body">
            <table id="setting-table" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all"></th>
                        <th>اسم التصنيف</th>
                        <th>الحالة</th>
                        <th>تاريخ الإضافة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- يتم ملء tbody عبر DataTables باستخدام AJAX -->
                </tbody>
            </table>
        </div>
    </div>
@endsection
