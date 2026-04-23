/**
 * ========================================================================
 * Approval Management System
 * ========================================================================
 * Handles the approval flow management interface including level toggles,
 * employee assignments, and dynamic table generation.
 */

class ApprovalManager {
    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    | Initialize the approval management system with required configurations.
    */
    constructor(routes, csrfToken) {
        this.routes = routes;
        this.csrfToken = csrfToken;
        this.currentFlow = 0;
        this.currentLevel = 0;
        this.flowsData = null; // Store flows data locally for validation

        this.init();
    }

    /*
    |--------------------------------------------------------------------------
    | Initialize System
    |--------------------------------------------------------------------------
    | Set up all event listeners and initial configurations.
    */
    init() {
        this.initializeSelect2();
        this.bindEvents();
    }

    /*
    |--------------------------------------------------------------------------
    | Initialize Select2 Dropdown
    |--------------------------------------------------------------------------
    | Configure Select2 for employee selection with RTL support.
    */
    initializeSelect2() {
        $('.select2').select2({
            width: '100%',
            dir: 'rtl',
            language: 'ar',
            dropdownParent: $('#employeeSelectionModal'),
            placeholder: 'اختر الموظف...',
            allowClear: true
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Bind Event Listeners
    |--------------------------------------------------------------------------
    | Attach all necessary event handlers for user interactions.
    */
    bindEvents() {
        // Approval checkbox toggle
        $(document).on('change', '.approval-checkbox', (e) => {
            this.handleApprovalToggle(e);
        });

        // Employee selection form submission
        $('#employeeSelectionForm').on('submit', (e) => {
            this.handleEmployeeSelection(e);
        });

        // Add new level button
        $(document).on('click', '.add-level-btn', (e) => {
            this.handleAddLevel(e);
        });

        // Delete level icon click
        $(document).on('click', '.delete-icon', (e) => {
            this.handleDeleteLevel(e);
        });

        // Modal events
        $('#employeeSelectionModal').on('hidden.bs.modal', () => {
            this.resetEmployeeSelection();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Handle Delete Level
    |--------------------------------------------------------------------------
    | Process level deletion with confirmation dialog.
    */
    handleDeleteLevel(event) {
        const $icon = $(event.currentTarget);
        const flowId = $icon.data('flow');
        const level = $icon.data('level');

        Swal.fire({
            title: 'هل أنت متأكد من عملية الحذف؟',
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
            if (!result.isConfirmed) return;

            const url = this.routes.deleteLevel
                .replace('_FLOW_', flowId)
                .replace('_LEVEL_', level);

            $.post(url, { _token: this.csrfToken })
                .done((response) => {
                    if (response.success) {
                        this.buildApprovalTable(flowId, response.data);
                        this.showSuccess(response.message);
                    } else {
                        this.showError(response.message);
                    }
                })
                .fail(() => {
                    this.showError('حدث خطأ أثناء حذف المستوى.');
                });
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Handle Approval Toggle
    |--------------------------------------------------------------------------
    | Process approval checkbox changes and show employee selection if needed.
    */
    handleApprovalToggle(event) {
        const checkbox = $(event.target);
        this.currentFlow = checkbox.data('flow');
        this.currentLevel = checkbox.data('level');

        if (checkbox.is(':checked')) {
            // Prevent immediate checking and show employee selection modal
            checkbox.prop('checked', false);
            $('#employeeSelectionModal').modal('show');
        } else {
            // Unassign employee
            this.toggleApproval(this.currentFlow, this.currentLevel, false);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Handle Employee Selection
    |--------------------------------------------------------------------------
    | Process employee selection form submission.
    */
    handleEmployeeSelection(event) {
        event.preventDefault();

        const employeeId = $('#employeeSelect').val();
        if (!employeeId) {
            this.showError('يرجى اختيار موظف من القائمة.');
            return;
        }

        this.toggleApproval(this.currentFlow, this.currentLevel, true, employeeId);
        $('#employeeSelectionModal').modal('hide');
    }

    /*
    |--------------------------------------------------------------------------
    | Handle Add Level
    |--------------------------------------------------------------------------
    | Process adding new approval levels to flows.
    */
    handleAddLevel(event) {
        const flowId = $(event.target).closest('.add-level-btn').data('flow');
        this.addApprovalLevel(flowId);
    }

    /*
    |--------------------------------------------------------------------------
    | Toggle Approval Assignment
    |--------------------------------------------------------------------------
    | Send AJAX request to toggle approval level assignment.
    */
    toggleApproval(flowId, level, assign, employeeId = null) {
        const url = this.routes.approve
            .replace('_FLOW_', flowId)
            .replace('_LEVEL_', level);

        const data = {
            _token: this.csrfToken,
            approval: assign ? 1 : 0
        };

        if (employeeId) {
            data.employee_id = employeeId;
        }

        $.post(url, data)
            .done((response) => {
                this.handleToggleSuccess(response, flowId);
            })
            .fail(() => {
                this.showError('حدث خطأ أثناء معالجة الطلب.');
            });
    }

    /*
    |--------------------------------------------------------------------------
    | Add Approval Level
    |--------------------------------------------------------------------------
    | Send AJAX request to add new approval level.
    */
    addApprovalLevel(flowId) {
        const url = this.routes.addLevel.replace('_FLOW_', flowId);

        $.post(url, {
            _token: this.csrfToken
        })
            .done((response) => {
                this.handleAddLevelSuccess(response, flowId);
            })
            .fail(() => {
                this.showError('تعذر إضافة المستوى الجديد.');
            });
    }

    /*
    |--------------------------------------------------------------------------
    | Handle Toggle Success Response
    |--------------------------------------------------------------------------
    | Process successful approval toggle responses.
    */
    handleToggleSuccess(response, flowId) {
        if (!response.success) {
            this.showError(response.message);
            return;
        }

        this.buildApprovalTable(flowId, response.data);
        this.showSuccess(response.message);
    }

    /*
    |--------------------------------------------------------------------------
    | Handle Add Level Success Response
    |--------------------------------------------------------------------------
    | Process successful add level responses.
    */
    handleAddLevelSuccess(response, flowId) {
        if (!response.success) {
            this.showError(response.message || 'فشل في إضافة المستوى.');
            return;
        }

        this.buildApprovalTable(flowId, response.data);
        this.showSuccess('تم إضافة مستوى جديد بنجاح.');
    }

    /*
    |--------------------------------------------------------------------------
    | Build Approval Table
    |--------------------------------------------------------------------------
    | Dynamically generate approval table with proper layout and controls.
    */
    buildApprovalTable(flowId, levels) {
        const table = $(`table[data-flow="${flowId}"]`);
        const rows = this.chunkArray(levels, 4);

        // Update local flows data for validation
        this.updateFlowData(flowId, levels);

        // Build table body
        this.buildTableBody(table, rows, flowId);
    }

    /*
    |--------------------------------------------------------------------------
    | Update Flow Data
    |--------------------------------------------------------------------------
    | Update local flows data with new levels information for validation.
    */
    updateFlowData(flowId, levels) {
        if (!this.flowsData) {
            this.flowsData = window.FLOWS_DATA || {};
        }

        // Find and update the flow data
        for (const [type, flows] of Object.entries(this.flowsData)) {
            const flowIndex = flows.findIndex(f => f.id == flowId);
            if (flowIndex !== -1) {
                this.flowsData[type][flowIndex].levels = levels;
                break;
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Build Table Body
    |--------------------------------------------------------------------------
    | Generate table body with approval controls and employee assignments.
    */
    buildTableBody(table, rows, flowId) {
        let bodyHtml = '';

        rows.forEach((row, rowIndex) => {
            bodyHtml += '<tr>';

            // Add level cells
            row.forEach(level => {
                bodyHtml += this.buildLevelCell(level, flowId);
            });

            // Fill empty cells to complete 4 columns
            for (let i = row.length; i < 4; i++) {
                bodyHtml += '<td class="text-center text-muted">-</td>';
            }

            bodyHtml += '</tr>';
        });

        table.find('tbody').html(bodyHtml);
    }

    /*
    |--------------------------------------------------------------------------
    | Build Level Cell
    |--------------------------------------------------------------------------
    | Generate individual level cell with toggle switch and employee info.
    */
    buildLevelCell(level, flowId) {
        const isAssigned = level.employee !== null;
        const canAssign = this.canAssignLevel(level, flowId);
        const canUnassign = this.canUnassignLevel(level, flowId);

        // Disable checkbox if cannot assign/unassign
        const isDisabled = isAssigned ? !canUnassign : !canAssign;
        const disableReason = this.getDisableReason(level, flowId, isAssigned, canAssign, canUnassign);

        return `
          <td class="text-center p-3">
            <div class="approval-level-card">
              <div class="toggle-container mb-2">
                <label class="form-check form-switch">
                  <input
                    type="checkbox"
                    class="form-check-input approval-checkbox"
                    data-flow="${flowId}"
                    data-level="${level.level}"
                    ${isAssigned ? 'checked' : ''}
                    ${isDisabled ? 'disabled' : ''}
                    ${disableReason ? `title="${disableReason}"` : ''}
                  >
                </label>
              </div>
              <div class="employee-info">
                <small class="text-muted d-block">
                  المعتمد <span>${level.level}</span>
                </small>
               <strong class="text-primary">
                ${isAssigned ? level.employee.name : 'غير محدد'}
               </strong>
              </div>

              <!-- trash icon, only when there's no assigned employee -->
              ${!isAssigned ? `
                <i
                  class="fas fa-trash delete-icon"
                  data-flow="${flowId}"
                  data-level="${level.level}"
                  title="حذف المستوى"
                ></i>
              ` : ''}
            </div>
          </td>
        `;
    }

    /*
    |--------------------------------------------------------------------------
    | Check if Can Assign Level
    |--------------------------------------------------------------------------
    | Check if an employee can be assigned to this level (previous levels must be assigned).
    */
    canAssignLevel(level, flowId) {
        // First level can always be assigned
        if (level.level === 1) {
            return true;
        }

        // Check if all previous levels are assigned
        const allLevels = this.getAllLevelsForFlow(flowId);
        for (let i = 1; i < level.level; i++) {
            const previousLevel = allLevels.find(l => l.level === i);
            if (!previousLevel || !previousLevel.employee) {
                return false;
            }
        }

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Check if Can Unassign Level
    |--------------------------------------------------------------------------
    | Check if an employee can be unassigned from this level (no higher levels should be assigned).
    */
    canUnassignLevel(level, flowId) {
        // Check if any higher levels are assigned
        const allLevels = this.getAllLevelsForFlow(flowId);

        for (let i = level.level + 1; i <= allLevels.length; i++) {
            const higherLevel = allLevels.find(l => l.level === i);
            if (higherLevel && higherLevel.employee) {
                return false;
            }
        }

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Get All Levels For Flow
    |--------------------------------------------------------------------------
    | Get all levels data for a specific flow from local storage.
    */
    getAllLevelsForFlow(flowId) {
        if (!this.flowsData) {
            return [];
        }

        // Search through all flow types to find the matching flow
        for (const [type, flows] of Object.entries(this.flowsData)) {
            const flow = flows.find(f => f.id == flowId);
            if (flow && flow.levels) {
                return flow.levels;
            }
        }

        return [];
    }

    /*
    |--------------------------------------------------------------------------
    | Get Disable Reason
    |--------------------------------------------------------------------------
    | Get tooltip text explaining why a checkbox is disabled.
    */
    getDisableReason(level, flowId, isAssigned, canAssign, canUnassign) {
        if (isAssigned && !canUnassign) {
            return 'يجب إلغاء تعيين المستويات الأعلى أولاً';
        }

        if (!isAssigned && !canAssign) {
            return 'يجب تعيين المستويات السابقة أولاً';
        }

        return '';
    }

    /*
    |--------------------------------------------------------------------------
    | Chunk Array Utility
    |--------------------------------------------------------------------------
    | Split array into chunks of specified size for table layout.
    */
    chunkArray(array, size) {
        return array.reduce((chunks, item, index) => {
            const chunkIndex = Math.floor(index / size);
            if (!chunks[chunkIndex]) chunks[chunkIndex] = [];
            chunks[chunkIndex].push(item);
            return chunks;
        }, []);
    }

    /*
    |--------------------------------------------------------------------------
    | Reset Employee Selection
    |--------------------------------------------------------------------------
    | Clear employee selection form and reset values.
    */
    resetEmployeeSelection() {
        $('#employeeSelectionForm')[0].reset();
        $('#employeeSelect').val('').trigger('change');
        this.currentFlow = 0;
        this.currentLevel = 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Show Success Message
    |--------------------------------------------------------------------------
    | Display success notification to user.
    */
    showSuccess(message) {
        if (typeof toastr !== 'undefined') {
            toastr.success(message);
        } else {
            alert(message);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Show Error Message
    |--------------------------------------------------------------------------
    | Display error notification to user.
    */
    showError(message) {
        if (typeof toastr !== 'undefined') {
            toastr.error(message);
        } else {
            alert(message);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Build Initial Tables
    |--------------------------------------------------------------------------
    | Generate initial table structure for all flows.
    */
    buildInitialTables(flowsData) {
        // Store flows data locally for validation purposes
        this.flowsData = flowsData;

        // Build tables for each flow type
        Object.keys(flowsData).forEach(type => {
            const flow = flowsData[type][0];
            if (flow && flow.levels) {
                this.buildApprovalTable(flow.id, flow.levels);
            }
        });
    }
}

/*
|--------------------------------------------------------------------------
| Initialize Approval Manager
|--------------------------------------------------------------------------
| Set up the approval management system when document is ready.
*/
$(document).ready(function () {
    // Ensure required global variables are available
    if (typeof window.APPROVAL_ROUTES !== 'undefined' && typeof window.CSRF_TOKEN !== 'undefined') {
        // Initialize the approval manager
        window.approvalManager = new ApprovalManager(window.APPROVAL_ROUTES, window.CSRF_TOKEN);

        // Build initial tables if flows data is available
        if (typeof window.FLOWS_DATA !== 'undefined') {
            window.approvalManager.buildInitialTables(window.FLOWS_DATA);
        }
    } else {
        console.error('Required global variables (APPROVAL_ROUTES, CSRF_TOKEN) are not defined');
    }
});
