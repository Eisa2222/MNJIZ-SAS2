<?php

namespace App\DataTables\OperationsCenter\Offer;

use App\DataTables\ArabicSearchDataTable;
use App\Models\OperationsCenter\Offer\Offers;
use Illuminate\Database\Eloquent\Builder;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Support\Collection;

class OfferTechnicalStudyDataTable extends ArabicSearchDataTable
{
    public function __construct() {}

    /*
    |============================================================================
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    |============================================================================
    */
    protected function resource()
    {
        return Offers::with(['customer', 'relationshipManager'])->get();
    }

    protected function resourceKey(): string
    {
        return 'offers';
    }

    protected function tableId(): string
    {
        return 'offers-technical-study-table';
    }


    protected function getColumns(): array
    {
        return [
            // ID
            [
                'data'       => 'checkbox',
                'name'       => 'checkbox',
                'title'      => '<input type="checkbox" id="select-all">',
                'orderable'  => false,
                'searchable' => false,
                'width'      => '10px',
                'className'  => 'custom-checkbox',
            ],

            ['data' => 'offer_name',            'name' => 'offer_name',             'title' => 'اسم العرض',     'className' => 'table-ellipsis'],
            ['data' => 'offer_number',          'name' => 'offer_number',           'title' => 'رقم العرض',     'className' => 'table-ellipsis'],
            ['data' => 'customer',              'name' => 'customer',               'title' => 'العميل',        'className' => 'table-ellipsis'],
            ['data' => 'relationship_manager',  'name' => 'relationship_manager',   'title' => 'مسؤول العلاقات', 'className' => 'table-ellipsis'],
            ['data' => 'status',                'name' => 'status',                 'title' => 'الحالة',         'className' => 'table-ellipsis'],

            // Actions
            ['data' => 'actions', 'name' => 'actions', 'title' => 'الإجراءات', 'className' => 'table-ellipsis', 'orderable'  => false, 'searchable' => false,],
        ];
    }

    /*
    |============================================================================
    |============================================================================
    |                               Optional Methods
    |============================================================================
    |============================================================================
    */
    protected function filterableColumns(): array
    {
        return ['offer_name', 'id'];
    }

    protected function formatRows(Collection $rows): Collection
    {
        return $rows->map(function ($row) {
            return [
                'checkbox'             => $this->checkbox($row),
                'offer_name'           => $row->offer_name,
                'offer_number'         => $row->offer_number,
                'customer'             => optional($row->customer)->name ?? '—',
                'relationship_manager' => optional($row->relationshipManager)->name ?? '—',
                'status'               => $this->statusBadge($row),
                'actions'              => view('operations_center.offers.offer_study.technical_study.action', compact('row'))->render()
            ];
        });
    }



    protected function rawColumns(): array
    {
        return ['checkbox', 'status', 'actions'];
    }



    private function checkbox($row): string
    {
        return '<input type="checkbox" class="row-checkbox" value="' . $row->id . '">';
    }

    private function statusBadge($row): string
    {
        $badge = $row->status_badge;
        return "<span title='{$row->status_tooltip}' class=\"badge bg-{$badge['bg']}\">{$row->status_name}</span>";
    }
}
