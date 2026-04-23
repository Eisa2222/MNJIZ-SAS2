{{-- resources/views/GeneralSetting/SettingsLawsuitsTypes.blade.php --}}
@extends('layouts.layoutMaster')

@section('title', 'إعدادات أنواع الدعاوى')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> إعدادات انواع الدعاوى</a>
        <i class="ti ti-star favorite-icon" data-page-name="إعدادات انواع الدعاوى" data-page-url="{{ url()->current() }}"
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
        /* لضمان ظهور Select2 فوق المودال */
        .select2-container--open {
            z-index: 1060 !important;
            /* Bootstrap modal z-index هو 1050 */
        }
    </style>
@endsection

@section('page-script')
    @vite(['resources/assets/js/form-validation.js'])
    <script>
        $(document).ready(function() {
            // تهيئة Select2 عند عرض المودال
            $('.modal').on('shown.bs.modal', function() {
                $(this).find('.select2').select2({
                    placeholder: function() {
                        return $(this).data('placeholder');
                    },
                    allowClear: true,
                    width: '100%',
                    language: 'ar',
                    dir: 'rtl',
                    dropdownParent: $(this) // تحديد الـ dropdownParent ليكون المودال الحالي
                });
            });

            // تهيئة Select2 للعناصر المرئية بالفعل
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
            var table = $('#lawsuits-type-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('settings-lawsuits-types.index') }}",
                    data: function(d) {
                        d.status = $('#filter-status').val();
                        d.category = $('#filter-category').val();
                        d.subcategory = $('#filter-sub-category').val();
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
                        data: 'main_category',
                        name: 'main_category',
                        orderable: false
                    },
                    {
                        data: 'subcategory',
                        name: 'subcategory',
                        orderable: false
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
                            {
                                extend: 'print',
                                text: 'طباعة'
                            }, {
                                text: 'حذف المحدد',
                                className: 'btn btn-default btn-delete-selected',
                                action: function(e, dt, node, config) {
                                    var selectedIds = [];
                                    $('.row-checkbox:checked').each(function() {
                                        selectedIds.push($(this).val());
                                    });

                                    confirmDeleteSelected(selectedIds,
                                        "{{ route('settings-lawsuits-types.massDelete') }}");
                                }
                            }
                        ]
                    },
                    {
                        text: '<i class="fas fa-plus-circle me-1"></i> إضافة أنواع دعاوى جديدة',
                        className: 'btn btn-primary btn-add',
                        action: function() {
                            $('#addLawsuitTypeModal').modal('show');
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


            var sortable = Sortable.create(document.querySelector('#lawsuits-type-table tbody'), {
                animation: 150,
                handle: '.sortable-handle', // يجب إضافة عنصر يحمل هذا الكلاس ليكون قابل للسحب
                onEnd: function(evt) {
                    var order = [];
                    $('#lawsuits-type-table tbody tr').each(function(index) {
                        order.push({
                            id: $(this).data('id'),
                            position: index + 1
                        });
                    });

                    // إرسال الترتيب الجديد إلى السيرفر عبر AJAX
                    $.ajax({
                        url: "{{ route('settings-lawsuits-types.reorder') }}",
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
                $('#lawsuits-type-table tbody tr').each(function() {
                    if ($(this).find('.sortable-handle').length === 0) {
                        $(this).find('td').first().append(
                            '<span class="sortable-handle ms-2" title="حرك العنصر لاعادة الترتيب" style="cursor: move;"><i class="fas fa-arrows"></i></span>'
                        );
                    }
                });
            });

            // تحديث الجدول عند تغيير الفلاتر
            $('#filter-status, #filter-category, #filter-sub-category').change(function() {
                table.draw();
            });



            // تحديد الكل
            $('#select-all').on('click', function() {
                var rows = $('#lawsuits-type-table').DataTable().rows({
                    'search': 'applied'
                }).nodes();
                $('input[type="checkbox"]', rows).prop('checked', this.checked);
            });




            // تحميل التصنيفات الفرعية بناءً على التصنيف الرئيسي في نموذج الإضافة
            $('#addLawsuitTypeForm #mainCategorySelect').on('change', function() {
                var mainCategoryId = $(this).val();
                var subcategorySelect = $('#addLawsuitTypeForm #modalSubcategorySelect');

                if (mainCategoryId) {
                    $.ajax({
                        url: "{{ route('settings-subcategories.byCategory') }}",
                        method: 'GET',
                        data: {
                            category_id: mainCategoryId
                        },
                        success: function(response) {
                            subcategorySelect.empty();
                            subcategorySelect.append(
                                '<option value="">اختر تصنيفًا فرعيًا</option>');
                            $.each(response, function(index, subcategory) {
                                subcategorySelect.append('<option value="' + subcategory
                                    .id + '">' + subcategory.name + '</option>');
                            });
                            subcategorySelect.trigger('change');
                        },
                        error: function(xhr) {
                            toastr.error('حدث خطأ أثناء جلب التصنيفات الفرعية.');
                        }
                    });
                } else {
                    subcategorySelect.empty();
                    subcategorySelect.append('<option value="">اختر تصنيفًا فرعيًا</option>');
                }
            });

            // إضافة أنواع دعاوى جديدة (عدة أنواع دفعة واحدة)
            $('#addLawsuitTypeForm').on('submit', function(e) {
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
                            $('#addLawsuitTypeModal').modal('hide');
                            form.trigger("reset");
                            // إعادة تعيين Select2
                            form.find('.select2').val(null).trigger('change');
                            // إعادة تحميل الـ DataTable
                            table.ajax.reload();
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
                            toastr.error('حدث خطأ أثناء إضافة أنواع الدعاوى.');
                        }
                    }
                });
            });

            // تغيير الحالة عند النقر على الشارة
            $(document).on('click', '.status-toggle', function() {
                var badge = $(this);
                var id = badge.data('id');

                $.ajax({
                    url: "{{ route('settings-lawsuits-types.toggleStatus', ':id') }}".replace(':id',
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

            // فتح المودال وتعبئة البيانات عند النقر على زر التعديل
            $(document).on('click', '.btn-edit', function() {
                var id = $(this).data('id');
                var editForm = $('#editLawsuitTypeForm');

                $.ajax({
                    url: "{{ route('settings-lawsuits-types.edit', ':id') }}".replace(':id', id),
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            var type = response.data;
                            $('#editLawsuitTypeName').val(type.name);
                            var actionUrl =
                                "{{ route('settings-lawsuits-types.update', ':id') }}".replace(
                                    ':id', id);
                            editForm.attr('action', actionUrl);

                            // تحميل التصنيفات الفرعية بناءً على التصنيف الرئيسي الحالي
                            $.ajax({
                                url: "{{ route('settings-subcategories.byCategory') }}",
                                method: 'GET',
                                data: {
                                    category_id: type.subcategory.category.id
                                },
                                success: function(subcategories) {
                                    var subcategorySelect = $(
                                        '#editSubcategorySelect');
                                    subcategorySelect.empty();
                                    subcategorySelect.append(
                                        '<option value="">اختر تصنيفًا فرعيًا</option>'
                                    );
                                    $.each(subcategories, function(index,
                                        subcategory) {
                                        var selected = subcategory.id ===
                                            type.subcategory.id ?
                                            'selected' : '';
                                        subcategorySelect.append(
                                            '<option value="' +
                                            subcategory.id + '" ' +
                                            selected + '>' + subcategory
                                            .name + '</option>');
                                    });
                                    subcategorySelect.trigger('change');
                                },
                                error: function(xhr) {
                                    toastr.error(
                                        'حدث خطأ أثناء جلب التصنيفات الفرعية.');
                                }
                            });

                            $('#editLawsuitTypeModal').modal('show');
                        } else {
                            toastr.error('فشل في جلب بيانات نوع الدعوى.');
                        }
                    },
                    error: function(xhr) {
                        toastr.error('حدث خطأ أثناء جلب بيانات نوع الدعوى.');
                    }
                });
            });

            // تحديث نوع الدعوى
            $('#editLawsuitTypeForm').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                var actionUrl = form.attr('action');

                $.ajax({
                    url: actionUrl,
                    method: 'PUT',
                    data: form.serialize(),
                    success: function(response) {
                        if (response.success) {
                            $('#editLawsuitTypeModal').modal('hide');
                            form.trigger("reset");
                            // إعادة تعيين Select2
                            form.find('.select2').val(null).trigger('change');
                            // إعادة تحميل الـ DataTable
                            table.ajax.reload();
                            toastr.success(response.success);
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
                            toastr.error('حدث خطأ أثناء تحديث نوع الدعوى.');
                        }
                    }
                });
            });

            // حذف نوع الدعوى
            $(document).on('click', '.btn-delete', function() {
                var id = $(this).data('id');
                Swal.fire({
                    title: 'هل أنت متأكد من عملية الحذف؟',
                    text: "لا يمكن التراجع عن هذا الإجراء!",
                    icon: 'warning',
                    showCancelButton: true, // يعرض زر الإلغاء
                    showConfirmButton: true, // يعرض زر التأكيد
                    showDenyButton: false, // لا يعرض زر الرفض
                    buttonsStyling: false, // لتعطيل التنسيق الافتراضي للأزرار
                    customClass: {
                        popup: 'custom-popup', // تخصيص شكل النافذة
                        title: 'custom-title', // تخصيص شكل العنوان
                        text: 'custom-text', // تخصيص شكل النص
                        confirmButton: 'btn btn-success custom-confirm', // تخصيص زر التأكيد
                        cancelButton: 'btn btn-danger custom-cancel' // تخصيص زر الإلغاء
                    },
                    confirmButtonText: 'تأكيد',
                    cancelButtonText: 'إلغاء',
                    reverseButtons: false, // لعكس ترتيب الأزرار إذا رغبت
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('settings-lawsuits-types') }}/" + id,
                            method: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    table.ajax.reload();
                                    toastr.success(response.success);
                                }
                            },
                            error: function(xhr) {
                                toastr.error('حدث خطأ أثناء الحذف.');
                            }
                        });
                    }
                })
            });

            // إضافة حقول جديدة لإدخال أنواع دعاوى متعددة
            $('#addLawsuitTypeForm').on('click', '#add-lawsuit-type-btn', function(e) {
                e.preventDefault();
                var container = $('#lawsuit-types-container');
                var newField = `
                    <div class="row mb-3 lawsuit-type-field">
                        <div class="col-10">
                            <input type="text" class="form-control" name="lawsuit_names[]" placeholder="اسم نوع الدعوى" required />
                            <div class="valid-feedback">تم التحقق بنجاح!</div>
                            <div class="invalid-feedback">اسم نوع الدعوى مطلوب.</div>
                        </div>
                        <div class="col-2">
                            <button class="btn btn-danger remove-lawsuit-type-field" type="button"><i class="ti ti-trash"></i></button>
                        </div>
                    </div>
                `;
                container.append(newField);
            });





            // إزالة حقل نوع دعوى معين
            $(document).on('click', '.remove-lawsuit-type-field', function() {
                $(this).closest('.lawsuit-type-field').remove();
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

    <!-- Add Lawsuit Type Modal -->
    <div class="modal fade" id="addLawsuitTypeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <!-- زيادة حجم المودال إلى lg -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">إضافة أنواع دعاوى جديدة</h5>
                    <button type="button" class="btn-close btn-pinned" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="needs-validation" novalidate id="addLawsuitTypeForm"
                        action="{{ route('settings-lawsuits-types.store') }}" method="POST">
                        @csrf

                        <!-- اختيار التصنيف الرئيسي -->
                        <div class="mb-4">
                            <label class="form-label" for="mainCategorySelect">التصنيف الرئيسي <span
                                    class="text-danger">*</span></label>
                            <select id="mainCategorySelect" name="main_category_id" class="form-select select2"
                                data-placeholder="اختر تصنيفًا رئيسيًا" required>
                                <option></option>
                                @foreach ($mainCategories as $mainCategory)
                                    <option value="{{ $mainCategory->id }}">{{ $mainCategory->name }}</option>
                                @endforeach
                            </select>
                            <div class="valid-feedback">تم التحقق بنجاح!</div>
                            <div class="invalid-feedback">التصنيف الرئيسي مطلوب.</div>
                        </div>

                        <!-- اختيار التصنيف الفرعي بناءً على التصنيف الرئيسي -->
                        <div class="mb-4">
                            <label class="form-label" for="modalSubcategorySelect">التصنيف الفرعي <span
                                    class="text-danger">*</span></label>
                            <select id="modalSubcategorySelect" name="subcategory_id" class="form-select select2"
                                data-placeholder="اختر تصنيفًا فرعيًا" required>
                                <option></option>
                                {{-- سيتم تحميل التصنيفات الفرعية ديناميكيًا عبر AJAX --}}
                            </select>
                            <div class="valid-feedback">تم التحقق بنجاح!</div>
                            <div class="invalid-feedback">التصنيف الفرعي مطلوب.</div>
                        </div>

                        <!-- حاوية لإدخال عدة أنواع دعوى -->
                        <div id="lawsuit-types-container">
                            <!-- الحقل الأول لإدخال نوع دعوى -->
                            <div class="row mb-3 lawsuit-type-field">
                                <div class="col-10">
                                    <input type="text" class="form-control" name="lawsuit_names[]"
                                        placeholder="اسم نوع الدعوى" required />
                                    <div class="valid-feedback">تم التحقق بنجاح!</div>
                                    <div class="invalid-feedback">اسم نوع الدعوى مطلوب.</div>
                                </div>
                                <div class="col-2">
                                    <button class="btn btn-danger remove-lawsuit-type-field" type="button"><i
                                            class="ti ti-trash"></i></button>
                                </div>
                            </div>
                        </div>

                        <!-- زر لإضافة حقول جديدة لإدخال أنواع دعاوى إضافية -->
                        <div class="mb-4">
                            <button id="add-lawsuit-type-btn" class="btn btn-secondary" type="button">
                                <i class="ti ti-plus"></i> إضافة نوع دعوى آخر
                            </button>
                        </div>

                        <!-- زر الحفظ -->
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">حفظ أنواع الدعاوى</button>
                            <button type="reset" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!--/ Add Lawsuit Type Modal -->

    <!-- Edit Lawsuit Type Modal -->
    <div class="modal fade" id="editLawsuitTypeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <!-- زيادة حجم المودال إلى lg -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تعديل نوع دعوى</h5>
                    <button type="button" class="btn-close btn-pinned" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editLawsuitTypeForm" class="needs-validation" novalidate method="POST">
                        @csrf
                        @method('PUT')

                        <!-- اختيار التصنيف الفرعي بناءً على التصنيف الرئيسي -->
                        <div class="mb-4">
                            <label class="form-label" for="editSubcategorySelect">التصنيف الفرعي <span
                                    class="text-danger">*</span></label>
                            <select id="editSubcategorySelect" name="subcategory_id" class="form-select select2"
                                data-placeholder="اختر تصنيفًا فرعيًا" required>
                                <option></option>
                                {{-- سيتم تحميل التصنيفات الفرعية ديناميكيًا عبر AJAX --}}
                            </select>
                            <div class="valid-feedback">تم التحقق بنجاح!</div>
                            <div class="invalid-feedback">التصنيف الفرعي مطلوب.</div>
                        </div>

                        <!-- حقل لتعديل اسم نوع الدعوى -->
                        <div class="mb-4">
                            <label class="form-label" for="editLawsuitTypeName">اسم نوع الدعوى <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="editLawsuitTypeName" name="name" class="form-control"
                                placeholder="اسم نوع الدعوى" required />
                            <div class="valid-feedback">تم التحقق بنجاح!</div>
                            <div class="invalid-feedback">اسم نوع الدعوى مطلوب.</div>
                        </div>

                        <!-- زر الحفظ -->
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">تحديث نوع الدعوى</button>
                            <button type="reset" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!--/ Edit Lawsuit Type Modal -->

    <div class="card">
        <div class="card-body">
            <div class="filters row g-3">
                <div class="col-md-4">
                    <label for="filter-category">التصنيف الرئيسي</label>
                    <select id="filter-category" class="form-control select2" data-placeholder="اختر التصنيف الرئيسي">
                        <option value=""> </option>
                        @foreach (\App\Models\general_setting\SettingsCategories::select(['id', 'name'])->orderBy('position', 'asc')->get() as $mainCat)
                            <option value="{{ $mainCat->id }}">{{ $mainCat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="filter-sub-category">التصنيف الفرعي</label>
                    <select id="filter-sub-category" class="form-control select2" data-placeholder="اختر التصنيف الفرعي">
                        <option value=""> </option>

                        {{-- هنا نعرض كل التصنيفات الفرعية مسبقًا --}}
                        @foreach (\App\Models\general_setting\SettingsSubcategories::select(['id', 'name', 'category_id'])->orderBy('position', 'asc')->get() as $subCat)
                            <option data-parent="{{ $subCat->category_id }}" value="{{ $subCat->id }}">
                                {{ $subCat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>


                <script>
                    $(document).ready(function() {
                        // حفظ نسخة من جميع الخيارات (ما عدا الخيار الفارغ الأول) للاستخدام في التصفية لاحقًا
                        let $allSubCats = $('#filter-sub-category').find('option:not([value=""])').clone();

                        // عند تغيير التصنيف الرئيسي
                        $('#filter-category').on('change', function() {
                            let categoryId = $(this).val(); // التصنيف الرئيسي المختار
                            let $filterLawsuitType = $('#filter-sub-category');

                            // إعادة تهيئة القائمة الفارغة
                            $filterLawsuitType.html('<option value="">اختر التصنيف الفرعي</option>');

                            // إذا لم يتم اختيار أي تصنيف رئيسي (القيمة فارغة)
                            if (!categoryId) {
                                // أضف جميع التصنيفات الفرعية
                                $filterLawsuitType.append($allSubCats);
                            } else {
                                // صنّف الخيارات بناءً على الـ data-parent
                                let $filtered = $allSubCats.filter(function() {
                                    // نقارن قيمة data-parent بقيمة categoryId
                                    return $(this).data('parent') == categoryId;
                                });

                                // أضف التصنيفات الفرعية المطابقة فقط
                                $filterLawsuitType.append($filtered);
                            }
                        });
                    });
                </script>


                <div class="col-md-4">
                    <label for="filter-status">الحالة</label>
                    <select id="filter-status" class="form-control select2" data-placeholder="اختر الحالة">
                        <option value=""></option>
                        <option value="active">نشط</option>
                        <option value="inactive">غير نشط</option>

                    </select>
                </div>

            </div>
            <hr class="mt-10">
            <table id="lawsuits-type-table" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th class="custom-checkbox">
                            <input type="checkbox" id="select-all"> <!-- Checkbox تحديد الكل -->
                        </th>
                        <th>التصنيف الرئيسي</th>
                        <th>التصنيف الفرعي</th>
                        <th>اسم نوع الدعوى</th>
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
