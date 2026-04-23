@extends('layouts.layoutMaster')

@section('title', 'تفاصيل رصيد الإجازة')

@section('breadcrumb')
<li><a href="#"> الموارد البشرية</a></li>
<li><a href="{{ route('hr.leave-balances.index') }}">أرصدة الإجازات</a></li>
<li class="breadcrumb-item d-flex align-items-center">
    <a href="#">تفاصيل رصيد الإجازة</a>
    <i class="ti ti-star favorite-icon" data-page-name="تفاصيل رصيد الإجازة" data-page-url="{{ url()->current() }}"
        onclick="toggleFavorite(event, this)"></i>
</li>
@endsection

@section('vendor-style')
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
<script>
    $(document).ready(function() {
            // تهيئة جدول البيانات
            $('#leaveBalanceLogsTable').DataTable({
                order: [
                    [0, 'desc']
                ],
                responsive: true,
                // تعريب الجدول
                language: {
                    "sProcessing": "جارٍ التحميل...",
                    "sLengthMenu": "أظهر _MENU_ مدخلات",
                    "sZeroRecords": "لم يعثر على أية سجلات",
                    "sInfo": "إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل",
                    "sInfoEmpty": "يعرض 0 إلى 0 من أصل 0 سجل",
                    "sInfoFiltered": "(منتقاة من مجموع _MAX_ مُدخل)",
                    "sInfoPostFix": "",
                    "sSearch": "بحث:",
                    "sUrl": "",
                    "oPaginate": {
                        "sFirst": "الأول",
                        "sPrevious": "السابق",
                        "sNext": "التالي",
                        "sLast": "الأخير"
                    }
                },
                dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                lengthMenu: [
                    [10, 25, 50, -1],
                    ['10', '25', '50', 'الكل']
                ]
            });

            // تفعيل التبويبات
            if (window.location.hash) {
                $('.nav-tabs a[href="' + window.location.hash + '"]').tab('show');
            }
            $('.nav-tabs a').on('shown.bs.tab', function(e) {
                history.pushState(null, null, e.target.hash);
            });
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
</style>
<div class="row">
    <!-- تبويبات جانبية -->
    <div class="col-12 col-md-3 col-xl-3">
        <div>
            <ul class="nav nav-tabs flex-column border-bottom-0">
                <!-- تبويب تفاصيل الرصيد -->
                <li class="card rounded-0 mb-3 nav-item shadow-none">
                    <a class="py-3 nav-link fw-bold active m-0" data-bs-toggle="tab" href="#balanceDetails">
                        <i class="ti ti-info-circle ti-sm me-1"></i>
                        <span class="align-middle">تفاصيل الرصيد</span>
                    </a>
                </li>
                <!-- تبويب سجل التغييرات -->
                <li class="card rounded-0 mb-3 nav-item shadow-none">
                    <a class="py-3 nav-link fw-bold m-0" data-bs-toggle="tab" href="#balanceLogs">
                        <i class="ti ti-history ti-sm me-1"></i>
                        <span class="align-middle">سجل النشاطات</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- المحتوى الرئيسي -->
    <div class="col-12 col-md-9 col-xl-9 order-1 order-md-0 mb-5">
        <div class="tab-content p-0">
            {{-- تبويب تفاصيل الرصيد --}}
            <div class="tab-pane fade show active" id="balanceDetails">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="fw-bold mb-0">تفاصيل رصيد الإجازة</h5>
                    </div>

                    <div class="card-body">
                        <!-- بطاقة معلومات الموظف -->
                        <div class="card border mb-4">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        @php
                                        $default_image = asset('assets/img/branding/Alburhan-Logo.png');

                                        if ($balance->employee->profile_picture) {
                                        $image_path = storage_path(
                                        'app/public/' . $balance->employee->profile_picture,
                                        );
                                        $image_exists = file_exists($image_path);
                                        $avatar = $image_exists
                                        ? asset('storage/' . $balance->employee->profile_picture)
                                        : $default_image;
                                        } else {
                                        $avatar = $default_image;
                                        }
                                        @endphp
                                        <img src="{{ $avatar }}" alt="صورة الموظف" class="rounded-circle" width="70"
                                            height="70">
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex flex-column justify-content-center"
                                            style="min-height: 70px;">
                                            <h5 class="mb-1 mt-2 fw-bold">
                                                {{ $balance->employee->rawName }}
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- بيانات الرصيد في بطاقات -->
                        <div class="row mb-4">
                            <!-- إجمالي أيام الإجازة -->
                            <div class="col-md-4 mb-3">
                                <div class="card border h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start">
                                            <i class="ti ti-calendar me-2"></i>
                                            <div>
                                                <h6 class="mb-2">إجمالي أيام الإجازة</h6>
                                                <h4 class="mb-0 fw-bold">
                                                    {{ is_int($balance->total_days) ? $balance->total_days :
                                                    number_format($balance->total_days, 4) }}
                                                </h4>
                                                @if ($logs->first())
                                                @php
                                                $change =
                                                $logs->first()->new_total_days -
                                                $logs->first()->old_total_days;
                                                $changeClass =
                                                $change > 0
                                                ? 'text-success'
                                                : ($change < 0 ? 'text-danger' : '' ); $changeSign=$change> 0 ? '+' :
                                                    '';
                                                    @endphp
                                                    @if ($change != 0)
                                                    <small class="{{ $changeClass }}">
                                                        {{ $changeSign }}{{ is_int($change) ? $change :
                                                        number_format($change, 4) }}
                                                    </small>
                                                    @endif
                                                    @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- الأيام المستخدمة -->
                            <div class="col-md-4 mb-3">
                                <div class="card border h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start">
                                            <i class="ti ti-clipboard-check me-2"></i>
                                            <div>
                                                <h6 class="mb-2">الأيام المستخدمة</h6>
                                                <h4 class="mb-0 fw-bold">
                                                    {{ is_int($balance->used_days) ? $balance->used_days :
                                                    number_format($balance->used_days, 4) }}
                                                </h4>
                                                @if ($logs->first())
                                                @php
                                                $change =
                                                $logs->first()->new_used_days -
                                                $logs->first()->old_used_days;
                                                $changeClass =
                                                $change < 0 ? 'text-success' : ($change> 0
                                                    ? 'text-danger'
                                                    : '');
                                                    $changeSign = $change > 0 ? '+' : '';
                                                    @endphp
                                                    @if ($change != 0)
                                                    <small class="{{ $changeClass }}">
                                                        {{ $changeSign }}{{ is_int($change) ? $change :
                                                        number_format($change, 4) }}
                                                    </small>
                                                    @endif
                                                    @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- الأيام المتبقية -->
                            <div class="col-md-4 mb-3">
                                <div class="card border h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start">
                                            <i class="ti ti-calendar-time me-2"></i>
                                            <div>
                                                <h6 class="mb-2">الأيام المتبقية</h6>
                                                <h4
                                                    class="mb-0 fw-bold {{ $balance->remaining_days < 0 ? 'text-danger' : '' }}">
                                                    {{ is_int($balance->remaining_days) ? $balance->remaining_days :
                                                    number_format($balance->remaining_days, 4) }}
                                                </h4>
                                                @if ($logs->first())
                                                @php
                                                $change =
                                                $logs->first()->new_remaining_days -
                                                $logs->first()->old_remaining_days;
                                                $changeClass =
                                                $change > 0
                                                ? 'text-success'
                                                : ($change < 0 ? 'text-danger' : '' ); $changeSign=$change> 0 ? '+' :
                                                    '';
                                                    @endphp
                                                    @if ($change != 0)
                                                    <small class="{{ $changeClass }}">
                                                        {{ $changeSign }}{{ is_int($change) ? $change :
                                                        number_format($change, 4) }}
                                                    </small>
                                                    @endif
                                                    @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- تبويب سجل التغييرات --}}
            <div class="tab-pane fade" id="balanceLogs">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="fw-bold mb-0">سجل تحديثات رصيد الإجازة</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="leaveBalanceLogsTable" class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th width="150">التاريخ والوقت</th>
                                        <th>نوع العملية</th>
                                        <th>بواسطة</th>
                                        <th>إجمالي الرصيد (قبل)</th>
                                        <th>إجمالي الرصيد (بعد)</th>
                                        <th>الرصيد المستخدم (قبل)</th>
                                        <th>الرصيد المستخدم (بعد)</th>
                                        <th>الرصيد المتبقي (قبل)</th>
                                        <th>الرصيد المتبقي (بعد)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($logs as $log)
                                    <tr>
                                        <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                        <td>
                                            @if($log->action === 'daily_accrual')
                                            <span class="badge bg-success">ترصيد يومي</span>
                                            @elseif($log->action === 'zero_balance_creation')
                                            <span class="badge bg-primary">إنشاء رصيد</span>
                                            @elseif($log->action === 'carry_forward')
                                            <span class="badge bg-warning">ترحيل</span>
                                            @elseif($log->action === 'manual_update')
                                            <span class="badge bg-info">تحديث يدوي</span>
                                            @else
                                            <span class="badge bg-secondary">{{ $log->action }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($log->user_id)
                                            {{ $log->user->name }}
                                            @else
                                            <span class="badge bg-dark">نظام تلقائي</span>
                                            @endif
                                        </td>
                                        <td>{{ is_int($log->old_total_days) ? $log->old_total_days :
                                            number_format($log->old_total_days, 4) }}
                                        </td>
                                        <td
                                            class="{{ $log->new_total_days > $log->old_total_days ? 'text-success fw-bold' : ($log->new_total_days < $log->old_total_days ? 'text-danger fw-bold' : '') }}">
                                            {{ is_int($log->new_total_days) ? $log->new_total_days :
                                            number_format($log->new_total_days, 4) }}
                                        </td>
                                        <td>{{ is_int($log->old_used_days) ? $log->old_used_days :
                                            number_format($log->old_used_days, 4) }}
                                        </td>
                                        <td
                                            class="{{ $log->new_used_days < $log->old_used_days ? 'text-success fw-bold' : ($log->new_used_days > $log->old_used_days ? 'text-danger fw-bold' : '') }}">
                                            {{ is_int($log->new_used_days) ? $log->new_used_days :
                                            number_format($log->new_used_days, 4) }}
                                        </td>
                                        <td>{{ is_int($log->old_remaining_days) ? $log->old_remaining_days :
                                            number_format($log->old_remaining_days, 4) }}
                                        </td>
                                        <td
                                            class="{{ $log->new_remaining_days > $log->old_remaining_days ? 'text-success fw-bold' : ($log->new_remaining_days < $log->old_remaining_days ? 'text-danger fw-bold' : '') }}">
                                            {{ is_int($log->new_remaining_days) ? $log->new_remaining_days :
                                            number_format($log->new_remaining_days, 4) }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection