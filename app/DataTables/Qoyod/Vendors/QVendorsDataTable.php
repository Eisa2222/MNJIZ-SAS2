<?php

namespace App\DataTables\Qoyod\Vendors;

use App\DataTables\Qoyod\AbstractResourceDataTable;
use App\Services\Qoyod\Contracts\Resources\VendorResourceInterface;
use Illuminate\Support\Collection;


class QVendorsDataTable extends AbstractResourceDataTable
{
    public function __construct(private VendorResourceInterface $vendors) {}

    /*
    |============================================================================
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    |============================================================================
    */
    protected function resource(): VendorResourceInterface
    {
        return $this->vendors;
    }

    protected function resourceKey(): string
    {
        return 'vendors';
    }

    protected function tableId(): string
    {
        return 'vendors-table';
    }

    protected function getColumns(): array
    {
        return [
            // ID
            ['data' => 'DT_RowIndex', 'title' => 'م', 'orderable' => false, 'searchable' => false, 'width' => '10px'],

            ['data' => 'name',              'name' => 'name',           'title' => 'اسم المورد',        'className' => 'table-ellipsis'],
            ['data' => 'organization',      'name' => 'organization',   'title' => 'اسم المنشأة',      'className' => 'table-ellipsis'],
            ['data' => 'email',             'name' => 'email',          'title' => 'البريد الإلكتروني',        'className' => 'table-ellipsis'],
            ['data' => 'phone_number',      'name' => 'phone_number',   'title' => 'رقم الاتصال',        'className' => 'table-ellipsis'],
            ['data' => 'tax_number',        'name' => 'tax_number',     'title' => 'الرقم الضريبي',    'className' => 'table-ellipsis'],
            ['data' => 'status',            'name' => 'status',         'title' => 'الحالة',           'className' => 'table-ellipsis'],

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
        return ['organization', 'name', 'status', 'tax_number'];
    }

    protected function formatRows(Collection $rows): Collection
    {
        return $rows->map(fn($row) => [
            ...$row,
            'organization'            => $row['organization']   ?: $this->emptyDataMessage(),
            'name'                    => $this->routeName(route('qoyod.vendors.show', $row['id']), $row['name']),
            'email'                   => $row['email']          ?: $this->emptyDataMessage(),
            'phone_number'            => $row['phone_number']   ?: $this->emptyDataMessage(),
            'tax_number'              => $row['tax_number']     ?: $this->emptyDataMessage(),
            'status'                  => $this->getStatus($row['status']),

            'actions'                  => view('qoyod.vendors.actions', compact('row'))->render(),
        ]);
    }


    protected function rawColumns(): array
    {
        return ['organization', 'name', 'email', 'phone_number', 'tax_number', 'status', 'actions'];
    }


    protected function getButtons(): array
    {
        return [
            [
                'text'      => '<i class="fas fa-plus-circle me-1"></i> إضافة مورد',
                'className' => 'btn btn-primary btn-add',
                'action'    => "function(){ window.location.href='" . route('qoyod.vendors.create') . "'; }",
            ],
        ];
    }


    private function getStatus($status): string
    {
        return match ($status) {
            'Active'    => '<span class="badge bg-success">نشط</span>',
            'Inactive'  => '<span class="badge bg-danger">غير نشط</span>',
            default     => '<span class="badge bg-secondary">غير محدد</span>',
        };
    }
}
