<div class="d-flex justify-content-center">
    @can('تعديل عرض')
        <a href="{{ route('operations-center.offers.edit', $row->id) }}" class="btn btn-sm text-secondary">
            <i class="ti ti-edit"></i>
        </a>
    @endcan

    @can('حذف عرض')
        <button type="button" class="btn btn-sm text-secondary" onclick="confirmDelete({{ $row->id }})">
            <i class="ti ti-trash"></i>
        </button>

        <form id="delete-form-{{ $row->id }}" action="{{ route('operations-center.offers.destroy', $row->id) }}" method="POST"
            style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    @endcan
</div>
