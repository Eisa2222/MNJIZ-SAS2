<div class="d-flex justify-content-center">

    <a href="{{ route('hr.payrolls.wps.details.revision.edit',  [$wps_payroll->id,$employee->id,$row->id]) }}" class="btn btn-sm text-secondary"
        title="تعديل">
        <i class="ti ti-edit"></i>
    </a>


    <a href="javascript:void(0);" onclick="confirmDelete({{ $row->id }})" class="btn btn-sm text-secondary"
        title="حذف">
        <i class="ti ti-trash"></i>
    </a>
    <form id="delete-form-{{ $row->id }}"
        action="{{ route('hr.payrolls.wps.details.revision.destroy',  [$wps_payroll->id,$employee->id,$row->id]) }}" method="POST"
        style="display: none;">
        @csrf
        @method('DELETE')
    </form>


</div>
