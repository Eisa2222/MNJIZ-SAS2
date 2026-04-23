<?php

namespace App\Http\Controllers\OperationsCenter\Contract;

use Alkoumi\LaravelHijriDate\Hijri;
use App\Data\OperationsCenter\Contract\ContractData;
use App\DataTables\OperationsCenter\Contract\ContractDataTable;
use App\Enums\OperationsCenter\Contract\ContractStatus;
use App\Enums\OperationsCenter\Contract\Payment\CalculationType;
use App\Enums\OperationsCenter\Contract\Payment\PaymentBatchType;
use App\Helpers\SettingsHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsCenter\Contract\ContractRequest;
use App\Jobs\DeleteFileFromOneDriveJob;
use App\Models\OperationsCenter\Customer\Customers;
use App\Models\general_setting\SettingsContractStatus;
use App\Models\general_setting\SettingsTemplate;
use App\Models\GeneralSetting\SystemSetting\Settings;
use App\Models\Hr\Employees\Employees;
use App\Models\OperationsCenter\Contract\Contract;
use App\Models\OperationsCenter\Offer\Offers;
use App\Models\task\Task;
use App\Services\Common\PdfExportService;
use App\Services\FilesService;
use App\Services\MicrosoftGraphBaseService;
use App\Services\OperationsCenter\Contract\ContractService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ContractController extends Controller
{

    public function __construct(private ContractService $contractService, private PdfExportService $pdfExport)
    {
        $this->middleware(function ($request, $next) {
            if ($request->user()->can('كل العقود') || $request->user()->can('العقود الخاصة بي')) {
                return $next($request);
            }
            abort(403);
        })->only(['index', 'show']);

        $this->middleware('can:إضافة عقد')->only(['create', 'store']);
        $this->middleware('can:تعديل عقد')->only(['edit', 'update']);
        $this->middleware('can:حذف عقد')->only(['destroy']);
    }


    public function index(ContractDataTable $dataTable)
    {
        $query = Contract::query();
        try {
            if (auth()->user()->can('العقود الخاصة بي') && !auth()->user()->can('كل العقود')) {
                $query->where('created_by', auth()->user()->employee->id);
            }

            // Statistics
            $statusCounts = $query
                ->select('status')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $totalContracts = array_sum($statusCounts);
            $pendingContracts  = $statusCounts[ContractStatus::PENDING->value] ?? 0;
            $approvedContracts = $statusCounts[ContractStatus::APPROVED->value] ?? 0;
            $rejectedContracts = $statusCounts[ContractStatus::REJECTED->value] ?? 0;

            // Filters
            $contractStatus             = ContractStatus::options();
            $customers                  = Customers::select('id', 'name')->get();
            $employees                  = Employees::active()->select('id', 'name', 'nickname')->get();
            return $dataTable->render('operations_center.contracts.index', compact(
                // Statistics
                'totalContracts',
                'pendingContracts',
                'approvedContracts',
                'rejectedContracts',
                // Filters
                'contractStatus',
                'customers',
                'employees',
            ));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    }


    public function create()
    {
        $employees                  = Employees::active()->select('id', 'name', 'nickname')->get();
        $offers                     = Offers::where('status', 'approved')->whereDoesntHave('contracts')->select(['id', 'offer_name'])->orderByDesc('created_at')->get();
        $settings_contract_status   = SettingsContractStatus::where('status', 'active')->select(['id', 'name'])->orderByDesc('created_at')->get();
        $mainContracts              = Contract::where('contract_type', 'main')->orderByDesc('created_at')->get();

        $calculationTypes           = CalculationType::options();
        $paymentBatchTypes          = PaymentBatchType::options();


        return view('operations_center.contracts.create', compact(
            'employees',
            'offers',
            'settings_contract_status',
            'mainContracts',
            'calculationTypes',
            'paymentBatchTypes'
        ));
    }


    public function store(ContractRequest $request)
    {
        try {
            DB::beginTransaction();

            $dto = new ContractData($request->validated());

            $this->contractService->createContract($dto);

            DB::commit();

            return redirect()->route('operations-center.contracts.index')->with('success', 'تم إضافة العقد بنجاح!');
        } catch (ValidationException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', 'حدث خطأ أثناء إضافة العقد. يرجى المحاولة مرة أخرى.');
        }
    }


    public function show(string $id)
    {
        $contract = Contract::with([
            'customer.region',
            'mainContract.customer.region',
            'offer',
            'approvalRequest.requestLevels.employee',
        ])->findOrFail($id);

        //-- Approvel --//
        $approvalStages = $contract->approvalRequest ? $contract->approvalRequest->getApprovalStages() : [];
        $isAutoApproved = empty($approvalStages) && ($contract->status === ContractStatus::APPROVED);
        //-- Approvel --//
        if ($contract->contract_type == "main") {
            $templateHtml = SettingsTemplate::where('template_type', 'contracts')->value('content') ?? '';
        } else {
            $templateHtml = SettingsTemplate::where('template_type', 'supplementary_contract')->value('content') ?? '';
        }

        $data = $this->getData($contract);
        $processedContent = $templateHtml;

        foreach ($data as $key => $value) {
            $processedContent = str_replace('{{' . $key . '}}', $value, $processedContent);
        }


        return view('operations_center.contracts.show', compact(
            'contract',
            'processedContent',
            'approvalStages',
            'isAutoApproved'
        ));
    }


    public function edit(Request $request, Contract $contract)
    {
        $offers = Offers::where('status', 'approved')->where(function ($query) use ($contract) {
            $query->whereDoesntHave('contracts') // العروض غير المرتبطة بعقود
                ->orWhere('id', $contract->offer_id); // العرض المرتبط بالعقد الحالي
        })->select(['id', 'offer_name'])->orderByDesc('created_at')->get();

        $employees                  = Employees::active()->select('id', 'name', 'nickname')->get();
        $settings_contract_status   = SettingsContractStatus::where('status', 'active')->select(['id', 'name'])->orderByDesc('created_at')->get();
        $mainContracts              = Contract::where('contract_type', 'main')
            ->where('id', '!=', $contract->id) // استثناء العقد الحالي إذا كان رئيسياً
            ->select(['id', 'contract_name', 'contract_number'])
            ->orderByDesc('created_at')->get();


        $calculationTypes   = CalculationType::options();
        $paymentBatchTypes   = PaymentBatchType::options();

        return view('operations_center.contracts.edit', compact(
            'employees',
            'offers',
            'settings_contract_status',
            'contract',
            'mainContracts',
            'calculationTypes',
            'paymentBatchTypes'
        ));
    }


    public function update(ContractRequest $request, $id)
    {
        try {
            $validated = $request->validated();
            $dto = new ContractData($validated);

            $this->contractService->updateContract($id, $dto);

            return redirect()->route('operations-center.contracts.index')->with('success', 'تم تحديث العقد بنجاح!');
        } catch (ValidationException $e) {
            return redirect()->route('operations-center.contracts.index')->with('error', $e->errors()['contract'][0]);
        } catch (Exception $e) {
            Log::error($e);
            return back()->withInput()->with('error', 'حدث خطأ أثناء تحديث العقد. يرجى المحاولة مرة أخرى.');
        }
    }


    public function destroy(string $id)
    {
        try {
            $this->contractService->deleteContract($id);
            return redirect()->route('operations-center.contracts.index')->with('success', 'تم حذف العقد بنجاح!');
        } catch (ValidationException $e) {
            return redirect()->route('operations-center.contracts.index')->with('error', $e->errors()['contract'][0]);
        } catch (\Exception $e) {
            return redirect()->route('operations-center.contracts.index')->with('error', 'حدث خطأ أثناء حذف العقد. يرجى المحاولة مرة أخرى.');
        }
    }

    /*
    |============================================================================
    |============================================================================
    |                           anothor functions
    |============================================================================
    |============================================================================
    */
    public function exportPdfOfficial(Contract $contract)
    {
        return $this->exportPdf($contract);
    }

    public function exportPdfSimple(Contract $contract)
    {
        return $this->exportPdf($contract, false);
    }

    public function exportPdf(Contract $contract, $templateImage = true)
    {
        try {
            $signature              = false;
            $is_private_and_secret  = false;

            if ($contract->contract_type == "main") {
                $template = SettingsTemplate::where('template_type', 'contracts')->value('content') ?? '';
            } else {
                $template = SettingsTemplate::where('template_type', 'supplementary_contract')->value('content') ?? '';
            }

            $data = $this->getData($contract);

            if ($contract->status === ContractStatus::APPROVED) {
                $signature = true;
            }

            if ($contract->is_private_and_secret) {
                $is_private_and_secret = true;
            }

            foreach ($data as $key => $value) {
                $template = str_replace('{{' . $key . '}}', $value, $template);
            }

            return $this->pdfExport->exportHtml(
                $template,
                $contract->contract_name . '.pdf',
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
                $contracts = Contract::onlyTrashed()
                    ->with(['customer', 'contractManager', 'offer'])
                    ->select([
                        'id',
                        'contract_name',
                        'contract_number',
                        'customer_id',
                        'contract_start_date',
                        'expected_closure_date',
                        'contract_manager_id',
                        'offer_id',
                        'deleted_at',
                    ])->orderBy('id', 'desc');

                return datatables()->of($contracts)
                    ->addIndexColumn()
                    ->addColumn('customer_name', function ($row) {
                        return $row->customer ? $row->customer->name : 'غير متوفر';
                    })
                    ->addColumn('employee_name', function ($row) {
                        return $row->contractManager ? $row->contractManager->name : 'غير متوفر';
                    })
                    ->addColumn('offer_name', function ($row) {
                        return $row->offer ? $row->offer->offer_name : 'غير متوفر';
                    })
                    ->editColumn('deleted_at', function ($row) {
                        return $row->deleted_at ? Hijri::ShortDate($row->deleted_at) : '';
                    })
                    ->addColumn('action', function ($row) {
                        $restoreUrl = route('operations-center.contracts.restore', $row->id);
                        $forceDeleteUrl = route('contracts.forceDelete', $row->id);
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

            return view('operations_center.operations-center.contracts.trashed');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    }

    // Restore
    public function restore($id)
    {
        try {
            $contract = Contract::onlyTrashed()->findOrFail($id);
            $contract->restore();
            return redirect()->route('operations-center.contracts.trashed')->with('success', 'تم استعادة العقد بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء استعادة العقد. يرجى المحاولة لاحقاً.');
        }
    }

    // forceDelete
    public function forceDelete($id)
    {
        $contract = Contract::onlyTrashed()->findOrFail($id);
        $graphService = app(MicrosoftGraphBaseService::class);
        $filesService = app(FilesService::class);
        $user = Auth::user();
        $userIdInSystem = $user->id;

        $accessToken = $graphService->getValidUserAccessToken($user);
        if (!$accessToken) {
            return redirect()->back()->with('error', 'فشل في الحصول على رمز الوصول.')->withInput();
        }

        $userId = $filesService->getUserIdByEmail($accessToken, $user->email);
        if (!$userId) {
            return redirect()->back()->with('error', 'فشل في الحصول على معرف المستخدم.')->withInput();
        }
        // حذف المرفقات المرتبطة
        foreach ($contract->attachments as $attachment) {
            // إرسال مهمة حذف الملف إلى الطابور
            DeleteFileFromOneDriveJob::dispatch(
                $userIdInSystem,
                $userId,
                $attachment->file_id,
                $attachment->id
            )->onConnection('database');
        }

        $tasks = Task::where('contract_id', $contract->id)->get();

        $this->deleteTask($tasks, $contract);


        return redirect()->route('operations-center.contracts.trashed')->with('success', 'تم حذف العقد نهائيًا بنجاح، وجاري حذف المرفقات !');
    }

    /*
    |============================================================================
    |============================================================================
    |                             Private functions
    |============================================================================
    |============================================================================
    */

    private function getData(Contract $contract): array
    {
        $isMainContract = $contract->contract_type === 'main';
        $customer       = $isMainContract ? $contract->customer : $contract->mainContract?->customer;

        return [
            // بيانات العميل
            'customer_name'         => $customer?->name ?? '-',
            'civil_registry'        => $customer?->civil_registry_number ?? '-',
            'address'               => $customer?->region?->name ?? '-',
            'contact_number'        => $this->formatPhoneNumber($customer?->contact_number),
            'email'                 => $customer?->email ?? '-',
            'nationality'                 => $customer?->nationality->name ?? '-',

            // بيانات العقد
            'contract_name'         => $contract->contract_name,
            'contract_number'       => $contract->contract_number,
            'expected_closure_date' => $contract->expected_closure_date,
            'contract_start_date'   => $contract->getRawOriginal('contract_start_date'),
            'contract_end_date'     => $contract->getRawOriginal('contract_end_date'),

            'contract_start_day'    => Carbon::parse($contract->getRawOriginal('contract_start_date'))->translatedFormat('l'),
            'contract_end_day'      => $contract->contract_end_date ? Carbon::parse($contract->getRawOriginal('contract_end_date'))->translatedFormat('l') : '',

            //
            'main_contract_number'      => $isMainContract ? '-' : ($contract->mainContract?->contract_number ?? '-'),
            'main_contract_start_date'  => $isMainContract ? '-' : ($contract->mainContract?->getRawOriginal('contract_start_date') ?? '-'),
            'supplement_preamble' => $isMainContract ? '-' : ($contract->supplement_preamble ?? '-'),
            'supplement_terms' => $isMainContract ? '-' : ($contract->supplement_terms ?? '-'),


            // بيانات العرض
            'technical_offer' => $isMainContract
                ? ($contract->offer?->technical_offer ?? '-')
                : ($contract->supplementary_technical_offer ?? '-'),
            'financial_offer' => $isMainContract
                ? ($contract->offer?->financial_offer ?? '-')
                : ($contract->supplementary_financial_offer ?? '-'),

            'current_date' => Carbon::now()->toDateString(),
            'day'          => Carbon::now()->translatedFormat('l'),
        ];
    }

    // للتعامل مع علامة + في ارقام الهواتف
    private function formatPhoneNumber($phoneNumber = null)
    {
        if (empty($phoneNumber)) {
            return '-';
        }
        if (str_contains($phoneNumber, '+')) {
            $cleanNumber = str_replace('+', '', $phoneNumber);
            return $cleanNumber . '+';
        }

        return $phoneNumber;
    }


    /*
    |============================================================================
    |============================================================================
    | Founction for API
    |============================================================================
    |============================================================================
    */
    // لجلب العميل و مسؤول العلاقة حسب العرض الذي تم اختياره
    public function getOfferDetails($id)
    {
        $offer = Offers::find($id);
        if ($offer) {
            $customer = $offer->customer;
            $relationshipManager = $offer->relationshipManager;

            $technical_offer = $offer->technical_offer;
            $financial_offer = $offer->financial_offer;

            return response()->json([
                'customer' => $customer,
                'relationship_manager' => $relationshipManager,

                'technical_offer' => $technical_offer,
                'financial_offer' => $financial_offer,
            ]);
        }
        return response()->json(null, 404);
    }

    // get Main Contract Details
    public function getMainContractDetails($id)
    {
        $contract = Contract::with(['offer', 'customer', 'relationshipManager'])->find($id);

        if (!$contract || $contract->contract_type !== 'main') {
            return response()->json(['error' => 'العقد غير موجود أو ليس عقدًا رئيسيًا.'], 404);
        }

        return response()->json([
            'offer' => [
                'offer_id' => $contract->offer->id,
                'offer_name' => $contract->offer->offer_name,
            ],
            'customer' => [
                'id' => $contract->customer->id,
                'name' => $contract->customer->name,
            ],
            'relationship_manager' => [
                'id' => $contract->relationship_manager_id,
                'name' => $contract->relationshipManager->name,
            ],
            'technical_offer' => $contract->offer->technical_offer,
            'financial_offer' => $contract->offer->financial_offer,
        ]);
    }
}
