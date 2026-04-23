<div class="d-flex justify-content-center">

    @if (!$row->isSessionExpired())
        @can('تعديل جلسة')
            <a href="{{ route($route . '.edit', $row->id) }}" class="btn btn-sm text-secondary">
                <i class="ti ti-edit"></i>
            </a>
        @endcan

        @can('حذف جلسة')
            <button type="button" class="btn btn-sm text-secondary" onclick="confirmDelete({{ $row->id }})">
                <i class="ti ti-trash"></i>
            </button>

            <form id="delete-form-{{ $row->id }}" action="{{ route($route . '.destroy', $row->id) }}" method="POST"
                style="display: none;">
                @csrf
                @method('DELETE')
            </form>
        @endcan
    @else
        <a title="استكمال ضبط الجلسة" href="{{ route($route . '.session-completion.completion', $row->id) }}"
            class="btn btn-sm text-secondary">
            <i class="ti ti-clipboard-check"></i>
        </a>
    @endif

</div>
