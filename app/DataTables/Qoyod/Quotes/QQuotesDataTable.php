<?php

namespace App\DataTables\Qoyod\Quotes;

use App\DataTables\Qoyod\AbstractResourceDataTable;
use App\Services\Qoyod\Contracts\Resources\QuoteResourceInterface;
use App\Services\Qoyod\Presenters\Quotes\QuotePresenter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Utilities\Expression;


class QQuotesDataTable extends AbstractResourceDataTable
{
    public function __construct(private QuoteResourceInterface $quote , private QuotePresenter $quotePresenter) {}

    /*
    |============================================================================
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    |============================================================================
    */
    protected function resource(): QuoteResourceInterface
    {
        return $this->quote;
    }

    protected function resourceKey(): string
    {
        return 'quote';
    }

    protected function tableId(): string
    {
        return 'quote-table';
    }

    protected function getColumns(): array
    {
        return [
            // ID
            ['data' => 'DT_RowIndex', 'title' => 'م', 'orderable' => false, 'searchable' => false, 'width' => '10px'],


            ['data' => 'reference',        'name' => 'reference',          'title' => 'المرجع',                 'className' => 'table-ellipsis'],
            ['data' => 'contact_name',        'name' => 'contact_name',          'title' => 'العميل',                 'className' => 'table-ellipsis'],
            ['data' => 'total_amount',        'name' => 'total_amount',          'title' => 'المجموع',                 'className' => 'table-ellipsis'],
            ['data' => 'issue_date',        'name' => 'issue_date',          'title' => 'تاريخ الاصدار',                 'className' => 'table-ellipsis'],
            ['data' => 'expiry_date',        'name' => 'expiry_date',          'title' => 'تاريخ الانتهاء',                 'className' => 'table-ellipsis'],
            ['data' => 'total_amount',        'name' => 'total_amount',          'title' => 'القيمة الإجمالية',                 'className' => 'table-ellipsis'],
            ['data' => 'status',        'name' => 'status',          'title' => 'الحالة',                 'className' => 'table-ellipsis'],

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
        return ['reference','contact_name', 'total_amount', 'issue_date', 'expiry_date', 'total_amount', 'status'];
    }

    protected function formatRows(Collection $rows): Collection
    {
        $presented = collect(
            $this->quotePresenter->getQuotesWithRelatedNames($rows->toArray())
        );

        return $presented->map(fn(array $row) => [
            ...$row,
            'reference'  => $this->routeName(route('qoyod.quotes.show', $row['id']), $row['reference']),
            'status'     => $this->getStatus($row['status']),
            'actions' => view('qoyod.quotes.actions', [
                'row' => $row,
            ])->render()
        ]);

    }


    protected function rawColumns(): array
    {
        return ['reference','status', 'actions'];
    }


    protected function getButtons(): array
    {
        return [
            [
                'text'      => '<i class="fas fa-plus-circle me-1"></i> إضافة عرض سعري',
                'className' => 'btn btn-primary btn-add',
                'action'    => "function(){ window.location.href='" . route('qoyod.quotes.create') . "'; }",
            ],
        ];
    }

    private function getStatus($status): string
    {
        return match ($status) {
            'Approved'            => '<span class="badge bg-success">موافق عليه</span>',
            'Draft'               => '<span class="badge bg-warning">مسودة</span>',
            'Invoiced'            => '<span class="badge bg-info">تمت الفوترة</span>',
            'Cancelled'           => '<span class="badge bg-primary">ملغي</span>',
            default                     => $status,
        };
    }
}
