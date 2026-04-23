<!DOCTYPE html>
<html lang="ar">

<head>
    <meta charset="UTF-8">
    <title>تذكرة الدعم الفني</title>
    <style>
        body {
            font-family: 'almarai', sans-serif;
            direction: rtl;
            line-height: 1.6;
            color: #333;
        }

        .ticket-container {
            padding: 20px;
            background: #fff;
            border-radius: 8px;
        }

        .ticket-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e0e0e0;
        }

        .ticket-header h3 {
            color: #2c3e50;
            margin: 0;
            font-size: 24px;
        }

        .ticket-number {
            color: #3498db;
            font-size: 18px;
            margin-top: 10px;
        }

        .ticket-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 25px;
        }

        .info-row {
            display: flex !important;
            justify-content: space-between !important;
            width: 100% !important;
            margin-bottom: 15px !important;
        }

        .info-item {
            display: inline-block !important;
            white-space: nowrap !important;
        }

        .info-item:first-child {
            float: right !important;
        }

        .info-item:last-child {
            float: left !important;
        }

        .priority-row {
            display: flex;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid #e0e0e0;
        }

        .ticket-info strong {
            margin-left: 8px;
        }

        .ticket-info strong:after {
            content: ':';
            margin-right: 5px;
        }

        .ticket-section {
            margin-bottom: 25px;
            padding: 20px;
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
        }

        .ticket-section h6 {
            color: #2c3e50;
            font-size: 16px;
            margin: 0 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #3498db;
        }

        .ticket-section p {
            margin: 0;
            line-height: 1.8;
            color: #555;
        }

        .ticket-reply {
            background: #f1f8ff;
        }

        .priority-high {
            color: #e74c3c;
            font-weight: bold;
        }

        .priority-medium {
            color: #f39c12;
            font-weight: bold;
        }

        .priority-low {
            color: #27ae60;
            font-weight: bold;
        }

        .info-item span {
            font-size: 14px;
            display: inline-block;
        }
    </style>
</head>

<body>
    <div class="ticket-container">
        <div class="ticket-header">
            <h3>تذكرة الدعم الفني</h3>
            <div class="ticket-number">{{ $support->ticket_number }}</div>
        </div>

        <div class="ticket-info">
            <table width="100%" style="margin-bottom: 15px;">
                <tr>
                    <td style="text-align: right; width: 50%;">
                        <strong>المستخدم</strong>
                        <span>{{ $support->user->name }}</span>
                    </td>
                    <td style="text-align: left; width: 50%;">
                        <strong>تصنيف التذكرة</strong>
                        <span>{{ $support->ticket_classification }}</span>
                    </td>
                </tr>
            </table>
            <div class="priority-row" style="text-align: center;">
                <strong>الأولوية</strong>
                <span class="priority-{{ strtolower($support->priority) }}">{{ $support->priority }}</span>
            </div>
        </div>

        <div class="ticket-section">
            <h6>عنوان الرسالة</h6>
            <p>{{ $support->title }}</p>
        </div>

        <div class="ticket-section">
            <h6>الوصف</h6>
            <p>{{ $support->notes }}</p>
        </div>

        @if ($support->reply)
            <div class="ticket-section ticket-reply">
                <h6>الرد</h6>
                <p>{{ $support->reply != '' ? $support->reply : 'لم يتم الرد بعد' }}</p>
            </div>
        @endif


        @if ($support->status == 'تمت المعالجة')
            <div style="text-align: left">
                @if ($support->processedBy)
                    <p>
                        <span class="me-2 h6">قام بمعالجة الطلب :</span>
                        <span>{{ $support->processedBy->name ?? '' }}</span>
                    </p>
                @endif


            </div>

        @endif
    </div>
</body>

</html>
