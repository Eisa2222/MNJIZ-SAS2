
@extends('layouts.layoutMaster')

@section('title', 'التقويم')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> التقويم</a>
        <i class="ti ti-star favorite-icon" data-page-name="التقويم" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/fullcalendar/fullcalendar.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/quill/editor.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection



@section('vendor-script')
    @vite(['resources/assets/vendor/libs/fullcalendar/fullcalendar.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/app-calendar.js'])

    <!-- تمرير حالة النجاح إلى جافاسكريبت -->
    <script>
        window.hasSuccess = @json(session('success'));
    </script>
@endsection

@section('page-style')
    @vite(['resources/assets/vendor/scss/pages/app-calendar.scss'])
@endsection

@section('content')
    <div class="card app-calendar-wrapper">
        <div class="row g-0">
            <!-- التقويم والشريط الجانبي لعرض وتعديل تفاصيل الحدث -->
            <div class="col app-calendar-content">
                <div class="card shadow-none border-0">
                    <div class="card-body pb-0">
                        <!-- FullCalendar -->
                        <div id="calendar"></div>
                    </div>
                </div>
                <div class="app-overlay"></div>

                <!-- شريط جانبي لعرض وتعديل تفاصيل الحدث -->
                <div class="offcanvas offcanvas-end event-details-sidebar" tabindex="-1" id="eventDetailsSidebar"
                    aria-labelledby="eventDetailsSidebarLabel">
                    <div class="offcanvas-header border-bottom p-4">
                        <h5 class="offcanvas-title" id="eventDetailsSidebarLabel">تفاصيل الحدث</h5>

                    </div>
                    <div class="offcanvas-body">
                        <form class="event-form pt-0" id="eventDetailsForm">
                            @csrf
                            {{-- @method('PUT') --}}
                            <input type="hidden" id="detailEventId" name="event_id">
                            <div class="mb-3">
                                <label class="form-label" for="detailEventTitle">عنوان الحدث</label>
                                <textarea  class="form-control" id="detailEventTitle" name="subject" readonly cols="30" rows="10"></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="detailEventStartDate">تاريخ البداية</label>
                                <input type="date" class="form-control" id="detailEventStartDate" name="start_date"
                                    readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="detailEventStartTime">وقت البداية</label>
                                <input type="time" class="form-control" id="detailEventStartTime" name="start_time"
                                    readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="detailEventEndDate">تاريخ النهاية</label>
                                <input type="date" class="form-control" id="detailEventEndDate" name="end_date" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="detailEventEndTime">وقت النهاية</label>
                                <input type="time" class="form-control" id="detailEventEndTime" name="end_time" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="detailEventDescription">الوصف</label>
                                <textarea class="form-control" name="body" id="detailEventDescription" readonly></textarea>
                            </div>
                            <div class="d-flex justify-content-sm-between justify-content-start mt-6 gap-2">
                                <div class="d-flex">
                                    <button type="button" id="editEventButton"
                                        class="btn btn-primary btn-edit-event me-4">تعديل</button>
                                    <button type="button" class="btn btn-label-secondary btn-cancel me-sm-0 me-1"
                                        data-bs-dismiss="offcanvas">إلغاء</button>
                                </div>
                                <button type="button" class="btn btn-primary d-none" id="saveEditButton">حفظ
                                    التعديلات</button>
                                <button type="button" class="btn btn-label-danger btn-delete-event d-none"
                                    id="deleteEventButton">حذف</button>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- /شريط جانبي لعرض وتعديل تفاصيل الحدث -->

                <!-- شريط جانبي لإضافة الأحداث -->
                <div class="offcanvas offcanvas-end" tabindex="-1" id="addEventSidebar"
                    aria-labelledby="addEventSidebarLabel">
                    <div class="offcanvas-header border-bottom p-4">
                        <h5 class="offcanvas-title" id="addEventSidebarLabel">إضافة حدث</h5>

                    </div>
                    <div class="offcanvas-body">
                        <form class="event-form pt-0" id="addEventForm" action="{{ route('calendar.createEvent') }}"
                            method="POST">
                            @csrf
                            <div class="mb-5">
                                <label class="form-label" for="eventTitle">عنوان الحدث</label>
                                <input type="text" class="form-control" id="eventTitle" name="subject"
                                    placeholder="عنوان الحدث" required />
                            </div>

                            <div class="mb-5">
                                <label class="form-label" for="eventStartDate">تاريخ البداية</label>
                                <input type="date" class="form-control" id="eventStartDate" name="start_date"
                                    required />
                            </div>
                            <div class="mb-5 time-fields">
                                <label class="form-label" for="eventStartTime">وقت البداية</label>
                                <input type="time" class="form-control" id="eventStartTime" name="start_time"
                                    required />
                            </div>
                            <div class="mb-5">
                                <label class="form-label" for="eventEndDate">تاريخ النهاية</label>
                                <input type="date" class="form-control" id="eventEndDate" name="end_date" required />
                            </div>
                            <div class="mb-5 time-fields">
                                <label class="form-label" for="eventEndTime">وقت النهاية</label>
                                <input type="time" class="form-control" id="eventEndTime" name="end_time" required />
                            </div>


                            <div class="mb-5">
                                <label class="form-label" for="eventDescription">الوصف</label>
                                <textarea class="form-control" name="body" id="eventDescription"></textarea>
                            </div>
                            <div class="d-flex justify-content-sm-between justify-content-start mt-6 gap-2">
                                <div class="d-flex">
                                    <button type="submit" id="addEventBtn"
                                        class="btn btn-primary btn-add-event me-4">إضافة</button>
                                    <button type="reset" class="btn btn-label-secondary btn-cancel me-sm-0 me-1"
                                        data-bs-dismiss="offcanvas">إلغاء</button>
                                </div>
                                <button type="button" class="btn btn-label-danger btn-delete-event d-none">حذف</button>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- /شريط جانبي لإضافة الأحداث -->
            </div>
            <!-- /التقويم والشريط الجانبي لعرض وتعديل تفاصيل الحدث -->
        </div>
    </div>
@endsection
