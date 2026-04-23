<div class="d-flex justify-content-center">
    @can('تعديل عقد إستثنائي')
        <a href="{{ route('operations-center.exceptional-contracts.edit', $row->id) }}" class="btn btn-sm text-secondary">
            <i class="ti ti-edit"></i>
        </a>
    @endcan

    @can('حذف عقد إستثنائي')
        <button type="button" class="btn btn-sm text-secondary" onclick="confirmDelete({{ $row->id }})">
            <i class="ti ti-trash"></i>
        </button>

        <form id="delete-form-{{ $row->id }}"
            action="{{ route('operations-center.exceptional-contracts.destroy', $row->id) }}" method="POST"
            style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    @endcan

</div>
