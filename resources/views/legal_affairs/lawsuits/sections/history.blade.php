<div class="col-12 col-lg-12 pt-6 pt-lg-0">
    <div class="card card-action mb-6">
        <div class="card-header align-items-center bg-white border-bottom mb-5">
            <h5 class="card-action-title mb-0 text-primary fw-bold">
                <i class="ti ti-history ti-lg me-1_5 text-body me-3 text-primary fw-bold"></i> سجل النشاطات
            </h5>
        </div>
        <div class="card-body">
            <ul class="timeline ">
                <li class="timeline-item">
                    <span class="timeline-point bg-secondary"></span>
                    <div class="timeline-event">
                        <small class="timeline-title text-capitalize">
                            تمت الاضافة بتاريخ
                        </small>
                        <small class="text-muted d-block">
                            {{ $lawsuit->created_at }}
                        </small>
                        <small>
                            <b>اضيف بواسطة</b>
                            <a href="{{ route('account.employee.profile', $lawsuit->created_by) }}">
                                {{ $lawsuit->createdBy->name }}
                            </a>
                        </small>
                    </div>
                </li>

                @if ($lawsuit->updated_by)
                    <li class="timeline-item">
                        <span class="timeline-point bg-success"></span>
                        <div class="timeline-event">
                            <small class="timeline-title text-capitalize">
                                اخر تعديل بتاريخ
                            </small>
                            <small class="text-muted d-block">
                                {{ $lawsuit->updated_at }}
                            </small>
                            <small>
                                <b>التعديل بواسطة</b>
                                <a href="{{ route('account.employee.profile', $lawsuit->updated_by) }}">
                                    {{ $lawsuit->updatedBy->name }}
                                </a>
                            </small>
                        </div>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</div>
