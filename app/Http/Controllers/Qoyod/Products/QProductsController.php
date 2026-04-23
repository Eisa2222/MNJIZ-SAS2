<?php

namespace App\Http\Controllers\Qoyod\Products;

use App\DataTables\Qoyod\Products\QProductsDataTable;
use App\Enums\Qoyod\Products\ExemptTaxReason;
use App\Enums\Qoyod\Products\ProductType;
use App\Enums\Qoyod\Products\SpecialTaxReason;
use App\Enums\Qoyod\Products\TaxType;
use App\Enums\Qoyod\Products\ZeroTaxReason;
use App\Http\Controllers\Controller;
use App\Http\Requests\Qoyod\Products\QProductsStoreRequest;
use App\Http\Requests\Qoyod\Products\QProductsUpdateRequest;
use App\Services\Qoyod\Contracts\Resources\AccountResourceInterface;
use App\Services\Qoyod\Contracts\Resources\CategoryResourceInterface;
use Illuminate\Http\Request;
use App\Services\Qoyod\Contracts\Resources\ProductResourceInterface;
use App\Services\Qoyod\Contracts\Resources\ProductUnitResourceInterface;
use App\Services\Qoyod\Exceptions\QoyodRequestException;
use App\Services\Qoyod\Presenters\Accounts\AccountsPresenter;

class QProductsController extends Controller
{
    /**
     * ============================================================================ 
     *   @param  ProductResourceInterface  $products
     * ============================================================================
     */
    protected   CategoryResourceInterface         $categories;          // تصنيفات المنتجات
    private     AccountsPresenter                 $accountPresenter;    // الحسابات
    protected   AccountResourceInterface          $accounts;            // الحسابات
    protected   ProductUnitResourceInterface      $units;               // وحدات المنتجات
    protected   ProductResourceInterface          $products;            // المنتجات للاضافة و العرض و التعديل 


    public function __construct(
        CategoryResourceInterface       $categories,
        ProductUnitResourceInterface    $units,
        AccountsPresenter               $accountPresenter,
        AccountResourceInterface        $accounts,
        ProductResourceInterface        $products,

    ) {
        $this->middleware('can:صلاحيات منصة قيود');
        $this->categories       = $categories;
        $this->units            = $units;
        $this->accountPresenter = $accountPresenter;
        $this->accounts         = $accounts;
        $this->products         = $products;
    }


    /*
    |============================================================================
    | index
    |============================================================================
    */
    public function index(QProductsDataTable $dataTable)
    {
        return $dataTable->render('qoyod.products.index');
    }

    /*
    |============================================================================
    | create
    |============================================================================
    */
    public function create()
    {
        $productType = ProductType::getSelectableTypes();

        // اصناف المنتجات
        $respCats    = $this->categories->all();
        $categories  = $respCats['categories'];

        // وحدات القياس
        $respUnits   = $this->units->all();
        $units       = $respUnits['product_unit_types'];

        // حساب المصروفات
        $expenseAccount = $this->accountPresenter->getFilterAccounts('parent_type', 'Expense');

        // حساب المبيعات
        $salesAccount = $this->accountPresenter->getFilterAccounts('parent_type', 'Revenue');

        // الضرائب و اسباب الاعفاء
        $taxes           = TaxType::options();
        $zeroReasons     = ZeroTaxReason::options();
        $exemptReasons   = ExemptTaxReason::options();



        return view('qoyod.products.create', compact(
            'productType',
            'categories',
            'units',
            'expenseAccount',
            'salesAccount',
            'taxes',
            'zeroReasons',
            'exemptReasons'
        ));
    }


