<?php

namespace App\DataTables\Qoyod\Accounts;

use App\DataTables\Qoyod\AbstractResourceDataTable;
use App\Services\Qoyod\Contracts\Resources\AccountResourceInterface;
use Illuminate\Support\Collection;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Utilities\Expression;


class QAccountsDataTable extends AbstractResourceDataTable
{
    public function __construct(private AccountResourceInterface $accounts) {}

    /*
    |============================================================================
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    |============================================================================
    */
    protected function resource(): AccountResourceInterface
    {
        return $this->accounts;
    }

    protected function resourceKey(): string
    {
        return 'accounts';
    }

    protected function tableId(): string
    {
        return 'accounts-table';
    }

    protected function getColumns(): array
    {
        return [
            // ID
            ['data' => 'DT_RowIndex', 'title' => 'م', 'orderable' => false, 'searchable' => false, 'width' => '10px'],

            ['data' => 'code',            'name' => 'code',             'title' => 'الكود',                 'className' => 'table-ellipsis'],
            ['data' => 'name_ar',         'name' => 'name_ar',          'title' => 'اسم الحساب',     'className' => 'table-ellipsis'],
            ['data' => 'type',            'name' => 'type',             'title' => 'النوع',                 'className' => 'table-ellipsis'],
            ['data' => 'group_type',      'name' => 'group_type',       'title' => 'نوع المجموعة',          'className' => 'table-ellipsis'],
            ['data' => 'type_of_account', 'name' => 'type_of_account',  'title' => 'نوع الحساب',            'className' => 'table-ellipsis'],
            ['data' => 'status',          'name' => 'status',           'title' => 'الحالة',                'className' => 'table-ellipsis'],

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
        return ['name_ar', 'code', 'type', 'group_type','type_of_account','status'];
    }

    protected function formatRows(Collection $rows): Collection
    {
        return $rows->map(fn($row) => [
            ...$row,
            'code'                  => $this->routeName(route('qoyod.accounts.show', $row['id']), $row['code']),
            'type'                  => $this->type($row['type']),
            'status'                => $this->status($row['status']),
            'type_of_account'       => $this->accountType($row['type_of_account']),
            'actions'               => view('qoyod.accounts.actions', compact('row'))->render(),
        ]);
    }

    protected function rawColumns(): array
    {
        return ['code', 'status', 'actions'];
    }

    protected function getButtons(): array
    {
        return [
            [
                'text'      => '<i class="fas fa-plus-circle me-1"></i> إضافة حساب',
                'className' => 'btn btn-primary btn-add',
                'action'    => "function(){ window.location.href='" . route('qoyod.accounts.create') . "'; }",
            ],
        ];
    }

    private function type(string $type): string
    {
        return match ($type) {
            'Asset'     => 'أصل',
            'Liability' => 'التزامات',
            'Equity'    => 'رأسمال',
            'Revenue'   => 'إيرادات',
            'Expense'   => 'مصروفات',
            default      => $type,
        };
    }

    private function status($status): string
    {
        return match ($status) {
            'Active'    => '<span class="badge bg-success">نشط</span>',
            'Inactive'  => '<span class="badge bg-danger">غير نشط</span>',
            default     => '<span class="badge bg-secondary">غير محدد</span>',
        };
    }

    private function accountType(string $balance): string
    {
        return match ($balance) {
            'Debit'  => 'مدين',
            'Credit' => 'دائن',
            default   => $balance,
        };
    }
}
