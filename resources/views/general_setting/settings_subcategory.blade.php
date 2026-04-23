{{-- resources/views/GeneralSetting/settings_subcategory.blade.php --}}
@extends('layouts.layoutMaster')

@section('title', 'إعدادات التصنيفات الفرعية')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> إعدادات التصنيفات الفرعية</a>
        <i class="ti ti-star favorite-icon" data-page-name="إعدادات التصنيفات الفرعية" data-page-url="{{ url()->current() }}"
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

@endsection

@section('page-script')
    <script>
        $(document).ready(function() {
            // تهيئة Select2 لحقول اختيار التصنيف الرئيسي في نماذج الإضافة والتعديل مع تحديد dropdownParent
            $('.select2').each(function() {
                var $this = $(this);
                var modal = $this.closest('.modal');
                $this.select2({
                    placeholder: 'اختر تصنيفًا رئيسيًا',
                    allowClear: true,
                    width: '100%',
                    language: 'ar',
                    dir: 'rtl',
                    dropdownParent: $this.parent(),
                });
            });

            // تهيئة DataTable
            var table = $('#subcategory-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('settings-subcategory.index') }}",
                    data: function(d) {
                        d.status = $('#filter-status').val();
                        d.category = $('#filter-category').val();
                    },
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
                    name: 'name',
                    orderable: false

                }, {
                    data: 'category_name',
                    name: 'category_name',
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
                }],

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
                                className: 'btn btn-defult btn-delete-selected',
                                action: function(e, dt, node, config) {
                                    var selectedIds = [];
                                    $('.row-checkbox:checked').each(function() {
                                        selectedIds.push($(this).val());
                                    });
                                    confirmDeleteSelected(selectedIds,
                                        "{{ route('settings-subcategory.massDelete') }}");
                                }
                            },
                        ]
                    },
                    {
                        text: '<i class="fas fa-plus-circle me-1"></i> إضافة تصنيف فرعي جديد',
                        className: 'btn btn-primary btn-add',
                        action: function(e, dt, node, config) {
                            $('#addSubcategoryModal').modal('show');
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
            var sortable = Sortable.create(document.querySelector('#subcategory-table tbody'), {
                animation: 150,
                handle: '.sortable-handle', // يجب إضافة عنصر يحمل هذا الكلاس ليكون قابل للسحب
                onEnd: function(evt) {
                    var order = [];
                    $('#subcategory-table tbody tr').each(function(index) {
                        order.push({
                            id: $(this).data('id'),
                            position: index + 1
                        });
                    });

                    // إرسال الترتيب الجديد إلى السيرفر عبر AJAX
                    $.ajax({
                        url: "{{ route('settings-subcategory.reorder') }}",
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

            table.on('draw', function() {
                $('#subcategory-table tbody tr').each(function() {
                    if ($(this).find('.sortable-handle').length === 0) {
                        $(this).find('td').first().append(
                            '<span class="sortable-handle ms-2" title="حرك العنصر لاعادة الترتيب" style="cursor: move;"><i class="fas fa-arrows"></i></span>'
                        );
                    }
                });
            });

            // تحديث الجدول عند تغيير الفلاتر
            $('#filter-status, #filter-category').change(function() {
                table.draw();
            });

            // تحديد الكل
            $('#select-all').on('click', function() {
                var rows = $('#subcategory-table').DataTable().rows({
                    'search': 'applied'
                }).nodes();
                $('input[type="checkbox"]', rows).prop('checked', this.checked);
            });

            // إرسال نموذج الإضافة عبر AJAX
            $('#addSubcategoryForm').submit(function(e) {
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
                            $('#addSubcategoryModal').modal('hide');
                            form.trigger('reset');
                            // إزالة الحقول الإضافية بعد الإضافة الناجحة
                            $('#subcategory-names-container').html(`
                                <div class="mb-3 subcategory-field">
                                    <label class="form-label">اسم التصنيف الفرعي <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="names[]" required />
                                        <button class="btn btn-danger remove-subcategory-field" type="button"><i class="ti ti-trash"></i></button>
                                    </div>
                                    <div class="valid-feedback">تم التحقق بنجاح!</div>
                                    <div class="invalid-feedback">اسم التصنيف الفرعي مطلوب.</div>
                                </div>
                            `);
                            // إعادة تحميل الـ DataTable
                            table.ajax.reload();
                            toastr.success(response.message);
                            // إعادة تهيئة Select2 بعد إعادة التحميل
                            $('.select2').select2({
                                placeholder: 'اختر تصنيفًا رئيسيًا',
                                allowClear: true,
                                width: '100%',
                                language: 'ar',
                                dir: 'rtl',
                                dropdownParent: $('#addSubcategoryModal')
                            });
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
            $('#editSubcategoryForm').submit(function(e) {
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
                            $('#editSubcategoryModal').modal('hide');
                            // إعادة تحميل الـ DataTable
                            table.ajax.reload();
                            toastr.success(response.message);
                            // إعادة تهيئة Select2 بعد إعادة التحميل
                            $('.select2').select2({
                                placeholder: 'اختر تصنيفًا رئيسيًا',
                                allowClear: true,
                                width: '100%',
                                language: 'ar',
                                dir: 'rtl',
                                dropdownParent: $('#editSubcategoryModal')
                            });
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
                var editForm = $('#editSubcategoryForm');
                var modal = $('#editSubcategoryModal');

                $.ajax({
                    url: "{{ route('settings-subcategory.show', ':id') }}".replace(':id', id),
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            editForm.attr('action',
                                "{{ route('settings-subcategory.update', ':id') }}".replace(
                                    ':id', id));
                            // تعيين قيمة اسم التصنيف الفرعي في الحقل
                            $('#editSubcategoryName').val(response.data.name);
                            // تعيين قيمة التصنيف الرئيسي
                            $('#editCategorySelect').val(response.data.category_id).trigger(
                                'change');

                            // فتح المودال
                            modal.modal('show');
                        } else {
                            toastr.error('فشل في جلب بيانات التصنيف الفرعي.');
                        }
                    },
                    error: function(xhr) {
                        toastr.error('حدث خطأ أثناء جلب بيانات التصنيف الفرعي.');
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
                            url: "{{ route('settings-subcategory.destroy', ':id') }}"
                                .replace(':id', id),
                            method: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    table.ajax.reload();
                                    toastr.success(response.message);
                                } else {
                                    toastr.error(response.message);
                                }
                            },
                            error: function(xhr) {
                                toastr.error('حدث خطأ أثناء حذف التصنيف الفرعي.');
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
                    url: "{{ route('settings-subcategory.toggleStatus', ':id') }}".replace(':id',
                        id),
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

            // إضافة حقول جديدة لإدخال تصنيفات فرعية متعددة
            $('#add-subcategory-btn').click(function(e) {
                e.preventDefault();
                var container = $('#subcategory-names-container');
                var newField = `
                    <div class="mb-3 subcategory-field">
                        <label class="form-label">اسم التصنيف الفرعي <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="names[]" required />
                            <button class="btn btn-danger remove-subcategory-field" type="button"><i class="ti ti-trash"></i></button>
                        </div>
                        <div class="valid-feedback">تم التحقق بنجاح!</div>
                        <div class="invalid-feedback">اسم التصنيف الفرعي مطلوب.</div>
                    </div>
                `;
                container.append(newField);
            });

            // إزالة حقل تصنيف فرعي معين
            $(document).on('click', '.remove-subcategory-field', function() {
                $(this).closest('.subcategory-field').remove();
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

    <!-- Add Subcategory Modal -->
    <div class="modal fade" id="addSubcategoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <!-- زيادة حجم المودال إلى lg -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">إضافة تصنيفات فرعية جديدة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addSubcategoryForm" class="needs-validation" novalidate
                        action="{{ route('settings-subcategory.store') }}" method="POST">
                        @csrf

                        <!-- اختيار التصنيف الرئيسي باستخدام Select2 -->
                        <div class="mb-3">
                            <label class="form-label" for="categorySelect">التصنيف الرئيسي <span
                                    class="text-danger">*</span></label>
                            <select class="form-select select2" id="categorySelect" name="category_id" required>
                                <option value="">اختر تصنيفًا رئيسيًا</option>
                                @foreach ($mainCategories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <div class="valid-feedback">تم التحقق بنجاح!</div>
                            <div class="invalid-feedback">التصنيف الرئيسي مطلوب.</div>
                        </div>

                        <!-- حاوية تصنيفات فرعية متعددة -->
                        <div id="subcategory-names-container">
                            <!-- الحقل الأول للإدخال -->
                            <div class="mb-3 subcategory-field">
                                <label class="form-label">اسم التصنيف الفرعي <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="names[]" required />
                                    <button class="btn btn-danger remove-subcategory-field" type="button"><i
                                            class="ti ti-trash"></i></button>
                                </div>
                                <div class="valid-feedback">تم التحقق بنجاح!</div>
                                <div class="invalid-feedback">اسم التصنيف الفرعي مطلوب.</div>
                            </div>
                        </div>

                        <!-- زر لإضافة حقول جديدة -->
                        <div class="mb-3">
                            <button id="add-subcategory-btn" class="btn btn-secondary" type="button">
                                <i class="ti ti-plus"></i> إضافة تصنيف فرعي آخر
                            </button>
                        </div>

                        <!-- زر الحفظ -->
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">حفظ التصنيفات الفرعية</button>
                            <button type="reset" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!--/ Add Subcategory Modal -->

    <!-- Edit Subcategory Modal -->
    <div class="modal fade" id="editSubcategoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <!-- حجم المودال الافتراضي مناسب -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تعديل تصنيفات فرعية</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editSubcategoryForm" class="needs-validation" novalidate method="POST">
                        @csrf
                        @method('PUT')

                        <!-- اختيار التصنيف الرئيسي باستخدام Select2 -->
                        <div class="mb-3">
                            <label class="form-label" for="editCategorySelect">التصنيف الرئيسي <span
                                    class="text-danger">*</span></label>
                            <select class="form-select select2" id="editCategorySelect" name="category_id" required>
                                <option value="">اختر تصنيفًا رئيسيًا</option>
                                @foreach ($mainCategories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <div class="valid-feedback">تم التحقق بنجاح!</div>
                            <div class="invalid-feedback">التصنيف الرئيسي مطلوب.</div>
                        </div>

                        <!-- اسم التصنيف الفرعي -->
                        <div class="mb-3">
                            <label class="form-label" for="editSubcategoryName">اسم التصنيف الفرعي <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editSubcategoryName" name="name" required />
                            <div class="valid-feedback">تم التحقق بنجاح!</div>
                            <div class="invalid-feedback">اسم التصنيف الفرعي مطلوب.</div>
                        </div>

                        <!-- زر الحفظ -->
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">تحديث التصنيفات الفرعية</button>
                            <button type="reset" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!--/ Edit Subcategory Modal -->

    <div class="card">
        <div class="card-body">
            <div class="filters row g-3">
                <div class="col-md-6">
                    <label for="filter-category">التصنيف الرئيسي </label>
                    <select id="filter-category" class="form-control select2" data-placeholder="اختر التصنيف الرئيسي ">
                        <option value=""></option>
                        @foreach (\App\Models\general_setting\SettingsCategories::select(['id', 'name'])->orderBy('position', 'asc')->get() as $contract)
                            <option value="{{ $contract->id }}">{{ $contract->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="filter-status">الحالة</label>
                    <select id="filter-status" class="form-control select2" data-placeholder="اختر الحالة">
                        <option value=""></option>
                        <option value="active">نشط</option>
                        <option value="inactive">غير نشط</option>

                    </select>
                </div>

            </div>
            <hr class="mt-10">

            <table id="subcategory-table" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th class="custom-checkbox"><input type="checkbox" id="select-all"></th>
                        <!-- Checkbox تحديد الكل -->
                        <th>اسم التصنيف الفرعي</th>
                        <th>التصنيف الرئيسي</th>
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
@endsection
