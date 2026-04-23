@extends('layouts.layoutMaster')

@section('title', 'إعدادت الشؤون القانونية')

@section('breadcrumb')
    <li><a href="{{ route('general-settings.index') }}">الإعدادات العامة</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> إعدادت الشؤون القانونية</a>
        <i class="ti ti-star favorite-icon" data-page-name="إعدادت الشؤون القانونية" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('content')
    <div class="row g-4 mb-5">
        <div class="col-12">
            <div class="card">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3">
                            <span class="avatar-initial rounded-circle bg-label-primary">
                                <i class="ti ti-settings ti-md"></i>
                            </span>
                        </div>
                        <div>
                            <p class="mb-0">إعدادت الشؤون القانونية</p>
                            <small class="text-muted mb-0">
                                تنظيم المحاكم، درجات الجهات، أنواع الدعاوى والجلسات
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        @foreach ($cards as $c)
            <div class="col-xl-4 col-lg-6 col-md-6">
                <a href="{{ $c['route'] }}" class="card h-100 setting-card text-center py-4 d-block">
                    <div class="avatar avatar-lg mx-auto mb-3">
                        <span class="avatar-initial rounded-circle {{ $c['badge'] }}">
                            <i class="{{ $c['icon'] }} ti-lg"></i>
                        </span>
                    </div>
                    <h6 class="mb-1">{{ $c['title'] }}</h6>
                    <small class="text-muted d-block my-5">{{ $c['note'] }}</small>
                </a>
            </div>
        @endforeach
    </div>

@endsection
