/**
 * main.js (for Summarization)
 * ============================================================================
 * نقطة الدخول ومنسق الأحداث لتطبيق "تلخيص المستندات".
 * هذا الكود يستخدم أسلوب إعادة ربط الأحداث بشكل آمن بعد كل تحديث للـ DOM.
 * تم تحديثه بالحل النهائي لمشكلة تضارب الأحداث بين الـ label والأزرار.
 * ============================================================================
 */
import * as commonApp from '../common/app.js';
import * as commonUi from '../common/ui.js';
import * as streaming from '../common/streaming.js';
import Swal from 'sweetalert2';

let globalClickHandler = null;

/**
 * الوظيفة الرئيسية.
 */
document.addEventListener('DOMContentLoaded', () => {
    const dataElement = document.getElementById('summarize-initial-data');
    if (!dataElement) return;
    const STATE = JSON.parse(dataElement.textContent);
    setupEventListeners(STATE);
    commonUi.scrollToBottom();
});

/**
 * إعداد جميع معالجات الأحداث للواجهة.
 */
function setupEventListeners(STATE) {
    const DOM = {
        form: document.querySelector('#summarize-form'),
        dropZone: document.querySelector('#drop-zone'),
        fileInput: document.querySelector('#document-input'),
        fileNameDisplay: document.querySelector('#file-name'),
        submitBtnWrapper: document.querySelector('#submit-btn-wrapper'),
    };

    if (globalClickHandler) {
        document.body.removeEventListener('click', globalClickHandler);
    }
    globalClickHandler = (e) => handleGlobalClicks(e, STATE);
    document.body.addEventListener('click', globalClickHandler);

    if (DOM.form && DOM.dropZone && DOM.fileInput) {
        DOM.form.onsubmit = (e) => handleSummarizeFormSubmit(e, DOM, STATE);

        addDragDropEvents(DOM.dropZone, DOM.fileInput, () => {
            updateFileDisplay(DOM.fileInput, DOM.fileNameDisplay, DOM.submitBtnWrapper);
        });

        if (DOM.submitBtnWrapper) {
            DOM.submitBtnWrapper.onclick = (event) => {
                event.stopPropagation();
            };

            DOM.submitBtnWrapper.querySelectorAll('.summary-option').forEach(option => {
                option.onclick = (e) => handleDropdownSelection(e, option);
            });
        }
    }
}

/**
 * يعالج النقرات العامة على الصفحة.
 */
function handleGlobalClicks(event, STATE) {
    const target = event.target;
    if (target.closest('.summary-option')) { return; }
    const newSummaryBtn = target.closest('#new-summary-btn');
    const chatLink = target.closest('.chat-link');
    const deleteBtn = target.closest(commonUi.SELECTORS.deleteBtn);
    const copyBtn = target.closest(commonUi.SELECTORS.copyBtn);
    const toggleSidebarBtn = target.closest('#toggle-sidebar-btn');

    if (newSummaryBtn) {
        event.preventDefault();
        commonApp.handleCreateNew(STATE, 'جلسة تلخيص').then(() => setupEventListeners(STATE));
    } else if (chatLink) {
        event.preventDefault();
        commonApp.handleSwitchChat(chatLink.dataset.chatId, STATE).then(() => setupEventListeners(STATE));
    } else if (deleteBtn) {
        commonApp.handleDelete(deleteBtn.dataset.id, STATE, 'جلسة التلخيص');
    }
    else if (toggleSidebarBtn) {
        document.querySelector('.app-chat')?.classList.toggle('app-chat-contacts-collapsed');
        const icon = toggleSidebarBtn;
        if (icon) {
            icon.classList.toggle('ti-menu-2');
            icon.classList.toggle('ti-x');
        }
    }

    else if (copyBtn) {
        commonUi.handleCopyMessage(copyBtn);
    }
}

/**
 * يعالج إرسال نموذج التلخيص.
 */
