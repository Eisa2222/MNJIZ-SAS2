let p=0;function z(){["#contact_id","#parent_id","#inventory_id","#status"].forEach(function(i){const l=$(i);l.length&&!l.hasClass("select2-hidden-accessible")&&l.select2({dropdownParent:$("#wizard-validation"),placeholder:" ",allowClear:!0,width:"100%",language:"ar",dir:"rtl"})})}document.addEventListener("DOMContentLoaded",function(){const i=document.getElementById("emptyMessage");i&&(i.style.display="block"),x();const l=document.getElementById("addLineItem");l&&l.addEventListener("click",x)});function x(){p++;const i=document.querySelector("#lineItemsTable tbody"),l=document.getElementById("emptyMessage"),a=document.createElement("tr");a.setAttribute("data-item",p),a.innerHTML=`
        <td rowspan="3" class="merged-number-cell">${p}</td>
        <td>
            <select id="product_id_${p}" name="line_items[${p}][product_id]"
           class="select2 form-select product-select" required>
       <option value="">اختر منتجاً</option>
       ${productOptions}
   </select>
        </td>
        <td>
            <input type="text" name="line_items[${p}][description]" 
                   class="form-control" placeholder="الوصف">
        </td>
        <td>
            <input type="number" name="line_items[${p}][quantity]" 
                   class="form-control text-center quantity-input" 
                   min="1" step="1" value="1" required>
        </td>
        <td>
            <input type="number" name="line_items[${p}][unit_price]" 
                   class="form-control text-center price-input" 
                   min="0" step="0.01" value="0.00" required>
        </td>
        <td>
            <div class="input-group">
                <input type="number" name="line_items[${p}][discount]" 
                       class="form-control text-center discount-input  mb-1" 
                       min="0" step="0.01" value="0.00" 
                       placeholder="قيمة الخصم">
                <select name="line_items[${p}][discount_type]" 
                        class="select2 form-select discount-type-select" style="max-width: 80px;">
                    <option value="amount" selected>قيمة</option>
                    <option value="percentage">نسبة</option>
                </select>
            </div>
        </td>
        <td rowspan="3" class="merged-action-cell">
            <button type="button" class="btn btn-sm btn-outline-danger btn-remove" 
                    onclick="removeItem(this)" title="حذف">
                <i class="ti ti-trash ti-xs"></i>
            </button>
        </td>
    `;const c=document.createElement("tr");c.setAttribute("data-item",p),c.className="item-separator",c.style.backgroundColor="#f8f9fa",c.style.borderTop="2px solid #dee2e6",c.innerHTML=`
        <th style="font-size: 0.85rem; color: #495057; font-weight: 600; text-align: center; padding: 8px;">
            <i class="ti ti-percentage me-1" style="font-size: 0.75rem;"></i>
            نوع الضريبة
        </th>
        <th colspan="1" style="font-size: 0.85rem; color: #495057; font-weight: 600; text-align: center; padding: 8px;">
            <i class="ti ti-calculator me-1" style="font-size: 0.75rem;"></i>
            المبلغ قبل الضريبة
        </th>
        <th style="font-size: 0.85rem; color: #495057; font-weight: 600; text-align: center; padding: 8px;">
            <i class="ti ti-receipt-tax me-1" style="font-size: 0.75rem;"></i>
            مبلغ الضريبة
        </th>
        <th colspan="2" style="font-size: 0.85rem; color: #495057; font-weight: 600; text-align: center; padding: 8px;">
            <i class="ti ti-coin me-1" style="font-size: 0.75rem;"></i>
            المبلغ الإجمالي
        </th>
    `;const r=document.createElement("tr");r.setAttribute("data-item",p),r.className="item-results",r.style.borderBottom="2px solid #dee2e6",r.innerHTML=`
        <td style="padding: 10px;">
            <select name="line_items[${p}][tax_percent]" class="select2 form-select tax-select" 
                    style="font-size: 0.9rem;">
                <option value="15" data-rate="15">ضريبة 15%</option>
                <option value="0" data-rate="0">ضريبة 0%</option>
            </select>
        </td>
        <td colspan="1" class="text-center" style="padding: 10px;">
            <div class="subtotal-cell calculated-field" 
                 style="background-color: #e3f2fd; padding: 8px; border-radius: 4px; font-weight: 500; color: #1976d2;">
                0.00 <span class="icon-saudi_riyal"></span>
            </div>
             <input type="hidden" name="line_items[${p}][subtotal]" value="0.00">
        </td>
        <td class="text-center" style="padding: 10px;">
            <div class="tax-amount-cell calculated-field" 
                 style="background-color: #fff3e0; padding: 8px; border-radius: 4px; font-weight: 500; color: #f57c00;">
                0.00 <span class="icon-saudi_riyal"></span>
            </div>
             <input type="hidden" name="line_items[${p}][tax_amount]" value="0.00">
        </td>
        <td colspan="2" class="text-center" style="padding: 10px;">
            <div class="total-cell calculated-field fw-bold" 
                 style="background-color: #e8f5e8; padding: 8px; border-radius: 4px; font-weight: bold; color: #2e7d32;">
                0.00 <span class="icon-saudi_riyal"></span>
            </div>
             <input type="hidden" name="line_items[${p}][total]" value="0.00">
        </td>
    `;const s=document.createElement("tr");s.setAttribute("data-item",p),s.className="item-visual-separator",s.innerHTML=`
        <td colspan="8" style="height: 20px; background-color: #ffffff; border: none;">
            <div style="text-align: center; color: #dee2e6; font-size: 1rem; line-height: 20px;">
                ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥
            </div>
        </td>
    `,i.appendChild(a),i.appendChild(c),i.appendChild(r),i.appendChild(s),l&&(l.style.display="none"),I(p),typeof $<"u"&&$.fn.select2&&$(".select2").select2({dropdownParent:$("#wizard-validation"),placeholder:" ",allowClear:!0,width:"100%",language:"ar",dir:"rtl"}),b()}window.removeItem=function(i){if(new Set(Array.from(document.querySelectorAll("tr[data-item]")).map(s=>s.getAttribute("data-item"))).size<=1)return;const c=i.closest("tr").getAttribute("data-item");if(!c)return;document.querySelectorAll(`tr[data-item="${c}"]`).forEach(s=>{s.parentNode&&s.parentNode.removeChild(s)}),R(),F(),b()};function R(){const i=document.querySelector("#lineItemsTable tbody");if(!i)return;const l=Array.from(i.children);let a=0;const c={};l.forEach(r=>{const s=r.getAttribute("data-item");s&&(c[s]||(c[s]=[]),c[s].push(r))}),Object.keys(c).forEach(r=>{a++,c[r].forEach(u=>{u.setAttribute("data-item",a);const n=u.querySelector(".merged-number-cell");n&&(n.textContent=a),u.querySelectorAll("input, select").forEach(e=>{const o=e.getAttribute("name");if(o&&o.includes("[")){const d=o.replace(/\[\d+\]/,`[${a}]`);e.setAttribute("name",d)}})}),H(a)}),p=a}function H(i){const l=document.querySelectorAll(`tr[data-item="${i}"]`);if(l.length<3)return;const a=l[0],c=l[2],r=a.querySelectorAll(".quantity-input, .price-input, .discount-input, .product-select, .discount-type-select"),s=c.querySelector(".tax-select");if(r.forEach(u=>{const n=u.cloneNode(!0);u.parentNode.replaceChild(n,u)}),s){const u=s.cloneNode(!0);s.parentNode.replaceChild(u,s)}I(i)}function F(){const i=document.querySelector("#lineItemsTable tbody"),l=document.getElementById("emptyMessage");if(i&&l){const a=i.children.length>0;l.style.display=a?"none":"block"}}function I(i){const l=document.querySelectorAll(`tr[data-item="${i}"]`);if(l.length<3)return;const a=l[0],c=l[2],r=a.querySelector(".product-select");r&&r.addEventListener("change",function(){const e=this.options[this.selectedIndex].getAttribute("data-price")||"0.00",o=a.querySelector(".price-input");o&&(o.value=e,f(i))}),a.querySelectorAll(".quantity-input, .price-input, .discount-input").forEach(t=>{t.addEventListener("input",()=>{q(i),f(i)}),t.addEventListener("change",()=>{q(i),f(i)})});const u=a.querySelector(".discount-type-select");u&&u.addEventListener("change",()=>{q(i),f(i)});const n=c.querySelector(".tax-select");n&&n.addEventListener("change",()=>{f(i)})}function q(i){const l=document.querySelectorAll(`tr[data-item="${i}"]`);if(l.length<1)return;const a=l[0],c=a.querySelector(".quantity-input"),r=a.querySelector(".price-input"),s=a.querySelector(".discount-input"),u=a.querySelector(".discount-type-select");if(!c||!r||!s||!u)return;const n=parseFloat(c.value)||0,t=parseFloat(r.value)||0,e=parseFloat(s.value)||0,o=u.value,d=n*t;if(e<0){s.value=0,S(s,"لا يمكن أن يكون الخصم بالسالب","error");return}o==="percentage"?e>100&&(s.value=100,S(s,"لا يمكن أن تتجاوز نسبة الخصم 100%","warning")):e>d&&(s.value=d.toFixed(2),S(s,"لا يمكن أن يتجاوز الخصم المبلغ الإجمالي","warning"))}function S(i,l,a){const c=i.parentNode.querySelector(".validation-message");c&&c.remove();const r=document.createElement("div");r.className=`validation-message small mt-1 text-${a==="error"?"danger":"warning"}`,r.textContent=l,i.parentNode.appendChild(r),setTimeout(()=>{r.parentNode&&r.remove()},3e3)}function f(i){const l=document.querySelectorAll(`tr[data-item="${i}"]`);if(l.length<3)return;const a=l[0],c=l[2],r=a.querySelector(".quantity-input"),s=a.querySelector(".price-input"),u=a.querySelector(".discount-input"),n=a.querySelector(".discount-type-select"),t=c.querySelector(".tax-select");if(!r||!s||!u||!n||!t)return;const e=parseFloat(r.value)||0,o=parseFloat(s.value)||0,d=parseFloat(u.value)||0,m=n.value,y=t.options[t.selectedIndex],k=parseFloat(y.getAttribute("data-rate"))||0,_=e*o;let g;m==="percentage"?g=_*(d/100):g=d,g=Math.min(g,_);let h,v,w;h=Math.max(0,_-g),v=h*(k/100),w=h+v;const A=c.querySelector(".subtotal-cell"),T=c.querySelector(".tax-amount-cell"),E=c.querySelector(".total-cell"),M=c.querySelector('input[name$="[subtotal]"]'),L=c.querySelector('input[name$="[tax_amount]"]'),C=c.querySelector('input[name$="[total]"]');A&&(A.innerHTML=`${h.toFixed(2)} <span class="icon-saudi_riyal"></span>`),T&&(T.innerHTML=`${v.toFixed(2)} <span class="icon-saudi_riyal"></span>`),E&&(E.innerHTML=`${w.toFixed(2)} <span class="icon-saudi_riyal"></span>`),M&&(M.value=h.toFixed(2)),L&&(L.value=v.toFixed(2)),C&&(C.value=w.toFixed(2)),b()}function b(){let i=0,l=0,a=0;document.querySelectorAll("tr.item-results").forEach(n=>{const t=n.querySelector(".subtotal-cell"),e=n.querySelector(".tax-amount-cell");if(t&&e){const o=t.textContent||"0.00 ",d=e.textContent||"0.00 ",m=parseFloat(o.replace(/[^\d.-]/g,""))||0,y=parseFloat(d.replace(/[^\d.-]/g,""))||0;i+=m,l+=y}}),a=i+l;const r=document.getElementById("subtotalDisplay"),s=document.getElementById("taxDisplay"),u=document.getElementById("totalDisplay");r&&(r.innerHTML=`${i.toFixed(2)} <span class="icon-saudi_riyal"></span>`),s&&(s.innerHTML=`${l.toFixed(2)} <span class="icon-saudi_riyal"></span>`),u&&(u.innerHTML=`${a.toFixed(2)} <span class="icon-saudi_riyal"></span>`)}if(typeof $<"u"){let i=function(){const n=document.querySelector("#lineItemsTable tbody"),t=document.getElementById("emptyMessage");n&&(n.innerHTML=`
            <tr>
                <td colspan="7" class="text-center py-5">
                    <div class="text-primary">
                        <i class="ti ti-loader ti-spin me-2" style="font-size: 1.5rem;"></i>
                        <span>جارٍ تحميل بنود الفاتورة...</span>
                    </div>
                </td>
            </tr>
        `),t&&(t.style.display="none")},l=function(){$.fn.select2&&$(".select2").filter(function(){return $(this).data("select2")!==void 0}).select2("destroy");const n=document.querySelector("#lineItemsTable tbody"),t=document.getElementById("emptyMessage");n&&(n.innerHTML=""),p=0,t&&(t.style.display="block"),a()},a=function(){const n=document.getElementById("subtotalDisplay"),t=document.getElementById("taxDisplay"),e=document.getElementById("totalDisplay");n&&(n.innerHTML='0.00 <span class="icon-saudi_riyal"></span>'),t&&(t.innerHTML='0.00 <span class="icon-saudi_riyal"></span>'),e&&(e.innerHTML='0.00 <span class="icon-saudi_riyal"></span>')},c=function(n){console.log("جاري ملء البنود:",n),console.log("عدد البنود:",n.length),l(),n.forEach((t,e)=>{console.log(`البند ${e+1}:`,t);const o={id:t.id||e+1,product_id:t.product_id||t.item_id||"",description:t.description||t.name||"",quantity:t.quantity||1,unit_price:t.unit_price||t.price||"0.00",discount:t.discount||t.discount_amount||"0.00",discount_type:t.discount_type||"amount",tax_percent:t.tax_percent||t.tax_rate||15,subtotal:t.subtotal||t.net_amount||"0.00",tax_amount:t.tax_amount||t.tax||"0.00",total:t.total||t.gross_amount||"0.00"};console.log(`البند المُنسق ${e+1}:`,o),r(o)}),$.fn.select2,b()},r=function(n){console.log("إضافة بند بالبيانات:",n),p++;const t=document.querySelector("#lineItemsTable tbody"),e=document.getElementById("emptyMessage"),o=s(p,n);t.insertAdjacentHTML("beforeend",o),e&&(e.style.display="none"),u(p,n)},s=function(n,t={}){return`
        <tr data-item="${n}">
            <td rowspan="3" class="merged-number-cell">${n}</td>
            <td>
                <select id="product_id_${n}" name="line_items[${n}][product_id]" class="select2 form-select product-select" required>
                    <option value="">اختر منتجاً</option>
                    ${typeof productOptions<"u"?productOptions:""}
                </select>
            </td>
            <td>
                <input type="text" name="line_items[${n}][description]" class="form-control" placeholder="الوصف" value="${t.description||""}">
            </td>
            <td>
                <input type="number" name="line_items[${n}][quantity]" class="form-control text-center quantity-input" min="1" step="1" value="${t.quantity||1}" required>
            </td>
            <td>
                <input type="number" name="line_items[${n}][unit_price]" class="form-control text-center price-input" min="0" step="0.01" value="${t.unit_price||"0.00"}" required>
            </td>
            <td>
                <div class="input-group">
                    <input type="number" name="line_items[${n}][discount]" class="form-control text-center discount-input mb-1" min="0" step="0.01" value="${t.discount||"0.00"}" placeholder="قيمة الخصم">
                    <select name="line_items[${n}][discount_type]" class="select2 form-select discount-type-select" style="max-width: 80px;">
                        <option value="amount" ${t.discount_type==="amount"?"selected":""}>قيمة</option>
                        <option value="percentage" ${t.discount_type==="percentage"?"selected":""}>نسبة</option>
                    </select>
                </div>
            </td>
            <td rowspan="3" class="merged-action-cell">
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove" onclick="removeItem(this)" title="حذف">
                    <i class="ti ti-trash ti-xs"></i>
                </button>
            </td>
        </tr>
        <tr data-item="${n}" class="item-separator" style="background-color: #f8f9fa; border-top: 2px solid #dee2e6;">
            <th style="font-size: 0.85rem; color: #495057; font-weight: 600; text-align: center; padding: 8px;">
                <i class="ti ti-percentage me-1" style="font-size: 0.75rem;"></i>
                نوع الضريبة
            </th>
            <th colspan="1" style="font-size: 0.85rem; color: #495057; font-weight: 600; text-align: center; padding: 8px;">
                <i class="ti ti-calculator me-1" style="font-size: 0.75rem;"></i>
                المبلغ قبل الضريبة
            </th>
            <th style="font-size: 0.85rem; color: #495057; font-weight: 600; text-align: center; padding: 8px;">
                <i class="ti ti-receipt-tax me-1" style="font-size: 0.75rem;"></i>
                مبلغ الضريبة
            </th>
            <th colspan="2" style="font-size: 0.85rem; color: #495057; font-weight: 600; text-align: center; padding: 8px;">
                <i class="ti ti-coin me-1" style="font-size: 0.75rem;"></i>
                المبلغ الإجمالي
            </th>
        </tr>
        <tr data-item="${n}" class="item-results" style="border-bottom: 2px solid #dee2e6;">
            <td style="padding: 10px;">
                <select name="line_items[${n}][tax_percent]" class="select2 form-select tax-select" style="font-size: 0.9rem;">
                    <option value="15" data-rate="15" ${t.tax_percent==15?"selected":""}>ضريبة 15%</option>
                    <option value="0" data-rate="0" ${t.tax_percent==0?"selected":""}>ضريبة 0%</option>
                </select>
            </td>
            <td colspan="1" class="text-center" style="padding: 10px;">
                <div class="subtotal-cell calculated-field" style="background-color: #e3f2fd; padding: 8px; border-radius: 4px; font-weight: 500; color: #1976d2;">
                    ${t.subtotal||"0.00"} <span class="icon-saudi_riyal"></span>
                </div>
                <input type="hidden" name="line_items[${n}][subtotal]" value="${t.subtotal||"0.00"}">
            </td>
            <td class="text-center" style="padding: 10px;">
                <div class="tax-amount-cell calculated-field" style="background-color: #fff3e0; padding: 8px; border-radius: 4px; font-weight: 500; color: #f57c00;">
                    ${t.tax_amount||"0.00"} <span class="icon-saudi_riyal"></span>
                </div>
                <input type="hidden" name="line_items[${n}][tax_amount]" value="${t.tax_amount||"0.00"}">
            </td>
            <td colspan="2" class="text-center" style="padding: 10px;">
                <div class="total-cell calculated-field fw-bold" style="background-color: #e8f5e8; padding: 8px; border-radius: 4px; font-weight: bold; color: #2e7d32;">
                    ${t.total||"0.00"} <span class="icon-saudi_riyal"></span>
                </div>
                <input type="hidden" name="line_items[${n}][total]" value="${t.total||"0.00"}">
            </td>
        </tr>
        <tr data-item="${n}" class="item-visual-separator">
            <td colspan="8" style="height: 20px; background-color: #ffffff; border: none;">
                <div style="text-align: center; color: #dee2e6; font-size: 1rem; line-height: 20px;">
                    ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥
                </div>
            </td>
        </tr>
    `},u=function(n,t){const e=$(`tr[data-item="${n}"]`);t.product_id&&e.find(".product-select").val(String(t.product_id)),e.find(".select2").each(function(){const o=$(this);o.hasClass("select2-hidden-accessible")||o.select2({dropdownParent:$("#wizard-validation"),placeholder:" ",allowClear:!0,width:"100%",language:"ar",dir:"rtl"})}),z(),e.find(".select2").trigger("change"),I(n),b()};var B=i,N=l,O=a,j=c,J=r,P=s,G=u;$(document).on("change",'select[name$="[product_id]"]',function(){const n=$(this).find(":selected").data("price");if(n){const t=$(this).closest("tr");t.find(".price-input").val(n);const o=t.data("item");o&&f(o)}}),$(document).on("change",'select[name$="[tax_percent]"]',function(){const t=$(this).closest("tr").data("item");t&&f(t)}),$(document).on("change",'select[name$="[discount_type]"]',function(){const t=$(this).closest("tr").data("item");t&&f(t)}),$(document).ready(function(){z(),$("#contact_id").on("change",function(){let n=$(this).val();if(!n){$("#parent_id").html('<option value="">اختر الفاتورة</option>').trigger("change");return}$("#parent_id").html("<option selected disabled>جارٍ تحميل الفواتير...</option>"),$("#loadingIndicator").show(),$.ajax({url:`/qoyod/invoices/get-invoices-by-customer/${n}`,type:"GET",success:function(t){let e='<option value="">اختر الفاتورة</option>';$("#loadingIndicator").hide(),t.forEach(o=>{e+=`<option value="${o.id}">فاتورة  ${o.reference}</option>`}),$("#parent_id").html(e).trigger("change")},error:function(){$("#loadingIndicator").hide(),$("#parent_id").html("<option selected disabled>تعذر جلب الفواتير</option>").trigger("change"),toastr.error("فشل في جلب الفواتير لهذا العميل")}})}),$("#parent_id").on("change",function(){let n=$(this).val();if(console.log("تم اختيار فاتورة:",n),!n){l(),setTimeout(()=>{x()},100);return}i();const t=`/qoyod/invoices/${n}/line-items`;console.log("URL للطلب:",t),$.ajax({url:t,type:"GET",success:function(e){console.log("استجابة الخادم الكاملة:",e),console.log("نوع البيانات:",typeof e);let o=null;if(e&&e.data&&Array.isArray(e.data))o=e.data,console.log("تم العثور على البيانات في response.data");else if(e&&e.line_items&&Array.isArray(e.line_items))o=e.line_items,console.log("تم العثور على البيانات في response.line_items");else if(e&&e.invoice&&e.invoice.line_items&&Array.isArray(e.invoice.line_items))o=e.invoice.line_items,console.log("تم العثور على البيانات في response.invoice.line_items");else if(e&&Array.isArray(e))o=e,console.log("الاستجابة هي array مباشرة");else if(e){console.log("البحث عن البيانات في خصائص أخرى..."),console.log("جميع خصائص الاستجابة:",Object.keys(e));for(let d in e)if(Array.isArray(e[d])&&e[d].length>0){const m=e[d][0];if(m&&(m.product_id||m.quantity||m.unit_price)){o=e[d],console.log(`تم العثور على البيانات في response.${d}`);break}}else if(e[d]&&typeof e[d]=="object"){console.log(`فحص ${d}:`,Object.keys(e[d]));for(let m in e[d])if(Array.isArray(e[d][m])&&e[d][m].length>0){const y=e[d][m][0];if(y&&(y.product_id||y.quantity||y.unit_price||y.item_id)){o=e[d][m],console.log(`تم العثور على البيانات في response.${d}.${m}`);break}}if(o)break}}console.log("البيانات المستخرجة:",o),console.log("عدد البنود:",o?o.length:"undefined"),o&&Array.isArray(o)&&o.length>0?(console.log("تم العثور على بنود:",o.length),console.log("البند الأول:",o[0]),c(o),typeof toastr<"u"&&toastr.success(`تم جلب ${o.length} بند من الفاتورة بنجاح`)):(console.log("لم يتم العثور على بنود صالحة"),console.log("الاستجابة الكاملة:",JSON.stringify(e,null,2)),l(),setTimeout(()=>{x()},100),typeof toastr<"u"&&toastr.info("لا توجد بنود في هذه الفاتورة"))},error:function(e){console.error("خطأ AJAX:",e),l(),setTimeout(()=>{x()},100);let o="فشل في جلب بنود الفاتورة";e.responseJSON&&e.responseJSON.message?o=e.responseJSON.message:e.status===404&&(o="الـ route غير موجود - تحقق من إعداد الـ routes"),typeof toastr<"u"&&toastr.error(o),console.error("تفاصيل الخطأ:",{status:e.status,statusText:e.statusText,responseText:e.responseText})}})})})}
