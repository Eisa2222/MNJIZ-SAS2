<?php

namespace App\DataTables\Qoyod;

use Illuminate\Support\Collection;
use Yajra\DataTables\Services\DataTable;
use App\Services\Qoyod\Contracts\CrudResourceInterface;

abstract class AbstractResourceDataTable extends DataTable
{
    protected int $recordsTotal    = 0;
    protected int $recordsFiltered = 0;

    /*
    |============================================================================
    |============================================================================
    |                               DataTable Methods
    |============================================================================
    |============================================================================
    */
    public function dataTable($query)
    {
        $rows = $this->formatRows($query);

        return datatables()
            ->collection($rows)
            ->addIndexColumn()
            ->skipPaging()
            ->with([
                'recordsTotal'    => $this->recordsTotal,
                'recordsFiltered' => $this->recordsFiltered,
            ])
            ->rawColumns($this->rawColumns());
    }


    public function query(): Collection
    {
        $request = $this->request;
        $start   = (int)$request->get('start', 0);
        $length  = (int)$request->get('length', 10);
        $page    = (int) floor($start / $length) + 1;

        $filters = $request->only($this->filterableColumns());

        $apiQuery = array_merge($filters, [
            'page'     => $page,
            'per_page' => $length,
        ]);

        $resp = $this->resource()->all($apiQuery);

        $items =  $resp[$this->resourceKey()];

        // determine total count
        if (isset($resp['meta']['pagination']['total'])) {
            $total = (int)$resp['meta']['pagination']['total'];
        } elseif (isset($resp['total'])) {
            $total = (int)$resp['total'];
        } else {
            $fetched = count($items);
            $total   = $fetched === $length
                ? ($page + 1) * $length
                : (($page - 1) * $length) + $fetched;
        }

        $this->recordsTotal    = $total;
        $this->recordsFiltered = $total;

        return collect($items);
    }

    public function html()
    {
        return $this->builder()
            ->setTableId($this->tableId())
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->processing(true)
            ->serverSide(true)
            ->orderBy(1)
            ->dom($this->dom())
            ->buttons($this->buttons())
            ->parameters($this->parameters());
    }


    /*
    |============================================================================
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    |============================================================================
    */
    // This method should return the resource instance that implements CrudResourceInterface.
    abstract protected function resource(): CrudResourceInterface;

    // This method should return the key used to access the resource in the response.
    abstract protected function resourceKey(): string;

    // This method should return the unique ID for the DataTable.
    abstract protected function tableId(): string;

    // This method should return the columns to be displayed in the DataTable.
    abstract protected function getColumns(): array;


    /*
    |============================================================================
    |============================================================================
    |                               Optional Methods
    |============================================================================
    |============================================================================
    */
    // This method returns the columns that can be filtered. 
    protected function filterableColumns(): array
    {
        return [];
    }

    // This method formats the rows before rendering. 
    protected function formatRows(Collection $rows): Collection
    {
        return $rows;
    }

    // This method specifies which columns should be treated as raw HTML.
    protected function rawColumns(): array
    {
        return [];
    }

    protected function getButtons(): array
    {
        return [];
    }

    /*
    |============================================================================
    |============================================================================
    |                          default settings methods
    |============================================================================
    |============================================================================
    */
    // This method defines the DOM structure for the DataTable.
    protected function dom(): string
    {
        return  '<"dt-toolbar"' .
            '<"dt-toolbar-left d-flex flex-column flex-sm-row align-items-start align-items-sm-center" l >' .
            '<"dt-toolbar-right d-flex flex-column flex-sm-row align-items-end align-items-sm-center justify-content-between" f B >' .
            '>' .
            'rt' .
            '<"row mt-3"' .
            '<"d-flex flex-column flex-sm-row align-items-center justify-content-between w-100" i <"pagination-wrapper overflow-auto w-100" p >' .
            '>' .
            '>';
    }

    // This method defines the buttons to be displayed in the DataTable.
    protected function buttons(): array
    {
        return [
            [
                'extend'    => 'collection',
                'className' => 'btn btn-export btn',
                'text'      => 'الإجراءات',
                'buttons'   => [
                    [
                        'extend'        => 'copy',
                        'text'          => 'نسخ',
                        'exportOptions' => [
                            'columns' => ':not(:last-child)', // استبعاد عمود الإجراءات
                        ],
                    ],
                    [
                        'extend'        => 'excel',
                        'text'          => 'إكسل',
                        'exportOptions' => [
                            'columns' => ':not(:last-child)', // استبعاد عمود الإجراءات
                        ],
                    ],
                ],
            ],
            $this->getButtons()
        ];
    }

    // This method defines the parameters for the DataTable.
    protected function parameters(): array
    {
        return [
            'responsive' => true,
            'processing' => true,
            'serverSide' => true,
            'language'   => ['url' => asset('assets/json/ar.json')],
            'pageLength' => 10,
        ];
    }

    /*
    |============================================================================
    |============================================================================
    |                            Additional Methods
    |============================================================================
    |============================================================================
    */
    protected function emptyDataMessage($message = null): string
    {
        return '<span class="text-danger">'
            . ($message ?? 'غير متوفر')
            . '</span>';
    }
}
