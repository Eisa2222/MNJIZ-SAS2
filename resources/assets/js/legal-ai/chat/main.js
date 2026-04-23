/**
 * main.js (for Chat)
 * ============================================================================
 * نقطة الدخول ومنسق الأحداث لتطبيق "الدردشة".
 *
 * هذا الملف خفيف ويعتمد بشكل كبير على الوحدات المشتركة في مجلد `common`.
 * وظيفته هي تهيئة الحالة وربط الأحداث الخاصة بواجهة الدردشة
 * (مثل إرسال رسالة نصية) بالوظائف العامة.
 * ============================================================================
 */

import * as commonApp from '../common/app.js';
import * as commonUi from '../common/ui.js';
import * as streaming from '../common/streaming.js';
import Swal from 'sweetalert2';

document.addEventListener('DOMContentLoaded', () => {
    const dataElement = document.getElementById('chat-initial-data');
    if (!dataElement) {
        return;
    }
    const STATE = JSON.parse(dataElement.textContent);

    const form = document.querySelector('#ai-send-message-form');
    const input = document.querySelector('#ai-message-input');
    const sendBtn = document.querySelector('.send-msg-btn');

    setupEventListeners(STATE, { form, input, sendBtn });

    commonUi.scrollToBottom();
});


function setupEventListeners(STATE, DOM) {
    if (DOM.form) {
        DOM.form.addEventListener('submit', (e) => handleChatFormSubmit(e, DOM, STATE));
    }

    if (DOM.input) {
        DOM.input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                DOM.form.dispatchEvent(new Event('submit', { cancelable: true }));
            }
        });
    }

    document.body.addEventListener('click', (e) => handleGlobalClicks(e, STATE));
}

// -----------------------------------------------------------------------------
// معالجات الأحداث (Event Handlers)
// -----------------------------------------------------------------------------

/**
 * يعالج جميع النقرات على مستوى الصفحة باستخدام تفويض الأحداث.
 * @param {Event} event - كائن الحدث.
 * @param {object} STATE - حالة التطبيق.
 */
function handleGlobalClicks(event, STATE) {
    const target = event.target;
    const newChatBtn = target.closest('#new-chat-btn');
    const chatLink = target.closest('.chat-link');
    const deleteBtn = target.closest(commonUi.SELECTORS.deleteBtn);
    const copyBtn = target.closest(commonUi.SELECTORS.copyBtn);

    // ============= الإضافة الجديدة هنا =============
    const toggleSidebarBtn = target.closest('#toggle-sidebar-btn');
    // ===============================================

    if (newChatBtn) {
        event.preventDefault();
        commonApp.handleCreateNew(STATE, STATE.toolConfig.name || 'محادثة');
    } else if (chatLink) {
        event.preventDefault();
        commonApp.handleSwitchChat(chatLink.dataset.chatId, STATE);
    } else if (deleteBtn) {
        commonApp.handleDelete(deleteBtn.dataset.id, STATE, STATE.toolConfig.name || 'المحادثة');
    }

    // ============= والإضافة الجديدة هنا =============
    else if (toggleSidebarBtn) {
        document.querySelector('.app-chat')?.classList.toggle('app-chat-contacts-collapsed');
        const icon = toggleSidebarBtn;
        if (icon) {
            icon.classList.toggle('ti-menu-2');
            icon.classList.toggle('ti-x');
        }
    }
    // ===============================================

    else if (copyBtn) {
        commonUi.handleCopyMessage(copyBtn);
    }
}

/**
 * يعالج حدث إرسال نموذج الدردشة.
 * @param {Event} event - كائن الحدث.
 * @param {object} DOM - كائنات الـ DOM الخاصة بالنموذج.
 * @param {object} STATE - حالة التطبيق.
 */
async function handleChatFormSubmit(event, DOM, STATE) {
    event.preventDefault();

    if (!STATE.activeChatId) {
        Swal.fire('تنبيه!', 'يرجى تحديد أو إنشاء محادثة أولاً.', 'warning');
        return;
    }
    const messageText = DOM.input.value.trim();
    if (!messageText) {
        return;
    }

    DOM.sendBtn.disabled = true;

    const userMessageHtml = commonUi.createMessageHTML({ sender: 'user', message: messageText }, STATE);
    commonUi.addHTMLToContainer(userMessageHtml);

    const { id: aiMessageId, html: placeholderHtml } = commonUi.createEmptyAiMessage(STATE.assets);
    commonUi.addHTMLToContainer(placeholderHtml);

    DOM.form.reset();
    DOM.input.focus();
    DOM.input.style.height = '';

    const formData = new FormData();
    formData.append('message', messageText);

    const streamUrl = STATE.routes.sendStream.replace('__CHAT_ID__', STATE.activeChatId);
    const streamOptions = {
        method: 'POST',
        body: formData,
        headers: { 'X-CSRF-TOKEN': STATE.csrfToken, 'Accept': 'text/event-stream' },
    };

    try {
        const fullText = await streaming.processStream(streamUrl, streamOptions, aiMessageId, STATE);

        if (fullText && fullText.trim()) {
            const savedData = await streaming.saveAiResponseToDatabase(STATE.activeChatId, fullText, STATE);

            if (savedData?.ai_message && savedData?.chat) {
                commonUi.finalizeAiMessage(aiMessageId, savedData.ai_message, STATE);

                commonUi.updateChatTitleInUI(savedData.chat);
            }
        } else {
            commonUi.removeElementById(aiMessageId);
        }
    } catch (error) {
        commonUi.removeElementById(aiMessageId);
        Swal.fire('خطأ في الاتصال!', error.message || 'فشل الاتصال بالخادم.', 'error');
    } finally {
        DOM.sendBtn.disabled = false;
    }
}
