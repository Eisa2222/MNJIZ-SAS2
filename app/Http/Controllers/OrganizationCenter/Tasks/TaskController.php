<?php

namespace App\Http\Controllers\OrganizationCenter\Tasks;

use App\Data\OrganizationCenter\Tasks\Task\TaskData;
use App\DataTables\OrganizationCenter\Task\AssignedTasksDataTable;
use App\DataTables\OrganizationCenter\Task\MyTasksDataTable;
use App\DataTables\OrganizationCenter\Task\TasksDataTable;
use App\Enums\OrganizationCenter\Tasks\Task\TaskField;
use App\Enums\OrganizationCenter\Tasks\Task\TaskPriority;
use App\Enums\OrganizationCenter\Tasks\Task\TaskRoutingAction;
use App\Enums\OrganizationCenter\Tasks\Task\TaskStatus;
use App\Enums\OrganizationCenter\Tasks\TaskStep\TaskStepStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrganizationCenter\Tasks\TaskRequest;
use App\Models\general_setting\SettingsMarketingChannel;
use App\Models\general_setting\SettingsSocial;
use App\Models\Hr\Employees\Employees;
use App\Models\OperationsCenter\Customer\Customers;
use App\Models\OrganizationCenter\Tasks\Task\Task;
use App\Models\OrganizationCenter\Tasks\Task\TaskAttachment;
use App\Models\OrganizationCenter\Tasks\Task\TaskEvent;
use App\Models\OrganizationCenter\Tasks\Task\TaskRouting;
use App\Models\OrganizationCenter\Tasks\TaskStep\TaskStep;
use App\Models\User;
use App\Notifications\StepCompletedNotification;
use App\Notifications\TaskCompletedNotification;
use App\Services\OrganizationCenter\Tasks\Task\Helper\TaskFormatterService;
use App\Services\OrganizationCenter\Tasks\Task\TaskService;
use App\Services\OrganizationCenter\Tasks\TaskStep\Helper\TaskStepFormatterService;
use App\Services\OrganizationCenter\Tasks\TaskStep\TaskStepService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;


class TaskController extends Controller
{
    private $route = "organization-center.tasks";
    private $page   = "organization_center.tasks";


    public function __construct(
        private TaskService $taskService,
        private TaskStepService $taskStepService,
        private TaskFormatterService $taskFormatterService,
        private TaskStepFormatterService $taskStepFormatterService
    ) {

        $this->middleware('can:كل المهام')->only(['index']);
        $this->middleware('can:المهام الخاصة بي')->only(['indexMyTasks']);
        $this->middleware('can:مهام مسندة للأخرين')->only(['indexAssignedTasks']);
        $this->middleware('can:ارشيف المهام')->only(['trashed']);
    }

