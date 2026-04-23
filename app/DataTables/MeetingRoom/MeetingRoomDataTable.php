<?php

namespace App\DataTables\MeetingRoom;


use App\DataTables\ArabicSearchDataTable;
use App\Models\MeetingRoom\MeetingRoom;

class MeetingRoomDataTable extends ArabicSearchDataTable
{
    private $route  = "meeting-rooms";
    private $page   = "meeting_rooms";


    /*
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    */
    protected function getTableId(): string
    {
        return "meeting-rooms-table";
    }

    protected function resource()
    {
        return MeetingRoom::query();
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

            ['data' => 'title',         'name' => 'title',      'title' => 'عنوان الإجتماع'],
            ['data' => 'hall',          'name' => 'hall',       'title' => 'القاعة'],
            ['data' => 'date',          'name' => 'date',       'title' => 'التاريخ'],
            ['data' => 'from_time',     'name' => 'from_time',  'title' => 'من وقت'],
            ['data' => 'to_time',       'name' => 'to_time',    'title' => 'إلى وقت'],

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

            ->editColumn('hall', function ($row) {
                return $row->hall_name;
            })

            ->addColumn('actions', function ($row) {
                return  view($this->page . '.action', ['row' => $row, 'route' => $this->route])->render();
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
        return ['checkbox', 'title',  'actions'];
    }
    /*
    |============================================================================
    |                               Optional Methods
    |============================================================================
    */
    protected function getSearchableColumns(): array
    {
        return [
            'title',
            'hall',
            'date',
            'from_time',
            'to_time',
        ];
    }

    protected function getCustomOrder(): array
    {
        return [1, 'desc'];
    }

    protected function getButtons(): array
    {
        if (auth()->user()->can('إضافة حجز')) {
            return [
                [
                    'text'      => '<i class="fas fa-plus-circle me-1"></i> إضافة حجز',
                    'className' => 'btn btn-primary btn-add',
                    'action'    => "function(){ window.location.href='" . route($this->route . '.create') . "'; }",
                ],
            ];
        }
        return [];
    }
}
