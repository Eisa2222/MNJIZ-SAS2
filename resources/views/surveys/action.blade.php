<div class="d-flex justify-content-center">

    @can('تعديل إستبيان')
        <a href="{{ route($route . '.edit', $row->id) }}" class="btn btn-sm text-secondary">
            <i class="ti ti-edit"></i>
        </a>
    @endcan


    @can('إحصائيات الإستبيان')
        <a href="{{ route($route . '.statistics', $row->id) }}" title="الاحصائيات" class="btn btn-sm text-secondary">
            <i class="ti ti-chart-bar"></i>
        </a>
    @endcan

</div>
