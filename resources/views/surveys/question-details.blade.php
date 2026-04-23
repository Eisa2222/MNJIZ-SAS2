@extends('layouts.layoutMaster')

@section('title', 'تفاصيل السؤال')

@section('breadcrumb')
    <li>
        <a href="{{ route('surveys.index') }}">إدارة الإستبيانات</a>
    </li>
    <li>
        <a href="{{ route('surveys.show', $survey) }}">{{ $survey->title }}</a>
    </li>
    <li>
        <a href="{{ route('surveys.statistics', $survey) }}">إحصائيات الاستبيان</a>
    </li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">تفاصيل السؤال</a>
        <i class="ti ti-star favorite-icon" data-page-name="تفاصيل السؤال" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/css/components/icons.css'])
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-12">
            {{-- معلومات السؤال --}}
            <div class="card">
                <div class="card-header py-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0">
                            <i class="ti ti-help-circle text-warning me-2"></i>
                            تفاصيل السؤال
                        </h6>
                        <div class="d-flex gap-2">
                            <span class="badge bg-label-info">
                                {{ collect($optionsDetails)->sum('count') }} إجابة إجمالية
                            </span>
                        </div>
                    </div>
                </div>

                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body">
                    <div class="rounded p-3 mb-4">
                        <p class="mb-0 fw-bold" dir="auto">{{ $question->question_text }}</p>
                    </div>

                    {{-- إحصائيات سريعة --}}
                    <div class="row text-center mb-4">
                        <div class="col-md-4">
                            <div class="border rounded p-3">
                                <h4 class="text-primary mb-1">{{ $question->options->count() }}</h4>
                                <small class="text-muted">عدد الخيارات</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3">
                                <h4 class="text-success mb-1">{{ collect($optionsDetails)->sum('count') }}</h4>
                                <small class="text-muted">إجمالي الإجابات</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3">
                                <h4 class="text-info mb-1">{{ collect($optionsDetails)->where('count', '>', 0)->count() }}
                                </h4>
                                <small class="text-muted">خيارات مُجابة</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- تفاصيل كل خيار --}}
        @foreach ($optionsDetails as $optionDetail)
            <div class="col-12">
                <div class="card h-100">
                    <div class="card-header py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="card-title mb-0">
                                <i class="ti ti-check-circle text-success me-2"></i>
                                {{ $optionDetail['option']->option_text }}
                            </h6>
                            <div class="d-flex gap-2">
                                <span class="badge bg-label-primary">
                                    {{ $optionDetail['count'] }} إجابة
                                </span>
                                <span class="badge bg-label-secondary">
                                    {{ collect($optionsDetails)->sum('count') > 0 ? round(($optionDetail['count'] / collect($optionsDetails)->sum('count')) * 100, 1) : 0 }}%
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        @if ($optionDetail['count'] > 0)
                            {{-- قائمة العملاء --}}
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>العميل</th>
                                            <th>تاريخ الإجابة</th>
                                            <th>العنوان IP</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($optionDetail['customers'] as $customerData)
                                            <tr>
                                                <td>
                                                    @if ($customerData['customer'])
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar avatar-sm me-2">
                                                                <img src="{{ asset('assets/img/branding/Alburhan-Logo.png') }}"
                                                                    alt="">
                                                            </div>
                                                            <div>
                                                                <small
                                                                    class="fw-bold">{{ $customerData['customer']->name }}</small>
                                                                @if ($customerData['customer']->email)
                                                                    <br><small
                                                                        class="text-muted">{{ $customerData['customer']->email }}</small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @else
                                                        <small class="text-muted">عميل غير محدد</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <small>{{ $customerData['answered_at']->format('Y/m/d H:i') }}</small>
                                                    <br><small
                                                        class="text-muted">{{ $customerData['answered_at']->diffForHumans() }}</small>
                                                </td>
                                                <td>
                                                    <small
                                                        class="text-muted">{{ $customerData['ip_address'] ?? '—' }}</small>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="ti ti-inbox fs-3 d-block mb-2"></i>
                                <small>لم يختر أي عميل هذا الخيار</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach

    </div>
@endsection
