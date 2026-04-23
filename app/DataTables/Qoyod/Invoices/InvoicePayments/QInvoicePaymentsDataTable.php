<?php

namespace App\DataTables\Qoyod\Invoices\InvoicePayments;

use App\Services\Qoyod\Contracts\Resources\InvoicePaymentResourceInterface;
use Illuminate\Support\Collection;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Utilities\Expression;


class QInvoicePaymentsDataTable extends DataTable
{
    public function __construct(private InvoicePaymentResourceInterface $invoice_payments) {}

    public function dataTable($query)
    {
        return datatables()
            ->collection($query)
            ->addIndexColumn()
            ->addColumn('actions',          fn($row) => view('qoyod.invoices.invoice_payments.actions', compact('row'))->render())
            ->rawColumns([
                'actions'
            ]);
    }

    public function query(): Collection
    {
        $resp  = $this->invoice_payments->all($this->request->query());
        $items = $resp['receipts'] ;
        return collect($items);
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('invoice_payments-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('<"dt-toolbar"' .
                '<"dt-toolbar-left d-flex flex-column flex-sm-row align-items-start align-items-sm-center" l >' .
                '<"dt-toolbar-right d-flex flex-column flex-sm-row align-items-end align-items-sm-center justify-content-between" f B >' .
                '>' .
                'rt' .
                '<"row mt-3"' .
                '<"d-flex flex-column flex-sm-row align-items-center justify-content-between w-100" i <"pagination-wrapper overflow-auto w-100" p >' .
                '>' .
                '>')
            ->orderBy(1)
            ->buttons([
                [
                    'extend' => 'collection',
                    'className' => 'btn btn-export btn',
                    'text'   => 'الإجراءات',
                    'buttons' => [
                        ['extend' => 'copy',  'text' => 'نسخ'],
                        ['extend' => 'excel', 'text' => 'إكسل'],
                    ],
                ],
                [
                    'text'      => '<i class="fas fa-plus-circle me-1"></i> إضافة موقع',
                    'className' => 'btn btn-primary btn-add',
                    // 'action'    => "function(){ window.location.href='" . route('') . "'; }",
                ],

            ])
            ->parameters([
                'responsive' => true,
                'language'   => ['url' => asset('assets/json/ar.json')],
                'pageLength' => 10,

            ]);
    }

    protected function getColumns(): array
    {
        return [
            ['data' => 'DT_RowIndex',     'title' => 'م',   'orderable' => false,  'searchable' => false],

            ['data' => 'reference',        'name' => 'reference',          'title' => 'رقم المرجع',                 'className' => 'table-ellipsis'],
            ['data' => 'description',           'name' => 'description',          'title' => 'الوصف',                 'className' => 'table-ellipsis'],
            ['data' => 'date',           'name' => 'date',          'title' => 'التاريخ',                 'className' => 'table-ellipsis'],
            ['data' => 'amount',           'name' => 'amount',          'title' => 'المبلغ',                 'className' => 'table-ellipsis'],
            ['data' => 'kind',           'name' => 'kind',          'title' => 'الحالة',                 'className' => 'table-ellipsis'],

            ['data' => 'actions',        'name' => 'actions',       'title' => 'الإجراءات',             'orderable' => false, 'searchable' => false],
        ];
    }

    protected function filename(): string
    {
        return 'Customers_' . date('YmdHis');
    }


    private function empty(): string
    {
        return '<p class="text-danger">لا يوجد </p>';
    }
}
