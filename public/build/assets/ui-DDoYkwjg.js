import{k as l,p as u}from"./purify.es-B6rbC9fn.js";l.setOptions({breaks:!0,gfm:!0});const n={messagesContainer:"#messages-container",chatList:"#chat-list",chatHeaderTitle:"#chat-header-title",chatBody:".chat-history-body",deleteBtn:".delete-conversation-btn",copyBtn:".copy-ai-message-btn",welcomeTemplate:"#welcome-message-template"};function d(e){return new Date(e).toLocaleTimeString("en-US",{hour:"2-digit",minute:"2-digit",hour12:!0})}function v(){const e=document.querySelector(n.chatBody);e&&(e.scrollTop=e.scrollHeight)}function w(e){const t=document.getElementById(e);t&&t.remove()}function p(){const e=document.querySelector(n.messagesContainer),t=document.querySelector(n.welcomeTemplate);e&&t&&(e.innerHTML="",e.appendChild(t.content.cloneNode(!0)))}function L(){const e=document.querySelector(n.messagesContainer);e&&(e.innerHTML='<div class="d-flex justify-content-center align-items-center h-100"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">جاري التحميل...</span></div></div>')}function h(e){const t=document.querySelector(n.messagesContainer);t&&(t.innerHTML=`<div class="text-center text-danger py-5"><i class="ti ti-alert-circle ti-lg mb-2"></i><p>${e}</p></div>`)}function C(e){const t=document.querySelector(n.messagesContainer);if(t){const s=t.querySelector(".chat-bienvenida-message, .d-flex.justify-content-center");s&&s.remove(),t.insertAdjacentHTML("beforeend",e),v()}}function M(e,t){if(!e||!e.chat){h("بيانات المحادثة غير صالحة.");return}t.activeChatId=e.chat.id;const s=document.querySelector(n.chatHeaderTitle);s&&(s.textContent=e.chat.title);const i=document.querySelector(n.messagesContainer);if(i){if(i.innerHTML="",e.messages&&e.messages.length>0){const c=document.createDocumentFragment();e.messages.forEach(a=>{const r=y(a,t),o=document.createElement("div");o.innerHTML=r,o.firstChild&&c.appendChild(o.firstChild)}),i.appendChild(c)}else p();x(e.chat.id),v()}}function S(e,t){const s=document.querySelector(n.chatList);if(!s)return;const i=s.querySelector(".text-center.p-4");i&&i.remove(),document.querySelectorAll(".conversation-item").forEach(r=>r.classList.remove("active"));const c=t.assets.aiAvatar,a=`
        <li class="chat-contact-list-item mb-1 conversation-item position-relative active" data-id="${e.id}">
            <a href="javascript:void(0);" class="d-flex align-items-center text-decoration-none chat-link" data-chat-id="${e.id}">
                <div class="flex-shrink-0 avatar me-3">
                    <img src="${c}" alt="AI" class="rounded-circle">
                </div>
                <div class="chat-contact-info flex-grow-1 overflow-hidden">
                    <h6 class="chat-contact-name text-truncate m-0 fw-normal">${e.title}</h6>
                </div>
            </a>
            <button class="btn btn-sm btn-icon delete-conversation-btn" data-id="${e.id}" title="حذف">
                <i class="ti ti-trash"></i>
            </button>
        </li>
    `;s.insertAdjacentHTML("afterbegin",a)}function $(e){const t=document.querySelector(`.conversation-item[data-id="${e}"]`);t&&t.remove();const s=document.querySelector(n.chatList);s&&s.children.length===0&&(s.innerHTML='<li class="text-center p-4 text-muted">لا توجد محادثات سابقة.</li>')}function x(e){document.querySelectorAll(".conversation-item").forEach(t=>{t.classList.toggle("active",String(t.dataset.id)===String(e))})}function H(e){if(!e||!e.id||!e.title)return;const t=document.querySelector(`.conversation-item[data-id="${e.id}"] .chat-contact-name`);t&&(t.textContent=e.title);const s=document.querySelector(n.chatHeaderTitle);s&&(s.textContent=e.title)}function y(e,t){var m;if(!t||!t.user||!t.assets)return"";const s=e.sender==="user",i=s?t.user.avatar:t.assets.aiAvatar,c=s?"chat-message-right":"",a=(m=e.id)!=null&&m.toString().startsWith("temp-")?e.id:`message-${e.id}`,r=e.created_at?d(new Date(e.created_at)):d(new Date),o=u.sanitize(l.parse(e.message||""));if(s)return`<li class="chat-message ${c}" id="${a}" data-sender="user"><div class="d-flex overflow-hidden"><div class="chat-message-wrapper flex-grow-1"><div class="chat-message-text">${o}</div><div class="text-end text-muted mt-1"><small>${r}</small></div></div><div class="user-avatar flex-shrink-0 ms-3"><div class="avatar avatar-sm"><img src="${i}" alt="Avatar" class="rounded-circle"></div></div></div></li>`;{const f=t.routes.exportMessage?t.routes.exportMessage.replace("__MESSAGE_ID__",e.id):"#",g=t.routes.exportMessage?`<a href="${f}" target="_blank" class="btn btn-text-secondary btn-sm rounded-pill btn-icon export-pdf-link" title="تنزيل كـ PDF"><i class="ti ti-download ti-18px"></i></a>`:"";return`<li class="chat-message" id="${a}" data-sender="ai"><div class="d-flex overflow-hidden"><div class="user-avatar flex-shrink-0 me-3"><div class="avatar avatar-sm"><img src="${i}" alt="AI Avatar" class="rounded-circle"></div></div><div class="chat-message-wrapper flex-grow-1"><div class="chat-message-text">${o}</div><div class="chat-message-actions d-flex align-items-center justify-content-between mt-2"><div class="d-flex align-items-center"><button class="btn btn-text-secondary btn-sm rounded-pill btn-icon copy-ai-message-btn" title="نسخ النص"><i class="ti ti-copy ti-18px"></i></button>${g}</div><div class="text-muted"><small>${r}</small></div></div></div></div></li>`}}function q(e){const t=`message-ai-${Date.now()}`,i=`
        <li class="chat-message" id="${t}" data-sender="ai">
            <div class="d-flex overflow-hidden">
                <div class="user-avatar flex-shrink-0 me-3">
                    <div class="avatar avatar-sm">
                        <img src="${e.aiAvatar}" alt="AI Avatar" class="rounded-circle">
                    </div>
                </div>
                <div class="chat-message-wrapper flex-grow-1">
                    <div class="chat-message-text">
                        
        <div class="ai-typing-indicator d-flex align-items-center justify-content-start">
            <div class="typing-dots d-flex align-items-center">
                <div class="dot-pulse"></div>
                <div class="dot-pulse dot-pulse-delay-1"></div>
                <div class="dot-pulse dot-pulse-delay-2"></div>
            </div>
        </div>
    
                        <div class="ai-response-content" style="display:none;"></div>
                    </div>
                    <div class="chat-message-actions d-flex align-items-center justify-content-between mt-2" style="display:none;">
                        <div class="d-flex align-items-center">
                            <button class="btn btn-text-secondary btn-sm rounded-pill btn-icon copy-ai-message-btn" title="نسخ النص">
                                <i class="ti ti-copy ti-18px"></i>
                            </button>
                            <a href="#" class="btn btn-text-secondary btn-sm rounded-pill btn-icon export-pdf-link disabled" title="جاري تحضير الرابط...">
                                <i class="ti ti-download ti-18px"></i>
                            </a>
                        </div>
                        <div class="text-muted">
                            <small>${d(new Date)}</small>
                        </div>
                    </div>
                </div>
            </div>
        </li>
    `;return{id:t,html:i}}function I(e,t,s){const i=document.getElementById(e);if(!i||!t)return;i.id=`message-${t.id}`;const c=i.querySelector(".chat-message-text");c&&(c.innerHTML=u.sanitize(l.parse(t.message||"")));const a=i.querySelector(".export-pdf-link");if(a&&s.routes.exportMessage){const r=s.routes.exportMessage.replace("__MESSAGE_ID__",t.id);a.href=r,a.setAttribute("target","_blank"),a.classList.remove("disabled"),a.title="تنزيل كـ PDF"}}function T(e){const t=e.closest(".chat-message-wrapper").querySelector(".chat-message-text");t&&navigator.clipboard.writeText(t.innerText).then(()=>{const s=e.innerHTML;e.innerHTML='<i class="ti ti-check text-success"></i>',setTimeout(()=>{e.innerHTML=s},1500)})}export{n as S,C as a,q as b,y as c,S as d,M as e,I as f,x as g,T as h,L as i,h as j,$ as k,w as r,v as s,H as u};
