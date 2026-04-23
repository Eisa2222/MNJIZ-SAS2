<div class="d-flex justify-content-center">

    <a href="{{ route('accreditation-requests.wps-requests.revision.approve', [$row->id]) }}" class="btn btn-sm text-secondary"
        title="قبول الطلب ">
        <i class="ti ti-circle-check"></i>
    </a>

    <a href="{{ route('accreditation-requests.wps-requests.revision.reject', [$row->id]) }}" class="btn btn-sm text-secondary"
        title="رفض الطلب ">
        <i class="ti ti-ban"></i>
    </a>

</div>
