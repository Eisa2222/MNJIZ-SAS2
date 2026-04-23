<div class="col app-chat-history bg-body">
    @if ($activeChat)
        <div class="chat-history-wrapper">
            <div class="chat-history-header border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex overflow-hidden align-items-center">
                        <i class="ti ti-menu-2 ti-lg cursor-pointer d-lg-none d-block me-4" data-bs-toggle="sidebar"
                            data-overlay data-target="#app-chat-contacts"></i>
                        <i id="toggle-sidebar-btn" class="ti ti-menu-2 ti-lg cursor-pointer me-4"></i>
                        <div class="flex-shrink-0 avatar">
                            <img src="{{ $aiAvatarPath }}" alt="AI" class="rounded-circle">
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
                            'aiAvatarPath' => $aiAvatarPath,
                        ])
                    @empty
                        @include('legal-ai.summarize.partials.welcome-message')
                    @endforelse
                </ul>
            </div>

            <div class="chat-history-footer shadow-sm">
                <form id="summarize-form" class="form-send-message p-3" onsubmit="return false;">
                    @csrf
                    <label for="document-input" id="drop-zone" class="d-flex align-items-center p-2 drop-zone"
                        style="cursor: pointer;">
                        <div class="btn btn-icon btn-label-primary me-2 flex-shrink-0">
                            <i class="ti ti-paperclip ti-18px"></i>
                        </div>
                        <div class="flex-grow-1 me-2 position-relative text-start">
                            <p id="file-name" class="mb-0 text-muted text-truncate">اسحب ملفًا إلى هنا، أو انقر
                                للإرفاق...</p>
                        </div>
                        <div class="btn-group" id="submit-btn-wrapper">
                            <button type="submit" id="submit-btn" class="btn btn-primary d-flex send-msg-btn" disabled>
                                <span id="submit-btn-text" class="align-middle">تلخيص</span>
                                <span class="spinner-border spinner-border-sm d-none ms-2" role="status"
                                    aria-hidden="true"></span>
                            </button>
                            <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split"
                                data-bs-toggle="dropdown" aria-expanded="false" disabled>
                                <span class="visually-hidden">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item summary-option active" href="#"
                                        data-value="detailed">تلخيص مفصل</a></li>
                                <li><a class="dropdown-item summary-option" href="#" data-value="short">تلخيص
                                        مختصر</a></li>
                            </ul>
                        </div>
                    </label>

                    <input type="file" id="document-input" name="document" class="d-none"
                        accept=".pdf,.doc,.docx,.txt">
                    <input type="hidden" name="summary_type" id="summary_type_input" value="detailed">
                </form>
            </div>
        </div>
    @else
        <div class="d-flex justify-content-center align-items-center h-100 flex-column text-center">
            <i class="ti ti-folders ti-4x mb-3 text-primary"></i>
            <h4>لا توجد جلسة تلخيص نشطة</h4>
            <p class="text-muted">اختر جلسة من القائمة أو ابدأ واحدة جديدة.</p>
        </div>
    @endif

    <template id="welcome-message-template">
        @include('legal-ai.summarize.partials.welcome-message')
    </template>
</div>
