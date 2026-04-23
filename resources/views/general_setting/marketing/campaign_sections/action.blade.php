<div class="d-flex justify-content-center">

    <button type="button" class="btn btn-sm text-secondary btn-edit" data-id="{{ $row->id }}"
        data-name="{{ e($row->name) }}"  data-color="{{ e($row->color) }}" >
        <i class="ti ti-edit"></i>
    </button>


    <button type="button" class="btn btn-sm text-secondary" onclick="confirmDelete({{ $row->id }})">
        <i class="ti ti-trash"></i>
    </button>

    <form id="delete-form-{{ $row->id }}" action="{{ route($route . '.destroy', $row->id) }}" method="POST"
        style="display: none;">
        @csrf
        @method('DELETE')
    </form>

</div>
