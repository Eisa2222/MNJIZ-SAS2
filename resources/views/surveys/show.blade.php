@extends('layouts.layoutMaster')

@section('title', 'تفاصيل الاستبيان')

@section('breadcrumb')
    <li>
        <a href="{{ route('surveys.index') }}">إدارة الإستبيانات</a>
    </li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">تفاصيل الاستبيان</a>
        <i class="ti ti-star favorite-icon" data-page-name="تفاصيل الاستبيان" data-page-url="{{ url()->current() }}"
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

            {{-- تفاصيل الاستبيان --}}
            <div class="card">
                <div class="card-header py-4">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-list-details text-warning me-2"></i>
                        تفاصيل الاستبيان
                    </h6>
                </div>

                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body px-5">

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
                                <td class="fw-bold">الوصف</td>
                                <td>{{ $survey->description ?: '—' }}</td>
                            </tr>

                            <tr>
                                <td class="fw-bold">قالب الرسالة</td>
                                <td class="text-start">
                                    <div class="border rounded p-2  small" dir="auto" style="white-space:pre-wrap">
                                        {{ $survey->message_template }}
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            {{-- الأسئلة والخيارات --}}
            <div class="card mt-3">
                <div class="card-header py-4 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-list-check text-warning me-2"></i>
                        أسئلة الاستبيان
                    </h6>
                    <span class="badge bg-label-primary">
                        {{ $survey->questions->count() }}
                    </span>
                </div>

                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body px-4">
                    @if ($survey->questions->isEmpty())
                        <div class="text-center text-muted">لا توجد أسئلة مسجّلة لهذا الاستبيان.</div>
                    @else
                        @foreach ($survey->questions as $idx => $q)
                            <div class="border rounded p-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0">
                                        السؤال {{ $idx + 1 }}
                                    </h6>
                                    <span class="badge bg-label-secondary">
                                        {{ $q->options->count() }} خيار
                                    </span>
                                </div>

                                <div class="mb-2 small fw-bold  text-primary" dir="auto">
                                    {{ $q->question_text }}
                                </div>

                                @if ($q->options->isNotEmpty())
                                    <ul class="small mb-0">
                                        @foreach ($q->options as $opt)
                                            <li class="mb-2 fw-bold">
                                                {{ $opt->option_text }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="text-muted small">لا توجد خيارات لهذا السؤال.</div>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

        </div>

        {{-- العمود الجانبي --}}
        <div class="col-12 col-md-4">
            <div class="d-flex flex-column gap-3">

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
