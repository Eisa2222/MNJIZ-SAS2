<div class="col app-chat-history">
    @if ($activeChat)
        <div class="chat-history-wrapper">
            <div class="chat-history-header border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex overflow-hidden align-items-center">
                        <i class="ti ti-menu-2 ti-lg cursor-pointer d-lg-none d-block me-4" data-bs-toggle="sidebar"
                            data-overlay data-target="#app-chat-contacts"></i>

                             <i id="toggle-sidebar-btn" class="ti ti-menu-2 ti-lg cursor-pointer me-4"></i>
                        <div class="flex-shrink-0 avatar">
                            <img src="{{ $aiAvatarPath }}" alt="AI"
                                class="rounded-circle">
                        </div>
                        <div class="chat-contact-info flex-grow-1 ms-2">
                            <h6 class="m-0 fw-normal" id="chat-header-title">{{ $activeChat->title }}</h6>
                        </div>
                    </div>
                </div>
            </div>
            <div class="chat-history-body">
                <ul class="list-unstyled chat-history m-0" id="messages-container">
                    @forelse ($messages as $message)
                        @include('legal-ai.common.partials.message', [
                            'message' => $message,
                            'authUser' => $authUser,
                        ])
                    @empty
                        @include('legal-ai.chat.partials.welcome-message')
                    @endforelse
                </ul>
            </div>
            <div class="chat-history-footer shadow-sm">
                <form id="ai-send-message-form"
                    class="form-send-message d-flex justify-content-between align-items-center">
                    @csrf
                    <textarea class="form-control message-input border-0 me-3 shadow-none" placeholder="اكتب رسالتك هنا..."
                        id="ai-message-input" name="message" rows="1"></textarea>
                    <div class="message-actions d-flex align-items-center">
                        <button type="submit" class="btn btn-primary d-flex send-msg-btn">
                            <span class="align-middle d-md-inline-block d-none">إرسال</span>
                            <i class="ti ti-send ti-16px ms-md-2 ms-0"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @else
        <div class="d-flex justify-content-center align-items-center h-100 flex-column text-center">
            <i class="ti ti-message-chatbot ti-4x mb-3 text-primary"></i>
            <h4>مرحباً بك في المساعد القانوني</h4>
            <p class="text-muted">اختر محادثة من القائمة أو ابدأ واحدة جديدة.</p>
        </div>
    @endif

    <template id="welcome-message-template">
        @include('legal-ai.chat.partials.welcome-message')
    </template>
</div>
