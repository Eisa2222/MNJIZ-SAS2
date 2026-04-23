/**
 * main.js (for Precedents)
 * ============================================================================
 * نقطة الدخول ومنسق الأحداث لتطبيق "بحث السوابق القضائية".
 * هذا الكود يستخدم تفويض الأحداث الكامل لضمان الاستقرار.
 * ============================================================================
 */
import * as commonApp from '../common/app.js';
import * as commonUi from '../common/ui.js';
import * as streaming from '../common/streaming.js';
import Swal from 'sweetalert2';

/**
 * الوظيفة الرئيسية التي يتم تشغيلها عند اكتمال تحميل الصفحة.
 */
document.addEventListener('DOMContentLoaded', () => {
    const dataElement = document.getElementById('precedents-initial-data');
    if (!dataElement) return;
    const STATE = JSON.parse(dataElement.textContent);

    setupEventListeners(STATE);
    commonUi.scrollToBottom();
});

/**
 * إعداد جميع معالجات الأحداث للواجهة (مرة واحدة).
 */
function setupEventListeners(STATE) {
    // معالج أحداث عام وموحد لجميع النقرات
    document.body.addEventListener('click', (e) => handleGlobalClicks(e, STATE));

    // ربط الأحداث التي لا يمكن تفويضها بسهولة (submit, input, change)
    const form = document.querySelector('#precedent-search-form');
    if (form) {
        const input = form.querySelector('#query-input');
        const fileInput = form.querySelector('#document-input');

        form.addEventListener('submit', (e) => handleSearchSubmit(e, form, STATE));

        if (input) {
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    form.dispatchEvent(new Event('submit', { cancelable: true }));
                }
            });
        }
        if (fileInput) {
            fileInput.addEventListener('change', () => handleFileSelection(fileInput));
        }
    }
}

/**
 * يعالج جميع النقرات العامة على الصفحة.
 */
function handleGlobalClicks(event, STATE) {
    const target = event.target;

    const newSearchBtn = target.closest('#new-search-btn');
    const chatLink = target.closest('.chat-link');
    const deleteBtn = target.closest(commonUi.SELECTORS.deleteBtn);
    const copyBtn = target.closest(commonUi.SELECTORS.copyBtn);
    const toggleSidebarBtn = target.closest('#toggle-sidebar-btn');

    if (newSearchBtn || chatLink) {
        event.preventDefault();
    }

    if (newSearchBtn) {
        commonApp.handleCreateNew(STATE, 'بحث');
    } else if (chatLink) {
        commonApp.handleSwitchChat(chatLink.dataset.chatId, STATE);
    } else if (deleteBtn) {
        commonApp.handleDelete(deleteBtn.dataset.id, STATE, 'البحث');
    } else if (copyBtn) {
        commonUi.handleCopyMessage(copyBtn);
    } else if (toggleSidebarBtn) {
        document.querySelector('.app-chat')?.classList.toggle('app-chat-contacts-collapsed');
        const icon = toggleSidebarBtn;
        if (icon) {
            icon.classList.toggle('ti-menu-2');
            icon.classList.toggle('ti-x');
        }
    }
}

/**
 * يعالج إرسال نموذج البحث.
 */
async function handleSearchSubmit(event, form, STATE) {
    event.preventDefault();

    const input = form.querySelector('#query-input');
    const fileInput = form.querySelector('#document-input');
    const submitBtn = form.querySelector('#start-search-btn');

    if (!STATE.activeChatId) {
        Swal.fire('تنبيه!', 'يرجى إنشاء أو تحديد جلسة بحث أولاً.', 'warning');
        return;
    }
    if (!input.value.trim() && fileInput.files.length === 0) {
        Swal.fire('تنبيه!', 'يرجى كتابة نص البحث أو إرفاق ملف.', 'warning');
        return;
    }

    setLoadingState(submitBtn, true);

    const userMessage = buildUserMessage(form);
    const userMessageHtml = commonUi.createMessageHTML({ sender: 'user', message: userMessage }, STATE);
    commonUi.addHTMLToContainer(userMessageHtml);

    const { id: aiMessageId, html: placeholderHtml } = commonUi.createEmptyAiMessage(STATE.assets);
    commonUi.addHTMLToContainer(placeholderHtml);

    const formData = new FormData(form);
    const streamUrl = STATE.routes.handleStream.replace('__CHAT_ID__', STATE.activeChatId);
    const streamOptions = { method: 'POST', body: formData, headers: { 'Accept': 'text/event-stream' } };

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
        setLoadingState(submitBtn, false);
        form.reset();
        document.getElementById('file-name-display').textContent = '';
    }
}

/**
 * يعالج عرض اسم الملف المختار.
 */
function handleFileSelection(fileInput) {
    const fileNameDisplay = document.getElementById('file-name-display');
    if (fileInput.files.length > 0) {
        fileNameDisplay.textContent = `الملف المرفق: ${fileInput.files[0].name}`;
    } else {
        fileNameDisplay.textContent = '';
    }
}

/**
 * يبني رسالة المستخدم للعرض في الواجهة.
 */
function buildUserMessage(form) {
    const text = form.querySelector('#query-input').value.trim();
    const file = form.querySelector('#document-input').files[0];
    let message = `تم طلب بحث عن سوابق قضائية.`;
    if (text) message += `\n\n**نص البحث:**\n${text}`;
    if (file) message += `\n\n**الملف المرفق للتحليل:** ${file.name}`;
    return message;
}

/**
 * يتحكم في حالة التحميل لزر الإرسال.
 */
function setLoadingState(button, isLoading) {
    if (!button) return;
    const spinner = button.querySelector('.spinner-border');
    const text = button.querySelector('span.align-middle');
    const icon = button.querySelector('i');

    button.disabled = isLoading;

    if (spinner) spinner.classList.toggle('d-none', !isLoading);
    if (text) text.style.display = isLoading ? 'none' : 'inline-block';
    if (icon) icon.style.display = isLoading ? 'none' : 'inline-block';
}
