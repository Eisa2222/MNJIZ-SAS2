<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta http-equiv="refresh" content="60">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>القاعة {{ $meeting_rooms === 'big' ? 'الكبرى' : 'الصغرى' }} | جدول اليوم</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            color: #2c3e50;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            padding: 30px 0;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(44, 62, 80, 0.1);
            border-top: 4px solid #34495e;
        }

        .room-title {
            font-size: 2.5rem;
            font-weight: 300;
            color: #2c3e50;
            margin-bottom: 10px;
            letter-spacing: -1px;
        }

        .current-time {
            font-size: 1.1rem;
            color: #7f8c8d;
            font-weight: 400;
        }

        .main-grid {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        @media (max-width: 992px) {
            .main-grid {
                grid-template-columns: 1fr;
            }

            .room-title {
                font-size: 2rem;
            }
        }

        .card {
            background: #ffffff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 15px rgba(44, 62, 80, 0.1);
            border: 1px solid #ecf0f1;
            margin-bottom: 20px;
        }

        .card-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ecf0f1;
        }

        .meeting-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-right: 4px solid #34495e;
        }

        .time-badge {
            display: inline-block;
            background: #34495e;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 0.95rem;
            font-weight: 500;
            margin-bottom: 15px;
            letter-spacing: 0.5px;
        }

        .status-chip {
            display: inline-block;
            background: #ecf0f1;
            color: #2c3e50;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            margin-right: 10px;
            border: 1px solid #bdc3c7;
        }

        .meeting-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .meeting-notes {
            color: #7f8c8d;
            font-size: 0.95rem;
            font-style: italic;
            line-height: 1.5;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #95a5a6;
            font-size: 1.1rem;
        }

        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        .schedule-table th {
            background: #f8f9fa;
            padding: 15px 12px;
            font-weight: 600;
            color: #2c3e50;
            border-bottom: 2px solid #ecf0f1;
            text-align: center;
            font-size: 0.95rem;
        }

        .schedule-table td {
            padding: 12px;
            border-bottom: 1px solid #ecf0f1;
            text-align: center;
            font-size: 0.95rem;
        }

        .schedule-table td:last-child {
            text-align: right;
            font-weight: 500;
        }

        .current-meeting {
            background: #f8f9fa;
            border-right: 3px solid #34495e;
        }

        .time-cell {
            font-family: 'Courier New', monospace;
            font-weight: 600;
            color: #34495e;
        }

        .full-schedule {
            margin-top: 30px;
        }

        .stats-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .next-indicator {
            background: #3498db;
            color: white;
        }

        .current-indicator {
            background: #27ae60;
            color: white;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }

            100% {
                opacity: 1;
            }
        }

        .no-meetings {
            background: #f8f9fa;
            border: 2px dashed #bdc3c7;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            color: #7f8c8d;
        }

        .divider {
            height: 1px;
            background: linear-gradient(to right, transparent, #bdc3c7, transparent);
            margin: 20px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1 class="room-title">القاعة {{ $meeting_rooms === 'big' ? 'الكبرى' : 'الصغرى' }}</h1>
            <div class="current-time">{{ \Illuminate\Support\Carbon::parse($now)->translatedFormat('l d M Y, H:i') }}
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="main-grid">
            <!-- Left Column: Current & Next -->
            <div>
                <!-- Current Meeting -->
                <div class="card">
                    <h3 class="card-title">الاجتماع الجاري الآن</h3>
                    @if ($current)
                        @php
                            $start = \Illuminate\Support\Carbon::parse($current->date . ' ' . $current->from_time, $tz);
                            $end = \Illuminate\Support\Carbon::parse($current->date . ' ' . $current->to_time, $tz);
                            $remainMins = now($tz)->diffInMinutes($end, false);
                        @endphp
                        <div class="meeting-info">
                            <div class="stats-row">
                                <span class="time-badge current-indicator">من {{ $start->format('H:i') }} إلى
                                    {{ $end->format('H:i') }}</span>
                                <span class="status-chip">يتبقى {{ $remainMins > 0 ? $remainMins : 0 }} دقيقة</span>
                            </div>
                            <div class="meeting-title">{{ $current->title }}</div>
                            @if ($current->notes)
                                <div class="meeting-notes">{{ $current->notes }}</div>
                            @endif
                        </div>
                    @else
                        <div class="no-meetings">
                            لا يوجد اجتماع جارٍ حاليًا.
                        </div>
                    @endif
                </div>

                <!-- Next Meeting -->
                <div class="card">
                    <h3 class="card-title">الاجتماع القادم</h3>
                    @if ($next)
                        @php
                            $nStart = \Illuminate\Support\Carbon::parse($next->date . ' ' . $next->from_time, $tz);
                            $nEnd = \Illuminate\Support\Carbon::parse($next->date . ' ' . $next->to_time, $tz);
                            $waitMins = now($tz)->diffInMinutes($nStart, false);
                        @endphp
                        <div class="meeting-info">
                            <div class="stats-row">
                                <span class="time-badge next-indicator">من {{ $nStart->format('H:i') }} إلى
                                    {{ $nEnd->format('H:i') }}</span>
                                <span class="status-chip">يبدأ بعد {{ max(0, $waitMins) }} دقيقة</span>
                            </div>
                            <div class="meeting-title">{{ $next->title }}</div>
                            @if ($next->notes)
                                <div class="meeting-notes">{{ $next->notes }}</div>
                            @endif
                        </div>
                    @else
                        <div class="no-meetings">
                            لا يوجد اجتماع قادم اليوم.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Column: Today's Schedule -->
            <div>
                <div class="card">
                    <h3 class="card-title">جدول اليوم</h3>
                    @if ($meetings->isNotEmpty())
                        <table class="schedule-table">
                            <thead>
                                <tr>
                                    <th>من</th>
                                    <th>إلى</th>
                                    <th>الاجتماع</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($meetings as $m)
                                    @php
                                        $st = \Illuminate\Support\Carbon::parse($m->date . ' ' . $m->from_time, $tz);
                                        $en = \Illuminate\Support\Carbon::parse($m->date . ' ' . $m->to_time, $tz);
                                        $isNow = now($tz)->between($st, $en);
                                    @endphp
                                    <tr class="{{ $isNow ? 'current-meeting' : '' }}">
                                        <td class="time-cell">{{ $st->format('H:i') }}</td>
                                        <td class="time-cell">{{ $en->format('H:i') }}</td>
                                        <td>{{ $m->title }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="no-meetings">
                            لا توجد اجتماعات اليوم.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Full Day Overview -->
        @if ($meetings->isNotEmpty())
            <div class="card full-schedule">
                <h3 class="card-title">نظرة عامة على اليوم</h3>
                <div class="stats-row">
                    <div class="status-chip">إجمالي الاجتماعات: {{ $meetings->count() }}</div>
                    @php
                        $totalMinutes = $meetings->sum(function ($meeting) use ($tz) {
                            $start = \Illuminate\Support\Carbon::parse($meeting->date . ' ' . $meeting->from_time, $tz);
                            $end = \Illuminate\Support\Carbon::parse($meeting->date . ' ' . $meeting->to_time, $tz);
                            return $start->diffInMinutes($end);
                        });
                        $hours = floor($totalMinutes / 60);
                        $minutes = $totalMinutes % 60;
                    @endphp
                    <div class="status-chip">الوقت المحجوز: {{ $hours }} ساعة
                        {{ $minutes > 0 ? 'و ' . $minutes . ' دقيقة' : '' }}</div>
                    <div class="status-chip">آخر تحديث: {{ now($tz)->format('H:i') }}</div>
                </div>
            </div>
        @endif
    </div>
</body>

</html>