    public function index(TasksDataTable $dataTable)
    {
        try {
            // Statistics
            // المهام مرتبطة مع المستخدمين وليس الموظفين
            $statusCounts = Task::select('status')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $totalTasks         = array_sum($statusCounts);
            $pendingTasks       = $statusCounts[TaskStatus::Pending->value] ?? 0;
            $inProgressTasks    = $statusCounts[TaskStatus::InProgress->value] ?? 0;
            $completedTasks     = $statusCounts[TaskStatus::Completed->value] ?? 0;

            // Filters
            $taskStatus         = TaskStatus::options();
            $taskFields         = TaskField::options();
            $taskPriority       = TaskPriority::options();
            $employees          = Employees::active()->select('id', 'user_id', 'name', 'nickname')->get();



            return $dataTable->render($this->page . '.index.main', compact(
                // Statistics
                'totalTasks',
                'pendingTasks',
                'inProgressTasks',
                'completedTasks',
                // Filters
                'taskStatus',
                'taskFields',
                'taskPriority',
                'employees'
            ));
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    }

    // مهامي
    public function indexMyTasks(MyTasksDataTable $dataTable)
    {
        try {
            // Statistics
            // المهام مرتبطة مع المستخدمين وليس الموظفين
            $userId = auth()->id();

            $statusCounts = Task::where(function ($query) use ($userId) {
                $query->whereHas('assignedUsers', function ($subQuery) use ($userId) {
                    $subQuery->where('users.id', $userId);
                })
                    ->orWhereHas('steps', function ($subQuery) use ($userId) {
                        $subQuery->whereHas('assignedUsers', function ($stepQuery) use ($userId) {
                            $stepQuery->where('users.id', $userId);
                        });
                    });
            })->select('status')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $totalTasks         = array_sum($statusCounts);
            $pendingTasks       = $statusCounts[TaskStatus::Pending->value] ?? 0;
            $inProgressTasks    = $statusCounts[TaskStatus::InProgress->value] ?? 0;
            $completedTasks     = $statusCounts[TaskStatus::Completed->value] ?? 0;

            // Filters
            $taskStatus         = TaskStatus::options();
            $taskFields         = TaskField::options();
            $taskPriority       = TaskPriority::options();



            return $dataTable->render($this->page . '.index.my_tasks', compact(
                // Statistics
                'totalTasks',
                'pendingTasks',
                'inProgressTasks',
                'completedTasks',
                // Filters
                'taskStatus',
                'taskFields',
                'taskPriority',
            ));
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    }

    // مهام قمت باسنادها للاخرين
    public function indexAssignedTasks(AssignedTasksDataTable $dataTable)
    {
        try {
            // Statistics
            // المهام مرتبطة مع المستخدمين وليس الموظفين
            $userId = auth()->id();

            $statusCounts = Task::where('created_by', $userId)->select('status')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $totalTasks         = array_sum($statusCounts);
            $pendingTasks       = $statusCounts[TaskStatus::Pending->value] ?? 0;
            $inProgressTasks    = $statusCounts[TaskStatus::InProgress->value] ?? 0;
            $completedTasks     = $statusCounts[TaskStatus::Completed->value] ?? 0;

            // Filters
            $taskStatus         = TaskStatus::options();
            $taskFields         = TaskField::options();
            $taskPriority       = TaskPriority::options();



            return $dataTable->render($this->page . '.index.assigned_tasks', compact(
                // Statistics
                'totalTasks',
                'pendingTasks',
                'inProgressTasks',
                'completedTasks',
                // Filters
                'taskStatus',
                'taskFields',
                'taskPriority',
            ));
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    }

    public function create()
    {
        $users                  = User::where('status', 'active')->select('id', 'name')->get();
        $taskFieldOptions       = TaskField::options();
        $marketingChannels      = SettingsMarketingChannel::where('status', 'active')->select(['id', 'name'])->get();

        $employees              = Employees::active()->select('id', 'name', 'nickname')->get();
        $customers              = Customers::select(['id', 'name'])->get();
        $socials                = SettingsSocial::where('status', 'active')->select(['id', 'name'])->get();


        return view($this->page . '.create', compact(
            'users',
            'taskFieldOptions',
            'marketingChannels',
            'employees',
            'customers',
            'socials'
        ));
    }

    public function store(TaskRequest $request)
    {
        try {
            $dto = new TaskData($request->validated());

            $this->taskService->create($dto);

            return $this->redirectMethod('success', 'تم إنشاء المهمة بنجاح');
        } catch (\Exception $e) {
            Log::error($e);
            return $this->redirectMethod('error', 'حدث خطأ أثناء إنشاء المهمة. يرجى المحاولة مرة أخرى.');
        }
    }

    public function show(Task $task)
    {
        try {
            $canViewTask = (
                $task->created_by === auth()->id() ||                    // منشئ المهمة
                $task->assignedUsers->contains('id', auth()->id()) ||    // مُسند إليه
                auth()->user()->can('كل المهام')                   // لديه صلاحية عامة
            );

            if (!$canViewTask) {
                return response()->view('errors.404');
            }



            $totalSteps         = $task->steps->count();
            $completedSteps     = $task->steps->whereIn('status', [TaskStepStatus::Completed, TaskStepStatus::Approved])->count();
            $progressPercentage = $totalSteps > 0 ? round(($completedSteps / $totalSteps) * 100) : 0;

            return view($this->page . '.show', compact(
                'task',
                'totalSteps',
                'completedSteps',
                'progressPercentage'
            ));
        } catch (\Exception $e) {
            // Log::error('خطأ في عرض المهمة: ' . $e->getMessage());
            return $this->redirectMethod('error', 'حدث خطأ أثناء عرض المهمة');
        }
    }

    public function edit($id)
    {
        $task = Task::ownedBy()->with(['steps.assignedUsers', 'assignedUsers.employee', 'attachments'])->findOrFail($id);

        $users              = User::where('status', 'active')->select('id', 'name')->get();
        $taskFieldOptions   = TaskField::options();
        $marketingChannels      = SettingsMarketingChannel::where('status', 'active')->select(['id', 'name'])->get();

        $employees              = Employees::active()->select('id', 'name', 'nickname')->get();
        $customers              = Customers::select(['id', 'name'])->get();
        $socials                = SettingsSocial::where('status', 'active')->select(['id', 'name'])->get();


        return view($this->page . '.edit', compact(
            'task',
            'users',
            'taskFieldOptions',
            'marketingChannels',
            'employees',
            'customers',
            'socials'
        ));
    }

    public function update(TaskRequest $request, $id)
    {
        $task = Task::ownedBy()->findOrFail($id);

        try {
            $dto = new TaskData($request->validated());

            $this->taskService->update($task, $dto);

            // إعادة التوجيه المناسب
            return $this->redirectMethod('success', 'تم تحديث المهمة بنجاح');
        } catch (\Exception $e) {
            Log::error($e);
            return $this->redirectMethod('error', 'حدث خطأ أثناء تعديل المهمة. يرجى المحاولة مرة أخرى.');
        }
    }

    public function destroy($id)
    {
        try {
            $task = Task::ownedBy()->findOrFail($id);

            $this->taskService->destroy($task);

            return $this->redirectMethod('success', 'تم حذف المهمة بنجاح');
        } catch (\Exception $e) {
            return $this->redirectMethod('error', 'حدث خطأ أثناء حذف المهمة. يرجى المحاولة مرة أخرى.');
        }
    }


    public function returnTask(Request $request, Task $task)
    {
        $request->validate([
            'return_reason' => ['required', 'string', 'max:1000'],
        ]);

        try {
            $result = $this->taskService->returnTask($task, $request->return_reason);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function reassignTask(Request $request, Task $task)
    {
        $request->validate([
            'note' => ['required', 'string', 'max:1000']
        ]);

        try {
            $result = $this->taskService->reassignTask($task, $request->note);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }




    // public function reassignTask(Request $request, Task $task)
    // {
    //
    // تحقّق أن المستخدم الحالي يملك حق الردّ
    // $lastReturn = $task->routings()->latest()->first();

    // abort_unless(
    //     $task->status === 'returned'
    //         && $lastReturn
    //         && $lastReturn->to_user_id === auth()->id(),
    //     403
    // );

    //     DB::transaction(function () use ($task, $request) {
    //         // سجلّ routing جديد
    //         TaskRouting::create([
    //             'task_id'      => $task->id,
    //             'from_user_id' => auth()->id(),
    //             'to_user_id'   => auth()->id(),
    //             'action'       => TaskRoutingAction::Assign,
    //             'reason'       => $request->note,
    //         ]);

    //         $this->recordTaskEvent($task, TaskStatus::Pending, 'تم اعادة اسناد المهمة.');

    //         // تحديث المهمة
    //         $task->update([
    //             'status'              => 'pending',
    //         ]);
    //     });

    //     return response()->json(['success' => true, 'message' => 'تم إعادة إسناد المهمة بنجاح.']);
    // }



    //   لاكمال و الغاء اكمال المهمة
    public function toggleTaskCompletion(Request $request, Task $task)
    {
        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'المهمة غير موجودة'
            ], 404);
        }

        if (!$task->assignedUsers->contains('id', auth()->id())) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكنك التعامل مع هذه المهمة لأنك غير مكلف بها.'
            ], 403);
        }

        try {
            $status = $request->input('status');

            $result = $this->taskService->toggleTaskCompletion($task, $status);

            if ($result instanceof \Illuminate\Http\JsonResponse) {
                return $result;
            }

            $message = $status === 'completed' ? 'تم إكمال المهمة بنجاح!' : 'تم إلغاء إكمال المهمة بنجاح!';

            return response()->json([
                'success'   => true,
                'message'   => $message,
                'status'    => $status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث الحالة: ' . $e->getMessage()
            ], 500);
        }
    }

