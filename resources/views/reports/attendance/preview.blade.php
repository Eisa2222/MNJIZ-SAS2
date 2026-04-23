<!DOCTYPE html>
<html lang="ar">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير حضور | {{ $employee ? $employee->name : 'جميع الموظفين' }}</title>
    <!-- إضافة خط جوجل -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        /* تنسيقات أساسية بسيطة */
        body {
            font-family: 'Tajawal', 'DejaVu Sans', sans-serif;
            direction: rtl;
            margin: 20px;
            padding: 0;
            color: #333;
            line-height: 1.5;
        }

        /* ترويسة التقرير */
        .report-header {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }

        .report-header h2 {
            margin: 0 0 5px 0;
            font-size: 20px;
            font-weight: 700;
        }

        .date-range {
            color: #666;
            font-size: 14px;
        }

        /* جدول البيانات الرئيسي */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            border: 1px solid #ddd;
            font-size: 12px;
        }

        .data-table th {
            background-color: #444;
            color: #fff;
            padding: 8px 6px;
            font-weight: 500;
            text-align: center;
            border: 1px solid #555;
            white-space: nowrap;
        }

        .data-table td {
            padding: 8px 6px;
            text-align: center;
            border: 1px solid #ddd;
        }

        /* الأنماط الأساسية */
        .late {
            color: #e74c3c;
            font-weight: 500;
        }

        .early-arrival {
            color: #3498db;
            font-weight: 500;
        }

        .early-leave {
            color: #f39c12;
            font-weight: 500;
        }

        .overtime {
            color: #2ecc71;
            font-weight: 500;
        }

        .absent {
            color: #9b59b6;
            font-weight: bold;
            background-color: #fcfcfc;
        }

        .leave {
            color: #3498db;
            font-weight: bold;
            background-color: #e8f4f8;
        }

        .work-hours {
            color: #3498db;
            font-weight: bold;
        }

        /* تنسيقات الحالات الجديدة */
        .status-missing-checkout {
            color: #e74c3c;
            font-weight: 600;
            font-size: 11px;
        }

        .status-insufficient-hours {
            color: #f39c12;
            font-weight: 600;
            font-size: 11px;
        }

        .status-present {
            color: #27ae60;
            font-weight: 600;
        }

        .status-remote-work {
            color: #16a085;
            font-weight: 600;
        }

        .no-records {
            text-align: center;
            padding: 20px;
            color: #777;
            font-style: italic;
        }

        .report-footer {
            margin-top: 20px;
            font-size: 12px;
            color: #777;
            text-align: center;
        }

        .total-row {
            background-color: #f5f5f5;
            font-weight: bold;
        }

        .total-row td {
            border-top: 2px solid #444;
            padding: 10px 6px;
            font-size: 13px;
        }

        .total-value {
            font-size: 14px;
            font-weight: bold;
        }

        .work-hours {
            color: #3498db;
        }

        .overtime-hours {
            color: #2ecc71;
        }

        /* عمود نوع الإجازة */
        .leave-type {
            color: #2c3e50;
            font-weight: 600;
            background-color: #f0f8ff;
        }

        .remote-work {
            color: #16a085;
            font-weight: 600;
            background-color: #e8f5f5;
        }
    </style>
</head>

