<!-- في ملف الأزرار (داخل الـ loop) -->
<div class="d-flex justify-content-center">
    @can('تعديل اصل')

        @if ($row->use_status->value == 'available')
            <button type="button" class="btn btn-sm " title="إسناد الاصل لموظف"
                onclick="openAssignModal({{ $row->id }}, '{{ $row->name }}')">
                <i class="ti ti-user-plus"></i>
            </button>
        @elseif ($row->use_status->value == 'in_use')
            @php
                $latestLog = $row->logs->first();
                $employeeId = $latestLog?->request?->employee?->id ?? 'null';
                $employeeName = $latestLog?->request?->employee?->name ?? 'غير محدد';
                $requestId = $latestLog?->custody_request_id ?? 'null';
            @endphp

            <button type="button" class="btn btn-sm " title="إرجاع الاصل من {{ $employeeName }}"
                onclick="openReturnModal({{ $requestId }}, '{{ $row->name }}', {{ $employeeId }}, '{{ $employeeName }}')">
                <i class="ti ti-user-minus"></i>
            </button>
        @elseif ($row->use_status->value == 'maintenance')
            <button type="button" class="btn btn-sm " disabled
                onclick="openMaintenanceModal({{ $row->id }}, '{{ $row->name }}')">
                <i class="ti ti-tool"></i>
            </button>
        @elseif ($row->use_status->value == 'stale')
            <button type="button" class="btn btn-sm" disabled
                onclick="openStaleModal({{ $row->id }}, '{{ $row->name }}')">
                <i class="ti ti-trash-x"></i>
            </button>
        @endif

        <a href="{{ route($route . '.edit', $row->id) }}" class="btn btn-sm text-secondary me-2" title="تعديل">
            <i class="ti ti-edit"></i>
        </a>
    @endcan

    @can('حذف اصل')
        <button type="button" class="btn btn-sm text-secondary" onclick="confirmDelete({{ $row->id }})"
            title="حذف">
            <i class="ti ti-trash"></i>
        </button>

        <form id="delete-form-{{ $row->id }}" action="{{ route($route . '.destroy', $row->id) }}" method="POST"
            style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    @endcan

</div>

<!-- موديل الإسناد -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-4">
                <h5 class="modal-title">إسناد الاصل لموظف</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="border-1 border-light border-dashed mb-2"></div>

            <form action="{{ route($route . '.assign') }}" method="POST" id="assignForm">
                @csrf
                <input type="hidden" name="custody_item_id" id="custody_item_id">

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">الاصل</label>
                        <p id="itemName" class="text-muted"></p>
                    </div>


                    <div class="mb-3">
                        <label for="employee_id" class="form-label">
                            الموظف <span class="text-danger">*</span>
                        </label>
                        <select name="employee_id" id="employee_id" class="form-control" required>
                            <option value=""></option>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                        @error('employee_id')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary m-0" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">إسناد الاصل</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- موديل الإرجاع -->
