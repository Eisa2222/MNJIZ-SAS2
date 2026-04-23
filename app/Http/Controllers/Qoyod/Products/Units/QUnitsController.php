<?php

namespace App\Http\Controllers\Qoyod\Products\Units;

use App\DataTables\Qoyod\Products\Units\QUnitsDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Qoyod\Products\Units\QUnitsRequest;
use App\Services\Qoyod\Contracts\Resources\ProductUnitResourceInterface;
use App\Services\Qoyod\Exceptions\QoyodRequestException;

class QUnitsController extends Controller
{
    /**
     * ============================================================================ 
     *   @param  ProductUnitResourceInterface  $units
     * ============================================================================
     */
    public function __construct(private ProductUnitResourceInterface $units)
    {
        $this->middleware('can:صلاحيات منصة قيود');
    }


    /*
    |============================================================================
    | index
    |============================================================================
    */
    public function index(QUnitsDataTable $dataTable)
    {
        return $dataTable->render('qoyod.products.units.index');
    }


    /*
    |============================================================================
    | create
    |============================================================================
    */
    public function create()
    {
        return view('qoyod.products.units.create');
    }


    /*
    |============================================================================
    | store
    |============================================================================
    */
    public function store(QUnitsRequest $request)
    {
        //Validation
        $data = $request->validated();

        try {
            $this->units->create($data);
            return redirect()->route('qoyod.product-unit-types.index')->with('success', 'تم إضافة وحدة القياس بنجاح');
        } catch (QoyodRequestException $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'api_error' => 'فشل في إضافة وحدة القياس الرجاء المحاولة مرة أخرى. التفاصيل: ' . $e->getMessage()
                ]);
        }
    }
}
