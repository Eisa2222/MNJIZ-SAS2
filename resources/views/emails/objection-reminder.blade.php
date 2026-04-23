<!doctype html>
<html lang="und" dir="auto" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml"
    xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <title></title>
    <!--[if !mso]><!-->
    <meta http-equiv="X-UA-Compatible" content="IE=edge"><!--<![endif]-->
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style type="text/css">
        #outlook a {
            padding: 0;
        }

        body {
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%
        }

        table,
        td {
            border-collapse: collapse;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt
        }

        img {
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
            -ms-interpolation-mode: bicubic
        }

        p {
            display: block;
            margin: 13px 0
        }
    </style>
    <!--[if mso]><noscript><xml><o:OfficeDocumentSettings><o:AllowPNG/><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript><![endif]-->
    <!--[if lte mso 11]><style>.mj-outlook-group-fix{width:100%!important}</style><![endif]-->
    <!--[if !mso]><!-->
    <link href="https://fonts.googleapis.com/css?family=Open Sans" rel="stylesheet" type="text/css">
    <style>
        @import url(https://fonts.googleapis.com/css?family=Open Sans);
    </style><!--<![endif]-->
    <style>
        @media only screen and (min-width:480px) {
            .mj-column-per-100 {
                width: 100% !important;
                max-width: 100%
            }
        }

        [owa] .mj-column-per-100 {
            width: 100% !important;
            max-width: 100%
        }

        @media only screen and (max-width:479px) {
            table.mj-full-width-mobile {
                width: 100% !important
            }

            td.mj-full-width-mobile {
                width: auto !important
            }
        }
    </style>
</head>

@php  $settings = App\Models\GeneralSetting\SystemSetting\Settings::find(1); @endphp

<body style="word-spacing:normal;background-color:#f8f8f8;">
    <div
        style="display:none;font-size:1px;color:#ffffff;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;">
        تذكير بآخر مهلة للاعتراض
    </div>

    <div style="background-color:#f8f8f8;" dir="auto">
        <!-- ===== رأس الرسالة ===== -->
        <div style="background:#ffffff;margin:0 auto;max-width:600px;">
            <table role="presentation" width="100%" bgcolor="#ffffff" align="center">
                <tr>
                    <td style="direction:ltr;font-size:0;padding:10px 0;text-align:center">
                        <div class="mj-column-per-100" style="display:inline-block;width:100%">
                            <table role="presentation" width="100%">
                                <tr>
                                    <td align="center" style="padding:10px 25px">
                                        <p
                                            style="direction:rtl;text-align:right;unicode-bidi:embed;border-top:5px solid #198c8c;font-size:1px;margin:0;width:100%">
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="padding:10px 0">
                                        <table role="presentation" style="border-collapse:collapse">
                                            <tr>
                                                <td style="width:110px">
                                                    <img src="http://hr.e-tec.sa//storage/image/Untitled__1_-removebg-preview-removebg-preview.png"
                                                        alt="شعار" width="110"
                                                        style="display:block;width:100%;height:auto;font-size:13px;border:0">
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center">
                                        <div
                                            style="font-family:'Open Sans',Helvetica,Arial,sans-serif;font-size:16px;line-height:24px;color:#000">
                                            <strong>{{ $settings->office_name ?? '' }}</strong>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- ===== محتوى الرسالة ===== -->
        <div style="background:#ffffff;margin:0 auto;max-width:600px;">
            <table role="presentation" width="100%" bgcolor="#ffffff" align="center">
                <tr>
                    <td style="direction:ltr;font-size:0;padding:15px 30px;text-align:right">
                        <div class="mj-column-per-100" style="display:inline-block;width:100%">
                            <table role="presentation" width="100%">
                                <tr>
                                    <td align="right">
                                        <div
                                            style="font-family:'Open Sans',Helvetica,Arial,sans-serif;font-size:16px;line-height:24px;color:#000">
                                            <h4 style="direction:rtl;text-align:right;margin:0 0 10px;color:#000">
                                                مرحبًا {{ $user->name ?? '' }}
                                            </h4>
                                            <h3 style="direction:rtl;text-align:right;margin:0 0 10px;color:#333">
                                                نود تذكيرك بآخر مهلة للاعتراض على الجلسة التالية:
                                            </h3>

                                            <ul style="list-style:none;padding:0;direction:rtl">
                                                <li
                                                    style="background:#f9f9f9;margin-bottom:10px;padding:10px;border-right:4px solid #198c8c">
                                                    <span style="font-weight:bold;display:block;margin-bottom:5px">اسم
                                                        الجلسة:</span>
                                                    <span
                                                        style="color:#333">{{ $session->session_name ?? 'غير محدد' }}</span>
                                                </li>

                                                <li
                                                    style="background:#f9f9f9;margin-bottom:10px;padding:10px;border-right:4px solid #198c8c">
                                                    <span style="font-weight:bold;display:block;margin-bottom:5px">تاريخ
                                                        آخر مهلة للاعتراض:</span>
                                                    <span style="color:#333"> {{ $objectionDate ?? 'غير محدد' }}</span>
                                                </li>
                                            </ul>

                                            <p style="direction:rtl;text-align:right;margin:0 0 10px;color:#333">
                                                يرجى اتخاذ الإجراءات اللازمة قبل انتهاء المهلة.
                                            </p>
                                            <p style="direction:rtl;text-align:right;margin:0 0 10px;color:#333">
                                                شكرًا لاستخدامك خدماتنا.
                                            </p>
                                            <p style="direction:rtl;text-align:right;margin:0 0 10px;color:#333">
                                                إذا واجهت أي مشكلة، يرجى التواصل معنا عبر البريد الإلكتروني.
                                            </p>

                                            <p style="direction:rtl;text-align:left;margin:0 0 10px;color:#333">
                                                مع أطيب التحيات،
                                            </p>
                                            <p style="direction:rtl;text-align:left;margin:0;color:#333">
                                                <strong>{{ $settings->office_name ?? '' }}</strong>
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- ===== التذييل ===== -->
        <div style="background:#f1f1f1;margin:0 auto;max-width:600px;">
            <table role="presentation" width="100%" bgcolor="#f1f1f1" align="center">
                <tr>
                    <td style="direction:ltr;font-size:0;padding:15px 20px;text-align:center">
                        <div
                            style="font-family:'Open Sans',Helvetica,Arial,sans-serif;font-size:14px;line-height:22px;color:#797e82">
                            شكرًا لاستخدام خدماتنا. نتمنى لكم يومًا سعيدًا!
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>
