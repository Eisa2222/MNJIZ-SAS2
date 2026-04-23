<?php

namespace App\DataTables\Marketing\ContentManagement;

use App\DataTables\ArabicSearchDataTable;
use App\Models\Hr\Advances\Advance;
use App\Models\Marketing\ContentManagement\ContentManagement;

class ContentManagementDataTable extends ArabicSearchDataTable
{
    private $route = "marketing.content-management";

    /*
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    */
    protected function getTableId(): string
    {
        return "content-management-table";
    }

    protected function resource()
    {
        return ContentManagement::query();
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

            ['data' => 'content_type_id',          'name' => 'content_type_id',          'title' => 'نوع المحتوى'],
            ['data' => 'publishing_pattern_id',    'name' => 'publishing_pattern_id',    'title' => 'نمط النشر'],
            ['data' => 'content_purpose_id',       'name' => 'content_purpose_id',       'title' => 'الهدف من المحتوى'],
            ['data' => 'media_type',               'name' => 'media_type',               'title' => 'نوع الوسائط'],
            ['data' => 'status',                   'name' => 'status',                   'title' => 'حالة الاعتماد'],
            ['data' => 'publication_status',       'name' => 'publication_status',       'title' => 'حالة النشر'],
            ['data' => 'publication_date',         'name' => 'publication_date',         'title' => 'تاريخ النشر'],

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

            ->editColumn('content_type_id', function ($row) {
                return $this->routeName(route('marketing.content-management.show', $row->id), $row->contentType->name);
            })

            ->editColumn('publishing_pattern_id', function ($row) {
                return $row->publishingPattern ?  $row->publishingPattern->name : '-';
            })

            ->editColumn('content_purpose_id', function ($row) {
                return $row->contentpurpose ?  $row->contentpurpose->name : '-';
            })

            ->editColumn('publication_date', function ($row) {
                return $row->publication_date?->format('Y-m-d') ?? '-';
            })

            ->editColumn('media_type', function ($row) {
                return $this->getMediaType($row);
            })

            ->editColumn('status', function ($row) {
                return $this->statusBadge($row);
            })

            ->editColumn('publication_status', function ($row) {
                return $this->publication_statusBadge($row);
            })

            ->addColumn('actions', function ($row) {
                return view('marketing.content_management.action', ['row' => $row, 'route' => $this->route])->render();
            });
    }

    protected function applyCustomFilters($query)
    {
        $request = request();

        if ($request->filled('status')) {
            $query->where('publication_status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('content_type_id', $request->type);
        }

        if ($request->filled('pattern')) {
            $query->where('publishing_pattern_id', $request->pattern);
        }

        return $query;
    }

    protected function rawColumns(): array
    {
        return ['checkbox', 'content_type_id', 'media_type', 'status','publication_status', 'actions'];
    }
    /*
    |============================================================================
    |                               Optional Methods
    |============================================================================
    */
    // الحقول المسموح البحث فيها 
    protected function filterableColumns(): array
    {
        return [
            'content_type_id',
            'publishing_pattern_id',
            'content_purpose_id',
            'media_type',
            'status',
            'publication_status',
            'publication_date',
        ];
    }

    protected function getCustomOrder(): array
    {
        return [1, 'desc'];
    }

    protected function getButtons(): array
    {
        return [
            [
                'text'      => '<i class="fas fa-plus-circle me-1"></i> إضافة محتوى',
                'className' => 'btn btn-primary btn-add',
                'action'    => "function(){ window.location.href='" . route($this->route . '.create') . "'; }",
            ],
        ];
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


    private function publication_statusBadge($row): string
    {

        return sprintf(
            '<span class="badge bg-%s">%s</span>',
            $row->publication_status->color(),
            $row->publication_status->label()
        );
    }


    private function getMediaType($row): string
    {
        return $row->media_type->label();
    }
}
