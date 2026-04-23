@extends('layouts.layoutMaster')

@section('title', 'تلخيص المستندات')

@section('breadcrumb')
    <li><a href="#">المساعد القانوني</a></li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="{{ route('legal-ai.summarize.dashboard') }}">تلخيص المستندات</a>
        <i class="ti ti-star favorite-icon" data-page-name="تلخيص المستندات" data-page-url="{{ url()->current() }}"
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

    @vite(['resources/assets/js/legal-ai/common/ui.js', 'resources/assets/js/legal-ai/common/streaming.js', 'resources/assets/js/legal-ai/common/app.js', 'resources/assets/js/legal-ai/summarize/main.js'])
@endsection

@section('content')
    <div class="app-chat card overflow-hidden">
        <div class="row g-0">

            @include('legal-ai.summarize.partials.sidebar', [
                'chats' => $chats,
                'activeChat' => $chat,
                'authUser' => $authUser,
            ])

            @include('legal-ai.summarize.partials.history', [
                'activeChat' => $chat,
                'messages' => $messages,
                'authUser' => $authUser,
            ])
            <div class="app-overlay"></div>
        </div>
    </div>

    <script id="summarize-initial-data" type="application/json">
    {
        "activeChatId": @json($chat->id ?? null),
        "user": {
            "id": @json($authUser->id),
            "avatar": @json($authUser->image ? asset('storage/' . $authUser->image) : asset('assets/img/branding/Alburhan-Logo.png'))
        },
        "routes": {
            "create": "{{ route('legal-ai.summarize.create') }}",
            "loadChat": "{{ route('legal-ai.summarize.load', ['chat' => '__CHAT_ID__']) }}",
            "handleStream": "{{ route('legal-ai.summarize.handle-stream', ['chat' => '__CHAT_ID__']) }}",
            "saveResponse": "{{ route('legal-ai.summarize.save-response', ['chat' => '__CHAT_ID__']) }}",
            "delete": "{{ route('legal-ai.summarize.destroy', ['chat' => '__CHAT_ID__']) }}",
            "exportMessage": "{{ route('legal-ai.message.export', ['message' => '__MESSAGE_ID__']) }}"
        },
        "assets": {
            "aiAvatar": "{{ $aiAvatarPath }}"
        },
        "csrfToken": "{{ csrf_token() }}",
        "activeProvider": @json($activeProvider ?? 'openai')
    }
    </script>
@endsection
