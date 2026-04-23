<div class="d-flex justify-content-center">


    @if ($row->wpsPayroll->canBeApproved())
        @if ($row->hasRevisions())
            <a href="{{ route('hr.payrolls.wps.details.revision.show', [$row->wps_payroll_id, $row->employee_id]) }}"
                class="btn btn-sm text-secondary" title="طلبات المراجعة">
                <i class="ti ti-list"></i>
            </a>
        @else
            <a href="{{ route('hr.payrolls.wps.details.revision.create', [$row->wps_payroll_id, $row->employee_id]) }}"
                class="btn btn-sm text-secondary" title="طلب مراجعة لمرتب الموظف">
                <i class="ti ti-pencil"></i>
            </a>
        @endif
    @endif


    <a href="{{ route('hr.payrolls.wps.details.export_pdf', [$row->wps_payroll_id, $row->employee_id]) }}"
        class="btn btn-sm text-secondary" title="تحميل الملف PDF">
        <i class="ti ti-file-type-pdf"></i>
    </a>

</div>
