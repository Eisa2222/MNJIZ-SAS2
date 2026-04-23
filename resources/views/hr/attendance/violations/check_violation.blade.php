@extends('layouts.layoutMaster')

@section('title', 'إضافة مخالفة للحضور')

@section('breadcrumb')
    <li><a href="#">الموارد البشرية</a></li>
    <li><a href="{{ route('attendances.index') }}">الحضور والانصراف</a></li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">إضافة مخالفة للحضور</a>
        <i class="ti ti-star favorite-icon" data-page-name="إضافة مخالفة للحضور" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/animate-css/animate.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('sweetalert-cdn')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endsection

@section('page-script')
    @yield('sweetalert-cdn')
    <script>
        $(function() {
            /*
            |--------------------------------------------------------------------------
            | تهيئة المكتبات والأدوات
            |--------------------------------------------------------------------------
            | إعداد مكتبات مثل select2 وغيرها من الأدوات المستخدمة في الصفحة.
            */
            $('.select2').select2();

            /*
            |--------------------------------------------------------------------------
            | وظائف جلب وعرض المخالفات
            |--------------------------------------------------------------------------
            | مجموعة من الوظائف لجلب وعرض المخالفات المتاحة للتطبيق على سجل الحضور.
            */

            // تعريف دالة جلب المخالفات
            function fetchViolations() {
                $("#violations-container").html(`
                <div class="text-center p-5">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2">جاري تحميل المخالفات المناسبة...</p>
                </div>
              `);

                $.ajax({
                    url: "{{ route('attendances.violations.get') }}",
                    type: "GET",
                    data: {
                        attendance_id: {{ $attendance->id }},
                        violation_type: "{{ $violationType }}",
                        duration: {{ $duration }},
                        duration_unit: "{{ $durationUnit }}"
                    },
                    success: function(response) {
                        if (response.success) {
                            renderViolations(response.violations);
                        } else {
                            $("#violations-container").html(`
                            <div class="alert alert-danger d-flex align-items-center">
                                <i class="ti ti-alert-circle me-2"></i>
                                <span>حدث خطأ أثناء تحميل المخالفات</span>
                            </div>
                        `);
                        }
                    },
                    error: function(xhr) {
                        console.error("Error loading violations:", xhr);
                        $("#violations-container").html(`
                        <div class="alert alert-danger d-flex align-items-center">
                            <i class="ti ti-wifi-off me-2"></i>
                            <span>حدث خطأ في الاتصال بالخادم</span>
                        </div>
                    `);
                    }
                });
            }

            // تعريف زر التحديث
            $("#refresh-violations-btn").on('click', function() {
                fetchViolations();
            });

            /*
            |--------------------------------------------------------------------------
            | عرض المخالفات المتاحة
            |--------------------------------------------------------------------------
            | رسم واجهة المستخدم لعرض المخالفات المتاحة بناءً على البيانات المستلمة من الخادم.
            */
            function renderViolations(violations) {
                if (violations.length === 0) {
                    $("#violations-container").html(`
                    <div class="alert alert-info d-flex align-items-center">
                        <i class="ti ti-info-circle me-2"></i>
                        <span>لا توجد مخالفات متاحة لهذا النوع من المخالفات</span>
                    </div>
                `);
                    return;
                }

                let html = '<div class="row">';

                violations.forEach(function(violation) {
                    html += `
                <div class="col-md-6 mb-4">
                    <div class="card border h-100">
                        <!-- رأس الكارد مع تحسين -->
                        <div class="card-header border-bottom p-3 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <i class="ti ti-file-alert text-warning fs-4 me-2"></i>
                                <h5 class="mb-0 fw-semibold">${violation.category.name}</h5>
                            </div>
                            <div class="ms-2">
                                <span class="badge bg-primary fs-6 px-3 py-2">المرة ${getOccurrenceText(violation.occurrence)}</span>
                            </div>
                        </div>

                        <!-- جسم الكارد مع مسافات محسنة -->
                        <div class="card-body p-4">
                            <!-- وصف المخالفة -->
                            <div class="mb-4">
                                <h6 class="mb-3 fw-semibold d-flex align-items-center">
                                    <i class="ti ti-info-circle text-primary me-2"></i>
                                    وصف المخالفة
                                </h6>
                                <div class="ps-4 pt-2 pb-2">
                                    <p class="mb-0 lh-base">${violation.description}</p>
                                </div>
                            </div>

                            <!-- العقوبة - مستطيل عادي بدون أيقونة -->
                            <div class="mt-4 pt-3 border-top">
                                <h6 class="mb-3 fw-semibold">
                                    العقوبة
                                </h6>
                                <div class="border ps-3 py-3 pe-3 rounded">
                                    <div class="fw-bold fs-6">${violation.applicable_penalty}</div>
                                    ${violation.extra_deduction ?
                                        `<div class="text-muted small mt-3 pt-2 border-top">
                                                    <span>${violation.extra_deduction}</span>
                                                </div>` :
                                    ''}
                                </div>
                            </div>
                        </div>

                        <!-- قدم الكارد -->
                        <div class="card-footer border-top d-flex justify-content-end p-3">
                            <button class="btn btn-sm btn-primary apply-violation" data-id="${violation.id}">
                                تطبيق المخالفة
                            </button>
                        </div>
                    </div>
                </div>`;
                });

                html += '</div>';
                $("#violations-container").html(html);

                // تعيين الأحداث بعد إنشاء العناصر
                $('.apply-violation').on('click', function() {
                    let violationId = $(this).data('id');
                    applyViolation(violationId);
                });
            }

            /*
            |--------------------------------------------------------------------------
            | تطبيق المخالفة وإجراءات التأكيد
            |--------------------------------------------------------------------------
            | وظائف عرض نوافذ التأكيد وإرسال طلبات تطبيق المخالفة إلى الخادم.
            */
            function applyViolation(violationId) {
                Swal.fire({
                    title: 'تأكيد إضافة المخالفة',
                    text: 'هل أنت متأكد من تطبيق هذه المخالفة على الموظف؟',
                    icon: 'warning',
                    showCancelButton: true,
                    cancelButtonText: 'إلغاء',
                    confirmButtonText: 'نعم، قم بإضافة المخالفة',
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    // إعادة ترتيب الأزرار (الموافقة يمين والإلغاء يسار)
                    reverseButtons: false,
                    customClass: {
                        confirmButton: 'btn btn-primary mx-2', // استخدام mx-2 لإضافة هوامش من الجانبين
                        cancelButton: 'btn btn-danger mx-2', // إضافة هوامش للأزرار
                        actions: 'justify-content-center gap-2' // إضافة مسافة بين الأزرار
                    },
                    buttonsStyling: false,
                    showCloseButton: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // عرض مؤشر التحميل مع ضمان تعريب الواجهة
                        Swal.fire({
                            title: 'جاري المعالجة...',
                            text: 'يرجى الانتظار',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            // ضمان عدم ظهور أي نص باللغة الإنجليزية
                            didOpen: () => {
                                Swal.showLoading();
                                // تعريب الأزرار أو النصوص الإضافية إن وجدت
                                const buttons = document.getElementsByClassName(
                                'swal2-confirm');
                                if (buttons.length > 0) {
                                    for (let i = 0; i < buttons.length; i++) {
                                        buttons[i].textContent = 'موافق';
                                    }
                                }
                            }
                        });

                        /*
                        |--------------------------------------------------------------------------
                        | إرسال طلب إضافة المخالفة
                        |--------------------------------------------------------------------------
                        | إرسال بيانات المخالفة للخادم وعرض نتيجة العملية.
                        */
                        $.ajax({
                            url: "{{ route('attendances.violations.create') }}",
                            type: "POST",
                            data: {
                                attendance_id: {{ $attendance->id }},
                                settings_violation_id: violationId,
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                if (response.success) {
                                    // استخدام toastr للنجاح
                                    toastr.success('تم إضافة المخالفة بنجاح');

                                    // إغلاق نافذة التحميل
                                    Swal.close();

                                    // الانتقال إلى صفحة الحضور والانصراف
                                    setTimeout(function() {
                                        window.location.href =
                                            "{{ route('attendances.index') }}";
                                    }, 1500); // انتظار 1.5 ثانية قبل الانتقال
                                } else {
                                    // استخدام toastr للخطأ
                                    toastr.error(response.message ||
                                        'حدث خطأ أثناء إضافة المخالفة');

                                    // إغلاق نافذة التحميل
                                    Swal.close();
                                }
                            },
                            error: function(xhr) {
                                let errorMessage = 'حدث خطأ أثناء إضافة المخالفة';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                }

                                // استخدام toastr للخطأ
                                toastr.error(errorMessage);

                                // إغلاق نافذة التحميل
                                Swal.close();
                            }
                        });
                    }
                });
            }

            /*
            |--------------------------------------------------------------------------
            | وظائف مساعدة
            |--------------------------------------------------------------------------
            | وظائف مساعدة متنوعة لمعالجة البيانات وتنسيقها.
            */
            function getOccurrenceText(occurrence) {
                switch (occurrence) {
                    case 1:
                        return 'الأولى';
                    case 2:
                        return 'الثانية';
                    case 3:
                        return 'الثالثة';
                    case 4:
                        return 'الرابعة';
                    default:
                        return occurrence;
                }
            }
            // استدعاء دالة جلب المخالفات عند تحميل الصفحة
            fetchViolations();
        });
    </script>
