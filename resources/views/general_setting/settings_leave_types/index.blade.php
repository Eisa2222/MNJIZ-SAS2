@extends('layouts.layoutMaster')

@section('title', 'إعدادات أنواع الإجازات')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">إعدادات أنواع الإجازات
        </a>
        <i class="ti ti-star favorite-icon" data-page-name="إعدادات أنواع الإجازات" data-page-url="{{ url()->current() }}"
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
            var table = $('#leave-type-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('settings-leave-types.index') }}",
                },
                columns: [{
                        data: 'checkbox',
                        name: 'checkbox',
                        orderable: false,
                        searchable: false,
                        className: 'custom-checkbox',
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'days',
                        name: 'days',
                        render: function(data, type, row) {
                            return data === null ? 'غير محددة المدة' : data;
                        }
                    },
                    {
                        data: 'is_paid',
                        name: 'is_paid',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        className: 'text-center',
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [6, 'desc']
                ],
                dom: '<"dt-toolbar"' +
                    '<"dt-toolbar-left"l>' +
                    '<"dt-toolbar-right"fB>' +
                    '>' +
                    'rt' +
                    '<"row"' +
                    '<"col-12 d-flex align-items-center justify-content-between"i p>' +
                    '>',
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
                                text: 'حذف المحدد',
                                className: 'btn btn-default btn-delete-selected',
                                action: function(e, dt, node, config) {
                                    var selectedIds = [];
                                    $('.row-checkbox:checked').each(function() {
                                        selectedIds.push($(this).val());
                                    });

                                    if (selectedIds.length > 0) {
                                        confirmDeleteSelectedmss(selectedIds,
                                            "{{ route('settings-leave-types.mass-delete') }}",
                                            'SettingsLeaveType');
                                    } else {
                                        toastr.warning('يرجى تحديد عنصر واحد على الاقل .');
                                    }
                                }
                            }
                        ]
                    },
                    {
                        text: '<i class="fas fa-plus-circle me-1"></i> إضافة نوع جديد',
                        className: 'btn btn-primary btn-add',
                        action: function() {
                            window.location = "{{ route('settings-leave-types.create') }}";
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

        // التعامل مع زر التعديل لفتح المودال وملء البيانات
        $(document).on('click', '.btn-edit', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var days = $(this).data('days');
            var is_paid = $(this).data('is_paid');
            var start_date = $(this).data('start_date');
            var end_date = $(this).data('end_date');
            var status = $(this).data('status');
            var editForm = $('#editLeaveTypeForm');

            var actionUrl = "{{ route('settings-leave-types.update', ':id') }}".replace(':id', id);

            editForm.attr('action', actionUrl);
            $('#modalLeaveTypeNameEdit').val(name);
            $('#modalLeaveTypeDaysEdit').val(days);

            // تعيين قيمة حقل نوع الإجازة (مدفوعة أم لا)
            if (is_paid == 1) {
                $('#modalLeaveTypeIsPaidEdit').prop('checked', true);
            } else {
                $('#modalLeaveTypeIsPaidEdit').prop('checked', false);
            }

            // تعيين قيمة حقول التاريخ
            $('#modalLeaveTypeStartDateEdit').val(start_date);
            $('#modalLeaveTypeEndDateEdit').val(end_date);

            // إذا كانت هناك تواريخ محددة، فيتم تحديد الخيار المناسب وإظهار حقول التاريخ
            if (start_date || end_date) {
                $('#modalLinkedToDateEdit').prop('checked', true);
                $('#dateFieldsContainerEdit').show();
                $('#manualDurationContainerEdit').hide();

                // أضف هذه الأسطر لحساب المدة
                if (start_date && end_date) {
                    var startDate = new Date(start_date);
                    var endDate = new Date(end_date);
                    var diffTime = Math.abs(endDate - startDate);
                    var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                    $('#modalLeaveTypeDurationCalculatedEdit').val(diffDays);
                }
            } else {
                $('#modalLinkedToDateEdit').prop('checked', false);
                $('#dateFieldsContainerEdit').hide();
                $('#manualDurationContainerEdit').show();
            }

            // تعيين حالة نوع الإجازة
            if (status === 'active') {
                $('#status_active_edit').prop('checked', true);
            } else {
                $('#status_inactive_edit').prop('checked', true);
            }

            $('#editLeaveTypeModal').modal('show');
        });

        // التعامل مع تغيير الحالة عند النقر على الشارة
        $(document).on('click', '.status-toggle', function() {
            var badge = $(this);
            var id = badge.data('id');

            $.ajax({
                url: "{{ route('settings-leave-types.toggle-status', ':id') }}".replace(':id', id),
                method: 'GET',
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

@section('content')


    <!-- كود JavaScript لتبديل ظهور الحقول وحساب المدة -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // تهيئة الإضافة
            initDateFields('modalLinkedToDate', 'dateFieldsContainer', 'manualDurationContainer',
                'modalLeaveTypeStartDate',
                'modalLeaveTypeEndDate', 'modalLeaveTypeDurationCalculated', 'modalLeaveTypeDaysManual');

            // تهيئة التعديل
            initDateFields('modalLinkedToDateEdit', 'dateFieldsContainerEdit', 'manualDurationContainerEdit',
                'modalLeaveTypeStartDateEdit',
                'modalLeaveTypeEndDateEdit', 'modalLeaveTypeDurationCalculatedEdit', 'modalLeaveTypeDaysEdit');

            // دالة تهيئة حقول التاريخ والمدة
            function initDateFields(checkboxId, dateContainerId, durationContainerId, startDateId, endDateId,
                calculatedDurationId, manualDaysId) {
                const linkedCheckbox = document.getElementById(checkboxId);
                if (!linkedCheckbox) return;

                const dateFieldsContainer = document.getElementById(dateContainerId);
                const manualDurationContainer = document.getElementById(durationContainerId);
                const startDateInput = document.getElementById(startDateId);
                const endDateInput = document.getElementById(endDateId);
                const calculatedDurationInput = document.getElementById(calculatedDurationId);
                const manualDaysInput = document.getElementById(manualDaysId);

                // دالة لحساب الفرق بالأيام بين تاريخين والتحقق من صحة التواريخ
                function calculateDuration() {
                    startDateInput.setCustomValidity("");
                    endDateInput.setCustomValidity("");

                    if (startDateInput.value && endDateInput.value) {
                        const startDate = new Date(startDateInput.value);
                        const endDate = new Date(endDateInput.value);

                        if (endDate < startDate) {
                            startDateInput.setCustomValidity("يجب أن يكون تاريخ البداية قبل تاريخ النهاية");
                            endDateInput.setCustomValidity("يجب أن يكون تاريخ النهاية بعد تاريخ البداية");
                            calculatedDurationInput.value = "";
                            manualDaysInput.value = "";
                        } else {
                            const diffTime = Math.abs(endDate - startDate);
                            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                            calculatedDurationInput.value = diffDays;
                            manualDaysInput.value = diffDays;
                        }
                    } else {
                        calculatedDurationInput.value = "";
                        manualDaysInput.value = "";
                    }
                }

                // عند تغيير حالة الـ Checkbox لتبديل ظهور الحقول
                linkedCheckbox.addEventListener('change', function() {
                    if (this.checked) {
                        dateFieldsContainer.style.display = 'block';
                        manualDurationContainer.style.display = 'none';
                        manualDaysInput.removeAttribute('required');
                        startDateInput.setAttribute('required', 'required');
                        endDateInput.setAttribute('required', 'required');
                        calculateDuration();
                    } else {
                        // هنا التعديل الرئيسي: مسح قيم الحقول عند إخفائها
                        startDateInput.value = '';
                        endDateInput.value = '';
                        calculatedDurationInput.value = '';
                        manualDaysInput.value = '';

                        dateFieldsContainer.style.display = 'none';
                        manualDurationContainer.style.display = 'block';
                        manualDaysInput.setAttribute('required', 'required');
                        startDateInput.removeAttribute('required');
                        endDateInput.removeAttribute('required');
                        startDateInput.setCustomValidity("");
                        endDateInput.setCustomValidity("");
                    }
                });

                if (startDateInput && endDateInput) {
                    startDateInput.addEventListener('change', calculateDuration);
                    endDateInput.addEventListener('change', calculateDuration);
                }
            }
        });

        // التأكد من استخدام قيمة حقل المدة المحسوبة قبل إرسال النموذج
        document.addEventListener('DOMContentLoaded', function() {
            // التعامل مع نموذج الإضافة
            const addForm = document.querySelector('form[action="{{ route('settings-leave-types.store') }}"]');
            if (addForm) {
                addForm.addEventListener('submit', function(e) {
                    // إذا كان الخيار "مرتبط بتاريخ" محدد
                    if (document.getElementById('modalLinkedToDate').checked) {
                        // نقل قيمة المدة المحسوبة إلى حقل عدد الأيام
                        const calculatedDuration = document.getElementById(
                            'modalLeaveTypeDurationCalculated').value;
                        if (calculatedDuration) {
                            document.getElementById('modalLeaveTypeDaysManual').value = calculatedDuration;
                        }
                    }
                });
            }

            // التعامل مع نموذج التعديل
            const editForm = document.getElementById('editLeaveTypeForm');
            if (editForm) {
                editForm.addEventListener('submit', function(e) {
                    // إذا كان الخيار "مرتبط بتاريخ" محدد
                    if (document.getElementById('modalLinkedToDateEdit').checked) {
                        // نقل قيمة المدة المحسوبة إلى حقل عدد الأيام
                        const calculatedDuration = document.getElementById(
                            'modalLeaveTypeDurationCalculatedEdit').value;
                        if (calculatedDuration) {
                            document.getElementById('modalLeaveTypeDaysEdit').value = calculatedDuration;
                        } else {
                            // إذا كانت القيمة فارغة، استخدم القيمة اليدوية
                            const manualDays = document.getElementById('modalLeaveTypeDaysEdit').value;
                            document.getElementById('modalLeaveTypeDurationCalculatedEdit').value =
                                manualDays;
                        }
                    }
                });
            }
        });
    </script>

    <div class="card">
        <div class="card-body">
            <table id="leave-type-table" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th class="custom-checkbox">
                            <input type="checkbox" id="select-all">
                        </th>
                        <th>اسم نوع الإجازة</th>
                        <th>الحد الأقصى للأيام</th>
                        <th>نوع الإجازة</th>
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
