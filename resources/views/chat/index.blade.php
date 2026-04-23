@php
    $authUser = Auth::user();
@endphp

@extends('layouts.layoutMaster')

@section('title', 'الدردشة ')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">الدردشة
        </a>
        <i class="ti ti-star favorite-icon" data-page-name="الدردشة" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite('resources/assets/vendor/libs/bootstrap-maxlength/bootstrap-maxlength.scss')
@endsection

@section('page-style')
    @vite('resources/assets/vendor/scss/pages/app-chat.scss')
    <Style>
        .ps__rail-x,
        .ps__rail-y {
            display: none !important;
            opacity: 0 !important;
            visibility: hidden !important;
        }
    </Style>
@endsection

@section('vendor-script')
    @vite('resources/assets/vendor/libs/bootstrap-maxlength/bootstrap-maxlength.js')
@endsection

@section('page-script')
    @vite(['resources/js/app.js', 'resources/assets/js/chat/app-chat.js'])

    <script>
        window.namedRoutes = {
            chatMessagesGet: @json(route('chat.messages.get', ['userId' => 'USER_ID'])),
            chatMessagesSend: @json(route('chat.messages.send')),
            chatMessagesRead: @json(route('chat.messages.read', ['messageId' => 'MESSAGE_ID'])),
            chatMessagesReadStatus: @json(route('chat.messages.read-status', ['userId' => 'USER_ID']))
        };

        window.compileRoute = function(template, map) {
            return Object.keys(map).reduce((url, key) => {
                const pattern = new RegExp('' + key.toUpperCase() + '', 'g');
                return url.replace(pattern, encodeURIComponent(map[key]));
            }, template);
        };
    </script>


@endsection

