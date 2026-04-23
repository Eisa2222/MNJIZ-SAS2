<?php

namespace App\Http\Controllers\Qoyod\JournalEntries;

use App\DataTables\Qoyod\JournalEntries\QJournalEntriesDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Qoyod\JournalEntries\QJournalEntriesRequest;
use App\Services\Qoyod\Contracts\Resources\InventoryResourceInterface;
use App\Services\Qoyod\Contracts\Resources\JournalEntrieResourceInterface;
use App\Services\Qoyod\Exceptions\QoyodRequestException;
use App\Services\Qoyod\Presenters\Accounts\AccountsPresenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QJournalEntriesController extends Controller
{
    /**
     * ============================================================================ 
     *   @param  JournalEntrieResourceInterface  $journal_entries
     * ============================================================================
     */
    public function __construct(
        private JournalEntrieResourceInterface  $journal_entries,
        private InventoryResourceInterface      $inventories,
        private AccountsPresenter               $accountPresenter   // الحسابات

    ) {$this->middleware('can:صلاحيات منصة قيود');}

    /*
    |============================================================================
    | index
    |============================================================================
    */
    public function index(QJournalEntriesDataTable $dataTable)
    {
        return $dataTable->render('qoyod.journal_entries.index');
    }


    /*
    |============================================================================
    | create
    |============================================================================
    */
    public function create()
    {
        $accounts = $this->accountPresenter->getFilterAccounts('parent_type', 'Asset');

        // الموقع
        $inventoriesResponse    = $this->inventories->all();
        $inventories            = $inventoriesResponse['inventories'];

        return view('qoyod.journal_entries.create', compact('inventories', 'accounts'));
    }



    /*
    |============================================================================
    | store
    |============================================================================
    */
    public function store(QJournalEntriesRequest $request)
    {
        try {
            $validatedData = $request->validated();

            // تحويل البيانات للصيغة المطلوبة للـ API
            $apiData = $this->transformFormDataToApiFormat($validatedData);

            try {
                $this->journal_entries->create($apiData);

                return redirect()->route('qoyod.journal-entries.index')->with('success', 'تم إضافة قيد اليومية بنجاح');
            } catch (QoyodRequestException $e) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'api_error' => 'فشل في إضافة قيد اليومية. الرجاء المحاولة مرة أخرى. التفاصيل: ' . $e->getMessage()
                    ]);
            }
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'general_error' => 'حدث خطأ غير متوقع. الرجاء المحاولة مرة أخرى.'
                ]);
        }
    }



    /*
    |============================================================================
    |============================================================================
    |                           Private methods
    |============================================================================
    |============================================================================
    */
    private function transformFormDataToApiFormat($formData)
    {
        // جمع البيانات الأساسية
        $description = $formData['description'] ?? '';
        $date = $formData['date'];
        $inventory_id = $formData['inventory_id'];

        $debitAmounts = [];
        $creditAmounts = [];

        // معالجة السطور
        if (isset($formData['journal_entry']['lines'])) {
            foreach ($formData['journal_entry']['lines'] as $line) {
                $accountId = (int) $line['account_id'];
                $debitValue = (float) ($line['debit'] ?? 0);
                $creditValue = (float) ($line['credit'] ?? 0);
                $comment = $line['comment'] ?? '';

                // إضافة للمدين إذا كان هناك قيمة
                if ($debitValue > 0) {
                    $debitAmounts[] = [
                        'account_id' => $accountId,
                        'amount' => $debitValue,
                        'comment' => $comment
                    ];
                }

                // إضافة للدائن إذا كان هناك قيمة
                if ($creditValue > 0) {
                    $creditAmounts[] = [
                        'account_id' => $accountId,
                        'amount' => $creditValue,
                        'comment' => $comment
                    ];
                }
            }
        }

        return [
            'journal_entry' => [
                'description' => $description,
                'date' => $date,
                'inventory_id' => (int) $inventory_id,
                'debit_amounts' => $debitAmounts,
                'credit_amounts' => $creditAmounts
            ]
        ];
    }
}
