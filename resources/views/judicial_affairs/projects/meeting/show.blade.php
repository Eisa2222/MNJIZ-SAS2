@extends('judicial_affairs/projects/layout')

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('sections')

    <div class=" ">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="col-12 col-lg-12">
            <div class="card card-action mb-6">
                <div class="card-header align-items-center border-bottom mb-5">
                    <h5 class="card-action-title mb-0 text-primary fw-bold">
                        <i class="ti ti-video  ti-lg text-body me-3 text-primary fw-bold"></i> الإجتماعات الخاصة بالمشروع
                        <span class="small text-muted">ستظهر هنا جميع الاجتماعات الخاصة بالمشروع التي تم عقدها</span>
                    </h5>
                </div>
                <div class="card-body pt-3">
                    <div class="row">
                        @foreach ($meeting as $item)
                            <div class="col-12 col-md-6 col-xxl-4">
                                <div class="card p-2 h-100 shadow-none border meeting-card">
                                    <div class="rounded-2 text-center mb-4 h-100">
                                        <!-- يمكنك استخدام صورة ثابتة أو ديناميكية حسب الحاجة -->
                                        <img class=" w-75"
                                            src="{{ $item->user->employee->profile_picture ? Storage::url($item->user->employee->profile_picture) : asset('assets/img/branding/Alburhan-Logo.png') }}"
                                            alt="meeting image" style=" object-fit: contain;" />
                                    </div>
                                    <div class="card-body p-4 pt-2 d-flex flex-column">
                                        <div class="text-end">

                                            <div class="badge-teams ">

                                                <span class="badge bg-label-primary">{{ $item->meeting_field_label }}</span>
                                            </div>
                                        </div>
                                        <p class="p-2">{{ $item->meeting_name }}</p>

                                        <div class="meeting-details pt-3">
                                            <div class="detail-item">
                                                <i class="ti ti-clock"></i>من :
                                                {{ \Carbon\Carbon::parse($item->meeting_start_date)->format('Y-m-d h:i') }}
                                            </div>
                                        </div>
                                        <div class="meeting-details pt-3">
                                            <div class="detail-item">
                                                <i class="ti ti-clock"></i>الى :
                                                {{ \Carbon\Carbon::parse($item->meeting_end_date)->format('Y-m-d h:i') }}
                                            </div>
                                        </div>

                                        <a class="w-100 mt-2 btn btn-label-primary d-flex align-items-center"
                                            data-bs-toggle="modal" data-bs-target="#meetingDetailsModal"
                                            data-meeting-name="{{ $item->meeting_name }}"
                                            data-meeting-start-date="{{ \Carbon\Carbon::parse($item->meeting_start_date)->format('Y-m-d h:i') }}"
                                            data-meeting-end-date="{{ \Carbon\Carbon::parse($item->meeting_end_date)->format('Y-m-d h:i') }}"
                                            data-meeting-field="{{ $item->meeting_field_label }}"
                                            data-meeting-points="{{ $item->meeting_points }}"
                                            data-meeting-outputs="{{ $item->meeting_outputs }}">
                                            <span class="me-2">تفاصيل الاجتماع</span><i
                                                class="ti ti-chevron-left ti-xs"></i>
                                        </a>

                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Modal -->
    <div class="modal fade" id="meetingDetailsModal" tabindex="-1" aria-labelledby="meetingDetailsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-primary" id="meetingDetailsModalLabel">
                        تفاصيل الاجتماع
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- تفاصيل الاجتماع سيتم تعبئتها ديناميكيًا -->
                    <div class="mb-3">
                        <h6 class="fw-bold text-secondary">اسم الاجتماع : <span id="modalMeetingDetails"
                                class="text-muted "></span></h6>

                    </div>

                    <div class="mb-3">
                        <h6 class="fw-bold text-secondary">من : <span id="modalMeetingStartDate" class="text-muted "></span>
                        </h6>

                    </div>

                    <div class="mb-3">
                        <h6 class="fw-bold text-secondary">الى : <span id="modalMeetingEndDate" class="text-muted "></span>
                        </h6>

                    </div>

                    <div class="mb-3">
                        <h6 class="fw-bold text-secondary">مجال الاجتماع : <span id="modalMeetingField"
                                class="text-muted "></span></h6>

                    </div>

                    <div class="mb-3">
                        <h6 class="fw-bold text-secondary">نقاط الاجتماع:</h6>
                        <p id="modalMeetingPoints" class="text-muted ps-3"></p>
                    </div>

                    <div class="mb-3">
                        <h6 class="fw-bold text-secondary">مخرجات الاجتماع:</h6>
                        <p id="modalMeetingOutputs" class="text-muted ps-3"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                </div>
            </div>
        </div>
    </div>



    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const meetingDetailsModal = document.getElementById('meetingDetailsModal');

            meetingDetailsModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;

                // استرجاع البيانات من الزر
                const meetingName = button.getAttribute('data-meeting-name');
                const meetingField = button.getAttribute('data-meeting-field');

                const meetingPoints = button.getAttribute('data-meeting-points');
                const meetingOutputs = button.getAttribute('data-meeting-outputs');

                const meetingStartDate = button.getAttribute('data-meeting-start-date');
                const meetingEndDate = button.getAttribute('data-meeting-end-date');


                // تعبئة البيانات داخل الـ Modal
                meetingDetailsModal.querySelector('#modalMeetingDetails').textContent = meetingName;
                meetingDetailsModal.querySelector('#modalMeetingField').textContent = meetingField;

                meetingDetailsModal.querySelector('#modalMeetingPoints').textContent = meetingPoints;
                meetingDetailsModal.querySelector('#modalMeetingOutputs').textContent = meetingOutputs;

                meetingDetailsModal.querySelector('#modalMeetingStartDate').textContent = meetingStartDate;
                meetingDetailsModal.querySelector('#modalMeetingEndDate').textContent = meetingEndDate;


            });
        });
    </script>

@endsection
