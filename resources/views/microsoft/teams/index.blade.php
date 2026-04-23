<!-- resources/views/microsoft/teams/index.blade.php -->

@extends('layouts.layoutMaster')

@section('title', ' الإجتماعات')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> الإجتماعات </a>
        <i class="ti ti-star favorite-icon" data-page-name="الإجتماعات" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/plyr/plyr.scss'])
@endsection

@section('page-style')
    @vite(['resources/css/dataTable.css'])

@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/plyr/plyr.js'])
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/app-academy-course.js'])
    <script>
        $(document).ready(function() {
            // التعامل مع حذف الاجتماع باستخدام SweetAlert
            $('.delete-meeting').on('click', function(e) {
                e.preventDefault();
                var eventId = $(this).data('id');

                Swal.fire({
                    title: 'هل أنت متأكد من عملية الحذف؟',
                    text: "لا يمكن التراجع عن هذا الإجراء!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'تأكيد',
                    cancelButtonText: 'إلغاء',
                    customClass: {
                        popup: 'custom-popup',
                        title: 'custom-title',
                        text: 'custom-text',
                        confirmButton: 'btn btn-success custom-confirm',
                        cancelButton: 'btn btn-danger custom-cancel'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/teams/' + eventId,
                            type: 'DELETE',
                            data: {
                                "_token": "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        title: 'تم الحذف بنجاح!',
                                        text: response.success,
                                        icon: 'success',
                                        confirmButtonText: 'موافق',
                                        customClass: {
                                            popup: 'custom-popup',
                                            title: 'custom-title',
                                            text: 'custom-text',
                                            confirmButton: 'btn btn-success custom-confirm'
                                        }
                                    });
                                    // إعادة تحميل الصفحة بعد الحذف
                                    setTimeout(function() {
                                        location.reload();
                                    }, 1500);
                                } else {
                                    Swal.fire({
                                        title: 'خطأ!',
                                        text: response.error,
                                        icon: 'error',
                                        confirmButtonText: 'موافق',
                                        customClass: {
                                            popup: 'custom-popup',
                                            title: 'custom-title',
                                            text: 'custom-text',
                                            confirmButton: 'btn btn-danger custom-confirm'
                                        }
                                    });
                                }
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    title: 'خطأ!',
                                    text: 'حدث خطأ أثناء حذف الاجتماع.',
                                    icon: 'error',
                                    confirmButtonText: 'موافق',
                                    customClass: {
                                        popup: 'custom-popup',
                                        title: 'custom-title',
                                        text: 'custom-text',
                                        confirmButton: 'btn btn-danger custom-confirm'
                                    }
                                });
                            }
                        });
                    }
                });
            });

            // التعامل مع عرض تفاصيل الاجتماع
            $('.view-details').on('click', function(e) {
                e.preventDefault();
                var meeting = $(this).data('meeting');

                // تحويل JSON إلى كائن JavaScript
                meeting = typeof meeting === 'string' ? JSON.parse(meeting) : meeting;

                // تعبئة بيانات النافذة المنبثقة
                $('#detailsSubject').text(meeting.subject);
                $('#detailsStartTime').text(meeting.startDateTime);
                $('#detailsEndTime').text(meeting.endDateTime);
                $('#detailsJoinUrl').attr('href', meeting.joinWebUrl);
                $('#detailsMeetingPoints').text(meeting.meeting_points || 'لا توجد نقاط الاجتماع.');
                $('#detailsMeetingOutputs').text(meeting.meeting_outputs || 'لا توجد مخرجات الاجتماع.');
                $('#detailsOrganizer').text(meeting.organizer);

                // إظهار النافذة المنبثقة
                $('#meetingDetailsModal').modal('show');
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            // عند فتح مودل المخرجات
            $(document).on('click', '.outputs-button', function(e) {
                e.preventDefault();
                const meetingId = $(this).data('meeting-id');
                const currentOutputs = $(this).data('outputs'); // نضيف data attribute للمخرجات الحالية

                $('#meeting_id').val(meetingId);
                $('#meeting_outputs').val(currentOutputs || ''); // نعرض المخرجات الحالية إذا وجدت
                $('#meetingOutputsModal').modal('show');
            });

            // معالجة تقديم نموذج المخرجات
            $('#outputsForm').on('submit', function(e) {
                e.preventDefault();
                const meetingId = $('#meeting_id').val();
                const outputs = $('#meeting_outputs').val();

                $.ajax({
                    url: `/teams/${meetingId}/outputs`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        meeting_outputs: outputs
                    },
                    success: function(response) {
                        $('#meetingOutputsModal').modal('hide');
                        toastr.success('تم حفظ مخرجات الاجتماع بنجاح');
                        location.reload();
                    },
                    error: function() {
                        toastr.error('حدث خطأ أثناء حفظ المخرجات');
                    }
                });
            });
        });
    </script>
