<?php

namespace App\DataTables\Qoyod\Customers;

use App\DataTables\Qoyod\AbstractResourceDataTable;
use App\Services\Qoyod\Contracts\Resources\CustomerResourceInterface;
use Illuminate\Support\Collection;


class QCustomersDataTable extends AbstractResourceDataTable
{
    public function __construct(private CustomerResourceInterface $customers) {}

    /*
    |============================================================================
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    |============================================================================
    */
    protected function resource(): CustomerResourceInterface
    {
        return $this->customers;
    }

    protected function resourceKey(): string
    {
        return 'customers';
    }

    protected function tableId(): string
    {
        return 'customers-table';
    }

    protected function getColumns(): array
    {
        return [
            // ID
            ['data' => 'DT_RowIndex', 'title' => 'م', 'orderable' => false, 'searchable' => false, 'width' => '10px'],

            ['data' => 'name',            'name' => 'name',         'title' => 'الاسم',                'className' => 'table-ellipsis'],
            ['data' => 'organization',    'name' => 'organization', 'title' => 'اسم المنشأة',          'className' => 'table-ellipsis'],
            ['data' => 'phone_number',    'name' => 'phone_number', 'title' => 'الجوال',              'className' => 'table-ellipsis'],
            ['data' => 'email',           'name' => 'email',        'title' => 'البريد الإلكتروني',   'className' => 'table-ellipsis'],

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
        return ['name', 'organization', 'phone_number', 'email'];
    }

    protected function formatRows(Collection $rows): Collection
    {
        return $rows->map(fn($row) => [
            ...$row,
            'organization' => $row['organization']  ?: $this->emptyDataMessage(),
            'phone_number' => $row['phone_number']  ?: $this->emptyDataMessage(),
            'email'        => $row['email']         ?: $this->emptyDataMessage(),
            'actions'      => view('qoyod.customers.actions', compact('row'))->render(),
        ]);
    }

    protected function rawColumns(): array
    {
        return ['organization', 'phone_number', 'email', 'actions'];
    }

    protected function getButtons(): array
    {
        return [
            [
                'text'      => '<i class="fas fa-plus-circle me-1"></i> إضافة عميل',
                'className' => 'btn btn-primary btn-add',
                'action'    => "function(){ window.location.href='" . route('qoyod.customers.create') . "'; }",
            ],
        ];
    }
}
