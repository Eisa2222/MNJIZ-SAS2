<?php

namespace App\DataTables\OperationsCenter\ExceptionalContract;

use App\Models\OperationsCenter\ExceptionalContract\ExceptionalContract;
use Illuminate\Database\Eloquent\Builder;
use Yajra\DataTables\Services\DataTable;

class ExceptionalContractDataTable extends DataTable
{
    /*
    |============================================================================
    | Query
    |============================================================================
    */
    public function query(ExceptionalContract $model): Builder
    {
        $q = $model->select([
            'id',
            'contract_name',
            'customer_id',
            'employee_id',
            'created_by_id',
            'status',
        ])->with(['customer', 'creator', 'employee']);


        $req = $this->request();           // نفس الـ Request القادم من الـ Controller
        $q->when($req->status,   fn($q, $v) => $q->where('status',   $v));
        $q->when($req->customer, fn($q, $v) => $q->where('customer_id',            $v));
        $q->when($req->employee, fn($q, $v) => $q->where('employee_id', $v));

        return $q;
    }

    /*
    |============================================================================
    | Columns
    |============================================================================
    */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()

            ->addColumn('checkbox',         fn($row) => $this->checkbox($row))
            ->addColumn('contract_name',     fn($row) => $this->link($row))
            ->addColumn('status',           fn($row) => $this->statusBadge($row))
            ->addColumn('action',           fn($row) => view('operations_center.exceptional_contract.action', compact('row'))->render())

            ->editColumn('customer_id',         fn($row) => optional($row->customer)->name          ?? 'غير متوفر')
            ->editColumn('employee_id',         fn($row) => optional($row->employee)->name          ?? 'غير متوفر')
            ->editColumn('created_by',       fn($row) => optional($row->creator)->name           ?? 'غير متوفر')


            ->rawColumns(['checkbox','contract_name', 'status', 'action']);
    }

    /*
    |============================================================================
    |============================================================================
    |                        private function
    |============================================================================
    |============================================================================
    */
    private function checkbox($row): string
    {
        return '<input type="checkbox" class="row-checkbox" value="' . $row->id . '">';
    }

    private function link($row): string
    {
        return '<a href="' . route('operations-center.exceptional-contracts.show', $row->id) . '">' . e($row->contract_name) . '</a>';
    }

    private function statusBadge($row): string
    {
        $badge = $row->status_badge;
        return "<span title='{$row->status_tooltip}' class=\"badge bg-{$badge['bg']}\">{$row->status_name}</span>";
    }
}
