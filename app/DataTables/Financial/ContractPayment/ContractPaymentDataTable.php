<?php

namespace App\DataTables\Financial\ContractPayment;

use App\DataTables\ArabicSearchDataTable;
use App\Models\OperationsCenter\Contract\Contract;

class ContractPaymentDataTable extends ArabicSearchDataTable
{
    private $route = "financial.contract-payments";

    /*
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    */
    protected function getTableId(): string
    {
        return "contract-paymen-table";
    }

    protected function resource()
    {
        return Contract::query()
            ->select('contracts.*')
            ->with('customer:id,name')
            ->withCount([
                'contractPayments as payments_total',
                'paidPayments as payments_paid',
                'scheduledPayments as payments_scheduled',
                'latePayments as payments_late',
                'cancelledPayments as payments_cancelled',
            ]);
    }



    protected function getColumns(): array
    {
        return [
            [
                'data' => '',
                'orderable'  => false,
                'searchable' => false,
            ],
            [
                'data' => 'id',
                'name' => 'id',
                'title' => 'ID',
                'visible' => false,
                'orderable' => true,
                'searchable' => false,
            ],
            // Checkbox
            [
                'data'       => 'checkbox',
                'name'       => 'checkbox',
                'title'      => '<span class="custom-checkbox-header"><input type="checkbox" id="select-all"></span>',
                'orderable'  => false,
                'searchable' => false,
                'width'      => '10px',
                'className'  => 'custom-checkbox',
                'titleAttr'  => 'تحديد الكل',
            ],

            ['data' => 'contract_name',             'name' => 'contract_name',              'title' => 'اسم العقد',             'width' => '400px'],
            ['data' => 'contract_number',           'name' => 'contract_number',            'title' => 'رقم العقد',             'width' => '150x'],
            ['data' => 'customer',                  'name' => 'customer.name',              'title' => 'العميل',                'width' => '300px'],

            ['data' => 'payments_total',            'name' => 'payments_total',             'title' => 'الدفعات المطلوبة',      'width' => '150px'],
            ['data' => 'payments_scheduled',        'name' => 'payments_scheduled',         'title' => 'الدفعات المجدولة',      'width' => '150px'],
            ['data' => 'payments_paid',             'name' => 'payments_paid',              'title' => 'الدفعات المدفوعة',      'width' => '150px'],
            ['data' => 'payments_late',             'name' => 'payments_late',              'title' => 'الدفعات المتأخرة',      'width' => '150px'],
            ['data' => 'payments_cancelled',        'name' => 'payments_cancelled',         'title' => 'الدفعات الملغية',       'width' => '150px'],
            // ['data' => 'payments_state',            'name' => 'payments_state',             'title' => 'الحالة',                 'width' => '150px',  'orderable' => false,],

        ];
    }

    /*
    |============================================================================
    |                           Helper Methods
    |============================================================================
    */
    protected function addCustomColumns($datatable)
    {
        $datatable
            ->addColumn('checkbox', function ($row) {
                return $this->checkbox($row);
            })

            ->editColumn('contract_name', function ($row) {
                return $this->routeName(route($this->route . '.show', $row->id), $row->contract_name);
            })

            ->editColumn('contract_number', function ($row) {
                return $row->contract_number ?? '-';
            })

            ->editColumn('customer', function ($row) {
                return $row->customer ? $row->customer->name : 'غير متوفر';
            });

            // ->editColumn('payments_state', function ($row) {
            //     return $this->statusBadge($row);
            // });
    }

    protected function applyCustomFilters($query)
    {
        $request = request();

        // if ($request->filled('status')) {
        //     $query->whereHas('contractPayments', function ($q) use ($request) {
        //         $q->where('status', $request->status);
        //     });
        // }

        if ($request->filled('customer')) {
            $query->where('customer_id', $request->customer);
        }

        if ($request->filled('contract')) {
            $query->where('id', $request->contract);
        }

        return $query;
    }

    protected function rawColumns(): array
    {
        return ['checkbox', 'contract_name'];
    }

    /*
    |============================================================================
    |                               Optional Methods
    |============================================================================
    */
    // الحقول المسموح البحث فيها 
    protected function getSearchableColumns(): array
    {
        return [
            'contract_name',
            'contract_number',
            'customer.name',
        ];
    }

    protected function getCustomOrder(): array
    {
        return [1, 'desc'];
    }


    /*
    |============================================================================
    |                            Private Helper Methods
    |============================================================================
    */
    // private function statusBadge($row): string
    // {
    //     return sprintf(
    //         '<span class="badge bg-%s">%s</span>',
    //         $row->payments_state->color(),
    //         $row->payments_state->label()
    //     );
    // }
}
