document.addEventListener("DOMContentLoaded",function(){(function(){const p=document.querySelector(".app-chat-contacts .sidebar-body");[].slice.call(document.querySelectorAll(".chat-contact-list-item:not(.chat-contact-list-item-title)"));const u=document.querySelector(".chat-history-body"),g=document.querySelector(".app-chat-sidebar-left .sidebar-body"),f=document.querySelector(".form-send-message"),l=document.querySelector(".message-input"),d=document.querySelector(".attachment-input"),r=document.querySelector(".attachment-preview"),w=document.querySelector(".chat-search-input");$.ajaxSetup({headers:{"X-CSRF-TOKEN":$('meta[name="csrf-token"]').attr("content")}}),p&&new PerfectScrollbar(p,{wheelPropagation:!1,suppressScrollX:!0}),u&&new PerfectScrollbar(u,{wheelPropagation:!1,suppressScrollX:!0}),g&&new PerfectScrollbar(g,{wheelPropagation:!1,suppressScrollX:!0}),l&&(l.addEventListener("input",function(){this.style.height="auto",this.style.height=Math.min(this.scrollHeight,120)+"px"}),f.addEventListener("submit",function(){setTimeout(()=>{l.style.height="auto"},100)}));function v(){u.scrollTop=u.scrollHeight}function b(){let e=parseInt(window.authId),t=parseInt($("#receiver_id").val()),a=[e,t];a.sort();let s="chat."+a[0]+"."+a[1];if(window.currentChatChannel&&window.Echo.leave("private-"+window.currentChatChannel),window.currentChatChannel=s,typeof window.Echo>"u"){console.error("window.Echo is undefined");return}window.Echo.private(s).listen(".App\\Events\\Chat\\MessageSent",i=>{let n=$("#receiver_id").val();(i.sender_id==n||i.sender_id==window.authId)&&(L(i),v())})}function S(){return"temp_"+Date.now()+"_"+Math.random().toString(36).substr(2,9)}function L(e){let t=document.querySelectorAll('[data-message-id^="temp_"]'),a=t[t.length-1];if(a&&e.sender_id==window.authId){a.setAttribute("data-message-id",e.id);let s=a.querySelector(".message-status");s&&(s.className="ti ti-check text-muted ms-1 message-status",s.title="تم الإرسال"),a.classList.remove("message-sending")}else e.sender_id!=window.authId&&(e.sender&&e.sender.image&&(e.sender.image="/storage/"+e.sender.image),x(e),y())}function y(){document.querySelectorAll(".chat-message-right .message-status.ti-check:not(.ti-checks)").forEach(function(t){t.className="ti ti-checks text-primary ms-1 message-status",t.title="تم القراءة"})}function x(e,t=!1){const a=e.sender&&e.sender.image?e.sender.image:"/assets/img/branding/Alburhan-Logo.png";let s="";e.has_attachment&&(e.sender_id==window.authId,s='<div class="attachment-wrapper mt-2"> ... </div>');let i=e.message?`<p class="mb-0">${e.message}</p>`:"",n=t?'<div class="spinner-border spinner-border-sm text-white-50 ms-1 message-status" style="width: 0.8rem; height: 0.8rem;" role="status"></div>':'<i class="ti ti-check text-muted ms-1 message-status" title="تم الإرسال"></i>',o="";e.sender_id==window.authId?o=`
            <li class="chat-message chat-message-right ${t?"message-sending":""}" data-message-id="${e.id}">
                <div class="d-flex overflow-hidden">
                    <div class="chat-message-wrapper flex-grow-1">
                        <div class="chat-message-text">${i}${s}</div>
                        <div class="text-end text-muted mt-1">
                            <small>${e.created_at}</small>
                            ${n}
                        </div>
                    </div>
                    <div class="user-avatar flex-shrink-0 ms-4">
                        <div class="avatar avatar-sm">
                            <img src="${a}" alt="الصورة الرمزية" class="rounded-circle">
                        </div>
                    </div>
                </div>
            </li>
        `:o=`
            <li class="chat-message" data-message-id="${e.id}">
                <div class="d-flex overflow-hidden">
                    <div class="user-avatar flex-shrink-0 me-4">
                        <div class="avatar avatar-sm">
                            <img src="${a}" alt="الصورة الرمزية" class="rounded-circle">
                        </div>
                    </div>
                    <div class="chat-message-wrapper flex-grow-1">
                        <div class="chat-message-text">${i}${s}</div>
                        <div class="text-muted mt-1">
                            <small>${e.created_at}</small>
                        </div>
                    </div>
                </div>
            </li>
        `,$("#messages").append(o)}d&&d.addEventListener("change",function(e){const t=e.target.files[0];if(t){const a=t.name,s=(t.size/1024/1024).toFixed(2);r.innerHTML=`
                        <div class="d-flex align-items-center p-2 border rounded mx-auto" style="max-width: 350px;">
                            <div class="me-3 ms-3">
                                <i class="ti ti-file text-primary fs-4"></i>
                            </div>
                            <div class="flex-grow-1 text-truncate" style="min-width:0;">
                                <div class="fw-medium text-dark small text-truncate" style="font-size: 0.92rem;">${a}</div>
                                <small class="text-muted">${s} KB</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger ms-2">
                                <i class="ti ti-x"></i>
                            </button>
                        </div>
                    `,r.style.display="block",r.querySelector(".btn").addEventListener("click",function(){d.value="",r.style.display="none",r.innerHTML=""})}else r.style.display="none"}),$(document).on("click",".chat-contact-list-item.user",function(){$(".chat-contact-list-item").removeClass("active"),$(this).addClass("active");let e=$(this).data("id");$("#receiver_id").val(e);let t=$(this).find(".chat-contact-name").text(),a=$(this).find(".chat-contact-status").text(),s=$(this).find("img").attr("src");$("#receiver-name").text(t),$("#receiver-job").text(a),$("#receiver-image").attr("src",s),$.ajax({type:"get",url:window.compileRoute(window.namedRoutes.chatMessagesGet,{user_id:String(e)}),cache:!1,success:function(i){$("#messages").html(i),v(),b(),setTimeout(function(){_()},300)},error:function(i,n,o){}})});function _(){$(".chat-message:not(.chat-message-right)").last().length>0&&y()}$(document).ready(function(){setTimeout(function(){if(typeof window.selectedUserId<"u"&&window.selectedUserId&&window.selectedUserId!==null&&window.selectedUserId!=="null"){let t=$(`.chat-contact-list-item.user[data-id="${window.selectedUserId}"]`);if(t.length>0){t[0].scrollIntoView({behavior:"smooth",block:"center"}),setTimeout(()=>{t.trigger("click"),t.addClass("pre-selected"),setTimeout(()=>{t.removeClass("pre-selected")},3e3)},500);return}}let e=$(".chat-contact-list-item.user").first();e.length>0&&e.trigger("click")},200)}),f.addEventListener("submit",e=>{e.preventDefault();let t=l.value.trim(),a=$("#receiver_id").val(),s=d?d.files[0]:null;if(!t&&!s){typeof toastr<"u"?toastr.error("يجب كتابة رسالة أو إرفاق ملف"):alert("يجب كتابة رسالة أو إرفاق ملف");return}if(!a){typeof toastr<"u"?toastr.error("يرجى اختيار مستقبل للرسالة"):alert("يرجى اختيار مستقبل للرسالة");return}let i={id:S(),message:t,sender_id:window.authId,receiver_id:a,created_at:new Date().toLocaleTimeString("en-US",{hour:"2-digit",minute:"2-digit",hour12:!0}),has_attachment:!!s,attachment_name:s?s.name:null,attachment_url:s?URL.createObjectURL(s):null,sender:{id:window.authId,name:"أنت",image:window.authUserImage}};x(i,!0),v(),l.value="",l.style.height="auto",d&&(d.value="",r.style.display="none",r.innerHTML="");let n=f.querySelector(".send-msg-btn"),o=n.innerHTML;n.disabled=!0,n.innerHTML='<i class="ti ti-loader-2 ti-16px animate-spin"></i>';let m=new FormData;m.append("receiver_id",a),t&&m.append("message",t),s&&m.append("attachment",s),$.ajax({type:"post",url:window.namedRoutes.chatMessagesSend,data:m,processData:!1,contentType:!1,cache:!1,success:function(c){},error:function(c,I,M){console.error(M),$(`[data-message-id="${i.id}"]`).remove();let h="حدث خطأ في إرسال الرسالة";c.responseJSON&&c.responseJSON.errors?h=Object.values(c.responseJSON.errors).flat().join("<br>"):c.responseJSON&&c.responseJSON.message&&(h=c.responseJSON.message),typeof toastr<"u"?toastr.error(h):alert(h)},complete:function(){n.disabled=!1,n.innerHTML=o,l.focus()}})}),w&&w.addEventListener("keyup",e=>{let t=e.currentTarget.value.toLowerCase(),a=0,s=document.querySelector(".chat-list-item-0"),i=[].slice.call(document.querySelectorAll("#chat-list li:not(.chat-contact-list-item-title)"));C(i,a,t,s)});function C(e,t,a,s){e.forEach(i=>{let n=i.textContent.toLowerCase();a?-1<n.indexOf(a)?(i.classList.add("d-flex"),i.classList.remove("d-none"),t++):i.classList.add("d-none"):(i.classList.add("d-flex"),i.classList.remove("d-none"),t++)}),s&&(t==0?s.classList.remove("d-none"):s.classList.add("d-none"))}})()});
