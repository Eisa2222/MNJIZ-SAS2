<div class="d-flex justify-content-center">

    @if (auth()->user()->can('إضافة / نزع صلاحية للدور'))
        <button type="button" class="btn btn-sm text-secondary" data-bs-toggle="modal"
            data-bs-target="#managePermissionsModal" data-user-id="{{ $row->user_id }}">
            <i class="ti ti-lock"></i>
        </button>
    @endif

</div>
