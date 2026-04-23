class i{constructor(e,t){this.routes=e,this.csrfToken=t,this.currentFlow=0,this.currentLevel=0,this.flowsData=null,this.init()}init(){this.initializeSelect2(),this.bindEvents()}initializeSelect2(){$(".select2").select2({width:"100%",dir:"rtl",language:"ar",dropdownParent:$("#employeeSelectionModal"),placeholder:"اختر الموظف...",allowClear:!0})}bindEvents(){$(document).on("change",".approval-checkbox",e=>{this.handleApprovalToggle(e)}),$("#employeeSelectionForm").on("submit",e=>{this.handleEmployeeSelection(e)}),$(document).on("click",".add-level-btn",e=>{this.handleAddLevel(e)}),$(document).on("click",".delete-icon",e=>{this.handleDeleteLevel(e)}),$("#employeeSelectionModal").on("hidden.bs.modal",()=>{this.resetEmployeeSelection()})}handleDeleteLevel(e){const t=$(e.currentTarget),l=t.data("flow"),s=t.data("level");Swal.fire({title:"هل أنت متأكد من عملية الحذف؟",text:"لا يمكن التراجع عن هذا الإجراء!",icon:"warning",showCancelButton:!0,showConfirmButton:!0,showDenyButton:!1,buttonsStyling:!1,customClass:{popup:"custom-popup",title:"custom-title",text:"custom-text",confirmButton:"btn btn-success custom-confirm",cancelButton:"btn btn-danger custom-cancel"},confirmButtonText:"تأكيد",cancelButtonText:"إلغاء",reverseButtons:!1}).then(o=>{if(!o.isConfirmed)return;const a=this.routes.deleteLevel.replace("_FLOW_",l).replace("_LEVEL_",s);$.post(a,{_token:this.csrfToken}).done(n=>{n.success?(this.buildApprovalTable(l,n.data),this.showSuccess(n.message)):this.showError(n.message)}).fail(()=>{this.showError("حدث خطأ أثناء حذف المستوى.")})})}handleApprovalToggle(e){const t=$(e.target);this.currentFlow=t.data("flow"),this.currentLevel=t.data("level"),t.is(":checked")?(t.prop("checked",!1),$("#employeeSelectionModal").modal("show")):this.toggleApproval(this.currentFlow,this.currentLevel,!1)}handleEmployeeSelection(e){e.preventDefault();const t=$("#employeeSelect").val();if(!t){this.showError("يرجى اختيار موظف من القائمة.");return}this.toggleApproval(this.currentFlow,this.currentLevel,!0,t),$("#employeeSelectionModal").modal("hide")}handleAddLevel(e){const t=$(e.target).closest(".add-level-btn").data("flow");this.addApprovalLevel(t)}toggleApproval(e,t,l,s=null){const o=this.routes.approve.replace("_FLOW_",e).replace("_LEVEL_",t),a={_token:this.csrfToken,approval:l?1:0};s&&(a.employee_id=s),$.post(o,a).done(n=>{this.handleToggleSuccess(n,e)}).fail(()=>{this.showError("حدث خطأ أثناء معالجة الطلب.")})}addApprovalLevel(e){const t=this.routes.addLevel.replace("_FLOW_",e);$.post(t,{_token:this.csrfToken}).done(l=>{this.handleAddLevelSuccess(l,e)}).fail(()=>{this.showError("تعذر إضافة المستوى الجديد.")})}handleToggleSuccess(e,t){if(!e.success){this.showError(e.message);return}this.buildApprovalTable(t,e.data),this.showSuccess(e.message)}handleAddLevelSuccess(e,t){if(!e.success){this.showError(e.message||"فشل في إضافة المستوى.");return}this.buildApprovalTable(t,e.data),this.showSuccess("تم إضافة مستوى جديد بنجاح.")}buildApprovalTable(e,t){const l=$(`table[data-flow="${e}"]`),s=this.chunkArray(t,4);this.updateFlowData(e,t),this.buildTableBody(l,s,e)}updateFlowData(e,t){this.flowsData||(this.flowsData=window.FLOWS_DATA||{});for(const[l,s]of Object.entries(this.flowsData)){const o=s.findIndex(a=>a.id==e);if(o!==-1){this.flowsData[l][o].levels=t;break}}}buildTableBody(e,t,l){let s="";t.forEach((o,a)=>{s+="<tr>",o.forEach(n=>{s+=this.buildLevelCell(n,l)});for(let n=o.length;n<4;n++)s+='<td class="text-center text-muted">-</td>';s+="</tr>"}),e.find("tbody").html(s)}buildLevelCell(e,t){const l=e.employee!==null,s=this.canAssignLevel(e,t),o=this.canUnassignLevel(e,t),a=l?!o:!s,n=this.getDisableReason(e,t,l,s,o);return`
          <td class="text-center p-3">
            <div class="approval-level-card">
              <div class="toggle-container mb-2">
                <label class="form-check form-switch">
                  <input
                    type="checkbox"
                    class="form-check-input approval-checkbox"
                    data-flow="${t}"
                    data-level="${e.level}"
                    ${l?"checked":""}
                    ${a?"disabled":""}
                    ${n?`title="${n}"`:""}
                  >
                </label>
              </div>
              <div class="employee-info">
                <small class="text-muted d-block">
                  المعتمد <span>${e.level}</span>
                </small>
               <strong class="text-primary">
                ${l?e.employee.name:"غير محدد"}
               </strong>
              </div>

              <!-- trash icon, only when there's no assigned employee -->
              ${l?"":`
                <i
                  class="fas fa-trash delete-icon"
                  data-flow="${t}"
                  data-level="${e.level}"
                  title="حذف المستوى"
                ></i>
              `}
            </div>
          </td>
        `}canAssignLevel(e,t){if(e.level===1)return!0;const l=this.getAllLevelsForFlow(t);for(let s=1;s<e.level;s++){const o=l.find(a=>a.level===s);if(!o||!o.employee)return!1}return!0}canUnassignLevel(e,t){const l=this.getAllLevelsForFlow(t);for(let s=e.level+1;s<=l.length;s++){const o=l.find(a=>a.level===s);if(o&&o.employee)return!1}return!0}getAllLevelsForFlow(e){if(!this.flowsData)return[];for(const[t,l]of Object.entries(this.flowsData)){const s=l.find(o=>o.id==e);if(s&&s.levels)return s.levels}return[]}getDisableReason(e,t,l,s,o){return l&&!o?"يجب إلغاء تعيين المستويات الأعلى أولاً":!l&&!s?"يجب تعيين المستويات السابقة أولاً":""}chunkArray(e,t){return e.reduce((l,s,o)=>{const a=Math.floor(o/t);return l[a]||(l[a]=[]),l[a].push(s),l},[])}resetEmployeeSelection(){$("#employeeSelectionForm")[0].reset(),$("#employeeSelect").val("").trigger("change"),this.currentFlow=0,this.currentLevel=0}showSuccess(e){typeof toastr<"u"?toastr.success(e):alert(e)}showError(e){typeof toastr<"u"?toastr.error(e):alert(e)}buildInitialTables(e){this.flowsData=e,Object.keys(e).forEach(t=>{const l=e[t][0];l&&l.levels&&this.buildApprovalTable(l.id,l.levels)})}}$(document).ready(function(){typeof window.APPROVAL_ROUTES<"u"&&typeof window.CSRF_TOKEN<"u"?(window.approvalManager=new i(window.APPROVAL_ROUTES,window.CSRF_TOKEN),typeof window.FLOWS_DATA<"u"&&window.approvalManager.buildInitialTables(window.FLOWS_DATA)):console.error("Required global variables (APPROVAL_ROUTES, CSRF_TOKEN) are not defined")});
