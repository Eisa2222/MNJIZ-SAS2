// resources/js/ai_chat_dashboard.js

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

document.addEventListener('DOMContentLoaded', function () {
    // إعداد Pusher و Laravel Echo
    window.Pusher = Pusher;

    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: import.meta.env.VITE_PUSHER_APP_KEY,
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
        forceTLS: true
    });

    const conversationsList = document.getElementById('chat-list');
    const createConversationBtn = document.getElementById('create-new-conversation');

    let activeConversationId = window.activeConversationId || null;
    let currentEchoChannel = null;
    const userAvatar = window.userAvatar || '/assets/img/branding/Alburhan-Logo.png';
    const authId = window.authId || null;

    let currentPlaceholderId = null;

    // تعريف المتغيرات والمستمعات في المستوى الأعلى
    const sendMessageForm = document.getElementById('ai-send-message-form');
    const messageInput = document.getElementById('ai-message-input');
    const aiMessagesContainer = document.getElementById('messages');

    const fileInput = document.getElementById('ai-message-file');
    const fileLabel = document.getElementById('selected-file-name');

    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function (m) { return map[m]; });
    }

    function formatTime(date) {
        const options = { hour: '2-digit', minute: '2-digit' };
        return new Date(date).toLocaleTimeString([], options);
    }

    function scrollToBottom() {
        const aiChatHistory = document.querySelector('.chat-history-body');
        if (aiChatHistory) {
            aiChatHistory.scrollTop = aiChatHistory.scrollHeight;
        } else {
            console.warn('chat-history element not found');
        }
    }

    function displayTextGradually(element, text) {
        let index = 0;
        const interval = setInterval(() => {
            if (index < text.length) {
                element.textContent += text[index];
                index++;
                scrollToBottom(); // Ensure scrolling while typing
            } else {
                clearInterval(interval);
            }
        }, 50);
    }

    // Function to fetch with timeout
    function fetchWithTimeout(resource, options = {}) {
        const { timeout = 120000 } = options; // Set timeout to 120 seconds
        return new Promise((resolve, reject) => {
            const timer = setTimeout(() => {
                reject(new Error('Timeout'));
            }, timeout);

            fetch(resource, options)
                .then(response => {
                    clearTimeout(timer);
                    resolve(response);
                })
                .catch(err => {
                    clearTimeout(timer);
                    reject(err);
                });
        });
    }

    // Create a new conversation via AJAX
    if (createConversationBtn) {
        createConversationBtn.addEventListener('click', function () {
            // Create a new conversation via AJAX
            fetch('/ai-chat/create', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json' // Ensure content-type is set
                },
                body: JSON.stringify({}) // No data to send
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'Conversation Created!' && data.conversation) {
                        const newConversation = data.conversation;
                        // Add the new conversation to the sidebar
                        const conversationItem = document.createElement('li');
                        conversationItem.classList.add('chat-contact-list-item', 'mb-1', 'conversation-item', 'active', 'position-relative');
                        conversationItem.setAttribute('data-id', newConversation.id);
                        conversationItem.innerHTML = `
                            <a href="javascript:void(0)" class="d-flex align-items-center">
                                <div class="flex-shrink-0 avatar avatar-online">
                                    <img src="${userAvatar}" alt="الصورة الرمزية" class="rounded-circle">
                                </div>
                                <div class="chat-contact-info flex-grow-1 me-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="chat-contact-name text-truncate m-0 fw-normal">محادثة جديدة</h6>
                                        <small class="text-muted">الآن</small>
                                    </div>
                                    <small class="chat-contact-status text-truncate">محادثة AI</small>
                                </div>
                            </a>
                            <button class="btn btn-sm delete-conversation" data-id="${newConversation.id}" title="حذف المحادثة">
                                <i class="fas fa-trash" style="color: #eff1f1 !important;"></i>
                            </button>
                        `;
                        // Remove 'active' class from all and set new one
                        document.querySelectorAll('.conversation-item').forEach(item => item.classList.remove('active'));
                        conversationsList.prepend(conversationItem);
                        // Load the new conversation
                        loadConversation(newConversation.id);
                    } else {
                        console.error('Unexpected response:', data);
                        Swal.fire('خطأ', 'حدث خطأ غير متوقع أثناء إنشاء المحادثة.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('خطأ', 'حدث خطأ أثناء إنشاء المحادثة الجديدة.', 'error');
                });
        });
    }

    // Handle clicking on conversation items
    if (conversationsList) {
        conversationsList.addEventListener('click', function (e) {
            const conversationItem = e.target.closest('.conversation-item');
            if (!conversationItem) return;

            const conversationId = conversationItem.getAttribute('data-id');
            if (conversationId === activeConversationId) return; // Do nothing if already active

            document.querySelectorAll('.conversation-item').forEach(item => item.classList.remove('active'));
            conversationItem.classList.add('active');
            loadConversation(conversationId);
        });
    }

    function loadConversation(conversationId) {
        fetch(`/ai-chat?conversation_id=${conversationId}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
            .then(response => {
                if (response.ok) {
                    return response.text();
                }
                throw new Error('Network response was not ok.');
            })
            .then(html => {
                const messagesContainer = document.getElementById('messages');
                if (messagesContainer) {
                    messagesContainer.innerHTML = html;
                    initializeChat(conversationId);
                } else {
                    console.error('Messages container not found.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('خطأ', 'حدث خطأ أثناء تحميل المحادثة.', 'error');
            });
    }

    function initializeChat(conversationId) {
        // Store the old conversation ID before updating
        const oldConversationId = activeConversationId;

        // Update activeConversationId to the new one
        activeConversationId = conversationId;

        if (currentEchoChannel) {
            currentEchoChannel.stopListening('.AIMessageSent');
            window.Echo.leave(`ai-chat.${oldConversationId}`);
        }

        currentEchoChannel = window.Echo.private(`ai-chat.${activeConversationId}`)
            .listen('.AIMessageSent', (e) => {
                if (e.message.sender === 'ai') {
                    if (currentPlaceholderId) {
                        const placeholder = document.getElementById(currentPlaceholderId);
                        if (placeholder) {
                            const messageElement = placeholder.querySelector('.chat-message-text p');
                            if (messageElement) {
                                const spinner = messageElement.querySelector('.spinner-border');
                                if (spinner) {
                                    spinner.remove();
                                }
                                displayTextGradually(messageElement, e.message.message);
                            }
                            const timestampElement = placeholder.querySelector('.text-muted small');
                            if (timestampElement) {
                                timestampElement.textContent = formatTime(e.message.created_at);
                            }
                            currentPlaceholderId = null;

                            // Add copy button
                            addCopyButtonToMessage(placeholder, `message-${e.message.id}`);
                        }
                    } else {
                        appendMessage('ai', e.message.message, e.message.created_at, false, `message-${e.message.id}`);
                    }
                }
                scrollToBottom();
            });

        // لا حاجة لإضافة المستمعات هنا لأنها أضيفت مرة واحدة
    }

    // مستمع لتغيير الملف المرفق
    if (fileInput) {
        fileInput.addEventListener('change', function () {
            if (fileInput.files && fileInput.files.length > 0) {
                const fileName = fileInput.files[0].name;
                fileLabel.textContent = fileName;
                fileLabel.style.display = 'inline'; // Show label
            } else {
                fileLabel.textContent = '';
                fileLabel.style.display = 'none'; // Hide label
            }
        });
    }

    // إضافة مستمع إرسال الرسالة مرة واحدة
    if (sendMessageForm && messageInput && aiMessagesContainer) {
        sendMessageForm.addEventListener('submit', sendMessageHandler);
    } else {
        console.warn('One or more chat elements are missing.');
    }

    function sendMessageHandler(e) {
        e.preventDefault();

        const message = messageInput.value.trim();
        const file = fileInput.files[0];

        if (!message && !file) return;

        sendMessageForm.querySelector('button[type="submit"]').disabled = true;

        const tempId = `message-${Date.now()}`;

        appendMessage('user', message || (file ? 'تم إرفاق ملف.' : ''), new Date(), false, tempId, file ? true : false);

        currentPlaceholderId = `ai-placeholder-${Date.now()}`;
        appendMessage('ai', '', new Date(), true, currentPlaceholderId);

        const formData = new FormData();
        formData.append('message', message);
        if (file) {
            formData.append('file', file);
        }

        fetchWithTimeout(`/ai-chat/${activeConversationId}/send`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            },
            body: formData,
            timeout: 120000 // 2 minutes
        })
            .then(response => {
                return response.json().then(data => {
                    if (!response.ok) {
                        throw data;
                    }
                    return data;
                });
            })
            .then(data => {
                if (data.status === 'Message Sent!') {
                    updateUserMessage(data.user_message, tempId);
                    if (data.conversation && data.conversation.title) {
                        // Update conversation title in sidebar
                        const conversationItem = document.querySelector(`.conversation-item[data-id="${data.conversation.id}"]`);
                        if (conversationItem) {
                            const nameElement = conversationItem.querySelector('.chat-contact-name');
                            if (nameElement) {
                                nameElement.textContent = escapeHtml(data.conversation.title);
                            }
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (error.error) {
                    appendErrorMessage(error.error);
                } else {
                    appendErrorMessage('حدث خطأ أثناء إرسال الرسالة.');
                }

                // Remove AI placeholder
                if (currentPlaceholderId) {
                    const placeholder = document.getElementById(currentPlaceholderId);
                    if (placeholder) {
                        placeholder.remove();
                        currentPlaceholderId = null;
                    }
                }
            })
            .finally(() => {
                sendMessageForm.querySelector('button[type="submit"]').disabled = false;
                fileInput.value = '';
                messageInput.value = '';
                fileLabel.textContent = '';
                fileLabel.style.display = 'none';
            });
    }

    function updateUserMessage(userMessage, tempId) {
        const messageElement = document.getElementById(tempId);
        if (messageElement) {
            const messageTextElement = messageElement.querySelector('.chat-message-text');
            if (messageTextElement) {
                messageTextElement.innerHTML = `<p class="mb-0">${escapeHtml(userMessage.message)}</p>`;
                if (userMessage.file_url) {
                    const fileLink = document.createElement('p');
                    fileLink.classList.add('mb-0');
                    fileLink.innerHTML = `<a href="${userMessage.file_url}" target="_blank" class="" style="#0fffff">
                        <i class="fas fa-paperclip"></i> ${userMessage.file_name}
                    </a>`;
                    messageTextElement.appendChild(fileLink);
                }
            }
        }
    }

    function appendMessage(sender, message, timestamp, isPlaceholder = false, uniqueId = null, hasFile = false) {
        const aiMessagesContainer = document.getElementById('messages');

        if (uniqueId && document.getElementById(uniqueId)) return;

        uniqueId = uniqueId || `message-${Date.now()}`;

        let messageItem = '';

        if (sender === 'user') {
            messageItem = `
                <li class="chat-message chat-message-right" id="${uniqueId}">
                    <div class="d-flex overflow-hidden">
                        <div class="chat-message-wrapper flex-grow-1">
                            <div class="chat-message-text">
                                <p class="mb-0">${escapeHtml(message)}</p>
                                ${hasFile ? `
                                <p class="mb-0">
                                    <i class="fas fa-paperclip"></i> <span class="text-muted">تم إرفاق ملف</span>
                                </p>` : ''}
                            </div>
                            <div class="text-end text-muted mt-1">
                                <small>${formatTime(timestamp)}</small>
                            </div>
                        </div>
                        <div class="user-avatar flex-shrink-0 ms-4">
                            <div class="avatar avatar-sm">
                                <img src="${userAvatar}" alt="الصورة الرمزية" class="rounded-circle">
                            </div>
                        </div>
                    </div>
                </li>
            `;
        } else if (sender === 'ai') {
            if (isPlaceholder) {
                messageItem = `
                    <li class="chat-message loading-ai-message" id="${uniqueId}">
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
                                    <small>${formatTime(timestamp)}</small>
                                </div>
                            </div>
                        </div>
                    </li>
                `;
            } else {
                messageItem = `
                    <li class="chat-message" id="${uniqueId}">
                        <div class="d-flex overflow-hidden">
                            <div class="user-avatar flex-shrink-0 me-4">
                                <div class="avatar avatar-sm">
                                    <img src="/assets/img/branding/Alburhan-Logo.png" alt="صورة AI"
                                        class="rounded-circle">
                                </div>
                            </div>
                            <div class="chat-message-wrapper flex-grow-1">
                                <div class="chat-message-text position-relative">
                                    <button class="btn btn-link copy-ai-message pt-2" data-message-id="${uniqueId}" title="نسخ">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    <p class="mb-0 me-4">${escapeHtml(message)}</p>
                                </div>
                                <div class="text-muted mt-1">
                                    <small>${formatTime(timestamp)}</small>
                                </div>
                            </div>
                        </div>
                    </li>
                `;
            }
        }

        aiMessagesContainer.insertAdjacentHTML('beforeend', messageItem);

        if (sender === 'ai' && !isPlaceholder) {
            const messageElement = document.getElementById(uniqueId);
            addCopyButtonToMessage(messageElement, uniqueId);
        }

        scrollToBottom();
    }

    function appendErrorMessage(errorMessage) {
        const aiMessagesContainer = document.getElementById('messages');
        const timestamp = new Date();
        const uniqueId = `error-${Date.now()}`;

        const messageItem = `
            <li class="chat-message" id="${uniqueId}">
                <div class="d-flex overflow-hidden">
                    <div class="user-avatar flex-shrink-0 me-4">
                        <div class="avatar avatar-sm">
                            <img src="/assets/img/branding/Alburhan-Logo.png" alt="صورة AI" class="rounded-circle">
                        </div>
                    </div>
                    <div class="chat-message-wrapper flex-grow-1">
                        <div class="chat-message-text">
                            <p class="mb-0 text-danger">${escapeHtml(errorMessage)}</p>
                        </div>
                        <div class="text-muted mt-1">
                            <small>${formatTime(timestamp)}</small>
                        </div>
                    </div>
                </div>
            </li>
        `;

        aiMessagesContainer.insertAdjacentHTML('beforeend', messageItem);
        scrollToBottom();
    }

    // وظيفة لإضافة زر النسخ إلى رسالة الذكاء الاصطناعي
    function addCopyButtonToMessage(messageElement, messageId) {
        const messageTextElement = messageElement.querySelector('.chat-message-text');
        if (messageTextElement) {
            const copyButton = document.createElement('button');
            copyButton.classList.add('btn', 'btn-link', 'copy-ai-message');
            copyButton.setAttribute('data-message-id', messageId);
            copyButton.setAttribute('title', 'نسخ');
            copyButton.innerHTML = '<i class="fas fa-copy"></i>';
            messageTextElement.insertBefore(copyButton, messageTextElement.firstChild);
        }
    }

    // مستمع لزر النسخ
    document.addEventListener('click', function (e) {
        if (e.target && (e.target.classList.contains('copy-ai-message') || e.target.closest('.copy-ai-message'))) {
            const button = e.target.closest('.copy-ai-message');
            const messageId = button.getAttribute('data-message-id');
            const messageElement = document.querySelector(`#${messageId} .chat-message-text p`);
            if (messageElement) {
                const text = messageElement.textContent;
                navigator.clipboard.writeText(text).then(() => {
                    showCopyToast();
                }).catch(err => {
                    console.error('Error copying text: ', err);
                });
            }
        }
    });

    // وظيفة لعرض Toast
    function showCopyToast() {
        const copyToastElement = document.getElementById('copyToast');
        const copyToast = new bootstrap.Toast(copyToastElement);
        copyToast.show();
    }

    // مستمع لزر حذف المحادثة
    document.addEventListener('click', function (e) {
        if (e.target && (e.target.classList.contains('delete-conversation') || e.target.closest('.delete-conversation'))) {
            const button = e.target.closest('.delete-conversation');
            const conversationId = button.getAttribute('data-id');

            Swal.fire({
                title: 'تأكيد الحذف',
                text: 'هل أنت متأكد من حذف هذه المحادثة؟',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'نعم، احذفها',
                cancelButtonText: 'إلغاء',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/ai-chat/${conversationId}/delete`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                        },
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'Conversation Deleted!') {
                                // إزالة المحادثة من القائمة
                                const conversationItem = document.querySelector(`.conversation-item[data-id="${conversationId}"]`);
                                if (conversationItem) {
                                    conversationItem.remove();
                                }
                                // إعادة تحميل المحادثة الأولى أو إظهار رسالة
                                const firstConversation = document.querySelector('.conversation-item');
                                if (firstConversation) {
                                    firstConversation.classList.add('active');
                                    loadConversation(firstConversation.getAttribute('data-id'));
                                } else {
                                    // لا توجد محادثات متبقية
                                    const chatHistoryWrapper = document.querySelector('.app-chat-history .chat-history-wrapper');
                                    if (chatHistoryWrapper) {
                                        chatHistoryWrapper.innerHTML = `
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
                                        `;
                                    }
                                }
                                // عرض رسالة النجاح باستخدام SweetAlert
                                Swal.fire(
                                    'تم الحذف!',
                                    'تم حذف المحادثة بنجاح.',
                                    'success'
                                );
                            } else {
                                Swal.fire('خطأ', 'حدث خطأ أثناء حذف المحادثة.', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire('خطأ', 'حدث خطأ أثناء حذف المحادثة.', 'error');
                        });
                }
            });
        }
    });

    if (activeConversationId) {
        initializeChat(activeConversationId);
    }
});
