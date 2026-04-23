import{P as q,E as D}from"./pusher-w6OCIzNZ.js";import"./_commonjsHelpers-BosuxZz1.js";document.addEventListener("DOMContentLoaded",function(){window.Pusher=q,window.Echo=new D({broadcaster:"pusher",key:"d1473f2fb70c54367e01",cluster:"mt1",forceTLS:!0});const p=document.getElementById("chat-list"),w=document.getElementById("create-new-conversation");let l=window.activeConversationId||null,h=null;const E=window.userAvatar||"/assets/img/branding/Alburhan-Logo.png";let r=null;const g=document.getElementById("ai-send-message-form"),y=document.getElementById("ai-message-input"),C=document.getElementById("messages"),d=document.getElementById("ai-message-file"),m=document.getElementById("selected-file-name");function f(e){const s={"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#039;"};return e.replace(/[&<>"']/g,function(t){return s[t]})}function u(e){const s={hour:"2-digit",minute:"2-digit"};return new Date(e).toLocaleTimeString([],s)}function v(){const e=document.querySelector(".chat-history-body");e?e.scrollTop=e.scrollHeight:console.warn("chat-history element not found")}function $(e,s){let t=0;const n=setInterval(()=>{t<s.length?(e.textContent+=s[t],t++,v()):clearInterval(n)},50)}function I(e,s={}){const{timeout:t=12e4}=s;return new Promise((n,a)=>{const i=setTimeout(()=>{a(new Error("Timeout"))},t);fetch(e,s).then(o=>{clearTimeout(i),n(o)}).catch(o=>{clearTimeout(i),a(o)})})}w&&w.addEventListener("click",function(){fetch("/ai-chat/create",{method:"POST",headers:{"X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content"),Accept:"application/json","Content-Type":"application/json"},body:JSON.stringify({})}).then(e=>e.json()).then(e=>{if(e.status==="Conversation Created!"&&e.conversation){const s=e.conversation,t=document.createElement("li");t.classList.add("chat-contact-list-item","mb-1","conversation-item","active","position-relative"),t.setAttribute("data-id",s.id),t.innerHTML=`
                            <a href="javascript:void(0)" class="d-flex align-items-center">
                                <div class="flex-shrink-0 avatar avatar-online">
                                    <img src="${E}" alt="الصورة الرمزية" class="rounded-circle">
                                </div>
                                <div class="chat-contact-info flex-grow-1 me-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="chat-contact-name text-truncate m-0 fw-normal">محادثة جديدة</h6>
                                        <small class="text-muted">الآن</small>
                                    </div>
                                    <small class="chat-contact-status text-truncate">محادثة AI</small>
                                </div>
                            </a>
                            <button class="btn btn-sm delete-conversation" data-id="${s.id}" title="حذف المحادثة">
                                <i class="fas fa-trash" style="color: #eff1f1 !important;"></i>
                            </button>
                        `,document.querySelectorAll(".conversation-item").forEach(n=>n.classList.remove("active")),p.prepend(t),b(s.id)}else console.error("Unexpected response:",e),Swal.fire("خطأ","حدث خطأ غير متوقع أثناء إنشاء المحادثة.","error")}).catch(e=>{console.error("Error:",e),Swal.fire("خطأ","حدث خطأ أثناء إنشاء المحادثة الجديدة.","error")})}),p&&p.addEventListener("click",function(e){const s=e.target.closest(".conversation-item");if(!s)return;const t=s.getAttribute("data-id");t!==l&&(document.querySelectorAll(".conversation-item").forEach(n=>n.classList.remove("active")),s.classList.add("active"),b(t))});function b(e){fetch(`/ai-chat?conversation_id=${e}`,{headers:{"X-Requested-With":"XMLHttpRequest",Accept:"text/html"}}).then(s=>{if(s.ok)return s.text();throw new Error("Network response was not ok.")}).then(s=>{const t=document.getElementById("messages");t?(t.innerHTML=s,L(e)):console.error("Messages container not found.")}).catch(s=>{console.error("Error:",s),Swal.fire("خطأ","حدث خطأ أثناء تحميل المحادثة.","error")})}function L(e){const s=l;l=e,h&&(h.stopListening(".AIMessageSent"),window.Echo.leave(`ai-chat.${s}`)),h=window.Echo.private(`ai-chat.${l}`).listen(".AIMessageSent",t=>{if(t.message.sender==="ai")if(r){const n=document.getElementById(r);if(n){const a=n.querySelector(".chat-message-text p");if(a){const o=a.querySelector(".spinner-border");o&&o.remove(),$(a,t.message.message)}const i=n.querySelector(".text-muted small");i&&(i.textContent=u(t.message.created_at)),r=null,S(n,`message-${t.message.id}`)}}else x("ai",t.message.message,t.message.created_at,!1,`message-${t.message.id}`);v()})}d&&d.addEventListener("change",function(){if(d.files&&d.files.length>0){const e=d.files[0].name;m.textContent=e,m.style.display="inline"}else m.textContent="",m.style.display="none"}),g&&y&&C?g.addEventListener("submit",A):console.warn("One or more chat elements are missing.");function A(e){e.preventDefault();const s=y.value.trim(),t=d.files[0];if(!s&&!t)return;g.querySelector('button[type="submit"]').disabled=!0;const n=`message-${Date.now()}`;x("user",s||(t?"تم إرفاق ملف.":""),new Date,!1,n,!!t),r=`ai-placeholder-${Date.now()}`,x("ai","",new Date,!0,r);const a=new FormData;a.append("message",s),t&&a.append("file",t),I(`/ai-chat/${l}/send`,{method:"POST",headers:{"X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content"),Accept:"application/json"},body:a,timeout:12e4}).then(i=>i.json().then(o=>{if(!i.ok)throw o;return o})).then(i=>{if(i.status==="Message Sent!"&&(B(i.user_message,n),i.conversation&&i.conversation.title)){const o=document.querySelector(`.conversation-item[data-id="${i.conversation.id}"]`);if(o){const c=o.querySelector(".chat-contact-name");c&&(c.textContent=f(i.conversation.title))}}}).catch(i=>{if(console.error("Error:",i),i.error?T(i.error):T("حدث خطأ أثناء إرسال الرسالة."),r){const o=document.getElementById(r);o&&(o.remove(),r=null)}}).finally(()=>{g.querySelector('button[type="submit"]').disabled=!1,d.value="",y.value="",m.textContent="",m.style.display="none"})}function B(e,s){const t=document.getElementById(s);if(t){const n=t.querySelector(".chat-message-text");if(n&&(n.innerHTML=`<p class="mb-0">${f(e.message)}</p>`,e.file_url)){const a=document.createElement("p");a.classList.add("mb-0"),a.innerHTML=`<a href="${e.file_url}" target="_blank" class="" style="#0fffff">
                        <i class="fas fa-paperclip"></i> ${e.file_name}
                    </a>`,n.appendChild(a)}}}function x(e,s,t,n=!1,a=null,i=!1){const o=document.getElementById("messages");if(a&&document.getElementById(a))return;a=a||`message-${Date.now()}`;let c="";if(e==="user"?c=`
                <li class="chat-message chat-message-right" id="${a}">
                    <div class="d-flex overflow-hidden">
                        <div class="chat-message-wrapper flex-grow-1">
                            <div class="chat-message-text">
                                <p class="mb-0">${f(s)}</p>
                                ${i?`
                                <p class="mb-0">
                                    <i class="fas fa-paperclip"></i> <span class="text-muted">تم إرفاق ملف</span>
                                </p>`:""}
                            </div>
                            <div class="text-end text-muted mt-1">
                                <small>${u(t)}</small>
                            </div>
                        </div>
                        <div class="user-avatar flex-shrink-0 ms-4">
                            <div class="avatar avatar-sm">
                                <img src="${E}" alt="الصورة الرمزية" class="rounded-circle">
                            </div>
                        </div>
                    </div>
                </li>
            `:e==="ai"&&(n?c=`
                    <li class="chat-message loading-ai-message" id="${a}">
                        <div class="d-flex overflow-hidden">
                            <div class="user-avatar flex-shrink-0 me-4">
                                <div class="avatar avatar-sm">
                                    <img src="/assets/img/branding/Alburhan-Logo.png" alt="صورة AI" class="rounded-circle">
                                </div>
                            </div>
                            <div class="chat-message-wrapper flex-grow-1">
                                <div class="chat-message-text">
                                    <p class="mb-0">
                                        <span class="spinner-border spinner-border-sm text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </span>
                                    </p>
                                </div>
                                <div class="text-muted mt-1">
                                    <small>${u(t)}</small>
                                </div>
                            </div>
                        </div>
                    </li>
                `:c=`
                    <li class="chat-message" id="${a}">
                        <div class="d-flex overflow-hidden">
                            <div class="user-avatar flex-shrink-0 me-4">
                                <div class="avatar avatar-sm">
                                    <img src="/assets/img/branding/Alburhan-Logo.png" alt="صورة AI"
                                        class="rounded-circle">
                                </div>
                            </div>
                            <div class="chat-message-wrapper flex-grow-1">
                                <div class="chat-message-text position-relative">
                                    <button class="btn btn-link copy-ai-message pt-2" data-message-id="${a}" title="نسخ">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    <p class="mb-0 me-4">${f(s)}</p>
                                </div>
                                <div class="text-muted mt-1">
                                    <small>${u(t)}</small>
                                </div>
                            </div>
                        </div>
                    </li>
                `),o.insertAdjacentHTML("beforeend",c),e==="ai"&&!n){const M=document.getElementById(a);S(M,a)}v()}function T(e){const s=document.getElementById("messages"),t=new Date,a=`
            <li class="chat-message" id="${`error-${Date.now()}`}">
                <div class="d-flex overflow-hidden">
                    <div class="user-avatar flex-shrink-0 me-4">
                        <div class="avatar avatar-sm">
                            <img src="/assets/img/branding/Alburhan-Logo.png" alt="صورة AI" class="rounded-circle">
                        </div>
                    </div>
                    <div class="chat-message-wrapper flex-grow-1">
                        <div class="chat-message-text">
                            <p class="mb-0 text-danger">${f(e)}</p>
                        </div>
                        <div class="text-muted mt-1">
                            <small>${u(t)}</small>
                        </div>
                    </div>
                </div>
            </li>
        `;s.insertAdjacentHTML("beforeend",a),v()}function S(e,s){const t=e.querySelector(".chat-message-text");if(t){const n=document.createElement("button");n.classList.add("btn","btn-link","copy-ai-message"),n.setAttribute("data-message-id",s),n.setAttribute("title","نسخ"),n.innerHTML='<i class="fas fa-copy"></i>',t.insertBefore(n,t.firstChild)}}document.addEventListener("click",function(e){if(e.target&&(e.target.classList.contains("copy-ai-message")||e.target.closest(".copy-ai-message"))){const t=e.target.closest(".copy-ai-message").getAttribute("data-message-id"),n=document.querySelector(`#${t} .chat-message-text p`);if(n){const a=n.textContent;navigator.clipboard.writeText(a).then(()=>{k()}).catch(i=>{console.error("Error copying text: ",i)})}}});function k(){const e=document.getElementById("copyToast");new bootstrap.Toast(e).show()}document.addEventListener("click",function(e){if(e.target&&(e.target.classList.contains("delete-conversation")||e.target.closest(".delete-conversation"))){const t=e.target.closest(".delete-conversation").getAttribute("data-id");Swal.fire({title:"تأكيد الحذف",text:"هل أنت متأكد من حذف هذه المحادثة؟",icon:"warning",showCancelButton:!0,confirmButtonText:"نعم، احذفها",cancelButtonText:"إلغاء",reverseButtons:!0}).then(n=>{n.isConfirmed&&fetch(`/ai-chat/${t}/delete`,{method:"DELETE",headers:{"X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content"),Accept:"application/json"}}).then(a=>a.json()).then(a=>{if(a.status==="Conversation Deleted!"){const i=document.querySelector(`.conversation-item[data-id="${t}"]`);i&&i.remove();const o=document.querySelector(".conversation-item");if(o)o.classList.add("active"),b(o.getAttribute("data-id"));else{const c=document.querySelector(".app-chat-history .chat-history-wrapper");c&&(c.innerHTML=`
                                            <div class="chat-history-header border-bottom">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="d-flex overflow-hidden align-items-center">
                                                        <i class="ti ti-menu-2 ti-lg cursor-pointer d-lg-none d-block me-4" data-bs-toggle="sidebar"
                                                            data-overlay data-target="#app-chat-contacts"></i>
                                                        <div class="flex-shrink-0 avatar avatar-online" id="receiver-avatar">
                                                            <img id="receiver-image" src="{{ asset('assets/img/branding/Alburhan-Logo.png') }}"
                                                                alt="الصورة الرمزية" class="rounded-circle" data-bs-toggle="sidebar" data-overlay
                                                                data-target="#app-chat-sidebar-right">
                                                        </div>
                                                        <div class="chat-contact-info flex-grow-1 me-4">
                                                            <h6 class="m-0 fw-normal" id="receiver-name">لا توجد محادثات</h6>
                                                            <small class="user-status text-body" id="receiver-job"></small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="chat-history-body">
                                                <p class="text-center mt-5">لا توجد محادثات. ابدأ محادثة جديدة.</p>
                                            </div>
                                            <div class="chat-history-footer shadow-xs">
                                                <form id="ai-send-message-form" class="form-send-message d-flex justify-content-between align-items-center">
                                                    @csrf
                                                    <input type="hidden" id="receiver_id" name="receiver_id" value="">
                                                    <div class="me-3 d-flex align-items-center">
                                                        <label for="ai-message-file" class="btn btn-sm mb-0" title="إرفاق ملف">
                                                            <i class="fas fa-paperclip fs-4"></i>
                                                        </label>
                                                        <input type="file" class="d-none" id="ai-message-file" name="file" accept=".pdf,.doc,.docx">
                                                        <span id="selected-file-name" class="ms-2 text-truncate" style="max-width: 150px; display: none;"></span>
                                                    </div>
                                                    <input class="form-control message-input border-0 me-4 shadow-none" placeholder="اكتب رسالتك هنا..." id="ai-message-input" name="message">
                                                    <div class="message-actions d-flex align-items-center">
                                                        <button type="submit" class="btn btn-primary d-flex send-msg-btn">
                                                            <span class="align-middle d-md-inline-block d-none">إرسال</span>
                                                            <i class="ti ti-send ti-16px ms-md-2 ms-0"></i>
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        `)}Swal.fire("تم الحذف!","تم حذف المحادثة بنجاح.","success")}else Swal.fire("خطأ","حدث خطأ أثناء حذف المحادثة.","error")}).catch(a=>{console.error("Error:",a),Swal.fire("خطأ","حدث خطأ أثناء حذف المحادثة.","error")})})}}),l&&L(l)});
