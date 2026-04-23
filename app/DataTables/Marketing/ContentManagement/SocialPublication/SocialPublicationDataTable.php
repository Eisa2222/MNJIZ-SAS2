<?php

namespace App\DataTables\Marketing\ContentManagement\SocialPublication;

use App\DataTables\ArabicSearchDataTable;
use App\Models\Marketing\ContentManagement\SocialPublication\SocialPublication;
use PHPUnit\Framework\MockObject\Stub\ReturnCallback;

class SocialPublicationDataTable extends ArabicSearchDataTable
{
    public  $content_management_id;
    /*
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    */
    
    protected function getTableId(): string
    {
        return "social-publication-table";
    }

    protected function resource()
    {
        return SocialPublication::where('content_management_id', $this->content_management_id);
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

            ['data' => 'platform',          'name' => 'platform',           'title' => 'المنصة'],
            ['data' => 'scheduled_for',     'name' => 'scheduled_for',      'title' => 'مواعيد النشر '],
            ['data' => 'status',            'name' => 'status',             'title' => 'الحالة'],
            ['data' => 'error_message',     'name' => 'error_message',      'title' => 'سبب الفشل',     'width' => '100px'],
            ['data' => 'platform_post_id',  'name' => 'platform_post_id',   'title' => 'رابط المنشور '],
            ['data' => 'published_at',      'name' => 'published_at',       'title' => 'تاريخ النشر'],

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

            ->editColumn('status', function ($row) {
                return $this->statusBadge($row);
            })

            ->editColumn('scheduled_for', function ($row) {
                return "<p style='font-size:11px; margin:0px;'>" . $row->scheduled_for . "</p>";
            })

            ->editColumn('error_message', function ($row) {
                return $row->error_message ? "<p style='font-size:11px; margin:0px;'>" . $row->error_message . "</p>" : '-';
            })

            ->editColumn('platform_post_id', function ($row) {
                return $row->platform_post_id ? $this->routeName($row->post_url, "رابط المنشور ", "", "_blank") : '-';
            })

            ->editColumn('published_at', function ($row) {
                return $row->published_at ? "<p style='font-size:11px; margin:0px;'>" .  $row->published_at?->format('Y-m-d  H:m:i') ?? '-' . "</p>": "-";
            });
    }



    protected function rawColumns(): array
    {
        return ['status', 'scheduled_for', 'error_message', 'platform_post_id', 'published_at'];
    }
    /*
    |============================================================================
    |                               Optional Methods
    |============================================================================
    */


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
