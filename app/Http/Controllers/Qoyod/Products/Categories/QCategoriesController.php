<?php

namespace App\Http\Controllers\Qoyod\Products\Categories;

use App\DataTables\Qoyod\Products\Categories\QCategoriesDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Qoyod\Products\Categories\QCategoriesRequest;
use App\Services\Qoyod\Contracts\Resources\CategoryResourceInterface;
use App\Services\Qoyod\Exceptions\QoyodRequestException;

class QCategoriesController extends Controller
{
    /**
     * ============================================================================ 
     *   @param  CategoryResourceInterface  $categories
     * ============================================================================
     */
    public function __construct(private CategoryResourceInterface $categories)
    {
        $this->middleware('can:صلاحيات منصة قيود');
    }


    /*
    |============================================================================
    | index
    |============================================================================
    */
    public function index(QCategoriesDataTable $dataTable)
    {
        return $dataTable->render('qoyod.products.categories.index');
    }


    /*
    |============================================================================
    | create
    |============================================================================
    */
    public function create()
    {
        $basic_category =  $this->categories->all();
        return view('qoyod.products.categories.create', compact('basic_category'));
    }


    /*
    |============================================================================
    | store
    |============================================================================
    */
    public function store(QCategoriesRequest $request)
    {
        //Validation
        $data = $request->validated();

        try {
            $this->categories->create($data);
            return redirect()->route('qoyod.categories.index')->with('success', 'تم إضافة تصنيف المنتجات  بنجاح');
        } catch (QoyodRequestException $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'api_error' => 'فشل في إضافة تصنيف المنتجات الرجاء المحاولة مرة أخرى. التفاصيل: ' . $e->getMessage()
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
            ['category' => $category] = $this->categories->find($id);

            $basic_category =  $this->categories->all();

            return view('qoyod.products.categories.edit', compact('category', 'basic_category'));
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
    public function update(QCategoriesRequest $request, string $id)
    {
        //Validation
        $data = $request->validated();

        try {
            $this->categories->update($id, $data);
            return redirect()->route('qoyod.categories.index')
                ->with('success', 'تم تحديث بيانات تصنيف المنتجات');
        } catch (QoyodRequestException $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'api_error' => 'فشل في تحديث بيانات تصنيف المنتجات: ' . $e->getMessage()
                ]);
        }
    }
}
