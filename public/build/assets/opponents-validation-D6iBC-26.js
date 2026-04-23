(function(){document.addEventListener("DOMContentLoaded",function(){const c=$(".select2"),s=document.querySelector("#wizard-validation");if(s){const a=s.querySelector("#power-form"),d=a.querySelectorAll(".content"),m=a.querySelectorAll(".btn-next"),p=a.querySelectorAll(".btn-prev"),i=a.querySelector(".btn-submit"),l=new Stepper(s,{linear:!0,animation:!0}),r=[];d.forEach((t,e)=>{let n={};e===0&&(n={name:{validators:{notEmpty:{message:"الاسم مطلوب."},stringLength:{min:3,max:150,message:"يجب أن يكون الاسم بين 3 و 150 حرف."}}},contact_number:{validators:{callback:{message:"رقم الجوال غير صحيح",callback:function(v){return v.value.trim()===""?!0:window.iti?window.iti.isValidNumber():!1}}}}}),e===1&&(n={});const f=FormValidation.formValidation(t,{fields:n,plugins:{trigger:new FormValidation.plugins.Trigger,bootstrap5:new FormValidation.plugins.Bootstrap5({rowSelector:".col-md-6, .col-md-12",eleValidClass:""}),autoFocus:new FormValidation.plugins.AutoFocus,submitButton:new FormValidation.plugins.SubmitButton}}).on("core.form.valid",function(){e<d.length-1?l.next():(i.setAttribute("disabled","disabled"),i.dataset.oldText=i.innerHTML,i.innerHTML='جار المعالجة <span class="spinner-border spinner-border-sm mx-2" role="status" aria-hidden="true"></span> ',a.submit())});r.push(f)}),m.forEach((t,e)=>{t.addEventListener("click",function(n){n.preventDefault(),r[e].validate()})}),p.forEach((t,e)=>{t.addEventListener("click",function(n){n.preventDefault(),l.previous()})}),c.length&&c.each(function(){var t=$(this);t.wrap('<div class="position-relative"></div>'),t.select2({placeholder:t.data("placeholder")||"اختر خيارًا",dropdownParent:t.parent(),language:"ar"}).on("change",function(){const e=t.attr("name"),n=l._currentIndex;r[n].revalidateField(e)})}),i&&i.addEventListener("click",function(t){t.preventDefault(),r.forEach(e=>e.validate())});const o=document.querySelector("#authorizations-wrapper"),u=document.querySelector("#add-authorization");u&&o&&u.addEventListener("click",function(){const t=o.children.length,e=document.createElement("div");e.classList.add("authorization-item","mb-3"),e.innerHTML=`
            <div class="row g-3">
              <div class="col-sm-3">
                <input type="text" name="authorizations[${t}][name]" class="form-control" placeholder="اسم المفوض" required>
              </div>
              <div class="col-sm-3">
                <input type="text" name="authorizations[${t}][identity_number]" class="form-control" placeholder="رقم الهوية" required>
              </div>
              <div class="col-sm-3">
                <input type="tel" name="authorizations[${t}][phone]" class="form-control" placeholder="رقم الجوال" required>
              </div>
              <div class="col-sm-3">
                <input type="email" name="authorizations[${t}][email]" class="form-control" placeholder="البريد الإلكتروني" required>
              </div>
            </div>
            <button type="button" class="btn btn-danger btn-sm mt-2 remove-authorization">إزالة</button>
          `,o.appendChild(e)}),o&&o.addEventListener("click",function(t){t.target&&t.target.classList.contains("remove-authorization")&&t.target.closest(".authorization-item").remove()})}})})();
