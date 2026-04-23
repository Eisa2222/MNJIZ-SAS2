@extends('layouts.layoutMaster')

@section('title', 'سجل تعديلات الحضور')

@section('breadcrumb')
    <li><a href="#">الموارد البشرية</a></li>
    <li><a href="{{ route('attendances.index') }}">الحضور والانصراف</a></li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">سجل تعديلات الحضور</a>
        <i class="ti ti-star favorite-icon" data-page-name="سجل تعديلات الحضور" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection


@section('page-script')
    <script>
        $(function() {
            $('#attendanceLogsTable').DataTable({
                order: [
                    [0, 'desc']
                ],
                responsive: true,
                language: {
                    url: "{{ asset('assets/json/ar.json') }}"
                },
                dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                lengthMenu: [
                    [10, 25, 50, -1],
                    ['10', '25', '50', 'الكل']
                ]
            });
            if (window.location.hash) {
                $('.nav-tabs a[href="' + window.location.hash + '"]').tab('show')
            }
            $('.nav-tabs a').on('shown.bs.tab', e => history.pushState(null, null, e.target.hash));
        });
    </script>
@endsection

@section('content')
    <style>
        .nav-tabs .nav-link,
        .nav-pills .nav-link {
            justify-content: start;
            margin-bottom: 10px;
        }

        .nav-tabs .nav-link.active,
        .nav-tabs .nav-link.active:hover,
        .nav-tabs .nav-link.active:focus {
            box-shadow: none;
            border-left: 5px solid;
        }

        .changes-table td {
            vertical-align: middle
        }

        .value-before {
            background: #ffebee;
            padding: .25rem .5rem;
            border-radius: .25rem;
            color: #e53935
        }

        .value-after {
            background: #e8f5e9;
            padding: .25rem .5rem;
            border-radius: .25rem;
            color: #43a047
        }
    </style>
    <div class="row">
        <div class="col-12 col-md-3 col-xl-3">
            <div>
                <ul class="nav nav-tabs flex-column border-bottom-0">
                    <!-- تبويب تفاصيل الرصيد -->
                    <li class="card rounded-0 mb-3 nav-item shadow-none">
                        <a class="py-3 nav-link fw-bold active m-0" data-bs-toggle="tab" href="#attendanceDetails">
                            <i class="ti ti-info-circle ti-sm me-1"></i><span>تفاصيل السجل</span>
                        </a>
                    </li>
                    <!-- تبويب سجل التغييرات -->
                    <li class="card rounded-0 mb-3 nav-item shadow-none">
                        <a class="py-3 nav-link fw-bold m-0" data-bs-toggle="tab" href="#attendanceLogs">
                            <i class="ti ti-history ti-sm me-1"></i><span>سجل التغيرات</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        {{-- المحتوى --}}
        <div class="col-12 col-md-9 col-xl-9 order-1 order-md-0 mb-5">
            <div class="tab-content p-0">
                {{-- تفاصيل السجل --}}
                <div class="tab-pane fade show active" id="attendanceDetails">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0">تفاصيل سجل الحضور</h5>
                        </div>
                        <div class="card-body">
                            {{-- بطاقة الموظف --}}
                            <div class="card border mb-4">
                                <div class="card-body d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        @php
                                            $defaultImg = asset('assets/img/branding/Alburhan-Logo.png');
                                            $avatar = $attendance->user->profile_picture
                                                ? asset('storage/' . $attendance->user->profile_picture)
                                                : $defaultImg;
                                        @endphp
                                        <img src="{{ $avatar }}" class="rounded-circle" width="70" height="70">
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="fw-bold mb-0">{{ $attendance->user->name ?? 'غير معروف' }}</h5>
                                        <small class="text-muted">{{ $attendance->date->format('Y-m-d') }}
                                            ({{ $attendance->date->locale('ar')->dayName }})</small>
                                    </div>
                                </div>
                            </div>

                            {{-- الحالة الحالية --}}
                            <div class="table-responsive mb-4">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr class="table-light">
                                            <th>حالة الحضور</th>
                                            <th>وقت الدخول</th>
                                            <th>وقت الخروج</th>
                                            <th>عدد ساعات العمل</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            @php $badge=['present'=>'success','absent'=>'danger','leave'=>'primary']; @endphp
                                            <td><span
                                                    class="badge bg-{{ $badge[$attendance->day_status] ?? 'secondary' }}">{{ ['present' => 'حضور', 'absent' => 'غياب', 'leave' => 'إجازة'][$attendance->day_status] ?? 'غير معروف' }}</span>
                                            </td>
                                            <td>{{ $attendance->check_in_time?->format('H:i:s') ?? '—' }}</td>
                                            <td>{{ $attendance->check_out_time?->format('H:i:s') ?? '—' }}</td>
                                            <td>
                                                @if ($attendance->check_in_time && $attendance->check_out_time)
                                                    @php $m=\Carbon\Carbon::parse($attendance->check_out_time)->diffInMinutes(\Carbon\Carbon::parse($attendance->check_in_time)); @endphp
                                                    {{ floor($m / 60) . ' س و ' . $m % 60 . ' د' }}
                                                @else
                                                    —
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- سجل التغيرات --}}
                <div class="tab-pane fade" id="attendanceLogs">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0">سجل التغيرات</h5>
                        </div>
                        <div class="card-body">
                            @if ($logs->count())
                                <div class="table-responsive">
                                    <table id="attendanceLogsTable" class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th width="150">التاريخ والوقت</th>
                                                <th width="180">المُعدِّل</th>
                                                <th width="200">سبب التعديل</th>
                                                <th>التغييرات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $fmt = fn($v) => $v ? \Carbon\Carbon::parse($v)->format('H:i:s') : '—';
                                                $txt = [
                                                    'present' => 'حضور',
                                                    'absent' => 'غياب',
                                                    'leave' => 'إجازة',
                                                ];
                                            @endphp
                                            @foreach ($logs as $log)
                                                @php
                                                    $old = $log->old_data;
                                                    $new = $log->new_data;
                                                @endphp
                                                <tr>
                                                    <td style="text-align: center; vertical-align: middle;">
                                                        {{ $log->edited_at->format('Y-m-d') }}<br>
                                                        <small
                                                            class="text-muted">{{ $log->edited_at->format('H:i:s') }}</small>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            @php
                                                                $editorImg =
                                                                    $log->editor && $log->editor->profile_picture
                                                                        ? asset(
                                                                            'storage/' . $log->editor->profile_picture,
                                                                        )
                                                                        : asset(
                                                                            'assets/img/branding/Alburhan-Logo.png',
                                                                        );
                                                            @endphp
                                                            <div class="avatar avatar-sm me-2">
                                                                <img src="{{ $editorImg }}" class="rounded-circle">
                                                            </div>
                                                            <div
                                                                style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 120px;">
                                                                {{ $log->editor->name ?? 'غير معروف' }}</div>
                                                        </div>
                                                    </td>
                                                    <td
                                                        style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px;">
                                                        {{ $log->edit_reason ?: 'لم يتم تحديد سبب' }}</td>
                                                    <td>
                                                        <table class="table changes-table mb-0 border">
                                                            @if (($old['day_status'] ?? null) !== ($new['day_status'] ?? null))
                                                                <tr>
                                                                    <td class="fw-bold" width="120"
                                                                        style="padding: 12px 15px;">
                                                                        <i class="ti ti-calendar-stats me-1"></i>
                                                                        حالة الحضور
                                                                    </td>
                                                                    <td style="padding: 12px 15px;">
                                                                        <span class="value-before"
                                                                            style="padding: 8px 12px; display: inline-block; min-width: 80px; text-align: center;">{{ $txt[$old['day_status']] ?? '—' }}</span>
                                                                        <i class="ti ti-arrow-right mx-3"
                                                                            style="margin: 0 15px;"></i>
                                                                        <span class="value-after"
                                                                            style="padding: 8px 12px; display: inline-block; min-width: 80px; text-align: center;">{{ $txt[$new['day_status']] ?? '—' }}</span>
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                            @if (($old['check_in_time'] ?? null) !== ($new['check_in_time'] ?? null))
                                                                <tr>
                                                                    <td class="fw-bold" width="120"
                                                                        style="padding: 12px 15px;">
                                                                        <i class="ti ti-login me-1"></i>
                                                                        وقت الدخول
                                                                    </td>
                                                                    <td style="padding: 12px 15px;">
                                                                        <span class="value-before"
                                                                            style="padding: 8px 12px; display: inline-block; min-width: 80px; text-align: center;">{{ $fmt($old['check_in_time'] ?? null) }}</span>
                                                                        <i class="ti ti-arrow-right mx-3"
                                                                            style="margin: 0 15px;"></i>
                                                                        <span class="value-after"
                                                                            style="padding: 8px 12px; display: inline-block; min-width: 80px; text-align: center;">{{ $fmt($new['check_in_time'] ?? null) }}</span>
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                            @if (($old['check_out_time'] ?? null) !== ($new['check_out_time'] ?? null))
                                                                <tr>
                                                                    <td class="fw-bold" width="120"
                                                                        style="padding: 12px 15px;">
                                                                        <i class="ti ti-logout me-1"></i>
                                                                        وقت الخروج
                                                                    </td>
                                                                    <td style="padding: 12px 15px;">
                                                                        <span class="value-before"
                                                                            style="padding: 8px 12px; display: inline-block; min-width: 80px; text-align: center;">{{ $fmt($old['check_out_time'] ?? null) }}</span>
                                                                        <i class="ti ti-arrow-right mx-3"
                                                                            style="margin: 0 15px;"></i>
                                                                        <span class="value-after"
                                                                            style="padding: 8px 12px; display: inline-block; min-width: 80px; text-align: center;">{{ $fmt($new['check_out_time'] ?? null) }}</span>
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                            @if (
                                                                !isset($old['day_status']) &&
                                                                    !isset($new['day_status']) &&
                                                                    !isset($old['check_in_time']) &&
                                                                    !isset($new['check_in_time']) &&
                                                                    !isset($old['check_out_time']) &&
                                                                    !isset($new['check_out_time']))
                                                                <tr>
                                                                    <td colspan="2" class="text-center text-muted"
                                                                        style="padding: 12px 15px;">
                                                                        <i class="ti ti-info-circle me-1"></i>
                                                                        لم يتم تسجيل تغييرات محددة
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                        </table>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-info mb-0">
                                    <i class="ti ti-info-circle me-2"></i>لا توجد تغيرات مسجلة على هذا السجل.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
