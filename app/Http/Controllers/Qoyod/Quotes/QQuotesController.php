<?php

namespace App\Http\Controllers\Qoyod\Quotes;

use App\DataTables\Qoyod\Quotes\QQuotesDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Qoyod\Quotes\QQuotesRequest;
use App\Services\Qoyod\Contracts\Resources\CustomerResourceInterface;
use App\Services\Qoyod\Contracts\Resources\InventoryResourceInterface;
use App\Services\Qoyod\Contracts\Resources\QuoteResourceInterface;
use App\Services\Qoyod\Exceptions\QoyodRequestException;
use App\Services\Qoyod\Presenters\Products\ProductPresenter;
use App\Services\Qoyod\Presenters\Quotes\QuotePresenter;

class QQuotesController extends Controller
{
    /**
     * ============================================================================ 
     *   @param  QuoteResourceInterface  $quotes
     * ============================================================================
     */
    public function __construct(
        private QuoteResourceInterface          $quotes,
        private ProductPresenter                $productPresenter,
        private InventoryResourceInterface      $inventories,
        private CustomerResourceInterface       $customers,
        private QuotePresenter                  $quotePresenter
    ) {
        $this->middleware('can:صلاحيات منصة قيود');
    }

    /*
    |============================================================================
    | index
    |============================================================================
    */
    public function index(QQuotesDataTable $dataTable)
    {
        return $dataTable->render('qoyod.quotes.index');
    }


    /*
    |============================================================================
    | create
    |============================================================================
    */
    public function create()
    {
        // المنتجات
        $products    = $this->productPresenter->getFilterProducts('type', 'Service'); //المنتجات 

        // العملاء
        $customersResponse        = $this->customers->all();
        $customers                = $customersResponse['customers'];

        // الموقع
        $inventoriesResponse    = $this->inventories->all();
        $inventories            = $inventoriesResponse['inventories'];

        return view('qoyod.quotes.create', compact('products', 'customers', 'inventories'));
    }


    /*
    |============================================================================
    | store
    |============================================================================
    */
    public function store(QQuotesRequest $request)
    {
        //Validation
        $data = $request->validated();

        $data['line_items'] = array_values($data['line_items']);

        // dd($data);
        try {
            $this->quotes->create($data);
            return redirect()->route('qoyod.quotes.index')->with('success', 'تم إضافة العرض السعري  بنجاح');
        } catch (QoyodRequestException $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'api_error' => 'فشل في إضافة العرض السعري الرجاء المحاولة مرة أخرى. التفاصيل: ' . $e->getMessage()
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
            $resp = $this->quotes->find($id);
            $allQuotes = $this->quotePresenter->getQuotesWithRelatedNames($resp);
            $quotes = $allQuotes['quote'] ?? null;

            if (! $quotes) {
                abort(404);
            }

            $lineItems = $quotes['line_items'] ?? [];

            $subtotal       = 0; // الإجمالي قبل الضريبة
            $taxAmount      = 0; // مجموع قيم الضريبة لكل بند
            $totalDiscount  = 0; // مجموع قيم الخصم لكل بند

            foreach ($lineItems as $item) {
                $quantity   = $item['quantity']             ?? 0;
                $unitPrice  = $item['unit_price']           ?? 0;
                $discount   = $item['discount_amount']      ?? 0;
                $vatValue   = $item['unrounded_vat_value']  ?? 0;
                $lineTotal  = $item['total']                ?? 0;

                $lineSubtotal = ($quantity * $unitPrice) - $discount;

                // الاجمالي قبل الضريبة
                $subtotal    += $lineSubtotal;

                // اجمالي الضريبة
                $taxAmount   += $vatValue;

                // اجمالي الخصم
                $totalDiscount += $discount;
            }

            $quotes['subtotal']      = $subtotal;
            $quotes['tax_amount']    = $taxAmount;
            $quotes['total_discount'] = $totalDiscount;
        } catch (\Exception $e) {
            abort(404);
        }

        return view('qoyod.quotes.show', compact('quotes'));
    }
}
