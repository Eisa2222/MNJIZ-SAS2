const d=new Set;(function(){const v=$(".select2"),m=document.querySelector("#wizard-validation");if(m){const a=m.querySelector("#employee-form"),o=a.querySelectorAll(".content"),l=a.querySelectorAll(".btn-next"),g=a.querySelectorAll(".btn-prev"),i=a.querySelector(".btn-submit"),u=new Stepper(m,{linear:!0}),f=[];o.forEach((t,e)=>{const n=FormValidation.formValidation(t,{fields:{...e===0&&{nickname:{validators:{notEmpty:{message:"حقل اللقب مطلوب."},stringLength:{max:20,message:"لا يجوز أن يتجاوز اللقب 20 حرفاً."}}},birth_date:{validators:{notEmpty:{message:"تاريخ الميلاد مطلوب"}}},qualification_degree:{validators:{notEmpty:{message:"درجة المؤهل مطلوبة"}}},personal_email:{validators:{notEmpty:{message:"البريد الإلكتروني الشخصي  مطلوب."},emailAddress:{message:"يرجى إدخال بريد إلكتروني صالح."}}},mobile:{validators:{notEmpty:{message:"رقم الهاتف مطلوب"},callback:{message:"رقم الجوال غير صحيح",callback:function(c){return c.value.trim()===""?!0:window.iti?window.iti.isValidNumber():!1}}}},address:{validators:{notEmpty:{message:"العنوان  مطلوب"},stringLength:{min:5,max:255,message:" العنوان  يجب أن يكون على الاقل 5 احرف و لا يتجازو 255 حرف."}}}},...e===1&&{}},plugins:{trigger:new FormValidation.plugins.Trigger,bootstrap5:new FormValidation.plugins.Bootstrap5({rowSelector:".col-md-4, .col-md-12",eleValidClass:""}),autoFocus:new FormValidation.plugins.AutoFocus,submitButton:new FormValidation.plugins.SubmitButton}}).on("core.form.valid",function(){var p;var c=window.iti.getNumber(),s=document.getElementById("contact_number");if(s&&(s.value=c),e<o.length-1)u.next();else if(d.size>0&&$("#deleted-attachments").val(Array.from(d).join(",")),i.disabled=!0,i.innerHTML="جاري الإرسال...",a){const b=new FormData(a);fetch(a.action||window.location.href,{method:"POST",body:b,headers:{"X-CSRF-TOKEN":(p=document.querySelector('meta[name="csrf-token"]'))==null?void 0:p.getAttribute("content")}}).then(h=>{h.ok?window.location.href=h.url||"/success":(i.disabled=!1,i.innerHTML="إرسال")}).catch(h=>{i.disabled=!1,i.innerHTML="إرسال"})}});f.push(n)}),l.forEach((t,e)=>{t.addEventListener("click",function(n){n.preventDefault(),f[e].validate()})}),g.forEach((t,e)=>{t.addEventListener("click",function(n){n.preventDefault(),u.previous()})});let r=$("#additional-attachments-container .row").length;$("#add-attachment").on("click",function(){const t=$("#additional-attachments-container"),e=`
                <div class="row mb-3" data-index="${r}">
                    <div class="col-md-5">
                        <input type="text"
                            name="additional_attachments[${r}][name]"
                            class="form-control"
                            placeholder="اسم المرفق"
                            required />
                    </div>
                    <div class="col-md-5">
                        <input type="file"
                            name="additional_attachments[${r}][file]"
                            class="form-control"
                            required />
                    </div>
                    <div class="col-md-2">
                        <button type="button"
                            class="btn btn-danger remove-attachment"
                            data-index="${r}">حذف
                        </button>
                    </div>
                </div>
            `;t.append(e),r++}),$(document).on("click",".remove-attachment",function(){const t=$(this).closest(".row"),e=t.data("attachment-id");e!==void 0&&(d.add(e.toString()),$("#deleted-attachments").val(Array.from(d).join(","))),t.fadeOut(300,function(){$(this).remove(),$("#additional-attachments-container .row").each(function(n){$(this).find('input[name^="additional_attachments["]').each(function(){const s=$(this).attr("name").replace(/\[\d+\]/,`[${n}]`);$(this).attr("name",s)})})})}),v.length&&v.each(function(){var t=$(this);t.wrap('<div class="position-relative"></div>'),t.select2({placeholder:t.data("placeholder")||"اختر خيارًا",dropdownParent:t.parent(),language:"ar",allowClear:!0}).on("change",function(){const e=t.attr("name"),n=u._currentIndex;f[n].revalidateField(e)})})}document.querySelectorAll("input.numeric-only").forEach(a=>{a.addEventListener("input",()=>{let o=a.value.replace(/\D/g,"");const l=a.getAttribute("maxlength");l&&(o=o.slice(0,l)),a.value=o})})})();
