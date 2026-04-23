(function(){const l=document.querySelector("#wizard-validation");if(!l)return;const n=l.querySelector("#form");n.querySelectorAll(".content");const i=n.querySelector(".btn-submit");new Stepper(l,{linear:!0,animation:!0});let a,e=0;function o(){const t={"name[]":{validators:{notEmpty:{message:"الاسم مطلوب"},stringLength:{min:2,max:255,message:"الاسم يجب أن يكون بين 2 و 255 حرف"}}},"file[]":{validators:{notEmpty:{message:"المرفق مطلوب"},file:{extension:"pdf",type:"application/pdf",maxSize:10485760,message:"يجب أن يكون الملف من نوع PDF وحجمه أقل من 10 ميغابايت"}}}};a=FormValidation.formValidation(n,{fields:t,plugins:{trigger:new FormValidation.plugins.Trigger,bootstrap5:new FormValidation.plugins.Bootstrap5({rowSelector:'[class*="col-md-"]',eleValidClass:""}),autoFocus:new FormValidation.plugins.AutoFocus,submitButton:new FormValidation.plugins.SubmitButton}}).on("core.form.valid",function(){i.setAttribute("disabled","disabled"),i.dataset.oldText=i.innerHTML,i.innerHTML='جار المعالجة <span class="spinner-border spinner-border-sm mx-2" role="status" aria-hidden="true"></span> ',n.submit()})}o(),i&&i.addEventListener("click",function(t){t.preventDefault(),a.validate()}),$(document).on("click","#addNew",function(){e++;const t=`
           <div class="row g-3 mt-3 additional-fields" id="fields-${e}">
               <div class="col-md-6">
                   <label class="form-label" for="name_${e}">
                       الاسم
                       <span class="text-danger">*</span>
                   </label>
                   <input type="text" name="name[]" id="name_${e}" class="form-control">
               </div>

               <div class="col-md-5">
                   <label class="form-label" for="file_${e}">
                       المرفق
                       <span class="text-danger">*</span>
                   </label>
                   <input type="file" name="file[]" id="file_${e}" class="form-control" accept=".pdf">
                   <small class="text-muted">مسموح PDF فقط الحجم الأقصى 10 ميغابايت</small>
               </div>

               <div class="col-md-1 d-flex align-items-center justify-content-center">
                   <button type="button" class="btn btn-sm btn-danger remove-fields" data-target="fields-${e}">
                       <i class="ti ti-trash"></i>
                   </button>
               </div>
           </div>
       `;$("#addNew").closest(".d-flex").before(t),setTimeout(()=>{a.addField(`name_${e}`,{selector:`#name_${e}`,validators:{notEmpty:{message:"الاسم مطلوب"},stringLength:{min:2,max:255,message:"الاسم يجب أن يكون بين 2 و 255 حرف"}}}),a.addField(`file_${e}`,{selector:`#file_${e}`,validators:{notEmpty:{message:"المرفق مطلوب"},file:{extension:"pdf",type:"application/pdf",maxSize:10485760,message:"يجب أن يكون الملف من نوع PDF وحجمه أقل من 10 ميغابايت"}}}),console.log(`Added validation for name_${e} and file_${e}`)},100)}),$(document).on("click",".remove-fields",function(){const t=$(this).data("target");$(`#${t} input[type="text"], #${t} input[type="file"]`).each(function(){const s=$(this).attr("id");s&&a.removeField(s)}),$("#"+t).remove()})})();
