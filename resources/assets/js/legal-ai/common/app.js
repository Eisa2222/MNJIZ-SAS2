/**
 * common/app.js
 * ============================================================================
 * وحدة مشتركة لإدارة منطق المحادثات (لجميع أدوات AI).
 * تحتوي على دوال التفاعل مع الخادم (API) ومعالجات الأحداث المشتركة.
 * ============================================================================
 */
import * as ui from './ui.js';
import Swal from 'sweetalert2';



async function fetchAPI(url, options) {
    try {
        const response = await fetch(url, options);
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({ message: `Server error: ${response.statusText}` }));
            throw new Error(errorData.message || 'An unknown server error occurred.');
        }
        return await response.json();
    } catch (error) {
        throw error;
    }
}

async function createNewChatAPI(STATE) {
    return await fetchAPI(STATE.routes.create, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': STATE.csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    });
}

async function loadChatAPI(chatId, STATE) {
    const url = STATE.routes.loadChat.replace('__CHAT_ID__', chatId);
    return await fetchAPI(url, {
        method: 'GET',
        headers: { 'Accept': 'application/json' }
    });
}

async function deleteChatAPI(chatId, STATE) {
    const url = STATE.routes.delete.replace('__CHAT_ID__', chatId);
    return await fetchAPI(url, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': STATE.csrfToken, 'Accept': 'application/json' }
    });
}


// -----------------------------------------------------------------------------
// معالجات الأحداث (Event Handlers) - (عالية المستوى)
// -----------------------------------------------------------------------------

export async function handleCreateNew(STATE, toolName) {
    try {
        const data = await createNewChatAPI(STATE);
        if (data && data.chat) {
            ui.addChatToSidebar(data.chat, STATE);
            ui.updateChatInterface({ chat: data.chat, messages: [] }, STATE);

            const basePath = window.location.pathname.split('/').slice(0, 3).join('/');
            const newUrl = `${window.location.origin}${basePath}/${data.chat.id}`;

            window.history.pushState({ chatId: data.chat.id }, '', newUrl);

        } else {
            throw new Error('الخادم لم يُرجع بيانات المحادثة الجديدة.');
        }
    } catch (error) {
        Swal.fire('خطأ!', `فشل في إنشاء ${toolName} جديدة: ${error.message}`, 'error');
    }
}

export async function handleSwitchChat(chatId, STATE) {
    if (!chatId || String(chatId) === String(STATE.activeChatId)) {
        return;
    }

    ui.updateActiveChatInSidebar(chatId);
    ui.showLoadingIndicator();
    STATE.activeChatId = chatId;

    const basePath = window.location.pathname.split('/').slice(0, 3).join('/');
    const newUrl = `${window.location.origin}${basePath}/${chatId}`;
    window.history.pushState({ chatId: chatId }, '', newUrl);

    try {
        const chatData = await loadChatAPI(chatId, STATE);
        if (String(chatId) !== String(STATE.activeChatId)) {
            return;
        }
        ui.updateChatInterface(chatData, STATE);
    } catch (error) {
        ui.showErrorInChatWindow(`فشل تحميل المحادثة: ${error.message}`);
    }
}

export async function handleDelete(chatId, STATE, toolName) {
    const result = await Swal.fire({
        title: 'هل أنت متأكد؟',
        text: `سيتم حذف ${toolName} بشكل نهائي!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'نعم، احذفها!',
        cancelButtonText: 'إلغاء',
        customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-label-secondary' },
        buttonsStyling: false
    });

    if (result.isConfirmed) {
        try {
            await deleteChatAPI(chatId, STATE);

            if (typeof toastr !== 'undefined') {
                toastr.success(`تم حذف ${toolName} بنجاح.`);
            } else {
                Swal.fire('تم الحذف!', `تم حذف ${toolName} بنجاح.`, 'success');
            }

            if (String(chatId) === String(STATE.activeChatId)) {
                const toolUrl = window.location.pathname.split('/').slice(0, 3).join('/');
                window.location.href = toolUrl;
            } else {
                ui.removeChatFromSidebar(chatId);
            }
        } catch (error) {
            if (typeof toastr !== 'undefined') {
                toastr.error(`فشل الحذف: ${error.message}`);
            } else {
                Swal.fire('خطأ!', `فشل الحذف: ${error.message}`, 'error');
            }
        }
    }
}
