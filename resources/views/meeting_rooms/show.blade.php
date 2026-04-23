@extends('layouts.layoutMaster')

@section('title', 'تفاصيل الحجز ')

@section('breadcrumb')
    <li>
        <a href="{{ route('meeting-rooms.index') }}">قاعات الإجتماعات</a>
    </li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">تفاصيل الحجز</a>
        <i class="ti ti-star favorite-icon" data-page-name="تفاصيل الحجز" data-page-url="{{ url()->current() }}"
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
            <div class="card ">
                <div class="card-header py-4">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-list-details text-warning me-2"></i>
                        تفاصيل الحجز
                    </h6>
                </div>

                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body px-5">
                    <div class="table-responsive">
                        <table class="table table-striped small text-center">
                            <tr>
                                <td class="fw-bold">عنوان الإجتماع</td>
                                <td>{{ $meeting_room->title }}</td>
                            </tr>

                            <tr>
                                <td class="fw-bold">القاعة </td>
                                <td>{{ $meeting_room->hall_name }}</td>
                            </tr>

                            <tr>
                                <td class="fw-bold">اليوم</td>
                                <td>{{ $meeting_room->date }}</td>
                            </tr>

                            <tr>
                                <td class="fw-bold">الساعة </td>
                                <td>{{ $meeting_room->from_time }}
                                    - {{ $meeting_room->to_time }}
                                </td>
                            </tr>

                            <tr>
                                <td class="fw-bold">ملاحظات الإجتماع</td>
                                <td>{{ $meeting_room->notes }}</td>
                            </tr>

                        </table>
                    </div>
                </div>
            </div>

            <div class="card  mt-3">
                <div class="card-header py-4 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-users text-warning me-2"></i>
                        أطراف الإجتماع
                    </h6>
                    <span class="badge bg-label-primary">{{ $meeting_room->participants->count() }}</span>
                </div>

                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body px-5">
                    @php
                        $groups = $meeting_room->participants->groupBy('type');
                        $emps = $groups->get('employees', collect());
                        $cuss = $groups->get('customers', collect());
                        $adds = $groups->get('additional', collect());
                    @endphp

                    {{-- الموظفون --}}
                    @if ($emps->isNotEmpty())
                        <h6 class="mb-2"><i class="ti ti-id text-secondary me-1"></i> الموظفون ({{ $emps->count() }})</h6>
                        <div class="table-responsive mb-3">
                            <table class="table table-sm table-striped small">
                                <thead>
                                    <tr class="text-center">
                                        <th>الاسم</th>
                                        <th>البريد الوظيفي</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($emps as $p)
                                        <tr class="text-center">
                                            <td>{{ $p->employee?->name ?? '—' }}</td>
                                            <td>{{ $p->employee?->work_email ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    {{-- العملاء --}}
                    @if ($cuss->isNotEmpty())
                        <h6 class="mb-2"><i class="ti ti-briefcase text-secondary me-1"></i> العملاء
                            ({{ $cuss->count() }})</h6>
                        <div class="table-responsive mb-3">
                            <table class="table table-sm table-striped small">
                                <thead>
                                    <tr class="text-center">
                                        <th>الاسم</th>
                                        <th>البريد</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($cuss as $p)
                                        <tr class="text-center">
                                            <td>{{ $p->customer?->name ?? '—' }}</td>
                                            <td>{{ $p->customer?->email ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    {{-- بريد إضافي --}}
                    @if ($adds->isNotEmpty())
                        <h6 class="mb-2"><i class="ti ti-mail text-secondary me-1"></i> بريد إضافي ({{ $adds->count() }})
                        </h6>
                        <ul class="list-unstyled small mb-0">
                            @foreach ($adds as $p)
                                <li class="mb-1">
                                    <i class="ti ti-point-filled text-muted me-1"></i>
                                    {{ $p->email }}
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    @if ($meeting_room->participants->isEmpty())
                        <div class="text-center text-muted">لا يوجد مدعوّون.</div>
                    @endif
                </div>
            </div>

        </div>

        <div class="col-12 col-md-4">
            <div class="d-flex flex-column gap-3">

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
                                <span class="timeline-point bg-secondary"></span>
                                <div class="timeline-event">
                                    <small class="timeline-title text-capitalize">تمت الاضافة بتاريخ</small>
                                    <small class="text-muted d-block">{{ $meeting_room->created_at }}</small>
                                    <small>
                                        <b>اضيف بواسطة</b>
                                        <a href="{{ route('account.employee.profile', $meeting_room->created_by) }}">
                                            {{ $meeting_room->createdBy->getRawNameAttribute() }}
                                        </a>
                                    </small>
                                </div>
                            </li>

                            @if ($meeting_room->updated_by)
                                <li class="timeline-item">
                                    <span class="timeline-point bg-success"></span>
                                    <div class="timeline-event">
                                        <small class="timeline-title text-capitalize">تمت التعديل بتاريخ</small>
                                        <small class="text-muted d-block">{{ $meeting_room->updated_at }}</small>
                                        <small>
                                            <b>التعديل بواسطة</b>
                                            <a href="{{ route('account.employee.profile', $meeting_room->updated_by) }}">
                                                {{ $meeting_room->updatedBy->getRawNameAttribute() }}
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
