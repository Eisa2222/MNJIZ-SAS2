@extends('judicial_affairs/lawsuits/layout')
@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection
@section('sections')
    @if (isset($lawsuits) && $lawsuits->isNotEmpty())
        <!-- شريط البحث برقم الدعوى -->
        <div class="col-md-12 mb-4">
            <form method="GET" action="{{ route('projects.lawsuits', $project->id) }}" class="d-flex">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="ti ti-search"></i>
                    </span>
                    <input type="text" name="search" class="form-control" placeholder="ابحث برقم الدعوى"
                        aria-label="ابحث برقم الدعوى" value="{{ request('search') }}">

                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-search"></i>
                    </button>
                </div>
            </form>
        </div>

        <div class="row g-3">
            @foreach ($lawsuits as $lawsuit)
                <div class="col-12">
                    <div class="card shadow-sm d-flex flex-row align-items-stretch">
                        <!-- زر السهم لعرض/إخفاء التحديثات -->
                        <button id="toggleUpdatesBtn-{{ $lawsuit->id }}"
                            class="btn btn-primary d-flex align-items-center justify-content-center"
                            style="width: 70px; border-radius: 0; height: auto;"
                            onclick="toggleUpdates({{ $lawsuit->id }})">
                            <i class="fas fa-arrow-down fa-2x text-white"></i>
                        </button>

                        <div class="card-body flex-grow-1">
                            <div class="row">
                                <div class="col">
                                    <div class="mb-2">
                                        <strong>رقم الدعوى</strong>
                                        <div>{{ $lawsuit->lawsuit_number }}</div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="mb-2">
                                        <strong>اسم الدعوى</strong>
                                        <div>{{ $lawsuit->name }}</div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="mb-2">
                                        <strong>تاريخ الإنشاء</strong>
                                        <div>{{ $lawsuit->hijri_created }}</div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="mb-2">
                                        <strong>الدائرة</strong>
                                        <div>{{ $lawsuit->circle }}</div>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <div class="dropdown">
                                        <button class="btn btn-icon btn-text-secondary rounded-pill" type="button"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="ti ti-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item"
                                                    href="{{ route('sessions.show', $lawsuit->id) }}">عرض</a>
                                            </li>

                                            <li>
                                                <form id="delete-form-{{ $lawsuit->id }}" class="mb-0"
                                                    action="{{ route('lawsuits.destroy_from_project', $lawsuit->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="dropdown-item text-danger"
                                                        onclick="confirmDelete({{ $lawsuit->id }})">
                                                        أرشفة الدعوى
                                                    </button>
                                                </form>
                                            </li>


                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- قائمة التحديثات الخاصة بكل دعوى -->
                    <div id="updates-{{ $lawsuit->id }}" class="mt-3" style="display: none;">
                        <div class="row">
                            @if ($lawsuit->sessions->count() > 0)
                                @foreach ($lawsuit->sessions->sortByDesc('created_at')->take(3) as $session)
                                    <input type="hidden" id="last_objection_deadline"
                                        value="{{ $session->last_objection_deadline }}">
                                    <input type="hidden" id="session_id_id" value="{{ $session->id }}">
                                    <!-- إضافة حقل مخفي لمعرف الدعوى -->
                                    <input type="hidden" id="lawsuit_id" value="{{ $lawsuit->id }}">
                                    <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center mb-3 pb-1">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar me-2">
                                                            <i class="ti ti-calendar ti-xl me-1_5 text-primary"></i>
                                                        </div>
                                                        <div class="me-1 text-heading h5 mb-0">
                                                            <small>{{ $session->session_name }}</small>
                                                        </div>
                                                    </div>
                                                    <div class="ms-auto">
                                                        <ul class="list-inline mb-0 d-flex align-items-center">
                                                            @if ($session->session_status->value == 'active')
                                                                <li class="list-inline-item">
                                                                    <div class="dropdown">
                                                                        <button type="button"
                                                                            class="btn btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow p-0"
                                                                            data-bs-toggle="dropdown" aria-expanded="false">
                                                                            <i
                                                                                class="ti ti-dots-vertical ti-md text-muted"></i>
                                                                        </button>
                                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                                            @php
                                                                                $now = \Carbon\Carbon::now();
                                                                                $sessionTime = \Carbon\Carbon::parse(
                                                                                    $session->session_date .
                                                                                        ' ' .
                                                                                        $session->session_time,
                                                                                );
                                                                                $isFuture = $now->lt($sessionTime);
                                                                                $isPast = $now->gte($sessionTime);
                                                                            @endphp

                                                                            <li>
                                                                                <!-- زر تعديل الجلسة -->
                                                                                <a class="dropdown-item {{ $isPast ? 'disabled text-muted' : '' }}"
                                                                                    href="{{ $isPast ? '#' : route('sessions.edit', $session->id) }}"
                                                                                    @if ($isPast) tabindex="-1" aria-disabled="true"
                                                                    title="لا يمكن تعديل الجلسة بعد انتهاء وقتها" @endif>
                                                                                    تعديل الجلسة
                                                                                </a>
                                                                            </li>
                                                                            <li>
                                                                                <!-- زر استكمال ضبط الجلسة -->
                                                                                <a class="dropdown-item {{ $isFuture ? 'disabled text-muted' : '' }}"
                                                                                    href="{{ $isFuture ? '#' : route('sessions.set_session_completion', $session->id) }}"
                                                                                    @if ($isFuture) tabindex="-1" aria-disabled="true"
                                                                    title="لا يمكن استكمال ضبط الجلسة قبل موعدها" @endif>
                                                                                    استكمال ضبط الجلسة
                                                                                </a>
                                                                            </li>
                                                                            <li>
                                                                                <!-- زر حذف الجلسة -->
                                                                                <a class="dropdown-item text-danger"
                                                                                    href="javascript:void(0);"
                                                                                    onclick="confirmDelete({{ $session->id }})">
                                                                                    حذف الجلسة
                                                                                </a>
                                                                                <form id="delete-form-{{ $session->id }}"
                                                                                    method="POST"
                                                                                    action="{{ route('sessions.destroy', $session->id) }}"
                                                                                    style="display: none;">
                                                                                    @csrf
                                                                                    @method('DELETE')
                                                                                </form>
                                                                            </li>
                                                                            <!-- JavaScript لمنع النقر على الروابط المعطلة وعرض رسالة توضيحية -->
                                                                            <script>
                                                                                document.addEventListener('DOMContentLoaded', function() {
                                                                                    const disabledLinks = document.querySelectorAll('.dropdown-item.disabled');
                                                                                    disabledLinks.forEach(function(link) {
                                                                                        link.addEventListener('click', function(e) {
                                                                                            e.preventDefault();
                                                                                            const title = link.getAttribute('title');
                                                                                            if (title) {
                                                                                                alert(title);
                                                                                            }
                                                                                        });
                                                                                    });
                                                                                });

                                                                                function confirmDelete(sessionId) {
                                                                                    if (confirm('هل أنت متأكد من حذف هذه الجلسة؟')) {
                                                                                        document.getElementById('delete-form-' + sessionId).submit();
                                                                                    }
                                                                                }
                                                                            </script>
                                                                        </ul>
                                                                    </div>
                                                                </li>
                                                            @else
                                                                <span class="badge bg-danger"> مغلقة</span>
                                                            @endif
                                                        </ul>
                                                    </div>
                                                </div>
                                                {{-- الموقت --}}
                                                @if (
                                                    ($session->summary_report_status == 'حكم موضوعي' || $session->summary_report_status == 'حكم شكلي') &&
                                                        is_null($session->objection_status))
                                                    <div class="col-md-12 mb-4 mt-4" id="countdown_container">
                                                        <div class="card border-light shadow-sm p-3 mb-5 bg-white rounded">
                                                            <div class="card-body">
                                                                <h6 class="card-title text-secondary">التقرير الإجمالي:
                                                                    <span
                                                                        class="badge bg-primary">{{ $session->summary_report_status }}</span>
                                                                </h6>
                                                                <div
                                                                    class="d-flex justify-content-between align-items-center">
                                                                    <div>
                                                                        <label for="countdown"
                                                                            class="form-label small text-muted">الوقت
                                                                            المتبقي
                                                                            للاعتراض:</label>
                                                                        <div id="countdown" class="text-danger small"
                                                                            style="font-size: 1.1rem; font-weight: bold;">
                                                                        </div>
                                                                    </div>
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-outline-danger rounded-pill"
                                                                        id="objection_button" style="display: none;">
                                                                        تقديم الاعتراض
                                                                    </button>
                                                                </div>
                                                                <!-- شريط التقدم الذي يعكس الوقت المتبقي للاعتراض -->
                                                                <div class="progress mt-2" style="height: 8px;">
                                                                    <div id="progress-bar" class="progress-bar bg-danger"
                                                                        role="progressbar" style="width: 0%;"
                                                                        aria-valuenow="0" aria-valuemin="0"
                                                                        aria-valuemax="100"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                                {{-- الموقت --}}
                                                <div class="mb-3 pb-1">
                                                    <p class="mb-2">
                                                        <span class="fw-bold">تاريخ الجلسة :</span>
                                                        {{ $session->hijri_session_date }}
                                                    </p>
                                                    <p class="mb-2">
                                                        <span class="fw-bold">زمن الجلسة :</span>
                                                        {{ \Carbon\Carbon::parse($session->session_time)->format('H:i') }}
                                                    </p>
                                                    <p class="mb-2">
                                                        <span class="fw-bold">أهمية الجلسة :</span>
                                                        {{ $session->session_importance }}
                                                    </p>
                                                </div>

                                                <ul class="list-group list-group-flush">
                                                    <li
                                                        class="list-group-item d-flex justify-content-between align-items-center flex-wrap p-0">
                                                        <div class="d-flex flex-wrap align-items-center">
                                                            <ul
                                                                class="list-unstyled users-list d-flex align-items-center avatar-group m-0 me-2">
                                                                @foreach ($session->assignedEmployees->take(3) as $employee)
                                                                    <li data-bs-toggle="tooltip"
                                                                        data-popup="tooltip-custom"
                                                                        data-bs-placement="bottom" class="avatar pull-up"
                                                                        aria-label="{{ $employee->name }}"
                                                                        data-bs-original-title="{{ $employee->name }}">
                                                                        <img class="rounded-circle"
                                                                            src="{{ $employee->avatar ? asset($employee->avatar) : asset('assets/img/branding/Alburhan-Logo.png') }}"
                                                                            alt="Avatar">
                                                                    </li>
                                                                @endforeach

                                                                @if ($session->assignedEmployees->count() > 3)
                                                                    <li class="avatar">
                                                                        <span type="button"
                                                                            class="avatar-initial rounded-circle pull-up text-heading"
                                                                            data-bs-placement="bottom"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#employeesModal"
                                                                            title="عرض جميع المكلفين">
                                                                            +{{ $session->assignedEmployees->count() - 3 }}</span>
                                                                    </li>
                                                                @endif

                                                                @if ($session->assignedEmployees->count() == 0)
                                                                    <li class="mb-4">
                                                                        لا يوجد مكلفين
                                                                    </li>
                                                                @endif

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
                                                                <h5 class="modal-title" id="employeesModalLabel">قائمة
                                                                    الموظفين
                                                                    المخصصين
                                                                </h5>
                                                                <button type="button" class="btn-close"
                                                                    data-bs-dismiss="modal" aria-label="إغلاق"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <ul class="list-group list-group-flush">
                                                                    @foreach ($session->assignedEmployees as $employee)
                                                                        <li
                                                                            class="list-group-item d-flex align-items-center">
                                                                            <div class="me-3">
                                                                                <img class="rounded-circle"
                                                                                    src="{{ $employee->avatar ? asset($employee->avatar) : asset('assets/img/branding/Alburhan-Logo.png') }}"
                                                                                    alt="Avatar" width="50"
                                                                                    height="50" loading="lazy">
                                                                            </div>
                                                                            <div>
                                                                                <strong>{{ $employee->name }}</strong>
                                                                            </div>
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary"
                                                                    data-bs-dismiss="modal">إغلاق</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="d-flex align-items-center">
                                                    <div class="ms-auto">
                                                        <a href="javascript:;"
                                                            class="btn btn-icon btn-text-secondary rounded-pill"
                                                            data-bs-toggle="modal" data-bs-target="#sessionModal"
                                                            data-session-name="{{ $session->session_name }}"
                                                            data-hijri-date="{{ $session->hijri_date }}"
                                                            data-gregorian-date="{{ $session->gregorian_date }}"
                                                            data-session-importance="{{ $session->session_importance }}"
                                                            data-summary-report-status="{{ $session->summary_report_status }}"
                                                            data-execution-minutes="{{ $session->execution_minutes }}"
                                                            data-report-sending-method="{{ $session->report_sending_method }}"
                                                            data-detailed-report="{{ $session->notes }}"
                                                            session_control_attached="{{ $session->session_control_attached }}">

                                                            <i class="ti ti-eye"></i>
                                                        </a>
                                                        <div class="dropdown d-inline-block">
                                                            <button class="btn btn-icon btn-text-secondary rounded-pill"
                                                                type="button" id="shareDropdown"
                                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                                <i class="ti ti-share"></i>
                                                            </button>
                                                            <ul class="dropdown-menu" aria-labelledby="shareDropdown">
                                                                <li>
                                                                    <a class="dropdown-item"
                                                                        href="sms:?body=تفاصيل الجلسة: {{ $session->session_name }} - التاريخ الهجري: {{ $session->hijri_date }} - التاريخ الميلادي: {{ $session->gregorian_date }}">
                                                                        <i class="fas fa-sms"></i> مشاركة عبر SMS
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item"
                                                                        href="mailto:?subject=تفاصيل الجلسة: {{ $session->session_name }}&body=التفاصيل: {{ $session->session_name }} - التاريخ الهجري: {{ $session->hijri_date }} - التاريخ الميلادي: {{ $session->gregorian_date }}">
                                                                        <i class="fas fa-envelope"></i> مشاركة عبر البريد
                                                                        الإلكتروني
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item"
                                                                        href="https://wa.me/?text=تفاصيل الجلسة: {{ $session->session_name }}%0Aالتاريخ الهجري: {{ $session->hijri_date }}%0Aالتاريخ الميلادي: {{ $session->gregorian_date }}"
                                                                        target="_blank">
                                                                        <i class="fab fa-whatsapp"></i> مشاركة عبر WhatsApp
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="col-12 text-center">

                                    <div class="card p-12">
                                        حاليًا، لا توجد أي جلسات مرتبطة بهذه الدعوى.
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            @endforeach
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
                                    <th scope="row">اسم الجلسة</th>
                                    <td id="modalSessionName"></td>
                                </tr>
                                <tr>
                                    <th scope="row">التاريخ الميلادي</th>
                                    <td id="modalGregorianDate"></td>
                                </tr>
                                <tr>
                                    <th scope="row">التاريخ الهجري</th>
                                    <td id="modalHijriDate"></td>
                                </tr>
                                <tr>
                                    <th scope="row">أهمية الجلسة</th>
                                    <td id="modalSessionImportance"></td>
                                </tr>
                                <tr>
                                    <th scope="row"> التقرير الإجمالي</th>
                                    <td id="modalSummaryStatus"></td>
                                </tr>
                                <tr>
                                    <th scope="row">دقائق التنفيذ</th>
                                    <td id="modalExecutionMinutes"></td>
                                </tr>
                                <tr>
                                    <th scope="row">طريقة إرسال التقرير</th>
                                    <td id="modalReportMethod"></td>
                                </tr>
                                <tr>
                                    <th scope="row">التقرير التفصيلي</th>
                                    <td id="modalDetailedReport"></td>
                                </tr>
                                <tr>
                                    <th scope="row"> مرفق ضبط الجلسة</th>
                                    <td id="modalsession_control_attached"></td>
                                </tr>
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
                    var hijriDate = button.getAttribute('data-hijri-date');
                    var gregorianDate = button.getAttribute('data-gregorian-date');
                    var sessionImportance = button.getAttribute('data-session-importance');
                    var summaryReportStatus = button.getAttribute('data-summary-report-status');
                    var executionMinutes = button.getAttribute('data-execution-minutes');
                    var reportSendingMethod = button.getAttribute('data-report-sending-method');
                    var detailedReport = button.getAttribute('data-detailed-report');
                    var session_control_attached = button.getAttribute('session_control_attached');

                    sessionModal.querySelector('#modalSessionName').innerText = sessionName || 'غير محدد';
                    sessionModal.querySelector('#modalHijriDate').innerText = hijriDate || 'غير محدد';
                    sessionModal.querySelector('#modalGregorianDate').innerText = gregorianDate || 'غير محدد';
                    sessionModal.querySelector('#modalSessionImportance').innerText = sessionImportance ||
                        'غير محدد';
                    sessionModal.querySelector('#modalSummaryStatus').innerText = summaryReportStatus ||
                        'غير محدد';
                    sessionModal.querySelector('#modalExecutionMinutes').innerText = executionMinutes ||
                        'غير محدد';
                    sessionModal.querySelector('#modalReportMethod').innerText = reportSendingMethod ||
                        'غير محدد';
                    sessionModal.querySelector('#modalDetailedReport').innerText = detailedReport || 'غير محدد';
                    // sessionModal.querySelector('#modalsession_control_attached').innerText = session_control_attached || 'غير محدد';

                    var controlAttachedElement = sessionModal.querySelector('#modalsession_control_attached');

                    if (session_control_attached) {
                        controlAttachedElement.innerHTML = `
            <a href="/storage/${session_control_attached}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-primary">
                عرض المرفق
            </a>
        `;
                    } else {
                        controlAttachedElement.innerText = 'غير محدد';
                    }





                });
            });
        </script>
        <style>
            .custom-pagination .text-muted {
                display: none !important;
            }
        </style>
        <!-- Pagination -->
        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            <div class="custom-pagination">
                {{ $lawsuits->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @else
        <div class="alert alert-info">
            لا توجد دعاوى مرتبطة بهذا المشروع.
        </div>
    @endif

    <script>
        function toggleUpdates(lawsuitId) {
            const updatesContainer = document.getElementById('updates-' + lawsuitId);
            const toggleBtnIcon = document.querySelector('#toggleUpdatesBtn-' + lawsuitId + ' i');

            if (updatesContainer.style.display === 'none') {
                updatesContainer.style.display = 'block';
                toggleBtnIcon.classList.remove('fa-arrow-down');
                toggleBtnIcon.classList.add('fa-arrow-up');
            } else {
                updatesContainer.style.display = 'none';
                toggleBtnIcon.classList.remove('fa-arrow-up');
                toggleBtnIcon.classList.add('fa-arrow-down');
            }
        }
    </script>

    <script>
        $(document).ready(function() {
            var countdownInterval;

            var objectionDeadlineDateStr = $('#last_objection_deadline').val();
            if (!objectionDeadlineDateStr) {
                console.error("تاريخ آخر مهلة للاعتراض غير موجود.");
                return;
            }

            var objectionDeadlineDate = new Date(objectionDeadlineDateStr);

            if (isNaN(objectionDeadlineDate.getTime())) {
                console.error("تاريخ آخر مهلة للاعتراض غير صالح.");
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

    <script>
        // فتح شاشة التعليقات وجلب التعليقات عبر AJAX
        function openComments(sessionId) {
            const commentsOffcanvas = new bootstrap.Offcanvas(document.getElementById('commentsOffcanvas'));
            document.getElementById('session_id').value = sessionId;

            // طلب التعليقات عبر AJAX
            fetch(`/sessions/${sessionId}/comments`)
                .then(response => response.json())
                .then(data => {
                    let commentsHtml = '';

                    if (data.comments.length > 0) {
                        data.comments.forEach(comment => {
                            commentsHtml += `
                        <div class="d-flex mb-2">
                            <div class="flex-shrink-0">
                                <i class="ti ti-user ti-2x text-primary"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <strong>${comment.user.name}</strong>
                                <span class="text-muted">${comment.created_at}</span>
                                <p>${comment.content}</p>
                            </div>
                        </div>
                    `;
                        });
                    } else {
                        commentsHtml = '<p class="text-muted">لا توجد تعليقات لعرضها.</p>';
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
                <div class="d-flex mb-2">
                    <div class="flex-shrink-0">
                        <i class="ti ti-user ti-2x text-primary"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <strong>${data.comment.user.name}</strong>
                        <span class="text-muted">${data.comment.created_at}</span>
                        <p>${data.comment.content}</p>
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
    </script>
@endsection
