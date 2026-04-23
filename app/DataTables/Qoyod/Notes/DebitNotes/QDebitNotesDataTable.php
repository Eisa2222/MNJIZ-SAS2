<?php

namespace App\DataTables\Qoyod\Notes\DebitNotes;

use App\DataTables\Qoyod\AbstractResourceDataTable;
use App\Services\Qoyod\Contracts\Resources\DebitNoteResourceInterface;
use App\Services\Qoyod\Presenters\Notes\DebitNotes\DebitNotePresenter;
use Illuminate\Support\Collection;
use Yajra\DataTables\Services\DataTable;


class QDebitNotesDataTable extends AbstractResourceDataTable
{
    public function __construct(private DebitNoteResourceInterface $debit_notes,private DebitNotePresenter $debitNotePresenter) {}


    /*
    |============================================================================
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    |============================================================================
    */
    protected function resource(): DebitNoteResourceInterface
    {
        return $this->debit_notes;
    }

    protected function resourceKey(): string
    {
        return 'debit_notes';
    }

    protected function tableId(): string
    {
        return 'debit-notes-table';
    }

    protected function getColumns(): array
    {
        return [
            // ID
            ['data' => 'DT_RowIndex', 'title' => 'م', 'orderable' => false, 'searchable' => false, 'width' => '10px'],

            ['data' => 'reference',         'name' => 'reference',          'title' => 'المرجع',                 'className' => 'table-ellipsis'],
            ['data' => 'contact_name',      'name' => 'contact_name',       'title' => 'المورد',                 'className' => 'table-ellipsis'],
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
            $this->debitNotePresenter->getDebitNoteWithVendorName($rows->toArray())
        );

        return $presented->map(fn($row) => [
            ...$row,
            'reference'  => $this->routeName(route('qoyod.debit-notes.show', $row['id']), $row['reference']),
            'status'     => $this->getStatus($row['status']),
            'actions'      => view('qoyod.notes.debit_notes.actions', compact('row'))->render(),
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
                'text'      => '<i class="fas fa-plus-circle me-1"></i> إضافة إشعار مدين',
                'className' => 'btn btn-primary btn-add',
                'action'    => "function(){ window.location.href='" . route('qoyod.debit-notes.create') . "'; }",
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
