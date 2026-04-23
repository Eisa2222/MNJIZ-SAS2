<div class="d-flex justify-content-center">
    <a href="{{ route('qoyod.vendors.edit', $row['id']) }}" class="btn btn-sm text-secondary">
        <i class="ti ti-edit"></i>
    </a>

    <div title="عذرًا، لا يمكن إتمام العملية حاليًا لعدم توفر الخدمة اللازمة">
        <button disabled type="button" class="btn btn-sm border-0">
            <i class="ti ti-trash text-danger"></i>
        </button>
    </div>

</div>
