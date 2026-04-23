@if ($message->sender === 'user')
    <li class="chat-message chat-message-right" id="message-{{ $message->id }}" data-sender="user">
        <div class="d-flex overflow-hidden">
            <div class="chat-message-wrapper flex-grow-1">
                <div class="chat-message-text">
                    @php
                        $converter = new \League\CommonMark\GithubFlavoredMarkdownConverter([
                            'html_input' => 'escape',
                            'allow_unsafe_links' => false,
                        ]);
                        $htmlMessage = $converter->convert($message->message);
                    @endphp
                    {!! $htmlMessage !!}
                </div>
                <div class="text-end text-muted mt-1">
                    <small>{{ $message->created_at->locale('en')->format('h:i A') }}</small>
                </div>
            </div>
            <div class="user-avatar flex-shrink-0 ms-3">
                <div class="avatar avatar-sm">
                    <img src="{{ $authUser->image ? asset('storage/' . $authUser->image) : asset('assets/img/branding/Alburhan-Logo.png') }}"
                        alt="الصورة الرمزية" class="rounded-circle">
                </div>
            </div>
        </div>
    </li>
@else
    @php
        $converter = new \League\CommonMark\GithubFlavoredMarkdownConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
        $htmlMessage = $converter->convert($message->message);
    @endphp

    <li class="chat-message" id="message-{{ $message->id }}" data-sender="ai">
        <div class="d-flex overflow-hidden">
            <div class="user-avatar flex-shrink-0 me-3">
                <div class="avatar avatar-sm">
                    <img src="{{ $aiAvatarPath }}" alt="صورة AI"
                        class="rounded-circle">
                </div>
            </div>
            <div class="chat-message-wrapper flex-grow-1">
                <div class="chat-message-text">
                    {!! $htmlMessage !!}
                </div>

                <div class="chat-message-actions d-flex align-items-center justify-content-between mt-2">
                    <div class="d-flex align-items-center">
                        <button class="btn btn-text-secondary btn-sm rounded-pill btn-icon copy-ai-message-btn"
                            title="نسخ النص">
                            <i class="ti ti-copy ti-18px"></i>
                        </button>
                        @if (Route::has('legal-ai.message.export'))
                            <a href="{{ route('legal-ai.message.export', $message) }}" target="_blank"
                                class="btn btn-text-secondary btn-sm rounded-pill btn-icon" title="تنزيل كـ PDF">
                                <i class="ti ti-download ti-18px"></i>
                            </a>
                        @endif
                    </div>
                    <div class="text-muted">
                        <small>{{ $message->created_at->locale('en')->format('h:i A') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </li>
@endif
