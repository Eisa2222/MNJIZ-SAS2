<div class="d-flex justify-content-center">

    @can('إعادة تعين كلمة المرور')
        <button type="button" class="btn btn-sm text-warning"
            onclick="sendPasswordResetFirstNotification('{{ route('hr.employees.send-password-reset', $row->user->id) }}')"
            title="إعادة تعيين كلمة المرور">
            <i class="ti ti-key"></i>
        </button>
    @endcan



    @can('تعديل موظف')
        <button type="button" class="btn btn-sm text-warning"
            onclick="confirmArchive('{{ route('hr.employees.archive', $row->id) }}')" title="نقل إلى الأرشيف">
            <i class="ti ti-archive"></i>
        </button>

        <form id="archive-form-{{ $row->id }}" action="{{ route($route . '.archive', $row->id) }}" method="POST"
            style="display: none;">
            @csrf
            @method('PATCH')
        </form>

        <a href="{{ route($route . '.edit', $row->id) }}" class="btn btn-sm text-secondary">
            <i class="ti ti-edit"></i>
        </a>
    @endcan


    @can('حذف موظف')
        <button type="button" class="btn btn-sm text-secondary" onclick="confirmDelete({{ $row->id }})">
            <i class="ti ti-trash"></i>
        </button>

        <form id="delete-form-{{ $row->id }}" action="{{ route($route . '.destroy', $row->id) }}" method="POST"
            style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    @endcan


</div>
