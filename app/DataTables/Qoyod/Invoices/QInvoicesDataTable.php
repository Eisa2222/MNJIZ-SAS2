<?php

namespace App\DataTables\Qoyod\Invoices;

use App\DataTables\Qoyod\AbstractResourceDataTable;
use App\Services\Qoyod\Contracts\Resources\InvoiceResourceInterface;
use App\Services\Qoyod\Presenters\Accounts\AccountsPresenter;
use App\Services\Qoyod\Presenters\Invoices\InvoicePresenter;
use Illuminate\Support\Collection;
use Yajra\DataTables\Services\DataTable;


class QInvoicesDataTable extends AbstractResourceDataTable
{
    public function __construct(
        private InvoiceResourceInterface    $invoices,
        private InvoicePresenter            $invoicePresenter,
        private AccountsPresenter           $accountPresenter
    ) {}


    /*
    |============================================================================
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    |============================================================================
    */
    protected function resource(): InvoiceResourceInterface
    {
        return $this->invoices;
    }

    protected function resourceKey(): string
    {
        return 'invoices';
    }

    protected function tableId(): string
    {
        return 'invoices-table';
    }

    protected function getColumns(): array
    {
        return [
            // ID
            ['data' => 'DT_RowIndex', 'title' => 'م', 'orderable' => false, 'searchable' => false, 'width' => '10px'],

            ['data' => 'reference',      'name' => 'reference',       'title' => 'المرجع',              'className' => 'table-ellipsis'],
            ['data' => 'contact_name',   'name' => 'contact_name',    'title' => 'العميل',              'className' => 'table-ellipsis'],
            ['data' => 'issue_date',     'name' => 'issue_date',      'title' => 'تاريخ الاصدار',        'className' => 'table-ellipsis'],
            ['data' => 'due_date',       'name' => 'due_date',        'title' => 'تاريخ الاستحقاق',      'className' => 'table-ellipsis'],
            ['data' => 'total',          'name' => 'total',           'title' => 'القيمة الاجمالية',     'className' => 'table-ellipsis'],
            ['data' => 'due_amount',     'name' => 'due_amount',      'title' => 'الرصيد',               'className' => 'table-ellipsis'],
            ['data' => 'status',         'name' => 'status',          'title' => 'الحالة',               'className' => 'table-ellipsis'],

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
        return ['reference', 'contact_name', 'issue_date', 'due_date', 'total', 'due_amount', 'status'];
    }

    protected function formatRows(Collection $rows): Collection
    {
        $pettyCashAccount = $this->accountPresenter->getFilterAccounts('group_type', 'Petty cash');
        
        $presented = collect(
            $this->invoicePresenter->getInvoicesWithCustomerName($rows->toArray())
        );

        return $presented->map(fn(array $row) => [
            ...$row,
            'reference'  => $this->routeName(route('qoyod.invoices.show', $row['id']), $row['reference']),
            'status'     => $this->getStatus($row['status']),
            'actions'    => view('qoyod.invoices.actions', [
                'row'                => $row,
                'pettyCashAccount'   => $pettyCashAccount
            ])->render()
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
                'text'      => '<i class="fas fa-plus-circle me-1"></i> إضافة فاتورة مبيعات',
                'className' => 'btn btn-primary btn-add',
                'action'    => "function(){ window.location.href='" . route('qoyod.invoices.create') . "'; }",
            ],
        ];
    }


    private function getStatus($status): string
    {
        return match ($status) {
            'Paid'           => '<span class="badge bg-success">دفعت</span>',
            'Partially Paid' => '<span class="badge bg-warning">دفعت جزئيا</span>',
            'Approved'       => '<span class="badge bg-info">موافق عليه</span>',
            'Draft'          => '<span class="badge bg-secondary">مسودة</span>',
            default     => $status,
        };
    }
}
