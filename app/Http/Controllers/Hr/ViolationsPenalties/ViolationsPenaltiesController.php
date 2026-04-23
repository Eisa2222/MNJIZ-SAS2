<?php

namespace App\Http\Controllers\Hr\ViolationsPenalties;

use App\Data\Hr\ViolationsPenalties\ViolationsPenaltiesData;
use App\DataTables\Hr\ViolationsPenalties\ViolationsPenaltiesDataTable;
use App\Enums\Hr\ViolationsPenalties\ViolationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Hr\ViolationsPenalties\RespondToAppealRequest;
use App\Http\Requests\Hr\ViolationsPenalties\StoreViolationsPenalties;
use App\Models\general_setting\SettingsViolation;
use App\Models\Hr\Employees\Employees;
use App\Models\Hr\Violations\Violation;
use App\Services\HR\ViolationsPenalties\ViolationPenaltyService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ViolationsPenaltiesController  extends Controller
{
    private $route = "hr.violations-penalties";

    public function __construct(private ViolationPenaltyService $penaltyService)
    {
        $this->middleware('can:إدارة الانتهاكات والعقوبات')->only(['index', 'show']);
        $this->middleware('can:إضافة عقوبة او إنتهاك')->only(['create', 'store']);
        $this->middleware('can:تعديل عقوبة او إنتهاك')->only(['edit', 'update']);
        $this->middleware('can:حذف عقوبة او إنتهاك')->only(['destroy']);
        $this->middleware('can:تطبيق/إلغاء عقوبة او إنتهاك')->only(['applyPenalty', 'cancelPenalty', 'respondToAppeal']);
    }

    public function index(ViolationsPenaltiesDataTable $dataTable)
    {
        try {
            // Statistics
            // $statusCounts = Reward::query()
            //     ->select('status')
            //     ->selectRaw('COUNT(*) as count')
            //     ->groupBy('status')
            //     ->pluck('count', 'status')
            //     ->toArray();

            // $totalReward = array_sum($statusCounts);
            // $pendingReward = $statusCounts[RewardStatus::Pending->value] ?? 0;
            // $approvedReward = $statusCounts[RewardStatus::Approved->value] ?? 0;
            // $rejectedReward = $statusCounts[RewardStatus::Rejected->value] ?? 0;

            // // Filters
            // $employees       = Employees::active()->select('id', 'name', 'nickname')->get();
            // $rewardTypes     = RewardType::options();
            // $rewardStatus    = RewardStatus::options();


            return $dataTable->render('hr.violations_penalties.index');

            // return $dataTable->render('hr.rewards.index', compact(
            //     // Statistics
            //     'totalReward',
            //     'pendingReward',
            //     'approvedReward',
            //     'rejectedReward',
            //     // Filters
            //     'employees',
            //     'rewardTypes',
            //     'rewardStatus'
            // ));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    }

    // Create
    public function create()
    {
        // الحصول على الانتهاكات مع تصنيفاتها
        $violations = SettingsViolation::with(['category:id,name'])
            ->select([
                'settings_violations.id',
                'settings_violations.description',
                'settings_violations.settings_violation_category_id',
                'settings_violations.penalty_first',
                'settings_violations.penalty_second',
                'settings_violations.penalty_third',
                'settings_violations.penalty_fourth',
                'settings_violations.extra_deduction'
            ])
            ->join('settings_violation_categories', 'settings_violations.settings_violation_category_id', '=', 'settings_violation_categories.id')
            ->where('settings_violations.status', 'active')
            ->orderBy('settings_violation_categories.name')
            ->orderBy('settings_violations.description')
            ->get();

        // تجميع الانتهاكات حسب التصنيف
        $categorizedViolations = $violations->groupBy('category.name');

        $employees  = Employees::active()->select('id', 'name', 'nickname')->get();


        return view('hr.violations_penalties.create', compact('employees', 'categorizedViolations'));
    }

    // Store
    public function store(StoreViolationsPenalties $request)
    {
        try {
            $dto = new ViolationsPenaltiesData($request->validated());

            $this->penaltyService->createViolation($dto);

            return redirect()->route($this->route . '.index')->with('success', 'تم إضافة الإنتهاك بنجاح.');
        } catch (\Exception $e) {
            // Log::error($e);
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    // Show
    public function show(Violation $violation)
    {
        return view('hr.violations_penalties.show', compact('violation'));
    }

    // Edit
    public function edit(Violation $violation)
    {
        if (!ViolationStatus::from($violation->status->value)->canEditOrDelete()) {
            return redirect()->route($this->route . '.index')->with('error', 'لا يمكن تعديل المخالفة إلا إذا كانت في حالة انتظار الاعتماد.');
        }

        // الحصول على الانتهاكات مع تصنيفاتها
        $violations = SettingsViolation::with(['category:id,name'])
            ->select([
                'settings_violations.id',
                'settings_violations.description',
                'settings_violations.settings_violation_category_id',
                'settings_violations.penalty_first',
                'settings_violations.penalty_second',
                'settings_violations.penalty_third',
                'settings_violations.penalty_fourth',
                'settings_violations.extra_deduction'
            ])
            ->join('settings_violation_categories', 'settings_violations.settings_violation_category_id', '=', 'settings_violation_categories.id')
            ->where('settings_violations.status', 'active')
            ->orderBy('settings_violation_categories.name')
            ->orderBy('settings_violations.description')
            ->get();

        // تجميع الانتهاكات حسب التصنيف
        $categorizedViolations = $violations->groupBy('category.name');

        $employees  = Employees::active()->select('id', 'name', 'nickname')->get();


        return view('hr.violations_penalties.edit', compact('violation', 'employees', 'categorizedViolations'));
    }

    // Updaet
    public function update(StoreViolationsPenalties $request, Violation $violation)
    {
        try {
            $dto = new ViolationsPenaltiesData($request->validated());

            $this->penaltyService->updateViolation($violation->id, $dto);

            return redirect()->route($this->route . '.index')->with('success', 'تم تحديث الانتهاك بنجاح');
        } catch (\Exception $e) {
            // Log::error($e);
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    // Destroy
    public function destroy(Violation $violation)
    {
        if (!ViolationStatus::from($violation->status->value)->canEditOrDelete()) {
            return redirect()->route($this->route . '.index')->with('error', 'لا يمكن حذف المخالفة إلا إذا كانت في حالة انتظار الاعتماد.');
        }

        try {

            $this->penaltyService->deleteViolation($violation->id);

            return redirect()->route($this->route . '.index')->with('success', 'تم حذف الانتهاك بنجاح');
        } catch (\Exception $e) {
            // Log::error($e);
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }


    public function applyPenalty(Request $request, Violation $violation)
    {

        try {

            // التحقق من وجود تظلم قيد المراجعة
            if ($violation->appeal && $violation->appeal->status === 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن تطبيق العقوبة أثناء وجود تظلم قيد المراجعة.'
                ], 422);
            }

            // التحقق من قبول التظلم
            if ($violation->appeal && $violation->appeal->status === 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن تطبيق العقوبة بعد قبول التظلم.'
                ], 422);
            }


            $validator = Validator::make($request->all(), [
                'notes' => 'nullable|string|max:1000',
                'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
            ]);

            if ($validator->fails()) {

                return response()->json([
                    'success' => false,
                    'message' => 'خطأ في البيانات المدخلة',
                    'errors' => $validator->errors()
                ], 422);
            }


            // استدعاء الخدمة
            $this->penaltyService->applyPenalty(
                $violation->id,
                $request->input('notes'),
                $request->file('attachment')
            );

            return response()->json([
                'success' => true,
                'message' => 'تم تطبيق العقوبة بنجاح',
                'data' => [
                    'violation_status' => $violation->fresh()->status->value
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e,
            ], 500);
        }
    }

    public function cancelPenalty(Violation $violation)
    {
        try {
            $this->penaltyService->cancelPenalty($violation->id);
            return response()->json(['success' => true, 'message' => 'تم إلغاء العقوبة بنجاح']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function respondToAppeal(RespondToAppealRequest $request,  $id)
    {
        try {
            $data = $request->validated();

            $this->penaltyService->respondToAppeal($id, $data['status'], $data['response']);

            $message = $data['status'] === 'approved'
                ? 'تم قبول التظلم وإلغاء المخالفة بنجاح.'
                : 'تم رفض التظلم وستطبق العقوبة على الموظف.';

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            // Log::error($e);
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }


    /*
    |============================================================================
    |                               API
    |============================================================================
    */
    public function details($id)
    {
        $violation = SettingsViolation::find($id);
        try {

            if (!$violation) {
                return response()->json([
                    'success' => false,
                    'message' => "الانتهاك غير موجود في قاعدة البيانات"
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $violation
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب تفاصيل الانتهاك: ' . $e->getMessage()
            ], 500);
        }
    }


    public function getOccurrenceCount(Request $request)
    {
        try {
            // التحقق من صحة البيانات
            $request->validate([
                'employee_id'       => 'required|exists:employees,id',
                'violation_id'      => 'required|exists:settings_violations,id',
                'violation_id_edit' => 'nullable|exists:violations,id',
            ], [
                'employee_id.required' => 'يجب تحديد الموظف.',
                'employee_id.exists' => 'الموظف غير موجود.',
                'violation_id.required' => 'يجب تحديد نوع المخالفة.',
                'violation_id.exists' => 'نوع المخالفة غير موجود.',
                'violation_id_edit.exists' => 'المخالفة المحددة للتعديل غير موجودة.',
            ]);

            // استدعاء السيرفس
            $result = $this->penaltyService->getOccurrenceCount(
                $request->employee_id,
                $request->violation_id,
                $request->violation_id_edit
            );

            return response()->json([
                'success'               => true,
                'count'                 => $result['count'],
                'occurrence'            => $result['occurrence'],
                'applicable_penalty'    => $result['formatted_penalty'],
                'penalty_text'          => $result['penalty'],
                'violation_type'        => $result['violation_type'],
                'category'              => $result['category'],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في البيانات المدخلة.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حساب عدد مرات التكرار: ' . $e->getMessage()
            ], 500);
        }
    }
}