@section('content')
    <div class="app-chat card overflow-hidden">
        <div class="row g-0">
            <!-- Chat & Contacts -->
            <div class="col app-chat-contacts app-sidebar flex-grow-0 overflow-hidden border-end" id="app-chat-contacts">
                <div class="sidebar-header h-px-75 px-5 border-bottom d-flex align-items-center">
                    <div class="d-flex align-items-center me-6 me-lg-0">
                        <div class="flex-shrink-0 avatar me-4" data-bs-toggle="sidebar" data-overlay="app-overlay-ex"
                            data-target="#app-chat-sidebar-left">
                            <img class="user-avatar rounded-circle cursor-pointer"
                                src="{{ Auth::user()->image ? asset('storage/' . Auth::user()->image) : asset('assets/img/branding/Alburhan-Logo.png') }}"
                                alt="الصورة الرمزية">
                        </div>
                        <div class="flex-grow-1 input-group input-group-merge rounded-pill">
                            <span class="input-group-text border-0 bg-light" id="basic-addon-search31">
                                <i class="ti ti-search text-muted"></i>
                            </span>
                            <input type="text" class="form-control chat-search-input border-0 bg-light"
                                placeholder="بحث..." aria-label="بحث..." aria-describedby="basic-addon-search31">
                        </div>
                    </div>
                    <i class="ti ti-x ti-lg cursor-pointer position-absolute top-50 end-0 translate-middle d-lg-none d-block"
                        data-overlay data-bs-toggle="sidebar" data-target="#app-chat-contacts"></i>
                </div>
                <div class="sidebar-body">
                    <ul class="list-unstyled chat-contact-list py-2 mb-0" id="chat-list">
                        @foreach ($users as $user)
                            <li class="chat-contact-list-item mb-1 user rounded" data-id="{{ $user->id }}">
                                <a class="d-flex align-items-center text-decoration-none rounded hover-bg-light">
                                    <div class="flex-shrink-0 avatar position-relative">
                                        <img src="{{ $user->image ? asset('storage/' . $user->image) : asset('assets/img/branding/Alburhan-Logo.png') }}"
                                            alt="الصورة الرمزية" class="rounded-circle">
                                        <span
                                            class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle unread-indicator"
                                            style="width: 10px; height: 10px; display: none;"
                                            data-user-id="{{ $user->id }}">
                                        </span>
                                    </div>
                                    <div class="chat-contact-info flex-grow-1 ms-4">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <h6 class="chat-contact-name text-truncate m-0 fw-medium">{{ $user->name }}
                                            </h6>
                                            <div class="d-flex align-items-center gap-2">
                                                @if ($user->last_message_time)
                                                    <small
                                                        class="text-muted">{{ \Carbon\Carbon::parse($user->last_message_time)->diffForHumans() }}</small>
                                                @endif
                                                @if ($user->unread_count > 0)
                                                    <span class="badge bg-primary rounded-pill user-unread-count"
                                                        style="font-size: 10px; min-width: 18px; height: 18px; line-height: 18px; padding: 0;"
                                                        data-user-id="{{ $user->id }}">
                                                        {{ $user->unread_count > 99 ? '99+' : $user->unread_count }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <!-- /Chat contacts -->

            <!-- Chat History -->
            <div class="col app-chat-history">
                <div class="chat-history-wrapper">
                    <div class="chat-history-header border-bottom">
                        <div class="d-flex justify-content-between align-items-center p-2">
                            <div class="d-flex overflow-hidden align-items-center">
                                <i class="ti ti-menu-2 ti-lg cursor-pointer d-lg-none d-block me-4" data-bs-toggle="sidebar"
                                    data-overlay data-target="#app-chat-contacts"></i>
                                <div class="flex-shrink-0 avatar" id="receiver-avatar">
                                    <img id="receiver-image" src="{{ asset('assets/img/branding/Alburhan-Logo.png') }}"
                                        alt="الصورة الرمزية" class="rounded-circle" data-bs-toggle="sidebar" data-overlay
                                        data-target="#app-chat-sidebar-right">
                                </div>
                                <div class="chat-contact-info flex-grow-1 ms-4">
                                    <h6 class="m-0 fw-semibold" id="receiver-name">اختر مستخدمًا لبدء الدردشة</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="chat-history-body bg-light bg-opacity-25">
                        <ul class="list-unstyled chat-history p-4" id="messages">
                            <!-- Messages will be loaded here -->
                        </ul>
                    </div>
                    <!-- Chat message form -->
                    <div class="chat-history-footer border-top bg-white">
                        <!-- Attachment Preview -->
                        <div class="attachment-preview" style="display: none;">
                            <!-- Preview content will be inserted here -->
                        </div>

                        <form class="form-send-message d-flex justify-content-between align-items-center p-4"
                            enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" id="receiver_id" name="receiver_id" value="">

                            <div class="d-flex align-items-center flex-grow-1 me-2">
                                <input class="form-control message-input border-0 shadow-none rounded-pill px-4 bg-light"
                                    placeholder="اكتب رسالتك هنا..." id="message" name="message"
                                    style="min-height: 44px;">
                            </div>

                            <div class="message-actions d-flex align-items-center gap-2">
                                <label for="attachment"
                                    class="btn btn-light btn-sm rounded-circle d-flex align-items-center justify-content-center border-0 shadow-sm position-relative"
                                    style="width: 44px; height: 44px;" title="إرفاق ملف">
                                    <i class="ti ti-paperclip fs-5 text-primary"></i>
                                </label>
                                <input type="file" id="attachment" name="attachment" class="attachment-input d-none"
                                    accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt,.zip,.rar">

                                <button type="submit"
                                    class="btn btn-primary rounded-circle d-flex align-items-center justify-content-center send-msg-btn border-0 shadow-sm position-relative"
                                    style="width: 44px; height: 44px;" title="إرسال الرسالة">
                                    <i class="ti ti-send fs-5"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="app-overlay"></div>
        </div>
    </div>

    <script>
        window.authId = {{ Auth::id() }};
        window.authUserImage =
            "{{ Auth::user()->image ? asset('storage/' . Auth::user()->image) : asset('assets/img/branding/Alburhan-Logo.png') }}";
        window.selectedUserId = {{ $selectedUserId ?? 'null' }};
    </script>

@endsection