<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-4">
                <h5 class="modal-title">إرجاع الاصل</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="border-1 border-light border-dashed mb-2"></div>

            <form action="{{ route($route . '.return') }}" method="POST" id="returnForm">
                @csrf
                <input type="hidden" name="parent_request_id" id="parent_request_id">
                <input type="hidden" name="employee_id" id="return_employee_id">

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">الاصل</label>
                        <p id="returnItemName" class="text-muted"></p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">مُسند لدى</label>
                        <p id="returnEmployeeName"></p>
                    </div>

                    <div class="mb-3">
                        <label for="return_status" class="form-label">سبب الارجاع
                            <span class="text-danger">*</span>
                        </label>
                        <select id="return_status" name="return_status" class="form-select select2"
                            data-placeholder="اختر سبب الارجاع" required>
                            <option value="">اختر سبب الارجاع</option>
                            @foreach ($returnStatus as $type)
                                <option value="{{ $type['id'] }}"
                                    {{ old('return_status') == $type['id'] ? 'selected' : '' }}>
                                    {{ $type['name'] }}
                                </option>
                            @endforeach
                        </select>

                    </div>

                    <div class="mb-3">
                        <label for="return_notes" class="form-label">ملاحظات الإرجاع</label>
                        <textarea name="notes" id="return_notes" class="form-control" rows="3" placeholder="اضف الملاحظات هنا"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary m-0" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">إرجاع الاصل</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // تهيئة Select2
        initSelect2();

        // ربط أحداث النماذج
        bindFormEvents();
    });

    // تهيئة Select2
    function initSelect2() {
        $('#employee_id').select2({
            placeholder: 'اختر الموظف',
            allowClear: true,
            dropdownParent: $('#assignModal'),
            width: '100%'
        });
        $('#return_status').select2({
            placeholder: 'اختر سبب الارجاع',
            allowClear: true,
            dropdownParent: $('#returnModal'),
            width: '100%'
        });
    }

    // ربط أحداث النماذج
    function bindFormEvents() {
        // إزالة المستمعين السابقين
        $('#assignForm, #returnForm').off('submit');

        // نموذج الإسناد
        $('#assignForm').on('submit', handleAssignSubmit);

        // نموذج الإرجاع
        $('#returnForm').on('submit', handleReturnSubmit);
    }

    // معالج إرسال نموذج الإسناد
    function handleAssignSubmit(e) {
        e.preventDefault();

        const employeeId = $('#employee_id').val();
        if (!employeeId) {
            toastr.warning('يرجى اختيار موظف أولاً', 'تنبيه');
            return;
        }

        submitForm(this, 'assignModal', 'تم إسناد الأصل بنجاح');
    }

    // معالج إرسال نموذج الإرجاع
    function handleReturnSubmit(e) {
        e.preventDefault();

        const employeeId = $('#return_employee_id').val();
        const requestId = $('#parent_request_id').val();
        const returnStatus = $('#return_status').val();

        if (!employeeId || !requestId) {
            toastr.error('بيانات الإرجاع غير مكتملة', 'خطأ');
            return;
        }

        if (!returnStatus) {
            toastr.warning('يرجى اختيار سبب الإرجاع', 'تنبيه');
            // إضافة تأثير بصري للحقل المطلوب
            $('#return_status').next('.select2-container').addClass('is-invalid');

            return;
        }

        // تأكيد الإرجاع
        Swal.fire({
            title: 'تأكيد الإرجاع',
            text: 'هل أنت متأكد من إرجاع هذا الأصل؟',
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
            if (result.isConfirmed) {
                submitForm(this, 'returnModal', 'تم إرجاع الأصل بنجاح');
            }
        });
    }

    // دالة موحدة لإرسال النماذج
    function submitForm(form, modalId, successMessage) {
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;

        if (submitBtn.disabled) return;

        // تعطيل الزر وإظهار التحميل
        toggleSubmitButton(submitBtn, true, 'جاري المعالجة...');

        const formData = new FormData(form);

        fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // إغلاق الموديل
                    bootstrap.Modal.getInstance(document.getElementById(modalId)).hide();

                    // إظهار رسالة نجاح
                    toastr.success(data.message || successMessage);

                    // تحديث الجدول
                    refreshDataTable();
                } else {
                    toastr.error(data.message || 'حدث خطأ أثناء العملية', 'خطأ!');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('حدث خطأ غير متوقع', error);
            })
            .finally(() => {
                // إعادة تفعيل الزر
                toggleSubmitButton(submitBtn, false, originalText);
            });
    }

    // تبديل حالة زر الإرسال
    function toggleSubmitButton(button, loading, text) {
        button.disabled = loading;

        if (loading) {
            button.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>${text}`;
        } else {
            button.textContent = text;
        }
    }

    // فتح موديل الإسناد
    function openAssignModal(itemId, itemName) {
        $('#custody_item_id').val(itemId);
        $('#itemName').text(itemName);

        // إعادة تعيين النموذج
        resetForm('assignForm');
        $('#employee_id').val(null).trigger('change');
        $('#custody_item_id').val(itemId); // إعادة تعيين بعد الريست

        // فتح الموديل
        new bootstrap.Modal(document.getElementById('assignModal')).show();
    }

    // فتح موديل الإرجاع
    function openReturnModal(requestId, itemName, employeeId, employeeName) {
        // التحقق من صحة البيانات
        if (!requestId || requestId === 'null') {
            toastr.error('بيانات الطلب غير صحيحة', 'خطأ');
            return;
        }

        if (!employeeId || employeeId === 'null') {
            toastr.error('بيانات الموظف غير صحيحة', 'خطأ');
            return;
        }

        // تعيين القيم
        $('#parent_request_id').val(requestId);
        $('#return_employee_id').val(employeeId);
        $('#returnItemName').text(itemName || 'غير محدد');
        $('#returnEmployeeName').text(employeeName || 'غير محدد');

        // إعادة تعيين النموذج
        resetForm('returnForm');

        // إعادة تعيين Select2 لسبب الإرجاع
        $('#return_status').val(null).trigger('change');

        // إعادة تعيين ملاحظات الإرجاع
        $('#return_notes').val('');

        // إعادة تعيين القيم المخفية بعد reset
        $('#parent_request_id').val(requestId);
        $('#return_employee_id').val(employeeId);

        // فتح الموديل
        const modal = new bootstrap.Modal(document.getElementById('returnModal'));
        modal.show();
    }

    // إعادة تعيين النموذج
    function resetForm(formId) {
        document.getElementById(formId).reset();
    }

    // تحديث الجدول
    function refreshDataTable() {
        try {
            $('#item-table').DataTable().ajax.reload(null, false);
        } catch (error) {
            console.warn('تعذر تحديث الجدول، سيتم إعادة تحميل الصفحة');
            setTimeout(() => location.reload(), 1000);
        }
    }
</script>
