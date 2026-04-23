<?php

namespace App\DataTables\Hr\CompanyPolicy;


use App\DataTables\ArabicSearchDataTable;
use App\Models\Hr\CompanyPolicy\CompanyPolicy;

class CompanyPolicyDataTable extends ArabicSearchDataTable
{
    private $route = "hr.company-policy";
    private $page  = "hr.company_policy";

    /*
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    */
    protected function getTableId(): string
    {
        return "company-policy-table";
    }

    protected function resource()
    {
        return CompanyPolicy::query();
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

            ['data' => 'name',              'name' => 'name',               'title' => 'الاسم',       'width' => '150px',],
            ['data' => 'file_path',         'name' => 'file_path',          'title' => 'المرفق',        'width' => '150px',],
            ['data' => 'is_mandatory',      'name' => 'is_mandatory',       'title' => 'الحالة',        'width' => '50px',],
            ['data' => 'created_by',        'name' => 'created_by',         'title' => 'اضيف بواسطة',      'width' => '150px',],
            ['data' => 'updated_by',        'name' => 'updated_by',         'title' => 'اخر تحديث بواسطة',      'width' => '150px',],

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


            ->editColumn('file_path', function ($row) {
                return $this->getFileLink($row);
            })

            ->editColumn('is_mandatory', function ($row) {
                return $this->statusBadge($row);
            })

            ->addColumn('created_by', function ($row) {
                return $this->routeName(route('account.employee.profile', $row->created_by), $row->createdBy?->getRawNameAttribute() ?? '-');
            })

            ->addColumn('updated_by', function ($row) {
                return $row->updated_by ? $this->routeName(route('account.employee.profile', $row->updated_by), $row->updatedBy->getRawNameAttribute()) : '-';
            })


            ->addColumn('actions', function ($row) {
                return (auth()->user()->can('تعديل اللوائح و السياسات') || auth()->user()->can('حذف اللوائح و السياسات')) ? view($this->page . '.action', ['row' => $row, 'route' => $this->route])->render() : ' ليس لديك صلاحيات';
            });
    }

    protected function applyCustomFilters($query)
    {
        $request = request();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('employee')) {
            $query->where('employee_id', $request->employee);
        }

        return $query;
    }

    protected function rawColumns(): array
    {
        return ['checkbox', 'is_mandatory', 'file_path', 'created_by', 'updated_by', 'actions'];
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
            'employee.name',
            'employee.nickname',
        ];
    }

    protected function getCustomOrder(): array
    {
        return [1, 'desc'];
    }

    protected function getButtons(): array
    {
        if (auth()->user()->can('إضافة اللوائح و السياسات')) {
            return [
                [
                    'text'      => '<i class="fas fa-plus-circle me-1"></i> إضافة سياسات و لوائح',
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
        if ($row->is_mandatory) {
            return '<span class="badge bg-danger">إلزامية</span>';
        } else {
            return '<span class="badge bg-success">اختيارية</span>';
        }
    }

    private function getFileLink($row): string
    {
        if ($row->file_path) {
            $url = asset('storage/' . $row->file_path);
            return '<a href="' . $url . '" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-file-pdf me-1"></i> عرض الملف
                    </a>';
        }
        return '<span class="text-muted">لا يوجد ملف</span>';
    }
}
