@extends('layouts.layoutMaster')

@section('title', ' السوابق القضائية')

@section('breadcrumb')
    <li><a href="#">المساعد القانوني</a></li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="{{ route('legal-ai.precedents.dashboard') }}"> السوابق القضائية</a>
        <i class="ti ti-star favorite-icon" data-page-name=" السوابق القضائية" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('page-style')
    @vite(['resources/assets/vendor/scss/pages/app-chat.scss', 'resources/assets/css/legal-ai/common/app-chat-sidebar.css'])
@endsection

@section('page-script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dompurify/dist/purify.min.js"></script>
    @vite(['resources/assets/js/legal-ai/common/ui.js', 'resources/assets/js/legal-ai/common/app.js', 'resources/assets/js/legal-ai/common/streaming.js', 'resources/assets/js/legal-ai/precedents/main.js'])
@endsection

@section('content')
    <div class="app-chat card overflow-hidden">
        <div class="row g-0">
            @include('legal-ai.precedents.partials.sidebar', [
                'chats' => $chats,
                'activeChat' => $chat,
                'authUser' => $authUser,
                'aiAvatarPath' => $aiAvatarPath,
                'toolConfig' => $toolConfig,
            ])
            @include('legal-ai.precedents.partials.history', [
                'activeChat' => $chat,
                'messages' => $messages,
                'authUser' => $authUser,
                'aiAvatarPath' => $aiAvatarPath,
            ])
            <div class="app-overlay"></div>
        </div>
    </div>

    <script id="precedents-initial-data" type="application/json">
    {
        "activeChatId": @json($chat->id ?? null),
        "user": {
            "id": @json($authUser->id),
            "avatar": @json($authUser->image ? asset('storage/' . $authUser->image) : $aiAvatarPath)
        },
        "routes": {
            "create": "{{ route('legal-ai.precedents.create') }}",
            "loadChat": "{{ route('legal-ai.precedents.load', ['chat' => '__CHAT_ID__']) }}",
            "handleStream": "{{ route('legal-ai.precedents.handle-stream', ['chat' => '__CHAT_ID__']) }}",
            "saveResponse": "{{ route('legal-ai.precedents.save-response', ['chat' => '__CHAT_ID__']) }}",
            "delete": "{{ route('legal-ai.precedents.destroy', ['chat' => '__CHAT_ID__']) }}",
            "exportMessage": "{{ route('legal-ai.message.export', ['message' => '__MESSAGE_ID__']) }}"
        },
        "assets": {
            "aiAvatar": "{{ $aiAvatarPath }}"
        },
        "csrfToken": "{{ csrf_token() }}",
        "activeProvider": @json($activeProvider ?? 'openai'),
        "toolConfig": {
            "name": "{{ $toolConfig['name'] }}",
            "icon": "{{ $toolConfig['icon'] }}"
        }
    }
    </script>
@endsection
