<?php

namespace App\DataTables\Qoyod\Purchases\Bills;

use App\DataTables\Qoyod\AbstractResourceDataTable;
use App\Services\Qoyod\Contracts\Resources\BillResourceInterface;
use App\Services\Qoyod\Presenters\Accounts\AccountsPresenter;
use Illuminate\Support\Collection;
use Yajra\DataTables\Services\DataTable;


class QBillsDataTable extends AbstractResourceDataTable
{
    public function __construct(
        private BillResourceInterface   $bills,
        private AccountsPresenter       $accountPresenter
    ) {}

    /*
    |============================================================================
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    |============================================================================
    */
    protected function resource(): BillResourceInterface
    {
        return $this->bills;
    }

    protected function resourceKey(): string
    {
        return 'bills';
    }

    protected function tableId(): string
    {
        return 'bills-table';
    }

    protected function getColumns(): array
    {
        return [
            // ID
            ['data' => 'DT_RowIndex', 'title' => 'م', 'orderable' => false, 'searchable' => false, 'width' => '10px'],


            ['data' => 'reference',         'name' => 'reference',          'title' => 'المرجع',                 'className' => 'table-ellipsis'],
            ['data' => 'issue_date',        'name' => 'issue_date',         'title' => '	تاريخ الإصدار',       'className' => 'table-ellipsis'],
            ['data' => 'due_date',          'name' => 'due_date',           'title' => 'تاريخ الاستحقاق',         'className' => 'table-ellipsis'],
            ['data' => 'total',             'name' => 'total',              'title' => 'القيمة الاجمالية',        'className' => 'table-ellipsis'],
            ['data' => 'paid_amount',       'name' => 'paid_amount',        'title' => 'تم دفع ',                 'className' => 'table-ellipsis'],
            ['data' => 'due_amount',        'name' => 'due_amount',         'title' => 'المتبقي',                 'className' => 'table-ellipsis'],
            ['data' => 'status',            'name' => 'status',             'title' => 'الحالة',                  'className' => 'table-ellipsis'],

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
        return ['reference', 'issue_date', 'due_date', 'total', 'paid_amount', 'due_amount', 'status'];
    }

    protected function formatRows(Collection $rows): Collection
    {
        $pettyCashAccount = $this->accountPresenter->getFilterAccounts('group_type', 'Petty cash');
        return $rows->map(fn($row) => [
            ...$row,
            'reference'  => $this->routeName(route('qoyod.bills.show', $row['id']), $row['reference']),
            'status'     => $this->getStatus($row['status']),
            'actions' => view('qoyod.purchases.bills.actions', [
                'row' => $row,
                'pettyCashAccount' => $pettyCashAccount
            ])->render(),
        ]);
    }


    protected function rawColumns(): array
    {
        return ['reference', 'status', 'actions'];
    }


    protected function getButtons(): array
    {
        return [
            [
                'text'      => '<i class="fas fa-plus-circle me-1"></i> إضافة فاتورة مشتريات',
                'className' => 'btn btn-primary btn-add',
                'action'    => "function(){ window.location.href='" . route('qoyod.bills.create') . "'; }",
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
