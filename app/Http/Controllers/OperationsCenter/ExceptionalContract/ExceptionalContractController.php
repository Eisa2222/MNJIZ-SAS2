<?php

namespace App\Http\Controllers\OperationsCenter\ExceptionalContract;

use App\DataTables\OperationsCenter\ExceptionalContract\ExceptionalContractDataTable;
use App\Helpers\SettingsHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsCenter\ExceptionalContract\ExceptionalContractRequest;
use App\Models\OperationsCenter\Customer\Customers;
use App\Models\Hr\Employees\Employees;
use App\Models\OperationsCenter\ExceptionalContract\ExceptionalContract;
use App\Models\GeneralSetting\SystemSetting\Settings;
use App\Services\Common\PdfExportService;
use App\Services\OperationsCenter\ExceptionalContract\ExceptionalContractService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class ExceptionalContractController extends Controller
{
    private ExceptionalContractService $contractService;
    private PdfExportService $pdfExport;

    public function __construct(ExceptionalContractService $contractService, PdfExportService $pdfExport)
    {
        $this->contractService = $contractService;
        $this->pdfExport       = $pdfExport;

        $this->middleware(function ($request, $next) {
            if ($request->user()->can('كل العقود الإستثنائية') || $request->user()->can('العقود الإستثنائية الخاصة بي')) {
                return $next($request);
            }
            abort(403);
        })->only(['index', 'show']);

        $this->middleware('can:إضافة عقد إستثنائي')->only(['create', 'store']);
        $this->middleware('can:تعديل عقد إستثنائي')->only(['edit', 'update']);
        $this->middleware('can:حذف عقد إستثنائي')->only(['destroy']);
    }

    /*
    |============================================================================
    | Index
    |============================================================================
    */
    public function index(ExceptionalContractDataTable $dataTable, Request $request)
    {
        $route = 'operations-center.exceptional-contracts';
        $baseQuery =  ExceptionalContract::query();


        try {
            if ($request->ajax()) return $dataTable->ajax();


            // Filters
            $customers   = Customers::select('id', 'name')->get();
            $employees   = Employees::active()->select('id', 'name', 'nickname')->get();
            $statuses = ExceptionalContract::getStatusOptions();

            return view('operations_center.exceptional_contract.index', compact(
                'route',
                'customers',
                'employees',
                'statuses'
            ));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    }


    /*
    |============================================================================
    | create
    |============================================================================
    */
    public function create()
    {
        extract($this->loadData());

        return view('operations_center.exceptional_contract.create', compact('employees', 'customers'));
    }


    /*
    |============================================================================
    | store
    |============================================================================
    */
    public function store(ExceptionalContractRequest $request)
    {
        try {
            $this->contractService->createContract($request->validated());

            return redirect()->route('operations-center.exceptional-contracts.index')->with('success', 'تم إضافة العقد الإستثنائي بنجاح');
        } catch (\Exception $e) {
            Log::error('Error creating exceptional contract: ' . $e->getMessage());
            return redirect()->back()->with('error', 'حدث خطأ أثناء إضافة العقد الإستثنائي. يرجى المحاولة لاحقاً.');
        }
    }


    /*
    |============================================================================
    | show
    |============================================================================
    */
    public function show(ExceptionalContract $exceptional_contract)
    {
        $exceptional_contract->load(['customer', 'employee', 'creator', 'approvals.approver']);

        $approvals      = $exceptional_contract->approvals;

        $pending        = $exceptional_contract->pendingApprovalFor(auth()->user());

        $seal   = Settings::current()->signature ?? null;
        $header =  SettingsHelper::get('horizontal_header_image') ?? null;
        $footer =  SettingsHelper::get('horizontal_footer_image') ?? null;

        $templateData = $this->getData($exceptional_contract) + [
            'approvals' => $approvals,   // ← مفتاح الحل
            'seal'      => $seal,
            'header'    => $header,
            'footer'    => $footer,
        ];

        $processedContent = view('operations_center.exceptional_contract.templates.show_template', $templateData)
            ->render();



        return view('operations_center.exceptional_contract.show', compact(
            'exceptional_contract',
            'processedContent',
            'approvals',
            'pending',
            'seal',
            'header',
            'footer'

        ));
    }



    /*
    |============================================================================
    | edit
    |============================================================================
    */
    public function edit(Request $request, ExceptionalContract $exceptional_contract)
    {
        extract($this->loadData());

        if ($exceptional_contract->projects()->exists()) {
            return redirect()->route('operations-center.exceptional-contracts.index')->with('error', 'لا يمكن تعديل العقد لارتباطه بسجلات أخرى.');
        }


        return view('operations_center.exceptional_contract.edit', compact('employees', 'customers', 'exceptional_contract'));
    }


    /*
    |============================================================================
    | update
    |============================================================================
    */
    public function update(ExceptionalContractRequest $request, ExceptionalContract $exceptional_contract)
    {

        if ($exceptional_contract->projects()->exists()) {
            return redirect()->route('operations-center.exceptional-contracts.index')->with('error', 'لا يمكن تعديل العقد لارتباطه بسجلات أخرى.');
        }

        try {
            $this->contractService->updateContract($exceptional_contract, $request->validated());
            return redirect()->route('operations-center.exceptional-contracts.index')->with('success', 'تم تعديل العقد الإستثنائي وإعادة طلب الاعتمادات بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error updating exceptional contract: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء تعديل العقد الإستثنائي. يرجى المحاولة لاحقاً.');
        }
    }



    /*
    |============================================================================
    | destroy
    |============================================================================
    */
    public function destroy(ExceptionalContract $exceptional_contract)
    {
        $exceptional_contract->delete();

        return redirect()->route('operations-center.exceptional-contracts.index')->with('success', 'تم حذف العقد الإستثنائي بنجاح!');
    }



    /*
    |============================================================================
    |============================================================================
    |                          anothor functions
    |============================================================================
    |============================================================================
    */
    public function exportPdfOfficial(ExceptionalContract $exceptional_contract)
    {
        return $this->exportPdf($exceptional_contract);
    }

    public function exportPdfSimple(ExceptionalContract $exceptional_contract)
    {
        return $this->exportPdf($exceptional_contract, false);
    }

    public function exportPdf(ExceptionalContract $exceptional_contract, $templateImage = true)
    {
        try {

            $exceptional_contract->load('approvals.approver');

            $approvals = $exceptional_contract->approvals;

            // ✦ دمج البيانات الإضافية مع بيانات القالب السابقة
            $data = $this->getData($exceptional_contract) + [
                'approvals' => $approvals,
            ];


            return $this->pdfExport->exportPage(
                'operations_center.exceptional_contract.templates.show_template',
                $data,
                $exceptional_contract->contract_name . '.pdf',
                $templateImage,
            );
        } catch (\Exception $e) {
            Log::error('Error generating PDF: ' . $e->getMessage());
            return redirect()->back()->with('error', 'حدث خطأ أثناء تصدير العقد. يرجى المحاولة لاحقاً.');
        }
    }


    public function approve(ExceptionalContract $exceptional_contract)
    {
        $employeeId = Auth::user()->employee->id;

        $approval = $exceptional_contract->approvals()->where('approver_id', $employeeId)->where('status', ExceptionalContract::STATUS_PENDING)->firstOrFail();

        $approval->update([
            'status'      => ExceptionalContract::STATUS_APPROVED,
            'approved_at' => now(),
            'reason'      => null,
        ]);

        $hasPending  = $exceptional_contract->approvals()->where('status', ExceptionalContract::STATUS_PENDING)->exists();

        $hasRejected = $exceptional_contract->approvals()->where('status', ExceptionalContract::STATUS_REJECTED)->exists();

        if (! $hasPending && ! $hasRejected) {
            $exceptional_contract->changeStatus(ExceptionalContract::STATUS_APPROVED);
        }

        return back()->with('success', 'تم اعتماد العقد بنجاح.');
    }


    public function reject(Request $request, ExceptionalContract $exceptional_contract)
    {
        $employeeId = Auth::user()->employee->id;

        $request->validate([
            'reason' => 'required|string|max:500',
        ], [
            'reason.required' => 'حقل السبب مطلوب عند الرفض.',
        ]);

        $approval = $exceptional_contract->approvals()->where('approver_id', $employeeId)->where('status', ExceptionalContract::STATUS_PENDING)->firstOrFail();

        $approval->update([
            'status'      => ExceptionalContract::STATUS_REJECTED,
            'approved_at' => now(),
            'reason'      => $request->input('reason'),
        ]);

        $exceptional_contract->changeStatus(ExceptionalContract::STATUS_REJECTED);

        return back()->with('success', 'تم رفض العقد مع تسجيل السبب.');
    }


    /*
    |============================================================================
    |============================================================================
    |                          Praivat functions
    |============================================================================
    |============================================================================
    */
    private function getData(ExceptionalContract $exceptional_contract)
    {
        return [
            'project_name'  => $exceptional_contract->project_name,
            'customer_name' => $exceptional_contract->customer->name,
            'employee_name' => $exceptional_contract->employee->raw_name,
            'scope_of_work' => $exceptional_contract->scope_of_work,
            'reasons'       => $exceptional_contract->reasons,
            'equivalent'    => $exceptional_contract->equivalent,
            'current_date'  => Carbon::now(),
        ];
    }


    private function loadData()
    {
        return [
            'employees' => Employees::active()->select('id', 'name', 'nickname')->get(),
            'customers' => Customers::select(['id', 'name'])->get(),
        ];
    }
}
