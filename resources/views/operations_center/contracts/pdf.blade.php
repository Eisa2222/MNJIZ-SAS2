<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta charset="UTF-8">
    <title>{{ $contract->contract_name }}</title>
    <style>
        * {
            font-family: 'Almarai', sans-serif !important;
            box-sizing: border-box;
        }

        body {
            font-family: 'Almarai', sans-serif !important;
            direction: rtl;
            text-align: right;
            font-size: 12px;
            color: #000;
            margin: 0;
            padding: 0;
            unicode-bidi: bidi-override;
        }


         .header {
            height: 40px;
            display: flex;
            flex-direction: row-reverse;
            justify-content: space-between;
            align-items: center;
            padding: 5px 20px;
            margin-bottom: 10px;
            background-color: white;
        }


        .header-left {
            width: 250px;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: visible;
        }

        .header-left img {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
            min-width: 150px;
            min-height: 60px;
        }

        .header-right {
            font-size: 12px;
            text-align: left;
        }

        .content-wrapper {
            padding: 0 40px;
            margin-bottom: 100px;
        }

        .template-preview {


            direction: rtl !important;
            text-align: right !important;
            line-height: 1.5;
        }

        /* حاوية التواقيع العائمة في اليمين (ما عدا الأخير) */
        .signatures-floating-right {
            position: fixed;
            /* لتكون ثابتة على الصفحة */
            top: 50%;
            /* منتصف الشاشة عموديًّا */
            right: 20px;
            /* مسافة من اليمين */
            transform: translateY(-50%);
            /* لتوسيط الحاوية عموديًا */
            direction: ltr !important;
            z-index: 9999;
        }

        /* شكل كل توقيع */
        .signature-box {
            margin-bottom: 10px;
            /* مسافة بين كل توقيع والذي يليه */
            text-align: center;
            /* توسيط محتوى التوقيع داخله */
        }

        .signature-box img {
            max-width: 100px;
            display: block;
            margin: 0 auto;
        }

        /* حاوية الختم + آخر توقيع في أسفل يسار الصفحة */
        .seal-wrapper {
            position: fixed;
            /* لكي تكون في موضع ثابت على الصفحة */
            bottom: 100px;
            /* من الأسفل 20px */
            left: 20px;
            /* من اليسار 20px */
            direction: ltr !important;
            z-index: 9999;
            /* فوق بقية العناصر */
            display: flex;
            /* لجعل العناصر (الختم + التوقيع الأخير) بجانب بعض */
            align-items: center;
            gap: 10px;
            /* مسافة بين الختم والتوقيع الأخير */
        }

        .seal {
            max-width: 150px;
            opacity: 0.8;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100%;
        }

        .footer img {
            width: 100%;
            max-height: 80px;
            display: block;
        }


        /* تنسيقات خاصة بالطباعة */
        @page {
            margin-top: 0px;
            margin-bottom: 0px;
        }

        @media print {

            /* التأكد من أن المحتوى يتناسب مع منطقة الطباعة */
            body {
                width: 100%;
                margin: 0;
                padding: 0;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .content-wrapper {
                width: 100% !important;
                margin: 0 auto !important;
                padding: 0 20px !important;
            }

            /* التأكد من عدم قطع العناصر عند الطباعة */
            .signature-section {
                page-break-inside: avoid !important;
            }

            /* تحسين عرض الصور */
            img {
                max-width: 100% !important;
                page-break-inside: avoid !important;
            }

            /* تحسين عرض النص */
            .template-preview {
                width: 100% !important;
                max-width: 100% !important;
                overflow: visible !important;
            }

            .template-preview p[class*="ql-direction-rtl"][class*="ql-align-right"] {
                text-align: left !important;
                direction: rtl !important;
            }

            .private_and_secret{
            text-align: left;
            color: red;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        }

        @media screen {
            body {
                max-width: 100%;
                margin: 0 auto;
            }
        }

        .ql-align-right {
            text-align: right;
        }

        .ql-align-center {
            text-align: center;
        }

        .ql-align-justify {
            text-align: justify;
        }

        .ql-direction-rtl {
            direction: rtl;
        }
    </style>
</head>

<body>


   

    <!-- Main Content -->
    <div class="content-wrapper">
        <div class="template-preview">
            @if ($contract->is_private_and_secret)
            <div class="private_and_secret">
                سري و خاص
            </div>
        @endif
            {!! $processedContent !!}
        </div>
    </div>
  
</body>

</html>
