<?php

namespace App\DataTables\Hr\ViolationsPenalties;

use App\DataTables\ArabicSearchDataTable;
use App\Enums\Hr\ViolationsPenalties\ViolationStatus;
use App\Models\Hr\Rewards\Reward;
use App\Models\Hr\Violations\Violation;

class ViolationsPenaltiesDataTable extends ArabicSearchDataTable
{
    private $route = "hr.violations-penalties";


    /*
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    */
    protected function getTableId(): string
    {
        return "violations-table";
    }

    protected function resource()
    {
        return Violation::query()->with('employee', 'violationType.category');
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

            ['data' => 'reference_number',      'name' => 'reference_number',       'title' => 'المرجع',    'width' => '120px'],
            ['data' => 'employee_name',         'name' => 'employee.name',          'title' => 'الموظف',    'width' => '150px'],
            ['data' => 'violation_type',        'name' => 'violation_type',         'title' => 'المخالفة',],
            ['data' => 'occurrence',            'name' => 'occurrence',             'title' => 'التكرار',   'width' => '70px'],
            ['data' => 'penalty_text',          'name' => 'penalty_text',           'title' => 'العقوبة',],
            ['data' => 'status',                'name' => 'status',                 'title' => 'الحالة',    'width' => '70px'],

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

            ->addColumn('reference_number', function ($row) {
                return $this->routeName(route($this->route . '.show', $row->id), $row->reference_number);
            })

            ->addColumn('employee_name', function ($row) {
                return $this->routeName(route('account.employee.profile', $row->employee_id), $row->employee?->getRawNameAttribute() ?? '-');
            })

            ->addColumn('violation_type', function ($row) {
                $description = $row->violationType->description ?? 'غير محدد';
                return  $description;
            })

            ->addColumn('penalty_text', function ($row) {
                return $row->formatPenalty($row->penalty_text);
            })

            ->editColumn('status', function ($row) {
                return $this->statusBadge($row);
            })

            ->addColumn('actions', function ($row) {
                return  ViolationStatus::from($row->status->value)->canEditOrDelete()
                    ? view('hr.violations-penalties.action', ['row' => $row, 'route' => $this->route])->render()
                    : "لا يمكن التعديل و الحذف";
            });
    }


    protected function applyCustomFilters($query)
    {
        $request = request();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('reward_type', $request->type);
        }

        if ($request->filled('employee')) {
            $query->where('employee_id', $request->employee);
        }

        return $query;
    }


    protected function rawColumns(): array
    {
        return ['checkbox', 'reference_number', 'employee_name', 'violation_type', 'penalty_text', 'status', 'actions'];
    }
    /*
    |============================================================================
    |                               Optional Methods
    |============================================================================
    */
    // الحقول المسموح البحث فيها
    // protected function getSearchableColumns(): array
    // {
    //     return [
    //         'reward_number',
    //         'employee.name',
    //         'employee.nickname',
    //         'reward_type',
    //         'amount',
    //         'status',
    //         'reward_date',
    //     ];
    // }

    protected function getCustomOrder(): array
    {
        return [1, 'desc'];
    }

    protected function getButtons(): array
    {
        if (auth()->user()->can('إضافة عقوبة او إنتهاك')) {
            return [
                [
                    'text'      => '<i class="fas fa-plus-circle me-1"></i> إضافة إنتهاك',
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
            $row->status->color(),
            $row->status->label()
        );
    }
}
