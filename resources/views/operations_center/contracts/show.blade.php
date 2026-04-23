@extends('layouts.layoutMaster')

@section('title', $contract->contract_name)

@section('breadcrumb')
    <li><a href="#"> مركز العمليات </a></li>
    <li><a href="{{ route('operations-center.contracts.index') }}">العقود</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">{{ Str::limit($contract->contract_name, 25) }}</a>
        <i class="ti ti-star favorite-icon" data-page-name="تفاصيل العقد" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/quill/typography.scss', 'resources/assets/vendor/libs/quill/katex.scss', 'resources/assets/vendor/libs/quill/editor.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/quill/katex.js', 'resources/assets/vendor/libs/quill/quill.js'])
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-9">
            <div class="card mb-4">
                <div class="d-flex justify-content-between card-header  py-4">
                    <div class="d-flex align-items-center">
                        <i class="ti ti-license text-warning me-2"></i>
                        <h6 class="card-title mb-0"> تفاصيل العقد </h6>
                    </div>

                    <div>
                        <a href="{{ route('operations-center.contracts.export-pdf.official', $contract->id) }}"
                            class="btn btn-sm btn-primary">
                            <i class="ti ti-file-type-pdf"></i> تصدير PDF رسمي
                        </a>

                        <a href="{{ route('operations-center.contracts.export-pdf.simple', $contract->id) }}"
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

                                    @if ($contract->is_private_and_secret)
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

        <div class="col-3">
            {{-- المرفقات  --}}
            <div class="card mb-3">
                <div class="card-header py-5">
                    <h6 class="card-title  mb-0">
                        <i class="ti ti-paperclip text-warning me-2"></i>
                        المرفقات
                    </h6>
                </div>

                <div class="border-1 border-light border-dashed mb-2"></div>
                <div class="card-body">
                    @if ($contract->attachments && $contract->attachments->count() > 0)
                        <div class="row g-2">
                            @foreach ($contract->attachments as $index => $attachment)
                                <div class="col-12">
                                    <div class="card border attachment-card shadow-sm mb-2">
                                        <div class="card-body p-2">
                                            <div class="d-flex align-items-center">

                                                <div class="attachment-icon me-2"
                                                    style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="ti ti-file text-secondary fs-3"></i>
                                                </div>

                                                <div class="attachment-details">
                                                    <h6 class="mb-0 fs-6">
                                                        <span>{{ $attachment->name }}</span>
                                                    </h6>
                                                </div>

                                                <div class="ms-auto">
                                                    <div class="btn-group">
                                                        <!-- زر العرض -->
                                                        <a href="{{ asset('storage/' . $attachment->attachment) }}"
                                                            target="_blank" class="btn btn-sm btn-outline-info me-1"
                                                            title="عرض الملف">
                                                            <i class="ti ti-eye fs-6"></i>
                                                        </a>


                                                        <!-- زر التنزيل -->
                                                        <a href="{{ asset('storage/' . $attachment->attachment) }}"
                                                            download="{{ $attachment->name }}"
                                                            class="btn btn-sm btn-outline-primary" title="تنزيل الملف">
                                                            <i class="ti ti-download fs-6"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted small text-center">لا توجد مرفقات</p>
                    @endif
                </div>
            </div>

            @if (!empty($approvalStages))
                <div class="card shadow-sm">
                    <div class="card-header py-4 bg-white">
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
                        </div> <!-- /.timeline -->
                    </div>
                </div>
            @elseif($isAutoApproved)
                <div class="card shadow-sm">
                    <div class="card-header py-4 bg-white">
                        <h6 class="card-title mb-0 fw-semibold">
                            <i class="ti ti-list-details text-primary me-2"></i>
                            مراحل الاعتمادات
                        </h6>
                    </div>
                    <div class="border-1 border-light border-dashed mb-2"></div>
                    <div class="card-body">
                        <div class="text-center">
                            <i class="ti ti-circle-dashed-check ti-lg text-success"></i>
                            <div class="mt-2 fw-semibold">تم الاعتماد تلقائيًا</div>
                            @if ($contract->updated_at)
                                <small class="text-muted d-block">{{ $contract->updated_at->format('d/m/Y H:i') }}</small>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>

    </div>
@endsection
