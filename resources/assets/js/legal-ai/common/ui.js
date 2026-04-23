/**
 * common/ui.js
 * ============================================================================
 * وحدة مشتركة للتعامل مع واجهة المستخدم (DOM) لجميع أدوات الذكاء الاصطناعي.
 * تحتوي على جميع دوال التلاعب بالـ DOM القابلة لإعادة الاستخدام.
 * ============================================================================
 */
import { marked } from 'marked';
import DOMPurify from 'dompurify';

marked.setOptions({
    breaks: true,
    gfm: true,
});

export const SELECTORS = {
    messagesContainer: '#messages-container',
    chatList: '#chat-list',
    chatHeaderTitle: '#chat-header-title',
    chatBody: '.chat-history-body',
    deleteBtn: '.delete-conversation-btn',
    copyBtn: '.copy-ai-message-btn',
    welcomeTemplate: '#welcome-message-template'
};

function formatTime(date) {
    return new Date(date).toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    });
}

export function scrollToBottom() {
    const container = document.querySelector(SELECTORS.chatBody);
    if (container) {
        container.scrollTop = container.scrollHeight;
    }
}

export function removeElementById(id) {
    const el = document.getElementById(id);
    if (el) el.remove();
}

export function showWelcomeMessage() {
    const container = document.querySelector(SELECTORS.messagesContainer);
    const template = document.querySelector(SELECTORS.welcomeTemplate);
    if (container && template) {
        container.innerHTML = '';
        container.appendChild(template.content.cloneNode(true));
    }
}

export function showLoadingIndicator() {
    const container = document.querySelector(SELECTORS.messagesContainer);
    if (container) {
        container.innerHTML = `<div class="d-flex justify-content-center align-items-center h-100"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">جاري التحميل...</span></div></div>`;
    }
}

export function showErrorInChatWindow(errorMessage) {
    const container = document.querySelector(SELECTORS.messagesContainer);
    if (container) {
        container.innerHTML = `<div class="text-center text-danger py-5"><i class="ti ti-alert-circle ti-lg mb-2"></i><p>${errorMessage}</p></div>`;
    }
}

export function addHTMLToContainer(html) {
    const container = document.querySelector(SELECTORS.messagesContainer);
    if (container) {
        const emptyState = container.querySelector('.chat-bienvenida-message, .d-flex.justify-content-center');
        if (emptyState) emptyState.remove();
        container.insertAdjacentHTML('beforeend', html);
        scrollToBottom();
    }
}

export function updateChatInterface(chatData, STATE) {
    if (!chatData || !chatData.chat) {
        showErrorInChatWindow('بيانات المحادثة غير صالحة.');
        return;
    }

    STATE.activeChatId = chatData.chat.id;

    const chatTitle = document.querySelector(SELECTORS.chatHeaderTitle);
    if (chatTitle) {
        chatTitle.textContent = chatData.chat.title;
    }

    const messagesContainer = document.querySelector(SELECTORS.messagesContainer);
    if (!messagesContainer) return;

    messagesContainer.innerHTML = '';

    if (chatData.messages && chatData.messages.length > 0) {
        const fragment = document.createDocumentFragment();
        chatData.messages.forEach(message => {
            const messageHTML = createMessageHTML(message, STATE);
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = messageHTML;
            if (tempDiv.firstChild) {
                fragment.appendChild(tempDiv.firstChild);
            }
        });
        messagesContainer.appendChild(fragment);
    } else {
        showWelcomeMessage();
    }

    updateActiveChatInSidebar(chatData.chat.id);
    scrollToBottom();
}

export function addChatToSidebar(chat, STATE) {
    const chatList = document.querySelector(SELECTORS.chatList);
    if (!chatList) return;

    const emptyMessage = chatList.querySelector('.text-center.p-4');
    if (emptyMessage) emptyMessage.remove();

    document.querySelectorAll('.conversation-item').forEach(item => item.classList.remove('active'));

    const avatarUrl = STATE.assets.aiAvatar;

    const chatHTML = `
        <li class="chat-contact-list-item mb-1 conversation-item position-relative active" data-id="${chat.id}">
            <a href="javascript:void(0);" class="d-flex align-items-center text-decoration-none chat-link" data-chat-id="${chat.id}">
                <div class="flex-shrink-0 avatar me-3">
                    <img src="${avatarUrl}" alt="AI" class="rounded-circle">
                </div>
                <div class="chat-contact-info flex-grow-1 overflow-hidden">
                    <h6 class="chat-contact-name text-truncate m-0 fw-normal">${chat.title}</h6>
                </div>
            </a>
            <button class="btn btn-sm btn-icon delete-conversation-btn" data-id="${chat.id}" title="حذف">
                <i class="ti ti-trash"></i>
            </button>
        </li>
    `;
    chatList.insertAdjacentHTML('afterbegin', chatHTML);
}

export function removeChatFromSidebar(chatId) {
    const chatElement = document.querySelector(`.conversation-item[data-id="${chatId}"]`);
    if (chatElement) chatElement.remove();

    const chatList = document.querySelector(SELECTORS.chatList);
    if (chatList && chatList.children.length === 0) {
        chatList.innerHTML = '<li class="text-center p-4 text-muted">لا توجد محادثات سابقة.</li>';
    }
}

export function updateActiveChatInSidebar(chatId) {
    document.querySelectorAll('.conversation-item').forEach(chat => {
        chat.classList.toggle('active', String(chat.dataset.id) === String(chatId));
    });
}

/**
 * [الدالة الجديدة المضافة]
 * يحدث فقط عنوان المحادثة في الشريط الجانبي والرأس.
 * @param {object} chat - كائن المحادثة المحدث من الخادم.
 */
