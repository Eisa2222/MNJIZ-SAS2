@extends('layouts.layoutMaster')

@section('title', 'إعدادات مواقع التواصل')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> إعدادات مواقع التواصل
        </a>
        <i class="ti ti-star favorite-icon" data-page-name="إعدادات مواقع التواصل" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('page-style')
    @vite('resources/assets/vendor/scss/pages/page-icons.scss')
@endsection

@section('content')

    <!-- Icon container -->
    <div class="d-flex flex-wrap justify-content-center" id="icons-container">

        @foreach ($settings as $item)
            <div class="card icon-card  text-center mb-6 mx-3">
                <div class="card-body"> <i class="{{ $item->icon }} mb-2"></i>
                    <p class="icon-name text-capitalize text-truncate mb-0">{{ $item->name }}</p>

                    @if ($item->name == 'linkedin')
                        {{-- التحقق من حالة LinkedIn --}}
                        @if (isset($linkedinStatus['connection']['success']) && $linkedinStatus['connection']['success'] == true)
                            <p class="text-success small fw-bold">متصل</p>
                            <p class="text-success small fw-bold mb-2">
                                {{ $linkedinStatus['connection']['user_info']['name'] ?? '--' }}
                            </p>
                            <a href="{{ route('settings-social.disconnect', $item->id) }}"
                                class="btn btn-danger btn-sm mt-5">
                                فصل الخدمة
                            </a>
                        @else
                            <p class="text-danger small fw-bold mb-10">غير متصل</p>
                            @if (isset($linkedinStatus['error']))
                                <p class="text-warning small m-0 truncate">{{ $linkedinStatus['error'] }}</p>
                            @endif
                            <a href="{{ route('settings-social.edit', $item->id) }}" class="btn btn-primary btn-sm mt-5">
                                إعدادات الربط
                            </a>
                        @endif
                    @elseif ($item->name == 'x')
                        {{-- التحقق من حالة Twitter --}}
                        @if (isset($twitterStatus['connection']['success']) && $twitterStatus['connection']['success'] == true)
                            <p class="text-success small fw-bold">متصل</p>
                            <p class="text-success small fw-bold mb-2">
                                {{ $twitterStatus['connection']['user_info']['username'] ?? '--' }}
                            </p>
                            <a href="{{ route('settings-social.disconnect', $item->id) }}"
                                class="btn btn-danger btn-sm mt-5">
                                فصل الخدمة
                            </a>
                        @else
                            <p class="text-danger small fw-bold mb-10">غير متصل</p>
                            @if (isset($twitterStatus['error']))
                                <p class="text-warning small m-0">{{ $twitterStatus['error'] }}</p>
                            @endif
                            <a href="{{ route('settings-social.edit', $item->id) }}" class="btn btn-primary btn-sm mt-5">
                                إعدادات الربط
                            </a>
                        @endif
                    @else
                        <p class="text-danger small fw-bold mb-10">غير متصل</p>
                        <a href="{{ route('settings-social.edit', $item->id) }}" class="btn btn-primary btn-sm mt-5">
                            إعدادات الربط
                        </a>
                    @endif



                </div>
            </div>
        @endforeach

    </div>

@endsection