    /*
    |============================================================================
    | store
    |============================================================================
    */
    public function store(QProductsStoreRequest $request)
    {
        //Validation
        $data = $request->validated();

        try {
            $this->products->create($data);
            return redirect()->route('qoyod.products.index')->with('success', 'تم إضافة المنتج  بنجاح');
        } catch (QoyodRequestException $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'api_error' => 'فشل في إضافة المنتج الرجاء المحاولة مرة أخرى. التفاصيل: ' . $e->getMessage()
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
            $resp       = $this->products->find($id);
            $product    = $resp['product'][0] ?? null;
            if ($product == null) {
                abort(404);
            }
        } catch (\Exception $e) {
            abort(404, 'المنتج غير موجود');
        }


        $productTypeRaw   = ProductType::options();


        $productTypeMap   = array_column($productTypeRaw,   'name', 'id');

        $productType = '-';
        if (! empty($product['type'])) {
            $reasonId = $product['type'];
            $productType = $productTypeMap[$reasonId];
        }


        $categoryName = '-';
        if (! empty($product['category_id'])) {
            try {
                $catResp     = $this->categories->find($product['category_id']);
                $categoryName = data_get($catResp, 'category.name', '-');
            } catch (\Exception $e) {
                $categoryName = '-';
            }
        }

        $unitName = '-';
        if (! empty($product['unit_type'])) {
            try {
                $unitResp = $this->units->find($product['unit_type']);
                $unitName = data_get($unitResp, 'product_unit_type.unit_name', '-');
            } catch (\Exception $e) {
                $unitName = '-';
            }
        }

        $salesAccountName = '-';
        if (! empty($product['sales_account_id'])) {
            try {
                $accResp          = $this->accounts->find($product['sales_account_id']);
                $salesAccountName = data_get($accResp, 'account.name_ar', '-');
            } catch (\Exception $e) {
                $salesAccountName = '-';
            }
        }

        $expenseAccountName = '-';
        if (! empty($product['expense_account_id'])) {
            try {
                $accResp            = $this->accounts->find($product['expense_account_id']);
                $expenseAccountName = data_get($accResp, 'account.name_ar', '-');
            } catch (\Exception $e) {
                $expenseAccountName = '-';
            }
        }

        $taxTypeRaw   = TaxType::options();


        $taxTypeMap   = array_column($taxTypeRaw,   'name', 'id');

        $taxType = '-';
        if (! empty($product['tax_id'])) {
            $reasonId = $product['tax_id'];
            $taxType = $taxTypeMap[$reasonId];
        }


        $zeroReasonsRaw   = ZeroTaxReason::options();
        $exemptReasonsRaw = ExemptTaxReason::options();


        $zeroReasonsMap   = array_column($zeroReasonsRaw,   'name', 'id');
        $exemptReasonsMap = array_column($exemptReasonsRaw, 'name', 'id');

        // --- نحصل على الاسم المناسب لـ special_tax_reason_id ---
        $specialTaxReasonName = '-';
        if (! empty($product['special_tax_reason_id'])) {
            $reasonId = $product['special_tax_reason_id'];
            // إذا كانت الضريبة Zero (tax_id = 2)
            if (($product['tax_id'] ?? null) == 2 && isset($zeroReasonsMap[$reasonId])) {
                $specialTaxReasonName = $zeroReasonsMap[$reasonId];
            }
            // إذا كانت الضريبة Exempt (tax_id = 3)
            elseif (($product['tax_id'] ?? null) == 3 && isset($exemptReasonsMap[$reasonId])) {
                $specialTaxReasonName = $exemptReasonsMap[$reasonId];
            }
        }



        // 4) أخيرًا نمرّر كل شيء للـ View
        return view(
            'qoyod.products.show',
            compact(
                'product',
                'productType',
                'categoryName',
                'unitName',
                'salesAccountName',
                'expenseAccountName',
                'taxType',

                'specialTaxReasonName'
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
            $resp = $this->products->find($id);
            $product = $resp['product'][0] ?? null;

            if ($product == null) {
                abort(404);
            }
            // اصناف المنتجات
            $respCats    = $this->categories->all();
            $categories  = $respCats['categories'];

            // وحدات القياس
            $respUnits   = $this->units->all();
            $units       = $respUnits['product_unit_types'];

            // حساب المصروفات
            $expenseAccount = $this->accountPresenter->getFilterAccounts('parent_type', 'Expense');

            // حساب المبيعات
            $salesAccount = $this->accountPresenter->getFilterAccounts('parent_type', 'Revenue');

            // الضرائب و اسباب الاعفاء
            $taxes           = TaxType::options();
            $zeroReasons     = ZeroTaxReason::options();
            $exemptReasons   = ExemptTaxReason::options();

            return view('qoyod.products.edit', compact(
                'product',
                'categories',
                'units',
                'expenseAccount',
                'salesAccount',
                'taxes',
                'zeroReasons',
                'exemptReasons'
            ));
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
    public function update(QProductsUpdateRequest $request, string $id)
    {
        //Validation
        $data = $request->validated();

        try {
            $this->products->update($id, $data);
            return redirect()->route('qoyod.products.index')->with('success', 'تم تحديث بيانات المنتج بنجاح');
        } catch (QoyodRequestException $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'api_error' => 'فشل في تحديث بيانات المنتج: ' . $e->getMessage()
                ]);
        }
    }
}
