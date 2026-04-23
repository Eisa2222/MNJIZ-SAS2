@extends('layouts.layoutMaster')

@section('title', $offer->offer_name . ' - إدارة الاعتمادات')

@section('breadcrumb')
    <li><a href="#">سير العمل</a></li>
    <li><a href="{{ route('approval-workflow.offers.index') }}">اعتمادات العروض</a></li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">{{ Str::limit($offer->offer_name, 25) }}</a>
        <i class="ti ti-star favorite-icon" data-page-name="اعتماد العرض" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('page-script')
    <script>
        $(document).ready(function() {
            // تفعيل التبويب بناءً على الهاش
            if (window.location.hash) {
                $('.nav-tabs a[href="' + window.location.hash + '"]').tab('show');
            }
            $('.nav-tabs a').on('shown.bs.tab', function(e) {
                history.pushState(null, null, e.target.hash);
            });

            // معالج زر الاعتماد
            $(document).on('click', '.approval-action', function(e) {
                e.preventDefault();

                const offerId = $(this).data('id');
                const action = $(this).data('action');
                let url = '';
                let titleText = '';
                let confirmBtn = '';
                let confirmColor = 'btn-primary';

                if (action === 'approve') {
                    url = '{{ route('approval-workflow.offers.approve', ':id') }}'.replace(':id', offerId);
                    titleText = 'تأكيد اعتماد العرض';
                    confirmBtn = 'نعم، اعتماد العرض';
                    confirmColor = 'btn-success';
                } else if (action === 'revoke') {
                    url = '{{ route('approval-workflow.offers.revoke', ':id') }}'.replace(':id', offerId);
                    titleText = 'تأكيد إلغاء الاعتماد';
                    confirmBtn = 'نعم، إلغاء الاعتماد';
                    confirmColor = 'btn-warning';
                }

                Swal.fire({
                    title: titleText,
                    text: 'هل أنت متأكد من هذا الإجراء؟',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: confirmBtn,
                    cancelButtonText: 'إلغاء',
                    customClass: {
                        confirmButton: confirmColor + ' me-3',
                        cancelButton: 'btn btn-secondary'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        performAction(url);
                    }
                });
            });

            // معالج زر الرفض
            $(document).on('click', '.reject-action', function(e) {
                e.preventDefault();

                const offerId = $(this).data('id');
                const url = '{{ route('approval-workflow.offers.reject', ':id') }}'.replace(':id', offerId);

                Swal.fire({
                    title: 'رفض العرض',
                    input: 'textarea',
                    inputLabel: 'سبب الرفض',
                    inputPlaceholder: 'يرجى إدخال سبب رفض العرض...',
                    inputAttributes: {
                        'aria-label': 'سبب الرفض',
                        'rows': 4,
                        'maxlength': 1000
                    },
                    showCancelButton: true,
                    confirmButtonText: 'رفض العرض',
                    cancelButtonText: 'إلغاء',
                    customClass: {
                        confirmButton: 'btn btn-danger me-3',
                        cancelButton: 'btn btn-secondary'
                    },
                    buttonsStyling: false,
                    inputValidator: (value) => {
                        if (!value || value.trim().length === 0) {
                            return 'يجب إدخال سبب الرفض'
                        }
                        if (value.length > 1000) {
                            return 'سبب الرفض لا يجب أن يتجاوز 1000 حرف'
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        performAction(url, {
                            reason: result.value
                        });
                    }
                });
            });

            // دالة تنفيذ الإجراءات
            function performAction(url, data = {}) {
                data._token = '{{ csrf_token() }}';

                Swal.fire({
                    title: 'جاري المعالجة...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: data,
                    success: function(response) {
                        Swal.close();

                        if (response.success) {
                            toastr.success(response.message);
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            toastr.error(response.message || 'حدث خطأ غير متوقع');
                        }
                    },
                    error: function(xhr) {
                        Swal.close();

                        let message = 'حدث خطأ أثناء تنفيذ الإجراء';

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        } else if (xhr.status === 403) {
                            message = 'ليس لديك الصلاحية لتنفيذ هذا الإجراء';
                        } else if (xhr.status === 422) {
                            message = 'البيانات المدخلة غير صحيحة';
                        }

                        toastr.error(message);
                    }
                });
            }
        });
    </script>
@endsection

