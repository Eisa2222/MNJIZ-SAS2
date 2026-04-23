<?php

namespace App\DataTables\LegalAffairs\PowerOfAttorney;



use App\DataTables\ArabicSearchDataTable;
use App\Enums\LegalAffair\PowerOfAttorney\PowerOfAttorneyStatus;
use App\Models\LegalAffair\PowerOfAttorney\PowerOfAttorney;

class PowerOfAttorneyDataTable extends ArabicSearchDataTable
{
    private $route  = "legal-affairs.power-attorney";
    private $page   = "legal_affairs.power-attorney";

    /*
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    */
    protected function getTableId(): string
    {
        return "power-attorney-table";
    }

    protected function resource()
    {
        $query = PowerOfAttorney::with('agents', 'customers')->select([
            'id',
            'power_name',
            'power_number',
            'date_issued',
            'date_expiry',
            'status',
        ]);

        // التحقق من صلاحيات المستخدم
        if (auth()->user()->can('الوكالات الخاصة بي') && !auth()->user()->can('كل الوكالات')) {
            $query->where('created_by', auth()->id());
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

            ['data' => 'power_name',      'name' => 'power_name',     'title' => 'اسم الوكالة',       'width' => '400px'],
            ['data' => 'power_number',    'name' => 'power_number',   'title' => 'رقم الوكالة',       'width' => '150px'],
            ['data' => 'customers',       'name' => 'customers',      'title' => 'العملاء',             'width' => '150px'],
            ['data' => 'agents',          'name' => 'agents',         'title' => 'الوكلاء',             'width' => '150px'],
            ['data' => 'date_issued',     'name' => 'date_issued',    'title' => 'تاريخ الاصدار',       'width' => '150px'],
            ['data' => 'date_expiry',     'name' => 'date_expiry',    'title' => 'تاريخ الانتهاء',      'width' => '150px'],
            ['data' => 'status',          'name' => 'status',         'title' => 'الحالة',             'width' => '150px'],

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

            ->editColumn('power_name', function ($row) {
                return $this->routeName(route($this->route . '.show', $row->id), $row->power_name);
            })

            ->addColumn('customers', function ($row) {
                $customerNames = $row->customers->pluck('name')->implode(', '); // تحويل الأسماء إلى سلسلة نصية
                $count = $row->customers->count();
                return '<button style="font-size:12px" type="button" class="btn btn-primary btn-sm view-customers" data-id="' . $row->id . '" data-customers="' . htmlspecialchars($customerNames, ENT_QUOTES, 'UTF-8') . '"> العملاء (' . $count . ')</button>';
            })

            ->addColumn('agents', function ($row) {
                $agentNames = $row->agents->pluck('name')->implode(', '); // تحويل الأسماء إلى سلسلة نصية
                $count = $row->agents->count();
                return '<button style="font-size:12px" type="button" class="btn btn-info btn-sm view-agents" data-id="' . $row->id . '" data-agents="' . htmlspecialchars($agentNames, ENT_QUOTES, 'UTF-8') . '"> الوكلاء (' . $count . ')</button>';
            })

            ->editColumn('date_issued', function ($row) {
                return $row->hijri_date_issued ? $row->hijri_date_issued : '';
            })
            ->editColumn('date_expiry', function ($row) {
                return $row->hijri_date_expiry ? $row->hijri_date_expiry : '';
            })

            ->editColumn('status', function ($row) {
                return $this->statusBadge($row);
            })

            ->addColumn('actions', function ($row) {
                return (auth()->user()->can('تعديل وكالة') || auth()->user()->can('حذف وكالة')) ? view($this->page . '.action', ['row' => $row, 'route' => $this->route])->render() : ' ليس لديك صلاحيات';
            });
    }

    protected function applyCustomFilters($query)
    {
        $request = request();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->customer) {
            $query->whereHas('customers', function ($q) use ($request) {
                $q->where('customer_id', $request->customer);
            });
        }

        if ($request->employee) {
            $query->whereHas('agents', function ($q) use ($request) {
                $q->where('employee_id', $request->employee);
            });
        }
        return $query;
    }

    protected function rawColumns(): array
    {
        return ['checkbox', 'power_name', 'customers', 'agents', 'status', 'actions'];
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
            'power_name',
            'power_number',
        ];
    }

    protected function getCustomOrder(): array
    {
        return [1, 'desc'];
    }

    protected function getButtons(): array
    {
        if (auth()->user()->can('إضافة وكالة')) {
            return [
                [
                    'text'      => '<i class="fas fa-plus-circle me-1"></i> إضافة وكالة',
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
        $badgeClass = sprintf('badge bg-%s', $row->status->color());
        $statusLabel = $row->status->label();

        if ($row->status == PowerOfAttorneyStatus::Expired) {
            return sprintf(
                '<span class="%s" title="لا يمكن تغيير حالة هذه الوكالة">%s</span>',
                $badgeClass,
                $statusLabel
            );
        }

        return sprintf(
            '<span class="%s cursor-pointer status-toggle" data-id="%d" title="اضغط هنا لتغيير حالة الوكالة">%s</span>',
            $badgeClass,
            $row->id,
            $statusLabel
        );
    }
}
