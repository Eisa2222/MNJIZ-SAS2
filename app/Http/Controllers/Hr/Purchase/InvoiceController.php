<?php

namespace App\Http\Controllers\Hr\Purchase;

use App\Http\Controllers\Controller;
use App\Models\ElectronicServices\PurchaseRequests\PurchaseRequest;
use App\Models\general_setting\SettingsPurchaseCategory;
use App\Models\Hr\Purchase\Invoice;
use App\Models\Hr\Purchase\Purchase;
use App\Models\GeneralSetting\SystemSetting\Settings;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | constructor
    |--------------------------------------------------------------------------
    */
    public function __construct()
    {
        $this->middleware('can:المشتريات')->only(['index', 'show']);
        $this->middleware('can:إضافة مشتريات')->only(['createByItem', 'storeByItem']);
        $this->middleware('can:إضافة مشتريات')->only(['create', 'store']);
        $this->middleware('can:حذف مشتريات')->only(['destroy']);
        $this->middleware('can:تعديل مشتريات')->only(['edit', 'update']);
    }


    /*
    |--------------------------------------------------------------------------
    | index
    |--------------------------------------------------------------------------
    */
    /**
     * عرض قائمة المشتريات مع الفلاتر
     */
    public function index(Request $request)
    {
        $baseQuery = Invoice::select([
            'id',
            'invoice_number',
            'invoice_date',
            'total_amount',
            'user_id'
        ]);

        try {
            if ($request->ajax()) {
                $invoice = clone $baseQuery;

                // تطبيق فلتر الفترة الزمنية
                if ($request->filled('date_range')) {
                    $now = now();

                    switch ($request->date_range) {
                        case 'today':
                            $invoice->whereDate('invoice_date', $now->toDateString());
                            break;
                        case 'week':
                            $invoice->whereBetween('invoice_date', [
                                $now->startOfWeek()->toDateString(),
                                $now->endOfWeek()->toDateString()
                            ]);
                            break;
                        case 'month':
                            $invoice->whereMonth('invoice_date', $now->month)
                                ->whereYear('invoice_date', $now->year);
                            break;
                        case 'quarter':
                            $invoice->whereBetween('invoice_date', [
                                $now->startOfQuarter()->toDateString(),
                                $now->endOfQuarter()->toDateString()
                            ]);
                            break;
                        case 'year':
                            $invoice->whereYear('invoice_date', $now->year);
                            break;
                        case 'custom':
                            if ($request->filled('date_from')) {
                                $invoice->whereDate('invoice_date', '>=', $request->date_from);
                            }
                            if ($request->filled('date_to')) {
                                $invoice->whereDate('invoice_date', '<=', $request->date_to);
                            }
                            break;
                    }
                }

                // فلتر المستخدم
                if ($request->filled('user_id')) {
                    $invoice->where('user_id', $request->user_id);
                }

                // فلتر المبلغ
                if ($request->filled('amount_operator') && $request->filled('amount_value')) {
                    switch ($request->amount_operator) {
                        case 'greater_than':
                            $invoice->where('total_amount', '>', $request->amount_value);
                            break;
                        case 'less_than':
                            $invoice->where('total_amount', '<', $request->amount_value);
                            break;
                        case 'between':
                            if ($request->filled('amount_value2')) {
                                $invoice->whereBetween('total_amount', [
                                    min($request->amount_value, $request->amount_value2),
                                    max($request->amount_value, $request->amount_value2)
                                ]);
                            }
                            break;
                    }
                }

                // فلتر فئة المشتريات
                if ($request->filled('category_id')) {
                    $invoice->whereHas('purchases', function ($query) use ($request) {
                        $query->where('purchase_category_id', $request->category_id);
                    });
                }

                return datatables()->of($invoice)
                    ->addIndexColumn()
                    ->addColumn('checkbox', function ($row) {
                        return '<input type="checkbox" class="row-checkbox" value="' . $row->id . '">';
                    })

                    ->editColumn('invoice_number', function ($row) {
                        if (auth()->user()->can('المشتريات'))
                            return '<a href="' . route('purchasing-center.purchase-requests.invoices.show', $row->id) . '" class="text-primary">' . e($row->invoice_number) . '</a>';
                        else
                            $row->invoice_number;
                    })

                    ->editColumn('total_amount', function ($row) {
                        return number_format($row->total_amount, 2) . ' ريال';
                    })

                    ->editColumn('invoice_date', function ($row) {
                        return $row->invoice_date;
                    })

                    ->editColumn('user_id', function ($row) {
                        if ($row->user) {
                            return '<a href="' . route('account.employee.profile', $row->user->employee->id) . '" class="text-primary">' . e($row->user->employee->name) . '</a>';
                        }
                        return '<span class="text-muted">غير محدد</span>';
                    })

                    ->addColumn('action', function ($row) {
                        $editUrl = route('purchasing-center.purchase-requests.invoices.edit', $row->id);
                        $deleteUrl = route('purchasing-center.purchase-requests.invoices.destroy', $row->id);
                        $formId = 'delete-form-' . $row->id;
                        $csrfField = csrf_field();
                        $methodField = method_field('DELETE');

                        $buttons = '<div class="d-flex gap-2">';

                        if (auth()->user()->can('تعديل مشتريات')) {
                            $buttons .= '<a href="' . $editUrl . '" class="btn btn-sm text-secondary" title="تعديل">
                                        <i class="ti ti-edit"></i>
                                     </a>';
                        }

                        if (auth()->user()->can('حذف مشتريات')) {
                            $buttons .= '<a href="javascript:void(0);" onclick="confirmDelete(' . $row->id . ')" class="btn btn-sm text-secondary" title="حذف">
                                        <i class="ti ti-trash"></i>
                                     </a>
                                     <form id="' . $formId . '" action="' . $deleteUrl . '" method="POST" style="display: none;">
                                        ' . $csrfField . '
                                        ' . $methodField . '
                                     </form>';
                        }

                        $buttons .= '</div>';

                        return $buttons;
                    })
                    ->rawColumns(['checkbox', 'action', 'user_id', 'invoice_number'])
                    ->make(true);
            }

            // جلب المستخدمين للفلتر - جلب المستخدمين الذين لديهم فواتير فقط
            $users = DB::table('users')
                ->join('invoices', 'users.id', '=', 'invoices.user_id')
                ->select('users.id', 'users.name')
                ->distinct()
                ->get();

            // إحصائيات للصفحة الرئيسية
            $totalInvoices = Invoice::count();

            // إجمالي قيمة المشتريات
            $totalAmount = Invoice::sum('total_amount');

            // مشتريات الشهر الحالي
            $monthlyAmount = Invoice::whereMonth('invoice_date', now()->month)
                ->whereYear('invoice_date', now()->year)
                ->sum('total_amount');



            // أكثر فئة مشتريات
            $topCategory = DB::table('purchases')
                ->join('settings_purchase_categories', 'purchases.purchase_category_id', '=', 'settings_purchase_categories.id')
                ->select('settings_purchase_categories.name', DB::raw('COUNT(*) as count'))
                ->groupBy('settings_purchase_categories.name')
                ->orderBy('count', 'desc')
                ->first();

            $topCategoryName = $topCategory ? $topCategory->name : 'لا يوجد';

            $purchase_category = SettingsPurchaseCategory::select('id','name')->get();


            return view('purchasing_center.invoices.index', compact(
                'totalInvoices',
                'totalAmount',
                'monthlyAmount',
                'topCategoryName',
                'users',
                'purchase_category'
            ));
        } catch (\Exception $e) {

            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات: ' . $e->getMessage());
        }
    }



    /*
    |--------------------------------------------------------------------------
    | create
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $next_number = $this->generateInvoiceNumber();
        $setting = Settings::current();
        $purchase_category = SettingsPurchaseCategory::select(['id', 'name'])->get();
        return view('purchasing_center.invoices.create', compact('setting', 'purchase_category', 'next_number'));
    }




    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        // التحقق من صحة البيانات
        $request->validate([
            'invoice_items.*.item_name'             => 'required|string',
            'invoice_items.*.purchase_category_id'  => 'required|integer',
            'invoice_items.*.item_price'            => 'required|numeric',
            'invoice_items.*.item_quantity'         => 'required|integer',
            'invoice_items.*.item_description'      => 'nullable|string',
            'attachment'                            => 'nullable|file|max:10240', // إضافة تحقق من المرفق
        ]);

        DB::beginTransaction();
        try {
            // معالجة المرفق إذا وجد
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('attachments', 'public');
            }

            // إنشاء سجل الفاتورة باستخدام التاريخ الصحيح
            $invoice = Invoice::create([
                'invoice_number' => $this->generateInvoiceNumber(), // دالة لتوليد رقم فاتورة فريد
                'invoice_date'   => now()->toDateString(),
                'total_amount'   => $this->calculateTotal($request->invoice_items), // دالة لحساب الإجمالي بناءً على عناصر الفاتورة
                'user_id'        => auth()->user()->id,
                'attachment'     => $attachmentPath,

            ]);

            // إنشاء عناصر المشتريات وربطها بالفاتورة
            foreach ($request->invoice_items as $item) {
                Purchase::create([
                    'invoice_id'            => $invoice->id,
                    'item_name'             => $item['item_name'],
                    'purchase_category_id'  => $item['purchase_category_id'],
                    'item_price'            => $item['item_price'],
                    'item_quantity'         => $item['item_quantity'],
                    'item_description'      => $item['item_description'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('purchasing-center.purchase-requests.invoices.index')->with('success', 'تم حفظ الفاتورة بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('خطأ أثناء حفظ الفاتورة: ' . $e->getMessage());
            return redirect()->back()->with('error', 'حدث خطأ أثناء حفظ الفاتورة.');
        }
    }





    /*
    |--------------------------------------------------------------------------
    | to generate invoice number
    |--------------------------------------------------------------------------
    */
    protected function generateInvoiceNumber()
    {
        // جلب آخر فاتورة بما في ذلك المحذوفات
        $lastInvoice = Invoice::withTrashed()->orderBy('invoice_number', 'desc')->first();

        $number = $lastInvoice ? $lastInvoice->invoice_number + 1 : 1;

        return str_pad($number, 4, '0', STR_PAD_LEFT);
    }



    /*
    |--------------------------------------------------------------------------
    | calculate total
    |--------------------------------------------------------------------------
    */
    protected function calculateTotal($invoiceItems)
    {
        $total = 0;
        foreach ($invoiceItems as $item) {
            $total += $item['item_price'] * $item['item_quantity'];
        }
        return $total;
    }



    /*
    |--------------------------------------------------------------------------
    | show
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        $invoice = Invoice::with('purchases')->findOrFail($id);

        $setting = Settings::current();

        return view('purchasing_center.invoices.show', compact('invoice', 'setting'));
    }




    /*
    |--------------------------------------------------------------------------
    | edit
    |--------------------------------------------------------------------------
    */
    public function edit($id)
    {
        $setting = Settings::current();

        $purchase_category = SettingsPurchaseCategory::select(['id', 'name'])->get();

        $invoice = Invoice::with('purchases')->findOrFail($id);

        return view('purchasing_center.invoices.edit', compact('setting', 'purchase_category', 'invoice'));
    }



    /*
    |--------------------------------------------------------------------------
    | update
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        // التحقق من صحة البيانات المُرسلة
        $request->validate([
            'invoice_items.*.item_name'             => 'required|string',
            'invoice_items.*.purchase_category_id'  => 'required|integer',
            'invoice_items.*.item_price'            => 'required|numeric',
            'invoice_items.*.item_quantity'         => 'required|integer',
            'invoice_items.*.item_description'      => 'nullable|string',
            'attachment'                            => 'nullable|file|max:10240', // إضافة تحقق من المرفق
        ]);

        DB::beginTransaction();
        try {
            // جلب الفاتورة المراد تعديلها
            $invoice = Invoice::findOrFail($id);

            // معالجة المرفق - الحفاظ على المرفق القديم كقيمة افتراضية
            $attachmentPath = $invoice->attachment;

            if ($request->hasFile('attachment') && $request->file('attachment')->isValid()) {
                // حذف المرفق القديم إذا كان موجوداً
                if ($invoice->attachment) {
                    Storage::disk('public')->delete($invoice->attachment);
                }

                // تخزين المرفق الجديد
                $attachmentPath = $request->file('attachment')->store('attachments', 'public');

                // تسجيل عملية تحميل المرفق في سجل التطبيق للتأكد من نجاح العملية
                Log::info('تم تحميل مرفق جديد للفاتورة #' . $invoice->invoice_number . ': ' . $attachmentPath);
            } else if ($request->has('remove_attachment') && $request->remove_attachment == 1) {
                // حذف المرفق القديم بدون إضافة مرفق جديد
                if ($invoice->attachment) {
                    Storage::disk('public')->delete($invoice->attachment);
                }
                $attachmentPath = null;
            }

            // تأكد من أن القيمة المحدثة للمرفق ستكون صحيحة
            $updateData = [
                'invoice_date'  => now()->toDateString(),
                'total_amount'  => $this->calculateTotal($request->invoice_items),
                'attachment'    => $attachmentPath,
            ];

            // تسجيل بيانات التحديث قبل إجراء التحديث
            Log::info('بيانات تحديث الفاتورة:', $updateData);

            // تحديث بيانات الفاتورة
            $invoice->update($updateData);

            // إعادة إنشاء عناصر المشتريات:
            // أولاً، حذف العناصر القديمة
            Purchase::where('invoice_id', $invoice->id)->delete();

            // ثم إنشاء العناصر الجديدة
            foreach ($request->invoice_items as $item) {
                Purchase::create([
                    'invoice_id'            => $invoice->id,
                    'item_name'             => $item['item_name'],
                    'purchase_category_id'  => $item['purchase_category_id'],
                    'item_price'            => $item['item_price'],
                    'item_quantity'         => $item['item_quantity'],
                    'item_description'      => $item['item_description'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('purchasing-center.purchase-requests.invoices.index')->with('success', 'تم تعديل الفاتورة بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('خطأ أثناء تعديل الفاتورة: ' . $e->getMessage());
            return redirect()->back()->with('error', 'حدث خطأ أثناء تعديل الفاتورة: ' . $e->getMessage());
        }
    }




    /*
    |--------------------------------------------------------------------------
    | destroy
    |--------------------------------------------------------------------------
    */
    public function destroy(Invoice $invoice)
    {
        DB::beginTransaction();
        try {
            // حذف عناصر المشتريات المرتبطة بالفاتورة
            Purchase::where('invoice_id', $invoice->id)->delete();

            // حذف الفاتورة
            $invoice->delete();

            DB::commit();
            return redirect()->route('purchasing-center.purchase-requests.invoices.index')->with('success', 'تم حذف الفاتورة بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('خطأ أثناء حذف الفاتورة: ' . $e->getMessage());
            return redirect()->back()->with('error', 'حدث خطأ أثناء حذف الفاتورة.');
        }
    }




    /*
    |--------------------------------------------------------------------------
    | Pdf
    |--------------------------------------------------------------------------
    */
    public function downloadPdf($id)
    {
        try {
            // جلب الفاتورة والإعدادات
            $invoice = Invoice::with('purchases', 'user.employee')->findOrFail($id);
            $setting = Settings::current();

            // إعداد متغيرات للصور والشعار
            $headerPath = $setting->horizontal_header_image ?
                storage_path('app/public/' . $setting->horizontal_header_image) : null;
            $footerPath = $setting->horizontal_footer_image ?
                storage_path('app/public/' . $setting->horizontal_footer_image) : null;


            // إعداد تكوين الخطوط
            $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
            $fontDirs = $defaultConfig['fontDir'];

            $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
            $fontData = $defaultFontConfig['fontdata'];

            // تكوين mPDF
            $config = [
                'mode' => 'utf-8',
                'format' => 'A4',
                'default_font' => 'cairo',
                'margin_left' => 0,
                'margin_right' => 0,
                'margin_top' => 30,
                'margin_bottom' => 35,
                'margin_header' => 0,
                'margin_footer' => 0,
                'orientation' => 'P',
                'fontDir' => array_merge($fontDirs, [
                    public_path('fonts/Cairo'),
                ]),
                'fontdata' => array_merge($fontData, [
                    'cairo' => [
                        'R' => 'Cairo-Regular.ttf',
                        'B' => 'Cairo-Bold.ttf',
                        'useOTL' => 0xFF,
                        'useKashida' => 75,
                    ]
                ]),
                'default_font_size' => 12,
                'tempDir' => storage_path('app/public/temp')
            ];

            // إنشاء كائن mPDF
            $mpdf = new \Mpdf\Mpdf($config);
            $mpdf->SetDirectionality('rtl');

            // تعريف ورقة الأنماط CSS
            $stylesheet = '
                body {
                    font-family: cairo;
                    font-size: 12px;
                    line-height: 1.5;
                    direction: rtl;
                }
                .page-header {
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    height: 60px;
                }
                .footer-container {
                    width: 100%;
                    padding: 0;
                    margin: 0;
                    position: fixed;
                    bottom: 0;
                }
                .footer-container img {
                    width: 100%;
                    height: 80px;
                    object-fit: cover;
                }
                .content {
                    padding: 0;
                    margin-left: 40px !important;
                    margin-right: 40px !important;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 20px;
                }
                table th, table td {
                    padding: 10px;
                    border: 1px solid #ddd;
                    text-align: right;
                }
                table th {
                    background-color: #f8f9fa;
                    font-weight: bold;
                }
                .total-row td {
                    font-weight: bold;
                }
                .text-center {
                    text-align: center;
                }
                .reference-number {
                    font-size: 14px;
                    font-weight: bold;
                }
                ';


            $mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);

            // إعداد الترويسة إذا كانت موجودة
            if ($headerPath && file_exists($headerPath)) {
                $headerContent = '
            <div style="padding-left:15px;">
                <table width="100%" style="padding: 25px 20px 35px 15px; border: none;">
                    <tr>
                        <td style="text-align: right; vertical-align: middle; border: none;">
                            <img src="' . $headerPath . '" style="height: 60px; max-width: 250px;" />
                        </td>
                        <td style="text-align: left; vertical-align: middle; border: none;">
                            <div class="reference-number">
                                رقم الفاتورة (' . $invoice->invoice_number . ')
                            </div>
                        </td>
                    </tr>
                </table>
            </div>';
                $mpdf->SetHTMLHeader($headerContent);
            }

            // إعداد التذييل إذا كان موجوداً
            if ($footerPath && file_exists($footerPath)) {
                $footerContent = '
            <div class="footer-container">
                <img src="' . $footerPath . '" />
            </div>';
                $mpdf->SetHTMLFooter($footerContent);
            }

            // إنشاء محتوى الفاتورة
            $content = '
        <div class="content" style="position: relative; min-height: 600px;">
            <div style="text-align: center; margin-bottom: 20px;">
                <h2>فاتورة مشتريات</h2>
                <p>تاريخ الإصدار: ' . $invoice->invoice_date . '</p>
            </div>

            <table>
                <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th width="30%">الاسم</th>
                        <th width="20%">التصنيف</th>
                        <th width="15%">سعر الوحدة</th>
                        <th width="10%">الكمية</th>
                        <th width="20%">الإجمالي</th>
                    </tr>
                </thead>
                <tbody>';

            foreach ($invoice->purchases as $index => $purchase) {
                $content .= '
                    <tr>
                        <td>' . ($index + 1) . '</td>
                        <td>' . $purchase->item_name . '</td>
                        <td>' . $purchase->category->name . '</td>
                        <td>' . number_format($purchase->item_price, 2) . '</td>
                        <td>' . $purchase->item_quantity . '</td>
                        <td>' . number_format($purchase->item_price * $purchase->item_quantity, 2) . '</td>
                    </tr>';
            }

            $content .= '
                    <tr class="total-row">
                        <td colspan="4"></td>
                        <td>الإجمالي:</td>
                        <td>' . number_format($invoice->total_amount, 2) . '</td>
                    </tr>
                </tbody>
            </table>

            <div style="margin-top: 50px; text-align: left; direction: rtl;">
                <p>صادرة بواسطة: ' . $invoice->user->name . '</p>
            </div>
        </div>';


            $mpdf->WriteHTML($content);

            // إرجاع الملف للتنزيل
            return response()->streamDownload(
                function () use ($mpdf) {
                    echo $mpdf->Output('', 'S');
                },
                'فاتورة_' . $invoice->invoice_number . '.pdf',
                ['Content-Type' => 'application/pdf']
            );
        } catch (\Exception $e) {
            // تسجيل الخطأ
            return redirect()->back()->with('error', 'حدث خطأ أثناء تحميل الفاتورة: ' . $e->getMessage());
        }
    }




    /*
    |--------------------------------------------------------------------------
    | create by item
    |--------------------------------------------------------------------------
    */
    public function createByItem($id)
    {
        $next_number = $this->generateInvoiceNumber();
        $setting = Settings::current();
        $purchase_category = SettingsPurchaseCategory::select(['id', 'name'])->get();

        $purchase_request = PurchaseRequest::find($id);

        return view('purchasing_center.invoices.create_by_item', compact('setting', 'purchase_category', 'next_number', 'purchase_request'));
    }


    /*
    |--------------------------------------------------------------------------
    | Store by item
    |--------------------------------------------------------------------------
    */
    public function storeByItem(Request $request, $id)
    {
        // التحقق من صحة البيانات
        $request->validate([
            'invoice_items.*.item_name'             => 'required|string',
            'invoice_items.*.purchase_category_id'  => 'required|integer',
            'invoice_items.*.item_price'            => 'required|numeric',
            'invoice_items.*.item_quantity'         => 'required|integer',
            'invoice_items.*.item_description'      => 'nullable|string',
            'attachment'                            => 'nullable|file|max:10240', // إضافة تحقق من المرفق
        ]);
        $purchase_request = PurchaseRequest::find($id);

        DB::beginTransaction();
        try {
            // معالجة المرفق إذا وجد
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('attachments', 'public');
            }


            // إنشاء سجل الفاتورة باستخدام التاريخ الصحيح
            $invoice = Invoice::create([
                'invoice_number' => $this->generateInvoiceNumber(), // دالة لتوليد رقم فاتورة فريد
                'invoice_date'   => now()->toDateString(),
                'total_amount'   => $this->calculateTotal($request->invoice_items), // دالة لحساب الإجمالي بناءً على عناصر الفاتورة
                'user_id'        => auth()->user()->id,
                'attachment'     => $attachmentPath

            ]);

            // إنشاء عناصر المشتريات وربطها بالفاتورة
            foreach ($request->invoice_items as $item) {
                Purchase::create([
                    'invoice_id'            => $invoice->id,
                    'item_name'             => $item['item_name'],
                    'purchase_category_id'  => $item['purchase_category_id'],
                    'item_price'            => $item['item_price'],
                    'item_quantity'         => $item['item_quantity'],
                    'item_description'      => $item['item_description'] ?? null,
                ]);
            }

            $purchase_request->status = 'approved';
            $purchase_request->processed_at = Carbon::now();
            $purchase_request->save();
            DB::commit();

            if (auth()->user()->can('المشتريات'))
                return redirect()->route('purchasing-center.purchase-requests.invoices.index')->with('success', 'تم حفظ الفاتورة بنجاح.');
            else
                return redirect()->route('purchasing-center.purchase-requests.index')->with('success', 'تم حفظ الفاتورة بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('خطأ أثناء حفظ الفاتورة: ' . $e->getMessage());
            return redirect()->back()->with('error', 'حدث خطأ أثناء حفظ الفاتورة.');
        }
    }
}
