<?php

namespace App\DataTables\Qoyod\JournalEntries;

use App\DataTables\Qoyod\AbstractResourceDataTable;
use App\Services\Qoyod\Contracts\Resources\JournalEntrieResourceInterface;
use App\Services\Qoyod\Presenters\JournalEntries\JournalEntriesPresenter;
use Illuminate\Support\Collection;


class QJournalEntriesDataTable extends AbstractResourceDataTable
{
    public function __construct(private JournalEntrieResourceInterface $journal_entries) {}

    /*
    |============================================================================
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    |============================================================================
    */
    protected function resource(): JournalEntrieResourceInterface
    {
        return $this->journal_entries;
    }

    protected function resourceKey(): string
    {
        return 'journal_entries';
    }

    protected function tableId(): string
    {
        return 'journal-entries-table';
    }

    protected function getColumns(): array
    {
        return [
            // ID
            ['data' => 'DT_RowIndex', 'title' => 'م', 'orderable' => false, 'searchable' => false, 'width' => '10px'],

            ['data' => 'description',    'name' => 'description',          'title' => 'الوصف',                 'className' => 'table-ellipsis'],
            ['data' => 'date',           'name' => 'date',                 'title' => 'التاريخ',                 'className' => 'table-ellipsis'],
            ['data' => 'total_debit',    'name' => 'total_debit',          'title' => 'إجمالي المدين',                 'className' => 'table-ellipsis'],
            ['data' => 'total_credit',   'name' => 'total_credit',         'title' => 'إجمالي الدائن',                 'className' => 'table-ellipsis'],

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
        return ['first_entry_id', 'description', 'date'];
    }


    
    protected function formatRows(Collection $rows): Collection
    {
        return $rows->map(fn($row) => [
            ...$row,
            'actions'           => view('qoyod.journal_entries.actions', compact('row'))->render(),
        ]);
    }


    protected function rawColumns(): array
    {
        return ['actions'];
    }


    protected function getButtons(): array
    {
        return [
            [
                'text'      => '<i class="fas fa-plus-circle me-1"></i> إضافة قيد يومية ',
                'className' => 'btn btn-primary btn-add',
                'action'    => "function(){ window.location.href='" . route('qoyod.journal-entries.create') . "'; }",
            ],
        ];
    }
}
