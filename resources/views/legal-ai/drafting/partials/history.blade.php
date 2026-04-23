<div class="col app-chat-history bg-body">
    @if ($activeChat)
        <div class="chat-history-wrapper">
            <div class="chat-history-header border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex overflow-hidden align-items-center">
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
                        @include('legal-ai.drafting.partials.welcome-message')
                    @endforelse
                </ul>
            </div>

            <div class="chat-history-footer shadow-sm">
                <form id="drafting-form" class="form-send-message p-3" onsubmit="return false;">
                    @csrf

                    <div id="drafting-options-container" class="mb-3">
                        <div class="btn-group btn-group-sm w-100" role="group">
                            @isset($draftingTypes)
                                @foreach ($draftingTypes as $index => $type)
                                    @php $typeId = 'type_' . $type->value; @endphp

                                    <input type="radio" class="btn-check" name="drafting_type" id="{{ $typeId }}"
                                        value="{{ $type->value }}" {{ $index === 0 ? 'checked' : '' }}>
                                    <label class="btn btn-outline-primary"
                                        for="{{ $typeId }}">{{ $type->label() }}</label>
                                @endforeach
                            @endisset
                        </div>
                    </div>

                    <div class="d-flex align-items-center">
                        <textarea class="form-control message-input border-0 me-3 shadow-none"
                            placeholder="اكتب الوقائع أو النقاط الأساسية هنا..." id="drafting-input" name="raw_text" rows="1"></textarea>

                        <label for="document-input" class="btn btn-icon btn-label-primary flex-shrink-0"
                            title="إرفاق ملف">
                            <i class="ti ti-paperclip ti-18px"></i>
                        </label>
                        <input type="file" id="document-input" name="document" class="d-none"
                            accept=".pdf,.doc,.docx,.txt">

                        <button type="submit" id="start-drafting-btn" class="btn btn-primary d-flex ms-2 send-msg-btn">
                            <i class="ti ti-send ti-16px me-md-2 me-0"></i>
                            <span class="align-middle d-md-inline-block d-none">صياغة</span>
                            <span class="spinner-border spinner-border-sm d-none ms-2" role="status"
                                aria-hidden="true"></span>
                        </button>
                    </div>
                    <div id="file-name-display" class="text-muted small mt-1 ps-1"></div>
                </form>
            </div>
        </div>
    @else
        <div class="d-flex justify-content-center align-items-center h-100 flex-column text-center">
            <i class="ti ti-feather ti-4x mb-3 text-primary"></i>
            <h4>استوديو الصياغة الذكي</h4>
            <p class="text-muted">اختر جلسة صياغة من القائمة أو ابدأ واحدة جديدة.</p>
        </div>
    @endif
    <template id="welcome-message-template">
        @include('legal-ai.drafting.partials.welcome-message')
    </template>
</div>
