<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>شكراً لك - تم إرسال الاستبيان بنجاح</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Almarai:wght@300;400;700;800&family=Cairo:wght@200..1000&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: "Almarai", sans-serif;
            background: linear-gradient(135deg, #d5a047 0%, #d5a047 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .thank-you-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 600px;
            width: 100%;
            margin: auto;
            animation: slideUp 0.8s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .success-icon {
            background: linear-gradient(135deg, #d5a047, #d5a047);
            color: white;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            font-size: 3rem;
            box-shadow: 0 10px 30px rgb(213 160 71);
            animation: bounce 1s ease-out 0.5s both;
        }

        @keyframes bounce {

            0%,
            20%,
            50%,
            80%,
            100% {
                transform: translateY(0);
            }

            40% {
                transform: translateY(-10px);
            }

            60% {
                transform: translateY(-5px);
            }
        }

        .thank-you-header {
            background: linear-gradient(135deg, #f8f9ff, #e8ecff);
            padding: 3rem 2rem;
            text-align: center;
            border-bottom: 1px solid #e9ecef;
        }


        .main-title {
            color: #2c3e50;
            font-weight: bold;
            font-size: 2.2rem;
            margin: 1.5rem 0 1rem;
        }

        .sub-title {
            color: #7f8c8d;
            font-size: 1.1rem;
            margin-bottom: 2rem;
            line-height: 1.6;
        }


        @media (max-width: 768px) {
            .thank-you-header {
                padding: 2rem 1rem;
            }

            .thank-you-body {
                padding: 2rem 1rem;
            }

            .main-title {
                font-size: 1.8rem;
            }

            .success-icon {
                width: 100px;
                height: 100px;
                font-size: 2.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="thank-you-card">
            <div class="thank-you-header">
                <div class="success-icon">
                    <i class="fas fa-check"></i>
                </div>
                <h1 class="main-title">شكراً لك!</h1>
                <p class="sub-title">
                    تم إرسال إجاباتك بنجاح. نقدر وقتك وآرائك القيمة
                </p>
            </div>
        </div>
    </div>
</body>

</html>