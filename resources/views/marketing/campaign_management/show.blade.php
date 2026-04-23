@extends('layouts.layoutMaster')

@section('title', 'تفاصيل الحملة الإعلانية')

@section('breadcrumb')
    <li><a href="#">التسويق</a></li>
    <li><a href="{{ route('marketing.campaign-management.index') }}">إدارة الحملات الإعلانية</a></li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">تفاصيل الحملة</a>
        <i class="ti ti-star favorite-icon" data-page-name="تفاصيل الحملة" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/css/components/icons.css','resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-12 col-md-8">
            <div class="card">
                <div class="card-header py-4">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-list-details text-warning me-2"></i>
                        تفاصيل الحملة الإعلانية
                    </h6>
                </div>
                <div class="border-1 border-light border-dashed mb-2"></div>
                <div class="card-body px-5">
                    <div class="table-responsive">
                        <table class="table table-striped small text-center">
                            <tr>
                                <td class="fw-bold w-25">اسم الحملة</td>
                                <td>{{ $campaign_management->campaign_name }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold w-25">نوع المحتوى</td>
                                <td>{{ $campaign_management->contentType->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold w-25">قسم الحملة</td>
                                <td>{{ $campaign_management->campaignSection->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold w-25">الهدف</td>
                                <td>{{ $campaign_management->contentPurpose->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold w-25">المنصة الإعلانية</td>
                                <td>{{ $campaign_management->social->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold w-25">الميزانية</td>
                                <td>{{ number_format($campaign_management->budget, 2) }} <span class="icon-saudi_riyal mx-2"></span></td>
                            </tr>
                            <tr>
                                <td class="fw-bold w-25">تاريخ البدء</td>
                                <td>{{ $campaign_management->start_date }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold w-25">تاريخ الانتهاء</td>
                                <td>{{ $campaign_management->end_date }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold w-25">الحالة</td>
                                <td>{!! $campaign_management->status->label() !!}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold w-25">الجمهور المستهدف</td>
                                <td>{{ $campaign_management->targetAudience->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold w-25">مكان العرض</td>
                                <td>{{ $campaign_management->displayLocation->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold w-25">النص الإعلاني</td>
                                <td>{{ $campaign_management->text }}</td>
                            </tr>

                        </table>
                    </div>
                </div>
            </div>


        </div>




        {{-- سجل النشاط --}}
        <div class="col-12 col-md-4">
            <div class="card">
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
                                <small class="timeline-title">تمت الإضافة بتاريخ</small>
                                <small class="text-muted d-block">{{ $campaign_management->created_at }}</small>
                                <small>
                                    <b>أضيف بواسطة</b>
                                    <a href="{{ route('account.employee.profile', $campaign_management->created_by) }}">
                                        {{ $campaign_management->createdBy?->getRawNameAttribute() }}
                                    </a>
                                </small>
                            </div>
                        </li>
                        @if ($campaign_management->updated_by)
                            <li class="timeline-item">
                                <span class="timeline-point bg-success"></span>
                                <div class="timeline-event">
                                    <small class="timeline-title">تم التعديل بتاريخ</small>
                                    <small class="text-muted d-block">{{ $campaign_management->updated_at }}</small>
                                    <small>
                                        <b>تم التعديل بواسطة</b>
                                        <a
                                            href="{{ route('account.employee.profile', $campaign_management->updated_by) }}">
                                            {{ $campaign_management->updatedBy?->getRawNameAttribute() }}
                                        </a>
                                    </small>
                                </div>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        @if ($campaign_management->result)
            <div class="col-12 col-md-8 mt-4">
                <div class="card">
                    <div class="card-header py-4">
                        <h6 class="card-title mb-0">
                            <i class="ti ti-chart-bar text-info me-2"></i>
                            نتائج الحملة الإعلانية
                        </h6>
                    </div>
                    <div class="border-1 border-light border-dashed mb-2"></div>
                    <div class="card-body px-5">
                        <div class="table-responsive">
                            <table class="table table-striped small text-center">
                                <tr>
                                    <td class="fw-bold w-25">تاريخ التقرير</td>
                                    <td>{{ $campaign_management->result->report_date->format("Y-m-d") }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold w-25">الإنفاق الفعلي</td>
                                    <td>{{ number_format($campaign_management->result->spend, 2) }} <span class="icon-saudi_riyal mx-2"></span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold w-25">مرات الظهور</td>
                                    <td>{{ $campaign_management->result->impressions ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold w-25">عدد النقرات</td>
                                    <td>{{ $campaign_management->result->clicks ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold w-25">معدل النقر (CTR)</td>
                                    <td>{{ $campaign_management->result->ctr ?? '-' }}%</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold w-25">تكلفة النقرة (CPC)</td>
                                    <td>{{ $campaign_management->result->cpc ?? '-' }} <span class="icon-saudi_riyal mx-2"></span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold w-25">عدد التحويلات</td>
                                    <td>{{ $campaign_management->result->conversions ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold w-25">قيمة التحويلات</td>
                                    <td>{{ $campaign_management->result->conversion_value ?? '-' }} <span class="icon-saudi_riyal mx-2"></span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold w-25">العائد على الإنفاق (ROAS)</td>
                                    <td>{{ $campaign_management->result->roas ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold w-25">ملاحظات</td>
                                    <td>{{ $campaign_management->result->notes ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
