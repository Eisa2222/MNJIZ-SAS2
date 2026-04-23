/*
|--------------------------------------------------------------------------
| ملف إدارة الاعتمادات - JavaScript
|--------------------------------------------------------------------------
| هذا الملف يحتوي على جميع وظائف إدارة الاعتمادات والموافقات
| تم تنظيم الكود بشكل احترافي مع تعليقات واضحة لكل دالة
*/

$(document).ready(function() {

    /*
    |--------------------------------------------------------------------------
    | تهيئة المتغيرات العامة
    |--------------------------------------------------------------------------
    */
    let currentFlow = 0;
    let currentLevel = 0;
    let approvalData = {};

    /*
    |--------------------------------------------------------------------------
    | تهيئة المكونات الأساسية
    |--------------------------------------------------------------------------
    */
    function initializeComponents() {
        // تهيئة Select2
        $('.select2').select2({
            width: '100%',
            dir: 'rtl',
            language: 'ar',
            dropdownParent: $('#employeeSelectionModal'),
            placeholder: '-- اختر الموظف --'
        });

        // تحديث البيانات من الخادم
        if (window.APPROVAL_CONFIG && window.APPROVAL_CONFIG.flows) {
            approvalData = window.APPROVAL_CONFIG.flows;
        }

        // بناء جميع الجداول
        buildAllTables();
    }

    /*
    |--------------------------------------------------------------------------
    | بناء جميع جداول الاعتمادات
    |--------------------------------------------------------------------------
    */
    function buildAllTables() {
        Object.keys(approvalData).forEach(flowType => {
            const flow = approvalData[flowType].first();
            if (flow && flow.levels) {
                buildApprovalGrid(flow.id, flow.levels);
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | بناء شبكة مستويات الاعتماد
    |--------------------------------------------------------------------------
    */
    function buildApprovalGrid(flowId, levels) {
        const container = $(`.approval-levels-container[data-flow="${flowId}"]`);
        if (!container.length) return;

        let gridHTML = '<div class="grid-levels">';

        levels.forEach(level => {
            const isEnabled = isPreviousLevelActive(levels, level.level);
            const hasEmployee = level.employee && level.employee.name;

            gridHTML += createLevelCard(flowId, level, isEnabled, hasEmployee);
        });

        gridHTML += '</div>';
        container.html(gridHTML);
    }

    /*
    |--------------------------------------------------------------------------
    | إنشاء بطاقة مستوى الاعتماد
    |--------------------------------------------------------------------------
    */
    function createLevelCard(flowId, level, isEnabled, hasEmployee) {
        const cardClass = `approval-level-card ${hasEmployee ? 'active' : ''} ${!isEnabled ? 'disabled' : ''}`;
        const badgeClass = `level-badge ${hasEmployee ? '' : 'inactive'}`;

        return `
            <div class="${cardClass}">
                <div class="${badgeClass}">${level.level}</div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="fw-bold mb-1">المستوى ${level.level}</h6>
                        <small class="text-muted">${hasEmployee ? 'مفعل' : 'غير مفعل'}</small>
                    </div>

                    <label class="approval-switch">
                        <input type="checkbox" class="approval-checkbox"
                               data-flow="${flowId}" data-level="${level.level}"
                               ${hasEmployee ? 'checked' : ''} ${!isEnabled ? 'disabled' : ''}>
                        <span class="approval-slider"></span>
                    </label>
                </div>

                ${hasEmployee ? createEmployeeSection(level.employee) : createEmptySection(isEnabled)}
            </div>
        `;
    }

    /*
    |--------------------------------------------------------------------------
    | إنشاء قسم معلومات الموظف
    |--------------------------------------------------------------------------
    */
    function createEmployeeSection(employee) {
        const initials = getEmployeeInitials(employee.name);

        return `
            <div class="employee-info">
                <div class="d-flex align-items-center">
                    <div class="employee-avatar">${initials}</div>
                    <div class="me-3 flex-grow-1">
                        <h6 class="fw-bold mb-1">${employee.name}</h6>
                        <small class="text-muted">معتمد نشط</small>
                    </div>
                </div>
                <div class="status-badge active mt-2">
                    <i class="ti ti-check me-1"></i>
                    المستوى مفعل
                </div>
            </div>
        `;
    }

    /*
    |--------------------------------------------------------------------------
    | إنشاء القسم الفارغ
    |--------------------------------------------------------------------------
    */
    function createEmptySection(isEnabled) {
        if (!isEnabled) {
            return `
                <div class="text-center py-4">
                    <div class="avatar bg-light mb-3">
                        <i class="ti ti-user text-muted"></i>
                    </div>
                    <p class="text-muted small mb-2">لم يتم تعيين موظف</p>
                    <div class="status-badge warning">
                        <i class="ti ti-alert-triangle me-1"></i>
                        يتطلب تفعيل المستوى السابق
                    </div>
                </div>
            `;
        }

        return `
            <div class="text-center py-4">
                <div class="avatar bg-light mb-3">
                    <i class="ti ti-user text-muted"></i>
                </div>
                <p class="text-muted small mb-2">لم يتم تعيين موظف</p>
                <p class="text-muted small">انقر على المفتاح للتعيين</p>
            </div>
        `;
    }

    /*
    |--------------------------------------------------------------------------
    | الحصول على الحروف الأولى من اسم الموظف
    |--------------------------------------------------------------------------
    */
    function getEmployeeInitials(name) {
        if (!name) return '??';

        const words = name.trim().split(' ');
        if (words.length >= 2) {
            return words[0].charAt(0) + words[1].charAt(0);
        }
        return words[0].charAt(0) + words[0].charAt(1);
    }

    /*
    |--------------------------------------------------------------------------
    | التحقق من تفعيل المستوى السابق
    |--------------------------------------------------------------------------
    */
    function isPreviousLevelActive(levels, currentLevel) {
        if (currentLevel === 1) return true;

        const previousLevel = levels.find(l => l.level === currentLevel - 1);
        return previousLevel && previousLevel.employee && previousLevel.employee.name;
    }

    /*
    |--------------------------------------------------------------------------
    | تبديل حالة الاعتماد (تفعيل/إلغاء)
    |--------------------------------------------------------------------------
    */
    function toggleApproval(flowId, level, isApproved, employeeId = null) {
        const url = window.APPROVAL_CONFIG.routes.approve
            .replace('_FLOW_', flowId)
            .replace('_LEVEL_', level);

        const data = {
            _token: window.APPROVAL_CONFIG.csrf,
            approval: isApproved ? 1 : 0,
            employee_id: employeeId
        };

        $.post(url, data)
            .done(function(response) {
                if (!response.success) {
                    toastr.error(response.message || 'حدث خطأ أثناء التحديث');
                    return;
                }

                // تحديث البيانات وإعادة بناء الشبكة
                buildApprovalGrid(flowId, response.data);
                toastr.success(response.message || 'تم التحديث بنجاح');
            })
            .fail(function() {
                toastr.error('فشل في الاتصال بالخادم');
            });
    }

    /*
    |--------------------------------------------------------------------------
    | إضافة مستوى جديد
    |--------------------------------------------------------------------------
    */
    function addNewLevel(flowId) {
        const url = window.APPROVAL_CONFIG.routes.addLevel.replace('_FLOW_', flowId);

        const data = {
            _token: window.APPROVAL_CONFIG.csrf
        };

        $.post(url, data)
            .done(function(response) {
                if (!response.success) {
                    toastr.error(response.message || 'فشل في إضافة المستوى');
                    return;
                }

                // تحديث البيانات وإعادة بناء الشبكة
                buildApprovalGrid(flowId, response.data);
                toastr.success('تم إضافة مستوى جديد بنجاح');
            })
            .fail(function() {
                toastr.error('فشل في الاتصال بالخادم');
            });
    }

    /*
    |--------------------------------------------------------------------------
    | معالج أحداث تغيير حالة المفتاح
    |--------------------------------------------------------------------------
    */
    $(document).on('change', '.approval-checkbox', function() {
        const $checkbox = $(this);
        const flowId = $checkbox.data('flow');
        const level = $checkbox.data('level');

        currentFlow = flowId;
        currentLevel = level;

        if ($checkbox.is(':checked')) {
            // إظهار مودال اختيار الموظف
            $checkbox.prop('checked', false);
            $('#employeeSelectionModal').modal('show');
        } else {
            // إلغاء التعيين
            toggleApproval(flowId, level, false);
        }
    });

    /*
    |--------------------------------------------------------------------------
    | معالج إرسال نموذج اختيار الموظف
    |--------------------------------------------------------------------------
    */
    $('#employeeSelectionForm').on('submit', function(e) {
        e.preventDefault();

        const employeeId = $('#employeeSelect').val();
        if (!employeeId) {
            toastr.error('يرجى اختيار موظف');
            return;
        }

        // تعيين الموظف
        toggleApproval(currentFlow, currentLevel, true, employeeId);

        // إغلاق المودال وإعادة تعيين النموذج
        $('#employeeSelectionModal').modal('hide');
        this.reset();
        $('#employeeSelect').val('').trigger('change');
    });

    /*
    |--------------------------------------------------------------------------
    | معالج زر إضافة مستوى جديد
    |--------------------------------------------------------------------------
    */
    $(document).on('click', '.add-level-btn', function() {
        const flowId = $(this).data('flow');

        // تأكيد الإضافة
        if (confirm('هل أنت متأكد من إضافة مستوى جديد؟')) {
            addNewLevel(flowId);
        }
    });

    /*
    |--------------------------------------------------------------------------
    | إعادة تعيين المودال عند الإغلاق
    |--------------------------------------------------------------------------
    */
    $('#employeeSelectionModal').on('hidden.bs.modal', function() {
        $('#employeeSelectionForm')[0].reset();
        $('#employeeSelect').val('').trigger('change');
        currentFlow = 0;
        currentLevel = 0;
    });

    /*
    |--------------------------------------------------------------------------
    | تهيئة النظام عند تحميل الصفحة
    |--------------------------------------------------------------------------
    */
    initializeComponents();

    /*
    |--------------------------------------------------------------------------
    | رسائل التنبيه المخصصة
    |--------------------------------------------------------------------------
    */
    if (typeof toastr !== 'undefined') {
        toastr.options = {
            closeButton: true,
            debug: false,
            newestOnTop: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            preventDuplicates: false,
            onclick: null,
            showDuration: '300',
            hideDuration: '1000',
            timeOut: '5000',
            extendedTimeOut: '1000',
            showEasing: 'swing',
            hideEasing: 'linear',
            showMethod: 'fadeIn',
            hideMethod: 'fadeOut'
        };
    }

    /*
    |--------------------------------------------------------------------------
    | دوال مساعدة إضافية
    |--------------------------------------------------------------------------
    */
    window.ApprovalFlows = {
        refresh: buildAllTables,
        addLevel: addNewLevel,
        toggleApproval: toggleApproval
    };
});
