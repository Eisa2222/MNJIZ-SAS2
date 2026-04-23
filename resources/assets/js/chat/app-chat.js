/**
 * App Chat - Enhanced Version with Real-time Read Status
 */

'use strict';

document.addEventListener('DOMContentLoaded', function () {
    (function () {
        const chatContactsBody = document.querySelector('.app-chat-contacts .sidebar-body'),
            chatContactListItems = [].slice.call(
                document.querySelectorAll('.chat-contact-list-item:not(.chat-contact-list-item-title)')
            ),
            chatHistoryBody = document.querySelector('.chat-history-body'),
            chatSidebarLeftBody = document.querySelector('.app-chat-sidebar-left .sidebar-body'),
            formSendMessage = document.querySelector('.form-send-message'),
            messageInput = document.querySelector('.message-input'),
            attachmentInput = document.querySelector('.attachment-input'),
            attachmentPreview = document.querySelector('.attachment-preview'),
            searchInput = document.querySelector('.chat-search-input');

        // إعداد CSRF Token
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Initialize PerfectScrollbar
        if (chatContactsBody) {
            new PerfectScrollbar(chatContactsBody, {
                wheelPropagation: false,
                suppressScrollX: true
            });
        }

        if (chatHistoryBody) {
            new PerfectScrollbar(chatHistoryBody, {
                wheelPropagation: false,
                suppressScrollX: true
            });
        }

        if (chatSidebarLeftBody) {
            new PerfectScrollbar(chatSidebarLeftBody, {
                wheelPropagation: false,
                suppressScrollX: true
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Auto-resize Textarea
        |--------------------------------------------------------------------------
        | Auto-resize message input based on content using Bootstrap approach.
        */

        if (messageInput) {
            messageInput.addEventListener('input', function () {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 120) + 'px';
            });

            // Reset height when form is submitted
            formSendMessage.addEventListener('submit', function () {
                setTimeout(() => {
                    messageInput.style.height = 'auto';
                }, 100);
            });
        }

        function scrollToBottom() {
            chatHistoryBody.scrollTop = chatHistoryBody.scrollHeight;
        }

        /*
        |--------------------------------------------------------------------------
        | Subscribe To Chat Channel
        |--------------------------------------------------------------------------
        | Subscribe to real-time chat channel.
        */

        function subscribeToChatChannel() {
            let senderId = parseInt(window.authId);
            let receiverId = parseInt($('#receiver_id').val());
            let ids = [senderId, receiverId];
            ids.sort();
            let channelName = 'chat.' + ids[0] + '.' + ids[1];

            if (window.currentChatChannel) {
                window.Echo.leave('private-' + window.currentChatChannel);
            }

            window.currentChatChannel = channelName;

            if (typeof window.Echo === 'undefined') {
                console.error('window.Echo is undefined');
                return;
            }

            window.Echo.private(channelName)
                .listen('.App\\Events\\Chat\\MessageSent', (e) => {
                    let currentChatUserId = $('#receiver_id').val();
                    if (e.sender_id == currentChatUserId || e.sender_id == window.authId) {
                        // تحديث الرسالة المؤقتة إذا كانت موجودة
                        updateTemporaryMessage(e);
                        scrollToBottom();
                    }
                });
        }

        /*
        |--------------------------------------------------------------------------
        | Generate Temporary Message ID
        |--------------------------------------------------------------------------
        | Generate unique ID for temporary messages.
        */

        function generateTempId() {
            return 'temp_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        }

        /*
        |--------------------------------------------------------------------------
        | Update Temporary Message
        |--------------------------------------------------------------------------
        | Replace temporary message with real message data.
        */

        function updateTemporaryMessage(messageData) {
            let tempMessages = document.querySelectorAll('[data-message-id^="temp_"]');
            let lastTempMessage = tempMessages[tempMessages.length - 1];

            if (lastTempMessage && messageData.sender_id == window.authId) {
                lastTempMessage.setAttribute('data-message-id', messageData.id);
                let statusIcon = lastTempMessage.querySelector('.message-status');
                if (statusIcon) {
                    statusIcon.className = 'ti ti-check text-muted ms-1 message-status';
                    statusIcon.title = 'تم الإرسال';
                }

                lastTempMessage.classList.remove('message-sending');
            } else if (messageData.sender_id != window.authId) {
                if (messageData.sender && messageData.sender.image) {
                    messageData.sender.image = '/storage/' + messageData.sender.image;
                }

                appendMessage(messageData);
                markPreviousMessagesAsRead();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Mark Previous Messages As Read
        |--------------------------------------------------------------------------
        | Update previous sent messages to read status when receiving reply.
        */

        function markPreviousMessagesAsRead() {
            // تحديث جميع الرسائل المرسلة التي لم تُقرأ بعد
            let unreadMessages = document.querySelectorAll('.chat-message-right .message-status.ti-check:not(.ti-checks)');
            unreadMessages.forEach(function (statusIcon) {
                statusIcon.className = 'ti ti-checks text-primary ms-1 message-status';
                statusIcon.title = 'تم القراءة';
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Append Message
        |--------------------------------------------------------------------------
        | Add new message to chat history with improved styling.
        */
        function appendMessage(messageData, isTemporary = false) {
            const senderImage = (messageData.sender && messageData.sender.image)
                ? messageData.sender.image
                : '/assets/img/branding/Alburhan-Logo.png';

            let attachmentHtml = '';
            if (messageData.has_attachment) {
                if (messageData.sender_id == window.authId) {
                    attachmentHtml = `<div class="attachment-wrapper mt-2"> ... </div>`;
                } else {
                    attachmentHtml = `<div class="attachment-wrapper mt-2"> ... </div>`;
                }
            }

            let messageText = messageData.message ? `<p class="mb-0">${messageData.message}</p>` : '';

            let statusIcon = isTemporary
                ? '<div class="spinner-border spinner-border-sm text-white-50 ms-1 message-status" style="width: 0.8rem; height: 0.8rem;" role="status"></div>'
                : '<i class="ti ti-check text-muted ms-1 message-status" title="تم الإرسال"></i>';

            let messageItem = '';
            if (messageData.sender_id == window.authId) {
                messageItem = `
            <li class="chat-message chat-message-right ${isTemporary ? 'message-sending' : ''}" data-message-id="${messageData.id}">
                <div class="d-flex overflow-hidden">
                    <div class="chat-message-wrapper flex-grow-1">
                        <div class="chat-message-text">${messageText}${attachmentHtml}</div>
                        <div class="text-end text-muted mt-1">
                            <small>${messageData.created_at}</small>
                            ${statusIcon}
                        </div>
                    </div>
                    <div class="user-avatar flex-shrink-0 ms-4">
                        <div class="avatar avatar-sm">
                            <img src="${senderImage}" alt="الصورة الرمزية" class="rounded-circle">
                        </div>
                    </div>
                </div>
            </li>
        `;
            } else {
                messageItem = `
            <li class="chat-message" data-message-id="${messageData.id}">
                <div class="d-flex overflow-hidden">
                    <div class="user-avatar flex-shrink-0 me-4">
                        <div class="avatar avatar-sm">
                            <img src="${senderImage}" alt="الصورة الرمزية" class="rounded-circle">
                        </div>
                    </div>
                    <div class="chat-message-wrapper flex-grow-1">
                        <div class="chat-message-text">${messageText}${attachmentHtml}</div>
                        <div class="text-muted mt-1">
                            <small>${messageData.created_at}</small>
                        </div>
                    </div>
                </div>
            </li>
        `;
            }

            $('#messages').append(messageItem);
        }

        /*
        |--------------------------------------------------------------------------
        | Handle Attachment Preview
        |--------------------------------------------------------------------------
        | Show/hide attachment preview with enhanced design.
        */

        if (attachmentInput) {
            attachmentInput.addEventListener('change', function (e) {
                const file = e.target.files[0];
                if (file) {
                    const fileName = file.name;
                    const fileSize = (file.size / 1024 / 1024).toFixed(2);
                    attachmentPreview.innerHTML = `
                        <div class="d-flex align-items-center p-2 border rounded mx-auto" style="max-width: 350px;">
                            <div class="me-3 ms-3">
                                <i class="ti ti-file text-primary fs-4"></i>
                            </div>
                            <div class="flex-grow-1 text-truncate" style="min-width:0;">
                                <div class="fw-medium text-dark small text-truncate" style="font-size: 0.92rem;">${fileName}</div>
                                <small class="text-muted">${fileSize} KB</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger ms-2">
                                <i class="ti ti-x"></i>
                            </button>
                        </div>
                    `;
                    attachmentPreview.style.display = 'block';

                    // Remove attachment
                    attachmentPreview.querySelector('.btn').addEventListener('click', function () {
                        attachmentInput.value = '';
                        attachmentPreview.style.display = 'none';
                        attachmentPreview.innerHTML = '';
                    });
                } else {
                    attachmentPreview.style.display = 'none';
                }
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Select User
        |--------------------------------------------------------------------------
        | Handle user selection from contacts list.
        */

        $(document).on('click', '.chat-contact-list-item.user', function () {
            $('.chat-contact-list-item').removeClass('active');
            $(this).addClass('active');

            let user_id = $(this).data('id');
            $('#receiver_id').val(user_id);

            let receiverName = $(this).find('.chat-contact-name').text();
            let receiverJob = $(this).find('.chat-contact-status').text();
            let receiverImage = $(this).find('img').attr('src');

            $('#receiver-name').text(receiverName);
            $('#receiver-job').text(receiverJob);
            $('#receiver-image').attr('src', receiverImage);

            $.ajax({
                type: "get",
                url: window.compileRoute(window.namedRoutes.chatMessagesGet, { user_id: String(user_id) }),
                cache: false,
                success: function (data) {
                    $('#messages').html(data);
                    scrollToBottom();
                    subscribeToChatChannel();

                    setTimeout(function () {
                        updateReadStatusOnLoad();
                    }, 300);
                },
                error: function (jqXHR, status, err) {
                    // Handle error appropriately, maybe show a toastr notification
                }
            });
        });

        /*
        |--------------------------------------------------------------------------
        | Update Read Status On Load
        |--------------------------------------------------------------------------
        | Update read status when switching between conversations.
        */

        function updateReadStatusOnLoad() {
            // البحث عن آخر رسالة من المستقبل في المحادثة
            let lastReceivedMessage = $('.chat-message:not(.chat-message-right)').last();

            if (lastReceivedMessage.length > 0) {
                // إذا كان هناك رد من المستقبل، تحديث الرسائل المرسلة إلى مقروءة
                markPreviousMessagesAsRead();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Auto Select First User
        |--------------------------------------------------------------------------
        | Select first user on page load.
        */

        // $(document).ready(function () {
        //     let firstUser = $('.chat-contact-list-item.user').first();
        //     if (firstUser.length > 0) {
        //         firstUser.trigger('click');
        //     }
        // });

        $(document).ready(function () {
            setTimeout(function () {
                if (typeof window.selectedUserId !== 'undefined' &&
                    window.selectedUserId &&
                    window.selectedUserId !== null &&
                    window.selectedUserId !== 'null') {
                    let targetUser = $(`.chat-contact-list-item.user[data-id="${window.selectedUserId}"]`);

                    if (targetUser.length > 0) {
                        targetUser[0].scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });

                        setTimeout(() => {
                            targetUser.trigger('click');
                            targetUser.addClass('pre-selected');
                            setTimeout(() => {
                                targetUser.removeClass('pre-selected');
                            }, 3000);
                        }, 500);

                        return;
                    }
                }

                let firstUser = $('.chat-contact-list-item.user').first();
                if (firstUser.length > 0) {
                    firstUser.trigger('click');
                }
            }, 200);
        });

        /*
        |--------------------------------------------------------------------------
        | Send Message - Enhanced with Instant UI Update
        |--------------------------------------------------------------------------
        | Handle message sending with instant UI feedback.
        */
        formSendMessage.addEventListener('submit', e => {
            e.preventDefault();

            let message = messageInput.value.trim();
            let receiver_id = $('#receiver_id').val();
            let attachment = attachmentInput ? attachmentInput.files[0] : null;

            // 1. Validation: Ensure message or attachment exists
            if (!message && !attachment) {
                if (typeof toastr !== 'undefined') {
                    toastr.error('يجب كتابة رسالة أو إرفاق ملف');
                } else {
                    alert('يجب كتابة رسالة أو إرفاق ملف');
                }

                return;
            }

            // 2. Validation: Ensure a receiver is selected
            if (!receiver_id) {
                if (typeof toastr !== 'undefined') {
                    toastr.error('يرجى اختيار مستقبل للرسالة');
                } else {
                    alert('يرجى اختيار مستقبل للرسالة');
                }

                return;
            }

            // 3. ✅ The Final Fix: Create the temporary message object using reliable data
            //    We now use `window.authUserImage` which was set by Blade on page load.
            let tempMessageData = {
                id: generateTempId(),
                message: message,
                sender_id: window.authId,
                receiver_id: receiver_id,
                created_at: new Date().toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                }),
                has_attachment: !!attachment,
                attachment_name: attachment ? attachment.name : null,
                attachment_url: attachment ? URL.createObjectURL(attachment) : null,
                sender: {
                    id: window.authId,
                    name: 'أنت',
                    // The definitive fix: We no longer search the DOM. We use the correct
                    // image path stored in the global `window.authUserImage` variable.
                    image: window.authUserImage
                }
            };

            // 4. Instantly display the temporary message on the screen
            appendMessage(tempMessageData, true);
            scrollToBottom();

            // 5. Instantly clear the form
            messageInput.value = '';
            messageInput.style.height = 'auto';
            if (attachmentInput) {
                attachmentInput.value = '';
                attachmentPreview.style.display = 'none';
                attachmentPreview.innerHTML = '';
            }

            // 6. Handle the AJAX request to send the actual message to the server
            let sendButton = formSendMessage.querySelector('.send-msg-btn');
            let originalContent = sendButton.innerHTML;

            sendButton.disabled = true;
            sendButton.innerHTML = '<i class="ti ti-loader-2 ti-16px animate-spin"></i>';

            let formData = new FormData();
            formData.append('receiver_id', receiver_id);
            if (message) formData.append('message', message);
            if (attachment) formData.append('attachment', attachment);

            $.ajax({
                type: "post",
                url: window.namedRoutes.chatMessagesSend,
                data: formData,
                processData: false,
                contentType: false,
                cache: false,
                success: function (response) {
                    // سيتم تحديث الرسالة الحقيقية عبر Laravel Echo
                },
                error: function (jqXHR, status, err) {
                    console.error(err);

                    $(`[data-message-id="${tempMessageData.id}"]`).remove();

                    let errorMessage = 'حدث خطأ في إرسال الرسالة';
                    if (jqXHR.responseJSON && jqXHR.responseJSON.errors) {
                        errorMessage = Object.values(jqXHR.responseJSON.errors).flat().join('<br>');
                    } else if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                        errorMessage = jqXHR.responseJSON.message;
                    }

                    if (typeof toastr !== 'undefined') {
                        toastr.error(errorMessage);
                    } else {
                        alert(errorMessage);
                    }
                },
                complete: function () {
                    // إعادة تفعيل زر الإرسال
                    sendButton.disabled = false;
                    sendButton.innerHTML = originalContent;
                    messageInput.focus();
                }
            });
        });

        /*
        |--------------------------------------------------------------------------
        | Search Functionality
        |--------------------------------------------------------------------------
        | Filter chat contacts.
        */

        if (searchInput) {
            searchInput.addEventListener('keyup', e => {
                let searchValue = e.currentTarget.value.toLowerCase(),
                    searchChatListItemsCount = 0,
                    chatListItem0 = document.querySelector('.chat-list-item-0'),
                    searchChatListItems = [].slice.call(
                        document.querySelectorAll('#chat-list li:not(.chat-contact-list-item-title)')
                    );

                searchChatContacts(searchChatListItems, searchChatListItemsCount, searchValue, chatListItem0);
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Search Chat Contacts
        |--------------------------------------------------------------------------
        | Search functionality helper.
        */

        function searchChatContacts(searchListItems, searchListItemsCount, searchValue, listItem0) {
            searchListItems.forEach(searchListItem => {
                let searchListItemText = searchListItem.textContent.toLowerCase();
                if (searchValue) {
                    if (-1 < searchListItemText.indexOf(searchValue)) {
                        searchListItem.classList.add('d-flex');
                        searchListItem.classList.remove('d-none');
                        searchListItemsCount++;
                    } else {
                        searchListItem.classList.add('d-none');
                    }
                } else {
                    searchListItem.classList.add('d-flex');
                    searchListItem.classList.remove('d-none');
                    searchListItemsCount++;
                }
            });

            if (listItem0) {
                if (searchListItemsCount == 0) {
                    listItem0.classList.remove('d-none');
                } else {
                    listItem0.classList.add('d-none');
                }
            }
        }
    })();
});
