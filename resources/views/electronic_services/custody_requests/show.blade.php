@extends('layouts.layoutMaster')

@section('title', 'تفاصيل الطلب')

@section('breadcrumb')
    <li><a href="#">الخدمات الإلكترونية</a></li>
    <li><a href="{{ route('account.electronic-services.custody-requests.index') }}">طلبات العهد</a></li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">تفاصيل الطلب</a>
        <i class="ti ti-star favorite-icon" data-page-name=" تفاصيل الطلب" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('content')
    <div class="row g-3 align-items-stretch">
        <div class="col-12 col-md-8">
            <div class="card h-100">
                <div class="card-header py-4">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-list-details text-warning me-2"></i>
                        تفاصيل الطلب
                    </h6>
                </div>

                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body px-5">
                    <div class="table-responsive">
                        <table class="table table-striped small text-center">
                            <tr>
                                <td class="fw-bold">الاصل</td>
                                <td class="fw-bold">{{ $custody_request->item->name }}</td>
                            </tr>

                            <tr>
                                <td class="fw-bold">الرقم التسلسلي</td>
                                <td>{{ $custody_request->item->serial_number }}</td>
                            </tr>

                            <tr>
                                <td class="fw-bold">التصنيف</td>
                                <td>{{ $custody_request->item->assetCategory->name }}</td>
                            </tr>

                            <tr>
                                <td class="fw-bold">نوع الطلب</td>
                                <td class="fw-bold">{{ $custody_request->request_type->label() }}</td>
                            </tr>

                            @if ($custody_request->return_status)
                                <tr>
                                    <td class="fw-bold">سبب الارجاع</td>
                                    <td class="text-danger">{{ $custody_request->return_status->label() }}</td>
                                </tr>
                            @endif

                            <tr>
                                <td class="fw-bold">الحالة</td>
                                <td>{{ $custody_request->status->label() }}</td>
                            </tr>

                            <tr>
                                <td class="fw-bold">ملاحظات</td>
                                <td>{{ $custody_request->notes ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

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
                                @if ($custody_request->updated_at)
                                    <small
                                        class="text-muted d-block">{{ $custody_request->updated_at->format('d/m/Y H:i') }}</small>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                {{-- سجل النشاط --}}
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
                                <span class="timeline-point bg-primary"></span>
                                <div class="timeline-event">
                                    <small class="timeline-title text-capitalize">
                                        تمت الاضافة بتاريخ
                                    </small>
                                    <small class="text-muted d-block">
                                        {{ $custody_request->created_at }}
                                    </small>
                                    <small>
                                        <b>اضيف بواسطة</b>
                                        <a href="{{ route('account.employee.profile', $custody_request->created_by) }}">
                                            {{ $custody_request->createdBy->getRawNameAttribute() }}
                                        </a>
                                    </small>
                                </div>
                            </li>

                            @if ($custody_request->updated_by)
                                <li class="timeline-item">
                                    <span class="timeline-point bg-warning"></span>
                                    <div class="timeline-event">
                                        <small class="timeline-title text-capitalize">
                                            اخر تحديث بتاريخ
                                        </small>
                                        <small class="text-muted d-block">
                                            {{ $custody_request->updated_at }}
                                        </small>
                                        <small>
                                            <b>التعديل بواسطة</b>
                                            <a
                                                href="{{ route('account.employee.profile', $custody_request->updated_by) }}">
                                                {{ $custody_request->updatedBy->getRawNameAttribute() }}
                                            </a>
                                        </small>
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
