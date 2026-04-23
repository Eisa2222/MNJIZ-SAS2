<?php

namespace App\DataTables\GeneralSetting\Marketing;

use App\DataTables\ArabicSearchDataTable;
use App\Models\general_setting\Marketing\SettingsCampaignSection;

class SettingsCampaignSectionDataTable extends ArabicSearchDataTable
{
    private $view  = "general_setting.marketing.campaign_sections";
    private $route = "settings-campaign-sections";

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
        return SettingsCampaignSection::query();
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


            ['data' => 'name',      'name' => 'name',       'title' => 'الاسم'],

            [
                'data' => 'color',
                'name' => 'color',
                'title' => 'اللون',
                'width'      => '80px',
                'orderable'  => false,
                'searchable' => false,
            ],

            ['data' => 'status',        'name' => 'status',        'title' => 'الحالة' , 'width' => '150px'],
            ['data' => 'created_at',    'name' => 'created_at',    'title' => 'تاريخ الإضافة' , 'width' => '150px'],


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

            ->addColumn('color', function ($row) {
                return $this->colorDisplayHover($row);
            })

            ->addColumn('status', function ($row) {
                switch ($row->status) {
                    case 'active':
                        return
                            '<span class="cursor-pointer badge bg-success status-toggle" data-id="' . $row->id . '">نشط</span>';

                    case 'inactive':
                        return
                            '<span class="cursor-pointer badge bg-danger status-toggle" data-id="' . $row->id . '">غير نشط</span>';

                    default:
                        return '<span class="badge bg-secondary">غير معروف</span>';
                }
            })

            ->editColumn('created_at', function ($row) {
                return $row->hijri_created_at ? $row->hijri_created_at : '';
            })

            ->addColumn('actions', function ($row) {
                return view($this->view . '.action', ['row' => $row, 'route' => $this->route])->render();
            });
    }


    protected function rawColumns(): array
    {
        return ['checkbox', 'color',  'status', 'created_at', 'actions'];
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
            'name',
            'user_id',
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
                'text'      => '<i class="fas fa-plus-circle me-1"></i> إضافة قسم',
                'className' => 'btn btn-primary btn-add',
                'action'    => "function(){ $('#addSectionModal').modal('show') }",
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


    /*
|============================================================================
|                            طرق عرض الألوان المختلفة
|============================================================================
*/

    // الطريقة الرابعة: دائرة كبيرة مع تأثير hover
    private function colorDisplayHover($row): string
    {
        return sprintf(
            '<div class="color-circle-hover  position-relative m-auto" 
                    style="width: 35px; height: 35px; background-color: %s; border-radius: 50%%; border: 2px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"
>
                    <div class="color-overlay position-absolute top-0 start-0 w-100 h-100 rounded-circle d-flex align-items-center justify-content-center"
                        style="background: rgba(0,0,0,0); transition: all 0.3s ease;">
                        <i class="fas fa-eye text-white" style="opacity: 0; transition: opacity 0.3s ease;"></i>
                    </div>
                </div>',
            $row->color,
        );
    }
}
