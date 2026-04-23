<?php

namespace App\DataTables\SystemAdministration\Roles;

use App\DataTables\ArabicSearchDataTable;
use App\Models\Hr\Employees\Employees;

class RolesDataTable extends ArabicSearchDataTable
{
    /*
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    */
    protected function getTableId(): string
    {
        return "roles-table";
    }

    protected function resource()
    {
        return Employees::active();
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

            ['data' => 'name',          'name' => 'name',           'title' => 'الموظف'],
            ['data' => 'nickname',      'name' => 'nickname',       'title' => 'اللقب'],
            ['data' => 'role',          'name' => 'role',           'title' => 'الدور'],

            // Actions
            ['data' => 'actions', 'name' => 'actions', 'title' => 'الإجراءات', 'orderable' => false, 'searchable' => false, 'width' => '150px',],
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

            ->editColumn('name', function ($row) {
                return $row->getRawOriginal('name');
            })


            ->addColumn('role', function ($row) {
                // جلب أول دور للموظف
                $roleName = $row->user && $row->user->roles->count() > 0 ? $row->user->roles->first()->name : 'غير محدد';

                // التحقق من صلاحية منح الدور
                $canAssignRole = auth()->user()->can('منح الادوار');

                // إذا لم يكن لديه صلاحية، أظهر الدور كنص ثابت
                if (!$canAssignRole) {
                    return '<span class="badge bg-primary">' . e($roleName) . '</span>';
                }
                // جعل اسم الدور قابل للنقر لفتح المودال
                return '<a href="javascript:void(0);" class="edit-role-link" data-employee-id="' . htmlspecialchars($row->id, ENT_QUOTES, 'UTF-8') . '">
                                    <span class="badge bg-primary">' . e($roleName) . '</span>
                                </a>';
            })

            ->editColumn('status', function ($row) {
                return $row->user->status;
                // return $this->statusBadge($row);
            })

            ->addColumn('actions', function ($row) {
                return view('system_administration.roles.action', ['row' => $row])->render();
            });
    }

    // protected function applyCustomFilters($query)
    // {
    //     $request = request();

    //     if ($request->filled('status')) {
    //         $query->where('status', $request->status);
    //     }

    //     if ($request->filled('type')) {
    //         $query->where('advance_type', $request->type);
    //     }

    //     if ($request->filled('employee')) {
    //         $query->where('employee_id', $request->employee);
    //     }

    //     return $query;
    // }

    protected function rawColumns(): array
    {
        return ['checkbox', 'role', 'status', 'actions'];
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
            'name',
            'nickname',
        ];
    }

    protected function getCustomOrder(): array
    {
        return [1, 'desc'];
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