<div class="d-flex justify-content-center">
    <button disabled type="button" class="btn btn-sm border-0"
        title="عذرًا، لا يمكن إتمام العملية حاليًا لعدم توفر الخدمة اللازمة">
        <i class="ti ti-edit text-danger"></i>
    </button>


    <button type="button" class="btn btn-sm text-secondary" onclick="confirmDelete({{ $row['id'] }})">
        <i class="ti ti-trash"></i>
    </button>

    <form id="delete-form-{{ $row['id'] }}" action="{{ route('qoyod.credit-notes.destroy', $row['id']) }}"
        method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
</div>