async function handleSummarizeFormSubmit(event, DOM, STATE) {
    event.preventDefault();
    if (!STATE.activeChatId || !DOM.fileInput || DOM.fileInput.files.length === 0) {
        Swal.fire('تنبيه!', 'يرجى اختيار ملف أولاً.', 'warning');
        return;
    }
    setLoadingState(DOM.submitBtnWrapper, true);
    const userMessageHtml = commonUi.createMessageHTML({
        sender: 'user', message: `تم تحديد ملف للتلخيص: **${DOM.fileInput.files[0].name}**`
    }, STATE);
    commonUi.addHTMLToContainer(userMessageHtml);
    const { id: aiMessageId, html: placeholderHtml } = commonUi.createEmptyAiMessage(STATE.assets);
    commonUi.addHTMLToContainer(placeholderHtml);
    const formData = new FormData(DOM.form);
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
        setLoadingState(DOM.submitBtnWrapper, false);
        DOM.form.reset();
        document.getElementById('summary_type_input').value = 'detailed';
        updateFileDisplay(DOM.fileInput, DOM.fileNameDisplay, DOM.submitBtnWrapper);
    }
}


/**
 * يربط أحداث السحب والإفلات.
 * تم تبسيط هذه الدالة. النقر الآن يتم معالجته بواسطة سلوك <label> الافتراضي.
 */
function addDragDropEvents(dropZone, fileInput, onFileSelectCallback) {
    fileInput.onchange = onFileSelectCallback;

    dropZone.ondragover = (e) => { e.preventDefault(); dropZone.classList.add('drag-over'); };
    dropZone.ondragleave = () => dropZone.classList.remove('drag-over');
    dropZone.ondrop = (e) => {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            onFileSelectCallback();
        }
    };
}

/**
 * يعالج اختيار خيار من القائمة المنسدلة.
 */
function handleDropdownSelection(event, selectedOption) {
    event.preventDefault();
    const menu = selectedOption.closest('.dropdown-menu');
    menu.querySelectorAll('.summary-option').forEach(opt => opt.classList.remove('active'));
    selectedOption.classList.add('active');
    const selectedType = selectedOption.dataset.value;
    document.getElementById('summary_type_input').value = selectedType;
    const buttonText = document.getElementById('submit-btn-text');
    buttonText.textContent = selectedType === 'short' ? 'تلخيص مختصر' : 'تلخيص مفصل';
    const dropdownInstance = bootstrap.Dropdown.getInstance(menu.previousElementSibling);
    if (dropdownInstance) dropdownInstance.hide();
}

/**
 * يحدث واجهة المستخدم لتعكس الملف المختار.
 */
function updateFileDisplay(fileInput, fileNameDisplay, buttonWrapper) {
    if (!fileInput || !fileNameDisplay || !buttonWrapper) return;
    const submitBtn = buttonWrapper.querySelector('#submit-btn');
    const dropdownToggle = buttonWrapper.querySelector('.dropdown-toggle');
    if (fileInput.files.length > 0) {
        fileNameDisplay.textContent = fileInput.files[0].name;
        submitBtn.disabled = false;
        dropdownToggle.disabled = false;
    } else {
        fileNameDisplay.textContent = 'اسحب ملفًا إلى هنا، أو انقر للإرفاق...';
        submitBtn.disabled = true;
        dropdownToggle.disabled = true;
        const buttonText = document.getElementById('submit-btn-text');
        if (buttonText) buttonText.textContent = 'تلخيص';
        const summaryOptions = buttonWrapper.querySelectorAll('.summary-option');
        if (summaryOptions.length > 0) {
            summaryOptions.forEach(opt => {
                opt.classList.toggle('active', opt.dataset.value === 'detailed');
            });
        }
    }
}

/**
 * يتحكم في حالة التحميل لزر الإرسال.
 */
function setLoadingState(buttonWrapper, isLoading) {
    if (!buttonWrapper) return;
    const button = buttonWrapper.querySelector('#submit-btn');
    const spinner = button.querySelector('.spinner-border');
    const dropdownToggle = buttonWrapper.querySelector('.dropdown-toggle');
    const btnText = document.getElementById('submit-btn-text');
    button.disabled = isLoading;
    dropdownToggle.disabled = isLoading;
    if (spinner) spinner.classList.toggle('d-none', !isLoading);
    if (isLoading) {
        if (btnText) btnText.textContent = 'جاري التلخيص...';
    } else {
        if (btnText) {
            const currentType = document.getElementById('summary_type_input').value;
            btnText.textContent = currentType === 'short' ? 'تلخيص مختصر' : 'تلخيص مفصل';
        }
    }
}
