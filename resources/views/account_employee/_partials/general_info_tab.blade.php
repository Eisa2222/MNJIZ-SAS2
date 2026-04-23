<div class="tab-pane fade show active" id="general_info_tab">
    <div class="row g-3 d-flex align-items-stretch">
        <div class="col-xl-8">
            <!-- معلومات عن الموظف -->
            <div class="card mb-6">
                <div class="card-header py-4">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-list-details text-warning me-2"></i>
                        البيانات الاساسية
                    </h6>
                </div>

                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body row">
                    <div class="col-6">
                        <small class="card-text text-primary fw-bold">معلومات الموظف</small>
                        <ul class="list-unstyled my-3 py-1">
                            <li class="d-flex align-items-center mb-4">
                                <i class="ti ti-user-circle  text-primary"></i>
                                <small class="mx-2 fw-semibold">{{ $employee->getRawOriginal('name') }}
                                    {{ $employee->nickname ? $employee->nickname : '' }}</small>
                            </li>

                            <li class="d-flex align-items-center mb-4">
                                <i class="ti ti-id-badge  text-primary"></i>
                                <small class="mx-2 fw-semibold">{{ $employee->job_title ?? 'غير متوفر' }}</small>
                            </li>

                        </ul>
                    </div>

                    <div class="col-6">

                        <small class="card-text text-primary fw-bold">التواصل</small>
                        <ul class="list-unstyled my-3 py-1">
                            <li class="d-flex align-items-center mb-4">
                                <i class="ti ti-at  text-primary"></i>
                                <small class="mx-2 fw-semibold">{{ $employee->work_email ?? 'غير محدد' }}</small>
                            </li>
                        </ul>
                    </div>



                </div>

            </div>
        </div>
        <div class="col-xl-4">
            <!-- سجل النشاطات -->
            <div class="card mb-6">
                <div class="card-header d-flex justify-content-between py-4">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-history text-warning me-2"></i>
                        سجل النشاطات
                    </h6>
                    <h6 class="small mb-0">اخر 3 نشاطات</h6>

                </div>
                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body pt-3 small fw-bold">
                    <ul class="timeline mb-0">
                        @forelse ($activities as $activity)
                            <li class="timeline-item">
                                <span class="timeline-point bg-secondary"></span>
                                <div class="timeline-event">
                                    <small class="timeline-title text-capitalize">
                                        {{ $activity->description }}
                                    </small>
                                    <small class="text-muted d-block">
                                        {{ $activity->created_at->diffForHumans() }}
                                    </small>
                                </div>
                            </li>
                        @empty
                            <h6 class="mb-0 small text-center">لا توجد نشاطات</h6>
                        @endforelse
                    </ul>
                </div>
            </div>
            <!--/ سجل النشاطات -->
        </div>
    </div>

</div>
