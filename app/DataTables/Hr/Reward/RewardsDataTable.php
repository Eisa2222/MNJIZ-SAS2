<?php

namespace App\DataTables\Hr\Reward;

use App\DataTables\ArabicSearchDataTable;
use App\Models\Hr\Rewards\Reward;

class RewardsDataTable extends ArabicSearchDataTable
{
    private $route = "hr.rewards";

    /*
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    */
    protected function getTableId(): string
    {
        return "rewards-table";
    }

    protected function resource()
    {
        return Reward::query()->with('employee');
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

            ['data' => 'reward_number',     'name' => 'reward_number',     'title' => 'المرجع'],
            ['data' => 'employee_name',     'name' => 'employee.name',     'title' => 'الموظف'],
            ['data' => 'reward_type',       'name' => 'reward_type',       'title' => 'نوع المكافأة'],
            ['data' => 'amount',            'name' => 'amount',            'title' => 'القيمة'],
            ['data' => 'reward_date',       'name' => 'reward_date',       'title' => 'تاريخ المكافأة'],
            ['data' => 'status',            'name' => 'status',            'title' => 'الحالة'],

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

            ->editColumn('reward_number', function ($row) {
                return $this->routeName(route('hr.rewards.show', $row->id), $row->reward_number);
            })

            ->addColumn('employee_name', function ($row) {
                return $this->routeName(route('account.employee.profile', $row->employee_id), $row->employee?->getRawNameAttribute() ?? '-');
            })

            ->editColumn('reward_type', function ($row) {
                return $this->getRewardType($row);
            })

            ->editColumn('amount', function ($row) {
                return number_format($row->amount ?? 0, 2) . '<span class="icon-saudi_riyal mx-2"></span>';
            })

            ->editColumn('reward_date', function ($row) {
                return $row->reward_date?->format('Y-m-d') ?? '-';
            })

            ->editColumn('status', function ($row) {
                return $this->statusBadge($row);
            })

            ->addColumn('actions', function ($row) {
                if (!$row->status->canEdit()) {
                    return "لا يمكن التعديل و الحذف";
                }
                return (auth()->user()->can('تعديل مكافأة') || auth()->user()->can('حذف مكافأة')) ? view('hr.rewards.action', ['row' => $row, 'route' => $this->route])->render() : ' ليس لديك صلاحيات';
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
        return ['checkbox', 'reward_number', 'employee_name', 'amount', 'status', 'actions'];
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
            'reward_number',
            'employee.name',
            'employee.nickname',
            'reward_type',
            'amount',
            'status',
            'reward_date',
        ];
    }

    protected function getCustomOrder(): array
    {
        return [1, 'desc'];
    }

    protected function getButtons(): array
    {
        if (auth()->user()->can('إضافة مكافأة')) {
            return [
                [
                    'text'      => '<i class="fas fa-plus-circle me-1"></i> إضافة مكافأة',
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

    private function getRewardType($row): string
    {
        return $row->reward_type->label();
    }
}
