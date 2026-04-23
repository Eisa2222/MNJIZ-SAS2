<div class="col app-chat-contacts app-sidebar flex-grow-0 overflow-hidden border-end drafting-sidebar" id="app-chat-contacts">
    <div class="sidebar-body">
        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom flex-shrink-0">
            <h5 class="text-primary mb-0 d-flex align-items-center">
                <span>{{ $toolConfig['name'] ?? 'الصياغات' }}</span>
            </h5>
            <button id="new-draft-btn" class="btn btn-sm btn-primary" title="صياغة جديدة">
                <i class="ti ti-plus"></i> 
            </button>
        </div>
        <div class="chat-list-scroll-container">
            <ul class="list-unstyled chat-contact-list py-2 mb-0" id="chat-list">
                @forelse ($chats as $c)
                    <li class="chat-contact-list-item mb-1 conversation-item position-relative {{ $c->id === ($activeChat->id ?? null) ? 'active' : '' }}" data-id="{{ $c->id }}">
                        <a href="javascript:void(0);" class="d-flex align-items-center text-decoration-none chat-link" data-chat-id="{{ $c->id }}">
                            <div class="flex-shrink-0 avatar me-3">
                                <img src="{{ $aiAvatarPath }}" alt="AI" class="rounded-circle">
                            </div>
                            <div class="chat-contact-info flex-grow-1 overflow-hidden">
                                <h6 class="chat-contact-name text-truncate m-0 fw-normal">{{ $c->title }}</h6>
                                {{-- @if ($lastMessage = $c->messages->last())
                                    <small class="chat-contact-status text-truncate text-muted">{{ Str::limit(strip_tags($lastMessage->message), 25) }}</small>
                                @else
                                    <small class="chat-contact-status text-truncate text-muted">لم تبدأ الصياغة بعد</small>
                                @endif --}}
                            </div>
                        </a>
                        <button class="btn btn-sm btn-icon delete-conversation-btn" data-id="{{ $c->id }}" title="حذف الجلسة"><i class="ti ti-trash"></i></button>
                    </li>
                @empty
                    <li class="text-center p-4 text-muted">لا توجد صياغات سابقة.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
