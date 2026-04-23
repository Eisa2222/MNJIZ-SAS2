<?php

namespace App\DataTables\Qoyod\Notes\CreditNotes;

use App\DataTables\Qoyod\AbstractResourceDataTable;
use App\Services\Qoyod\Contracts\Resources\CreditNoteResourceInterface;
use App\Services\Qoyod\Presenters\Notes\CreditNotes\CreditNotePresenter;
use Illuminate\Support\Collection;


class QCreditNotesDataTable extends AbstractResourceDataTable
{
    public function __construct(private CreditNoteResourceInterface $credit_notes, private CreditNotePresenter $creditNotePresenter) {}


    /*
    |============================================================================
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    |============================================================================
    */
    protected function resource(): CreditNoteResourceInterface
    {
        return $this->credit_notes;
    }

    protected function resourceKey(): string
    {
        return 'credit_notes';
    }

    protected function tableId(): string
    {
        return 'credit-notes-table';
    }

    protected function getColumns(): array
    {
        return [
            // ID
            ['data' => 'DT_RowIndex', 'title' => 'م', 'orderable' => false, 'searchable' => false, 'width' => '10px'],

            ['data' => 'reference',         'name' => 'reference',          'title' => 'المرجع',                 'className' => 'table-ellipsis'],
            ['data' => 'contact_name',      'name' => 'contact_name',       'title' => 'العميل',                 'className' => 'table-ellipsis'],
            ['data' => 'issue_date',        'name' => 'issue_date',         'title' => 'تاريخ الاصدار',           'className' => 'table-ellipsis'],
            ['data' => 'total_amount',      'name' => 'total_amount',       'title' => 'القيمة الإجمالية',       'className' => 'table-ellipsis'],
            ['data' => 'remaining_amount',  'name' => 'remaining_amount',   'title' => 'الرصيد',                 'className' => 'table-ellipsis'],
            ['data' => 'status',            'name' => 'status',             'title' => 'الحالة',                 'className' => 'table-ellipsis'],

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
        return ['reference', 'contact_name', 'issue_date', 'total_amount', 'remaining_amount', 'status'];
    }

    protected function formatRows(Collection $rows): Collection
    {
        $presented = collect(
            $this->creditNotePresenter->getCreditNoteWithCustomerName($rows->toArray())
        );

        return $presented->map(fn($row) => [
            ...$row,
            'reference'  => $this->routeName(route('qoyod.credit-notes.show', $row['id']), $row['reference']),
            'status'     => $this->getStatus($row['status']),
            'actions'      => view('qoyod.notes.credit_notes.actions', compact('row'))->render(),
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
                'text'      => '<i class="fas fa-plus-circle me-1"></i> إضافة إشعار دائن',
                'className' => 'btn btn-primary btn-add',
                'action'    => "function(){ window.location.href='" . route('qoyod.credit-notes.create') . "'; }",
            ],
        ];
    }


    private function getStatus($status): string
    {
        return match ($status) {
            'Draft'     => '<span class="badge bg-secondary">مسودة</span>',
            'Used'      => '<span class="badge bg-success">مستعمل</span>',
            'Unused'    => '<span class="badge bg-info">غير مستعمل</span>',
            default     => $status,
        };
    }
}
