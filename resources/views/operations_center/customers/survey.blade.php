@extends('layouts.layoutMaster')

@section('title', 'تفاصيل الإستبيان')

@section('breadcrumb')
    <li><a href="#"> مركز العمليات </a></li>
    <li><a href="{{ route('operations-center.customers.index') }}">العملاء</a></li>
    <li><a
            href="{{ route('operations-center.customers.show', $survey_response->customer_id) }}">{{ $survey_response->customer->name }}</a>
    </li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">تفاصيل الإستبيان</a>
        <i class="ti ti-star favorite-icon" data-page-name="تفاصيل الإستبيان" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection



@section('content')
    <div class="row g-3">
        <div class="col-12 col-xl-8">
            <div class="card">
                <div class="card-header py-4 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-list-check text-primary me-2"></i>
                        أسئلة الاستبيان وإجابات العميل
                    </h6>
                    <span class="badge bg-label-primary">
                        {{ $survey_response->survey->questions->count() }} سؤال
                    </span>
                </div>

                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body px-4">
                    @if ($survey_response->survey->questions->isEmpty())
                        <div class="text-center text-muted">لا توجد أسئلة مسجّلة لهذا الاستبيان.</div>
                    @else
                        @foreach ($survey_response->survey->questions as $idx => $question)
                            <div class="border rounded p-4 mb-4 ">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h6 class="mb-0 text-primary">
                                        السؤال {{ $idx + 1 }}
                                    </h6>
                                    <div class="d-flex gap-2">
                                        <span class="badge bg-label-secondary">
                                            {{ $question->options->count() }} خيار
                                        </span>

                                    </div>
                                </div>
                                <div class="border-1 border-light border-dashed mb-5"></div>

                                <div class="mb-3 fw-bold text-dark" dir="auto">
                                    {{ $question->question_text }}
                                </div>


                                @if ($question->options->isNotEmpty())
                                    <div class="row">
                                        @foreach ($question->options as $option)
                                            @php
                                                $isSelected =
                                                    isset($selectedMap[$question->id]) &&
                                                    (int) $selectedMap[$question->id] === (int) $option->id;
                                            @endphp

                                            <div class="col-md-6 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" disabled
                                                        {{ $isSelected ? 'checked' : '' }}>
                                                    <label
                                                        class="form-check-label {{ $isSelected ? 'fw-bold text-success' : 'text-muted' }}">
                                                        {{ $option->option_text }}
                                                        @if ($isSelected)
                                                            <i class="ti ti-check-circle text-success ms-2"></i>
                                                            <span class="badge bg-label-success ms-1">إجابة العميل</span>
                                                        @endif
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-muted small">لا توجد خيارات لهذا السؤال.</div>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card">
                <div class="card-header py-4">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-clipboard-check text-primary me-2"></i>
                        معلومات الاستبيان
                    </h6>
                </div>

                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body">
                    <div class="row g-3 text-center">
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <div class="text-primary small fw-bold">
                                    {{ $survey_response->created_at->format('Y/m/d H:i') }}
                                </div>
                                <small class="text-muted">تاريخ الإرسال</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <div class="text-primary small fw-bold">
                                    {{ $survey_response->completed_at->format('Y/m/d H:i') }}
                                </div>
                                <small class="text-muted">تاريخ الإكمال</small>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="border rounded p-2">
                                <div class="text-primary fw-bold">
                                    @php
                                        $minutes = $survey_response->created_at->diffInMinutes(
                                            $survey_response->completed_at,
                                        );
                                    @endphp

                                    @if ($minutes >= 60)
                                        {{ intval($minutes / 60) }} ساعة {{ $minutes % 60 }} دقيقة
                                    @elseif($minutes > 0)
                                        {{ $minutes }} دقيقة
                                    @else
                                        أقل من دقيقة
                                    @endif
                                </div>
                                <small class="text-muted">المدة المستغرقة</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
