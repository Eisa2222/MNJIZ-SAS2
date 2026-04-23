@extends('layouts.layoutMaster')

@section('title', 'بطاقة العمل ')

@section('breadcrumb')
    <li><a href="#"> الموارد البشرية</a></li>
    <li><a href="{{ route('hr.employees.index') }}"> الموظفين</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> بطاقة العمل</a>
        <i class="ti ti-star favorite-icon" data-page-name="بطاقة العمل" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/css/components/icons.css'])
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Cairo', Arial, sans-serif;
            direction: rtl;
        }

        .container-fluid {
            padding: 20px;
        }

        .cards-container {
            display: flex;
            flex-wrap: wrap;
            gap: 40px;
            justify-content: center;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* بطاقة العمل الأفقية */
        .business-card {
            width: 380px;
            height: 240px;
            background: #ffffff;
            border-radius: 16px;
            box-shadow:
                0 20px 40px -12px rgba(26, 54, 93, 0.15),
                0 8px 16px -4px rgba(26, 54, 93, 0.1),
                0 0 0 1px rgba(255, 255, 255, 0.95);
            overflow: hidden;
            position: relative;
            transition: all 0.4s ease;
            cursor: pointer;
        }


        /* الجانب الأيسر - معلومات الشركة */
        .company-section {
            position: absolute;
            left: 0;
            top: 0;
            width: 120px;
            height: 100%;
            background: linear-gradient(180deg, #a79170 0%, #2d3748 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            padding: 20px 10px;
        }

        .company-logo {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
        }

        .company-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 8px;
        }

        .company-name {
            font-size: 11px;
            font-weight: 800;
            text-align: center;
            line-height: 1.3;
            letter-spacing: 0.5px;
        }

        .company-tagline {
            font-size: 8px;
            opacity: 0.8;
            text-align: center;
            margin-top: 8px;
            font-weight: 400;
        }

        /* الجانب الأيمن - معلومات الموظف */
        .employee-section {
            margin-right: 120px;
            padding: 25px 30px;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .employee-name {
            font-size: 22px;
            font-weight: 900;
            color: #a79170;
            margin-bottom: 8px;
            line-height: 1.2;
        }

        .employee-position {
            font-size: 13px;
            color: #a79170;
            font-weight: 600;
            margin-bottom: 18px;
            padding-bottom: 12px;
            border-bottom: 2px solid #e2e8f0;
        }

        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 11px;
            color: #475569;
            font-weight: 500;
        }

        .contact-icon {
            width: 14px;
            height: 14px;
            color: #a79170;
            flex-shrink: 0;
        }

        /* بطاقة الهوية الرأسية */
        .employee-id-card {
            width: 500px;
            background: #ffffff;
            border-radius: 20px;
            box-shadow:
                0 25px 50px -12px #a7917033,
                0 8px 16px -4px rgba(26, 54, 93, 0.1),
                0 0 0 1px rgba(255, 255, 255, 0.9);
            overflow: hidden;
            position: relative;
            transition: all 0.4s ease;
        }

        /* رأس البطاقة */
        .card-header-id-card {
            background: linear-gradient(135deg, #af9772 0%, #a28d6e 100%);
            height: 240px;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            color: white;
            padding-top: 50px
        }

        .header-decoration {
            position: absolute;
            top: -20px;
            right: -20px;
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: rotate 20s linear infinite;
        }

        .header-decoration::after {
            content: '';
            position: absolute;
            bottom: -40px;
            left: -40px;
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
            animation: rotate 15s linear infinite reverse;
        }

        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .company-logo-large {
            width: 70px;
            height: 70px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
        }

        .company-logo-large img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 12px;
        }

        .company-name-large {
            font-size: 16px;
            font-weight: 800;
            text-align: center;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
            letter-spacing: 1px;
        }

        /* صورة الموظف */
        .employee-photo-section {
            padding: 25px;
            text-align: center;
            margin-top: -90px;
            position: relative;
        }

        .photo-frame {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            background: linear-gradient(178deg, #a38d6ecc, #d39f476b);
            padding: 4px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 15px 35px rgba(26, 54, 93, 0.3);
            position: relative;
        }

        .employee-photo {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            background: linear-gradient(135deg, #a79170 0%, #475569 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 42px;
            font-weight: bold;
        }

        .employee-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        /* معلومات الموظف في البطاقة */
        .employee-info {
            padding: 0 30px 30px;
            text-align: center;
        }

        .employee-name-large {
            font-size: 24px;
            font-weight: 900;
            color: #a79170;
            margin: 15px 0;
            line-height: 1.2;
        }

        .employee-position-large {
            font-size: 14px;
            color: #a79170;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 6px 45px;
            border-radius: 25px;
            display: inline-block;
            margin-bottom: 25px;
            font-weight: 600;
            border: 1px solid rgba(26, 54, 93, 0.1);
            box-shadow: 0 4px 6px rgba(26, 54, 93, 0.05);
        }

        .employee-details {
            text-align: right;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 0;
            border-bottom: 2px solid #f8fafc;
            font-size: 13px;
            transition: all 0.2s ease;
        }



        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: #a79170;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .detail-label::before {
            content: '⚖️';
            font-size: 14px;
        }

        .detail-value {
            color: #a79170;
            font-weight: 800;
        }

        /* رمز QR */
        .qr-section {
            text-align: center;
            padding-top: 20px;
            border-top: 2px solid #f8fafc;
        }

        .qr-code {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border: 3px solid #a79170;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: #a79170;
            font-weight: bold;
            box-shadow: 0 8px 20px rgba(26, 54, 93, 0.2);
            position: relative;
            overflow: hidden;
        }

        .qr-code::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.6) 50%, transparent 70%);
            animation: shine 4s infinite;
        }

        @keyframes shine {
            0% {
                transform: translateX(-100%) translateY(-100%) rotate(30deg);
            }

            100% {
                transform: translateX(100%) translateY(100%) rotate(30deg);
            }
        }

        /* أزرار التحكم */
        .card-controls {
            text-align: center;
            margin: 40px 0;
        }

        .control-btn {
            background: linear-gradient(135deg, #a79170 0%, #2d3748 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            margin: 10px;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(26, 54, 93, 0.3);
            cursor: pointer;
            font-family: 'Cairo', sans-serif;
        }


        .control-btn.secondary {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            color: #a79170;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }



        /* الاستجابة للشاشات الصغيرة */
        @media (max-width: 768px) {
            .business-card {
                width: 340px;
                height: 220px;
            }

            .employee-id-card {
                width: 300px;
                height: 480px;
            }

            .cards-container {
                gap: 20px;
            }

            .control-btn {
                padding: 12px 24px;
                font-size: 14px;
            }

            .employee-section {
                padding: 20px 25px;
            }

            .employee-name {
                font-size: 18px;
            }

            .employee-position {
                font-size: 12px;
            }

            .contact-item {
                font-size: 10px;
            }
        }

        /* تأثيرات الطباعة */
        @media print {
            body {
                background: white !important;
            }

            .card-controls {
                display: none !important;
            }

            .business-card,
            .employee-id-card {
                box-shadow: none !important;
                break-inside: avoid;
                margin: 20px 0;
            }

            .card-header {
                display: none !important;
            }

            .breadcrumb {
                display: none !important;
            }
        }
    </style>
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header py-4">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-tie text-warning me-2"></i>
                        بطاقة العمل للموظف : <span class="fw-bold">{{ $employee->getRawNameAttribute() ?? '-' }}</span>
                    </h6>
                </div>
                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="container-fluid">
                    <div class="cards-container">


                        <!-- بطاقة الهوية الرأسية -->
                        <div class="employee-id-card" id="id-card">
                            <!-- رأس البطاقة -->
                            <div class="card-header-id-card">
                                <div class="header-decoration"></div>
                                <div class="company-logo-large">
                                    <img src="{{ $settings->image ? asset('storage/' . $settings->image) : asset('assets/img/branding/Alburhan-Logo.png') }}"
                                        alt="Company Logo">
                                </div>
                                <div class="company-name-large">{{ $settings->office_name }}
                                </div>
                            </div>

                            <!-- صورة الموظف -->
                            <div class="employee-photo-section">
                                <div class="photo-frame">
                                    <div class="employee-photo">
                                        @if ($employee->profile_picture)
                                            <img src="{{ asset('storage/' . $employee->profile_picture) }}"
                                                alt="{{ $employee->getRawNameAttribute() }}">
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- معلومات الموظف -->
                            <div class="employee-info">
                                <div class="employee-name-large">{{ $employee->getRawNameAttribute() ?? 'اسم الموظف' }}
                                </div>
                                <div class="employee-position-large">{{ $employee->job_title ?? 'محامي أول' }}</div>

                                <div class="employee-details">
                                    <div class="detail-row">
                                        <span class="detail-label">الرقم الوظيفي</span>
                                        <span class="detail-value">{{ $employee->id_number ?? '' }}</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">رقم الجوال</span>
                                        <span dir="ltr" class="detail-value">{{ $employee->mobile ?? '' }}</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">البريد الالكتروني</span>
                                        <span class="detail-value">{{ $employee->personal_email ?? '' }}</span>
                                    </div>
                                </div>
                                @if (isset($qrCode))
                                    <div class="qr-section">
                                        <div class="qr-code">{!! $qrCode !!}</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
