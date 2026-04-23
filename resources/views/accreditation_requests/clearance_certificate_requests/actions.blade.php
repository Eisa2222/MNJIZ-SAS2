<div class="d-flex gap-2">
    <!-- نموذج اعتماد الطلب -->
    <form action="{{ route('accreditation-requests.clearance-certificate.approve', $row->id) }}" method="POST" style="display:inline">
        @csrf
        @method('PATCH')
        <button type="submit" class="btn btn-sm text-success" title="قبول الطلب">
            <i class="ti ti-check"></i>
        </button>
    </form>

    <!-- نموذج رفض الطلب -->
    <form action="{{ route('accreditation-requests.clearance-certificate.reject', $row->id) }}" method="POST" style="display:inline">
        @csrf
        @method('PATCH')
        <button type="submit" class="btn btn-sm text-danger" title="رفض الطلب">
            <i class="ti ti-x"></i>
        </button>
    </form>
</div>
