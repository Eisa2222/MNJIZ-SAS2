@extends('layouts.layoutMaster')

@section('title', 'تفاصيل العميل')

@section('breadcrumb')
    <li><a href="#"> مركز العمليات </a></li>

    <li><a href="{{ route('operations-center.customers.index') }}">العملاء</a></li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">تفاصيل العميل

        </a>
        <i class="ti ti-star favorite-icon" data-page-name="تفاصيل العميل" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection


@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-12 col-xl-8">
            <div class="card mb-3">
                <div class="card-header py-4">

                    <h6 class="card-title mb-0">
                        <i class="ti ti-list-details text-primary me-2"></i>
                        تفاصيل العميل
                    </h6>
                </div>

                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body px-5">
                    <div class="table-responsive">
                        <table class="table table-striped small text-center">
                            {{-- المعلومات الأساسية --}}
                            <tr>
                                <td class="fw-bold">نوع العميل</td>
                                <td>
                                    <span
                                        class="badge bg-{{ $customer->customer_type->color() }}">{{ $customer->customer_type->label() }}</span>
                                </td>
                            </tr>

                            <tr>
                                <td class="fw-bold">الاسم</td>
                                <td>{{ $customer->name }}</td>
                            </tr>

                            {{-- بيانات الفرد --}}
                            @if ($customer->customer_type->value == 'individual')
                                <tr>
                                    <td class="fw-bold">الكنية</td>
                                    <td>{{ $customer->title ?? '-' }}</td>
                                </tr>

                                <tr>
                                    <td class="fw-bold">السجل المدني</td>
                                    <td>{{ $customer->civil_registry_number ?? '-' }}</td>
                                </tr>
                            @endif

                            @if ($customer->nationality)
                                <tr>
                                    <td class="fw-bold">الجنسية</td>
                                    <td>{{ $customer->nationality->name }}</td>
                                </tr>
                            @endif

                            {{-- بيانات الشخصية الاعتبارية --}}
                            @if ($customer->customer_type->value == 'company')
                                <tr>
                                    <td class="fw-bold">رقم السجل التجاري</td>
                                    <td>{{ $customer->commercial_registration_number ?? '-' }}</td>
                                </tr>

                                <tr>
                                    <td class="fw-bold">الرقم الموحد</td>
                                    <td>{{ $customer->unified_number ?? '-' }}</td>
                                </tr>
                            @endif

                            {{-- معلومات الاتصال --}}
                            @if ($customer->contact_number)
                                <tr>
                                    <td class="fw-bold">رقم الاتصال</td>
                                    <td dir="ltr">
                                        <a href="tel:{{ $customer->contact_number }}">
                                            {{ $customer->contact_number }}
                                        </a>
                                    </td>
                                </tr>
                            @endif

                            @if ($customer->email)
                                <tr>
                                    <td class="fw-bold">البريد الإلكتروني</td>
                                    <td>
                                        <a href="mailto:{{ $customer->email }}">
                                            {{ $customer->email }}
                                        </a>
                                    </td>
                                </tr>
                            @endif



                            @if ($customer->region)
                                <tr>
                                    <td class="fw-bold">المدينة</td>
                                    <td>{{ $customer->region->name }}</td>
                                </tr>
                            @endif

                            {{-- حالة العميل --}}
                            @if ($customer->status)
                                <tr>
                                    <td class="fw-bold">حالة العميل</td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $customer->status->name }}</span>
                                    </td>
                                </tr>
                            @endif

                            {{-- القطاع --}}
                            @if ($customer->sector)
                                <tr>
                                    <td class="fw-bold">القطاع</td>
                                    <td>{{ $customer->sector->name }}</td>
                                </tr>
                            @endif

                            {{-- مسؤول العلاقات --}}
                            <tr>
                                <td class="fw-bold">مسؤول العلاقات</td>
                                <td>
                                    <a href="{{ route('account.employee.profile', $customer->relationshipManager->id) }}">
                                        {{ $customer->relationshipManager->name }}
                                    </a>
                                </td>
                            </tr>

                            {{-- قناة التسويق --}}
                            @if ($customer->marketingChannel)
                                <tr>
                                    <td class="fw-bold">قناة التسويق</td>
                                    <td>{{ $customer->marketingChannel->name }}</td>
                                </tr>
                            @endif

                            {{-- قناة التسويق التفصيلية --}}
                            @if ($customer->detailedMarketingChannel)
                                <tr>
                                    <td class="fw-bold">قناة التسويق التفصيلية</td>
                                    <td>
                                        <a
                                            href="{{ route('account.employee.profile', $customer->detailedMarketingChannel->id) }}">
                                            {{ $customer->detailedMarketingChannel->name }}
                                        </a>
                                    </td>
                                </tr>
                            @endif

                            {{-- العميل الرئيسي --}}
                            @if ($customer->parentCustomer)
                                <tr>
                                    <td class="fw-bold">العميل الرئيسي</td>
                                    <td>
                                        <a
                                            href="{{ route('operations-center.customers.show', $customer->parentCustomer->id) }}">
                                            {{ $customer->parentCustomer->name }}
                                        </a>
                                    </td>
                                </tr>
                            @endif

                            {{-- موقع التواصل الاجتماعي --}}
                            @if ($customer->socialMedia)
                                <tr>
                                    <td class="fw-bold">موقع التواصل الاجتماعي</td>
                                    <td>{{ $customer->socialMedia->name }}</td>
                                </tr>
                            @endif


                        </table>
                    </div>

                    {{-- عرض المفوضين للشخصية الاعتبارية --}}
                    @if ($customer->customer_type->value == 'company' && $customer->authorizations->count() > 0)
                        <div class="mt-4">
                            <div class="border-1 border-light border-dashed mb-2"></div>

                            <h6 class="mb-3">
                                <i class="ti ti-users text-warning me-2"></i>
                                المفوضين
                            </h6>
                            <div class="border-1 border-light border-dashed mb-2"></div>

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered small">
                                    <thead class="table-light">
                                        <tr>
                                            <th>الاسم</th>
                                            <th>رقم الهوية</th>
                                            <th>رقم الهاتف</th>
                                            <th>البريد الإلكتروني</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($customer->authorizations as $auth)
                                            <tr>
                                                <td>{{ $auth->name }}</td>
                                                <td>{{ $auth->id_number }}</td>
                                                <td>
                                                    @if ($auth->phone)
                                                        <a href="tel:{{ $auth->phone }}">{{ $auth->phone }}</a>
                                                    @else
                                                        <span class="text-muted">غير محدد</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($auth->email)
                                                        <a href="mailto:{{ $auth->email }}">{{ $auth->email }}</a>
                                                    @else
                                                        <span class="text-muted">غير محدد</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif


                </div>

            </div>

            {{-- عرض استبيانات العميل --}}
            @if ($surveyResponses->count() > 0)
                <div class="card">
                    <div class="card-header py-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="card-title mb-0">
                                <i class="ti ti-clipboard-list text-primary me-2"></i>
                                استبيانات العميل
                            </h6>
                            <div class="d-flex gap-2">
                                <span class="badge bg-label-info">
                                    {{ $surveysCount }} استبيان
                                </span>
                                <span class="badge bg-label-success">
                                    {{ $completedSurveysCount }} مكتمل
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="border-1 border-light border-dashed mb-2"></div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped small">
                                <thead>
                                    <tr>
                                        <th>الاستبيان</th>
                                        <th>الحالة</th>
                                        <th>تاريخ الإرسال</th>
                                        <th>تاريخ الإكمال</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($surveyResponses as $response)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm me-2">
                                                        <span class="avatar-initial bg-label-primary rounded-circle">
                                                            <i class="ti ti-clipboard-text"></i>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold">{{ $response->survey->title }}
                                                        </div>
                                                        <small
                                                            class="text-muted">{{ $response->survey->type->label() }}</small>
                                                    </div>
                                                </div>
                                            </td>

                                            <td>
                                                <span
                                                    class="badge badge-sm bg-label-{{ $response->status->color() }}  px-5 py-1">
                                                    {{ $response->status->label() }}
                                                </span>
                                            </td>

                                            <td>
                                                <small>{{ $response->created_at->format('Y/m/d H:i') }}</small>
                                                <br><small
                                                    class="text-muted">{{ $response->created_at->diffForHumans() }}</small>
                                            </td>

                                            <td>
                                                @if ($response->completed_at)
                                                    <small>{{ $response->completed_at->format('Y/m/d H:i') }}</small>
                                                    <br><small
                                                        class="text-muted">{{ $response->completed_at->diffForHumans() }}</small>
                                                @else
                                                    <span class="text-muted">لم يكمل بعد</span>
                                                @endif
                                            </td>

                                            <td>
                                                <div class="d-flex gap-1">
                                                    @if ($response->status->value === 'completed')
                                                        <div class="d-flex gap-1">
                                                            <a href="{{ route('operations-center.customers.getSurvey', $response->id) }}"
                                                                class="btn btn-sm btn-outline-primary"
                                                                title=" تفاصيل الإستبيان">
                                                                <i class="ti ti-external-link"></i>
                                                            </a>
                                                        </div>
                                                    @else
                                                        لم يتم الإكمال
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>



        <div class="col-12 col-md-4">
            {{-- الإحصائيات --}}
            <!-- بطاقة الإحصائيات -->
            <div class="card mb-3">
                <div class="card-header py-4">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-chart-bar text-primary me-2"></i>
                        إحصائيات سريعة
                    </h6>
                </div>
                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body">
                    <div class="row g-3 text-center">
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <div class="text-primary fs-4 fw-bold">
                                    {{ $offersCount }}</div>
                                <small class="text-muted">العروض</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <div class="text-primary fs-4 fw-bold">
                                    {{ $contractsCount }}
                                </div>
                                <small class="text-muted">العقود</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <div class="text-primary fs-4 fw-bold">
                                    {{ $powerOfAttorneysCount }}</div>
                                <small class="text-muted">الوكالات</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <div class="text-primary fs-4 fw-bold">
                                    {{ $lawsuitsCount }}</div>
                                <small class="text-muted">الدعاوى </small>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="border rounded p-2">
                                <div class="text-primary fs-4 fw-bold">
                                    {{ $surveysCount }}</div>
                                <small class="text-muted">الاستبيانات</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            {{-- سجل النشاط --}}
            <div class="card">
                <div class="card-header py-4">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-logs text-primary me-2"></i>
                        سجل النشاطات
                    </h6>
                </div>
                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body">
                    <ul class="timeline ">
                        <li class="timeline-item">
                            <span class="timeline-point bg-secondary"></span>
                            <div class="timeline-event">
                                <small class="timeline-title text-capitalize">
                                    تمت الاضافة بتاريخ
                                </small>
                                <small class="text-muted d-block">
                                    {{ $customer->created_at }}
                                </small>
                                <small>
                                    <b>اضيف بواسطة</b>
                                    <a href="{{ route('account.employee.profile', $customer->created_by) }}">
                                        {{ $customer->createdBy->employee->name }}
                                    </a>
                                </small>
                            </div>
                        </li>

                        @if ($customer->updated_by)
                            <li class="timeline-item">
                                <span class="timeline-point bg-success"></span>
                                <div class="timeline-event">
                                    <small class="timeline-title text-capitalize">
                                        تمت التعديل بتاريخ
                                    </small>
                                    <small class="text-muted d-block">
                                        {{ $customer->updated_at }}
                                    </small>
                                    <small>
                                        <b>التعديل بواسطة</b>
                                        <a href="{{ route('account.employee.profile', $customer->updated_by) }}">
                                            {{ $customer->updatedBy->name }}
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

@endsection
