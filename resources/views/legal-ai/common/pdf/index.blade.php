<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $title ?? 'مستند المساعد القانوني' }}</title>
    @if (isset($stylesheet) && !empty($stylesheet))
        <style>
            {!! $stylesheet !!}
        </style>
    @endif

</head>

<body>
    <htmlpageheader name="pageHeader">
        <div style="width: 100%; border-bottom: 1.5px solid #198c8c; padding-bottom: 10px;">
            <table width="100%" style="font-family: 'almarai'; vertical-align: middle; border-collapse: collapse;">
                <tr>
                    <td width="50%" style="text-align: right; border: 0;">
                        @if (isset($headerImagePath) && $headerImagePath && file_exists($headerImagePath))
                            <img src="{{ $headerImagePath }}" style="height: 60px; max-width: 250px;" />
                        @else
                            @if (env('APP_NAME'))
                                <h2 style="font-family: 'almarai'; color: #198c8c; margin: 0;">{{ env('APP_NAME') }}
                                </h2>
                            @endif
                        @endif
                    </td>
                    <td width="50%"
                        style="text-align: left; font-family: 'almarai'; font-size: 10px; color: #555; border: 0;">
                        <div style="line-height: 1.4;">
                            موضوع المحادثة: {{ $chatTitle }}<br>
                            تاريخ التصدير: {{ $currentDate }}
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </htmlpageheader>

    <htmlpagefooter name="pageFooter">
        @if (isset($footerImagePath) && $footerImagePath && file_exists($footerImagePath))
            <div style="width: 100%; position: absolute; bottom: 0; left: 0; right: 0;">
                <img src="{{ $footerImagePath }}" style="width: 100%; height: auto;" />
            </div>
        @endif
    </htmlpagefooter>

    {!! $content !!}

</body>

</html>
