<div class="d-flex justify-content-center">

    @if ($row->canBeApproved())
        <a href="{{ route('hr.payrolls.wps.approve', [$row->id]) }}" class="btn btn-sm text-secondary"
            title="تصديق المسير وارساله للاعتماد">
            <i class="ti ti-file-like"></i>
        </a>
    @elseif($row->isSubmittedForApproval())
        <small>
            في انتظار موافقة الإدارة
        </small>
    @elseif($row->isApproved())
        <div class="py-1 px-3" title="المسير معتمد">
            <i class="ti ti-file-check text-success"></i>
        </div>
        <a href="{{ route('hr.payrolls.wps.export_excel', [$row->id]) }}" class="btn btn-sm text-secondary"
            title="تحميل الملف PDF">
            <i class="ti ti-file-type-pdf"></i>
        </a>
    @elseif($row->isRejected())
        <div class="py-1 px-3" title="تم رفض المسير الرجاء اعادة تعديل المسير و اعادة تصديقه ورفع للاعتماد">
            <i class="ti ti-file-alert text-danger"></i>
        </div>
    @endif

</div>
