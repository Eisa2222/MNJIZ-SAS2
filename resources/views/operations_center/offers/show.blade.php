@extends('layouts.layoutMaster')

@section('title', $offer->offer_name)

@section('breadcrumb')
    <li><a href="#"> مركز العمليات </a></li>
    <li><a href="{{ route('operations-center.offers.index') }}">العروض</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">{{ Str::limit($offer->offer_name, 25) }}</a>
        <i class="ti ti-star favorite-icon" data-page-name="تفاصيل العرض" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/quill/typography.scss', 'resources/assets/vendor/libs/quill/katex.scss', 'resources/assets/vendor/libs/quill/editor.scss'])
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-9">
            <div class="card mb-4">
                <div class="d-flex justify-content-between card-header py-4">
                    <div class="d-flex align-items-center">
                        <i class="ti ti-license text-warning me-2"></i>
                        <h6 class="card-title mb-0"> تفاصيل العرض </h6>
                    </div>

                    <div>
                        <a href="{{ route('operations-center.offers.export-pdf.official', $offer->id) }}"
                            class="btn btn-sm btn-primary">
                            <i class="ti ti-file-type-pdf"></i> تصدير PDF رسمي
                        </a>

                        <a href="{{ route('operations-center.offers.export-pdf.simple', $offer->id) }}"
                            class="btn btn-sm btn-primary">
                            <i class="ti ti-file-type-pdf"></i> تصدير PDF مبسط
                        </a>
                    </div>
                </div>

                <div class="border-1 border-light border-dashed mb-2"></div>
                <div class="card-body">
                    <div id="preview-info" class="content">
                        <div class="row position-relative">
                            <div id="template-canvas" class="col-12 position-relative">

                                <div class="ql-editor" style="white-space: normal">

                                    @if ($offer->is_private_and_secret)
                                        <div class="mb-3 text-end text-danger fw-bold">
                                            سري و خاص
                                        </div>
                                    @endif

                                    {!! $processedContent !!}

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if (!empty($approvalStages))
            <div class="col-3">
                <div class="card shadow-sm">
                    <div class="card-header py-5 bg-white">
                        <div class="d-flex align-items-center">
                            <h6 class="card-title mb-0 fw-semibold">
                                <i class="ti ti-list-details text-primary me-2"></i>
                                مراحل الاعتمادات
                            </h6>
                        </div>
                    </div>
                    <div class="border-1 border-light border-dashed mb-2"></div>
                    <div class="card-body">
                        <div class="timeline ms-2">
                            @foreach ($approvalStages as $stage)
                                @php
                                    $statusConfig = $stage['config'];
                                    $isCurrent = $stage['is_current'] ?? false;
                                    $isLast = $loop->last;
                                @endphp
                                <div class="d-flex position-relative {{ !$isLast ? 'mb-8' : '' }}">
                                    <div class="me-3 position-relative" style="z-index:2;">
                                        <div class="avatar avatar-md">
                                            <div
                                                class="avatar-initial {{ $statusConfig['bgClass'] }} {{ $statusConfig['textClass'] }} rounded-circle border-3 {{ $isCurrent ? 'border-primary' : 'border-0' }}">
                                                <i class="ti {{ $statusConfig['icon'] }} ti-sm"></i>
                                            </div>
                                        </div>
                                        @unless ($isLast)
                                            <div class="position-absolute bg-{{ $statusConfig['lineColor'] }}"
                                                style="left:50%; transform:translateX(-50%); width:4px; height:55px; z-index:1;">
                                            </div>
                                        @endunless
                                    </div>
                                    <div class="flex-fill d-flex align-items-center">
                                        <div>
                                            <span class="fw-semibold">المستوى {{ $stage['level'] ?? 'N/A' }}</span>
                                            <h6 class="mb-0 fw-bold">{{ $stage['employee_name'] ?? 'غير محدد' }}</h6>
                                            @if ($isCurrent)
                                                {{-- <small class="text-primary fw-bold">المرحلة الحالية</small> --}}
                                            @endif
                                            @if (!empty($stage['action_date']))
                                                <small class="text-muted d-block">{{ $stage['action_date'] }}</small>
                                            @endif
                                            @if (!empty($stage['reason']))
                                                <small class="text-muted d-block">{{ $stage['reason'] }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @elseif($isAutoApproved)
            <div class="col-3">
                <div class="card shadow-sm">
                    <div class="card-header py-5 bg-white">
                        <h6 class="card-title mb-0 fw-semibold">
                            <i class="ti ti-list-details text-primary me-2"></i>
                            مراحل الاعتمادات
                        </h6>
                    </div>
                    <div class="border-1 border-light border-dashed mb-2"></div>
                    <div class="card-body">
                        <div class="text-center">
                            <i class="ti ti-circle-dashed-check ti-lg text-success"></i>
                            <div class="mt-2 fw-semibold">تم الاعتماد تلقائيًا </div>
                            @if ($offer->updated_at)
                                <small class="text-muted d-block">{{ $offer->updated_at->format('d/m/Y H:i') }}</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- ============================================================== -->
    <!-- القسم العلوي: مراحل الاعتمادات (إذا وجدت)                     -->
    <!-- ============================================================== -->

@endsection