@endsection

@section('content')

    <div class="card p-0 mb-4 shadow-sm border-0 rounded">
        <div class="card-body d-flex flex-column flex-md-row justify-content-between gap-4 p-4">
            <!-- الصورة الأولى -->
            <div class="text-center text-md-start">
                {{-- <img src="{{ asset('assets/img/illustrations/bulb-light.png') }}" class="img-fluid scaleX-n1-rtl"
                    alt="مصباح في اليد" style="max-width: 100px;"> --}}
            </div>
            <!-- النص الرئيسي -->
            <div class="d-flex flex-column align-items-center text-center flex-grow-1">
                <h2 class="mb-3 text-heading" style="font-size: 1.5rem;"> اجعل كل اجتماع فرصة جديدة للإنجاز</h2>
                <p class="text-muted mb-4" style="font-size: 0.95rem;">
                    مع <span class="text-primary">Microsoft Teams</span>، يمكنك بناء جسور للتواصل الفعّال،
                    والتنسيق مع فريقك بكل سهولة ودقة.
                </p>
                <form method="GET" action="{{ route('microsoft.teams.index') }}" class="d-flex align-items-center w-100"
                    style="max-width: 400px;">
                    <input type="search" name="search" value="{{ request()->input('search') }}"
                        placeholder="ابحث عن اجتماع" class="form-control me-2 flex-grow-1" style="font-size: 0.9rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-search ti-sm"></i>
                    </button>
                </form>
            </div>
            <!-- الصورة الثانية -->
            <div class="text-center">
                {{-- <img src="{{ asset('assets/img/illustrations/pencil-rocket.png') }}" alt="صاروخ قلم"
                    style="max-width: 120px;"> --}}
            </div>
        </div>
    </div>


    <!-- قائمة الاجتماعات بالكاردات -->
    <div class="card mb-6">
        <div class="card-header d-flex flex-wrap justify-content-between gap-4">
            <div class="card-title mb-0 me-1">
                <h5 class="mb-0">قائمة الاجتماعات</h5>
                <p class="mb-0">إجمالي {{ $paginatedMeetings->total() }} اجتماع قمت بإنشائه أو تمت دعوتك إليه</p>
            </div>
            <div class="d-flex justify-content-md-end align-items-center column-gap-6">
                <a href="{{ route('teams.create') }}" class="btn btn-primary btn-add">
                    <i class="ti ti-video ti-md"></i> إنشاء اجتماع
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row gy-6 mb-6">
                @forelse($paginatedMeetings as $meeting)
                    <div class="col-sm-12 col-md-6 col-xxl-3">
                        <!-- تغيير الأعمدة إلى 4 أعمدة -->
                        <div class="card p-2 h-100 shadow-none border meeting-card">
                            <div class="rounded-2 text-center mb-4 h-100">
                                <!-- يمكنك استخدام صورة ثابتة أو ديناميكية حسب الحاجة -->
                                <a href="{{ $meeting['joinWebUrl'] }}" target="_blank">
                                    @if ($meeting['creator_image'])
                                        <img class=" w-75" src="{{ asset('storage/' . $meeting['creator_image']) }}"
                                            alt="meeting image" style=" object-fit: contain;" />
                                    @else
                                        <img class="w-75" src="{{ asset('assets/img/avatars/1.png') }}"
                                            alt="meeting image" style=" object-fit: contain;" />
                                    @endif
                                </a>
                            </div>
                            <div class="card-body p-4 pt-2 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <span>
                                        <i class="ti ti-user user-icon text-warning"></i>
                                        {{ $meeting['attendees_count'] }} <!-- عدد الحضور -->
                                    </span>
                                    <div class="badge-teams">
                                        {{-- @if ($meeting['creator_image'])
                                <img src="{{ asset('storage/employee/' . $meeting['creator_image']) }}" alt="User Image" class="user-profile-img">
                                @else
                                <img src="{{ asset('assets/img/pages/user-placeholder.png') }}" alt="User Image" class="user-profile-img">
                                @endif --}}
                                        <span class="badge bg-label-primary">{{ $meeting['meeting_field'] }}</span>
                                    </div>
                                </div>
                                <a href="{{ $meeting['joinWebUrl'] }}" class="h5">{{ $meeting['subject'] }}</a>
                                @php
                                    \Carbon\Carbon::setLocale('ar');

                                    // إزالة "صباحاً" أو "مساءً" من السلاسل النصية إذا كانت موجودة
                                    $startDateString = preg_replace(
                                        '/\s*(صباحاً|مساءً)/u',
                                        '',
                                        $meeting['startDateTime'],
                                    );
                                    $endDateString = preg_replace('/\s*(صباحاً|مساءً)/u', '', $meeting['endDateTime']);

                                    try {
                                        // افترض أن التواريخ مخزنة بتوقيت UTC، قم بتحويلها إلى توقيت الرياض
                                        $startDate = \Carbon\Carbon::createFromFormat(
                                            'd/m/Y H:i',
                                            $startDateString,
                                            'UTC',
                                        )
                                            ->setTimezone('Asia/Riyadh')
                                            ->translatedFormat('l d/m/y h:i A');
                                    } catch (\Exception $e) {
                                        $startDate = 'تاريخ غير صالح';
                                    }

                                    try {
                                        $endDate = \Carbon\Carbon::createFromFormat('d/m/Y H:i', $endDateString, 'UTC')
                                            ->setTimezone('Asia/Riyadh')
                                            ->translatedFormat('h:i A');
                                    } catch (\Exception $e) {
                                        $endDate = 'تاريخ غير صالح';
                                    }
                                @endphp

                                <div class="meeting-details pt-3">
                                    <div class="detail-item">
                                        <i class="ti ti-clock"></i>
                                        {{ $startDate }} - {{ $endDate }}
                                    </div>
                                </div>

                                <div class="mt-auto">
                                    <div class="d-flex flex-column flex-md-row gap-3 mt-3">
                                        @php
                                            $now = \Carbon\Carbon::now('Asia/Riyadh');

                                            try {
                                                // نستخدم نفس طريقة تنسيق التاريخ المستخدمة في عرض التاريخ
                                                $endDateString = preg_replace(
                                                    '/\s*(صباحاً|مساءً)/u',
                                                    '',
                                                    $meeting['endDateTime'],
                                                );
                                                $endDateTime = \Carbon\Carbon::createFromFormat(
                                                    'd/m/Y H:i',
                                                    $endDateString,
                                                    'UTC',
                                                )->setTimezone('Asia/Riyadh');

                                                $isMeetingEnded = $now->gt($endDateTime);

                                                // للتحقق من صحة التحويل
                                                // dd([
                                                //     'now' => $now->format('Y-m-d H:i'),
                                                //     'endDateTime' => $endDateTime->format('Y-m-d H:i'),
                                                //     'original' => $meeting['endDateTime'],
                                                //     'isMeetingEnded' => $isMeetingEnded
                                                // ]);
                                            } catch (\Exception $e) {
                                                $isMeetingEnded = false;
                                            }
                                        @endphp

                                        @if ($isMeetingEnded)
                                            <a href="#"
                                                class="w-100 btn btn-label-primary d-flex align-items-center outputs-button"
                                                data-meeting-id="{{ $meeting['id'] }}"
                                                data-outputs="{{ $meeting['meeting_outputs'] ?? '' }}"
                                                data-bs-toggle="modal" data-bs-target="#meetingOutputsModal">
                                                <span class="me-2">مخرجات الاجتماع</span>
                                                <i class="ti ti-clipboard-text ti-xs"></i>
                                            </a>
                                        @else
                                            <a href="{{ $meeting['joinWebUrl'] }}"
                                                class="w-100 btn btn-label-primary d-flex align-items-center">
                                                <span class="me-2">انضمام</span>
                                                <i class="ti ti-chevron-right ti-xs"></i>
                                            </a>
                                        @endif
                                        <div class="dropdown">
                                            <button
                                                class="w-100 btn btn-label-secondary d-flex align-items-center dropdown-toggle"
                                                type="button" id="dropdownMenuButton{{ $meeting['id'] }}"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                الإجراءات
                                            </button>
                                            <ul class="dropdown-menu"
                                                aria-labelledby="dropdownMenuButton{{ $meeting['id'] }}">
                                                <li>
                                                    @if (!$isMeetingEnded)
                                                        <a class="dropdown-item"
                                                            href="{{ route('teams.edit', $meeting['id']) }}">
                                                            تعديل
                                                        </a>
                                                    @endif
                                                </li>
                                                <li>
                                                    <a href="{{ route('teams.show', $meeting['id']) }}"
                                                        class="dropdown-item">
                                                        <span class="me-2">عرض التفاصيل</span>
                                                        <i class="ti ti-info-circle ti-xs"></i>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item delete-meeting" href="#"
                                                        data-id="{{ $meeting['id'] }}">
                                                        حذف
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <p class="text-center">لا توجد اجتماعات لعرضها.</p>
                    </div>
                @endforelse
            </div>
            <!-- نظام التصفح Pagination -->
            <nav aria-label="Page navigation" class="d-flex align-items-center justify-content-center">
                <ul class="pagination mb-0 pagination-rounded">
                    {{-- رابط الصفحة الأولى --}}
                    @if ($paginatedMeetings->currentPage() > 1)
                        <li class="page-item first">
                            <a class="page-link" href="{{ $paginatedMeetings->url(1) }}"><i
                                    class="ti ti-chevrons-left ti-md scaleX-n1-rtl"></i></a>
                        </li>
                    @else
                        <li class="page-item first disabled">
                            <span class="page-link"><i class="ti ti-chevrons-left ti-md scaleX-n1-rtl"></i></span>
                        </li>
                    @endif

                    {{-- رابط الصفحة السابقة --}}
                    @if ($paginatedMeetings->onFirstPage())
                        <li class="page-item prev disabled">
                            <span class="page-link"><i class="ti ti-chevron-left ti-md scaleX-n1-rtl"></i></span>
                        </li>
                    @else
                        <li class="page-item prev">
                            <a class="page-link" href="{{ $paginatedMeetings->previousPageUrl() }}"><i
                                    class="ti ti-chevron-left ti-md scaleX-n1-rtl"></i></a>
                        </li>
                    @endif

                    {{-- روابط الصفحات --}}
                    @foreach ($paginatedMeetings->getUrlRange(1, $paginatedMeetings->lastPage()) as $page => $url)
                        @if ($page == $paginatedMeetings->currentPage())
                            <li class="page-item active" aria-current="page">
                                <span class="page-link">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach

                    {{-- رابط الصفحة التالية --}}
                    @if ($paginatedMeetings->hasMorePages())
                        <li class="page-item next">
                            <a class="page-link" href="{{ $paginatedMeetings->nextPageUrl() }}"><i
                                    class="ti ti-chevron-right ti-md scaleX-n1-rtl"></i></a>
                        </li>
                    @else
                        <li class="page-item next disabled">
                            <span class="page-link"><i class="ti ti-chevron-right ti-md scaleX-n1-rtl"></i></span>
                        </li>
                    @endif

                    {{-- رابط الصفحة الأخيرة --}}
                    @if ($paginatedMeetings->currentPage() < $paginatedMeetings->lastPage())
                        <li class="page-item last">
                            <a class="page-link" href="{{ $paginatedMeetings->url($paginatedMeetings->lastPage()) }}"><i
                                    class="ti ti-chevrons-right ti-md scaleX-n1-rtl"></i></a>
                        </li>
                    @else
                        <li class="page-item last disabled">
                            <span class="page-link"><i class="ti ti-chevrons-right ti-md scaleX-n1-rtl"></i></span>
                        </li>
                    @endif
                </ul>
            </nav>
        </div>

        <!-- Meeting Details Modal -->
        <div class="modal fade" id="meetingDetailsModal" tabindex="-1" aria-labelledby="meetingDetailsModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">تفاصيل الاجتماع</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                    <div class="modal-body">
                        <h5 id="detailsSubject"></h5>
                        <p><strong>من:</strong> <span id="detailsStartTime"></span> <strong>إلى:</strong> <span
                                id="detailsEndTime"></span></p>
                        <p><strong>رابط الانضمام:</strong> <a href="#" id="detailsJoinUrl" target="_blank">انضم إلى
                                الاجتماع</a></p>
                        <p><strong>نقاط الاجتماع:</strong></p>
                        <p id="detailsMeetingPoints"></p>
                        <p><strong>مخرجات الاجتماع:</strong></p>
                        <p id="detailsMeetingOutputs"></p>
                        <p><strong>منظم الاجتماع:</strong> <span id="detailsOrganizer"></span></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal لإدخال المخرجات -->
        <div class="modal fade" id="meetingOutputsModal" tabindex="-1" aria-labelledby="meetingOutputsModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="meetingOutputsModalLabel">مخرجات الاجتماع</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="outputsForm">
                        <div class="modal-body">
                            <input type="hidden" id="meeting_id" name="meeting_id">
                            <div class="mb-3">
                                <label for="meeting_outputs" class="form-label">المخرجات</label>
                                <textarea class="form-control" id="meeting_outputs" name="meeting_outputs" rows="6" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                            <button type="submit" class="btn btn-primary">حفظ المخرجات</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>


    </div>
@endsection