<body>
    <!-- ترويسة التقرير -->
    <div class="report-header">
        <h2>تقرير حضور | {{ $employee ? $employee->name : 'جميع الموظفين' }}</h2>
        <span class="date-range">الفترة: {{ $dateFrom }} إلى {{ $dateTo }}</span>
    </div>

    @php
        $totalWorkMinutes = 0;
        $totalOvertimeMinutes = 0;
        $formatTime = fn($mins) => sprintf('%02d:%02d', intdiv(max(0, $mins), 60), max(0, $mins) % 60);

        // جلب معلومات الإجازات
        $leaveInfo = [];
        $records->load('leaveType'); // تحميل العلاقة إذا لم تكن محملة

        foreach ($records as $record) {
            if ($record->day_status == 'leave') {
                if ($record->leaveType) {
                    $leaveInfo[$record->user_id . '_' . $record->date->format('Y-m-d')] = $record->leaveType->name;
                } else {
                    // fallback للطريقة القديمة
                    $leaveType = DB::table('leave_requests')
                        ->join('settings_leave_types', 'leave_requests.leave_type_id', '=', 'settings_leave_types.id')
                        ->join('employees', 'leave_requests.employee_id', '=', 'employees.id')
                        ->where('employees.user_id', $record->user_id)
                        ->where('leave_requests.status', 'approved')
                        ->whereDate('leave_requests.start_date', '<=', $record->date)
                        ->whereDate('leave_requests.end_date', '>=', $record->date)
                        ->value('settings_leave_types.name');

                    $leaveInfo[$record->user_id . '_' . $record->date->format('Y-m-d')] = $leaveType ?: 'إجازة';
                }
            }
        }
    @endphp

    <table class="data-table">
        <thead>
            <tr>
                @if (!$employee)
                    <th>#</th>
                @endif
                <th>التاريخ</th>
                <th>اسم الموظف</th>
                <th>رقم الموظف</th>
                <th>وقت الدخول</th>
                <th>وقت الخروج</th>
                <th>إجازة</th>
                <th>التأخير</th>
                <th>الحضور المبكر</th>
                <th>الخروج المبكر</th>
                <th>الوقت الإضافي</th>
                <th>مدة العمل</th>
                <th>الحالة</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $index => $record)
                @php
                    // اسم ورقم الموظف
                    $empName = $employee ? $employee->name : $employeesData[$record->user_id]['name'] ?? '---';
                    $empId = $employee ? $employee->id : $employeesData[$record->user_id]['id'] ?? $record->user_id;

                    // حساب مدة العمل - بسيط بدون حالات وصفية
                    $workTimeDisplay = '—';

                    if ($record->check_in_time && $record->check_out_time) {
                        // حساب مدة العمل الفعلية
                        $workMins = \Carbon\Carbon::parse($record->check_out_time)->diffInMinutes(
                            \Carbon\Carbon::parse($record->check_in_time),
                        );

                        $workTimeDisplay = $formatTime($workMins);
                        $totalWorkMinutes += $workMins;
                    }

                    $totalOvertimeMinutes += $record->overtime_minutes;

                    // جلب نوع الإجازة
                    $leaveType = '—';
                    $leaveClass = '';
                    if ($record->day_status == 'leave') {
                        $leaveKey = $record->user_id . '_' . $record->date->format('Y-m-d');
                        $leaveType = $leaveInfo[$leaveKey] ?? 'إجازة';

                        // تحديد فئة CSS مناسبة لنوع الإجازة
                        if (stripos($leaveType, 'عن بعد') !== false || stripos($leaveType, 'عمل عن بعد') !== false) {
                            $leaveClass = 'remote-work';
                        } else {
                            $leaveClass = 'leave-type';
                        }
                    }

                    // تحديد الحالة مع الحالات الجديدة
                    $statusLabel = '—';
                    $statusClass = '';

                    if ($record->day_status == 'present') {
                        if ($record->check_in_time && !$record->check_out_time) {
                            $statusLabel = 'مفقود توقيع الخروج';
                            $statusClass = 'status-missing-checkout';
                        } elseif ($record->check_in_time && $record->check_out_time) {
                            // حساب مدة الدوام المطلوبة
                            $requiredMinutes = \Carbon\Carbon::parse($record->scheduled_start_time)->diffInMinutes(
                                \Carbon\Carbon::parse($record->scheduled_end_time),
                            );

                            $workMins = \Carbon\Carbon::parse($record->check_out_time)->diffInMinutes(
                                \Carbon\Carbon::parse($record->check_in_time),
                            );

                            if ($workMins < $requiredMinutes) {
                                $statusLabel = 'وقت العمل غير كافي';
                                $statusClass = 'status-insufficient-hours';
                            } else {
                                $statusLabel = 'حضور';
                                $statusClass = 'status-present';
                            }
                        } else {
                            $statusLabel = 'حضور';
                            $statusClass = 'status-present';
                        }
                    } elseif ($record->day_status == 'leave') {
                        // إذا كانت إجازة، تحقق من النوع
                        $leaveTypeForStatus = $leaveType;

                        if (
                            stripos($leaveTypeForStatus, 'عن بعد') !== false ||
                            stripos($leaveTypeForStatus, 'عمل عن بعد') !== false
                        ) {
                            $statusLabel = $leaveTypeForStatus;
                            $statusClass = 'status-remote-work';
                        } else {
                            $statusLabel = 'إجازة';
                            $statusClass = 'leave';
                        }
                    } else {
                        $statusLabel = match ($record->day_status) {
                            'absent' => 'غياب',
                            default => '—',
                        };
                        $statusClass = $record->day_status ?? '';
                    }
                @endphp

                <tr class="{{ $record->day_status }}">
                    @if (!$employee)
                        <td>{{ $index + 1 }}</td>
                    @endif
                    <td>{{ \Carbon\Carbon::parse($record->date)->format('Y/m/d') }}</td>
                    <td>{{ $empName }}</td>
                    <td>{{ $empId }}</td>
                    <td>
                        {{ $record->check_in_time ? \Carbon\Carbon::parse($record->check_in_time)->format('H:i:s') : '—' }}
                    </td>
                    <td>
                        {{ $record->check_out_time ? \Carbon\Carbon::parse($record->check_out_time)->format('H:i:s') : '—' }}
                    </td>
                    <td class="{{ $leaveClass }}">{{ $leaveType }}</td>
                    <td class="late">{{ $formatTime($record->late_minutes) }}</td>
                    <td class="early-arrival">{{ $formatTime($record->early_arrival_minutes) }}</td>
                    <td class="early-leave">{{ $formatTime($record->early_leave_minutes) }}</td>
                    <td class="overtime">{{ $formatTime($record->overtime_minutes) }}</td>
                    <td class="work-hours"><strong>{{ $workTimeDisplay }}</strong></td>
                    <td class="{{ $statusClass }}">{{ $statusLabel }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $employee ? 12 : 13 }}" class="no-records">
                        لا توجد سجلات حضور خلال الفترة المحددة
                    </td>
                </tr>
            @endforelse

            @if ($records->isNotEmpty())
                <tr class="total-row">
                    <td colspan="{{ $employee ? 9 : 10 }}"><strong>الإجمالي</strong></td>
                    <td class="overtime-hours total-value">
                        {{ $formatTime($totalOvertimeMinutes) }}
                    </td>
                    <td class="work-hours total-value">
                        {{ $formatTime($totalWorkMinutes) }}
                    </td>
                    <td></td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- تذييل التقرير -->
    <div class="report-footer">
        <p>تم إنشاء هذا التقرير في {{ now()->format('Y/m/d H:i:s') }}</p>
    </div>
</body>

</html>
