<?php

namespace App\Http\Controllers\Hr\Advances;

use App\Data\Hr\Advances\AdvanceData;
use App\DataTables\Hr\Advance\AdvancesDataTable;
use App\Enums\Hr\Advance\AdvanceStatus;
use App\Enums\Hr\Advance\AdvanceType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Hr\Advances\AdvanceRequest;
use App\Models\Hr\Advances\Advance;
use App\Models\Hr\Employees\Employees;
use App\Services\HR\Advances\AdvanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdvanceController extends Controller
{
    public function __construct(private AdvanceService $service)
    {
        $this->middleware('can:إدارة السلف')->only(['index', 'show']);
        $this->middleware('can:إضافة سلفة')->only(['create', 'store']);
        $this->middleware('can:تعديل سلفة')->only(['edit', 'update']);
        $this->middleware('can:حذف سلفة')->only(['destroy']);
    }
    /*
    |============================================================================
    | Index
    |============================================================================
    */
    public function index(AdvancesDataTable $dataTable, Request $request)
    {
        try {
            if ($request->ajax()) return $dataTable->ajax();

            // Statistics
            $statusCounts = Advance::query()
                ->select('status')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $totalAdvance = array_sum($statusCounts);
            $pendingAdvance  = $statusCounts[AdvanceStatus::Pending->value] ?? 0;
            $approvedAdvance = $statusCounts[AdvanceStatus::Approved->value] ?? 0;
            $rejectedAdvance = $statusCounts[AdvanceStatus::Rejected->value] ?? 0;

            // Filters
            $employees        = Employees::active()->select('id', 'name', 'nickname')->get();
            $advanceTypes     = AdvanceType::options();
            $advanceStatus    = AdvanceStatus::options();
            return $dataTable->render('hr.advances.index', compact(
                // Statistics
                'totalAdvance',
                'pendingAdvance',
                'approvedAdvance',
                'rejectedAdvance',
                // Filters
                'employees',
                'advanceTypes',
                'advanceStatus'
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
        $employees    = Employees::active()->select('id', 'name', 'nickname', 'user_id')->get();
        $advanceTypes = AdvanceType::options();
        return view('hr.advances.create', compact('employees', 'advanceTypes'));
    }

    /*
    |============================================================================
    | store
    |============================================================================
    */
    public function store(AdvanceRequest $request)
    {
        try {
            $dto = new AdvanceData($request->validated());

            $this->service->createAdvance($dto);

            return redirect()->route('hr.advances.index')->with('success', 'تم إضافة السلفة بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }


    /*
    |============================================================================
    | show
    |============================================================================
    */
    public function show(Advance $advance)
    {
        $advance->load([
            'employee',
            'createdBy',
            'updatedBy',
            'approvalRequest.requestLevels.employee',
        ]);

        $approvalStages = $advance->approvalRequest
            ? $advance->approvalRequest->getApprovalStages()
            : [];

        $finalEnums   = [AdvanceStatus::Approved, AdvanceStatus::Executed];
        $finalStrings = ['approved', 'executed'];

        $status        = $advance->status;
        $isFinalStatus = $status instanceof AdvanceStatus
            ? in_array($status, $finalEnums, true)
            : in_array(strtolower((string) $status), $finalStrings, true);

        $isAutoApproved = empty($approvalStages) && $isFinalStatus;

        return view('hr.advances.show', compact('advance', 'approvalStages', 'isAutoApproved'));
    }




    /*
    |============================================================================
    | edit
    |============================================================================
    */
    public function edit(Advance $advance)
    {
        if (!$advance->status->canEdit()) {
            return redirect()
                ->route('hr.advances.index')
                ->with('error', ' لا يمكن تعديل السلفة في  حالة  ' . $advance->status->label());
        }

        $employees = Employees::active()->select('id', 'name', 'nickname', 'user_id')->get();

        $advanceTypes = AdvanceType::options();

        return view('hr.advances.edit', compact('advance', 'employees', 'advanceTypes'));
    }


    /*
    |============================================================================
    | Update
    |============================================================================
    */
    public function update(AdvanceRequest $request, int $id)
    {
        try {
            $dto = new AdvanceData($request->validated());

            $this->service->update($id, $dto);

            return redirect()->route('hr.advances.index')->with('success', 'تم تحديث السلفة بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }


    /*
    |============================================================================
    | Delete
    |============================================================================
    */
    public function destroy(int $id)
    {
        try {
            $this->service->delete($id);

            return redirect()->route('hr.advances.index')->with('success', 'تم حذف السلفة بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
}
