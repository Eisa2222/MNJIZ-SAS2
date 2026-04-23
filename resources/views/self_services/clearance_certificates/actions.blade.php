<div class="d-flex gap-2">

    @can('تعديل طلب إخلاء طرف')
        <a href="{{ route('account.self-services.clearance-certificate.edit', $row->id) }}" class="btn btn-sm text-secondary"
            title="تعديل">
            <i class="ti ti-edit"></i>
        </a>
    @endcan


    @can('حذف طلب إخلاء طرف')
        <a href="javascript:void(0);" onclick="confirmDelete({{ $row->id }})" class="btn btn-sm text-secondary"
            title="حذف">
            <i class="ti ti-trash"></i>
        </a>
        <form id="delete-form-{{ $row->id }}"
            action="{{ route('account.self-services.clearance-certificate.destroy', $row->id) }}" method="POST"
            style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    @endcan


</div>
