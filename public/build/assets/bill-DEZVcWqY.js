let l=0;document.addEventListener("DOMContentLoaded",function(){const t=document.getElementById("emptyMessage");t&&(t.style.display="block"),L();const e=document.getElementById("addLineItem");e&&e.addEventListener("click",L)});function L(){l++;const t=document.querySelector("#lineItemsTable tbody"),e=document.getElementById("emptyMessage"),s=document.createElement("tr");s.setAttribute("data-item",l),s.innerHTML=`
        <td rowspan="3" class="merged-number-cell">${l}</td>
        <td>
            <select id="product_id" name="line_items[${l}][product_id]"
           class="select2 form-select product-select" required>
       <option value="">اختر منتجاً</option>
       ${productOptions}
   </select>
        </td>
        <td>
            <input type="text" name="line_items[${l}][description]" 
                   class="form-control" placeholder="الوصف">
        </td>
        <td>
            <input type="number" name="line_items[${l}][quantity]" 
                   class="form-control text-center quantity-input" 
                   min="1" step="1" value="1" required>
        </td>
        <td>
            <input type="number" name="line_items[${l}][unit_price]" 
                   class="form-control text-center price-input" 
                   min="0" step="0.01" value="0.00" required>
        </td>
        <td>
            <div class="input-group">
                <input type="number" name="line_items[${l}][discount]" 
                       class="form-control text-center discount-input  mb-1" 
                       min="0" step="0.01" value="0.00" 
                       placeholder="قيمة الخصم">
                <select name="line_items[${l}][discount_type]" 
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
    `;const n=document.createElement("tr");n.setAttribute("data-item",l),n.className="item-separator",n.style.backgroundColor="#f8f9fa",n.style.borderTop="2px solid #dee2e6",n.innerHTML=`
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
    `;const a=document.createElement("tr");a.setAttribute("data-item",l),a.className="item-results",a.style.borderBottom="2px solid #dee2e6",a.innerHTML=`
        <td style="padding: 10px;">
            <select name="line_items[${l}][tax_percent]" class="select2 form-select tax-select" 
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
             <input type="hidden" name="line_items[${l}][subtotal]" value="0.00">
        </td>
        <td class="text-center" style="padding: 10px;">
            <div class="tax-amount-cell calculated-field" 
                 style="background-color: #fff3e0; padding: 8px; border-radius: 4px; font-weight: 500; color: #f57c00;">
                0.00 <span class="icon-saudi_riyal"></span>
            </div>
             <input type="hidden" name="line_items[${l}][tax_amount]" value="0.00">
        </td>
        <td colspan="2" class="text-center" style="padding: 10px;">
            <div class="total-cell calculated-field fw-bold" 
                 style="background-color: #e8f5e8; padding: 8px; border-radius: 4px; font-weight: bold; color: #2e7d32;">
                0.00 <span class="icon-saudi_riyal"></span>
            </div>
             <input type="hidden" name="line_items[${l}][total]" value="0.00">
        </td>
    `;const o=document.createElement("tr");o.setAttribute("data-item",l),o.className="item-visual-separator",o.innerHTML=`
        <td colspan="8" style="height: 20px; background-color: #ffffff; border: none;">
            <div style="text-align: center; color: #dee2e6; font-size: 1rem; line-height: 20px;">
                ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥
            </div>
        </td>
    `,t.appendChild(s),t.appendChild(n),t.appendChild(a),t.appendChild(o),e&&(e.style.display="none"),M(l),typeof $<"u"&&$.fn.select2&&$(".select2").select2({dropdownParent:$("#wizard-validation"),placeholder:" ",allowClear:!0,width:"100%",language:"ar",dir:"rtl"}),S()}window.removeItem=function(t){if(new Set(Array.from(document.querySelectorAll("tr[data-item]")).map(o=>o.getAttribute("data-item"))).size<=1)return;const n=t.closest("tr").getAttribute("data-item");if(!n)return;document.querySelectorAll(`tr[data-item="${n}"]`).forEach(o=>{o.parentNode&&o.parentNode.removeChild(o)}),F(),z(),S()};function F(){const t=document.querySelector("#lineItemsTable tbody");if(!t)return;const e=Array.from(t.children);let s=0;const n={};e.forEach(a=>{const o=a.getAttribute("data-item");o&&(n[o]||(n[o]=[]),n[o].push(a))}),Object.keys(n).forEach(a=>{s++,n[a].forEach(i=>{i.setAttribute("data-item",s);const c=i.querySelector(".merged-number-cell");c&&(c.textContent=s),i.querySelectorAll("input, select").forEach(d=>{const u=d.getAttribute("name");if(u&&u.includes("[")){const p=u.replace(/\[\d+\]/,`[${s}]`);d.setAttribute("name",p)}})}),H(s)}),l=s}function H(t){const e=document.querySelectorAll(`tr[data-item="${t}"]`);if(e.length<3)return;const s=e[0],n=e[2],a=s.querySelectorAll(".quantity-input, .price-input, .discount-input, .product-select, .discount-type-select"),o=n.querySelector(".tax-select");if(a.forEach(i=>{const c=i.cloneNode(!0);i.parentNode.replaceChild(c,i)}),o){const i=o.cloneNode(!0);o.parentNode.replaceChild(i,o)}M(t)}function z(){const t=document.querySelector("#lineItemsTable tbody"),e=document.getElementById("emptyMessage");if(t&&e){const s=t.children.length>0;e.style.display=s?"none":"block"}}function M(t){const e=document.querySelectorAll(`tr[data-item="${t}"]`);if(e.length<3)return;const s=e[0],n=e[2],a=s.querySelector(".product-select");a&&a.addEventListener("change",function(){const d=this.options[this.selectedIndex].getAttribute("data-price")||"0.00",u=s.querySelector(".price-input");u&&(u.value=d,m(t))}),s.querySelectorAll(".quantity-input, .price-input, .discount-input").forEach(r=>{r.addEventListener("input",()=>{w(t),m(t)}),r.addEventListener("change",()=>{w(t),m(t)})});const i=s.querySelector(".discount-type-select");i&&i.addEventListener("change",()=>{w(t),m(t)});const c=n.querySelector(".tax-select");c&&c.addEventListener("change",()=>{m(t)})}function w(t){const e=document.querySelectorAll(`tr[data-item="${t}"]`);if(e.length<1)return;const s=e[0],n=s.querySelector(".quantity-input"),a=s.querySelector(".price-input"),o=s.querySelector(".discount-input"),i=s.querySelector(".discount-type-select");if(!n||!a||!o||!i)return;const c=parseFloat(n.value)||0,r=parseFloat(a.value)||0,d=parseFloat(o.value)||0,u=i.value,p=c*r;if(d<0){o.value=0,q(o,"لا يمكن أن يكون الخصم بالسالب","error");return}u==="percentage"?d>100&&(o.value=100,q(o,"لا يمكن أن تتجاوز نسبة الخصم 100%","warning")):d>p&&(o.value=p.toFixed(2),q(o,"لا يمكن أن يتجاوز الخصم المبلغ الإجمالي","warning"))}function q(t,e,s){const n=t.parentNode.querySelector(".validation-message");n&&n.remove();const a=document.createElement("div");a.className=`validation-message small mt-1 text-${s==="error"?"danger":"warning"}`,a.textContent=e,t.parentNode.appendChild(a),setTimeout(()=>{a.parentNode&&a.remove()},3e3)}function m(t){const e=document.querySelectorAll(`tr[data-item="${t}"]`);if(e.length<3)return;const s=e[0],n=e[2],a=s.querySelector(".quantity-input"),o=s.querySelector(".price-input"),i=s.querySelector(".discount-input"),c=s.querySelector(".discount-type-select"),r=n.querySelector(".tax-select");if(!a||!o||!i||!c||!r)return;const d=parseFloat(a.value)||0,u=parseFloat(o.value)||0,p=parseFloat(i.value)||0,x=c.value,h=r.options[r.selectedIndex],R=parseFloat(h.getAttribute("data-rate"))||0,b=d*u;let y;x==="percentage"?y=b*(p/100):y=p,y=Math.min(y,b);let f,g,v;f=Math.max(0,b-y),g=f*(R/100),v=f+g;const I=n.querySelector(".subtotal-cell"),E=n.querySelector(".tax-amount-cell"),_=n.querySelector(".total-cell"),A=n.querySelector('input[name$="[subtotal]"]'),T=n.querySelector('input[name$="[tax_amount]"]'),C=n.querySelector('input[name$="[total]"]');I&&(I.innerHTML=`${f.toFixed(2)} <span class="icon-saudi_riyal"></span>`),E&&(E.innerHTML=`${g.toFixed(2)} <span class="icon-saudi_riyal"></span>`),_&&(_.innerHTML=`${v.toFixed(2)} <span class="icon-saudi_riyal"></span>`),A&&(A.value=f.toFixed(2)),T&&(T.value=g.toFixed(2)),C&&(C.value=v.toFixed(2)),S()}function S(){let t=0,e=0,s=0;document.querySelectorAll("tr.item-results").forEach(c=>{const r=c.querySelector(".subtotal-cell"),d=c.querySelector(".tax-amount-cell");if(r&&d){const u=r.textContent||"0.00 ",p=d.textContent||"0.00 ",x=parseFloat(u.replace(/[^\d.-]/g,""))||0,h=parseFloat(p.replace(/[^\d.-]/g,""))||0;t+=x,e+=h}}),s=t+e;const a=document.getElementById("subtotalDisplay"),o=document.getElementById("taxDisplay"),i=document.getElementById("totalDisplay");a&&(a.innerHTML=`${t.toFixed(2)} <span class="icon-saudi_riyal"></span>`),o&&(o.innerHTML=`${e.toFixed(2)} <span class="icon-saudi_riyal"></span>`),i&&(i.innerHTML=`${s.toFixed(2)} <span class="icon-saudi_riyal"></span>`)}typeof $<"u"&&($(document).on("change",'select[name$="[product_id]"]',function(){const t=$(this).find(":selected").data("price");if(t){const e=$(this).closest("tr");e.find(".price-input").val(t);const n=e.data("item");n&&m(n)}}),$(document).on("change",'select[name$="[tax_percent]"]',function(){const e=$(this).closest("tr").data("item");e&&m(e)}),$(document).on("change",'select[name$="[discount_type]"]',function(){const e=$(this).closest("tr").data("item");e&&m(e)}));