@section('content')
    <div class="row">
        <style>
            .nav-tabs .nav-link,
            .nav-pills .nav-link {
                justify-content: start;
                margin-bottom: 10px
            }

            .nav-tabs .nav-link.active,
            .nav-tabs .nav-link.active:hover,
            .nav-tabs .nav-link.active:focus {
                box-shadow: none;
                border-left: 5px solid;
            }
        </style>

        {{-- القائمة الجانبية للتبويبات --}}
        <div class="col-12 col-md-3 col-xl-3">
            <div>
                <ul class="nav nav-tabs flex-column border-bottom-0">
                    <!-- تبويب تفاصيل العرض -->
                    <li class="card rounded-0 mb-3 nav-item shadow-none">
                        <a class="py-3 nav-link fw-bold active m-0" data-bs-toggle="tab" href="#offerDetails">
                            <i class="ti ti-settings ti-sm me-1"></i>
                            <span class="align-middle">تفاصيل العرض</span>
                        </a>
                    </li>
                    <!-- تبويب سجل الاعتمادات -->
                    <li class="card rounded-0 mb-3 nav-item shadow-none">
                        <a class="py-3 nav-link fw-bold m-0" data-bs-toggle="tab" href="#approvalsLog">
                            <i class="ti ti-history ti-sm me-1"></i>
                            <span class="align-middle">سجل الاعتمادات</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        {{-- محتوى التبويبات --}}
        <div class="col-12 col-md-9 col-xl-9 order-1 order-md-0 mb-5">
            <div class="tab-content p-0">

                {{-- تبويب تفاصيل العرض --}}
                <div class="tab-pane fade show active" id="offerDetails">

                    {{-- تفاصيل العرض --}}
                    <div class="card mb-4">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h5 class="text-primary fw-bold mb-0">تفاصيل العرض</h5>
                        </div>
                        <div class="card-body">
                            <div id="preview-info" class="content">
                                <div class="row position-relative">
                                    <div id="template-canvas" class="col-12 position-relative">
                                        <!-- صورة الهيدر -->
                                        <div class="d-flex justify-content-between">
                                            @if (App\Helpers\SettingsHelper::get('horizontal_header_image'))
                                                <img src="{{ Storage::url(App\Helpers\SettingsHelper::get('horizontal_header_image')) }}"
                                                    alt="Header Image" class="header-logo"
                                                    style="max-height: 100px; object-fit: cover;">
                                            @else
                                                <p class="text-danger">يرجي اختيار صورة للترويسة من الاعدادات الخاصة
                                                    بالطباعة</p>
                                            @endif
                                            {{-- <div>
                                                <div>
                                                    الرقم المرجعي للعرض ({{ $offer->offer_number }})
                                                </div>
                                            </div> --}}
                                        </div>

                                        <!-- منطقة المعاينة -->
                                        <div class="ql-editor" style="white-space: normal">
                                            @if ($offer->is_private_and_secret)
                                                <div class="mb-3 text-end text-danger fw-bold">
                                                    سري و خاص
                                                </div>
                                            @endif
                                            {!! $processedContent !!}
                                            @if (App\Helpers\SettingsHelper::get('horizontal_footer_image'))
                                                <img id="footer-image"
                                                    src="{{ Storage::url(App\Helpers\SettingsHelper::get('horizontal_footer_image')) }}"
                                                    alt="Footer Image" class="img-fluid d-block w-100 mt-3"
                                                    style="max-height: 150px; object-fit: cover;">
                                            @else
                                                <p class="text-danger">يرجي اختيار صورة للفوتر من الاعدادات الخاصة بالطباعة
                                                </p>
                                            @endif
                                        </div>

                                        <!-- التواقيع والأختام -->
                                        @if ($approvalStatus['status'] === 'approved')
                                            <div class="mt-5">
                                                <div class="row">
                                                    @php
                                                        // جلب التواقيع من سجل الاعتمادات
                                                        $approvedLogs = $approvalLogs
                                                            ->where('action', 'approved')
                                                            ->sortBy('level');
                                                        $signatures = $approvedLogs->pluck('signature_path')->filter();
                                                        $seal =
                                                            \App\Models\GeneralSetting\SystemSetting\Settings::first()
                                                                ->signature ?? null;
                                                    @endphp

                                                    @if ($signatures->isEmpty())
                                                        {{-- لا توجد تواقيع --}}
                                                        <div class="col-12 text-center">
                                                            @if ($seal)
                                                                <img src="{{ Storage::url($seal) }}" alt="Seal"
                                                                    style="position: absolute; bottom: 100px;max-height: 150px; left:5px">
                                                            @else
                                                                <p class="text-muted">لا يوجد ختم</p>
                                                            @endif
                                                        </div>
                                                    @elseif ($signatures->count() === 1)
                                                        {{-- توقيع واحد --}}
                                                        <div class="col-12 text-center">
                                                            <img src="{{ Storage::url($signatures->first()) }}"
                                                                alt="Signature"
                                                                style="width:70px; display: block; position: absolute; bottom:100px;max-height: 70px; left:50px">
                                                            @if ($seal)
                                                                <img src="{{ Storage::url($seal) }}" alt="Seal"
                                                                    style="position: absolute; bottom: 100px;max-height: 150px; left:5px">
                                                            @endif
                                                        </div>
                                                    @elseif ($signatures->count() === 2)
                                                        {{-- توقيعان --}}
                                                        <div class="col-6 d-flex flex-column align-items-start">
                                                            <img src="{{ Storage::url($signatures->first()) }}"
                                                                alt="Signature"
                                                                style="width:70px; display: block; position: absolute; bottom:50%;max-height: 70px; right:5px">
                                                        </div>
                                                        <div class="col-6 text-center">
                                                            <img src="{{ Storage::url($signatures->last()) }}"
                                                                alt="Signature"
                                                                style="width:70px; display: block; position: absolute; bottom:100px;max-height: 70px; left:50px">
                                                            @if ($seal)
                                                                <img src="{{ Storage::url($seal) }}" alt="Seal"
                                                                    style="position: absolute; bottom: 100px;max-height: 150px; left:5px">
                                                            @endif
                                                        </div>
                                                    @elseif ($signatures->count() >= 3)
                                                        {{-- ثلاثة تواقيع أو أكثر --}}
                                                        <div class="col-6 d-flex flex-column align-items-start">
                                                            @foreach ($signatures->take(2) as $index => $signature)
                                                                <img src="{{ Storage::url($signature) }}" alt="Signature"
                                                                    style="width:70px; display: block; position: absolute; bottom:{{ 50 + $index * 5 }}%;max-height: 70px; right:5px">
                                                            @endforeach
                                                        </div>
                                                        <div class="col-6 text-center">
                                                            @if ($signatures->count() > 2)
                                                                <img src="{{ Storage::url($signatures->skip(2)->first()) }}"
                                                                    style="width:70px; display: block; position: absolute; bottom:100px;max-height: 70px; left:50px"
                                                                    alt="Signature">
                                                            @endif
                                                            @if ($seal)
                                                                <img src="{{ Storage::url($seal) }}" alt="Seal"
                                                                    style="position: absolute; bottom: 100px;max-height: 150px; left:5px">
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- أزرار التصدير والتعديل --}}
                            <div class="d-flex justify-content-end gap-2 mt-3">
                                @if (auth()->user()->can('إعتماد العروض') || auth()->user()->employee->id === $offer->created_by)
                                    <a href="{{ route('operations-center.offers.edit', ['offer' => $offer->id, 'from_show' => true]) }}"
                                        class="btn btn-primary">
                                        <i class="ti ti-edit me-1"></i> تعديل العرض
                                    </a>
                                @endif

                                <a href="{{ route('operations-center.offers.export-pdf', $offer->id) }}"
                                    class="btn btn-outline-primary">
                                    <i class="ti ti-file-download me-1"></i> تصدير PDF
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- مراحل الاعتمادات --}}
                    @if ($approvalStatus['has_approval_process'])
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="text-primary fw-bold mb-0">مراحل الاعتمادات</h5>
                            </div>
                            <div class="card-body">
                                @if (count($approvalStages) === 0)
                                    <p class="text-muted">لا توجد معلومات اعتماد بعد.</p>
                                @else
                                    <div class="d-flex justify-content-between align-items-center w-100 position-relative">
                                        @foreach ($approvalStages as $index => $stage)
                                            @php
                                                // تحديد لون الدائرة بناءً على الحالة
                                                $circleColor = '';
                                                $icon = '';

                                                if ($stage['status'] === 'approved') {
                                                    $circleColor = 'bg-success'; // أخضر
                                                    $icon = '<i class="ti ti-check"></i>'; // علامة صح
                                                } elseif ($stage['status'] === 'rejected') {
                                                    $circleColor = 'bg-danger'; // أحمر
                                                    $icon = '<i class="ti ti-x"></i>'; // علامة خطأ
                                                } elseif ($stage['status'] === 'pending') {
                                                    $circleColor = 'bg-warning'; // أصفر
                                                    $icon = '<i class="ti ti-clock"></i>'; // علامة ساعة
                                                } else {
                                                    $circleColor = 'bg-secondary'; // رمادي
                                                    $icon = '<i class="ti ti-clock"></i>'; // علامة ساعة
                                                }
                                            @endphp

                                            {{-- المرحلة --}}
                                            <div class="d-flex flex-column align-items-center position-relative">
                                                {{-- الدائرة --}}
                                                <div class="rounded-circle {{ $circleColor }} d-flex align-items-center justify-content-center text-white"
                                                    style="width: 50px; height: 50px;">
                                                    {!! $icon !!}
                                                </div>
                                                {{-- اسم المعتمد --}}
                                                <span class="mt-2 text-center small">{{ $stage['employee_name'] }}</span>
                                                {{-- المستوى --}}
                                                <span class="small text-muted">المستوى {{ $stage['level'] }}</span>
                                                {{-- تاريخ الإجراء --}}
                                                @if ($stage['action_date'])
                                                    <span class="small text-muted">{{ $stage['action_date'] }}</span>
                                                @endif
                                                {{-- المرحلة الحالية --}}
                                                @if ($stage['is_current'])
                                                    <span class="badge bg-warning mt-1">الحالية</span>
                                                @endif
                                            </div>

                                            {{-- الخط الفاصل --}}
                                            @if (!$loop->last)
                                                <div class="flex-grow-1 bg-{{ $stage['status'] === 'approved' ? 'success' : ($stage['status'] === 'rejected' ? 'danger' : 'secondary') }}"
                                                    style="height: 2px; margin: auto;"></div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- أزرار الاعتماد --}}
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="d-flex flex-wrap gap-3 justify-content-center">
                                    {{-- @if ($userPermissions['canApprove']) --}}
                                    <button class="btn btn-success approval-action" data-id="{{ $offer->id }}"
                                        data-action="approve">
                                        <i class="ti ti-check me-1"></i>
                                        اعتماد العرض
                                    </button>
                                    {{-- @endif --}}

                                    {{-- @if ($userPermissions['canReject']) --}}
                                    <button class="btn btn-danger reject-action" data-id="{{ $offer->id }}">
                                        <i class="ti ti-x me-1"></i>
                                        رفض العرض
                                    </button>
                                    {{-- @endif --}}

                                    {{-- @if ($userPermissions['canRevoke']) --}}
                                    <button class="btn btn-warning approval-action" data-id="{{ $offer->id }}"
                                        data-action="revoke">
                                        <i class="ti ti-rotate-clockwise me-1"></i>
                                        إلغاء الاعتماد
                                    </button>
                                    {{-- @endif --}}
                                </div>
                            </div>
                        </div>
                    @endif

                </div>

                {{-- تبويب سجل الاعتمادات --}}
                <div class="tab-pane fade" id="approvalsLog">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="text-primary fw-bold mb-0">سجل الاعتمادات </h5>
                        </div>
                        <div class="card-body">
                            @if ($approvalLogs->count() > 0)
                                <ul class="timeline">
                                    @foreach ($approvalLogs as $log)
                                        @php
                                            // تحديد لون الشارة بناءً على نوع الإجراء
                                            $iconClass = 'bg-secondary';
                                            if ($log->action === 'approved') {
                                                $iconClass = 'bg-success';
                                            } elseif ($log->action === 'rejected') {
                                                $iconClass = 'bg-danger';
                                            } elseif ($log->action === 'revoked') {
                                                $iconClass = 'bg-warning';
                                            }
                                        @endphp
                                        <li class="timeline-item">
                                            <span class="timeline-point {{ $iconClass }}"></span>
                                            <div class="timeline-event">
                                                <h6 class="timeline-title text-capitalize">
                                                    {{ $log->action_label }}
                                                </h6>
                                                <p class="mb-1">
                                                    <strong>المعتمد:</strong> {{ $log->employee->name ?? 'غير معروف' }}
                                                </p>
                                                <p class="mb-1">
                                                    <strong>المستوى:</strong> {{ $log->level }}
                                                </p>

                                                {{-- عرض السبب فقط إذا كان الإجراء هو "رفض" --}}
                                                @if ($log->action === 'rejected' && $log->reason)
                                                    <div class="alert alert-warning alert-sm mt-2">
                                                        <strong>سبب الرفض:</strong> {{ $log->reason }}
                                                    </div>
                                                @endif

                                                {{-- عرض التوقيع إن وجد --}}
                                                @if ($log->signature_path)
                                                    <div class="mt-2">
                                                        <img src="{{ Storage::url($log->signature_path) }}"
                                                            alt="التوقيع"
                                                            style="max-height: 40px; max-width: 80px; border: 1px solid #dee2e6; border-radius: 0.25rem; padding: 0.25rem;">
                                                        <div class="small text-muted">التوقيع</div>
                                                    </div>
                                                @endif

                                                <small class="text-muted">
                                                    <i class="ti ti-clock ti-xs me-1"></i>
                                                    {{ $log->created_at->format('d/m/Y H:i') }}
                                                </small>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted mb-0">لا يوجد سجلات اعتماد بعد.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CSS مخصص للتصميم --}}
    <style>
        .timeline {
            list-style: none;
            padding: 0;
            position: relative;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e9ecef;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 25px;
            padding-left: 50px;
        }

        .timeline-point {
            position: absolute;
            left: -7px;
            top: 5px;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            border: 2px solid #fff;
            z-index: 1;
        }

        .timeline-event {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            border-left: 3px solid #007bff;
        }

        .timeline-title {
            color: #495057;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .alert-sm {
            padding: 8px 12px;
            font-size: 12px;
        }
    </style>
@endsection
