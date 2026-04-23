<?php

namespace App\DataTables\OperationsCenter\Contract;

use App\DataTables\ArabicSearchDataTable;
use App\Models\OperationsCenter\Contract\Contract;

class ContractDataTable extends ArabicSearchDataTable
{
    private $route = "operations-center.contracts";

    /*
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    */
    protected function getTableId(): string
    {
        return "contract-table";
    }

    protected function resource()
    {

        $query = Contract::with([
            'customer:id,name',
            'contractManager:id,name,nickname'
        ])
            ->select([
                'contracts.id as id',
                'contracts.contract_name',
                'contracts.contract_number',
                'contracts.customer_id',
                'contracts.contract_manager_id',
                'contracts.contract_start_date',
                'contracts.status',
                'contracts.created_by'
            ]);

        // التحقق من صلاحيات المستخدم
        if (auth()->user()->can('العقود الخاصة بي') && !auth()->user()->can('كل العقود')) {
            $query->where('created_by', auth()->user()->employee->id);
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

            ['data' => 'contract_name',             'name' => 'contract_name',              'title' => 'اسم العقد',             'width' => '400px'],
            ['data' => 'contract_number',           'name' => 'contract_number',            'title' => 'رقم العقد',             'width' => '150x'],
            ['data' => 'customer_id',               'name' => 'customer_id',              'title' => 'العميل',                'width' => '300px'],
            ['data' => 'contract_manager_id',       'name' => 'contract_manager_id',       'title' => 'مسؤول العقد',           'width' => '300px'],
            ['data' => 'contract_start_date',       'name' => 'contract_start_date',        'title' => 'تاريخ بداية العقد',    'width' => '150px'],
            ['data' => 'status',                    'name' => 'status',                     'title' => 'الحالة',                 'width' => '150px'],

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

            ->editColumn('contract_name', function ($row) {
                return $this->routeName(route('operations-center.contracts.show', $row->id), $row->contract_name);
            })

            ->editColumn('contract_number', function ($row) {
                return $row->contract_number ?? '-';
            })

            ->editColumn('customer_id', function ($row) {
                return $row->customer ? $row->customer->name : 'غير متوفر';
            })

            ->editColumn('contract_manager_id', function ($row) {
                return $row->contractManager
                    ? $this->routeName(route('account.employee.profile', $row->contractManager->id), $row->contractManager->name)
                    : 'غير محدد';
            })

            ->editColumn('status', function ($row) {
                return $this->statusBadge($row);
            })

            ->addColumn('actions', function ($row) {
                return (auth()->user()->can('تعديل عقد') || auth()->user()->can('حذف عقد'))  ? view('operations_center.contracts.action', ['row' => $row, 'route' => $this->route])->render() : ' ليس لديك صلاحيات';
            });
    }

    protected function applyCustomFilters($query)
    {
        $request = request();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('customer')) {
            $query->where('customer_id', $request->customer);
        }

        if ($request->filled('employee')) {
            $query->where('contract_manager_id', $request->employee);
        }

        return $query;
    }

    protected function rawColumns(): array
    {
        return ['checkbox', 'contract_name', 'contract_manager_id', 'status', 'actions'];
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
            'contract_name',
            'contract_number',
            'customer.name',
            'contractManager.name',
            'contract_start_date',
        ];
    }

    protected function getCustomOrder(): array
    {
        return [1, 'desc'];
    }

    protected function getButtons(): array
    {
        if (auth()->user()->can('إضافة عقد')) {
            return [
                [
                    'text'      => '<i class="fas fa-plus-circle me-1"></i> إضافة عقد',
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
