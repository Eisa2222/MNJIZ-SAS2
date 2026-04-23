<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $settings->office_name }}</title>

    <!-- Bootstrap RTL -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Google Fonts Arabic -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Almarai:wght@300;400;700;800&family=Cairo:wght@200..1000&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --primary-color: #000000;
            --accent-color: #d5a047;
            --success-color: #38a169;
            --warning-color: #d69e2e;
            --danger-color: #e53e3e;
            --light-bg: #f7fafc;
            --card-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        * {
            font-family: "Almarai", sans-serif;
        }

        body {
            min-height: 100vh;
            padding: 20px 0;
        }

        .survey-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .survey-header {
            background: linear-gradient(135deg, #d5a047 0%, #d5a047 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .survey-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="20" cy="20" r="2" fill="white" opacity="0.1"/><circle cx="80" cy="80" r="1" fill="white" opacity="0.1"/><circle cx="40" cy="60" r="1.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            animation: float 20s linear infinite;
        }

        @keyframes float {
            0% {
                transform: translate(-50%, -50%) rotate(0deg);
            }

            100% {
                transform: translate(-50%, -50%) rotate(360deg);
            }
        }

        .survey-header h1 {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 15px;
            position: relative;
            z-index: 1;
        }

        .survey-header .subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .company-info {
            background: var(--light-bg);
            padding: 25px 30px;
            border-bottom: 1px solid #e2e8f0;
            text-align: center;
        }



        .survey-form {
            padding: 40px 30px;
        }

        .question-card {
            background: #f8f9ff;
            border: 2px solid transparent;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .question-card:hover {
            border-color: var(--accent-color);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(49, 130, 206, 0.15);
        }

        .question-number {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--accent-color);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .question-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-left: 20px;
        }

        .option-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .option-item {
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 18px 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .option-item:hover {
            border-color: var(--accent-color);
            background: #f0f8ff;
            transform: translateX(-5px);
        }

        .option-item input[type="radio"] {
            width: 20px;
            height: 20px;
            margin: 0;
            accent-color: var(--accent-color);
        }

        .option-item.selected {
            border-color: var(--accent-color);
            background: linear-gradient(135deg, #d5a047 0%, #d5a0475c 100%);
            box-shadow: 0 4px 15px rgba(49, 130, 206, 0.2);
        }

        .submit-section {
            text-align: center;
            padding: 30px;
            background: var(--light-bg);
            border-top: 1px solid #e2e8f0;
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--success-color) 0%, #48bb78 100%);
            border: none;
            color: white;
            padding: 15px 40px;
            font-size: 1.2rem;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(56, 161, 105, 0.3);
        }



        .btn-submit:disabled {
            color: white;

            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .progress-bar {
            height: 8px;
            background: #e2e8f0;
            border-radius: 10px;
            margin: 20px 0;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(269deg, var(--accent-color) 0%, #d5a0473b 100%);
            border-radius: 10px;
            transition: width 0.3s ease;
            width: 0%;
        }

        .customer-info {
            background: #d5a04717;
            padding: 15px;
            margin-bottom: 25px;
            text-align: center;
        }

        .alert {
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 20px;
            border: none;
        }

        .alert-warning {
            background: #fffbeb;
            color: #92400e;
            border-left: 4px solid var(--warning-color);
        }

        .footer-info {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            color: #6c757d;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .survey-container {
                margin: 10px;
            }

            .survey-header {
                padding: 30px 20px;
            }

            .survey-form {
                padding: 25px 20px;
            }

            .question-card {
                padding: 35px 15px;
            }

            .question-title {
                font-size: 1.1rem;
            }
        }
    </style>
</head>

<body>
    <div class="survey-container">
        <!-- Header -->
        <div class="survey-header">
            <h1>{{ $surveyResponse->survey->title }}</h1>
            <p class="subtitle">نقدر وقتك في تقييم خدماتنا</p>
        </div>

        <!-- Company Info -->
        <div class="company-info">
            <div class="">
                <img class="w-10 h-10"
                    src="{{ $settings->image ? asset('storage/' . $settings->image) : asset('assets/img/branding/Alburhan-Logo.png') }}"
                    alt="">
            </div>
            <h5 class="mb-1">
                {{ $settings->office_name }}
            </h5>
        </div>

        <!-- Customer Info -->
        <div class="customer-info">
            <i class="fas fa-user-circle fa-2x text-primary mb-2"></i>
            <h6>مرحباً {{ $surveyResponse->customer->name }}</h6>
            <small class="text-muted">شكراً لك على الوقت المخصص لتقييم خدماتنا</small>
        </div>

        <!-- Progress Bar -->
        <div class="px-4">
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill"></div>
            </div>
            <div class="text-center mt-2">
                <small class="text-muted">
                    <span id="currentQuestion">0</span> من {{ count($surveyResponse->survey->questions) }} أسئلة
                </small>
            </div>
        </div>

        <!-- Form -->
        <form id="surveyForm" action="{{ route('survey.submit', $surveyResponse->token) }}" method="POST"
            class="survey-form">
            @csrf

            @if ($errors->any())
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    يرجى الإجابة على جميع الأسئلة المطلوبة
                </div>
            @endif

            @foreach ($surveyResponse->survey->questions as $index => $question)
                <div class="question-card" data-question="{{ $index }}">
                    <div class="question-number">{{ $index + 1 }}</div>
                    <div class="question-title">
                        {{ $question->question_text }}
                        @if ($question->is_required ?? true)
                            <span class="text-danger">*</span>
                        @endif
                    </div>

                    <div class="option-group">
                        @foreach ($question->options as $option)
                            <label class="option-item" for="option_{{ $question->id }}_{{ $option->id }}">
                                <input required type="radio" name="answers[{{ $question->id }}]"
                                    value="{{ $option->id }}" id="option_{{ $question->id }}_{{ $option->id }}"
                                    {{ old("answers.{$question->id}") == $option->id ? 'checked' : '' }}
                                    onchange="updateProgress()">
                                <span class="option-text">{{ $option->option_text }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="submit-section">
                <button type="submit" class="btn btn-submit" id="submitBtn">
                    <i class="fas fa-paper-plane me-2"></i>
                    إرسال الإجابات
                    <div class="loading-spinner" id="loadingSpinner"></div>
                </button>
                <p class="text-muted mt-3 mb-0">
                    <i class="fas fa-shield-alt me-1"></i>
                    معلوماتك محمية ولن يتم مشاركتها مع أطراف خارجية
                </p>
            </div>
        </form>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // تحديث شريط التقدم
        function updateProgress() {
            const totalQuestions = {{ count($surveyResponse->survey->questions) }};
            const answeredQuestions = document.querySelectorAll('input[type="radio"]:checked').length;
            const progress = (answeredQuestions / totalQuestions) * 100;

            document.getElementById('progressFill').style.width = progress + '%';
            document.getElementById('currentQuestion').textContent = answeredQuestions;

            // تفعيل/إلغاء تفعيل زر الإرسال
            const submitBtn = document.getElementById('submitBtn');
            if (answeredQuestions === totalQuestions) {
                submitBtn.disabled = false;
                submitBtn.style.opacity = '1';
            }
        }

        // تطبيق التأثيرات البصرية
        document.addEventListener('DOMContentLoaded', function() {
            // تفعيل التأثير على الخيارات
            const optionItems = document.querySelectorAll('.option-item');
            optionItems.forEach(item => {
                item.addEventListener('click', function() {
                    // إزالة التحديد من جميع الخيارات في نفس المجموعة
                    const questionCard = this.closest('.question-card');
                    questionCard.querySelectorAll('.option-item').forEach(opt => {
                        opt.classList.remove('selected');
                    });

                    // إضافة التحديد للخيار المختار
                    this.classList.add('selected');

                    // تحديث الـ radio button
                    const radio = this.querySelector('input[type="radio"]');
                    if (radio) {
                        radio.checked = true;
                        updateProgress();
                    }
                });
            });

            // معالجة إرسال النموذج
            document.getElementById('surveyForm').addEventListener('submit', function(e) {
                const submitBtn = document.getElementById('submitBtn');
                const loadingSpinner = document.getElementById('loadingSpinner');

                submitBtn.disabled = true;
                loadingSpinner.style.display = 'inline-block';
                submitBtn.innerHTML =
                    '<i class="fas fa-clock me-2"></i>جاري الإرسال...<div class="loading-spinner" style="display: inline-block; margin-left: 10px;"></div>';
            });

            // تحديث التقدم الأولي
            updateProgress();
        });

        // تأثيرات الحركة عند التمرير
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animation = 'fadeInUp 0.6s ease-out';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.question-card').forEach(card => {
            observer.observe(card);
        });
    </script>
</body>

</html>
