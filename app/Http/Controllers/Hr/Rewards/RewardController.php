<?php

namespace App\Http\Controllers\Hr\Rewards;

use App\Data\Hr\Rewards\RewardData;
use App\DataTables\Hr\Reward\RewardsDataTable;
use App\Enums\Hr\Reward\RewardStatus;
use App\Enums\Hr\Reward\RewardType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Hr\Rewards\RewardRequest;
use App\Models\Hr\Employees\Employees;
use App\Models\Hr\Rewards\Reward;
use App\Services\HR\Rewards\RewardService;
use Illuminate\Http\Request;

class RewardController extends Controller
{
    public function __construct(private RewardService $service)
    {
        $this->middleware('can:إدارة المكافأت')->only(['index', 'show']);
        $this->middleware('can:إضافة مكافأة')->only(['create', 'store']);
        $this->middleware('can:تعديل مكافأة')->only(['edit', 'update']);
        $this->middleware('can:حذف مكافأة')->only(['destroy']);
    }

    /*
    |============================================================================
    | Index
    |============================================================================
    */
    public function index(RewardsDataTable $dataTable, Request $request)
    {
        try {
            if ($request->ajax()) return $dataTable->ajax();

            // Statistics
            $statusCounts = Reward::query()
                ->select('status')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $totalReward    = array_sum($statusCounts);
            $pendingReward  = $statusCounts[RewardStatus::Pending->value] ?? 0;
            $approvedReward = $statusCounts[RewardStatus::Approved->value] ?? 0;
            $rejectedReward = $statusCounts[RewardStatus::Rejected->value] ?? 0;

            // Filters
            $employees       = Employees::active()->select('id', 'name', 'nickname')->get();
            $rewardTypes     = RewardType::options();
            $rewardStatus    = RewardStatus::options();


            return $dataTable->render('hr.rewards.index', compact(
                // Statistics
                'totalReward',
                'pendingReward',
                'approvedReward',
                'rejectedReward',
                // Filters
                'employees',
                'rewardTypes',
                'rewardStatus'
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
        $employees    = Employees::active()->select('id', 'name', 'nickname')->get();
        $rewardTypes  = RewardType::options();
        return view('hr.rewards.create', compact('employees', 'rewardTypes'));
    }

    /*
    |============================================================================
    | store
    |============================================================================
    */
    public function store(RewardRequest $request)
    {
        try {
            $dto = new RewardData($request->validated());

            $this->service->createReward($dto);

            return redirect()->route('hr.rewards.index')->with('success', 'تم إضافة المكافأة بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }


    /*
    |============================================================================
    | show
    |============================================================================
    */
    public function show(Reward $reward)
    {
        $reward->load([
            'employee',
            'createdBy',
            'updatedBy',
            'approvalRequest.requestLevels.employee',
        ]);

        // -- Approval --
        $approvalStages = $reward->approvalRequest ? $reward->approvalRequest->getApprovalStages() : [];
        $isAutoApproved = empty($approvalStages) && ($reward->status === RewardStatus::Approved);
        // -- Approval --

        return view('hr.rewards.show', compact('reward', 'approvalStages', 'isAutoApproved'));
    }





    /*
    |============================================================================
    | edit
    |============================================================================
    */
    public function edit(Reward $reward)
    {
        if (!$reward->status->canEdit()) {
            return redirect()
                ->route('hr.rewards.index')
                ->with('error', ' لا يمكن تعديل المكافأة في  حالة  ' . $reward->status->label());
        }

        $employees   = Employees::active()->select('id', 'name', 'nickname')->get();

        $rewardTypes = RewardType::options();

        return view('hr.rewards.edit', compact('reward', 'employees', 'rewardTypes'));
    }


    /*
    |============================================================================
    | Update
    |============================================================================
    */
    public function update(RewardRequest $request, int $id)
    {
        try {
            $dto = new RewardData($request->validated());

            $this->service->updateReward($id, $dto);

            return redirect()->route('hr.rewards.index')->with('success', 'تم تحديث المكافأة بنجاح.');
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
            $this->service->deleteReward($id);

            return redirect()->route('hr.rewards.index')->with('success', 'تم حذف المكافأة بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
}
