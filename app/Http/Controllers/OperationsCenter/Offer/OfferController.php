<?php

namespace App\Http\Controllers\OperationsCenter\Offer;

use Alkoumi\LaravelHijriDate\Hijri;
use App\Data\OperationsCenter\Offer\OfferData;
use App\Data\OperationsCenter\Offer\OfferUpdateData;
use App\DataTables\OperationsCenter\Offer\OffersDataTable;
use App\Enums\OperationsCenter\Offer\OfferStatus;
use App\Helpers\SettingsHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsCenter\Offers\OfferRequest;
use App\Models\OperationsCenter\Customer\Customers;
use App\Models\general_setting\SettingsTemplate;
use App\Models\Hr\Employees\Employees;
use App\Models\OperationsCenter\Offer\Offers;
use App\Models\GeneralSetting\SystemSetting\Settings;
use App\Models\task\Task;
use App\Services\Common\PdfExportService;
use App\Services\OperationsCenter\Offer\OfferService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OfferController extends Controller
{

    public function __construct(private OfferService $offerService, private PdfExportService $pdfExport)
    {
        $this->middleware(function ($request, $next) {
            if ($request->user()->can('كل العروض') || $request->user()->can('العروض الخاصة بي')) {
                return $next($request);
            }
            abort(403);
        })->only(['index', 'show']);

        $this->middleware('can:إضافة عرض')->only(['create', 'store']);
        $this->middleware('can:تعديل عرض')->only(['edit', 'update']);
        $this->middleware('can:حذف عرض')->only(['destroy']);
    }


    public function index(OffersDataTable $dataTable)
    {
        $query = Offers::query();
        try {

            if (auth()->user()->can('العروض الخاصة بي') && !auth()->user()->can('كل العروض')) {
                $query->where('created_by', auth()->user()->employee->id);
            }
            // Statistics
            $statusCounts = $query
                ->select('status')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $totalOffers = array_sum($statusCounts);
            $pendingOffers  = $statusCounts[OfferStatus::PENDING->value] ?? 0;
            $approvedOffers = $statusCounts[OfferStatus::APPROVED->value] ?? 0;
            $rejectedOffers = $statusCounts[OfferStatus::REJECTED->value] ?? 0;

            // Filters
            $offer_status               = OfferStatus::options();
            $customers                  = Customers::select('id', 'name')->get();
            $employees                  = Employees::active()->select('id', 'name', 'nickname')->get();
            return $dataTable->render('operations_center.offers.index', compact(
                // Statistics
                'totalOffers',
                'pendingOffers',
                'approvedOffers',
                'rejectedOffers',
                // Filters
                'offer_status',
                'customers',
                'employees',
            ));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    }


    public function create()
    {
        if (Customers::count() == 0) {
            return redirect()->route('operations-center.offers.index')->with('warning', 'عذراً، لا يمكن إضافة عرض جديد حالياً لعدم وجود عملاء.');
        }

        $customers = Customers::select(['id', 'name'])->get();

        return view('operations_center.offers.create', compact('customers'));
    }


    public function store(OfferRequest $request)
    {
        try {

            $dto = new OfferData($request->validated());

            $this->offerService->createOffer($dto);

            return redirect()->route('operations-center.offers.index')->with('success', 'تم إضافة العرض بنجاح');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'حدث خطأ أثناء إضافة العرض. يرجى المحاولة مرة أخرى.');
        }
    }


    public function show(string $id)
    {
        $offer = Offers::with([
            'customer',
            'relationshipManager',
            'approvalRequest.requestLevels.employee'
        ])->findOrFail($id);

        $approvalStages = $offer->approvalRequest ? $offer->approvalRequest->getApprovalStages() : [];

        $isAutoApproved = empty($approvalStages) && (
            ($offer->status instanceof OfferStatus
                && $offer->status === OfferStatus::APPROVED)
            || (is_string($offer->status) && $offer->status === 'approved')
        );

        $templateHtml = SettingsTemplate::where('template_type', 'offers')->value('content') ?? '';
        $data = $this->getData($offer);
        $processedContent = $templateHtml;

        foreach ($data as $key => $value) {
            $processedContent = str_replace('{{' . $key . '}}', $value, $processedContent);
        }


        return view('operations_center.offers.show', compact(
            'offer',
            'processedContent',
            'approvalStages',
            'isAutoApproved'
        ));
    }


    public function edit(Request $request, string $id)
    {
        $offer = Offers::findOrFail($id);

        $customers = Customers::select(['id', 'name'])->get();

        return view('operations_center.offers.edit', compact(
            'customers',
            'offer'
        ));
    }


    public function update(OfferRequest $request, Offers $offer)
    {
        try {
            $dto = new OfferUpdateData($request->validated());

            $this->offerService->updateOffer($offer, $dto);

            return redirect()->route('operations-center.offers.index')->with('success', 'تم تحديث العرض بنجاح!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'حدث خطأ أثناء تحديث العرض. يرجى المحاولة مرة أخرى.');
        }
    }


    public function destroy(string $id)
    {
        $offer = Offers::findOrFail($id);

        $hasContracts = $offer->contracts()->exists();

        if ($hasContracts) {
            return redirect()->route('operations-center.offers.index')->with('error', 'لا يمكن حذف العرض لارتباطه بسجلات أخرى.');
        }
        $offer->delete();

        return redirect()->route('operations-center.offers.index')->with('success', 'تم حذف العرض بنجاح!');
    }

    /*
    |============================================================================
    |============================================================================
    |                           anothor functions
    |============================================================================
    |============================================================================
    */
    public function exportPdfOfficial(Offers $offer)
    {
        return $this->exportPdf($offer);
    }

    public function exportPdfSimple(Offers $offer)
    {
        return $this->exportPdf($offer, false);
    }

    // export_pdf
    public function exportPdf(Offers $offer, $templateImage = true)
    {
        try {
            $signature              = false;
            $is_private_and_secret  = false;

            $template = SettingsTemplate::where('template_type',  'offers')->value('content');

            $data = $this->getData($offer);

            if ($offer->status === OfferStatus::APPROVED) {
                $signature = true;
            }

            if ($offer->is_private_and_secret) {
                $is_private_and_secret = true;
            }

            foreach ($data as $key => $value) {
                $template = str_replace('{{' . $key . '}}', $value, $template);
            }

            return $this->pdfExport->exportHtml(
                $template,
                $offer->offer_name . '.pdf',
                $templateImage,
                $signature,
                $is_private_and_secret
            );
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء التصدير يرجى المحاولة لاحقاً.');
        }
    }

    // trashed
    public function trashed(Request $request)
    {
        try {
            if ($request->ajax()) {
                $offers = Offers::onlyTrashed()->with(['customer', 'relationshipManager', 'stagePriceOffer'])
                    ->select([
                        'offers.id as id',
                        'offer_name',
                        'stage_price_offer_id',
                        'customer_id',
                        'relationship_manager_id',
                        'start_date',
                        'offers.deleted_at as deleted_at',
                    ])->orderBy('id', 'desc');

                return datatables()->of($offers)
                    ->addIndexColumn()
                    ->addColumn('customer_id', function ($row) {
                        return $row->customer ? $row->customer->name : 'غير متوفر';
                    })
                    ->addColumn('relationship_manager_id', function ($row) {
                        return $row->relationshipManager ? $row->relationshipManager->name : 'غير متوفر';
                    })
                    ->addColumn('stage_price_offer_id', function ($row) {
                        return $row->stagePriceOffer ? $row->stagePriceOffer->name : 'غير متوفر';
                    })
                    ->addColumn('start_date', function ($row) {
                        return $row->hijri_start_date;
                    })
                    ->editColumn('deleted_at', function ($row) {
                        return $row->deleted_at ? Hijri::ShortDate($row->deleted_at) : '';
                    })
                    ->addColumn('action', function ($row) {
                        $restoreUrl = route('operations-center.offers.restore', $row->id);
                        $forceDeleteUrl = route('operations-center.offers.forceDelete', $row->id);
                        return '
                            <a href="javascript:void(0);" onclick="confirmRestore(' . $row->id . ')" class="btn btn-sm text-success"><i
                                    class="ti ti-rotate"></i> استعادة</a>
                            <a href="javascript:void(0);" onclick="confirmForceDelete(' . $row->id . ')" class="btn btn-sm text-danger"><i
                                    class="ti ti-trash"></i> حذف نهائي</a>
                            <form id="restore-form-' . $row->id . '" action="' . $restoreUrl . '" method="POST" style="display: none;">
                                ' . csrf_field() . '
                                ' . method_field('PUT') . '
                            </form>
                            <form id="force-delete-form-' . $row->id . '" action="' . $forceDeleteUrl . '" method="POST" style="display: none;">
                                ' . csrf_field() . '
                                ' . method_field('DELETE') . '
                            </form>
                            ';
                    })
                    ->rawColumns(['action'])
                    ->make(true);
            }
            return view('operations_center.offers.trashed');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    }

    // restore
    public function restore($id)
    {
        try {
            $offer = Offers::onlyTrashed()->findOrFail($id);
            // استعادة العرض
            $offer->restore();

            return redirect()->route('operations-center.offers.trashed')->with('success', 'تم استعادة العرض بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء استعادة العرض. يرجى المحاولة لاحقاً.');
        }
    }

    // forceDelete
    public function forceDelete($id)
    {
        try {
            $offer = Offers::onlyTrashed()->findOrFail($id);
            // حذف العرض نهائيًا
            $tasks = Task::where('offer_id', $offer->id)->get();

            $this->deleteTask($tasks, $offer);

            return redirect()->route('operations-center.offers.trashed')->with('success', 'تم حذف العرض نهائياً بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء حذف العرض نهائياً. يرجى المحاولة لاحقاً.');
        }
    }

    /*
    |============================================================================
    |============================================================================
    |                          Praivat functions
    |============================================================================
    |============================================================================
    */
    private function getData(Offers $offer)
    {
        return [
            'offer_number'      => $offer->offer_number,
            'offer_name'        => $offer->offer_name,
            'customer_name'     => $offer->customer->name,
            'start_date'        => $offer->start_date,
            'technical_offer'   => $offer->technical_offer,
            'financial_offer'   => $offer->financial_offer,
            'current_date'      => Carbon::now()->toDateString(),

        ];
    }





    /*
    |============================================================================
    |============================================================================
    |                            Founction for API
    |============================================================================
    |============================================================================
    */
    //  لعرض مسؤول العلاقة بعد اختيار العميل.
    public function relationship_manager($id)
    {
        $customer = Customers::find($id);

        if ($customer && $customer->relationshipManager) {
            return response()->json([
                'id'    => $customer->relationshipManager->id,
                'name'  => $customer->relationshipManager->name,
            ]);
        }

        return response()->json(null, 404);
    }
}