    //  لاكمال و الغاء اكمال الخطوات
    public function toggleStepCompletion(Request $request, $stepId)
    {
        try {
            $step = TaskStep::findOrFail($stepId);

            if (!$step->assignedUsers->contains('id', auth()->id())) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكنك التعامل مع هذه الخطوة لأنك غير مكلف بها.'
                ], 403);
            }

            if ($step->task->status == TaskStatus::Completed) {
                return response()->json([
                    'success' => false,
                    'message' => 'عفوا هذه المهمة مكتملة لا يمكن التعديل في خطواتها'
                ], 400);
            }

            $isChecked = filter_var($request->input('isChecked', false), FILTER_VALIDATE_BOOLEAN);

            $result = $this->taskStepService->toggleStepCompletion($step, $isChecked);

            if (!$result['success']) {
                return response()->json($result, 400);
            }

            $step = $result['step'];
            $duration = $step->duration;

            return response()->json([
                'success'           => true,
                'message'           => $result['message'],
                'status'            => $step->status->value,
                'step_start_date'   => $step->step_start_date ? $step->step_start_date->format('Y-m-d H:i:s') : '-',
                'step_end_date'     => $step->step_end_date ? $step->step_end_date->format('Y-m-d H:i:s') : '-',
                'duration'          => $duration ?: '-',
                'completed_by'      => $step->completedBy ?? '-'
            ]);
        } catch (\Exception $e) {
            Log::error('خطأ في إكمال الخطوة: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث حالة الخطوة: ' . $e->getMessage()
            ], 500);
        }
    }

    // اعتماد ورفض الخطوة
    public function toggleStepApproval(Request $request, $stepId)
    {
        try {
            // البحث عن الخطوة باستخدام المعرف
            $step = TaskStep::findOrFail($stepId);

            if (!$step->assignedUsers->contains('id', auth()->id())) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكنك التعامل مع هذه الخطوة لأنك غير مكلف بها.'
                ], 403);
            }

            if ($step->task->status == TaskStatus::Completed) {
                return response()->json([
                    'success' => false,
                    'message' => 'عفوا هذه المهمة مكتملة لا يمكن التعديل في خطواتها'
                ], 400);
            }

            $action         = $request->input('action');
            $rejectReason   = $request->input('reject_reason');

            if (!in_array($action, ['approve', 'reject'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'إجراء غير صحيح'
                ], 400);
            }

            if ($action === 'reject' && empty($rejectReason)) {
                return response()->json([
                    'success' => false,
                    'message' => 'يجب إدخال سبب الرفض'
                ], 400);
            }


            $result = $this->taskStepService->toggleStepApproval($step, $action, $rejectReason);

            if (!$result['success']) {
                return response()->json($result, 400);
            }

            $step = $result['step'];

            return response()->json([
                'success'           => true,
                'message'           => $result['message'],
                'status'            => $step->status->value,
                'step_start_date'   => $step->step_start_date ? $step->step_start_date->format('Y-m-d H:i:s') : '-',
                'step_end_date'     => $step->step_end_date ? $step->step_end_date->format('Y-m-d H:i:s') : '-',
                'duration'          => $step->duration ?: '-',
                'completed_by'      => $step->completedBy ?? '-',
                'reject_reason'     => $step->reject_reason ?? ''
            ]);
        } catch (\Exception $e) {
            Log::error('خطأ في اعتماد/رفض الخطوة: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث حالة اعتماد الخطوة: ' . $e->getMessage()
            ], 500);
        }
    }



    /*
    |============================================================================
    |============================================================================
    |                            Private Methods
    |============================================================================
    |============================================================================
    */
    private function redirectMethod($status, $message)
    {
        if (auth()->user()->can('كل المهام')) {
            return redirect()->route('organization-center.tasks.index')->with($status, $message);
        } elseif (auth()->user()->can('مهام مسندة للأخرين')) {
            return redirect()->route('organization-center.tasks.assigned-tasks')->with($status, $message);
        } else {
            return redirect()->route('organization-center.tasks.my-tasks')->with($status, $message);
        }
    }





    public function trashed(Request $request)
    {
        try {
            if ($request->ajax()) {
                $tasks = Task::onlyTrashed()  // لعرض المهام المحذوفة فقط
                    ->with(['steps' => function ($query) {
                        $query->orderBy('step_order');
                    }])
                    ->select([
                        'id',
                        'task_name',
                        'priority',
                        'task_field',
                        'status',
                        'due_date',
                        'due_time',
                        'created_by',
                        'created_at',
                        'task_start_date',
                        'task_end_date',
                    ]);
                // فلترة حسب الأولوية
                if ($request->priority) {
                    $tasks->where('priority', $request->priority);
                }

                // فلترة حسب مجال المهمة
                if ($request->task_field) {
                    $tasks->where('task_field', $request->task_field);
                }

                // فلترة حسب حالة المهمة
                if ($request->status) {
                    $tasks->where('status', $request->status);
                }
                $tasks->orderBy('id', 'desc');
                return DataTables::of($tasks)
                    ->editColumn('task_name', function ($row) {
                        $truncated = mb_substr($row->task_name, 0, 40) . (mb_strlen($row->task_name) > 40 ? '...' : '');
                        return '<a href="' . route("tasks.show", $row->id) . '">' . e($truncated) . '</a>';
                    })

                    ->editColumn('priority', function ($row) {
                        $style = 'display:inline-block; font-size:10px; text-align: center;';
                        if ($row->priority === 'low') {
                            return '<span class="badge bg-success" style="' . $style . '">منخفضة</span>';
                        } else if ($row->priority === 'medium') {
                            return '<span class="badge bg-warning" style="' . $style . '">متوسطة</span>';
                        } else if ($row->priority === 'high') {
                            return '<span class="badge bg-danger" style="' . $style . '">مرتفعة</span>';
                        }
                        return $row->priority;
                    })
                    ->editColumn('task_field', function ($row) {
                        return '<span style="font-size:10px;" class="fw-bold badge rounded-pill bg-gradient-light text-dark px-3 py-1">
                                   ' . e($row->task_field_in_arabic) . '
                                </span>';
                    })


                    // المتبقي  و التاخير
                    ->addColumn('remaining_days', function ($row) {

                        if ($row->status == 'completed') {
                            return $row->duration;
                        } elseif ($row->steps->contains('status', 'rejected')) {
                            return '<span style="font-size:10px;" class="badge bg-danger">تم رفض الاعتماد</span>';
                        } else {


                            // تحديد تواريخ البداية والاستحقاق والوقت الحالي
                            $start = $row->created_at; // يمكن تغييره إذا كان هناك حقل بداية محدد
                            $due = \Carbon\Carbon::parse($row->due_date . ' ' . $row->due_time);
                            $now = \Carbon\Carbon::now();

                            // حساب المدة الكلية (بالثواني) بين البداية والاستحقاق
                            $totalDuration = $start->diffInSeconds($due);
                            // حساب الزمن المنقضي (بالثواني) من البداية وحتى الآن
                            $elapsed = $start->diffInSeconds($now);

                            if ($now->lessThanOrEqualTo($due)) {
                                // إذا لم ينقض الموعد
                                $progressRatio = $totalDuration > 0 ? $elapsed / $totalDuration : 0;
                                $progressWidthPercent = $progressRatio * 100;
                                $diff = $now->diff($due);
                                $daysRemaining = $diff->d;
                                $hoursRemaining = $diff->h;
                                $minutesRemaining = $diff->i;
                                $timeText = "{$daysRemaining} يوم, {$hoursRemaining} ساعة, {$minutesRemaining} دقيقة ";
                                $barClass = 'bg-success';
                                $statusClass = 'text-success';
                            } else {
                                // إذا تجاوز الموعد
                                $progressWidthPercent = 100;
                                $diff = $due->diff($now);
                                $daysOverdue = $diff->d;
                                $hoursOverdue = $diff->h;
                                $minutesOverdue = $diff->i;
                                $timeText = "-{$daysOverdue} يوم, {$hoursOverdue} ساعة, {$minutesOverdue} دقيقة ";
                                $barClass = 'bg-danger';
                                $statusClass = 'text-danger';
                            }

                            // إنشاء شريط التقدم HTML
                            $progressHtml = '
                            <div class="text-center">
                                <div class="progress" style="height: 10px; width: 110px;">
                                    <div class="progress-bar ' . $barClass . '"
                                         role="progressbar"
                                         style="width: ' . $progressWidthPercent . '%;"
                                         aria-valuenow="' . $progressWidthPercent . '"
                                         aria-valuemin="0"
                                         aria-valuemax="100">
                                    </div>
                                </div>
                                <small style="font-size: 10px;" class="' . $statusClass . '">' . $timeText . '</small>
                            </div>
                        ';

                            return $progressHtml;
                        }
                    })

                    // الحالة
                    ->editColumn('status', function ($row) {
                        $statuses = [
                            'pending' => ['class' => 'bg-secondary', 'text' => 'قيد الانتظار'],
                            'in_progress' => ['class' => 'bg-primary', 'text' => 'قيد التنفيذ'],
                            'completed' => ['class' => 'bg-success', 'text' => 'مكتملة'],
                            'cancel_completion' => ['class' => 'bg-danger', 'text' => 'ملغاة']
                        ];
                        $status = $statuses[$row->status] ?? ['class' => 'bg-secondary', 'text' => $row->status];
                        if ($row->steps->contains('status', 'rejected')) {
                            return '<span style="font-size:10px;" class="badge bg-danger">تم رفض الاعتماد</span>';
                        } else {
                            return '<span style="font-size:10px;" class="badge ' . $status['class'] . '">' . $status['text'] . '</span>';
                        }
                    })

                    // الخطوات
                    ->addColumn('steps_data', function ($row) {
                        $stepsHtml = '<div class="nested-steps-container p-3">';
                        $stepsHtml .= '<table class="table table-bordered nested-steps-table w-100">';
                        $stepsHtml .= '<thead><tr>';
                        $stepsHtml .= '<th>رقم الخطوة</th>';
                        $stepsHtml .= '<th>وصف الخطوة</th>';
                        $stepsHtml .= '<th>الحالة</th>';
                        $stepsHtml .= '<th>تاريخ البدء</th>';
                        $stepsHtml .= '<th>تاريخ الانتهاء</th>';
                        $stepsHtml .= '<th>المدة</th>';
                        $stepsHtml .= '</tr></thead><tbody>';

                        foreach ($row->steps->sortBy('step_order') as $index => $step) {
                            $statusClass = $this->getStatusClass($step->status);
                            $statusText = $this->getStatusText($step->status);
                            $stepsHtml .= '<tr>';

                            $stepsHtml .= '<td><small>' . ($index + 1) . '</small></td>';
                            $stepsHtml .= '<td><small>' . $step->name . '</small></td>';
                            $stepsHtml .= '<td><small><span class="badge ' . $statusClass . '">' . $statusText . '</span></small></td>';
                            $stepsHtml .= '<td><small>' . ($step->step_start_date ? $step->step_start_date->format('Y-m-d H:i:s') : '-') . '</small></td>';
                            $stepsHtml .= '<td><small>' . ($step->step_end_date ? $step->step_end_date->format('Y-m-d H:i:s') : '-') . '</small></td>';
                            $stepsHtml .= '<td><small>' . ($step->duration ?: '-') . '</small></td>';
                            $stepsHtml .= '</tr>';
                        }

                        $stepsHtml .= '</tbody></table></div>';
                        return $row->steps->count() > 0 ? $stepsHtml : '';
                    })

                    // اضيفة بواسطة
                    ->addColumn('created_by', function ($row) {
                        // 1. احصل على الاسم الكامل أو القيمة الافتراضية
                        $fullName = $row->createdBy && $row->createdBy->employee
                            ? $row->createdBy->employee->name
                            : 'غير محدد';

                        // 2. قص إلى 10 حروف مع "..."
                        $truncated = Str::limit($fullName, 10, '...');

                        // 3. أرجع span مع title للاسم الكامل
                        return '<span title="' . e($fullName) . '">' . $truncated . '</span>';
                    })

                    // المكلفين
                    ->addColumn('assigned_users', function ($row) {
                        // التحقق من وجود مستخدمين مكلفين
                        $assignedUsers = $row->assignedUsers;

                        if ($assignedUsers->isEmpty()) {
                            return '<span class="badge bg-secondary">غير محدد</span>';
                        }

                        // تحديد عدد الصور المعروضة
                        $displayCount = min(4, $assignedUsers->count());
                        $remaining = $assignedUsers->count() - $displayCount;

                        // بناء HTML للمستخدمين
                        $usersHtml = '<div class="d-flex align-items-center">';

                        // عرض الصور
                        for ($i = 0; $i < $displayCount; $i++) {
                            $assignee = $assignedUsers[$i];
                            $profilePicture = $assignee->employee && $assignee->employee->profile_picture
                                ? asset('storage/' . $assignee->employee->profile_picture)
                                : asset('assets/img/avatars/1.png');

                            $usersHtml .= '
                                <img class="avatar avatar-task rounded-circle" style="width: 30px; height: 30px;"
                                     src="' . $profilePicture . '"
                                     alt="' . e($assignee->employee->name) . '"
                                     title="' . e($assignee->employee->name) . '" >';
                        }

                        // إضافة العدد المتبقي إذا وجد
                        if ($remaining > 0) {
                            $usersHtml .= '
                                <div class="remaining-avatars"
                                     data-bs-toggle="popover"
                                     data-bs-trigger="hover"
                                     data-bs-html="true"
                                     data-bs-content="' . $remaining . ' مستخدم إضافي"
                                     style="width: 30px; height: 30px; border-radius: 50%;  margin-left: -16px; background: #ccc; display: flex; align-items: center; justify-content: center; font-size: 12px; cursor: pointer;">
                                    +' . $remaining . '
                                </div>';
                        }

                        $usersHtml .= '</div>';

                        return $usersHtml;
                    })


                    ->addColumn('action', function ($row) {
                        $restoreUrl = route('organization-center.tasks.restore', $row->id);
                        $forceDeleteUrl = route('organization-center.tasks.forceDelete', $row->id);
                        return '
                        <a href="javascript:void(0);" onclick="confirmRestore(' . $row->id . ')" class="btn btn-sm text-success"><i class="ti ti-rotate"></i> استعادة</a>
                        <a href="javascript:void(0);" onclick="confirmForceDelete(' . $row->id . ')" class="btn btn-sm text-danger"><i class="ti ti-trash"></i> حذف نهائي</a>
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
                    ->rawColumns(['created_by', 'task_name', 'task_field', 'priority', 'remaining_days', 'status', 'steps_data', 'assigned_users', 'action'])
                    ->make(true);
            }

            // الحصول على الموظف الحالي إذا كان مرتبطًا
            $currentEmployee = auth()->user();

            $totalTasks = Task::onlyTrashed()->count();
            $completedTasks = Task::onlyTrashed()->where('status', 'completed')->count();
            $inProgressTasks = Task::onlyTrashed()->where('status', 'in_progress')->count();
            $pendingTasks = Task::onlyTrashed()->where('status', 'pending')->count();

            // الحصول على جميع الموظفين
            $users = User::select('id', 'name')->get();
            return view('tasks.trashed', compact(
                'currentEmployee',
                'users',
                'totalTasks',
                'completedTasks',
                'inProgressTasks',
                'pendingTasks',
            ));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    }

    public function restore($id)
    {
        try {
            $offer = Task::onlyTrashed()->findOrFail($id);

            // استعادة العرض
            $offer->restore();

            return redirect()->route('organization-center.tasks.trashed')->with('success', 'تم استعادة المهمة بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء استعادة المهمة. يرجى المحاولة لاحقاً.');
        }
    }


    public function forceDelete($id)
    {
        DB::beginTransaction();

        try {
            // جلب المهمة المحذوفة
            $task = Task::onlyTrashed()->findOrFail($id);

            // foreach ($task->assignees as $assignee) {
            //     BatchDeleteMicrosoftTaskJob::dispatchSync($task, $assignee);
            // }


            // حذف المرفقات إن وجدت
            if ($task->creator_attachment) {
                Storage::disk('public')->delete($task->creator_attachment);
            }

            // حذف المهمة نهائياً
            $task->forceDelete();

            DB::commit();

            return redirect()->route('organization-center.tasks.trashed')
                ->with('success', 'تم حذف المهمة نهائياً بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            // Log::error('خطأ أثناء حذف المهمة نهائياً: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء حذف المهمة نهائياً. يرجى المحاولة لاحقاً.');
        }
    }



    public function deleteAttachment($attachmentId)
    {
        try {
            $attachment = TaskAttachment::findOrFail($attachmentId);

            // حذف الملف من التخزين
            if (Storage::disk('public')->exists($attachment->file_path)) {
                Storage::disk('public')->delete($attachment->file_path);
            }

            // حذف سجل المرفق من قاعدة البيانات
            $attachment->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف المرفق بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف المرفق: ' . $e->getMessage()
            ], 500);
        }
    }








    /*
    |============================================================================
    |============================================================================
    |                               Private methods
    |============================================================================
    |============================================================================
    */
    protected function recordTaskEvent(Task $task, $eventType, $message)
    {
        return TaskEvent::create([
            'task_id'       => $task->id,
            'user_id'       => auth()->id(),
            'event_type'    => $eventType,
            'message'       => $message
        ]);
    }
}
