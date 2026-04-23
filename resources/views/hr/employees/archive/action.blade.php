<div class="d-flex justify-content-center">

    <button type="button" class="btn btn-sm text-warning"
        onclick="confirmRestore('{{ route($route . '.restore', $row->id) }}')"  title="استعادة من الأرشيف">
        <i class="ti ti-rotate-clockwise"></i>
    </button>

    {{--  --}}
    <a href="{{ route($route . '.edit', $row->id) }}" class="btn btn-sm text-secondary">
        <i class="ti ti-edit"></i>
    </a>


    <button type="button" class="btn btn-sm text-secondary" onclick="confirmDelete({{ $row->id }})">
        <i class="ti ti-trash"></i>
    </button>

    <form id="delete-form-{{ $row->id }}" action="{{ route($route . '.destroy', $row->id) }}" method="POST"
        style="display: none;">
        @csrf
        @method('DELETE')
    </form>

</div>
