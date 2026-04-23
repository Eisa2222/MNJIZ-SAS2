<?php

namespace App\Http\Controllers\Qoyod\Customers;

use App\DataTables\Qoyod\Customers\QCustomersDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Qoyod\Customers\QCustomersRequest;
use Illuminate\Http\Request;
use App\Services\Qoyod\Contracts\Resources\CustomerResourceInterface;
use App\Services\Qoyod\Exceptions\QoyodRequestException;
use Illuminate\Support\Facades\Log;

class QCustomersController extends Controller
{
    /**
     * ============================================================================ 
     *   @param  CustomerResourceInterface  $customers
     * ============================================================================
     */
    public function __construct(private CustomerResourceInterface $customers)
    {
        $this->middleware('can:صلاحيات منصة قيود');
    }

    /*
    |============================================================================
    | index
    |============================================================================
    */
    public function index(QCustomersDataTable $dataTable)
    {
        return $dataTable->render('qoyod.customers.index');
    }

    /*
    |============================================================================
    | create
    |============================================================================
    */
    public function create()
    {
        return view('qoyod.customers.create');
    }



    /*
    |============================================================================
    | store
    |============================================================================
    */
    public function store(QCustomersRequest $request)
    {
        //Validation
        $data = $request->validated();

        try {
            $this->customers->create($data);
            return redirect()->route('qoyod.customers.index')->with('success', 'تم إضافة العميل بنجاح');
        } catch (QoyodRequestException $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'api_error' => 'فشل في إضافة العميل الرجاء المحاولة مرة أخرى. التفاصيل: ' . $e->getMessage()
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
            $resp       = $this->vendors->find($id);
            $vendor    = $resp['contact'] ?? null;
            if ($vendor == null) {
                abort(404);
            }
        } catch (\Exception $e) {
            abort(404, 'المنتج غير موجود');
        }

        return view(
            'qoyod.vendors.show',
            compact(
                'vendor',
            )
        );
    }

    /*
    |============================================================================
    | edit
    |============================================================================
    */
    public function edit(string $id)
    {
        try {
            $resp   = $this->customers->find($id);
            $customer = $resp['contact'] ?? null;


            return view('qoyod.customers.edit', compact('customer'));
        } catch (QoyodRequestException $e) {
            return redirect()
                ->route('qoyod.customers.index')
                ->withErrors(['api_error' => $e->getMessage()]);
        }
    }



    /*
    |============================================================================
    | update
    |============================================================================
    */
    public function update(QCustomersRequest $request, string $id)
    {
        //Validation
        $data = $request->validated();

        try {
            $this->customers->update($id, $data);
            return redirect()->route('qoyod.customers.index')->with('success', 'تم تحديث بيانات العميل بنجاح');
        } catch (QoyodRequestException $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'api_error' => 'فشل في تحديث بيانات العميل: ' . $e->getMessage()
                ]);
        }
    }


    /*
    |============================================================================
    |============================================================================
    |                             other method
    |============================================================================
    |============================================================================
    */
    public function getCustomers()
    {
        $allCustomers = $this->customers->all();
        $customers = $allCustomers['customers'] ?? null;
        return $customers;
    }
}
