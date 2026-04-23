$(document).ready(function(){let n=0,c=0,r={};function m(){$(".select2").select2({width:"100%",dir:"rtl",language:"ar",dropdownParent:$("#employeeSelectionModal"),placeholder:"-- اختر الموظف --"}),window.APPROVAL_CONFIG&&window.APPROVAL_CONFIG.flows&&(r=window.APPROVAL_CONFIG.flows),p()}function p(){Object.keys(r).forEach(t=>{const e=r[t].first();e&&e.levels&&d(e.id,e.levels)})}function d(t,e){const l=$(`.approval-levels-container[data-flow="${t}"]`);if(!l.length)return;let a='<div class="grid-levels">';e.forEach(i=>{const o=b(e,i.level),s=i.employee&&i.employee.name;a+=f(t,i,o,s)}),a+="</div>",l.html(a)}function f(t,e,l,a){return`
            <div class="${`approval-level-card ${a?"active":""} ${l?"":"disabled"}`}">
                <div class="${`level-badge ${a?"":"inactive"}`}">${e.level}</div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="fw-bold mb-1">المستوى ${e.level}</h6>
                        <small class="text-muted">${a?"مفعل":"غير مفعل"}</small>
                    </div>

                    <label class="approval-switch">
                        <input type="checkbox" class="approval-checkbox"
                               data-flow="${t}" data-level="${e.level}"
                               ${a?"checked":""} ${l?"":"disabled"}>
                        <span class="approval-slider"></span>
                    </label>
                </div>

                ${a?h(e.employee):g(l)}
            </div>
        `}function h(t){return`
            <div class="employee-info">
                <div class="d-flex align-items-center">
                    <div class="employee-avatar">${w(t.name)}</div>
                    <div class="me-3 flex-grow-1">
                        <h6 class="fw-bold mb-1">${t.name}</h6>
                        <small class="text-muted">معتمد نشط</small>
                    </div>
                </div>
                <div class="status-badge active mt-2">
                    <i class="ti ti-check me-1"></i>
                    المستوى مفعل
                </div>
            </div>
        `}function g(t){return t?`
            <div class="text-center py-4">
                <div class="avatar bg-light mb-3">
                    <i class="ti ti-user text-muted"></i>
                </div>
                <p class="text-muted small mb-2">لم يتم تعيين موظف</p>
                <p class="text-muted small">انقر على المفتاح للتعيين</p>
            </div>
        `:`
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
            `}function w(t){if(!t)return"??";const e=t.trim().split(" ");return e.length>=2?e[0].charAt(0)+e[1].charAt(0):e[0].charAt(0)+e[0].charAt(1)}function b(t,e){if(e===1)return!0;const l=t.find(a=>a.level===e-1);return l&&l.employee&&l.employee.name}function u(t,e,l,a=null){const i=window.APPROVAL_CONFIG.routes.approve.replace("_FLOW_",t).replace("_LEVEL_",e),o={_token:window.APPROVAL_CONFIG.csrf,approval:l?1:0,employee_id:a};$.post(i,o).done(function(s){if(!s.success){toastr.error(s.message||"حدث خطأ أثناء التحديث");return}d(t,s.data),toastr.success(s.message||"تم التحديث بنجاح")}).fail(function(){toastr.error("فشل في الاتصال بالخادم")})}function v(t){const e=window.APPROVAL_CONFIG.routes.addLevel.replace("_FLOW_",t),l={_token:window.APPROVAL_CONFIG.csrf};$.post(e,l).done(function(a){if(!a.success){toastr.error(a.message||"فشل في إضافة المستوى");return}d(t,a.data),toastr.success("تم إضافة مستوى جديد بنجاح")}).fail(function(){toastr.error("فشل في الاتصال بالخادم")})}$(document).on("change",".approval-checkbox",function(){const t=$(this),e=t.data("flow"),l=t.data("level");n=e,c=l,t.is(":checked")?(t.prop("checked",!1),$("#employeeSelectionModal").modal("show")):u(e,l,!1)}),$("#employeeSelectionForm").on("submit",function(t){t.preventDefault();const e=$("#employeeSelect").val();if(!e){toastr.error("يرجى اختيار موظف");return}u(n,c,!0,e),$("#employeeSelectionModal").modal("hide"),this.reset(),$("#employeeSelect").val("").trigger("change")}),$(document).on("click",".add-level-btn",function(){const t=$(this).data("flow");confirm("هل أنت متأكد من إضافة مستوى جديد؟")&&v(t)}),$("#employeeSelectionModal").on("hidden.bs.modal",function(){$("#employeeSelectionForm")[0].reset(),$("#employeeSelect").val("").trigger("change"),n=0,c=0}),m(),typeof toastr<"u"&&(toastr.options={closeButton:!0,debug:!1,newestOnTop:!0,progressBar:!0,positionClass:"toast-top-right",preventDuplicates:!1,onclick:null,showDuration:"300",hideDuration:"1000",timeOut:"5000",extendedTimeOut:"1000",showEasing:"swing",hideEasing:"linear",showMethod:"fadeIn",hideMethod:"fadeOut"}),window.ApprovalFlows={refresh:p,addLevel:v,toggleApproval:u}});
