@extends('layouts.layoutMaster')

@section('title', 'إحصائيات الاستبيان')

@section('breadcrumb')
    <li>
        <a href="{{ route('surveys.index') }}">إدارة الإستبيانات</a>
    </li>
    <li>
        <a href="{{ route('surveys.show', $survey) }}">{{ $survey->title }}</a>
    </li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">إحصائيات الاستبيان</a>
        <i class="ti ti-star favorite-icon" data-page-name="إحصائيات الاستبيان" data-page-url="{{ url()->current() }}"
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
    <div class="row g-3 align-items-stretch">
        <div class="col-12 col-md-8">

            {{-- الإحصائيات العامة --}}
            <div class="card">
                <div class="card-header py-4">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-chart-bar text-warning me-2"></i>
                        الإحصائيات العامة
                    </h6>
                </div>

                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body px-5">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="card y">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-center">

                                        <div>
                                            <h4 class="mb-0 ">{{ number_format($totalSent) }}</h4>
                                            <small class="text-muted">إجمالي المُرسل</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card ">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-center">

                                        <div>
                                            <h4 class="mb-0 ">{{ number_format($totalCompleted) }}</h4>
                                            <small class="text-muted">تم الإجابة عليه</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card ">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-center">

                                        <div>
                                            <h4 class="mb-0 ">{{ $completionRate }}%</h4>
                                            <small class="text-muted">معدل الإكمال</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="table-responsive">
                                <table class="table table-striped small text-center">
                                    <tr>
                                        <td class="fw-bold">عنوان الاستبيان</td>
                                        <td>{{ $survey->title }}</td>
                                    </tr>

                                    <tr>
                                        <td class="fw-bold">النوع</td>
                                        <td>{{ $survey->type->label() }}</td>
                                    </tr>

                                    <tr>
                                        <td class="fw-bold">الحالة</td>
                                        <td>
                                            <span class="badge bg-label-{{ $survey->status->color() }}">
                                                {{ $survey->status->label() }}
                                            </span>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="fw-bold">عدد الأسئلة</td>
                                        <td>{{ $survey->questions->count() }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- إحصائيات الأسئلة --}}
            <div class="card mt-3">
                <div class="card-header py-4 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-chart-pie text-warning me-2"></i>
                        إحصائيات تفصيلية للأسئلة
                    </h6>
                    <span class="badge bg-label-primary">
                        {{ $survey->questions->count() }} سؤال
                    </span>
                </div>

                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body px-4">
                    @if (empty($questionsStats))
                        <div class="text-center text-muted">لا توجد إحصائيات متاحة لهذا الاستبيان.</div>
                    @else
                        @foreach ($questionsStats as $idx => $questionStat)
                            <div class="border rounded p-3 mb-4">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h6 class="mb-0 text-primary">
                                        السؤال {{ $idx + 1 }}
                                    </h6>

                                    <div class="d-flex gap-2">
                                        <span class="badge bg-label-info">
                                            {{ $questionStat['total_answers'] }} إجابة
                                        </span>
                                        <a href="{{ route('surveys.question.details', [$survey, $questionStat['question']]) }}"
                                            class="btn btn-sm btn-outline-info" title="عرض تفاصيل السؤال">
                                            <i class="ti ti-eye"></i>
                                            تفاصيل
                                        </a>

                                    </div>
                                </div>

                                <div class="mb-3 fw-bold" dir="auto">
                                    {{ $questionStat['question']->question_text }}
                                </div>

                                @if (!empty($questionStat['options_stats']))
                                    <div class="row">
                                        @foreach ($questionStat['options_stats'] as $optionStat)
                                            <div class="col-md-6 mb-3">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span
                                                        class="small fw-bold">{{ $optionStat['option']->option_text }}</span>
                                                    <span class="badge bg-label-secondary">
                                                        {{ $optionStat['count'] }} ({{ $optionStat['percentage'] }}%)
                                                    </span>
                                                </div>
                                                <div class="progress" style="height: 6px;">
                                                    <div class="progress-bar bg-primary"
                                                        style="width: {{ $optionStat['percentage'] }}%" role="progressbar"
                                                        aria-valuenow="{{ $optionStat['percentage'] }}" aria-valuemin="0"
                                                        aria-valuemax="100">
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    @if ($questionStat['total_answers'] > 0)
                                        <div class="mt-3">
                                            <canvas id="chartQuestion{{ $questionStat['question']->id }}" width="200"
                                                height="200" style="max-width: 300px; max-height: 200px;">
                                            </canvas>
                                        </div>
                                    @endif
                                @else
                                    <div class="text-muted small">لا توجد إجابات لهذا السؤال.</div>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

        </div>

        <div class="col-12 col-md-4">
            <div class="d-flex flex-column gap-3">

                <div class="card">
                    <div class="card-header py-4">
                        <h6 class="card-title mb-0">
                            <i class="ti ti-settings text-warning me-2"></i>
                            إجراءات سريعة
                        </h6>
                    </div>

                    <div class="border-1 border-light border-dashed mb-2"></div>

                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('surveys.show', $survey) }}" class="btn btn-outline-primary btn-sm">
                                عرض تفاصيل الاستبيان
                            </a>
                            <a href="{{ route('surveys.edit', $survey) }}" class="btn btn-outline-success btn-sm">
                                تعديل الاستبيان
                            </a>

                            <button type="button" class="btn btn-outline-warning btn-sm" onclick="refreshStats()">
                                تحديث الإحصائيات
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header py-4">
                        <h6 class="card-title mb-0">
                            <i class="ti ti-info-circle text-warning me-2"></i>
                            معلومات إضافية
                        </h6>
                    </div>

                    <div class="border-1 border-light border-dashed mb-2"></div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <tr>
                                    <td class="fw-bold small">المتبقي:</td>
                                    <td class="small">{{ $totalSent - $totalCompleted }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold small">آخر إجابة:</td>
                                    <td class="small">
                                        @php
                                            $lastResponse = \App\Models\Survey\SurveyResponse::where(
                                                'survey_id',
                                                $survey->id,
                                            )
                                                ->where('status', 'completed')
                                                ->latest('completed_at')
                                                ->first();
                                        @endphp
                                        {{ $lastResponse ? $lastResponse->completed_at->diffForHumans() : 'لا يوجد' }}
                                    </td>
                                </tr>

                            </table>
                        </div>
                    </div>
                </div>

                {{-- سجل النشاطات --}}
                <div class="card flex-fill">
                    <div class="card-header py-4">
                        <h6 class="card-title mb-0">
                            <i class="ti ti-logs text-warning me-2"></i>
                            سجل النشاطات
                        </h6>
                    </div>

                    <div class="border-1 border-light border-dashed mb-2"></div>

                    <div class="card-body">
                        <ul class="timeline">
                            <li class="timeline-item">
                                <span class="timeline-point bg-secondary"></span>
                                <div class="timeline-event">
                                    <small class="timeline-title text-capitalize">تمت الإضافة بتاريخ</small>
                                    <small class="text-muted d-block">{{ $survey->created_at }}</small>
                                    @if ($survey->created_by && $survey->createdBy)
                                        <small>
                                            <b> بواسطة</b>
                                            <a href="{{ route('account.employee.profile', $survey->created_by) }}">
                                                {{ $survey->createdBy->getRawNameAttribute() }}
                                            </a>
                                        </small>
                                    @endif
                                </div>
                            </li>

                            @if ($survey->updated_by)
                                <li class="timeline-item">
                                    <span class="timeline-point bg-success"></span>
                                    <div class="timeline-event">
                                        <small class="timeline-title text-capitalize">آخر تعديل بتاريخ</small>
                                        <small class="text-muted d-block">{{ $survey->updated_at }}</small>
                                        @if ($survey->updatedBy)
                                            <small>
                                                <b> بواسطة</b>
                                                <a href="{{ route('account.employee.profile', $survey->updated_by) }}">
                                                    {{ $survey->updatedBy->getRawNameAttribute() }}
                                                </a>
                                            </small>
                                        @endif
                                    </div>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('page-script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // رسم الرسوم البيانية للأسئلة
        @foreach ($questionsStats as $questionStat)
            @if ($questionStat['total_answers'] > 0)
                const ctx{{ $questionStat['question']->id }} = document.getElementById(
                    'chartQuestion{{ $questionStat['question']->id }}').getContext('2d');
                new Chart(ctx{{ $questionStat['question']->id }}, {
                    type: 'doughnut',
                    data: {
                        labels: [
                            @foreach ($questionStat['options_stats'] as $optionStat)
                                '{{ $optionStat['option']->option_text }}',
                            @endforeach
                        ],
                        datasets: [{
                            data: [
                                @foreach ($questionStat['options_stats'] as $optionStat)
                                    {{ $optionStat['count'] }},
                                @endforeach
                            ],
                            backgroundColor: [
                                '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'
                            ],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    padding: 15,
                                    font: {
                                        size: 11
                                    }
                                }
                            }
                        }
                    }
                });
            @endif
        @endforeach

        function refreshStats() {
            location.reload();
        }
    </script>
@endsection
