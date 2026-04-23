@extends('layouts.layoutMaster')

@section('title', 'تفاصيل الجلسة ')

@section('breadcrumb')
    <li><a href="#">الشؤون القانونية</a></li>
    <li><a href="{{ route('legal-affairs.sessions.index') }}"> الجلسات</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> تفاصيل الجلسة </a>
        <i class="ti ti-star favorite-icon" data-page-name="تفاصيل الجلسة " data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tributejs/5.1.3/tribute.css"
        integrity="sha512-GnwBnXd+ZGO9CdP343MUr0jCcJXCr++JVtQRnllexRW2IDq4Zvrh/McTQjooAKnSUbXZ7wamp7AQSweTnfMVoA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Tribute.js JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tributejs/5.1.3/tribute.min.js"
        integrity="sha512-KJYWC7RKz/Abtsu1QXd7VJ1IJua7P7GTpl3IKUqfa21Otg2opvRYmkui/CXBC6qeDYCNlQZ7c+7JfDXnKdILUA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // إنشاء خريطة بين name و id
            var nameToIdMap = {
                @foreach ($employees as $employee)
                    '{{ addslashes($employee->name) }}': '{{ $employee->id }}',
                @endforeach
            };

            var tribute = new Tribute({
                values: [
                    @foreach ($employees as $employee)
                        {
                            key: '{{ addslashes($employee->name) }}', // استخدام name كمفتاح
                            value: '{{ addslashes($employee->name) }}#', // تضمين id مع name
                            label: '{{ addslashes($employee->name) }}' // عرض الاسم كعلامة
                        },
                    @endforeach
                ],
                lookup: 'key', // البحث بناءً على 'key' وهو 'name'
                fillAttr: 'value', // ملء النص بـ 'name'
                replace: function(mention) {
                    return '@' + mention.original.value; // إزالة إضافة المسافة هنا
                },
                menuItemTemplate: function(item) {
                    return '<div>' + item.original.label + '</div>'; // عرض الاسم فقط
                }
            });

            tribute.attach(document.getElementById('content'));

            window.nameToIdMap = nameToIdMap;
        });
    </script>

    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-12 col-md-8">
            <div class="card">
                <div class="card-header py-4 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-calendar-event text-primary me-2"></i>
                        تفاصيل الجلسة
                    </h6>
                    <span class="badge bg-{{ $session->session_status->color() }}">
                        {{ $session->session_status->label() }}
                    </span>
                </div>

                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body px-5">
                    <div class="table-responsive">
                        <table class="table table-striped small text-center">
                            <tr>
                                <td class="fw-bold">اسم الجلسة</td>
                                <td>{{ $session->session_name }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold ">المشروع</td>
                                <td>
                                    <a href="{{ route('projects.show', $session->project_id) }}"
                                        class="text-decoration-none">
                                        {{ $session->project->project_name }}
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold ">الدعوى</td>
                                <td>
                                    <a href="{{ route('legal-affairs.lawsuits.show', $session->lawsuit_id) }}"
                                        class="text-decoration-none">
                                        {{ $session->lawsuit->name }}
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold ">درجة الجهة</td>
                                <td>
                                    {{ $session->entity_rank->name ?? 'غير محدد' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold ">التاريخ الهجري</td>
                                <td>
                                    {{ $session->hijri_session_date ?? 'غير محدد' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold ">التاريخ الميلادي</td>
                                <td>
                                    {{ $session->session_date ? $session->session_date->format('Y-m-d') : 'غير محدد' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold ">الوقت</td>
                                <td>
                                    {{ $session->session_time ? $session->session_time->format('H:i') : 'غير محدد' }}
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header py-4 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-users text-primary me-2"></i>
                        المكلفين بالجلسة
                    </h6>
                </div>

                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body px-5 pt-0">
                    {{-- عرض المكلفين --}}
                    @if ($session->assignedUsers->count() > 0)
                        <div class="mt-4">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered small">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center">الاسم</th>
                                            <th class="text-center">المنصب</th>
                                            <th class="text-center">رقم الهاتف</th>
                                            <th class="text-center">البريد الإلكتروني</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($session->assignedUsers as $user)
                                            <tr>
                                                <td class="text-center">
                                                    <div class="d-flex align-items-center justify-content-center">
                                                        <div class="avatar avatar-xs me-2">
                                                            <img src="{{ $user->profile_photo_url ?? asset('assets/img/avatars/default.png') }}"
                                                                alt="Avatar" class="rounded-circle">
                                                        </div>
                                                        <a href="{{ route('account.employee.profile', $user->employee->id) }}"
                                                            class="text-decoration-none">
                                                            {{ $user->employee->name }}
                                                        </a>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    @if ($user->employee && $user->employee->job_title)
                                                        <span
                                                            class="badge bg-label-primary">{{ $user->employee->job_title }}</span>
                                                    @else
                                                        <span class="text-muted">غير محدد</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if ($user->employee->mobile)
                                                        <a href="tel:{{ $user->employee->mobile }}"
                                                            class="text-decoration-none">
                                                            {{ $user->employee->mobile }}
                                                        </a>
                                                    @else
                                                        <span class="text-muted">غير محدد</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if ($user->employee->work_email)
                                                        <a href="mailto:{{ $user->employee->work_email }}"
                                                            class="text-decoration-none">
                                                            {{ $user->employee->work_email }}
                                                        </a>
                                                    @else
                                                        <span class="text-muted">غير محدد</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                </div>

            </div>

            <div class="card mt-3">
                <div class="card-header py-4 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-settings text-primary me-2"></i>
                        تفاصيل ضبط الجلسة
                    </h6>
                </div>

                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body px-5">
                    <div class="table-responsive">
                        <table class="table table-striped small text-center">
                            <tr>
                                <td class="fw-bold">التقرير الإجمالي</td>
                                <td>{{ $session->summary_report_status->label() }}</td>

                            </tr>

                            @if ($session->last_objection_deadline)
                                <tr>
                                    <td class="fw-bold">تاريخ آخر مهلة للاعتراض</td>
                                    <td>{{ $session->last_objection_deadline->format('Y-m-d') }}</td>
                                </tr>
                            @endif

                            @if ($session->session_type)
                                <tr>
                                    <td class="fw-bold ">نوع الجلسة</td>
                                    <td>{{ $session->sessionType->name }}</td>
                                </tr>
                            @endif

                            @if ($session->rule_type)
                                <tr>
                                    <td class="fw-bold">نوع الحكم</td>
                                    <td>{{ $session->ruleType->name }}</td>
                                </tr>
                            @endif

                            @if ($session->execution_format)
                                <tr>
                                    <td class="fw-bold">صيغة تنفيذية</td>
                                    <td>{{ $session->execution_format == 'yes' ? 'نعم' : 'لا' }}</td>
                                </tr>
                            @endif

                            @if ($session->expected_execution_date)
                                <tr>
                                    <td class="fw-bold">التاريخ المتوقع للتنفيذ</td>
                                    <td>{{ $session->expected_execution_date->format('Y-m-d') }}</td>
                                </tr>
                            @endif

                            <tr>
                                <td class="fw-bold ">دقائق التنفيذ</td>
                                <td>{{ $session->execution_minutes }}</td>
                            </tr>

                            <tr>
                                <td class="fw-bold ">طريقة إرسال التقرير </td>
                                <td>{{ $session->report_sending_method }}</td>
                            </tr>


                        </table>
                    </div>
                </div>

            </div>
            @if ($session->session_control_attached || $session->rule_attached)
                <div class="card mt-3">
                    <div class="card-header py-4 d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0">
                            <i class="ti ti-paperclip text-primary me-2"></i>
                            مرفقات الجلسة
                        </h6>
                    </div>

                    <div class="border-1 border-light border-dashed mb-2"></div>

                    <div class="card-body px-5 pt-0">

                        <div class="mt-4">

                            <div class="row g-3">
                                @if ($session->session_control_attached)
                                    <div class="col-md-6">
                                        <div class="card border border-primary">
                                            <div class="card-body text-center py-3">
                                                <i class="ti ti-file-text text-primary fs-1 mb-2"></i>
                                                <h6 class="card-title mb-2">ضبط الجلسة</h6>
                                                <a href="{{ Storage::url($session->session_control_attached) }}"
                                                    target="_blank" class="btn btn-outline-primary btn-sm">
                                                    <i class="ti ti-download me-1"></i>
                                                    تحميل
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if ($session->rule_attached)
                                    <div class="col-md-6">
                                        <div class="card border border-success">
                                            <div class="card-body text-center py-3">
                                                <i class="ti ti-file-certificate text-success fs-1 mb-2"></i>
                                                <h6 class="card-title mb-2">الحكم</h6>
                                                <a href="{{ Storage::url($session->rule_attached) }}" target="_blank"
                                                    class="btn btn-outline-success btn-sm">
                                                    <i class="ti ti-download me-1"></i>
                                                    تحميل
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                    </div>

                </div>
            @endif

        </div>

        <div class="col-12 col-md-4">
            @if ($session->session_status->canEditOrDelete() && $session->isSessionExpired())
                <div class="card mb-3">
                    <div class="card-header py-4 d-flex justify-content-center">
                        <a title="استكمال ضبط الجلسة"
                            href="{{ route('legal-affairs.sessions.session-completion.completion', $session->id) }}"
                            class="btn btn-sm btn-primary ">
                            استكمال ضبط الجلسة
                            <i class="ti ti-clipboard-check"></i>
                        </a>
                    </div>
                </div>
            @endif


            @if (
                ($session->summary_report_status->value == 'formal_ruling' ||
                    $session->summary_report_status->value == 'substantive_ruling') &&
                    is_null($session->objection_status))
                {{-- إضافة الحقول المخفية --}}
                <input type="hidden" id="last_objection_deadline" value="{{ $session->last_objection_deadline }}">
                <input type="hidden" id="session_id_id" value="{{ $session->id }}">

                <div class="card mb-3" id="countdown_container">
                    <div class="card-header py-4">
                        <h6 class="card-title mb-0">
                            <i class="ti ti-clock text-info me-2"></i>
                            تقديم الاعتراض
                        </h6>
                    </div>
                    <div class="border-1 border-light border-dashed mb-2"></div>

                    <div class="card-body">
                        <p class="card-title text-secondary">التقرير الإجمالي :
                            {{ $session->summary_report_status->label() }}
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <label for="countdown" class="form-label small text-muted">الوقت المتبقي للاعتراض:</label>
                                <div id="countdown" class="text-danger small"></div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger" id="objection_button"
                                style="display: none;">
                                تقديم الاعتراض
                            </button>
                        </div>
                        <div class="progress mt-2" style="height: 8px;">
                            <div id="progress-bar" class="progress-bar bg-danger" role="progressbar" style="width: 0%;"
                                aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            @endif


            <div class="card mb-3">
                <div class="card-header py-4 d-flex justify-content-center">
                    <button type="button" class="btn btn-sm btn-outline-primary"
                        onclick="openComments({{ $session->id }})">
                        <i class="ti ti-message-circle me-1"></i>
                        التعليقات
                    </button>
                </div>
            </div>

            <div class="offcanvas offcanvas-end" tabindex="-1" id="commentsOffcanvas"
                aria-labelledby="commentsOffcanvasLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="commentsOffcanvasLabel">
                        التعليقات على الجلسة
                    </h5>
                </div>
                <div class="offcanvas-body">
                    <!-- عرض التعليقات -->
                    <div id="commentsContent">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">جاري التحميل...</span>
                            </div>
                        </div>
                    </div>

                    <!-- نموذج إضافة تعليق جديد -->
                    <div class="border-top pt-3 mt-3">
                        <form id="addCommentForm">
                            <div class="mb-3">
                                <label for="content" class="form-label">إضافة تعليق جديد</label>
                                <textarea name="content" id="content" class="form-control" rows="3" placeholder="اكتب تعليقك هنا..."
                                    required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="ti ti-send me-1"></i>
                                إرسال التعليق
                            </button>
                        </form>
                    </div>
                </div>
            </div>


            <script>
                $(document).ready(function() {
                    // 1. سكريبت العد التنازلي للاعتراض
                    initObjectionCountdown();

                    // 2. تفعيل tooltips
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl);
                    });
                });

                // دالة العد التنازلي للاعتراض
                function initObjectionCountdown() {
                    var countdownInterval;
                    var objectionDeadlineDateStr = $('#last_objection_deadline').val();

                    if (!objectionDeadlineDateStr) {
                        return;
                    }

                    var objectionDeadlineDate = new Date(objectionDeadlineDateStr);
                    if (isNaN(objectionDeadlineDate.getTime())) {
                        return;
                    }

                    var totalTime = objectionDeadlineDate.getTime() - new Date().getTime();

                    if (totalTime <= 0) {
                        $('#countdown').text("انتهت مهلة الاعتراض");
                        $('#objection_button').prop('disabled', true)
                            .removeClass('btn-outline-danger')
                            .addClass('btn-secondary')
                            .html('<i class="ti ti-clock-x me-1"></i>انتهت المهلة');
                        // $('#countdown_container').addClass('d-none');
                        return;
                    }

                    countdownInterval = setInterval(function() {
                        var now = new Date().getTime();
                        var timeLeft = objectionDeadlineDate - now;

                        if (timeLeft <= 0) {
                            clearInterval(countdownInterval);
                            $('#countdown').text("انتهت مهلة الاعتراض");
                            $('#objection_button').prop('disabled', true)
                                .removeClass('btn-outline-danger')
                                .addClass('btn-secondary')
                                .html('<i class="ti ti-clock-x me-1"></i>انتهت المهلة');
                            // $('#countdown_container').addClass('d-none');

                            if (typeof toastr !== 'undefined') {
                                toastr.warning("انتهت المهلة المحددة للاعتراض", "تنبيه");
                            }
                            return;
                        }

                        var days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
                        var hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                        var minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
                        var seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);

                        $('#countdown').text(days + " يوم " + hours + " ساعة " + minutes + " دقيقة " + seconds + " ثانية");

                        var progressPercentage = (timeLeft / totalTime) * 100;
                        $('#progress-bar').css('width', progressPercentage + '%');
                        $('#progress-bar').attr('aria-valuenow', progressPercentage);

                        $('#objection_button').show();
                    }, 1000);

                    // إصلاح دالة زر الاعتراض مع SweetAlert2
                    $('#objection_button').on('click', function() {
                        var button = $(this);

                        Swal.fire({
                            title: 'هل أنت متأكد من تقديم الاعتراض؟',
                            text: "لا يمكن التراجع عن هذا الإجراء!",
                            icon: 'warning',
                            showCancelButton: true,
                            showConfirmButton: true,
                            showDenyButton: false,
                            buttonsStyling: false,
                            customClass: {
                                popup: 'custom-popup',
                                title: 'custom-title',
                                text: 'custom-text',
                                confirmButton: 'btn btn-success custom-confirm',
                                cancelButton: 'btn btn-danger custom-cancel'
                            },
                            confirmButtonText: 'تأكيد الاعتراض',
                            cancelButtonText: 'إلغاء',
                            reverseButtons: false,
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // تعطيل الزر وإظهار حالة التحميل
                                button.prop('disabled', true).html(
                                    '<i class="spinner-border spinner-border-sm me-1"></i> جاري التقديم...'
                                );

                                // إرسال طلب AJAX
                                $.ajax({
                                    url: "{{ route('legal-affairs.sessions.objection', $session->id) }}",
                                    method: 'POST',
                                    data: {
                                        _token: $('meta[name="csrf-token"]').attr('content')
                                    },
                                    success: function(response) {
                                        if (response.success) {
                                            toastr.success('تم تقديم الاعتراض بنجاح');

                                            window.location.reload();

                                        } else {
                                            // عرض رسالة خطأ
                                            toastr.error('حدث خطأ أثناء تقديم الاعتراض');
                                            // إعادة تفعيل الزر
                                            button.prop('disabled', false).html('تقديم الاعتراض');
                                        }
                                    },
                                    error: function(xhr) {
                                        toastr.error(
                                            'حدث خطأ أثناء تقديم الاعتراض الرجاء مراجعة الدعم الفني'
                                        );

                                        button.prop('disabled', false).html('تقديم الاعتراض');
                                    }
                                });
                            }
                        });
                    });
                }

                function parseMentions(content) {
                    return content.replace(/@([\p{L}]+(?:\s[\p{L}]+)*)/gu, function(match, p1) {
                        var id = window.nameToIdMap[p1.trim()];
                        if (id) {
                            return `<a href="/account/employee/${id}/profile" class="mention">@${p1}</a>`;
                        } else {
                            return match; // اترك النص كما هو إذا لم يتم العثور على id
                        }
                    });
                }

                // دالة فتح التعليقات
                function openComments(sessionId) {
                    const commentsOffcanvas = new bootstrap.Offcanvas(document.getElementById('commentsOffcanvas'));

                    // جلب التعليقات عبر AJAX
                    fetch("{{ route('legal-affairs.sessions.comments.index', $session->id) }}")
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            let commentsHtml = '';

                            if (data.comments && data.comments.length > 0) {
                                data.comments.forEach(comment => {
                                    commentsHtml += `
                        <div class="d-flex mb-3 p-3 border rounded">
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-sm">
                                    <span class="avatar-initial rounded-circle bg-primary">
                                        ${comment.user.name.charAt(0)}
                                    </span>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong class="text-primary">${comment.user.name}</strong>
                                    <small class="text-muted">${comment.created_at}</small>
                                </div>
                                <p class="mb-0">${parseMentions(comment.content)}</p>
                                     ${comment.can_edit_delete_old ? `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="d-flex">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <button class="btn btn-sm btn-link text-primary edit-comment-button" data-id="${comment.id}"><i class="bi bi-pencil-square"></i> تعديل</button>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <button class="btn btn-sm btn-link text-danger delete-comment-button" data-id="${comment.id}"><i class="bi bi-trash"></i> حذف</button>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                        ` : ''}
                            </div>


                        </div>
                    `;
                                });
                            } else {
                                commentsHtml = `
                    <div class="text-center py-4">
                        <i class="ti ti-message-circle-off" style="font-size: 3rem; color: #ccc;"></i>
                        <p class="text-muted mt-2">لا توجد تعليقات لعرضها.</p>
                    </div>
                `;
                            }

                            document.getElementById('commentsContent').innerHTML = commentsHtml;
                            commentsOffcanvas.show();
                        })
                        .catch(error => {
                            console.error('Error fetching comments:', error);
                            document.getElementById('commentsContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="ti ti-alert-circle me-2"></i>
                    حدث خطأ أثناء تحميل التعليقات
                </div>
            `;
                            commentsOffcanvas.show();
                        });
                }

                // إرسال تعليق جديد
                document.getElementById('addCommentForm').addEventListener('submit', function(event) {
                    event.preventDefault();

                    const formData = new FormData(this);
                    const sessionId = formData.get('session_id');
                    const content = formData.get('content');

                    if (!content.trim()) {
                        if (typeof toastr !== 'undefined') {
                            toastr.warning('يرجى كتابة محتوى التعليق');
                        }
                        return;
                    }

                    const submitButton = this.querySelector('button[type="submit"]');
                    const originalText = submitButton.innerHTML;
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<i class="spinner-border spinner-border-sm me-1"></i> جاري الإرسال...';

                    fetch("{{ route('legal-affairs.sessions.comments.store', $session->id) }}", {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content'),
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                content: content.trim()
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const newCommentHtml = `
                <div class="d-flex mb-3 p-3 border rounded">
                    <div class="flex-shrink-0">
                        <div class="avatar avatar-sm">
                            <span class="avatar-initial rounded-circle bg-primary">
                                ${data.comment.user.name.charAt(0)}
                            </span>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong class="text-primary">${data.comment.user.name}</strong>
                            <small class="text-muted">${data.comment.created_at}</small>
                        </div>
                        <p class="mb-0">${parseMentions(data.comment.content)}</p>
                    </div>
                </div>
            `;

                                // إضافة التعليق في المقدمة
                                const commentsContent = document.getElementById('commentsContent');
                                if (commentsContent.innerHTML.includes('لا توجد تعليقات')) {
                                    commentsContent.innerHTML = newCommentHtml;
                                } else {
                                    commentsContent.insertAdjacentHTML('afterbegin', newCommentHtml);
                                }

                                // إعادة تعيين النموذج
                                document.getElementById('addCommentForm').reset();

                                if (typeof toastr !== 'undefined') {
                                    toastr.success('تم إضافة التعليق بنجاح');
                                }
                            } else {
                                if (typeof toastr !== 'undefined') {
                                    toastr.error(data.message || 'حدث خطأ أثناء إضافة التعليق');
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Error adding comment:', error);
                            if (typeof toastr !== 'undefined') {
                                toastr.error('حدث خطأ أثناء إضافة التعليق');
                            }
                        })
                        .finally(() => {
                            submitButton.disabled = false;
                            submitButton.innerHTML = originalText;
                        });
                });

                // دالة تأكيد الحذف
                function confirmDelete(sessionId) {
                    if (confirm('هل أنت متأكد من حذف هذه الجلسة؟\nهذا الإجراء لا يمكن التراجع عنه.')) {
                        document.getElementById('delete-form-' + sessionId).submit();
                    }
                }

                // منع النقر على الروابط المعطلة
                document.addEventListener('DOMContentLoaded', function() {
                    const disabledLinks = document.querySelectorAll('.dropdown-item.disabled');
                    disabledLinks.forEach(function(link) {
                        link.addEventListener('click', function(e) {
                            e.preventDefault();
                            const title = link.getAttribute('title');
                            if (title) {
                                if (typeof toastr !== 'undefined') {
                                    toastr.warning(title, 'تنبيه');
                                } else {
                                    alert(title);
                                }
                            }
                        });
                    });
                });


                //
                // document.getElementById('commentsContent').addEventListener('click', function(event) {
                //     // تعديل التعليق
                //     if (event.target.classList.contains('edit-comment-button') || event.target.closest(
                //             '.edit-comment-button')) {
                //         const button = event.target.closest('.edit-comment-button');
                //         const commentId = button.getAttribute('data-id');

                //         // جلب بيانات التعليق عبر AJAX
                //         fetch(`/sessions/${sessionId}/comments/${commentId}`)
                //             .then(response => response.json())
                //             .then(data => {
                //                 if (!data.success) {
                //                     toastr.error(data.message || 'حدث خطأ أثناء جلب بيانات التعليق.');
                //                 } else {
                //                     // تعبئة النموذج ببيانات التعليق
                //                     editCommentIdInput.value = data.comment.id;
                //                     editCommentContentInput.value = data.comment.content;

                //                     // إظهار نموذج التعديل
                //                     editCommentFormContainer.style.display = 'block';
                //                 }
                //             })
                //             .catch(error => {
                //                 console.error('Error fetching comment details:', error);
                //                 toastr.error('حدث خطأ أثناء جلب بيانات التعليق.');
                //             });
                //     }

                //     // حذف التعليق
                //     if (event.target.classList.contains('delete-comment-button') || event.target.closest(
                //             '.delete-comment-button')) {
                //         const button = event.target.closest('.delete-comment-button');
                //         const commentId = button.getAttribute('data-id');
                //         const sessionId = document.getElementById('session_id').value;

                //         // تأكيد الحذف
                //         Swal.fire({
                //             title: 'هل أنت متأكد من عملية الحذف؟',
                //             text: "لا يمكن التراجع عن هذا الإجراء!",
                //             icon: 'warning',
                //             showCancelButton: true,
                //             buttonsStyling: false,
                //             customClass: {
                //                 confirmButton: 'btn btn-success me-2',
                //                 cancelButton: 'btn btn-danger'
                //             },
                //             confirmButtonText: 'تأكيد',
                //             cancelButtonText: 'إلغاء',
                //             reverseButtons: false,
                //         }).then((result) => {
                //             if (result.isConfirmed) {
                //                 // إرسال طلب الحذف
                //                 fetch(`/sessions/${sessionId}/comments/${commentId}`, {
                //                         method: 'DELETE',
                //                         headers: {
                //                             'X-CSRF-TOKEN': '{{ csrf_token() }}',
                //                             'Accept': 'application/json',
                //                         }
                //                     })
                //                     .then(response => response.json())
                //                     .then(data => {
                //                         if (data.success) {
                //                             // إزالة التعليق من القائمة
                //                             const commentElement = document.querySelector(
                //                                 `[data-comment-id="${commentId}"]`);
                //                             if (commentElement) {
                //                                 commentElement.remove();
                //                             }
                //                             toastr.success('تم حذف التعليق بنجاح.');
                //                         } else {
                //                             toastr.error(data.message || 'حدث خطأ أثناء حذف التعليق.');
                //                         }
                //                     })
                //                     .catch(error => {
                //                         console.error('Error deleting comment:', error);
                //                         toastr.error('حدث خطأ أثناء حذف التعليق.');
                //                     });
                //             }
                //         });
                //     }
                // });
            </script>



            <!-- بطاقة الإحصائيات -->
            <div class="card mb-3">
                <div class="card-header py-4">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-chart-bar text-info me-2"></i>
                        إحصائيات سريعة
                    </h6>
                </div>
                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body">
                    <div class="row g-3 text-center">
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <div class="text-primary fs-4 fw-bold">{{ $session->assignedUsers->count() }}</div>
                                <small class="text-muted">المكلفين</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <div class="text-success fs-4 fw-bold">
                                    {{ $session->lawsuit->sessions()->count() }}
                                </div>
                                <small class="text-muted">جلسات الدعوى</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <div class="text-primary fs-4 fw-bold">{{ $session->comments->count() }}</div>
                                <small class="text-muted">التعليقات</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- بطاقة سجل النشاطات -->
            <div class="card">
                <div class="card-header py-4">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-clock-history text-warning me-2"></i>
                        سجل النشاطات
                    </h6>
                </div>
                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body">
                    <ul class="timeline">
                        <li class="timeline-item">
                            <span class="timeline-point bg-primary"></span>
                            <div class="timeline-event">
                                <small class="timeline-title text-capitalize">
                                    تمت الإضافة بتاريخ
                                </small>
                                <small class="text-muted d-block">
                                    {{ $session->created_at->format('Y-m-d H:i') }}
                                </small>
                                <small>
                                    <b>أضيف بواسطة</b>
                                    @if ($session->createdBy)
                                        <a href="{{ route('account.employee.profile', $session->created_by) }}"
                                            class="text-decoration-none">
                                            {{ $session->createdBy->name }}
                                        </a>
                                    @else
                                        <span class="text-muted">غير محدد</span>
                                    @endif
                                </small>
                            </div>
                        </li>

                        @if ($session->updated_by && $session->updated_at)
                            <li class="timeline-item">
                                <span class="timeline-point bg-success"></span>
                                <div class="timeline-event">
                                    <small class="timeline-title text-capitalize">
                                        تم التعديل بتاريخ
                                    </small>
                                    <small class="text-muted d-block">
                                        {{ $session->updated_at->format('Y-m-d H:i') }}
                                    </small>
                                    <small>
                                        <b>التعديل بواسطة</b>
                                        <a href="{{ route('account.employee.profile', $session->updated_by) }}"
                                            class="text-decoration-none">
                                            {{ $session->updatedBy->name }}
                                        </a>
                                    </small>
                                </div>
                            </li>
                        @endif

                        @if ($session->session_status->value === 'inactive')
                            <li class="timeline-item">
                                <span class="timeline-point bg-danger"></span>
                                <div class="timeline-event">
                                    <small class="timeline-title text-capitalize">
                                        تم إغلاق الجلسة
                                    </small>

                                </div>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>



@endsection
