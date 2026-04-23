<?php

namespace App\DataTables\LegalAffairs\Session;


use App\DataTables\ArabicSearchDataTable;
use App\Enums\LegalAffair\Session\SessionStatus;
use App\Models\LegalAffair\Lawsuit\Lawsuit;
use App\Models\LegalAffair\Session\Session;
use Illuminate\Support\Facades\DB;

class SessionsDataTable extends ArabicSearchDataTable
{
    private $route  = "legal-affairs.sessions";
    private $page   = "legal_affairs.sessions";

    /*
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    */
    protected function getTableId(): string
    {
        return "sessions-table";
    }

    protected function resource()
    {
        $query = Session::query()
            ->with(['assignedUsers.employee'])
            ->orderByProximity();

        // التحقق من صلاحيات المستخدم
        if (auth()->user()->can('الجلسات الخاصة بي') && !auth()->user()->can('كل الجلسات')) {
            $userId         = auth()->user()->id;
            $employeeId = auth()->user()->employee->id;

            $query->where(function ($q) use ($userId, $employeeId) {
                // الجلسات التي أنشأها المستخدم
                $q->where('created_by', $employeeId)
                    // أو الجلسات التي هو مكلف فيها
                    ->orWhereHas('assignedUsers', function ($subQuery) use ($userId) {
                        $subQuery->where('assigned_to', $userId);
                    });
            });
        }




        return $query;
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

            ['data' => 'session_name',      'name' => 'session_name',       'title' => 'اسم الجلسة',       'width' => '300px', 'orderable'  => false,],
            ['data' => 'session_date',      'name' => 'session_date',       'title' => 'تاريخ الجلسة',     'width' => '150px', 'orderable'  => false,],
            ['data' => 'session_time',      'name' => 'session_time',       'title' => 'زمن الجلسة',       'width' => '150px', 'orderable'  => false,],
            ['data' => 'time_remaining',    'name' => 'time_remaining',     'title' => 'زمن الجلسة',       'width' => '150px', 'orderable'  => false,],
            ['data' => 'assigned_users',    'name' => 'assigned_users',     'title' => 'المكلفين',         'width' => '150px', 'orderable' => false],
            ['data' => 'session_status',    'name' => 'session_status',     'title' => 'حالة الجلسة',      'width' => '150px', 'orderable'  => false,],

            // Actions
            ['data' => 'actions', 'name' => 'actions', 'title' => 'الإجراءات', 'orderable' => false, 'searchable' => false, 'width' => '20px',],
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

            ->editColumn('session_name', function ($row) {
                return $this->routeName(route($this->route . '.show', $row->id), $row->session_name);
            })

            ->addColumn('session_date', function ($row) {
                $gregorianDate = $row->session_date->format('d/m/Y');
                $dayName = $row->session_date->locale('ar')->dayName;

                return $dayName . ' - ' . $gregorianDate;
            })

            ->addColumn('time_remaining', function ($row) {
                return $row->time_remaining;
            })

            ->addColumn('assigned_users', function ($row) {
                return $this->buildAssignedUsers($row);
            })

            ->editColumn('session_status', function ($row) {
                return $this->statusBadge($row);
            })

            ->addColumn('actions', function ($row) {
                return  SessionStatus::from($row->session_status->value)->canEditOrDelete()
                    ? view($this->page . '.action', ['row' => $row, 'route' => $this->route])->render()
                    : "<small style='font-size:10px'>الجلسة مغلقة لا يمكن تعديلها ولا حذفها</small>";
            });
    }

    protected function applyCustomFilters($query)
    {
        $request = request();

        if ($request->filled('status')) {
            $query->where('session_status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('session_type', $request->type);
        }

        if ($request->filled('ranks')) {
            $query->where('entity_ranks_id', $request->ranks);
        }

        return $query;
    }

    protected function rawColumns(): array
    {
        return ['checkbox', 'session_name', 'session_date', 'time_remaining', 'assigned_users', 'session_status', 'actions'];
    }

    /*
    |============================================================================
    |                               Optional Methods
    |============================================================================
    */
    protected function getSearchableColumns(): array
    {
        return [
            'session_name',
        ];
    }

    protected function getCustomOrder(): array
    {
        return [1, 'desc'];
    }


    protected function getButtons(): array
    {
        if (auth()->user()->can('إضافة جلسة')) {
            return [
                [
                    'text'      => '<i class="fas fa-plus-circle me-1"></i> إضافة جلسة',
                    'className' => 'btn btn-primary btn-add',
                    'action'    => "function(){ window.location.href='" . route($this->route . '.create') . "'; }",
                ],
            ];
        }

        return [];
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
            $row->session_status->color(),
            $row->session_status->label()
        );
    }

    private function buildAssignedUsers($row): string
    {
        $assignedUsers = $row->assignedUsers;

        if ($assignedUsers->isEmpty()) {
            return '<span class="badge bg-secondary">غير محدد</span>';
        }

        return view($this->page . '.partials.assigned-employees', compact('assignedUsers'))->render();
    }
}
