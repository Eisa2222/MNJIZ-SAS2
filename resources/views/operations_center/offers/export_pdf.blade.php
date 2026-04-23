<!DOCTYPE html>
<html lang="ar">

<head>
    <meta charset="UTF-8">
    <title>تفاصيل العرض - {{ $offer->offer_name }}</title>
    <style>
        body {
            font-family: Almarai, sans-serif;
            direction: rtl;
            text-align: right;
        }

        .header,
        .footer {
            width: 100%;

            position: fixed;
        }


        .footer {
            bottom: -60px;
            font-size: 12px;
            color: #777;
        }

        .content {
            padding-top: 80px !important;
            margin: 80px 40px;
        }

        .content img {
            max-width: 100%;
            height: auto;
        }



        /* إضافة أنماط أخرى حسب الحاجة */
    </style>
</head>

<body>
    <div class="header">
        <table style="width: 100%; border: none;">
            <tr>
               
                <td style="text-align: right; vertical-align: middle;">
                    @if (App\Helpers\SettingsHelper::get('horizontal_header_image'))
                        <img src="{{ public_path('storage/' . App\Helpers\SettingsHelper::get('horizontal_header_image')) }}"
                            alt="Header Image" style="max-height: 80px; object-fit: cover;">
                    @else
                        <p style="color: red;">يرجي اختيار صورة للترويسة من الاعدادات الخاصة بالطباعة</p>
                    @endif
                </td>

                <td style="text-align: left; vertical-align: middle;">
                    <p style="margin: 0;">الرقم المرجعي للعرض: {{ $offer->offer_number }}</p>
                </td>
            </tr>
        </table>
    </div>



    <!-- الفوتر -->
    <div class="footer">
        @if (App\Helpers\SettingsHelper::get('horizontal_footer_image'))
            <img src="{{ public_path('storage/' . App\Helpers\SettingsHelper::get('horizontal_footer_image')) }}"
                alt="Footer Image" style="max-height: 150px; object-fit: cover;">
        @else
            <p style="color: red;">يرجي اختيار صورة للفوتر من الاعدادات الخاصة بالطباعة</p>
        @endif
    </div>

    <!-- المحتوى الرئيسي -->
    <div class="content">
        <!-- تفاصيل العرض -->
        <div>
            {!! $processedContent !!}
        </div>
    </div>
</body>

</html>
