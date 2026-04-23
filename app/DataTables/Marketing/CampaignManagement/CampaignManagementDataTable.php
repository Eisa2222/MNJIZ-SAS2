<?php

namespace App\DataTables\Marketing\CampaignManagement;

use App\DataTables\ArabicSearchDataTable;
use App\Models\Marketing\CampaignManagement\CampaignManagement;

class CampaignManagementDataTable extends ArabicSearchDataTable
{
    private $route = "marketing.campaign-management";

    /*
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    */
    protected function getTableId(): string
    {
        return "campaign-management-table";
    }

    protected function resource()
    {
        return CampaignManagement::with([
            'contentType',
            'campaignSection',
        ]);
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
            ['data' => 'campaign_name',       'name' => 'campaign_name',       'title' => 'الحملة'],
            [
                'data'       => 'campaign_section_color',
                'name'       => 'campaign_section_color',
                'title'      => 'Color',
                'visible'    => false,
                'searchable' => false,
            ],
            ['data' => 'content_type_id',     'name' => 'content_type_id',     'title' => 'نوع المحتوى'],
            ['data' => 'campaign_section_id', 'name' => 'campaign_section_id', 'title' => 'قسم الحملة'],
            ['data' => 'budget',              'name' => 'budget',              'title' => 'الميزانية'],
            ['data' => 'start_date',          'name' => 'start_date',          'title' => 'تاريخ البدء'],
            ['data' => 'end_date',            'name' => 'end_date',            'title' => 'تاريخ الانتهاء'],
            ['data' => 'status',              'name' => 'status',              'title' => 'الحالة'],

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
            ->addColumn('checkbox', fn($row) => $this->checkbox($row))

            ->editColumn('campaign_name', function ($row) {
                return $this->routeName(route('marketing.campaign-management.show', $row->id), $row->campaign_name);
            })

            ->editColumn('content_type_id', fn($row) => optional($row->contentType)->name ?? '-')

            ->editColumn('campaign_section_id', fn($row) => optional($row->campaignSection)->name ?? '-')

            ->addColumn('campaign_section_color', fn($row) => optional($row->campaignSection)->color ?? '#FFFFFF')

            
            ->editColumn('content_purpose_id', fn($row) => optional($row->contentPurpose)->name ?? '-')

            ->editColumn('social_id', fn($row) => optional($row->social)->name ?? '-')

            ->editColumn('budget', fn($row) => number_format($row->budget, 2))

            ->editColumn('start_date', fn($row) => $row->start_date ?? '-')

            ->editColumn('end_date', fn($row) => $row->end_date ?? '-')

            ->editColumn('status', fn($row) => $this->statusBadge($row))

            ->addColumn(
                'actions',
                fn($row) =>
                view('marketing.campaign_management.action', ['row' => $row, 'route' => $this->route])->render()
            );
    }


    protected function applyCustomFilters($query)
    {
        $request = request();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('content_type_id', $request->type);
        }

        if ($request->filled('section')) {
            $query->where('campaign_section_id', $request->section);
        }

        return $query;
    }

    protected function rawColumns(): array
    {
        return ['checkbox', 'campaign_name', 'campaign_section_id', 'status', 'actions'];
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
            'campaign_name',
            'content_type_id',
            'campaign_section_id',
            'content_purpose_id',
            'social_id',
            'status',
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
                'text'      => '<i class="fas fa-plus-circle me-1"></i> إضافة حملة',
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
}
