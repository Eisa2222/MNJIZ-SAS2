<?php

namespace App\Http\Controllers\Qoyod\Inventories;

use App\DataTables\Qoyod\Inventories\QInventoriesDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Qoyod\Inventories\QInventoriesRequest;
use Illuminate\Http\Request;
use App\Services\Qoyod\Contracts\Resources\InventoryResourceInterface;
use App\Services\Qoyod\Exceptions\QoyodRequestException;
use App\Services\Qoyod\Presenters\Accounts\AccountsPresenter;

class QInventoriesController extends Controller
{
    /**
     * ============================================================================ 
     *   @param  InventoryResourceInterface  $inventory
     * ============================================================================
     */
    private AccountsPresenter    $accountPresenter;    // الحسابات

    public function __construct(private InventoryResourceInterface $inventory, AccountsPresenter $accountPresenter)
    {
        $this->middleware('can:صلاحيات منصة قيود');
        $this->accountPresenter = $accountPresenter;
    }

    /*
    |============================================================================
    | index
    |============================================================================
    */
    public function index(QInventoriesDataTable $dataTable)
    {
        return $dataTable->render('qoyod.inventories.index');
    }


    /*
    |============================================================================
    | create
    |============================================================================
    */
    public function create()
    {
        // حساب المخزون
        $inventoryAccount = $this->accountPresenter->getFilterAccounts('group_type', 'Inventory');

        return view('qoyod.inventories.create', compact('inventoryAccount'));
    }


    /*
    |============================================================================
    | store
    |============================================================================
    */
    public function store(QInventoriesRequest $request)
    {
        //Validation
        $data = $request->getFormattedData();

        try {
            $this->inventory->create($data);
            return redirect()->route('qoyod.inventories.index')->with('success', 'تم إضافة الموقع  بنجاح');
        } catch (QoyodRequestException $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'api_error' => 'فشل في إضافة الموقع الرجاء المحاولة مرة أخرى. التفاصيل: ' . $e->getMessage()
                ]);
        }
    }



    /*
    |============================================================================
    | edit
    |============================================================================
    */
    public function edit(string $id)
    {
        try {
            $inventory = $this->inventory->find($id);

            // حساب المخزون
            $inventoryAccount = $this->accountPresenter->getFilterAccounts('group_type', 'Inventory');

            return view('qoyod.inventories.edit', compact('inventory', 'inventoryAccount'));
        } catch (QoyodRequestException $e) {
            return redirect()
                ->route('qoyod.inventories.index')
                ->withErrors(['api_error' => $e->getMessage()]);
        }
    }


    /*
    |============================================================================
    | update
    |============================================================================
    */
    public function update(QInventoriesRequest $request, string $id)
    {
        //Validation
        $data = $request->getFormattedData();

        try {
            $this->inventory->update($id, $data);
            return redirect()->route('qoyod.inventories.index')->with('success', 'تم تحديث بيانات الموقع بنجاح');
        } catch (QoyodRequestException $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'api_error' => 'فشل في تحديث بيانات الموقع: ' . $e->getMessage()
                ]);
        }
    }
}
