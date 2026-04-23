<?php

namespace App\DataTables\Qoyod\Purchases\BillPayments;

use App\DataTables\Qoyod\AbstractResourceDataTable;
use App\Services\Qoyod\Contracts\Resources\BillPaymentResourceInterface;
use Illuminate\Support\Collection;


class QBillPaymentsDataTable extends AbstractResourceDataTable
{
    public function __construct(private BillPaymentResourceInterface $bill_payments) {}

    /*
    |============================================================================
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    |============================================================================
    */
    protected function resource(): BillPaymentResourceInterface
    {
        return $this->bill_payments;
    }

    protected function resourceKey(): string
    {
        return 'bill_payments';
    }

    protected function tableId(): string
    {
        return 'bill-payments-table';
    }

    protected function getColumns(): array
    {
        return [
            // ID
            ['data' => 'DT_RowIndex', 'title' => 'م', 'orderable' => false, 'searchable' => false, 'width' => '10px'],

            ['data' => 'reference',        'name' => 'reference',        'title' => 'رقم المرجع',      'className' => 'table-ellipsis'],
            ['data' => 'description',      'name' => 'description',      'title' => 'الوصف',            'className' => 'table-ellipsis'],
            ['data' => 'date',             'name' => 'date',             'title' => 'التاريخ',          'className' => 'table-ellipsis'],
            ['data' => 'amount',           'name' => 'amount',           'title' => 'المبلغ',           'className' => 'table-ellipsis'],
            ['data' => 'kind',             'name' => 'kind',             'title' => 'الحالة',           'className' => 'table-ellipsis'],

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
            'reference'  => $this->routeName(route('qoyod.bills.show', $row['id']), $row['reference']),
            'actions' => view('qoyod.purchases.bills.actions', [
                'row' => $row,
            ])->render(),
        ]);
    }


    protected function rawColumns(): array
    {
        return ['reference', 'actions'];
    }


    protected function getButtons(): array
    {
        return [
            [
                'text'      => '<i class="fas fa-plus-circle me-1"></i> إضافة مدفوعات مشتريات',
                'className' => 'btn btn-primary btn-add',
                'action'    => "function(){ window.location.href='" . route('qoyod.bill-payments.create') . "'; }",
            ],
        ];
    }

    private function getStatus($status): string
    {
        return match ($status) {
            'Approved'                  => '<span class="badge bg-success">موافق عليه</span>',
            'Draft'                     => '<span class="badge bg-warning">مسودة</span>',
            'Partially Paid'            => '<span class="badge bg-info">دفعت جزئيا</span>',
            'Paid'                      => '<span class="badge bg-primary">دفعت</span>',
            'Awaiting for approval'     => '<span class="badge bg-danger">بانتظار الموافقة</span>',
            default                     => $status,
        };
    }
}
