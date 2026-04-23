<!-- Tribute.js CSS -->
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

        tribute.attach(document.getElementById('comment_content'));
        tribute.attach(document.getElementById('edit_comment_content'));

        // تخزين الخريطة في نافذة global للوصول إليها في parseMentions
        window.nameToIdMap = nameToIdMap;
    });
</script>

<div class="col-12 col-lg-12 pt-6 pt-lg-0">
    <div class="card card-action mb-6">
        <div class="card-header align-items-center bg-white border-bottom mb-5">
            <h5 class="card-action-title mb-0 text-primary fw-bold">
                <i class="ti ti-calendar  ti-lg text-body me-3 text-primary fw-bold"></i> الجلسات
            </h5>

            @if ($lawsuit->lawsuit_status->value == 'inactive')
                <button type="button" class="btn btn-primary ms-auto" disabled>
                    هذه الدعوى مغلقة لا يمكن اضافة جلسات فيها
                </button>
            @elseif($hasActiveSession)
                <button type="button" class="btn btn-primary ms-auto" disabled
                    title="لا يمكنك إضافة جلسة جديدة لأن هناك جلسة لم تغلق بعد.">
                    لا يمكنك إضافة جلسة جديدة لأن اخر جلسة لم تغلق بعد
                </button>
            @else
                <!-- زر يسمح بإضافة جلسة جديدة -->
                <a href="{{ route('legal-affairs.sessions.create') }}" class="btn btn-primary ms-auto">
                    <i class="fas fa-plus mx-2"></i> إضافة جلسة جديدة
                </a>
            @endif
        </div>
        <div class="card-body pt-3">
            <div class="row">
                <!-- عرض الجلسات باستخدام الكارد -->
                @foreach ($sessions as $session)
                    <input type="hidden" id="last_objection_deadline" value="{{ $session->last_objection_deadline }}">
                    <input type="hidden" id="session_id_id" value="{{ $session->id }}">
                    <!-- إضافة حقل مخفي لمعرف الدعوى -->
                    <input type="hidden" id="lawsuit_id" value="{{ $lawsuit->id }}">

                    <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
                        <div class="card border-2 border-primary">
                            <a href="{{ route('legal-affairs.sessions.show', $session->id) }}">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3 pb-1">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar me-2">
                                                <i class="ti ti-calendar  ti-xl me-1_5 text-primary"></i>
                                            </div>
                                            <div class="me-1 text-heading h5 mb-0 text-primary fw-bold">
                                                <small>{{ \Illuminate\Support\Str::limit($session->session_name, 20, '...') }}</small>
                                            </div>

                                        </div>
                                        <div class="ms-auto">
                                            <ul class="list-inline mb-0 d-flex align-items-center">
                                                <span class="badge bg-{{ $session->session_status->color() }}">
                                                    {{ $session->session_status->label() }}
                                                </span>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="mb-3 pb-1">
                                        <p class="mb-2">
                                            <span class="fw-bold">تاريخ الجلسة :</span>
                                            {{ $session->hijri_session_date }}
                                        </p>
                                        <p class="mb-2">
                                            <span class="fw-bold">زمن الجلسة :</span>
                                            {{ \Carbon\Carbon::parse($session->session_time)->format('H:i') }}
                                        </p>

                                    </div>

                                    <ul class="list-group list-group-flush">
                                        <li
                                            class="list-group-item d-flex justify-content-between align-items-center flex-wrap p-0">
                                            <div class="d-flex flex-wrap align-items-center">
                                                <ul
                                                    class="list-unstyled users-list d-flex align-items-center avatar-group m-0 me-2">
                                                    {{-- @foreach ($session->assignedEmployees->take(3) as $employee)
                                                    <li data-bs-toggle="tooltip" data-popup="tooltip-custom"
                                                        data-bs-placement="bottom" class="avatar pull-up"
                                                        aria-label="{{ $employee->name }}"
                                                        data-bs-original-title="{{ $employee->name }}">
                                                        <img class="rounded-circle"
                                                            src="{{ $employee->avatar ? asset($employee->avatar) : asset('assets/img/branding/Alburhan-Logo.png') }}"
                                                            alt="Avatar">
                                                    </li>
                                                @endforeach --}}

                                                    {{-- @if ($session->assignedEmployees->count() > 3)
                                                    <li class="avatar">
                                                        <span type="button"
                                                            class="avatar-initial rounded-circle pull-up text-heading"
                                                            data-bs-placement="bottom" data-bs-toggle="modal"
                                                            data-bs-target="#employeesModal"
                                                            title="عرض جميع المكلفين">
                                                            +{{ $session->assignedEmployees->count() - 3 }}</span>
                                                    </li>
                                                @endif

                                                @if ($session->assignedEmployees->count() == 0)
                                                    <li class="mb-4">
                                                        لا يوجد مكلفين
                                                    </li>
                                                @endif --}}

                                                </ul>
                                            </div>
                                        </li>
                                    </ul>

                                    <!-- مودال عرض جميع الموظفين -->
                                    <div class="modal fade" id="employeesModal" tabindex="-1"
                                        aria-labelledby="employeesModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="employeesModalLabel">قائمة الموظفين
                                                        المخصصين
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="إغلاق"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <ul class="list-group list-group-flush">
                                                        {{-- @foreach ($session->assignedEmployees as $employee)
                                                        <li class="list-group-item d-flex align-items-center">
                                                            <div class="me-3">
                                                                <img class="rounded-circle"
                                                                    src="{{ $employee->avatar ? asset($employee->avatar) : asset('assets/img/branding/Alburhan-Logo.png') }}"
                                                                    alt="Avatar" width="50" height="50"
                                                                    loading="lazy">
                                                            </div>
                                                            <div>
                                                                <strong>{{ $employee->name }}</strong>
                                                            </div>
                                                        </li>
                                                    @endforeach --}}
                                                    </ul>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">إغلاق</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                </div>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </div>
    <style>
        /* تخصيص شريط التمرير في offcanvas */
        .offcanvas-body {
            scrollbar-width: thin;
            /* لفايرفوكس */
            scrollbar-color: var(--primary-color) #f1f1f1;
            /* تخصيص ألوان شريط التمرير في فايرفوكس */
        }

        /* تخصيص شريط التمرير في كروم وسفاري وإيدج */
        .offcanvas-body::-webkit-scrollbar {
            width: 8px;
        }

        .offcanvas-body::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 8px;
        }

        .offcanvas-body::-webkit-scrollbar-thumb {
            background-color: var(--primary-color);
            border-radius: 8px;
        }

        .offcanvas-body::-webkit-scrollbar-thumb:hover {
            background-color: #0056b3;
        }
    </style>

    <!-- شاشة جانبية للتعليقات -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="commentsOffcanvas" aria-labelledby="commentsOffcanvasLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="commentsOffcanvasLabel">التعليقات على الجلسة</h5>
        </div>
        <div class="offcanvas-body">
            <!-- عرض التعليقات -->
            <div id="commentsContent">
                <!-- سيتم تعبئة التعليقات هنا عبر AJAX -->
            </div>
            <!-- نموذج إضافة تعليق جديد -->
            <div class="mt-4">
                <h6>إضافة تعليق جديد</h6>
                <form id="addCommentForm" method="POST">
                    @csrf
                    <input type="hidden" name="session_id" id="session_id" value="">
                    <div class="mb-3 position-relativ">
                        <label for="comment_content" class="form-label">نص التعليق</label>
                        <textarea class="form-control" id="comment_content" name="content" rows="3" required></textarea>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">إضافة التعليق</button>
                    </div>
                </form>
            </div>
            <!-- نموذج تعديل التعليق -->
            <div id="editCommentFormContainer" style="display: none;">
                <h6>تعديل التعليق</h6>
                <form id="editCommentForm" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="comment_id" id="edit_comment_id" value="">
                    <div class="mb-3">
                        <label for="edit_comment_content" class="form-label">نص التعليق</label>
                        <textarea class="form-control" id="edit_comment_content" name="content" rows="3" required></textarea>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-secondary me-2" id="cancelEditComment">إلغاء</button>
                        <button type="submit" class="btn btn-primary">تحديث التعليق</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- زر عرض المزيد يظهر فقط إذا كان هناك صفحات إضافية -->

    <style>
        .custom-pagination .text-muted {
            display: none !important;
        }
    </style>
    <div class="d-flex justify-content-center mt-4">
        <div class="custom-pagination">
            {{ $sessions->links('pagination::bootstrap-5') }}
        </div>
    </div>

    <!-- مودال عرض تفاصيل الجلسة -->
    <div class="modal fade" id="sessionModal" tabindex="-1" aria-labelledby="sessionDetailModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تفاصيل الجلسة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th scope="row">حالة الجلسة</th>
                                <td id="modalSessionStatus"></td>
                            </tr>
                            <tr>
                                <th scope="row">اسم الجلسة</th>
                                <td id="modalSessionName"></td>
                            </tr>
                            <tr>
                                <th scope="row"> درجة الجهة </th>
                                <td id="modalRank"></td>
                            </tr>
                            <tr>
                                <th scope="row">تاريخ الجلسة </th>
                                <td id="modalGregorianDate"></td>
                            </tr>
                            <tr>
                                <th scope="row">وقت الجلسة</th>
                                <td id="modalSessionTime"></td>
                            </tr>
                            <tr>
                                <th scope="row">نوع الجلسة</th>
                                <td id="modalSessionType"></td>
                            </tr>
                            {{-- <tr>
                                    <th scope="row">أهمية الجلسة</th>
                                    <td id="modalSessionImportance"></td>
                                </tr> --}}

                            <tr>
                                <th scope="row">التقرير الإجمالي</th>
                                <td id="modalSummaryStatus"></td>
                            </tr>

                            <tr>
                                <th scope="row">نوع الحكم</th>
                                <td id="modalRuleType"></td>
                            </tr>
                            <tr>
                                <th scope="row">صيغة التنفيذ</th>
                                <td id="modalExecutionFormat"></td>
                            </tr>
                            <tr>
                                <th scope="row">آخر موعد للاعتراض</th>
                                <td id="modalLastObjectionDeadline"></td>
                            </tr>
                            <tr>
                                <th scope="row">حالة الاعتراض</th>
                                <td id="modalObjectionStatus"></td>
                            </tr>
                            <tr>
                                <th scope="row">دقائق التنفيذ</th>
                                <td id="modalExecutionMinutes"></td>
                            </tr>

                            <tr>
                                <th scope="row">التقرير التفصيلي</th>
                                <td id="modalDetailedReport"></td>
                            </tr>
                            <tr>
                                <th scope="row">مرفق ضبط الجلسة</th>
                                <td id="modalsession_control_attached"></td>
                            </tr>
                            <tr>
                                <th scope="row">مرفق الحكم</th>
                                <td id="modalRuleAttached"></td>
                            </tr>

                            <tr>
                                <th scope="row">طريقة إرسال التقرير</th>
                                <td id="modalReportMethod"></td>
                            </tr>
                            <!-- أضف باقي الحقول هنا -->
                        </tbody>
                    </table>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                </div>
            </div>
        </div>
    </div>


    <!-- سكريبت لملء بيانات المودال وتحميل المزيد من الجلسات -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            var sessionModal = document.getElementById('sessionModal');
            sessionModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;

                var sessionName = button.getAttribute('data-session-name');
                var sessionRank = button.getAttribute('data-session-rank');

                var gregorianDate = button.getAttribute('data-gregorian-date');
                var sessionTime = button.getAttribute('data-session-time');
                // var sessionImportance = button.getAttribute('data-session-importance');
                var summaryReportStatus = button.getAttribute('data-summary-report-status');
                var executionMinutes = button.getAttribute('data-execution-minutes');
                var reportSendingMethod = button.getAttribute('data-report-sending-method');
                var detailedReport = button.getAttribute('data-detailed-report');
                var sessionControlAttached = button.getAttribute('data-session-control-attached');
                var ruleAttached = button.getAttribute('data-rule-attached');
                var sessionStatus = button.getAttribute('data-session-status');
                var sessionType = button.getAttribute('data-session-type');
                var ruleType = button.getAttribute('data-rule-type');
                var lastObjectionDeadline = button.getAttribute('data-last-objection-deadline');
                var objectionStatus = button.getAttribute('data-objection-status');
                var executionFormat = button.getAttribute('data-execution-format');

                sessionModal.querySelector('#modalSessionName').innerText = sessionName || 'غير محدد';
                sessionModal.querySelector('#modalRank').innerText = sessionRank || 'غير محدد';

                sessionModal.querySelector('#modalGregorianDate').innerText = gregorianDate || 'غير محدد';
                sessionModal.querySelector('#modalSessionTime').innerText = sessionTime || 'غير محدد';
                // sessionModal.querySelector('#modalSessionImportance').innerText = sessionImportance ||
                //     'غير محدد';
                sessionModal.querySelector('#modalSummaryStatus').innerText = summaryReportStatus ||
                    'غير محدد';
                sessionModal.querySelector('#modalExecutionMinutes').innerText = executionMinutes ||
                    'غير محدد';
                sessionModal.querySelector('#modalReportMethod').innerText = reportSendingMethod ||
                    'غير محدد';
                sessionModal.querySelector('#modalDetailedReport').innerText = detailedReport || 'غير محدد';
                sessionModal.querySelector('#modalSessionStatus').innerText = sessionStatus || 'غير محدد';
                sessionModal.querySelector('#modalSessionType').innerText = sessionType || 'غير محدد';
                sessionModal.querySelector('#modalRuleType').innerText = ruleType || 'غير محدد';
                sessionModal.querySelector('#modalLastObjectionDeadline').innerText =
                    lastObjectionDeadline || 'غير محدد';
                sessionModal.querySelector('#modalObjectionStatus').innerText = objectionStatus ||
                    'غير محدد';
                sessionModal.querySelector('#modalExecutionFormat').innerText = executionFormat ||
                    'غير محدد';

                // التعامل مع المرفقات
                var controlAttachedElement = sessionModal.querySelector('#modalsession_control_attached');
                if (sessionControlAttached) {
                    controlAttachedElement.innerHTML = `
            <a href="/storage/${sessionControlAttached}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-primary">
                عرض مرفق ضبط الجلسة
            </a>
        `;
                } else {
                    controlAttachedElement.innerText = 'غير محدد';
                }

                var ruleAttachedElement = sessionModal.querySelector('#modalRuleAttached');
                if (ruleAttached) {
                    ruleAttachedElement.innerHTML = `
            <a href="/storage/${ruleAttached}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-primary">
                عرض مرفق الحكم
            </a>
        `;
                } else {
                    ruleAttachedElement.innerText = 'غير محدد';
                }

            });
        });

        function confirmDelete(id) {
            Swal.fire({
                title: 'هل أنت متأكد من عملية الحذف؟',
                text: "لا يمكن التراجع عن هذا الإجراء!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'تأكيد',
                cancelButtonText: 'إلغاء',
                reverseButtons: false,
                customClass: {
                    confirmButton: 'btn btn-success me-2',
                    cancelButton: 'btn btn-danger'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            });
        }
    </script>

    <script>
        $(document).ready(function() {
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
                $('#objection_button').hide();
                $('#countdown_container').hide(); // إخفاء العد التنازلي
                return;
            }

            countdownInterval = setInterval(function() {
                var now = new Date().getTime();
                var timeLeft = objectionDeadlineDate - now;

                var days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
                var hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);

                $('#countdown').text(days + " يوم " + hours + " ساعة " + minutes + " دقيقة " + seconds +
                    " ثانية ");

                var progressPercentage = (timeLeft / totalTime) * 100;
                $('#progress-bar').css('width', progressPercentage + '%');

                if (timeLeft > 0) {
                    $('#objection_button').show();
                }

                if (timeLeft <= 0) {
                    clearInterval(countdownInterval);
                    $('#countdown').text("انتهت مهلة الاعتراض");
                    $('#objection_button').hide();
                    $('#countdown_container').hide(); // إخفاء العد التنازلي

                    toastr.success("انتهت المهلة المحدد سيتم اغلاق الدعوى تلقائيا ");

                }
            }, 1000);

            $('#objection_button').on('click', function() {
                clearInterval(countdownInterval);

                $.ajax({
                    url: '/submit-objection',
                    method: 'POST',
                    data: {
                        session_id: $('#session_id_id').val(),
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);

                            $('#countdown_container').hide();
                            $('#objection_button').hide();
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function() {
                        toastr.error('حدث خطأ أثناء تقديم الاعتراض، يرجى المحاولة مرة أخرى.');
                    }
                });
            });
        });
    </script>
    <style>
        .highlight-comment {
            background-color: #ffff99;
            /* لون خلفية فاتح */
            border-left: 4px solid #ffeb3b;
            /* شريط أيسر ملون */
            padding-left: 10px;
            /* transition: background-color 0.5s ease; */
        }
    </style>

    <script>
        function parseMentions(content) {
            return content.replace(/@([\p{L}]+(?:\s[\p{L}]+)*)/gu, function(match, p1) {
                var id = window.nameToIdMap[p1.trim()];
                if (id) {
                    return `<a href="/employees/${id}" class="mention">@${p1}</a>`;
                } else {
                    return match; // اترك النص كما هو إذا لم يتم العثور على id
                }
            });
        }


        // فتح شاشة التعليقات وجلب التعليقات عبر AJAX
        function openCommentsSession(sessionId, commentId = null) {
            const commentsOffcanvas = new bootstrap.Offcanvas(document.getElementById('commentsOffcanvas'));
            const commentsOffcanvasLabel = new bootstrap.Offcanvas(document.getElementById('commentsOffcanvasLabel'));

            document.getElementById('session_id').value = sessionId;

            // طلب التعليقات عبر AJAX
            fetch(`/sessions/${sessionId}/comments`)
                .then(response => response.json())
                .then(data => {
                    let commentsHtml = '';

                    if (data.comments.length > 0) {
                        data.comments.forEach(comment => {
                            commentsHtml += `
                                <div class="d-flex mb-2 comment-item" data-comment-id="${comment.id}">
                                    <div class="flex-shrink-0">
                                        <i class="ti ti-user ti-2x text-primary"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <strong>${comment.user.name}</strong>
                                        <span class="text-muted">${comment.created_at}</span>
                                       <p>${parseMentions(comment.content)}</p>
                                        ${comment.can_edit_delete ? `
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
                        commentsHtml = '<p class="text-muted">لا توجد تعليقات لعرضها.</p>';
                    }


                    // إذا كان هناك commentId، قم بتمييزه
                    if (commentId) {
                        // استخدم setTimeout للتأكد من أن Offcanvas قد فتح بالفعل
                        setTimeout(() => {
                            const commentElement = document.querySelector(
                                `.comment-item[data-comment-id="${commentId}"]`);
                            if (commentElement) {
                                // أضف فئة CSS لتمييز التعليق
                                commentElement.classList.add('highlight-comment');

                                // قم بالتمرير إلى التعليق المحدد
                                commentElement.scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'center'
                                });


                            }
                        }, 500); // تأخير بسيط للتأكد من تحميل التعليقات
                    }

                    document.getElementById('commentsContent').innerHTML = commentsHtml;
                    commentsOffcanvas.show();
                })
                .catch(error => console.error('Error fetching comments:', error));
        }

        // إرسال تعليق جديد عبر AJAX
        document.getElementById('addCommentForm').addEventListener('submit', function(event) {
            event.preventDefault();

            const formData = new FormData(this);
            const sessionId = formData.get('session_id');
            const content = formData.get('content');

            fetch(`/sessions/${sessionId}/comments`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        content
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const newCommentHtml = `
                        <div class="d-flex mb-2" data-comment-id="${data.comment.id}">
                            <div class="flex-shrink-0">
                                <i class="ti ti-user ti-2x text-primary"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <strong>${data.comment.user.name}</strong>
                                <span class="text-muted">${data.comment.created_at}</span>
                                <p>${parseMentions(data.comment.content)}</p>
                                <div class="d-flex">
                                    <button class="btn btn-sm btn-link text-primary edit-comment-button" data-id="${data.comment.id}"><i class="bi bi-pencil-square"></i> تعديل</button>
                                    <button class="btn btn-sm btn-link text-danger delete-comment-button" data-id="${data.comment.id}"><i class="bi bi-trash"></i> حذف</button>
                                </div>
                            </div>
                        </div>
                    `;

                        document.getElementById('commentsContent').insertAdjacentHTML('beforeend',
                            newCommentHtml);
                        document.getElementById('addCommentForm').reset();
                        toastr.success('تم إضافة التعليق بنجاح.');
                    } else {
                        toastr.error('حدث خطأ أثناء إضافة التعليق.');
                    }
                })
                .catch(error => console.error('Error adding comment:', error));
        });

        // تعريف العناصر
        const editCommentFormContainer = document.getElementById('editCommentFormContainer');
        const editCommentForm = document.getElementById('editCommentForm');
        const editCommentIdInput = document.getElementById('edit_comment_id');
        const editCommentContentInput = document.getElementById('edit_comment_content');
        const cancelEditCommentButton = document.getElementById('cancelEditComment');

        // التعامل مع أزرار التعديل والحذف للتعليقات
        document.getElementById('commentsContent').addEventListener('click', function(event) {
            // تعديل التعليق
            if (event.target.classList.contains('edit-comment-button') || event.target.closest(
                    '.edit-comment-button')) {
                const button = event.target.closest('.edit-comment-button');
                const commentId = button.getAttribute('data-id');
                const sessionId = document.getElementById('session_id').value;

                // جلب بيانات التعليق عبر AJAX
                fetch(`/sessions/${sessionId}/comments/${commentId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            toastr.error(data.message || 'حدث خطأ أثناء جلب بيانات التعليق.');
                        } else {
                            // تعبئة النموذج ببيانات التعليق
                            editCommentIdInput.value = data.comment.id;
                            editCommentContentInput.value = data.comment.content;

                            // إظهار نموذج التعديل
                            editCommentFormContainer.style.display = 'block';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching comment details:', error);
                        toastr.error('حدث خطأ أثناء جلب بيانات التعليق.');
                    });
            }

            // حذف التعليق
            if (event.target.classList.contains('delete-comment-button') || event.target.closest(
                    '.delete-comment-button')) {
                const button = event.target.closest('.delete-comment-button');
                const commentId = button.getAttribute('data-id');
                const sessionId = document.getElementById('session_id').value;

                // تأكيد الحذف
                Swal.fire({
                    title: 'هل أنت متأكد من عملية الحذف؟',
                    text: "لا يمكن التراجع عن هذا الإجراء!",
                    icon: 'warning',
                    showCancelButton: true,
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-success me-2',
                        cancelButton: 'btn btn-danger'
                    },
                    confirmButtonText: 'تأكيد',
                    cancelButtonText: 'إلغاء',
                    reverseButtons: false,
                }).then((result) => {
                    if (result.isConfirmed) {
                        // إرسال طلب الحذف
                        fetch(`/sessions/${sessionId}/comments/${commentId}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json',
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // إزالة التعليق من القائمة
                                    const commentElement = document.querySelector(
                                        `[data-comment-id="${commentId}"]`);
                                    if (commentElement) {
                                        commentElement.remove();
                                    }
                                    toastr.success('تم حذف التعليق بنجاح.');
                                } else {
                                    toastr.error(data.message || 'حدث خطأ أثناء حذف التعليق.');
                                }
                            })
                            .catch(error => {
                                console.error('Error deleting comment:', error);
                                toastr.error('حدث خطأ أثناء حذف التعليق.');
                            });
                    }
                });
            }
        });

        // إلغاء عملية التعديل للتعليق
        cancelEditCommentButton.addEventListener('click', function() {
            editCommentFormContainer.style.display = 'none';
            editCommentForm.reset();
        });

        // إرسال نموذج تعديل التعليق
        editCommentForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const commentId = editCommentIdInput.value;
            const content = editCommentContentInput.value;
            const sessionId = document.getElementById('session_id').value;

            fetch(`/sessions/${sessionId}/comments/${commentId}`, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        content: content
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // تحديث التعليق في القائمة
                        const commentElement = document.querySelector(`[data-comment-id="${commentId}"]`);
                        if (commentElement) {
                            commentElement.querySelector('p').textContent = content;
                        }

                        // إخفاء نموذج التعديل
                        editCommentFormContainer.style.display = 'none';
                        editCommentForm.reset();

                        toastr.success('تم تعديل التعليق بنجاح.');
                    } else {
                        toastr.error(data.message || 'حدث خطأ أثناء تعديل التعليق.');
                    }
                })
                .catch(error => {
                    console.error('Error updating comment:', error);
                    toastr.error('حدث خطأ أثناء تعديل التعليق.');
                });
        });
    </script>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if (isset($session_notification_id))
            // تشغيل دالة openCommentsSession مع session_notification_id و comment_id (إذا كانت موجودة)
            openCommentsSession(
                "{{ $session_notification_id }}", "{{ $comment_id ?? 'null' }}"
            );
        @endif
    });
