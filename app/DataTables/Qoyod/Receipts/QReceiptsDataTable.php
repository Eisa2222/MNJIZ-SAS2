<?php

namespace App\DataTables\Qoyod\Receipts;

use App\DataTables\Qoyod\AbstractResourceDataTable;
use App\Services\Qoyod\Contracts\Resources\ReceiptResourceInterface;
use Illuminate\Support\Collection;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Utilities\Expression;


class QReceiptsDataTable extends AbstractResourceDataTable
{
    public function __construct(private ReceiptResourceInterface $receipts) {}

    /*
    |============================================================================
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    |============================================================================
    */
    protected function resource(): ReceiptResourceInterface
    {
        return $this->receipts;
    }

    protected function resourceKey(): string
    {
        return 'receipts';
    }

    protected function tableId(): string
    {
        return 'receipts-table';
    }

    protected function getColumns(): array
    {
        return [
            // ID
            ['data' => 'DT_RowIndex', 'title' => 'م', 'orderable' => false, 'searchable' => false, 'width' => '10px'],


            ['data' => 'reference',      'name' => 'reference',       'title' => 'رقم المرجع',         'className' => 'table-ellipsis'],
            ['data' => 'description',    'name' => 'description',     'title' => 'الوصف',              'className' => 'table-ellipsis'],
            ['data' => 'date',           'name' => 'date',            'title' => 'التاريخ',             'className' => 'table-ellipsis'],
            ['data' => 'amount',         'name' => 'amount',          'title' => 'المبلغ',              'className' => 'table-ellipsis'],
            ['data' => 'kind',           'name' => 'kind',            'title' => 'النوع',              'className' => 'table-ellipsis'],

            // Actions
            ['data' => 'actions', 'name' => 'actions', 'title' => 'الإجراءات', 'orderable' => false, 'searchable' => false, 'width' => '50px'],
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
        return ['reference', 'description', 'date', 'amount', 'kind'];
    }

    protected function formatRows(Collection $rows): Collection
    {

        return $rows->map(fn($row) => [
            ...$row,
            'reference' => $this->routeName(route('qoyod.receipts.show', $row['id']), $row['reference']),
            'kind'      => $this->getKind($row['kind']),
            'actions'   => view('qoyod.receipts.actions', compact('row'))->render(),
        ]);
    }


    protected function rawColumns(): array
    {
        return ['reference', 'kind', 'actions'];
    }


    protected function getButtons(): array
    {
        return [
            [
                'text'      => '<i class="fas fa-plus-circle me-1"></i> إضافة إيصال ',
                'className' => 'btn btn-primary btn-add',
                'action'    => "function(){ window.location.href='" . route('qoyod.receipts.create') . "'; }",
            ],
        ];
    }

    private function getKind($kind): string
    {
        return match ($kind) {
            'paid'          => '<span class="badge bg-success">صرف</span>',
            'received'      => '<span class="badge bg-warning">قبض</span>',
            default         => $kind,
        };
    }
}
