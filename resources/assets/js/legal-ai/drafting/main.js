/**
 * main.js (for Drafting)
 * ============================================================================
 * نقطة الدخول ومنسق الأحداث لتطبيق "صياغة المذكرات".
 */
import * as commonApp from '../common/app.js';
import * as commonUi from '../common/ui.js';
import * as streaming from '../common/streaming.js';
import Swal from 'sweetalert2';

document.addEventListener('DOMContentLoaded', () => {
    const dataElement = document.getElementById('drafting-initial-data');
    if (!dataElement) return;
    const STATE = JSON.parse(dataElement.textContent);

    setupEventListeners(STATE);
    commonUi.scrollToBottom();
});

/**
 * إعداد جميع معالجات الأحداث للواجهة (مرة واحدة).
 */
function setupEventListeners(STATE) {
    document.body.addEventListener('click', (e) => handleGlobalClicks(e, STATE));

    const form = document.querySelector('#drafting-form');
    if (form) {
        const input = form.querySelector('#drafting-input');
        const fileInput = form.querySelector('#document-input');
        const optionsContainer = form.querySelector('#drafting-options-container');

        form.addEventListener('submit', (e) => handleDraftingSubmit(e, form, STATE));

        const showOptionsIfNeeded = () => {
            if ((input && input.value.trim() !== '') || (fileInput && fileInput.files.length > 0)) {
                if (optionsContainer) optionsContainer.style.display = 'block';
            }
        };

        if (input) input.addEventListener('input', showOptionsIfNeeded);
        if (fileInput) {
            fileInput.addEventListener('change', () => {
                handleFileSelection(fileInput);
                showOptionsIfNeeded();
            });
        }

        // [الحل هنا] إضافة معالج للنقر على أزرار الراديو
        // هذا الجزء لم يكن موجودًا في الكود الذي قدمته
        if (optionsContainer) {
            optionsContainer.querySelectorAll('input[name="drafting_type"]').forEach(radio => {
                radio.addEventListener('change', () => {
                    // يمكنك هنا إضافة أي منطق تريده عند تغيير الاختيار،
                    // مثل تغيير نص أو أيقونة. حاليًا، لا نحتاج لشيء.
                    console.log(`Drafting type changed to: ${radio.value}`);
                });
            });
        }
    }
}

/**
 * يعالج جميع النقرات العامة على الصفحة.
 */
function handleGlobalClicks(event, STATE) {
    const target = event.target;

    const newDraftBtn = target.closest('#new-draft-btn');
    const chatLink = target.closest('.chat-link');
    const deleteBtn = target.closest(commonUi.SELECTORS.deleteBtn);
    const attachFileLabel = target.closest('label[for="document-input"]'); // استهداف الـ label مباشرة
    const toggleSidebarBtn = target.closest('#toggle-sidebar-btn');
    const copyBtn = target.closest(commonUi.SELECTORS.copyBtn);

    if (newDraftBtn || chatLink) {
        event.preventDefault();
    }

    if (newDraftBtn) {
        commonApp.handleCreateNew(STATE, 'صياغة');
    } else if (chatLink) {
        commonApp.handleSwitchChat(chatLink.dataset.chatId, STATE);
    } else if (deleteBtn) {
        commonApp.handleDelete(deleteBtn.dataset.id, STATE, 'الصياغة');
    } else if (toggleSidebarBtn) {
        // تم تبسيط هذا الجزء ليكون أكثر قوة
        document.querySelector('.app-chat')?.classList.toggle('app-chat-contacts-collapsed');
        const icon = toggleSidebarBtn;
        if (icon) {
            icon.classList.toggle('ti-menu-2');
            icon.classList.toggle('ti-x');
        }
    } else if (copyBtn) {
        commonUi.handleCopyMessage(copyBtn);
    }
    // لا حاجة لـ `attachFileBtn` هنا لأن النقر على الـ label يعمل تلقائيًا
}

/**
 * يعالج إرسال نموذج الصياغة.
 */
async function handleDraftingSubmit(event, form, STATE) {
    event.preventDefault();

    const input = form.querySelector('#drafting-input');
    const fileInput = form.querySelector('#document-input');
    const submitBtn = form.querySelector('#start-drafting-btn');

    if (!STATE.activeChatId || (!input.value.trim() && fileInput.files.length === 0)) {
        Swal.fire('تنبيه!', 'يرجى كتابة بعض النقاط أو إرفاق ملف.', 'warning');
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
    const streamOptions = {
        method: 'POST',
        body: formData,
        headers: { 'Accept': 'text/event-stream' }
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
        setLoadingState(submitBtn, false);
        form.reset();
        document.querySelector('#file-name-display').textContent = '';
        const optionsContainer = document.querySelector('#drafting-options-container');
        if (optionsContainer) {
            optionsContainer.style.display = 'none';
            const defaultRadio = optionsContainer.querySelector('input[value="defense_memo"]');
            if (defaultRadio) defaultRadio.checked = true;
        }
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
    const typeElement = form.querySelector('input[name="drafting_type"]:checked');
    const typeLabel = typeElement ? typeElement.nextElementSibling.textContent.trim() : 'مستند';
    const text = form.querySelector('#drafting-input').value.trim();
    const file = form.querySelector('#document-input').files[0];

    let message = `تم طلب صياغة **${typeLabel}**.`;
    if (text) message += `\n\n**النقاط الأساسية:**\n${text}`;
    if (file) message += `\n\n**الملف المرفق:** ${file.name}`;
    return message;
}


/**
 * يتحكم في حالة التحميل لزر الإرسال.
 */
function setLoadingState(button, isLoading) {
    if (!button) return;
    const icon = button.querySelector('i');
    const textSpan = button.querySelector('span.align-middle');
    const spinner = button.querySelector('.spinner-border');

    button.disabled = isLoading;

    if (isLoading) {
        if (icon) icon.style.display = 'none';
        if (textSpan) textSpan.style.display = 'none';
        if (spinner) spinner.classList.remove('d-none');
    } else {
        if (icon) icon.style.display = 'inline-block';
        if (textSpan) textSpan.style.display = 'inline-block';
        if (spinner) spinner.classList.add('d-none');
    }
}
