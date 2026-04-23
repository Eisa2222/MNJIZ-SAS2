@extends('layouts.layoutMaster')

@section('title', 'تفاصيل بيانات الخصم')

@section('breadcrumb')
    <li><a href="#">الشؤون القانونية</a></li>

    <li><a href="{{ route('legal-affairs.opponents.index') }}">الخصوم</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">تفاصيل بيانات الخصم</a>
        <i class="ti ti-star favorite-icon" data-page-name="تفاصيل بيانات الخصم" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('content')
    {{-- <div class="row g-4 mb-4">
        <div class="row g-3 mb-4 d-flex justify-content-center">
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">الدعاوى</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">{{ $lawsuitsCount }}</h4>
                                </div>
                                <small class="mb-0">إجمالي الدعاوى الخاصة بالخصم</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class="ti ti-gavel ti-26px"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}
    <div class="row g-3">
        <div class="col-12 col-md-8">
            <div class="card">
                <div class="card-header py-4">

                    <h6 class="card-title mb-0">
                        <i class="ti ti-list-details text-warning me-2"></i>
                        تفاصيل الخصم
                    </h6>
                </div>

                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body px-5">
                    <div class="table-responsive">
                        <table class="table table-striped small text-center">
                            {{-- المعلومات الأساسية --}}
                            <tr>
                                <td class="fw-bold">نوع الخصم</td>
                                <td>
                                    <span
                                        class="badge bg-{{ $opponent->type->color() }}">{{ $opponent->type->label() }}</span>
                                </td>
                            </tr>

                            <tr>
                                <td class="fw-bold">الاسم</td>
                                <td>{{ $opponent->name }}</td>
                            </tr>



                            {{-- معلومات الاتصال --}}
                            <tr>
                                <td class="fw-bold">رقم الاتصال</td>
                                <td>
                                    @if ($opponent->contact_number)
                                        <a href="tel:{{ $opponent->contact_number }}">
                                            {{ $opponent->contact_number }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <td class="fw-bold">البريد الإلكتروني</td>
                                <td>
                                    @if ($opponent->email)
                                        <a href="mailto:{{ $opponent->email }}">
                                            {{ $opponent->email ?? '-' }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>



                            <tr>
                                <td class="fw-bold">المدينة</td>
                                <td>{{ $opponent->region->name ?? '-' }}</td>
                            </tr>

                            @if ($opponent->type->value == 'company')
                                <tr>
                                    <td class="fw-bold">رقم السجل التجاري</td>
                                    <td>{{ $opponent->commercial_registration ?? '-' }}</td>
                                </tr>

                                <tr>
                                    <td class="fw-bold">الرقم الموحد</td>
                                    <td>{{ $opponent->unified_number ?? '-' }}</td>
                                </tr>
                            @endif



                            @if ($opponent->type->value == 'individual')
                                <tr>
                                    <td class="fw-bold">رقم الهوية</td>
                                    <td>{{ $opponent->identity_number ?? '-' }}</td>
                                </tr>
                            @endif

                            <tr>
                                <td class="fw-bold">نبذه عن الخصم</td>
                                <td>
                                    {{ $opponent->bio ?? '-' }}
                                </td>
                            </tr>

                        </table>
                    </div>

                    {{-- عرض المفوضين للشخصية الاعتبارية --}}
                    @if ($opponent->type->value == 'company' && $opponent->authorizations->count() > 0)
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
                                        @foreach ($opponent->authorizations as $auth)
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
        </div>



        <div class="col-12 col-md-4">
            {{-- الإحصائيات --}}


            <!-- بطاقة الإحصائيات -->
            <div class="card mb-3">
                <div class="card-header py-4">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-chart-bar text-info me-2"></i>
                        إحصائيات سريعة
                    </h6>
                </div>
                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body">
                    <div class="row g-3 text-center">
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <div class="text-primary fs-4 fw-bold">{{ $lawsuitsCount }}</div>
                                <small class="text-muted">الدعاوى</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- سجل النشاط --}}
            <div class="card">
                <div class="card-header py-4">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-logs text-warning me-2"></i>
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
                                    {{ $opponent->created_at }}
                                </small>
                                @if ($opponent->created_by)
                                    <small>
                                        <b>اضيف بواسطة</b>
                                        <a href="{{ route('account.employee.profile', $opponent->created_by) }}">
                                            {{ $opponent->createdBy->employee->name }}
                                        </a>
                                    </small>
                                @endif

                            </div>
                        </li>

                        @if ($opponent->updated_by)
                            <li class="timeline-item">
                                <span class="timeline-point bg-success"></span>
                                <div class="timeline-event">
                                    <small class="timeline-title text-capitalize">
                                        تمت التعديل بتاريخ
                                    </small>
                                    <small class="text-muted d-block">
                                        {{ $opponent->updated_at }}
                                    </small>
                                    <small>
                                        <b>التعديل بواسطة</b>
                                        <a href="{{ route('account.employee.profile', $opponent->updated_by) }}">
                                            {{ $opponent->updatedBy->name }}
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
