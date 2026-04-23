<div class="d-flex justify-content-center">
    @canany(['تعديل مهمة', 'حذف مهمة'])
        @can('تعديل مهمة')
            <a href="{{ route($route . '.edit', $row->id) }}" class="btn btn-sm text-secondary" title="تعديل">
                <i class="ti ti-edit"></i>
            </a>
        @endcan

        @can('حذف مهمة')
            <button type="button" class="btn btn-sm text-secondary" onclick="confirmDelete({{ $row->id }})" title="حذف">
                <i class="ti ti-trash"></i>
            </button>

            <form id="delete-form-{{ $row->id }}" action="{{ route($route . '.destroy', $row->id) }}" method="POST"
                style="display: none;">
                @csrf
                @method('DELETE')
            </form>
        @endcan
    @else
        <span class="text-muted small">
            ليس لديك صلاحيات
        </span>
    @endcanany
</div>
