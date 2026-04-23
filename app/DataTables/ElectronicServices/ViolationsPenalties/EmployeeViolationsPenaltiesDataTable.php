<?php

namespace App\DataTables\ElectronicServices\ViolationsPenalties;

use App\DataTables\ArabicSearchDataTable;
use App\Models\Hr\Violations\Violation;
use Illuminate\Support\Facades\Auth;

class EmployeeViolationsPenaltiesDataTable extends ArabicSearchDataTable
{
    private $route = "account.electronic-services.violations-penalties";


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
        return Violation::query()->where('employee_id', Auth::user()->employee->id)->with('employee', 'violationType.category');
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

            ['data' => 'reference_number',      'name' => 'reference_number',       'title' => 'المرجع',    'width' => '150px'],
            ['data' => 'violation_type',        'name' => 'violation_type',         'title' => 'المخالفة',],
            ['data' => 'occurrence',            'name' => 'occurrence',             'title' => 'التكرار',   'width' => '70px'],
            ['data' => 'penalty_text',          'name' => 'penalty_text',           'title' => 'العقوبة',],
            ['data' => 'status',                'name' => 'status',                 'title' => 'الحالة',    'width' => '70px'],

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


            ->addColumn('violation_type', function ($row) {
                $description = $row->violationType->description ?? 'غير محدد';
                return  $description;
            })

            ->addColumn('penalty_text', function ($row) {
                return $row->formatPenalty($row->penalty_text);
            })

            ->editColumn('status', function ($row) {
                return $this->statusBadge($row);
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
