@extends('layouts.layoutMaster')

@section('title', 'تفاصيل الوكالة')

@section('breadcrumb')
    <li><a href="#">الشؤون القانونية</a></li>
    <li><a href="{{ route('legal-affairs.power-attorney.index') }}">الوكالات</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">تفاصيل الوكالة</a>
        <i class="ti ti-star favorite-icon" data-page-name="تفاصيل الوكالة" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
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
                        <i class="ti ti-file-certificate text-warning me-2"></i>
                        تفاصيل الوكالة
                    </h6>
                </div>

                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body px-5">
                    <div class="table-responsive">
                        <table class="table table-striped small text-center">
                            <tr>
                                <td class="fw-bold">اسم الوكالة</td>
                                <td>{{ $powerAttorney->power_name }}</td>
                            </tr>

                            <tr>
                                <td class="fw-bold">رقم الوكالة</td>
                                <td><span class="badge bg-info">{{ $powerAttorney->power_number }}</span></td>
                            </tr>

                            <tr>
                                <td class="fw-bold">تاريخ الإصدار</td>
                                <td>{{ $powerAttorney->hijri_date_issued ?? $powerAttorney->date_issued }}</td>
                            </tr>

                            <tr>
                                <td class="fw-bold">تاريخ الانتهاء</td>
                                <td>
                                    @if ($powerAttorney->date_expiry)
                                        {{ $powerAttorney->hijri_date_expiry ?? $powerAttorney->date_expiry }}
                                    @else
                                        <span class="text-muted">غير محدد</span>
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <td class="fw-bold">الحالة</td>
                                <td>
                                    <span class="badge bg-{{ $powerAttorney->status->color() }}">
                                        {{ $powerAttorney->status->label() }}
                                    </span>
                                </td>
                            </tr>

                            @if ($powerAttorney->file_attachment)
                                <tr>
                                    <td class="fw-bold">المرفق</td>
                                    <td>
                                        <a href="{{ asset('storage/' . $powerAttorney->file_attachment) }}" target="_blank"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="ti ti-download me-1"></i>
                                            تحميل المرفق
                                        </a>
                                    </td>
                                </tr>
                            @endif

                            @if ($powerAttorney->notes)
                                <tr>
                                    <td class="fw-bold">الملاحظات</td>
                                    <td>{{ $powerAttorney->notes }}</td>
                                </tr>
                            @endif
                        </table>
                    </div>

                    {{-- عرض العملاء --}}
                    @if ($powerAttorney->customers->count() > 0)
                        <div class="mt-4">
                            <div class="border-1 border-light border-dashed mb-2"></div>
                            <h6 class="mb-3">
                                <i class="ti ti-users text-primary me-2"></i>
                                العملاء ({{ $powerAttorney->customers->count() }})
                            </h6>
                            <div class="border-1 border-light border-dashed mb-2"></div>

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered small">
                                    <thead class="table-light">
                                        <tr>
                                            <th>الاسم</th>
                                            <th>رقم الهاتف</th>
                                            <th>البريد الإلكتروني</th>
                                            <th>المدينة</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($powerAttorney->customers as $customer)
                                            <tr>
                                                <td>
                                                    <a
                                                        href="{{ route('operations-center.customers.show', $customer->id) }}">
                                                        {{ $customer->name }}
                                                    </a>
                                                </td>
                                                <td>
                                                    @if ($customer->contact_number)
                                                        <a
                                                            href="tel:{{ $customer->contact_number }}">{{ $customer->contact_number }}</a>
                                                    @else
                                                        <span class="text-muted">غير محدد</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($customer->email)
                                                        <a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a>
                                                    @else
                                                        <span class="text-muted">غير محدد</span>
                                                    @endif
                                                </td>
                                                <td>{{ $customer->region->name ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    {{-- عرض الوكلاء --}}
                    @if ($powerAttorney->agents->count() > 0)
                        <div class="mt-4">
                            <div class="border-1 border-light border-dashed mb-2"></div>
                            <h6 class="mb-3">
                                <i class="ti ti-user-check text-info me-2"></i>
                                الوكلاء ({{ $powerAttorney->agents->count() }})
                            </h6>
                            <div class="border-1 border-light border-dashed mb-2"></div>

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered small">
                                    <thead class="table-light">
                                        <tr>
                                            <th>الاسم</th>
                                            <th>رقم الهاتف</th>
                                            <th>البريد الإلكتروني</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($powerAttorney->agents as $agent)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('account.employee.profile', $agent->id) }}">
                                                        {{ $agent->name }}
                                                    </a>
                                                </td>
                                                <td>
                                                    @if ($agent->mobile)
                                                        <a href="tel:{{ $agent->mobile }}">{{ $agent->mobile }}</a>
                                                    @else
                                                        <span class="text-muted">غير محدد</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($agent->work_email)
                                                        <a
                                                            href="mailto:{{ $agent->work_email }}">{{ $agent->work_email }}</a>
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
                                <div class="text-primary fs-4 fw-bold">{{ $powerAttorney->lawsuits->count() ?? 0 }}</div>
                                <small class="text-muted">الدعاوى</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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
                                    {{ $powerAttorney->created_at }}
                                </small>
                                <small>
                                    <b>اضيف بواسطة</b>
                                    @if ($powerAttorney->created_by)
                                        <a href="{{ route('account.employee.profile', $powerAttorney->created_by) }}">
                                            {{ $powerAttorney->createdBy->employee->name }}
                                        </a>
                                    @else
                                        غير محدد
                                    @endif

                                </small>
                            </div>
                        </li>

                        @if ($powerAttorney->updated_by)
                            <li class="timeline-item">
                                <span class="timeline-point bg-success"></span>
                                <div class="timeline-event">
                                    <small class="timeline-title text-capitalize">
                                        تمت التعديل بتاريخ
                                    </small>
                                    <small class="text-muted d-block">
                                        {{ $powerAttorney->updated_at }}
                                    </small>
                                    <small>
                                        <b>التعديل بواسطة</b>
                                        <a href="{{ route('account.employee.profile', $powerAttorney->updated_by) }}">
                                            {{ $powerAttorney->updatedBy->name }}
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
