<?php

namespace App\DataTables\ElectronicServices\LeaveRequests;


use App\DataTables\ArabicSearchDataTable;
use App\Enums\ElectronicServices\LeaveRequests\LeaveRequestsStatus;
use App\Models\ElectronicServices\LeaveRequests\LeaveRequest;
use Illuminate\Support\Facades\Auth;

class EmployeeLeaveRequestsDataTable extends ArabicSearchDataTable
{
    private $route  = "account.electronic-services.leave-requests";
    private $page   = "electronic_services.leave_requests";



    /*
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    */
    protected function getTableId(): string
    {
        return "request-table";
    }

    protected function resource()
    {
        return LeaveRequest::where('employee_id', Auth::user()->employee->id);
    }

    protected function getColumns(): array
    {
        return [
            [
                'data' => '',
                'orderable'  => false,
                'searchable' => false,
            ],
            [
                'data' => 'id',
                'name' => 'id',
                'title' => 'ID',
                'visible' => false,
                'orderable' => true,
                'searchable' => false,
            ],
            // Checkbox
            [
                'data'       => 'checkbox',
                'name'       => 'checkbox',
                'title'      => '<span class="custom-checkbox-header"><input type="checkbox" id="select-all"></span>',
                'orderable'  => false,
                'searchable' => false,
                'width'      => '10px',
                'className'  => 'custom-checkbox',
                'titleAttr'  => 'تحديد الكل',
            ],

            ['data' => 'leave_type_id',         'name' => 'leave_type_id',      'title' => 'الطلب',             'width' => '150px'],
            ['data' => 'start_date',            'name' => 'start_date',         'title' => 'تاريخ البداية',    'width' => '150px'],
            ['data' => 'end_date',              'name' => 'end_date',           'title' => 'تاريخ النهاية',    'width' => '150px'],
            ['data' => 'days_count',            'name' => 'days_count',         'title' => 'عدد الايام',         'width' => '150px'],
            ['data' => 'status',                'name' => 'status',             'title' => 'الحالة',            'width' => '150px'],
            ['data' => 'created_at',            'name' => 'created_at',         'title' => 'تاريخ الطلب',       'width' => '150px'],


            // Actions
            ['data' => 'actions', 'name' => 'actions', 'title' => 'الإجراءات', 'orderable' => false, 'searchable' => false, 'width' => '150px'],
        ];
    }

    /*
    |============================================================================
    |                           Helper Methods
    |============================================================================
    */
    protected function addCustomColumns($datatable)
    {
        $datatable
            ->addColumn('checkbox', function ($row) {
                return $this->checkbox($row);
            })

            ->editColumn('leave_type_id', function ($row) {
                return sprintf(
                    '<a href="%s" class="text-primary fw-semibold text-decoration-none" title="عرض تفاصيل الطلب">%s</a>',
                    route($this->route . '.show', $row->id),
                    $row->leaveType->name
                );
            })

            ->editColumn('start_date', fn($row) => $row->start_date ? date('Y-m-d', strtotime($row->start_date)) : '—')

            ->editColumn('end_date', fn($row) => $row->end_date ? date('Y-m-d', strtotime($row->end_date)) : '—')

            ->editColumn('status', function ($row) {
                return $this->statusBadge($row);
            })

            ->editColumn('created_at', function ($row) {
                return $row->created_at->format('Y-m-d H:i:s');
            })

            ->addColumn('actions', function ($row) {
                if ($row->status == LeaveRequestsStatus::Pending) {
                    return view($this->page . '.action', ['row' => $row, 'route' => $this->route])->render();
                } elseif ($row->status == LeaveRequestsStatus::Rejected) {
                    return '<span class="badge bg-danger" title="تم رفض الطلب ولا يمكن تعديله أو حذفه">مرفوض</span>';
                } else {
                    return '<span class="badge bg-secondary" title="لا يمكن تعديل أو حذف الطلب بعد اعتماده">مغلق</span>';
                }
            });
    }


    protected function applyCustomFilters($query)
    {
        $request = request();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('leave_type_id', $request->type);
        }

        return $query;
    }


    protected function rawColumns(): array
    {
        return ['checkbox', 'leave_type_id', 'status', 'actions'];
    }
    /*
    |============================================================================
    |                               Optional Methods
    |============================================================================
    */
    // الحقول المسموح البحث فيها
    protected function getSearchableColumns(): array
    {
        return [
            'leave_type_id',
            'start_date',
            'end_date',
        ];
    }

    protected function getCustomOrder(): array
    {
        return [1, 'desc'];
    }

    protected function getButtons(): array
    {
        return [
            [
                'text'      => '<i class="fas fa-plus-circle me-1"></i> طلب إجازة',
                'className' => 'btn btn-primary btn-add',
                'action'    => "function(){ window.location.href='" . route($this->route .  '.create') . "'; }",
            ],
        ];
    }

    /*
    |============================================================================
    |                            Private Helper Methods
    |============================================================================
    */
    private function statusBadge($row): string
    {
        return sprintf(
            '<span class="badge bg-%s">%s</span>',
            $row->status->color(),
            $row->status->label()
        );
    }
}
