<?php

namespace App\Http\Controllers\Qoyod\Accounts;

use App\DataTables\Qoyod\Accounts\QAccountsDataTable;
use App\Enums\Qoyod\Accounts\AccountType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Qoyod\Accounts\QAccountsRequest;
use Illuminate\Http\Request;
use App\Services\Qoyod\Contracts\Resources\AccountResourceInterface;
use App\Services\Qoyod\Exceptions\QoyodRequestException;

class QAccountsController extends Controller
{
    /**
     * ============================================================================ 
     *   @param  AccountResourceInterface  $accounts
     * ============================================================================
     */
    public function __construct(private AccountResourceInterface $accounts) 
    {
        $this->middleware('can:صلاحيات منصة قيود');
    }

    /*
    |============================================================================
    | index
    |============================================================================
    */
    public function index(QAccountsDataTable $dataTable)
    {
        return $dataTable->render('qoyod.accounts.index');
    }



    /*
    |============================================================================
    | create
    |============================================================================
    */
    public function create()
    {
        $accountTypeRaw   = AccountType::options();
        return view('qoyod.accounts.create', compact('accountTypeRaw'));
    }



    /*
    |============================================================================
    | store
    |============================================================================
    */
    public function store(QAccountsRequest $request)
    {
        //Validation
        $data = $request->validated();

        try {
            $this->accounts->create($data);
            return redirect()->route('qoyod.accounts.index')->with('success', 'تم إضافة الحساب بنجاح');
        } catch (QoyodRequestException $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'api_error' => 'فشل في إضافة الحساب الرجاء المحاولة مرة أخرى. التفاصيل: ' . $e->getMessage()
                ]);
        }
    }



    /*
    |============================================================================
    | show
    |============================================================================
    */
    public function show($id)
    {
        try {
            $resp       = $this->accounts->find($id);
            $account    = $resp['account'] ?? null;
            if ($account == null) {
                abort(404);
            }
        } catch (\Exception $e) {
            abort(404, 'المنتج غير موجود');
        }
        $account['type_label'] = AccountType::from($account['type'])->label();

        return view('qoyod.accounts.show', compact('account'));
    }
}