export function updateChatTitleInUI(chat) {
    if (!chat || !chat.id || !chat.title) return;

    const sidebarItem = document.querySelector(`.conversation-item[data-id="${chat.id}"] .chat-contact-name`);
    if (sidebarItem) {
        sidebarItem.textContent = chat.title;
    }

    const headerTitle = document.querySelector(SELECTORS.chatHeaderTitle);
    if (headerTitle) {
        headerTitle.textContent = chat.title;
    }
}

export function createMessageHTML(message, STATE) {
    if (!STATE || !STATE.user || !STATE.assets) {
        return '';
    }
    const isUser = message.sender === 'user';
    const avatar = isUser ? STATE.user.avatar : STATE.assets.aiAvatar;
    const directionClass = isUser ? 'chat-message-right' : '';
    const messageId = message.id?.toString().startsWith('temp-') ? message.id : `message-${message.id}`;
    const timestamp = message.created_at ? formatTime(new Date(message.created_at)) : formatTime(new Date());
    const messageContent = DOMPurify.sanitize(marked.parse(message.message || ''));

    if (isUser) {
        return `<li class="chat-message ${directionClass}" id="${messageId}" data-sender="user"><div class="d-flex overflow-hidden"><div class="chat-message-wrapper flex-grow-1"><div class="chat-message-text">${messageContent}</div><div class="text-end text-muted mt-1"><small>${timestamp}</small></div></div><div class="user-avatar flex-shrink-0 ms-3"><div class="avatar avatar-sm"><img src="${avatar}" alt="Avatar" class="rounded-circle"></div></div></div></li>`;
    } else {
        const exportUrl = STATE.routes.exportMessage ? STATE.routes.exportMessage.replace('__MESSAGE_ID__', message.id) : '#';
        const downloadButtonHtml = STATE.routes.exportMessage ? `<a href="${exportUrl}" target="_blank" class="btn btn-text-secondary btn-sm rounded-pill btn-icon export-pdf-link" title="تنزيل كـ PDF"><i class="ti ti-download ti-18px"></i></a>` : '';
        return `<li class="chat-message" id="${messageId}" data-sender="ai"><div class="d-flex overflow-hidden"><div class="user-avatar flex-shrink-0 me-3"><div class="avatar avatar-sm"><img src="${avatar}" alt="AI Avatar" class="rounded-circle"></div></div><div class="chat-message-wrapper flex-grow-1"><div class="chat-message-text">${messageContent}</div><div class="chat-message-actions d-flex align-items-center justify-content-between mt-2"><div class="d-flex align-items-center"><button class="btn btn-text-secondary btn-sm rounded-pill btn-icon copy-ai-message-btn" title="نسخ النص"><i class="ti ti-copy ti-18px"></i></button>${downloadButtonHtml}</div><div class="text-muted"><small>${timestamp}</small></div></div></div></div></li>`;
    }
}


/**
 * إنشاء رسالة AI فارغة مع مؤشر كتابة احترافي محسن
 * @param {object} assets - كائن الأصول
 * @returns {object} معرف الرسالة والـ HTML الخاص بها
 */
export function createEmptyAiMessage(assets) {
    const id = `message-ai-${Date.now()}`;

    // مؤشر كتابة مضغوط مع نقاط أكبر
    const typingIndicatorHTML = `
        <div class="ai-typing-indicator d-flex align-items-center justify-content-start">
            <div class="typing-dots d-flex align-items-center">
                <div class="dot-pulse"></div>
                <div class="dot-pulse dot-pulse-delay-1"></div>
                <div class="dot-pulse dot-pulse-delay-2"></div>
            </div>
        </div>
    `;

    const html = `
        <li class="chat-message" id="${id}" data-sender="ai">
            <div class="d-flex overflow-hidden">
                <div class="user-avatar flex-shrink-0 me-3">
                    <div class="avatar avatar-sm">
                        <img src="${assets.aiAvatar}" alt="AI Avatar" class="rounded-circle">
                    </div>
                </div>
                <div class="chat-message-wrapper flex-grow-1">
                    <div class="chat-message-text">
                        ${typingIndicatorHTML}
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
                            <small>${formatTime(new Date())}</small>
                        </div>
                    </div>
                </div>
            </div>
        </li>
    `;

    return { id, html };
}

export function finalizeAiMessage(tempId, finalMessageData, STATE) {
    const messageElement = document.getElementById(tempId);
    if (!messageElement || !finalMessageData) return;

    messageElement.id = `message-${finalMessageData.id}`;

    const messageContent = messageElement.querySelector('.chat-message-text');
    if (messageContent) {
        messageContent.innerHTML = DOMPurify.sanitize(marked.parse(finalMessageData.message || ''));
    }

    const downloadLink = messageElement.querySelector('.export-pdf-link');
    if (downloadLink && STATE.routes.exportMessage) {
        const exportUrl = STATE.routes.exportMessage.replace('__MESSAGE_ID__', finalMessageData.id);
        downloadLink.href = exportUrl;
        downloadLink.setAttribute('target', '_blank');
        downloadLink.classList.remove('disabled');
        downloadLink.title = 'تنزيل كـ PDF';
    }
}

export function handleCopyMessage(button) {
    const messageTextContainer = button.closest('.chat-message-wrapper').querySelector('.chat-message-text');
    if (messageTextContainer) {
        navigator.clipboard.writeText(messageTextContainer.innerText)
            .then(() => {
                const originalIcon = button.innerHTML;
                button.innerHTML = '<i class="ti ti-check text-success"></i>';
                setTimeout(() => { button.innerHTML = originalIcon; }, 1500);
            });
    }
}