</script>
{{-- الكود الخاص ب لنفسي  :  --}}
<!-- تمرير بيانات الموظف الحالي إلى جافاسكريبت -->
<script>
    var currentEmployee = @json($currentEmployee);
</script>
<script>
    // تأكد من تحميل jQuery وSelect2 قبل هذا الكود
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('assign-myself-btn').addEventListener('click', function() {
            if (!currentEmployee) {
                alert('لم يتم العثور على موظف مرتبط بحسابك.');
                return;
            }

            var select = $('#assigned_to');
            var employeeId = currentEmployee.id;
            var employeeName = currentEmployee.name;

            // التحقق مما إذا كان الخيار موجودًا بالفعل
            if (!select.find('option[value="' + employeeId + '"]').length) {
                // إضافة الخيار
                var newOption = new Option(employeeName, employeeId, true, true);
                select.append(newOption).trigger('change');
            } else {
                // تحديد الخيار الموجود
                var selectedValues = select.val() || [];
                if (!selectedValues.includes(String(employeeId))) {
                    selectedValues.push(employeeId);
                    select.val(selectedValues).trigger('change');
                } else {
                    // إذا كان الخيار محددًا بالفعل، يمكنك إظهار رسالة أو تجاهل
                    toastr.warning('تم تعيين نفسك بالفعل.');

                }
            }
        });
    });
</script>
{{-- الكود الخاص ب لنفسي  :  --}}
<script>
    (function() {
        'use strict'
        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        var forms = document.querySelectorAll('.needs-validation')
        // Loop over them and prevent submission
        Array.prototype.slice.call(forms)
            .forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }

                    form.classList.add('was-validated')
                }, false)
            })
    })()
</script>
