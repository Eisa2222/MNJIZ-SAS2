<?php

namespace App\DataTables\Survey;


use App\DataTables\ArabicSearchDataTable;
use App\Models\Survey\Survey;

class SurveyDataTable extends ArabicSearchDataTable
{
    private $route  = "surveys";
    private $page   = "surveys";

    /*
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    */
    protected function getTableId(): string
    {
        return "survey-table";
    }

    protected function resource()
    {
        return Survey::query();
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

            ['data' => 'title',         'name' => 'title',      'title' => 'عنوان الإستبيان'],
            ['data' => 'type',          'name' => 'type',       'title' => 'النوع'],
            ['data' => 'created_by',    'name' => 'created_by', 'title' => 'أنشئ بواسطة'],
            ['data' => 'status',        'name' => 'status',     'title' => 'الحالة'],

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

            ->editColumn('title', function ($row) {
                return $this->routeName(route($this->route . '.show', $row->id), $row->title);
            })


            ->editColumn('type', function ($row) {
                return $this->typeBadge($row);
            })

            ->editColumn('created_by', function ($row) {
                return $this->routeName(route('account.employee.profile', $row->created_by), $row->createdBy?->getRawNameAttribute() ?? '-');
            })


            ->editColumn('status', function ($row) {
                return $this->statusBadge($row);
            })


            ->addColumn('actions', function ($row) {
                return view($this->page . '.action', ['row' => $row, 'route' => $this->route])->render();
                // return (auth()->user()->can('تعديل الإستبيان') || auth()->user()->can('حذف الإستبيان')) ? view('hr.advances.action', ['row' => $row, 'route' => $this->route])->render() : ' ليس لديك صلاحيات';
            });
    }

    protected function applyCustomFilters($query)
    {
        $request = request();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('advance_type', $request->type);
        }

        if ($request->filled('employee')) {
            $query->where('employee_id', $request->employee);
        }

        return $query;
    }

    protected function rawColumns(): array
    {
        return ['checkbox', 'title', 'created_by', 'status', 'actions'];
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
            'advance_number',
            'employee.name',
            'employee.nickname',
            'advance_type',
            'amount',
            'status',
            'due_date',
            'advance_date',
        ];
    }

    protected function getCustomOrder(): array
    {
        return [1, 'desc'];
    }

    // protected function getButtons(): array
    // {
    //     if (auth()->user()->can('إضافة إستبيان')) {
    //         return [
    //             [
    //                 'text'      => '<i class="fas fa-plus-circle me-1"></i> إضافة استبيان ',
    //                 'className' => 'btn btn-primary btn-add',
    //                 'action'    => "function(){ window.location.href='" . route($this->route . '.create') . "'; }",
    //             ],
    //         ];
    //     }
    //     return [];
    // }

    /*
    |============================================================================
    |                            Private Helper Methods
    |============================================================================
    */
    private function statusBadge($row): string
    {
        $url   = route($this->route . '.toggle-status', $row->id);
        $label = $row->status->label();
        $color = $row->status->color();

        return sprintf(
            '<span class="badge bg-%s status-toggle cursor-pointer" data-id="%d" data-url="%s" title="تبديل الحالة">%s</span>',
            e($color),
            $row->id,
            e($url),
            e($label)
        );
    }



    private function typeBadge($row): string
    {
        return
            $row->type->label();
    }
}