@endsection

@section('content')
    <div class="row">
        <!-- بطاقة معلومات الموظف والحضور -->
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-body pb-0">
                    <div class="row">
                        <!-- معلومات الموظف والتاريخ -->
                        <div class="col-lg-6 mb-4">
                            <div class="d-flex">
                                @php
                                    $defaultImg = asset('assets/img/branding/Alburhan-Logo.png');
                                    $avatar =
                                        $attendance->user->employee && $attendance->user->employee->profile_picture
                                            ? asset('storage/' . $attendance->user->employee->profile_picture)
                                            : $defaultImg;
                                @endphp
                                <div class="flex-shrink-0 me-3">
                                    <img src="{{ $avatar }}" alt="{{ $attendance->user->name }}"
                                        style="width: 64px; height: 64px; border-radius: 50%; object-fit: cover; border: 1px solid #eee;">
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-1">{{ $attendance->user->name }}</h5>
                                    <div>
                                        <i class="ti ti-calendar-event me-1"></i>
                                        {{ $attendance->date->format('Y-m-d') }}
                                        <span class="mx-2">|</span>
                                        {{ $attendance->date->locale('ar')->dayName }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row pt-2">
                        <!-- معلومات وقت الدخول -->
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="border rounded p-3 h-100">
                                <h6 class="mb-3 border-bottom pb-2">
                                    <i class="ti ti-login me-1"></i>
                                    وقت الدخول
                                </h6>
                                <div>
                                    {{ $attendance->check_in_time ? $attendance->check_in_time->format('H:i:s') : '—' }}
                                </div>
                            </div>
                        </div>

                        <!-- معلومات وقت الخروج -->
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="border rounded p-3 h-100">
                                <h6 class="mb-3 border-bottom pb-2">
                                    <i class="ti ti-logout me-1"></i>
                                    وقت الخروج
                                </h6>
                                <div>
                                    {{ $attendance->check_out_time ? $attendance->check_out_time->format('H:i:s') : '—' }}
                                </div>
                            </div>
                        </div>

                        <!-- تصنيف المخالفة -->
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="border rounded p-3 h-100">
                                <h6 class="mb-3 border-bottom pb-2">
                                    <i class="ti ti-alert-triangle me-1"></i>
                                    نوع المخالفة
                                </h6>
                                <div>
                                    @if ($violationType == 'delay')
                                        <span class="badge bg-warning">
                                            <i class="ti ti-clock me-1"></i>
                                            تأخير ({{ $duration }}
                                            {{ $durationUnit == 'minutes' ? 'دقيقة' : 'ساعة' }})
                                        </span>
                                    @elseif($violationType == 'early_leave')
                                        <span class="badge bg-warning">
                                            <i class="ti ti-door-exit me-1"></i>
                                            خروج مبكر ({{ $duration }}
                                            {{ $durationUnit == 'minutes' ? 'دقيقة' : 'ساعة' }})
                                        </span>
                                    @elseif($violationType == 'absence')
                                        <span class="badge bg-danger">
                                            <i class="ti ti-user-off me-1"></i>
                                            غياب ({{ $duration }} {{ $durationUnit == 'days' ? 'يوم' : 'ساعة' }})
                                        </span>
                                    @elseif($violationType == 'after_hours')
                                        <span class="badge bg-info">
                                            <i class="ti ti-clock-plus me-1"></i>
                                            تواجد بعد ساعات العمل ({{ $duration }}
                                            {{ $durationUnit == 'minutes' ? 'دقيقة' : 'ساعة' }})
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">غير محدد</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- قائمة المخالفات المتاحة -->
        <div class="col-12">
            <div class="card">
                <div class="card-body p-4">
                    <div id="violations-container">
                        <div class="text-center p-5">
                            <div class="spinner-border text-primary"></div>
                            <p class="mt-3">جاري تحميل المخالفات المناسبة...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
