@extends('layouts.layoutMaster')

@section('title', 'إعدادات مركز العمليات')

@section('breadcrumb')
    <li><a href="{{ route('general-settings.index') }}">الإعدادات العامة</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> إعدادات مركز العمليات</a>
        <i class="ti ti-star favorite-icon" data-page-name="إعدادات مركز العمليات" data-page-url="{{ url()->current() }}"
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
                            <p class="mb-0">إعدادات مركز العمليات</p>
                            <small class="text-muted mb-0">
                                إدارة أقسام المشاريع، حالات العقود، وسير العمل التشغيلي
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        @foreach ($cards as $card)
            <div class="col-xl-4 col-lg-6 col-md-6">
                <a href="{{ $card['route'] }}" class="card h-100 setting-card text-center py-4 d-block">
                    <div class="avatar avatar-lg mx-auto mb-3">
                        <span class="avatar-initial rounded-circle {{ $card['badge'] }}">
                            <i class="{{ $card['icon'] }} ti-lg"></i>
                        </span>
                    </div>
                    <h6 class="mb-1">{{ $card['title'] }}</h6>
                    <small class="text-muted d-block my-5">{{ $card['note'] }}</small>
                </a>
            </div>
        @endforeach

    </div>
@endsection
