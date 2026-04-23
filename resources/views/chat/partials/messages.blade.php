@foreach ($messages as $message)
    @if ($message->sender_id == Auth::id())
        <!-- رسالة مرسلة -->
        <li class="chat-message chat-message-right" data-message-id="{{ $message->id }}">
            <div class="d-flex overflow-hidden">
                <div class="chat-message-wrapper flex-grow-1">
                    <div class="chat-message-text">
                        @if ($message->message)
                            <p class="mb-0">{{ $message->message }}</p>
                        @endif

                        @if ($message->has_attachment)
                            <div class="attachment-wrapper mt-2">
                                <a href="{{ $message->attachment_url }}" target="_blank"
                                    class="attachment-link d-flex align-items-center p-2 rounded text-decoration-none"
                                    style="background-color: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.2); max-width: 220px;">
                                    <div class="file-icon me-2 flex-shrink-0">
                                        <i class="ti ti-file text-white" style="font-size: 16px;"></i>
                                    </div>
                                    <div class="flex-grow-1 text-truncate">
                                        <div class="fw-medium text-white text-truncate"
                                            style="font-size: 13px; line-height: 1.2;">
                                            {{ $message->attachment_name }}</div>
                                    </div>
                                </a>
                            </div>
                        @endif
                    </div>
                    <div class="text-end text-muted mt-1">
                        <small>{{ $message->created_at->format('h:i A') }}</small>
                        @if ($message->is_read)
                            <i class="ti ti-checks text-primary ms-1" title="تم القراءة"></i>
                        @else
                            <i class="ti ti-check text-muted ms-1" title="تم الإرسال"></i>
                        @endif
                    </div>
                </div>
                <div class="user-avatar flex-shrink-0 ms-4">
                    <div class="avatar avatar-sm">
                        <img src="{{ Auth::user()->image ? asset('storage/' . Auth::user()->image) : asset('assets/img/branding/Alburhan-Logo.png') }}"
                            alt="الصورة الرمزية" class="rounded-circle">
                    </div>
                </div>
            </div>
        </li>
    @else
        <!-- رسالة مستلمة -->
        <li class="chat-message" data-message-id="{{ $message->id }}">
            <div class="d-flex overflow-hidden">
                <div class="user-avatar flex-shrink-0 me-4">
                    <div class="avatar avatar-sm">
                        <img src="{{ $message->sender->image ? asset('storage/' . $message->sender->image) : asset('assets/img/branding/Alburhan-Logo.png') }}"
                            alt="الصورة الرمزية" class="rounded-circle">
                    </div>
                </div>
                <div class="chat-message-wrapper flex-grow-1">
                    <div class="chat-message-text">
                        @if ($message->message)
                            <p class="mb-0">{{ $message->message }}</p>
                        @endif

                        @if ($message->has_attachment)
                            <div class="attachment-wrapper mt-2">
                                <a href="{{ $message->attachment_url }}" target="_blank"
                                    class="attachment-link d-flex align-items-center p-2 rounded text-decoration-none"
                                    style="background-color: rgba(var(--bs-secondary-rgb), 0.1); border: 1px solid rgba(var(--bs-secondary-rgb), 0.2); max-width: 220px;">
                                    <div class="file-icon me-2 flex-shrink-0">
                                        <i class="ti ti-file text-body" style="font-size: 16px;"></i>
                                    </div>
                                    <div class="flex-grow-1 text-truncate">
                                        <div class="fw-medium text-body text-truncate"
                                            style="font-size: 13px; line-height: 1.2;">{{ $message->attachment_name }}
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endif
                    </div>
                    <div class="text-muted mt-1">
                        <small>{{ $message->created_at->format('h:i A') }}</small>
                    </div>
                </div>
            </div>
        </li>
    @endif
@endforeach
