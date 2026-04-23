function k(t){Swal.fire({title:"تأكيد عملية الحذف",text:"هل أنت متأكد من رغبتك في حذف المهمة؟ .",icon:"warning",showCancelButton:!0,buttonsStyling:!1,customClass:{popup:"custom-popup",title:"custom-title",text:"custom-text",confirmButton:"btn btn-success custom-confirm",cancelButton:"btn btn-danger custom-cancel"},confirmButtonText:"تأكيد",cancelButtonText:"إلغاء"}).then(async e=>{if(e.isConfirmed)try{(await(await fetch(`/tasks/${t}`,{method:"DELETE",headers:{"X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content")}})).json()).success?(toastr.success("تم حذف المهمة بنجاح."),$("#tasks-table").DataTable().ajax.reload(null,!1)):toastr.error("حدث خطأ أثناء حذف المهمة.")}catch{toastr.error("حدث خطأ أثناء حذف المهمة.")}})}function g(t){let e=document.getElementById(`editTaskForm${t}`);if(!e.checkValidity()){e.classList.add("was-validated"),toastr.error("يرجى التأكد من ملء جميع الحقول المطلوبة بشكل صحيح.");return}let a=new FormData(e);a.append("_method","PUT");const n=document.getElementById(`new-attachments-${t}`);if(n&&n.files.length>0){a.has("new_attachments[]")&&a.delete("new_attachments[]");for(let s=0;s<n.files.length;s++)a.append("new_attachments[]",n.files[s])}$.ajax({url:`/tasks/${t}`,type:"PUT",data:a,contentType:!1,processData:!1,headers:{"X-CSRF-TOKEN":$('meta[name="csrf-token"]').attr("content")},success:function(s){if(s.success){toastr.success(s.message);let i=document.getElementById(`editTaskOffcanvas${t}`),l=bootstrap.Offcanvas.getInstance(i);l&&l.hide(),location.reload()}else toastr.error(s.message||"حدث خطأ ما.")},error:function(s){console.error("Error:",s.responseText),toastr.error("حدث خطأ أثناء الحفظ.")}})}function m(t){const e=$(`#editTaskOffcanvas${t}`);e.find(".select2").each(function(){$(this).select2({dropdownParent:e,dir:"rtl",templateResult:d,templateSelection:u,escapeMarkup:function(i){return i}})}),$(`#assign-myself-btn-edit-${t}`).on("click",function(){var i=$(this).data("user-id");$(`#assigned_user_id_edit${t}`).val([i]).trigger("change")});const a=e.find(`#hasSteps_edit${t}`),n=e.find(`#stepsContainer_edit${t}`);a.off("change").on("change",function(){this.checked?(n.show(),n.find(".step-block").length===0&&b(t),n.find("input, select, textarea").prop("disabled",!1),n.find('input[name="step_name[]"], select').attr("required","required")):(n.hide(),n.find("input, select, textarea").prop("disabled",!0),n.find('input[name="step_name[]"], select').removeAttr("required"))}),e.find(`#addStepButton_edit${t}`).off("click").on("click",function(){b(t)}),e.on("click",".remove-step-btn",function(){const i=$(this).closest(".step-block");i.parent().find(".step-block").length>1?i.remove():(i.remove(),a.prop("checked",!1),n.hide()),p(t)});const s=document.getElementById(`editTaskForm${t}`);$(s).off("submit").on("submit",function(i){if(i.preventDefault(),!s.checkValidity()){s.classList.add("was-validated"),toastr.error("يرجى التأكد من ملء جميع الحقول المطلوبة بشكل صحيح.");return}g(t)})}function d(t){if(!t.id)return t.text;var e=$(t.element).data("image"),a=t.text,n=e?`<img src="${e}" alt="${a}" style="width: 30px; height: 30px; border-radius: 50%; margin-right: 10px;">`:`<div style="width: 30px; height: 30px; background-color: #007bff; color: white; font-weight: bold; text-align: center; line-height: 30px; border-radius: 50%; margin-right: 10px;">${a.charAt(0)}</div>`;return`<div class="d-flex align-items-center">${n}<span>${a}</span></div>`}function u(t){if(!t.id)return t.text;var e=$(t.element).data("image"),a=t.text,n=e?`<img src="${e}" alt="${a}" style="width: 20px; height: 20px; border-radius: 50%; margin-right: 5px;">`:`<div style="width: 20px; height: 20px; background-color: #007bff; color: white; font-weight: bold; text-align: center; line-height: 20px; border-radius: 50%; margin-right: 5px;">${a.charAt(0)}</div>`;return`<div class="d-flex align-items-center">${n}<span>${a}</span></div>`}function v(t){const e=$(t).attr("id")==="task_field";$(t).select2({placeholder:$(t).data("placeholder"),allowClear:!0,width:"100%",language:"ar",dir:"rtl",templateResult:function(a){if(!e&&a.element&&$(a.element).data("image")){var n=$(a.element).data("image"),s=a.text,i=n?`<img src="${n}" alt="${s}" style="width: 30px; height: 30px; border-radius: 50%; margin-right: 10px;">`:`<div style="width: 30px; height: 30px; background-color: #007bff; color: white; font-weight: bold; text-align: center; line-height: 30px; border-radius: 50%; margin-right: 10px;">${s.charAt(0)}</div>`;return $(`<div class="d-flex align-items-center">${i}<span>${s}</span></div>`)}return a.text},templateSelection:function(a){if(!e&&a.element&&$(a.element).data("image")){var n=$(a.element).data("image"),s=a.text,i=n?`<img src="${n}" alt="${s}" style="width: 20px; height: 20px; border-radius: 50%; margin-right: 5px;">`:`<div style="width: 20px; height: 20px; background-color: #007bff; color: white; font-weight: bold; text-align: center; line-height: 20px; border-radius: 50%; margin-right: 5px;">${s.charAt(0)}</div>`;return $(`<div class="d-flex align-items-center">${i}<span>${s}</span></div>`)}return a.text},escapeMarkup:function(a){return a},dropdownParent:$(t).closest(".offcanvas-end")})}function f(){$("#task_field").select2({placeholder:"اختر مجال المهمة",allowClear:!0,width:"100%",language:"ar",dir:"rtl",templateResult:function(t){return t.id,t.text},templateSelection:function(t){return t.id,t.text},escapeMarkup:function(t){return t},dropdownParent:$("#addTaskOffcanvas")})}function p(t){$(t+" .step-block").each(function(e,a){$(a).find(".step-number").text("الخطوة "+(e+1)),$(a).attr("data-index",e)})}function w(t){const e="#stepsContainer_edit"+t,a="#addStepButton_edit"+t;function n(){return $(e+" .step-block").length||0}let s=n();function i(){let l="";const r=$(`#stepsContainer_edit${t} .step-block:first select`);if(r.length>0)l=r.find("option").map(function(){return`<option value="${this.value}" data-image="${$(this).data("image")}">${this.text}</option>`}).get().join("");else{const h=$(`#assigned_user_id_edit${t}`);if(h.length>0)l=h.find("option").map(function(){return`<option value="${this.value}" data-image="${$(this).data("image")}">${this.text}</option>`}).get().join("");else{console.error("لا يمكن إيجاد قائمة المستخدمين");return}}const o=`step_${t}_${s}`,c=`
        <div class="step-block mb-3 border p-3" data-index="${s}" id="${o}">
            <div class="step-header mb-2">
                <strong class="step-number">الخطوة ${s+1}</strong>
            </div>
            <div class="row">
                <div class="col-9">
                    <label class="form-label">اسم الخطوة</label>
                    <input type="text" name="step_name[]" class="form-control" placeholder="اسم الخطوة" required>
                    <div class="invalid-feedback">اسم الخطوة مطلوب</div>
                </div>
                <div class="col-3">
                    <label class="form-label">تحتاج لاعتماد؟</label>
                    <span title="عند تفعيل الاعتماد سيظهر للمستخدم قبول او رفض وعند الرفض سيتم ارجاع المهمة لمراجعتها" style="color: var(--primary-color);">
                        <i class="fa fa-question-circle fa-lg" aria-hidden="true"></i>
                    </span>
                    <div class="form-check">
                        <input type="checkbox" name="needs_approval[${s}]" class="form-check-input" value="1">
                        <label class="form-check-label">نعم</label>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label mt-2">المكلفين</label>
                    <select name="step_assigned_user_ids[${s}][]" class="form-select-new" multiple required data-placeholder="اختر الموظفين">
                        ${l}
                    </select>
                    <div class="invalid-feedback">الرجاء اختيار موظف واحد على الأقل</div>
                </div>
            </div>
            <div class="mt-2 text-end">
                <button type="button" class="btn btn-danger btn-sm remove-step-btn">حذف الخطوة</button>
            </div>
        </div>
      `;return $(c)[0]}$(document).on("click",a,function(){const l=i();if(!l)return;$(this).before(l);let r=l.querySelector(".form-select-new");$(r).removeClass("form-select-new").addClass("form-select").select2({placeholder:"اختر الموظفين",allowClear:!0,width:"100%",language:"ar",dir:"rtl",templateResult:d,templateSelection:u,escapeMarkup:function(o){return o},dropdownParent:$(`#editTaskOffcanvas${t}`)}),s++,p(e)}),$(document).on("click",e+" .remove-step-btn",function(){const l=$(e),r=$(this).closest(".step-block");l.children(".step-block").length>1?r.remove():(r.remove(),l.hide(),$(`#hasSteps_edit${t}`).prop("checked",!1)),l.children(".step-block").each(function(o){$(this).find(".step-number").text("الخطوة "+(o+1)),$(this).attr("data-index",o)}),s=l.children(".step-block").length||0}),$(`${e} .step-block select`).each(function(){$(this).hasClass("select2-hidden-accessible")||$(this).select2({placeholder:"اختر الموظفين",allowClear:!0,width:"100%",language:"ar",dir:"rtl",templateResult:d,templateSelection:u,escapeMarkup:function(l){return l},dropdownParent:$(`#editTaskOffcanvas${t}`)})})}function x(){const t="#stepsContainer",e="#addStepButton";function a(){return $(t+" .step-block").length||0}let n=a();function s(){const i=$("#assigned_user_id option").map(function(){return`<option value="${this.value}" data-image="${$(this).data("image")}">${this.text}</option>`}).get().join(""),l=`
        <div class="step-block mb-3 border p-3" data-index="${n}">
            <div class="step-header mb-2">
                <strong class="step-number">الخطوة ${n+1}</strong>
            </div>
            <div class="row">
                <div class="col-9">
                    <label class="form-label">اسم الخطوة</label>
                    <input type="text" name="step_name[]" class="form-control" placeholder="اسم الخطوة" required>
                    <div class="invalid-feedback">اسم الخطوة مطلوب</div>
                </div>
                <div class="col-3">
                    <label class="form-label">تحتاج لاعتماد؟</label>
                    <span title="عند تفعيل الاعتماد سيظهر للمستخدم قبول او رفض" style="color: var(--primary-color);">
                        <i class="fa fa-question-circle fa-lg" aria-hidden="true"></i>
                    </span>
                    <div class="form-check">
                        <input type="checkbox" name="needs_approval[${n}]" class="form-check-input" value="1">
                        <label class="form-check-label">نعم</label>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label mt-2">المكلفين</label>
                    <select name="step_assigned_user_ids[${n}][]" class="form-select-new" multiple required data-placeholder="اختر الموظفين">
                        ${i}
                    </select>
                    <div class="invalid-feedback">الرجاء اختيار موظف واحد على الأقل</div>
                </div>
            </div>
            <div class="mt-2 text-end">
                <button type="button" class="btn btn-danger btn-sm remove-step-btn">حذف الخطوة</button>
            </div>
        </div>
      `;return $(l)[0]}$(e).off("click").on("click",function(){const i=s();this.parentNode.insertBefore(i,this);let l=i.querySelector(".form-select-new");$(l).removeClass("form-select-new").addClass("form-select").select2({placeholder:"اختر الموظفين",allowClear:!0,width:"100%",language:"ar",dir:"rtl",templateResult:d,templateSelection:u,escapeMarkup:function(r){return r},dropdownParent:$("#addTaskOffcanvas")}),n++}),$(t).off("click",".remove-step-btn").on("click",".remove-step-btn",function(){const i=$(t),l=$(this).closest(".step-block");i.children(".step-block").length>1?l.remove():(l.remove(),i.hide(),$("#hasSteps").prop("checked",!1)),i.children(".step-block").each(function(r){$(this).find(".step-number").text("الخطوة "+(r+1)),$(this).attr("data-index",r)}),n=i.children(".step-block").length||0}),$(t+" .step-block select").each(function(){$(this).hasClass("select2-hidden-accessible")||$(this).select2({placeholder:"اختر الموظفين",allowClear:!0,width:"100%",language:"ar",dir:"rtl",templateResult:d,templateSelection:u,escapeMarkup:function(i){return i},dropdownParent:$("#addTaskOffcanvas")})})}$(document).ready(function(){$(document).on("shown.bs.offcanvas",".offcanvas",function(){const e=this.id.replace("editTaskOffcanvas","");e&&m(e)}),$("#tasks-table").on("draw.dt",function(){$(".edit-task").each(function(){const e=$(this).data("task-id");e&&m(e)})}),$(".select2").each(function(){$(this).attr("id")==="task_field"?f():v(this)}),$("#addTaskOffcanvas").on("shown.bs.offcanvas",function(){f()}),$(document).on("shown.bs.offcanvas",".offcanvas-edit",function(){f()}),x();var t=[].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));t.map(function(e){return new bootstrap.Popover(e,{html:!0,content:function(){var a=this.getAttribute("data-bs-content");return document.querySelector(a).innerHTML}})}),$("#hasSteps").on("change",function(){var e=$("#stepsContainer");$(this).is(":checked")?(e.show(),e.find("input, select, textarea").prop("disabled",!1),e.find('input[name="step_name[]"], select').attr("required","required")):(e.hide(),e.find("input, select, textarea").prop("disabled",!0),e.find('input[name="step_name[]"], select').removeAttr("required"))}),$("#addTaskForm").on("submit",function(e){let a=document.getElementById("addTaskForm");if(!a.checkValidity()){a.classList.add("was-validated"),toastr.error("يرجى التأكد من ملء جميع الحقول المطلوبة بشكل صحيح.");return}var n=$("#assigned_user_id").val(),s=$("#assigned_user_id").data("select2"),i=s?s.$container:null,l=!0;!n||n.length===0?(i&&i.addClass("is-invalid"),l=!1):i&&i.removeClass("is-invalid"),$("#stepsContainer select[required]").each(function(){var r=$(this).val(),o=$(this).data("select2"),c=o?o.$container:null;!r||r.length===0?(c&&c.addClass("is-invalid"),l=!1):c&&c.removeClass("is-invalid")}),l||(e.preventDefault(),e.stopPropagation()),document.getElementById("hasSteps").checked?$("#stepsContainer input, #stepsContainer select, #stepsContainer textarea").prop("disabled",!1):$("#stepsContainer input, #stepsContainer select, #stepsContainer textarea").prop("disabled",!0)}),$("#assigned_user_id").on("select2:select",function(){var e=$(this).data("select2"),a=e?e.$container:null;a&&a.removeClass("is-invalid")}),$("#assigned_user_id").on("select2:unselect",function(){var e=$(this).val(),a=$(this).data("select2"),n=a?a.$container:null;(!e||e.length===0)&&n&&n.addClass("is-invalid")}),$(document).on("change",".select2",function(){var e=$(this).val(),a=$(this).data("select2"),n=a?a.$container:null;$(this).is("[required]")?e&&e.length>0?n&&n.removeClass("is-invalid"):n&&n.addClass("is-invalid"):n&&n.removeClass("is-invalid")}),$("#addTaskOffcanvas").on("shown.bs.offcanvas",function(){$(this).find(".select2").each(function(){v(this)})}),$("#addTaskOffcanvas").on("hidden.bs.offcanvas",function(){$(this).find(".select2").each(function(){$(this).data("select2")&&$(this).select2("destroy")})}),$(document).on("shown.bs.offcanvas",".offcanvas-edit",function(){var e=$(this).attr("id").replace("editTaskOffcanvas","");w(e),$(`#hasSteps_edit${e}`).off("change").on("change",function(){const a=$(`#stepsContainer_edit${e}`);$(this).is(":checked")?(a.show(),a.find("input, select, textarea").prop("disabled",!1),a.find('input[name="step_name[]"], select').attr("required","required")):(a.hide(),a.find("input, select, textarea").prop("disabled",!0),a.find('input[name="step_name[]"], select').removeAttr("required"))})}),$("#assign-myself-btn-create").on("click",function(){$("#assigned_user_id").val([currentUserId]).trigger("change")}),$(document).on("click","[id^=assign-myself-btn-edit-]",function(){var e=$(this).attr("id").replace("assign-myself-btn-edit-","");$("#assigned_user_id_edit"+e).val([currentUserId]).trigger("change")})});(function(){window.addEventListener("load",function(){var t=document.getElementsByClassName("needs-validation");Array.prototype.filter.call(t,function(e){e.addEventListener("submit",function(a){e.checkValidity()===!1&&(a.preventDefault(),a.stopPropagation()),e.classList.add("was-validated")},!1)})},!1)})();function b(t){const e=$(`#stepsContainer_edit${t}`),a=e.find(".step-block").length,n=$(`#assigned_user_id_edit${t} option`).map(function(){return`<option value="${this.value}" data-image="${$(this).data("image")}">${this.text}</option>`}).get().join(""),s=`
        <div class="step-block mb-3 border p-3" data-index="${a}">
            <div class="step-header mb-2">
                <strong class="step-number">الخطوة ${a+1}</strong>
            </div>
            <div class="row">
                <div class="col-9">
                    <label class="form-label">اسم الخطوة</label>
                    <input type="text" name="step_name[]" class="form-control" placeholder="اسم الخطوة" required>
                    <div class="invalid-feedback">اسم الخطوة مطلوب</div>
                </div>
                <div class="col-3">
                    <label class="form-label">تحتاج لاعتماد؟</label>
                    <span title="عند تفعيل الاعتماد سيظهر للمستخدم قبول او رفض" style="color: var(--primary-color);">
                        <i class="fa fa-question-circle fa-lg"></i>
                    </span>
                    <div class="form-check">
                        <input type="checkbox" name="needs_approval[${a}]" class="form-check-input" value="1">
                        <label class="form-check-label">نعم</label>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label mt-2">المكلفين</label>
                    <select name="step_assigned_user_ids[${a}][]" class="form-select select2-new" multiple required data-placeholder="اختر الموظفين">
                        ${n}
                    </select>
                    <div class="invalid-feedback">الرجاء اختيار موظف واحد على الأقل</div>
                </div>
            </div>
            <div class="mt-2 text-end">
                <button type="button" class="btn btn-danger btn-sm remove-step-btn">
                    حذف الخطوة
                </button>
            </div>
        </div>
    `;$(s).insertBefore(e.find(`#addStepButton_edit${t}`)),e.find(".select2-new").each(function(){$(this).removeClass("select2-new").select2({dropdownParent:$(`#editTaskOffcanvas${t}`),dir:"rtl",templateResult:d,templateSelection:u,escapeMarkup:function(i){return i}})}),p(t)}window.updateTask=g;window.confirmDeleteTask=k;$(document).ready(function(){$(window).on("load",function(){$("#filter-priority, #filter-task-field, #filter-status").each(function(){$(this).hasClass("select2-hidden-accessible")&&$(this).select2("destroy"),$(this).select2({placeholder:$(this).data("placeholder")||"اختر...",allowClear:!0,width:"100%",language:"ar",dir:"rtl",dropdownParent:$("body"),minimumResultsForSearch:5})})}),$("#tasks-table").on("draw.dt",function(){$(".select2-container").css("width","100%")}),$(document).on("select2:open",function(){$(".select2-dropdown").css("z-index",9999)})});document.addEventListener("DOMContentLoaded",function(){setTimeout(function(){$.fn.select2&&$("#filter-priority, #filter-task-field, #filter-status").each(function(){$(this).data("select2")||$(this).select2({placeholder:$(this).data("placeholder")||"اختر...",allowClear:!0,width:"100%",language:"ar",dir:"rtl",dropdownParent:$("body")})})},500)});
