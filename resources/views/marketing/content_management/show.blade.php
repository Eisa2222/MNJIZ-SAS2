@extends('layouts.layoutMaster')

@section('title', 'تفاصيل المحتوى ')

@section('breadcrumb')
    <li><a href="#">التسويق</a></li>
    <li><a href="{{ route('marketing.content-management.index') }}">إدارة المحتوى</a></li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">تفاصيل المحتوى</a>
        <i class="ti ti-star favorite-icon" data-page-name="تفاصيل المحتوى" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/css/components/icons.css', 'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/js/custom/select-all.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
    {!! $dataTable->scripts() !!}
@endsection

@section('page-style')
    @vite(['resources/css/dataTable.css'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/marketing/content-management/content-management.js'])
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('content')
    <div class="row g-3 align-items-stretch">
        {{-- العمود الأيسر: تفاصيل المحتوى + جدول النشر --}}
        <div class="col-12 col-md-8">
            <div class="card h-100">
                <div class="card-header py-4">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-list-details text-warning me-2"></i>
                        تفاصيل المحتوى
                    </h6>
                </div>

                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body px-5">
                    <div class="table-responsive">
                        <table class="table table-striped small text-center">
                            <tr>
                                <td class="fw-bold w-25">نوع المحتوى</td>
                                <td>{{ $content_management->contentType?->name ?? '-' }}</td>
                            </tr>

                            <tr>
                                <td class="fw-bold w-25">نمط النشر</td>
                                <td>{{ $content_management->publishingPattern?->name ?? '-' }}</td>
                            </tr>

                            <tr>
                                <td class="fw-bold w-25">الهدف من المحتوى</td>
                                <td>{{ $content_management->contentpurpose?->name ?? '-' }}</td>
                            </tr>

                            <tr>
                                <td class="fw-bold w-25">الحالة</td>
                                <td>{{ $content_management->status?->label() ?? '-' }}</td>
                            </tr>

                            <tr>
                                <td class="fw-bold w-25">نوع الوسائط</td>
                                <td>{{ $content_management->media_type?->label() ?? '-' }}</td>
                            </tr>

                            <tr>
                                <td class="fw-bold w-25">رابط/ملف الوسائط</td>
                                <td>
                                    @if (!empty($content_management->media_url ?? null))
                                        <a href="{{ $content_management->media_url }}" target="_blank">فتح الوسائط</a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <td class="fw-bold w-25">تاريخ النشر</td>
                                <td>{{ $content_management->publication_date?->format('Y-m-d') ?? '-' }}</td>
                            </tr>

                            <tr>
                                <td class="fw-bold w-25">نص المحتوى</td>
                                <td>{{ $content_management->content_text ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>

                    {{-- جدول النشر على الشبكات الاجتماعية --}}
                    <div class="mt-4">
                        {!! $dataTable->table(['class' => 'table table-striped table-bordered w-100'], true) !!}
                    </div>
                </div>
            </div>
        </div>

        {{-- العمود الجانبي: مطابق لتصميم العهد --}}
        <div class="col-12 col-md-4">
            <div class="d-flex flex-column h-100 gap-3">
                {{-- كارد مراحل الاعتمادات --}}
                @if (!empty($approvalStages))
                    <div class="card shadow-sm flex-fill">
                        <div class="card-header py-4 bg-white">
                            <h6 class="card-title mb-0 fw-semibold">
                                <i class="ti ti-list-details text-primary me-2"></i>
                                مراحل الاعتمادات
                            </h6>
                        </div>
                        <div class="border-1 border-light border-dashed mb-2"></div>

                        <div class="card-body">
                            <div class="timeline ms-2">
                                @foreach ($approvalStages as $stage)
                                    @php
                                        $statusConfig = $stage['config'] ?? [];
                                        $isCurrent = $stage['is_current'] ?? false;
                                        $isLast = $loop->last;
                                    @endphp

                                    <div class="d-flex position-relative {{ !$isLast ? 'mb-8' : '' }}">
                                        <div class="me-3 position-relative" style="z-index:2;">
                                            <div class="avatar avatar-md">
                                                <div
                                                    class="avatar-initial {{ $statusConfig['bgClass'] ?? 'bg-light' }} {{ $statusConfig['textClass'] ?? 'text-body' }}
                                  rounded-circle border-3 {{ $isCurrent ? 'border-primary' : 'border-0' }}">
                                                    <i class="ti {{ $statusConfig['icon'] ?? 'ti-user' }} ti-sm"></i>
                                                </div>
                                            </div>
                                            @unless ($isLast)
                                                <div class="position-absolute bg-{{ $statusConfig['lineColor'] ?? 'secondary' }}"
                                                    style="left:50%; transform:translateX(-50%); width:4px; height:55px; z-index:1;">
                                                </div>
                                            @endunless
                                        </div>

                                        <div class="flex-fill d-flex align-items-center">
                                            <div>
                                                <span class="fw-semibold">المستوى {{ $stage['level'] ?? 'N/A' }}</span>
                                                <h6 class="mb-0 fw-bold">{{ $stage['employee_name'] ?? 'غير محدد' }}</h6>

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
                    <div class="card shadow-sm flex-fill">
                        <div class="card-header py-4 bg-white">
                            <h6 class="card-title mb-0 fw-semibold">
                                <i class="ti ti-list-details text-primary me-2"></i>
                                مراحل الاعتمادات
                            </h6>
                        </div>
                        <div class="border-1 border-light border-dashed mb-2"></div>
                        <div class="card-body d-flex align-items-center justify-content-center">
                            <div class="text-center">
                                <i class="ti ti-circle-dashed-check ti-lg text-success"></i>
                                <div class="mt-2 fw-semibold">تم الاعتماد تلقائيًا</div>
                                @if ($content_management->updated_at)
                                    <small
                                        class="text-muted d-block">{{ $content_management->updated_at->format('d/m/Y H:i') }}</small>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

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
                                    <small class="text-muted d-block">{{ $content_management->created_at }}</small>
                                    <small>
                                        <b>أضيف بواسطة</b>
                                        @if ($content_management->createdBy)
                                            <a
                                                href="{{ route('account.employee.profile', $content_management->created_by) }}">
                                                {{ $content_management->createdBy?->getRawNameAttribute() ?? $content_management->createdBy?->name }}
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </small>
                                </div>
                            </li>

                            @if ($content_management->updated_by)
                                <li class="timeline-item">
                                    <span class="timeline-point bg-success"></span>
                                    <div class="timeline-event">
                                        <small class="timeline-title text-capitalize">تم التعديل بتاريخ</small>
                                        <small class="text-muted d-block">{{ $content_management->updated_at }}</small>
                                        <small>
                                            <b>تم التعديل بواسطة</b>
                                            @if ($content_management->updatedBy)
                                                <a
                                                    href="{{ route('account.employee.profile', $content_management->updated_by) }}">
                                                    {{ $content_management->updatedBy?->getRawNameAttribute() ?? $content_management->updatedBy?->name }}
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </small>
                                    </div>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div> <!-- /right column -->
        </div>
    </div>
@endsection
