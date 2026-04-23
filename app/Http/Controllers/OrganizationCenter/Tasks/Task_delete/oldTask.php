<?php

namespace App\Http\Controllers\Tasks;

use App\DataTables\Task\TasksDataTable;
use App\Http\Controllers\Controller;
use App\Jobs\StepsTask\SyncStepsTaskWithMicrosoftJob;
use App\Jobs\Tasks\SyncTaskWithMicrosoftJob;
use App\Jobs\Tasks\BatchDeleteMicrosoftTaskJob;
use App\Jobs\Tasks\BatchUpdateMicrosoftTaskJob;
use App\Jobs\Tasks\Email\SendStepCompletionEmailJob;
use App\Models\GeneralSetting\SystemSetting\Settings;
use App\Models\Task\Task;
use App\Models\Task\TaskEvent;
use App\Models\Task\TaskStep;
use App\Models\Task\TaskStepEvent;
use App\Models\TaskAttachment;
use App\Models\User;
use App\Services\OrganizationCenter\Tasks\Task\Helper\TaskFormatterService;
use App\Services\OrganizationCenter\Tasks\Task\TaskService;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;


class TaskController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | service of Task
    |--------------------------------------------------------------------------
    | للتعامل مع المهام في مايكروسوفت
    */
    protected $taskFormatter;
    protected $taskService;



    /*
    |--------------------------------------------------------------------------
    | constructor
    |--------------------------------------------------------------------------
    */
    public function __construct(TaskService $taskService, TaskFormatterService $taskFormatter)
    {
        // لتهيئة بيانات المهمة لارسالها لمايكروسوفت
        $this->taskFormatter = $taskFormatter;

        $this->taskService = $taskService;
        $this->middleware('can:كل المهام')->only(['index']);
        $this->middleware('can:المهام الخاصة بي')->only(['myTasks']);
        $this->middleware('can:مهام مسندة للأخرين')->only(['assignedTasks']);
        $this->middleware('can:ارشيف المهام')->only(['trashed']);
    }




    /*
    |--------------------------------------------------------------------------
    | index Task
    |--------------------------------------------------------------------------
    */

    // public function index(TasksDataTable $dataTable, Request $request)
    // {
    //     try {
    //         if ($request->ajax()) return $dataTable->ajax();

    //         // Statistics
    //         $statusCounts = Task::query()
    //             ->select('status')
    //             ->selectRaw('COUNT(*) as count')
    //             ->groupBy('status')
    //             ->pluck('count', 'status')
    //             ->toArray();



    //         // $totalAdvance = array_sum($statusCounts);
    //         // $pendingAdvance  = $statusCounts[AdvanceStatus::Pending->value] ?? 0;
    //         // $approvedAdvance = $statusCounts[AdvanceStatus::Approved->value] ?? 0;
    //         // $rejectedAdvance = $statusCounts[AdvanceStatus::Rejected->value] ?? 0;

    //         // // Filters
    //         // $employees        = Employees::active()->select('id', 'name', 'nickname')->get();
    //         // $advanceTypes     = AdvanceType::options();
    //         // $advanceStatus    = AdvanceStatus::options();
    //         return $dataTable->render('tasks.index');
    //     } catch (\Exception $e) {
    //         return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
    //     }
    // }

    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $tasks = Task::with(['steps' => function ($query) {
                    $query->orderBy('step_order'); // تغيير من 'order' إلى 'step_order'
                }])->select([
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

                    // حالة الاستكمال
                    ->addColumn('complete_checkbox', function ($row) {
                        // الحصول على معرف المستخدم الحالي
                        $userId = auth()->id();
                        // التأكد من أن المهمة تحتوي على علاقة assignedUsers وأن المستخدم موجود ضمنها
                        if (!$row->assignedUsers->contains('id', $userId)) {
                            return '
                                <input title="عفوا ليس لديك صلاحيات اكمال او الغاء اكمال هذه المهمة"
                                    class="row-checkbox disable-checkbox bg-danger border border-border-danger"
                                    type="checkbox"
                                    disabled
                                >
                            ';
                        }

                        // بناء HTML زر الاستكمال إذا كان المستخدم مكلفاً
                        $checked = $row->status === 'completed' ? 'checked' : '';
                        return '
                                <input
                                    class="row-checkbox task-complete-checkbox"
                                    type="checkbox"
                                    data-task-id="' . $row->id . '"
                                    ' . $checked . '
                                >
                        ';
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
                        $stepsHtml .= '<th>اكمال الخطوة</th>';
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
                            $userId = auth()->id();
                            $stepsHtml .= '<tr>';
                            if (!$step->assignedUsers->contains('id', $userId)) {
                                $checkboxHtml  = '<small>ليس لديك صلاحيات</small>';
                                $stepsHtml .= '<td><small>' . $checkboxHtml . '</small></td>';
                            } elseif ($step->needs_approval) {
                                $approvalHtml  = '<div class="step-approval-container small">';
                                $approvalHtml .= '<button class="btn btn-sm btn-primary step-approval-btn mb-2 w-100" style="font-size:12px;" data-step-id="' . $step->id . '" data-action="approve" ' . ($step->status === 'approved' ? 'disabled' : '') . '>';
                                $approvalHtml .= '<small>اعتماد</small>';
                                $approvalHtml .= '</button>';
                                $approvalHtml .= '<button class="btn btn-sm btn-secondary step-approval-btn w-100" style="font-size:12px;" data-step-id="' . $step->id . '" data-action="reject" ' . ($step->status === 'rejected' ? 'disabled' : '') . '>';
                                $approvalHtml .= '<small>رفض</small>';
                                $approvalHtml .= '</button>';
                                $approvalHtml .= '</div>';
                                $stepsHtml .= '<td><small>' . $approvalHtml . '</small></td>';
                            } else {
                                $checkboxHtml  = '<div class="px-1   custom-checkbox"><small>';
                                $checkboxHtml .= '<input class="form-check-input custom-item step-complete-checkbox" type="checkbox" name="step_complete" id="step_complete_' . $step->id . '" title="إكمال الخطوة" ' . (in_array($step->status, ['completed', 'approved']) ? 'checked' : '') . ' data-step-id="' . $step->id . '">';
                                $checkboxHtml .= '</small></div>';
                                $stepsHtml .= '<td>' . $checkboxHtml . '</td>';
                            }
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

                    // في ملف TaskController.php - تحديث دالة إضافة عمود action
                    ->addColumn('action', function ($row) {
                        if ($row->status === 'completed') {
                            return '<span class="text-muted">مكتملة </span>';
                        } elseif ($row->steps->contains('status', 'rejected')) {
                            return '<span class="text-muted">تم رفض الاعتماد - لا يمكن التعديل أو الحذف</span>';
                        }

                        $attachmentsHtml = '';
                        if ($row->attachments && $row->attachments->count() > 0) {
                            $attachmentsHtml .= '<div class="current-files-list">';
                            foreach ($row->attachments as $attachment) {
                                $attachmentsHtml .= '
                                <div class="attachment-item d-flex justify-content-between align-items-center p-2 mb-2 border rounded">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-file me-2"></i>
                                        <a href="' . asset("storage/" . $attachment->file_path) . '" target="_blank" class="text-decoration-none">
                                            ' . $attachment->file_name . '
                                        </a>
                                        <small class="text-muted ms-2">(' . $attachment->formatted_file_size . ')</small>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-sm btn-danger remove-attachment-btn"
                                                data-attachment-id="' . $attachment->id . '"
                                                onclick="removeAttachment(' . $attachment->id . ', ' . $row->id . ')">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>';
                            }
                            $attachmentsHtml .= '</div>';
                        } else {
                            $attachmentsHtml .= '<p class="text-muted">لا توجد مرفقات حالية.</p>';
                        }
                        $editOffcanvas = '
                                        <div class="offcanvas offcanvas-end" tabindex="-1" id="editTaskOffcanvas' . $row->id . '"
                                            aria-labelledby="editTaskOffcanvasLabel' . $row->id . '" style="width: 600px;">
                                            <div class="offcanvas-header d-flex justify-content-between" dir="ltr">
                                                <h5 id="editTaskOffcanvasLabel' . $row->id . '" class="order-2">تعديل المهمة</h5>
                                                <button type="button" class="btn-close m-0 text-reset order-1" data-bs-dismiss="offcanvas" aria-label="إغلاق"></button>
                                            </div>
                                            <div class="offcanvas-body">
                                                <form id="editTaskForm' . $row->id . '" class="needs-validation" novalidate>
                                                    ' . csrf_field() . '
                                                    <div class="row">
                                                        <!-- عنوان المهمة -->
                                                        <div class="col-6 mb-3">
                                                            <label class="form-label">عنوان المهمة</label>
                                                            <input type="text" class="form-control" name="task_name" value="' . $row->task_name . '" required>
                                                            <div class="invalid-feedback">عنوان المهمة مطلوب</div>
                                                        </div>

                                                        <!-- تاريخ الاستحقاق -->
                                                        <div class="col-6 mb-3">
                                                            <label class="form-label">تاريخ الاستحقاق</label>
                                                            <input type="datetime-local" class="form-control" name="due_date"
                                                                value="' . \Carbon\Carbon::parse($row->due_date . " " . $row->due_time)->format("Y-m-d\TH:i") . '" required>
                                                            <div class="invalid-feedback">تاريخ الاستحقاق مطلوب</div>
                                                        </div>

                                                        <!-- المكلفين -->
                                                        <div class="mb-3">
                                                            <div class="mb-2">
                                                                <label class="form-label">مكلف بها</label>
                                                                <button type="button" class="btn btn-outline-secondary btn-sm mx-2"
                                                                        id="assign-myself-btn-edit-' . $row->id . '">
                                                                    لنفسي
                                                                </button>
                                                            </div>
                                                            <select name="assigned_user_ids[]" class="form-select select2" multiple required
                                                                    data-placeholder="اختر الموظفين" id="assigned_user_id_edit' . $row->id . '">
                                                                ' . $this->getUserOptionsHtml($row) . '
                                                            </select>
                                                            <div class="invalid-feedback">الرجاء اختيار موظف واحد على الأقل</div>
                                                        </div>

                                                        <!-- الأولوية -->
                                                        <div class="mb-3">
                                                            <label class="form-label">أولوية المهمة</label>
                                                            <div class="d-flex align-items-start" style="gap: 10px;">
                                                                <div class="d-flex align-items-center" style="gap: 5px;">
                                                                    <input class="form-check-input" type="radio" name="priority" value="high"
                                                                        ' . ($row->priority === "high" ? "checked" : "") . ' required>
                                                                    <span style="background-color: red; width: 15px; height: 15px; border-radius: 50%;"></span>
                                                                    <span>مرتفعة</span>
                                                                </div>
                                                                <div class="d-flex align-items-center" style="gap: 5px;">
                                                                    <input class="form-check-input" type="radio" name="priority" value="medium"
                                                                        ' . ($row->priority === "medium" ? "checked" : "") . ' required>
                                                                    <span style="background-color: orange; width: 15px; height: 15px; border-radius: 50%;"></span>
                                                                    <span>متوسطة</span>
                                                                </div>
                                                                <div class="d-flex align-items-center" style="gap: 5px;">
                                                                    <input class="form-check-input" type="radio" name="priority" value="low"
                                                                        ' . ($row->priority === "low" ? "checked" : "") . ' required>
                                                                    <span style="background-color: green; width: 15px; height: 15px; border-radius: 50%;"></span>
                                                                    <span>منخفضة</span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- الوصف -->
                                                        <div class="mb-3">
                                                            <label class="form-label">وصف المهمة</label>
                                                            <textarea class="form-control" name="description" rows="4">' . $row->description . '</textarea>
                                                        </div>

                                                      <!-- المرفقات -->
                                    <div class="col-12 mb-3">
                                        <label class="form-label">المرفقات</label>

                                        <!-- عرض المرفقات الحالية -->
                                        <div id="current-attachments-' . $row->id . '" class="mb-3">
                                            ' . $attachmentsHtml . '
                                        </div>

                                        <!-- إضافة مرفقات جديدة -->
                                        <div class="new-attachments-container">
                                            <div class="input-group">
                                                <input type="file" class="form-control" name="new_attachments[]" multiple
                                                       id="new-attachments-' . $row->id . '"
                                                       accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx">
                                                <button type="button" class="btn btn-secondary" id="clear-new-attachments-' . $row->id . '">
                                                    <i class="fas fa-times"></i> مسح
                                                </button>
                                            </div>
                                            <small class="form-text text-muted">يمكنك اختيار عدة ملفات دفعة واحدة</small>

                                            <!-- قائمة الملفات المختارة حديثًا -->
                                            <div id="new-attachments-list-' . $row->id . '" class="mt-2"></div>
                                        </div>
                                    </div>


                                                        <!-- خيار الخطوات -->
                                                        <div class="col-4 mb-3">
                                                            <label for="hasSteps_edit{{ $item->id }}" class="form-label">المهمة
                                                                    تحتوي
                                                                    على خطوات</label>
                                                            <div class="form-check form-switch">
                                                                <input type="checkbox" class="form-check-input" id="hasSteps_edit' . $row->id . '"
                                                                    name="hasSteps" ' . ($row->steps->count() > 0 ? "checked" : "") . '>
                                                                <label for="hasSteps_edit' . $row->id . '" class="form-check-label mx-2">إضافة خطوات</label>
                                                            </div>
                                                        </div>

                                                        <!-- حاوية الخطوات -->
                                                        <div id="stepsContainer_edit' . $row->id . '"
                                                            style="display: ' . ($row->steps->count() > 0 ? "block" : "none") . '">
                                                            ' . $this->getStepsHtml($row) . '
                                                            <button type="button" class="btn btn-secondary btn-sm"
                                                                    id="addStepButton_edit' . $row->id . '">
                                                                إضافة خطوة
                                                            </button>
                                                        </div>

                                                        <!-- أزرار التحكم -->
                                                        <div class="d-flex justify-content-end" style="margin-top: 100px">
                                                            <button type="button" class="btn btn-secondary me-2"
                                                                    data-bs-dismiss="offcanvas">إلغاء</button>
                                                            <button type="button" class="btn btn-primary"
                                                                    onclick="updateTask(' . $row->id . ')">حفظ التعديلات</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>';


                        return '<div class="d-flex gap-2">' .
                            // التحقق مما إذا كان المستخدم هو منشئ المهمة
                            ($row->created_by !== auth()->id()
                                ? '<span class="text-muted">ليس لديك صلاحيات</span>'
                                : (
                                    // إذا كان المستخدم هو المنشئ، نتحقق من وجود صلاحية التعديل
                                    (auth()->user()->can("تعديل مهمة")
                                        ? '<button class="btn btn-sm text-secondary" data-task-id="' . $row->id . '" title="تعديل المهمة" data-bs-toggle="offcanvas" data-bs-target="#editTaskOffcanvas' . $row->id . '">
                                                        <i class="ti ti-edit"></i>
                                                    </button>'
                                        : ''
                                    ) .
                                    // ونتحقق أيضاً من وجود صلاحية الحذف
                                    (auth()->user()->can("حذف مهمة")
                                        ? '<button onclick="confirmDeleteTask(' . $row->id . ')" class="btn btn-sm text-secondary">
                                                        <i class="ti ti-trash"></i>
                                                    </button>'
                                        : ''
                                    )
                                )
                            ) .
                            '</div>' . $editOffcanvas;
                    })
                    ->rawColumns(['complete_checkbox', 'created_by', 'task_name', 'task_field', 'priority', 'remaining_days', 'status', 'steps_data', 'assigned_users', 'action'])
                    ->make(true);
            }

            $currentEmployee = auth()->user();

            $totalTasks = Task::count();
            $completedTasks = Task::where('status', 'completed')->count();
            $inProgressTasks = Task::where('status', 'in_progress')->count();
            $pendingTasks = Task::where('status', 'pending')->count();

            // الحصول على جميع الموظفين
            $users = User::select('id', 'name')->get();
            return view('tasks.index', compact(
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



    /*
    |--------------------------------------------------------------------------
    | my tasks
    |--------------------------------------------------------------------------
    */
    public function myTasks(Request $request)
    {
        $userId = auth()->id();

        try {
            if ($request->ajax()) {
                $tasks = Task::with([
                    'steps' => function ($query) {
                        $query->where('status', '!=', 'pending')
                            ->orderBy('step_order');
                    },
                    'assignedUsers'  // إضافة العلاقة assignedUsers
                ])
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
                    ])
                    ->where(function ($query) use ($userId) {
                        $query->whereHas('assignedUsers', function ($subQuery) use ($userId) {
                            $subQuery->where('users.id', $userId);
                        })
                            ->orWhereHas('steps', function ($subQuery) use ($userId) {
                                $subQuery->where('status', '!=', 'pending')
                                    ->whereHas('assignedUsers', function ($stepQuery) use ($userId) {
                                        $stepQuery->where('users.id', $userId);
                                    });
                            });
                    });



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

                    // حالة الاستكمال
                    ->addColumn('complete_checkbox', function ($row) {
                        // الحصول على معرف المستخدم الحالي
                        $userId = auth()->id();
                        // التأكد من أن المهمة تحتوي على علاقة assignedUsers وأن المستخدم موجود ضمنها
                        if (!$row->assignedUsers->contains('id', $userId)) {
                            return '
                                <input title="عفوا ليس لديك صلاحيات اكمال او الغاء اكمال هذه المهمة"
                                    class="row-checkbox disable-checkbox bg-danger border border-border-danger"
                                    type="checkbox"
                                    disabled
                                >
                            ';
                        }

                        // بناء HTML زر الاستكمال إذا كان المستخدم مكلفاً
                        $checked = $row->status === 'completed' ? 'checked' : '';
                        return '
                                <input
                                    class="row-checkbox task-complete-checkbox"
                                    type="checkbox"
                                    data-task-id="' . $row->id . '"
                                    ' . $checked . '
                                >
                        ';
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
                        $userId = auth()->id(); // الحصول على معرف المستخدم الحالي

                        $filteredSteps = $row->steps->filter(function ($step) use ($userId) {
                            return $step->assignedUsers->contains('id', $userId);
                        });

                        // إذا لم يكن المستخدم مكلفًا بأي خطوة، لا يتم عرض أي شيء
                        if ($filteredSteps->isEmpty()) {
                            return '';
                        }

                        $stepsHtml = '<div class="nested-steps-container p-3">';
                        $stepsHtml .= '<table class="table table-bordered nested-steps-table w-100">';
                        $stepsHtml .= '<thead><tr>';
                        $stepsHtml .= '<th>اكمال الخطوة</th>';
                        $stepsHtml .= '<th>رقم الخطوة</th>';
                        $stepsHtml .= '<th>وصف الخطوة</th>';
                        $stepsHtml .= '<th>الحالة</th>';
                        $stepsHtml .= '<th>تاريخ البدء</th>';
                        $stepsHtml .= '<th>تاريخ الانتهاء</th>';
                        $stepsHtml .= '<th>المدة</th>';
                        $stepsHtml .= '</tr></thead><tbody>';

                        foreach ($filteredSteps->sortBy('step_order') as $index => $step) {
                            $statusClass = $this->getStatusClass($step->status);
                            $statusText = $this->getStatusText($step->status);
                            $userId = auth()->id();
                            $stepsHtml .= '<tr>';
                            if (!$step->assignedUsers->contains('id', $userId)) {
                                $checkboxHtml  = '<small>ليس لديك صلاحيات</small>';
                                $stepsHtml .= '<td><small>' . $checkboxHtml . '</small></td>';
                            } elseif ($step->needs_approval) {
                                $approvalHtml  = '<div class="step-approval-container small">';
                                $approvalHtml .= '<button class="btn btn-sm btn-primary step-approval-btn mb-2 w-100" style="font-size:12px;" data-step-id="' . $step->id . '" data-action="approve" ' . ($step->status === 'approved' ? 'disabled' : '') . '>';
                                $approvalHtml .= '<small>اعتماد</small>';
                                $approvalHtml .= '</button>';
                                $approvalHtml .= '<button class="btn btn-sm btn-secondary step-approval-btn w-100" style="font-size:12px;" data-step-id="' . $step->id . '" data-action="reject" ' . ($step->status === 'rejected' ? 'disabled' : '') . '>';
                                $approvalHtml .= '<small>رفض</small>';
                                $approvalHtml .= '</button>';
                                $approvalHtml .= '</div>';
                                $stepsHtml .= '<td><small>' . $approvalHtml . '</small></td>';
                            } else {
                                $checkboxHtml  = '<div class="px-1   custom-checkbox"><small>';
                                $checkboxHtml .= '<input class="form-check-input custom-item step-complete-checkbox" type="checkbox" name="step_complete" id="step_complete_' . $step->id . '" title="إكمال الخطوة" ' . (in_array($step->status, ['completed', 'approved']) ? 'checked' : '') . ' data-step-id="' . $step->id . '">';
                                $checkboxHtml .= '</small></div>';
                                $stepsHtml .= '<td>' . $checkboxHtml . '</td>';
                            }
                            $stepsHtml .= '<td><small>' . ($index + 1) . '</small></td>';
                            $stepsHtml .= '<td><small>' . $step->name . '</small></td>';
                            $stepsHtml .= '<td><small><span class="badge ' . $statusClass . '">' . $statusText . '</span></small></td>';
                            $stepsHtml .= '<td><small>' . ($step->step_start_date ? $step->step_start_date->format('Y-m-d H:i:s') : '-') . '</small></td>';
                            $stepsHtml .= '<td><small>' . ($step->step_end_date ? $step->step_end_date->format('Y-m-d H:i:s') : '-') . '</small></td>';
                            $stepsHtml .= '<td><small>' . ($step->duration ?: '-') . '</small></td>';
                            $stepsHtml .= '</tr>';
                        }

                        $stepsHtml .= '</tbody></table></div>';
                        return $stepsHtml;
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

                    // في ملف TaskController.php - تحديث دالة إضافة عمود action
                    ->addColumn('action', function ($row) {
                        if ($row->status === 'completed') {
                            return '<span class="text-muted">مكتملة </span>';
                        } elseif ($row->steps->contains('status', 'rejected')) {
                            return '<span class="text-muted">تم رفض الاعتماد - لا يمكن التعديل أو الحذف</span>';
                        }
                        // تحقق مما إذا كان المستخدم الحالي هو من أنشأ المهمة
                        if ($row->created_by != auth()->id()) {
                            return '<span class="text-muted">غير مصرح بالتعديل أو الحذف</span>';
                        }
                        $editOffcanvas = '
                                    <div class="offcanvas offcanvas-end" tabindex="-1" id="editTaskOffcanvas' . $row->id . '"
                                        aria-labelledby="editTaskOffcanvasLabel' . $row->id . '" style="width: 600px;">
                                        <div class="offcanvas-header d-flex justify-content-between" dir="ltr">
                                            <h5 id="editTaskOffcanvasLabel' . $row->id . '" class="order-2">تعديل المهمة</h5>
                                            <button type="button" class="btn-close m-0 text-reset order-1" data-bs-dismiss="offcanvas" aria-label="إغلاق"></button>
                                        </div>
                                        <div class="offcanvas-body">
                                            <form id="editTaskForm' . $row->id . '" class="needs-validation" novalidate>
                                                ' . csrf_field() . '
                                                <div class="row">
                                                    <!-- عنوان المهمة -->
                                                    <div class="col-6 mb-3">
                                                        <label class="form-label">عنوان المهمة</label>
                                                        <input type="text" class="form-control" name="task_name" value="' . $row->task_name . '" required>
                                                        <div class="invalid-feedback">عنوان المهمة مطلوب</div>
                                                    </div>

                                                    <!-- تاريخ الاستحقاق -->
                                                    <div class="col-6 mb-3">
                                                        <label class="form-label">تاريخ الاستحقاق</label>
                                                        <input type="datetime-local" class="form-control" name="due_date"
                                                            value="' . \Carbon\Carbon::parse($row->due_date . " " . $row->due_time)->format("Y-m-d\TH:i") . '" required>
                                                        <div class="invalid-feedback">تاريخ الاستحقاق مطلوب</div>
                                                    </div>

                                                    <!-- المكلفين -->
                                                    <div class="mb-3">
                                                        <div class="mb-2">
                                                            <label class="form-label">مكلف بها</label>
                                                            <button type="button" class="btn btn-outline-secondary btn-sm mx-2"
                                                                    id="assign-myself-btn-edit-' . $row->id . '">
                                                                لنفسي
                                                            </button>
                                                        </div>
                                                        <select name="assigned_user_ids[]" class="form-select select2" multiple required
                                                                data-placeholder="اختر الموظفين" id="assigned_user_id_edit' . $row->id . '">
                                                            ' . $this->getUserOptionsHtml($row) . '
                                                        </select>
                                                        <div class="invalid-feedback">الرجاء اختيار موظف واحد على الأقل</div>
                                                    </div>

                                                    <!-- الأولوية -->
                                                    <div class="mb-3">
                                                        <label class="form-label">أولوية المهمة</label>
                                                        <div class="d-flex align-items-start" style="gap: 10px;">
                                                            <div class="d-flex align-items-center" style="gap: 5px;">
                                                                <input class="form-check-input" type="radio" name="priority" value="high"
                                                                    ' . ($row->priority === "high" ? "checked" : "") . ' required>
                                                                <span style="background-color: red; width: 15px; height: 15px; border-radius: 50%;"></span>
                                                                <span>مرتفعة</span>
                                                            </div>
                                                            <div class="d-flex align-items-center" style="gap: 5px;">
                                                                <input class="form-check-input" type="radio" name="priority" value="medium"
                                                                    ' . ($row->priority === "medium" ? "checked" : "") . ' required>
                                                                <span style="background-color: orange; width: 15px; height: 15px; border-radius: 50%;"></span>
                                                                <span>متوسطة</span>
                                                            </div>
                                                            <div class="d-flex align-items-center" style="gap: 5px;">
                                                                <input class="form-check-input" type="radio" name="priority" value="low"
                                                                    ' . ($row->priority === "low" ? "checked" : "") . ' required>
                                                                <span style="background-color: green; width: 15px; height: 15px; border-radius: 50%;"></span>
                                                                <span>منخفضة</span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- الوصف -->
                                                    <div class="mb-3">
                                                        <label class="form-label">وصف المهمة</label>
                                                        <textarea class="form-control" name="description" rows="4">' . $row->description . '</textarea>
                                                    </div>

                                                    <!-- المرفقات -->
                                                    <div class="col-8 mb-3">
                                                        <label class="form-label">مرفق</label>
                                                        <input type="file" class="form-control" name="attachment"
                                                            accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                                                        ' . ($row->attachment ? '
                                                        <div class="mt-2">
                                                            <a href="' . asset("storage/" . $row->attachment) . '" target="_blank">
                                                                عرض المرفق الحالي
                                                            </a>
                                                        </div>' : '') . '
                                                    </div>


                                                    <!-- خيار الخطوات -->
                                                    <div class="col-4 mb-3">
                                                        <label for="hasSteps_edit{{ $item->id }}" class="form-label">المهمة
                                                                تحتوي
                                                                على خطوات</label>
                                                        <div class="form-check form-switch">
                                                            <input type="checkbox" class="form-check-input" id="hasSteps_edit' . $row->id . '"
                                                                name="hasSteps" ' . ($row->steps->count() > 0 ? "checked" : "") . '>
                                                            <label for="hasSteps_edit' . $row->id . '" class="form-check-label mx-2">إضافة خطوات</label>
                                                        </div>
                                                    </div>

                                                    <!-- حاوية الخطوات -->
                                                    <div id="stepsContainer_edit' . $row->id . '"
                                                        style="display: ' . ($row->steps->count() > 0 ? "block" : "none") . '">
                                                        ' . $this->getStepsHtml($row) . '
                                                        <button type="button" class="btn btn-secondary btn-sm"
                                                                id="addStepButton_edit' . $row->id . '">
                                                            إضافة خطوة
                                                        </button>
                                                    </div>

                                                    <!-- أزرار التحكم -->
                                                    <div class="d-flex justify-content-end" style="margin-top: 100px">
                                                        <button type="button" class="btn btn-secondary me-2"
                                                                data-bs-dismiss="offcanvas">إلغاء</button>
                                                        <button type="button" class="btn btn-primary"
                                                                onclick="updateTask(' . $row->id . ')">حفظ التعديلات</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>';

                        return '<div class="d-flex gap-2">' .
                            // التحقق مما إذا كان المستخدم هو منشئ المهمة
                            ($row->created_by !== auth()->id()
                                ? '<span class="text-muted">ليس لديك صلاحيات</span>'
                                : (
                                    // إذا كان المستخدم هو المنشئ، نتحقق من وجود صلاحية التعديل
                                    (auth()->user()->can("تعديل مهمة")
                                        ? '<button class="btn btn-sm text-secondary" data-task-id="' . $row->id . '" title="تعديل المهمة" data-bs-toggle="offcanvas" data-bs-target="#editTaskOffcanvas' . $row->id . '">
                                                    <i class="ti ti-edit"></i>
                                                </button>'
                                        : ''
                                    ) .
                                    // ونتحقق أيضاً من وجود صلاحية الحذف
                                    (auth()->user()->can("حذف مهمة")
                                        ? '<button onclick="confirmDeleteTask(' . $row->id . ')" class="btn btn-sm text-secondary">
                                                    <i class="ti ti-trash"></i>
                                                </button>'
                                        : ''
                                    )
                                )
                            ) .
                            '</div>' . $editOffcanvas;
                    })
                    ->rawColumns(['created_by', 'complete_checkbox', 'task_name', 'task_field', 'priority', 'remaining_days', 'status', 'steps_data', 'assigned_users', 'action'])
                    ->make(true);
            }

            // الحصول على الموظف الحالي إذا كان مرتبطًا
            $currentEmployee = auth()->user();

            // دالة مساعدة لإعادة شرط التصفية للمكلفين سواء في المهمة مباشرة أو عبر الخطوات
            $assignedFilter = function ($query) use ($userId) {
                $query->whereHas('assignedUsers', function ($subQuery) use ($userId) {
                    $subQuery->where('users.id', $userId);
                });
            };

            $totalTasks = Task::where($assignedFilter)->count();

            $lowPriorityTasksCount = Task::where('priority', 'low')
                ->where($assignedFilter)
                ->count();

            $mediumPriorityTasksCount = Task::where('priority', 'medium')
                ->where($assignedFilter)
                ->count();

            $highPriorityTasksCount = Task::where('priority', 'high')
                ->where($assignedFilter)
                ->count();

            // الحصول على جميع الموظفين
            $users = User::select('id', 'name')->get();
            return view('tasks.my_tasks', compact(
                'currentEmployee',
                'users',
                'totalTasks',
                'lowPriorityTasksCount',
                'mediumPriorityTasksCount',
                'highPriorityTasksCount',
            ));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    }



    /*
    |--------------------------------------------------------------------------
    | my tasks
    |--------------------------------------------------------------------------
    */
    public function assignedTasks(Request $request)
    {
        $userId = auth()->id();

        try {
            if ($request->ajax()) {
                $tasks = Task::with(['steps'])->where('created_by', $userId)
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

                    // حالة الاستكمال
                    ->addColumn('complete_checkbox', function ($row) {
                        // الحصول على معرف المستخدم الحالي
                        $userId = auth()->id();
                        // التأكد من أن المهمة تحتوي على علاقة assignedUsers وأن المستخدم موجود ضمنها
                        if (!$row->assignedUsers->contains('id', $userId)) {
                            return '
                                <input title="عفوا ليس لديك صلاحيات اكمال او الغاء اكمال هذه المهمة"
                                    class="row-checkbox disable-checkbox bg-danger border border-border-danger"
                                    type="checkbox"
                                    disabled
                                >
                            ';
                        }

                        // بناء HTML زر الاستكمال إذا كان المستخدم مكلفاً
                        $checked = $row->status === 'completed' ? 'checked' : '';
                        return '
                                <input
                                    class="row-checkbox task-complete-checkbox"
                                    type="checkbox"
                                    data-task-id="' . $row->id . '"
                                    ' . $checked . '
                                >
                        ';
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
                        $userId = auth()->id(); // الحصول على معرف المستخدم الحالي

                        $filteredSteps = $row->steps->filter(function ($step) use ($userId) {
                            return $step->assignedUsers->contains('id', $userId);
                        });

                        // إذا لم يكن المستخدم مكلفًا بأي خطوة، لا يتم عرض أي شيء
                        if ($filteredSteps->isEmpty()) {
                            return '';
                        }

                        $stepsHtml = '<div class="nested-steps-container p-3">';
                        $stepsHtml .= '<table class="table table-bordered nested-steps-table w-100">';
                        $stepsHtml .= '<thead><tr>';
                        $stepsHtml .= '<th>اكمال الخطوة</th>';
                        $stepsHtml .= '<th>رقم الخطوة</th>';
                        $stepsHtml .= '<th>وصف الخطوة</th>';
                        $stepsHtml .= '<th>الحالة</th>';
                        $stepsHtml .= '<th>تاريخ البدء</th>';
                        $stepsHtml .= '<th>تاريخ الانتهاء</th>';
                        $stepsHtml .= '<th>المدة</th>';
                        $stepsHtml .= '</tr></thead><tbody>';

                        foreach ($filteredSteps->sortBy('step_order') as $index => $step) {
                            $statusClass = $this->getStatusClass($step->status);
                            $statusText = $this->getStatusText($step->status);
                            $userId = auth()->id();
                            $stepsHtml .= '<tr>';
                            if (!$step->assignedUsers->contains('id', $userId)) {
                                $checkboxHtml  = '<small>ليس لديك صلاحيات</small>';
                                $stepsHtml .= '<td><small>' . $checkboxHtml . '</small></td>';
                            } elseif ($step->needs_approval) {
                                $approvalHtml  = '<div class="step-approval-container small">';
                                $approvalHtml .= '<button class="btn btn-sm btn-primary step-approval-btn mb-2 w-100" style="font-size:12px;" data-step-id="' . $step->id . '" data-action="approve" ' . ($step->status === 'approved' ? 'disabled' : '') . '>';
                                $approvalHtml .= '<small>اعتماد</small>';
                                $approvalHtml .= '</button>';
                                $approvalHtml .= '<button class="btn btn-sm btn-secondary step-approval-btn w-100" style="font-size:12px;" data-step-id="' . $step->id . '" data-action="reject" ' . ($step->status === 'rejected' ? 'disabled' : '') . '>';
                                $approvalHtml .= '<small>رفض</small>';
                                $approvalHtml .= '</button>';
                                $approvalHtml .= '</div>';
                                $stepsHtml .= '<td><small>' . $approvalHtml . '</small></td>';
                            } else {
                                $checkboxHtml  = '<div class="px-1   custom-checkbox"><small>';
                                $checkboxHtml .= '<input class="form-check-input custom-item step-complete-checkbox" type="checkbox" name="step_complete" id="step_complete_' . $step->id . '" title="إكمال الخطوة" ' . (in_array($step->status, ['completed', 'approved']) ? 'checked' : '') . ' data-step-id="' . $step->id . '">';
                                $checkboxHtml .= '</small></div>';
                                $stepsHtml .= '<td>' . $checkboxHtml . '</td>';
                            }
                            $stepsHtml .= '<td><small>' . ($index + 1) . '</small></td>';
                            $stepsHtml .= '<td><small>' . $step->name . '</small></td>';
                            $stepsHtml .= '<td><small><span class="badge ' . $statusClass . '">' . $statusText . '</span></small></td>';
                            $stepsHtml .= '<td><small>' . ($step->step_start_date ? $step->step_start_date->format('Y-m-d H:i:s') : '-') . '</small></td>';
                            $stepsHtml .= '<td><small>' . ($step->step_end_date ? $step->step_end_date->format('Y-m-d H:i:s') : '-') . '</small></td>';
                            $stepsHtml .= '<td><small>' . ($step->duration ?: '-') . '</small></td>';
                            $stepsHtml .= '</tr>';
                        }

                        $stepsHtml .= '</tbody></table></div>';
                        return $stepsHtml;
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

                    // في ملف TaskController.php - تحديث دالة إضافة عمود action
                    ->addColumn('action', function ($row) {
                        if ($row->status === 'completed') {
                            return '<span class="text-muted">مكتملة </span>';
                        } elseif ($row->steps->contains('status', 'rejected')) {
                            return '<span class="text-muted">تم رفض الاعتماد - لا يمكن التعديل أو الحذف</span>';
                        }
                        // تحقق مما إذا كان المستخدم الحالي هو من أنشأ المهمة
                        if ($row->created_by != auth()->id()) {
                            return '<span class="text-muted">غير مصرح بالتعديل أو الحذف</span>';
                        }
                        $editOffcanvas = '
                                    <div class="offcanvas offcanvas-end" tabindex="-1" id="editTaskOffcanvas' . $row->id . '"
                                        aria-labelledby="editTaskOffcanvasLabel' . $row->id . '" style="width: 600px;">
                                        <div class="offcanvas-header d-flex justify-content-between" dir="ltr">
                                            <h5 id="editTaskOffcanvasLabel' . $row->id . '" class="order-2">تعديل المهمة</h5>
                                            <button type="button" class="btn-close m-0 text-reset order-1" data-bs-dismiss="offcanvas" aria-label="إغلاق"></button>
                                        </div>
                                        <div class="offcanvas-body">
                                            <form id="editTaskForm' . $row->id . '" class="needs-validation" novalidate>
                                                ' . csrf_field() . '
                                                <div class="row">
                                                    <!-- عنوان المهمة -->
                                                    <div class="col-6 mb-3">
                                                        <label class="form-label">عنوان المهمة</label>
                                                        <input type="text" class="form-control" name="task_name" value="' . $row->task_name . '" required>
                                                        <div class="invalid-feedback">عنوان المهمة مطلوب</div>
                                                    </div>

                                                    <!-- تاريخ الاستحقاق -->
                                                    <div class="col-6 mb-3">
                                                        <label class="form-label">تاريخ الاستحقاق</label>
                                                        <input type="datetime-local" class="form-control" name="due_date"
                                                            value="' . \Carbon\Carbon::parse($row->due_date . " " . $row->due_time)->format("Y-m-d\TH:i") . '" required>
                                                        <div class="invalid-feedback">تاريخ الاستحقاق مطلوب</div>
                                                    </div>

                                                    <!-- المكلفين -->
                                                    <div class="mb-3">
                                                        <div class="mb-2">
                                                            <label class="form-label">مكلف بها</label>
                                                            <button type="button" class="btn btn-outline-secondary btn-sm mx-2"
                                                                    id="assign-myself-btn-edit-' . $row->id . '">
                                                                لنفسي
                                                            </button>
                                                        </div>
                                                        <select name="assigned_user_ids[]" class="form-select select2" multiple required
                                                                data-placeholder="اختر الموظفين" id="assigned_user_id_edit' . $row->id . '">
                                                            ' . $this->getUserOptionsHtml($row) . '
                                                        </select>
                                                        <div class="invalid-feedback">الرجاء اختيار موظف واحد على الأقل</div>
                                                    </div>

                                                    <!-- الأولوية -->
                                                    <div class="mb-3">
                                                        <label class="form-label">أولوية المهمة</label>
                                                        <div class="d-flex align-items-start" style="gap: 10px;">
                                                            <div class="d-flex align-items-center" style="gap: 5px;">
                                                                <input class="form-check-input" type="radio" name="priority" value="high"
                                                                    ' . ($row->priority === "high" ? "checked" : "") . ' required>
                                                                <span style="background-color: red; width: 15px; height: 15px; border-radius: 50%;"></span>
                                                                <span>مرتفعة</span>
                                                            </div>
                                                            <div class="d-flex align-items-center" style="gap: 5px;">
                                                                <input class="form-check-input" type="radio" name="priority" value="medium"
                                                                    ' . ($row->priority === "medium" ? "checked" : "") . ' required>
                                                                <span style="background-color: orange; width: 15px; height: 15px; border-radius: 50%;"></span>
                                                                <span>متوسطة</span>
                                                            </div>
                                                            <div class="d-flex align-items-center" style="gap: 5px;">
                                                                <input class="form-check-input" type="radio" name="priority" value="low"
                                                                    ' . ($row->priority === "low" ? "checked" : "") . ' required>
                                                                <span style="background-color: green; width: 15px; height: 15px; border-radius: 50%;"></span>
                                                                <span>منخفضة</span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- الوصف -->
                                                    <div class="mb-3">
                                                        <label class="form-label">وصف المهمة</label>
                                                        <textarea class="form-control" name="description" rows="4">' . $row->description . '</textarea>
                                                    </div>

                                                    <!-- المرفقات -->
                                                    <div class="col-8 mb-3">
                                                        <label class="form-label">مرفق</label>
                                                        <input type="file" class="form-control" name="attachment"
                                                            accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                                                        ' . ($row->attachment ? '
                                                        <div class="mt-2">
                                                            <a href="' . asset("storage/" . $row->attachment) . '" target="_blank">
                                                                عرض المرفق الحالي
                                                            </a>
                                                        </div>' : '') . '
                                                    </div>


                                                    <!-- خيار الخطوات -->
                                                    <div class="col-4 mb-3">
                                                        <label for="hasSteps_edit{{ $item->id }}" class="form-label">المهمة
                                                                تحتوي
                                                                على خطوات</label>
                                                        <div class="form-check form-switch">
                                                            <input type="checkbox" class="form-check-input" id="hasSteps_edit' . $row->id . '"
                                                                name="hasSteps" ' . ($row->steps->count() > 0 ? "checked" : "") . '>
                                                            <label for="hasSteps_edit' . $row->id . '" class="form-check-label mx-2">إضافة خطوات</label>
                                                        </div>
                                                    </div>

                                                    <!-- حاوية الخطوات -->
                                                    <div id="stepsContainer_edit' . $row->id . '"
                                                        style="display: ' . ($row->steps->count() > 0 ? "block" : "none") . '">
                                                        ' . $this->getStepsHtml($row) . '
                                                        <button type="button" class="btn btn-secondary btn-sm"
                                                                id="addStepButton_edit' . $row->id . '">
                                                            إضافة خطوة
                                                        </button>
                                                    </div>

                                                    <!-- أزرار التحكم -->
                                                    <div class="d-flex justify-content-end" style="margin-top: 100px">
                                                        <button type="button" class="btn btn-secondary me-2"
                                                                data-bs-dismiss="offcanvas">إلغاء</button>
                                                        <button type="button" class="btn btn-primary"
                                                                onclick="updateTask(' . $row->id . ')">حفظ التعديلات</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>';

                        return '<div class="d-flex gap-2">' .
                            // التحقق مما إذا كان المستخدم هو منشئ المهمة
                            ($row->created_by !== auth()->id()
                                ? '<span class="text-muted">ليس لديك صلاحيات</span>'
                                : (
                                    // إذا كان المستخدم هو المنشئ، نتحقق من وجود صلاحية التعديل
                                    (auth()->user()->can("تعديل مهمة")
                                        ? '<button class="btn btn-sm text-secondary" data-task-id="' . $row->id . '" title="تعديل المهمة" data-bs-toggle="offcanvas" data-bs-target="#editTaskOffcanvas' . $row->id . '">
                                                    <i class="ti ti-edit"></i>
                                                </button>'
                                        : ''
                                    ) .
                                    // ونتحقق أيضاً من وجود صلاحية الحذف
                                    (auth()->user()->can("حذف مهمة")
                                        ? '<button onclick="confirmDeleteTask(' . $row->id . ')" class="btn btn-sm text-secondary">
                                                    <i class="ti ti-trash"></i>
                                                </button>'
                                        : ''
                                    )
                                )
                            ) .
                            '</div>' . $editOffcanvas;
                    })
                    ->rawColumns(['created_by', 'complete_checkbox', 'task_name', 'task_field', 'priority', 'remaining_days', 'status', 'steps_data', 'assigned_users', 'action'])
                    ->make(true);
            }

            // الحصول على الموظف الحالي إذا كان مرتبطًا
            $currentEmployee = auth()->user();

            // دالة مساعدة لإعادة شرط التصفية للمكلفين سواء في المهمة مباشرة أو عبر الخطوات
            $priorityCounts = Task::where('created_by', $userId)
                ->selectRaw('priority, count(*) as count')
                ->groupBy('priority')
                ->pluck('count', 'priority')
                ->toArray();

            $totalTasks = array_sum($priorityCounts);
            $lowPriorityTasksCount = $priorityCounts['low'] ?? 0;
            $mediumPriorityTasksCount = $priorityCounts['medium'] ?? 0;
            $highPriorityTasksCount = $priorityCounts['high'] ?? 0;
            // الحصول على جميع الموظفين
            $users = User::select('id', 'name')->get();
            return view('tasks.assigned_tasks', compact(
                'currentEmployee',
                'users',
                'totalTasks',
                'lowPriorityTasksCount',
                'mediumPriorityTasksCount',
                'highPriorityTasksCount',
            ));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Retrieves the CSS class associated with a given task status.
    |--------------------------------------------------------------------------
    */
    private function getStatusClass($status)
    {
        return [
            'pending' => 'bg-secondary',
            'in_progress' => 'bg-primary',
            'completed' => 'bg-success',
            'approved' => 'bg-success',
            'rejected' => 'bg-danger'
        ][$status] ?? 'bg-secondary';
    }


    /*
    |--------------------------------------------------------------------------
    | to convert the status to arabic
    |--------------------------------------------------------------------------
    */
    private function getStatusText($status)
    {
        return [
            'completed' => 'مكتملة',
            'pending' => 'قيد الانتظار',
            'in_progress' => 'قيد التنفيذ',
            'approved' => 'معتمدة',
            'rejected' => 'مرفوضة',
        ][$status] ?? 'غير محدد';
    }



    /*
    |--------------------------------------------------------------------------
    | Generates HTML option elements for a select input representing user options
    |--------------------------------------------------------------------------
    */
    private function getUserOptionsHtml($task)
    {
        $html = '';
        $assignedUserIds = $task->assignedUsers->pluck('id')->toArray();

        $users = User::select('id', 'name')->get();
        foreach ($users as $user) {
            $selected = in_array($user->id, $assignedUserIds) ? 'selected' : '';
            $profilePicture = $user->employee && $user->employee->profile_picture
                ? asset('storage/' . $user->employee->profile_picture)
                : asset('assets/img/avatars/1.png');

            $html .= '<option value="' . $user->id . '" ' . $selected . '
                  data-image="' . $profilePicture . '">' . $user->employee->name . '</option>';
        }

        return $html;
    }


    /*
    |--------------------------------------------------------------------------
    | Generates HTML markup for each step
    |--------------------------------------------------------------------------
    */
    private function getStepsHtml($task)
    {
        $html = '';
        foreach ($task->steps as $index => $step) {
            $canDelete = !in_array($step->status, ['completed', 'approved', 'rejected']);
            $html .= '
                    <div class="step-block mb-3 border p-3" data-index="' . $index . '">
                                    <input type="hidden" name="step_id[]" value="' . $step->id . '">

                        <div class="step-header mb-2">
                            <strong class="step-number">الخطوة ' . ($index + 1) . '</strong>
                        </div>
                        <div class="row">
                            <div class="col-9">
                                <label class="form-label">اسم الخطوة</label>
                                <input type="text" name="step_name[]" class="form-control"
                                    value="' . $step->name . '" required>
                            </div>
                            <div class="col-3">
                                <label class="form-label">تحتاج لاعتماد؟</label>
                                <div class="form-check">
                                    <input type="checkbox" name="needs_approval[' . $index . ']"
                                        class="form-check-input" value="1" ' . ($step->needs_approval ? 'checked' : '') . '>
                                    <label class="form-check-label">نعم</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label mt-2">المكلفين</label>
                                <select name="step_assigned_user_ids[' . $index . '][]"
                                        class="form-select select2" multiple required>
                                    ' . $this->getStepUserOptionsHtml($step) . '
                                </select>
                            </div>
                        </div>
                      <div class="mt-2 text-end">';

            if ($canDelete) {
                $html .= '<button type="button" class="btn btn-danger btn-sm remove-step-btn">
                                    حذف الخطوة
                                  </button>';
            } else {
                // يمكنك إضافة رسالة أو أيقونة توضح سبب عدم إمكانية الحذف
                $html .= '<span class="text-muted">
                                    <i class="ti ti-lock me-1"></i>
                                    لا يمكن حذف الخطوة بعد اكتمالها
                                  </span>';
            }

            $html .= '</div></div>';
        }
        return $html;
    }


    /*
    |--------------------------------------------------------------------------
    |  Generates HTML for user selection options specific to a step.
    |--------------------------------------------------------------------------
    */
    private function getStepUserOptionsHtml($step)
    {
        $html = '';
        $assignedUserIds = $step->assignedUsers->pluck('id')->toArray();

        $users = User::select('id', 'name')->get();
        foreach ($users as $user) {
            $selected = in_array($user->id, $assignedUserIds) ? 'selected' : '';
            $profilePicture = $user->employee && $user->employee->profile_picture
                ? asset('storage/' . $user->employee->profile_picture)
                : asset('assets/img/avatars/1.png');

            $html .= '<option value="' . $user->id . '" ' . $selected . '
                data-image="' . $profilePicture . '">' . $user->employee->name . '</option>';
        }

        return $html;
    }


    /*
    |--------------------------------------------------------------------------
    | task events record
    |--------------------------------------------------------------------------
    */
    protected function recordTaskEvent(Task $task, $eventType, $message)
    {
        return TaskEvent::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'event_type' => $eventType,
            'message' => $message
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | step events record
    |--------------------------------------------------------------------------
    */
    protected function recordStepEvent(TaskStep $step, $eventType, $message, $rejectReason = null)
    {
        return TaskStepEvent::create([
            'task_step_id' => $step->id,
            'user_id' => auth()->id(),
            'event_type' => $eventType,
            'message' => $message,
            'reject_reason' => $rejectReason
        ]);
    }



    /*
    |--------------------------------------------------------------------------
    | store Task
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        // شروط التحقق
        $validatedData = $request->validate([
            'task_field' => 'nullable|in:offers,contracts,projects,lawsuits,sessions,power_of_attorney,other',

            'task_name' => 'required|string|max:255', // اسم المهمة إلزامي
            'priority' => 'required|in:low,medium,high', // الأولوية
            'description' => 'nullable|string', // الوصف اختياري

            'offer_id' => 'nullable|exists:offers,id', // يجب أن تكون موجودة في جدول العروض
            'contract_id' => 'nullable|exists:contracts,id', // يجب أن تكون موجودة في جدول العقود
            'project_id' => 'nullable|exists:projects,id', // يجب أن تكون موجودة في جدول المشاريع
            'lawsuit_id' => 'nullable|exists:lawsuits,id', // يجب أن تكون موجودة في جدول القضايا
            'session_id' => 'nullable|exists:sessions,id', // يجب أن تكون موجودة في جدول الجلسات
            'power_of_attorney_id' => 'nullable|exists:power_of_attorney,id', // يجب أن تكون موجودة في جدول الوكالات

            'due_date' => 'required|date', // تاريخ الاستحقاق
            'assigned_user_ids'   => 'required|array',
            'assigned_user_ids.*' => 'exists:users,id', // المستخدم المسندة له المهمة
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx|max:10240', // 10MB كحد أقصى لكل ملف

            // في حال تفعيل وجود خطوات
            'hasSteps'              => 'sometimes|accepted',
            'step_name'             => 'array',
            'step_name.*'           => 'required_with:hasSteps|string|max:255',
            'needs_approval'        => 'array',
            'needs_approval.*'      => 'nullable|boolean',
            'step_assigned_user_ids' => 'array',
            'step_assigned_user_ids.*' => 'required_with:hasSteps|array|min:1',

        ]);

        /*
        |--------------------------------------------------------------------------
        | بداية تجهيز البيانات
        |--------------------------------------------------------------------------
        */
        // تحويل القيم إلى boolean
        // $validatedData['remind_before_5min'] = $request->has('remind_before_5min');
        // $validatedData['remind_before_30min'] = $request->has('remind_before_30min');
        // $validatedData['reminder_5min_sent_at'] = null;
        // $validatedData['reminder_30min_sent_at'] = null;



        //  استخراج الوقت و التاريخ
        $dateTime = $request->input('due_date'); // الحصول على قيمة الحقل
        $validatedData['due_date'] = date('Y-m-d', strtotime($dateTime)); // استخراج التاريخ
        $validatedData['due_time'] = date('H:i:s', strtotime($dateTime)); // استخراج الوقت

        // إضافة المستخدم الذي أنشأ المهمة
        $validatedData['created_by'] = auth()->id();

        // تحديد الحالة وتاريخ البداية
        if ($request->has('hasSteps')) {
            $validatedData['status'] = 'pending';
            // لا نضع تاريخ بداية للمهام التي لها خطوات
            $validatedData['task_start_date'] = null;
        } else {
            $validatedData['status'] = 'in_progress';
            // نضع تاريخ البداية الآن للمهام التي ليس لها خطوات
            $validatedData['task_start_date'] = now();
        }
        /*
        |--------------------------------------------------------------------------
        | نهاية تجهيز البيانات
        |--------------------------------------------------------------------------
        */



        /*
        |--------------------------------------------------------------------------
        | create task and assignee user
        |--------------------------------------------------------------------------
        */
        $task = Task::create($validatedData);

        $task->assignedUsers()->attach($validatedData['assigned_user_ids']); // assignee user


        /*
        |--------------------------------------------------------------------------
        | المرفقات
        |--------------------------------------------------------------------------
        */
        // حفظ المرفق ان وجد
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $attachmentFile) {
                $attachmentPath = $attachmentFile->store('task_attachments', 'public');

                // حفظ معلومات المرفق في قاعدة البيانات
                $attachment = new TaskAttachment();
                $attachment->task_id = $task->id;
                $attachment->file_path = $attachmentPath;
                $attachment->file_name = $attachmentFile->getClientOriginalName();
                $attachment->file_type = $attachmentFile->getClientMimeType();
                $attachment->file_size = $attachmentFile->getSize();
                $attachment->save();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | if task has steps
        |--------------------------------------------------------------------------
        */
        if ($request->has('hasSteps')) {
            // اسم الخطوة
            $stepNames = $request->input('step_name');
            // لاحظ أن checkbox الخاص بـ needs_approval قد لا يُرسل قيمة إذا لم يكن مفعلًا
            $needsApprovalArr = $request->input('needs_approval', []);
            // المكلفين في الخطوة
            $stepAssignedUsers = $request->input('step_assigned_user_ids', []);

            // معالجة كل خطوة
            foreach ($stepNames as $index => $stepName) {
                $step = new TaskStep();
                $step->task_id    = $task->id;
                $step->name       = $stepName;
                $step->step_order = $index + 1;
                // إذا كانت القيمة موجودة، تعتبر الحاجة للاعتماد مُفعلة
                $step->needs_approval = isset($needsApprovalArr[$index]) ? true : false;

                // تعيين الحالة الابتدائية للخطوة
                if ($index === 0) {
                    $step->status = 'in_progress';
                    $step->step_start_date = now(); // تاريخ بداية أول خطوة يكون الآن
                } else {
                    $step->status = 'pending';
                    $step->step_start_date = null;
                }

                $step->save();

                // ربط المستخدمين بالمهمة الفرعية (علاقة many-to-many عبر دالة assignedUsers في نموذج TaskStep)
                if (isset($stepAssignedUsers[$index]) && is_array($stepAssignedUsers[$index])) {
                    $step->assignedUsers()->sync($stepAssignedUsers[$index]);
                }
            }
        }

        $inProgressStep = $task->steps()->where('status', 'in_progress')->first();

        // اضافة إلى Microsoft ToDo وتقويم إذا كانت الخطوة في حالة in_progress
        if ($inProgressStep) {

            $dueDateTime = $task->due_date . ' ' . $task->due_time;
            $dueDateTime = \Carbon\Carbon::parse($dueDateTime, 'Asia/Riyadh')->toIso8601String();
            $stepTaskData = [
                'step_id' => $inProgressStep->id, // استخدام معرف الخطوة
                'task_id' => $task->id, // استخدام معرف الخطوة
                'step_name' => auth()->user()->name . ' قام بإضافة خطوة لك باسم "' . $inProgressStep->name . '" في مهمة "' . $task->task_name . '".',
                'description' => 'تم تعيين خطوة جديدة لك في مهمة "' . $task->task_name . '" بواسطة ' . auth()->user()->name . '.',
                'task_priority' => $task->priority_in_arabic, // استخدم نفس أولوية المهمة الأصلية
                'endDateTime' => $dueDateTime,
                'addDateTime' => now()->toIso8601String(),
                'showEndDate' => true,
                // يمكن إضافة المزيد من البيانات حسب الحاجة
            ];

            // ارسال الاشعار


            $stepAssignedUsersCollection = $inProgressStep->assignedUsers;
            // dd($stepAssignedUsersCollection);

            foreach ($stepAssignedUsersCollection as $assignee) {
                $assignee->notify(new \App\Notifications\TaskCreatedNotification($task));
            }
            // إرسال الخطوة إلى Microsoft ToDo وتقويم
            SyncStepsTaskWithMicrosoftJob::dispatch(
                assignedUsers: $stepAssignedUsersCollection,
                taskData: $stepTaskData,
                officeName: Settings::find(1)->office_name
            );
        } else {
            $taskData = $this->taskFormatter->formatForMicrosoft($task, 'create');
            SyncTaskWithMicrosoftJob::dispatch(
                assignedUsers: $task->assignedUsers,
                taskData: $taskData,
                officeName: Settings::find(1)->office_name
            );

            // ارسال الاشعار
            foreach ($task->assignedUsers as $assignee) {
                $assignee->notify(new \App\Notifications\TaskCreatedNotification($task));
            }
        }


        $this->recordTaskEvent($task, 'create', 'تم إضافة المهمة');

        if ($task->task_field != null) {
            if ($task->project_id) {
                return redirect()->route('project.tasks', $task->project_id)->with('success', 'تم إنشاء المهمة بنجاح!');
            } elseif ($task->lawsuit_id) {
                return redirect()->to(route('lawsuits.show', $task->lawsuit_id) . '#tasks')
                    ->with('success', 'تم إنشاء المهمة بنجاح!');
            }
        }

        return redirect()->back()->with('success', 'تم إنشاء المهمة بنجاح!');
    }



    /*
    |--------------------------------------------------------------------------
    | show Task
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        try {
            $task = Task::with(['steps' => function ($query) {
                $query->orderBy('step_order');
            }])
                ->findOrFail($id);

            // تحويل بعض القيم لعرضها بشكل أفضل
            $task->priority_text = match ($task->priority) {
                'low' => 'منخفضة',
                'medium' => 'متوسطة',
                'high' => 'مرتفعة',
                default => 'غير محدد'
            };

            $task->task_field_text = match ($task->task_field) {
                'offers' => 'عروض',
                'contracts' => 'عقود',
                'projects' => 'مشاريع',
                'lawsuits' => 'قضايا',
                'sessions' => 'جلسات',
                'power_of_attorney' => 'تفويض',
                'other' => 'أخرى',
                default => 'غير محدد'
            };

            $task->status_text = match ($task->status) {
                'pending' => 'قيد الانتظار',
                'in_progress' => 'قيد التنفيذ',
                'completed' => 'مكتملة',
                'cancel_completion' => 'ملغاة',
                default => 'غير محدد'
            };

            // التحقق من الصلاحيات (اختياري)
            if (
                $task->created_by !== auth()->id() &&
                !$task->assignedUsers->contains('id', auth()->id()) &&
                !auth()->user()->can('كل المهام')
            ) {
                return redirect()->back()
                    ->with('error', 'غير مصرح لك بعرض هذه المهمة');
            }

            // جلب المكلفين بالمهمة
            $assignedUsers = $task->assignedUsers;

            // إحصائيات الخطوات
            $totalSteps = $task->steps->count();
            $completedSteps = $task->steps->whereIn('status', ['completed', 'approved'])->count();
            $pendingSteps = $totalSteps - $completedSteps;

            return view('tasks.show', compact(
                'task',
                'assignedUsers',
                'totalSteps',
                'completedSteps',
                'pendingSteps'
            ));
        } catch (\Exception $e) {
            \Log::error('خطأ في عرض المهمة: ' . $e->getMessage());
            return redirect()->route('organization-center.tasks.index')
                ->with('error', 'حدث خطأ أثناء عرض المهمة');
        }
    }



    /*
    |--------------------------------------------------------------------------
    | update Task
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        // ابحث عن المهمة
        $task = Task::findOrFail($id);

        // تحقق من الصلاحية مثلاً
        if ($task->created_by !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بتعديل هذه المهمة.'
            ], 403);
        }

        // التحقق من البيانات
        $validatedData = $request->validate([
            // 'task_field' => 'nullable|in:offers,contracts,projects,lawsuits,sessions,power_of_attorney,other',
            'task_name'  => 'required|string|max:255',
            'priority'   => 'required|in:low,medium,high',
            'description' => 'nullable|string',
            'due_date'   => 'required|date',
            'assigned_user_ids'   => 'required|array',
            'assigned_user_ids.*' => 'exists:users,id',
            'new_attachments' => 'nullable|array',
            'new_attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx|max:10240',
            // 'remind_before_5min'  => 'boolean',
            // 'remind_before_30min' => 'boolean',
            // في حال تفعيل وجود خطوات:
            'hasSteps'             => 'sometimes|accepted',
            'step_name'            => 'array',
            'step_name.*'          => 'required_with:hasSteps|string|max:255',
            'needs_approval'       => 'array',
            'needs_approval.*'     => 'nullable|boolean',
            'step_assigned_user_ids' => 'array',
            'step_assigned_user_ids.*' => 'required_with:hasSteps|array|min:1',
        ]);

        /*
        |--------------------------------------------------------------------------
        | بداية تجهيز البيانات
        |--------------------------------------------------------------------------
        */
        // إذا تم رفع ملف
        if ($request->hasFile('new_attachments')) {
            foreach ($request->file('new_attachments') as $attachmentFile) {
                $attachmentPath = $attachmentFile->store('task_attachments', 'public');

                // حفظ معلومات المرفق
                $attachment = new TaskAttachment();
                $attachment->task_id = $task->id;
                $attachment->file_path = $attachmentPath;
                $attachment->file_name = $attachmentFile->getClientOriginalName();
                $attachment->file_type = $attachmentFile->getClientMimeType();
                $attachment->file_size = $attachmentFile->getSize();
                $attachment->save();
            }
        }


        // تجهيز تاريخ ووقت الاستحقاق
        $dateTime = $validatedData['due_date']; // الحقل القادم من النموذج
        $validatedData['due_date'] = date('Y-m-d', strtotime($dateTime));
        $validatedData['due_time'] = date('H:i:s', strtotime($dateTime));

        // من قام بالتعديل
        $validatedData['updated_by_user_id'] = auth()->id();

        // تحديد الحالة
        $validatedData['status'] = $request->has('hasSteps') ?  'pending' : 'in_progress';

        /*
        |--------------------------------------------------------------------------
        | نهاية تجهيز البيانات
        |--------------------------------------------------------------------------
        */


        try {
            // تحديث المهمة
            $task->update($validatedData);


            // التعامل مع خطوات المهمة:
            if ($request->has('hasSteps')) {
                // نتوقع من الواجهة إرسال مصفوفات: step_id, step_name, needs_approval, step_assigned_user_ids
                $stepIds           = $request->input('step_id', []); // يجب أن يحتوي على id للخطوات الموجودة
                $stepNames         = $request->input('step_name', []);
                $needsApprovalArr  = $request->input('needs_approval', []);
                $stepAssignedUsers = $request->input('step_assigned_user_ids', []);

                // مصفوفة لتجميع المعرفات (id) التي تم تحديثها أو الاحتفاظ بها
                $updatedStepIds = [];

                // عند إنشاء خطوات جديدة، نحتاج لإيجاد أعلى قيمة حالية لـ step_order
                $maxOrder = $task->steps()->max('step_order') ?? 0;

                // نتعامل مع الخطوات المرسلة بناءً على الفهرس
                foreach ($stepNames as $index => $stepName) {
                    // إذا وُجد step_id فهذا يعني أنها خطوة موجودة بالفعل
                    if (isset($stepIds[$index]) && !empty($stepIds[$index])) {
                        $step = TaskStep::find($stepIds[$index]);
                        if ($step && $step->task_id == $task->id) {
                            // نقوم بتحديث البيانات دون تغيير الـ step_order (أي لا نغير المعرف أو رقم الترتيب)
                            $step->name = $stepName;
                            $step->needs_approval = isset($needsApprovalArr[$index]) ? true : false;
                            $step->save();

                            if (isset($stepAssignedUsers[$index]) && is_array($stepAssignedUsers[$index])) {
                                $step->assignedUsers()->sync($stepAssignedUsers[$index]);
                            }
                            $updatedStepIds[] = $step->id;
                        }
                    } else {
                        // لا يوجد step_id، إذن هذه خطوة جديدة
                        $maxOrder++; // نعطيها رقم ترتيب جديد يكون أكبر من الموجود
                        $step = new TaskStep();
                        $step->task_id = $task->id;
                        $step->name = $stepName;
                        $step->needs_approval = isset($needsApprovalArr[$index]) ? true : false;
                        $step->step_order = $maxOrder;

                        // تحديد حالة الخطوة الجديدة بناءً على حالة الخطوة السابقة (إذا كانت موجودة)
                        // إذا لم توجد خطوة سابقة، نعطيها "in_progress" افتراضيًا
                        $prevStep = $task->steps()->where('step_order', $maxOrder - 1)->first();
                        if (!$prevStep || in_array($prevStep->status, ['completed', 'approved'])) {
                            $step->status = 'in_progress';
                        } else {
                            $step->status = 'pending';
                        }
                        $step->save();

                        if (isset($stepAssignedUsers[$index]) && is_array($stepAssignedUsers[$index])) {
                            $step->assignedUsers()->sync($stepAssignedUsers[$index]);
                        }
                        $updatedStepIds[] = $step->id;
                    }
                }

                // حذف الخطوات التي لم يتم إرسال step_id الخاص بها في الطلب (أي الخطوات التي حُذفت من الواجهة)
                $task->steps()->whereNotIn('id', $updatedStepIds)->delete();

                // بعد الحذف، نقوم بإعادة فحص الخطوات المتبقية لتحديث حالة أول خطوة غير مكتملة إلى in_progress
                $remainingSteps = $task->steps()->orderBy('step_order')->get();
                foreach ($remainingSteps as $step) {
                    // إذا كانت الخطوة ليست مكتملة أو معتمدة، نجعلها in_progress ثم نخرج من الحلقة
                    if (!in_array($step->status, ['completed', 'approved'])) {
                        $step->status = 'in_progress';
                        $step->save();
                        break;
                    }
                }

                // تحديث حالة المهمة بناءً على حالة الخطوات المتبقية
                $incompleteStepsCount = $task->steps()->whereNotIn('status', ['completed', 'approved'])->count();
                $newStatus = $incompleteStepsCount > 0 ? 'pending' : 'in_progress';

                $task->update(['status' => $newStatus]);
            } else {
                $task->steps()->delete();
                $validatedData['status'] = 'in_progress';
            }


            // microsoft graph
            // في دالة update في TaskController

            // تجهيز البيانات للمزامنة مع مايكروسوفت
            $tasUpdatekData = $this->taskFormatter->formatForMicrosoft($task, 'update');
            $tasNewkData = $this->taskFormatter->formatForMicrosoft($task, 'create');

            // المستخدمون السابقون
            $previousUsers = $task->assignedUsers()->get();

            // استخراج بيانات الـ pivot للمستخدمين السابقين
            $previousUsersData = $previousUsers->map(function ($user) {
                return [
                    'id'              => $user->id,
                    'email'           => $user->email,
                    'graph_task_id'   => $user->pivot->graph_task_id,
                    'graph_list_id'   => $user->pivot->graph_list_id,
                    'graph_event_id'  => $user->pivot->graph_event_id,
                ];
            });

            // تحديث المكلفين في قاعدة البيانات
            $task->assignedUsers()->sync($validatedData['assigned_user_ids']);

            // المستخدمون بعد التحديث
            $newUsers = $task->assignedUsers()->get();

            // تحديد المستخدمين المضافين حديثاً
            $addedUsers = $newUsers->reject(function ($user) use ($previousUsers) {
                return $previousUsers->contains('id', $user->id);
            });

            // تحديد المستخدمين المحذوفين (باستخدام بيانات الـ previousUsersData)
            $removedUsersData = $previousUsersData->reject(function ($userData) use ($newUsers) {
                return $newUsers->contains('id', $userData['id']);
            });

            // تحديد المستخدمين الحاليين الذين لم يتم إضافتهم حديثاً
            $existingUsers = $newUsers->reject(function ($user) use ($addedUsers) {
                return $addedUsers->contains('id', $user->id);
            });

            // إضافة المهام للمستخدمين الجدد
            if ($addedUsers->isNotEmpty()) {
                SyncTaskWithMicrosoftJob::dispatch(
                    assignedUsers: $addedUsers,
                    taskData: $tasNewkData,
                    officeName: Settings::find(1)->office_name
                );
            }

            // حذف المهام من المستخدمين المحذوفين باستخدام بيانات الـ pivot المخزنة
            if ($removedUsersData->isNotEmpty()) {
                BatchDeleteMicrosoftTaskJob::dispatch(
                    task: $task,
                    removedUsersData: $removedUsersData->all()
                );
            }

            // تحديث المهام للمستخدمين الحاليين
            if ($existingUsers->isNotEmpty()) {
                BatchUpdateMicrosoftTaskJob::dispatch(
                    task: $task,
                    taskData: $tasUpdatekData,
                    officeName: Settings::find(1)->office_name,
                    existingUsers: $existingUsers,
                );
            }

            // تسجيل حدث المهمة
            $this->recordTaskEvent($task, 'update', 'تم تحديث بيانات المهمة');


            // إرسال إشعارات للمستخدمين الجدد
            foreach ($addedUsers as $newUser) {
                $newUser->notify(new \App\Notifications\TaskCreatedNotification($task));
            }

            // إرسال إشعارات تحديث للمستخدمين الحاليين
            foreach ($existingUsers as $existingUser) {
                $existingUser->notify(new \App\Notifications\TaskUpdatedNotification($task));
            }

            // حذف الإشعارات للمستخدمين المحذوفين
            foreach ($removedUsersData as $removedUserData) {
                // حذف الإشعارات من قاعدة البيانات
                if ($removedUser = User::find($removedUserData['id'])) {
                    // هنا نقوم بحذف الإشعارات المتعلقة بهذه المهمة لهذا المستخدم
                    DatabaseNotification::where('notifiable_id', $removedUser->id)
                        ->where('notifiable_type', get_class($removedUser))
                        ->where('data->task_id', $task->id)
                        ->delete();
                }
            }


            // إذا كان الطلب عبر AJAX
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث المهمة بنجاح!',
            ]);

            // أو إعادة التوجيه بشكل عادي
        } catch (\Exception $e) {
            Log::error('خطأ أثناء تحديث المهمة: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث المهمة.',
            ], 500);
        }
    }




    /*
    |--------------------------------------------------------------------------
    | delete task
    |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        $task = Task::findOrFail($id);

        // التحقق من أنّ المستخدم الحالي هو نفسه الذي أنشأ المهمة
        if ($task->created_by !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بحذف هذه المهمة.'
            ], 403);
        }


        try {

            DatabaseNotification::whereRaw("JSON_EXTRACT(data, '$.task_id') = ?", [$task->id])->delete();

            // حذف المهمة
            $task->delete();

            // حذف الإشعارات المرتبطة
            // DatabaseNotification::where('data->task_id', $task->id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف المهمة بنجاح!',
            ]);
        } catch (\Exception $e) {
            // Log::error('خطأ أثناء حذف المهمة: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف المهمة.',
            ], 500);
        }
    }




    /*
    |--------------------------------------------------------------------------
    | trash of tasks
    |--------------------------------------------------------------------------
    | عرض المهمات المحذوفة
    */

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



    private function buildAssigneesList($task)
    {
        if ($task->assignees->count() > 0) {
            $html = '<ul>';
            foreach ($task->assignees as $assignee) {
                $html .= '<li>' . e($assignee->employee->name) . '</li>';
            }
            $html .= '</ul>';
            return $html;
        } else {
            return 'لا يوجد معنيون بعد.';
        }
    }



    /*
    |--------------------------------------------------------------------------
    | restore
    |--------------------------------------------------------------------------
    | لاستعادة المهمة
    */
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


    /*
    |--------------------------------------------------------------------------
    | forceDelete
    |--------------------------------------------------------------------------
    | حذف المهمة نهائياً
    */
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


    /*
    |--------------------------------------------------------------------------
    | complate task
    |--------------------------------------------------------------------------
    | لاكمال و الغاء اكمال المهمة
    */
    public function toggleTaskCompletion(Request $request, Task $task)
    {

        // تأكد من وجود المهمة
        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'المهمة غير موجودة'
            ], 404);
        }

        if (!$task->assignedUsers->contains('id', auth()->id())) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكنك  التعامل مع هذه المهمة  لأنك غير مكلف بها.'
            ], 403);
        }


        try {
            // تحديث الحالة
            $status = $request->input('status');
            $task->status = $status;


            if ($status == 'completed') {

                // تحقق من عدم وجود خطوات غير مكتملة أو غير معتمدة في المهمة
                $incompleteSteps = $task->steps()->whereNotIn('status', ['completed', 'approved'])->count();
                if ($incompleteSteps > 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'لا يمكن إكمال المهمة، توجد خطوات غير مكتملة.'
                    ], 400);
                }

                $task->task_end_date = now();
                $task->completed_by = auth()->id();

                $taskData = [
                    'task_url' => route('organization-center.tasks.show', $task->id),           // رابط المهمة
                    'title' => 'قام ' . auth()->user()->name . ' بإكمال المهمة ',
                    'user_name' => $task->createdBy->name ?? '',
                    'task_name' => $task->task_name ?? '',
                    'assignee_name' =>  auth()->user()->name ?? '',
                    'completion_date' => $task->task_end_date ?? '',
                    'duration' => $task->duration ?? '',
                    'office_name' => Settings::find(1)->office_name ?? '',
                ];
                if ($task->createdBy && $task->createdBy->email) {
                    // إرسال البريد الإلكتروني في الخلفية كـ job
                    SendTaskCompletionEmailJob::dispatch($taskData, $task->createdBy->email);
                    $task->createdBy->notify(new \App\Notifications\TaskCompletedNotification($task));
                }
            } else {
                $task->task_end_date = null;
                $task->completed_by = null;
            }


            $task->save();

            // تحديد رسالة مخصصة بناءً على الحالة
            $message = $status == 'completed'
                ? 'تم إكمال المهمة بنجاح'
                : 'تم إلغاء إكمال المهمة';


            // تسجيل حدث المهمة
            $this->recordTaskEvent($task, $task->status === 'completed' ? 'completed' : 'incompleted', $task->status === 'completed' ? 'تم إكمال المهمة.' : 'تم إلغاء إكمال المهمة.');

            return response()->json([
                'success' => true,
                'message' => $message,
                'status' => $status
            ]);
        } catch (\Exception $e) {
            // سجل الخطأ للتشخيص
            Log::error('خطأ في تحديث المهمة: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث الحالة: ' . $e->getMessage()
            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | steps task
    |--------------------------------------------------------------------------
    | لاكمال و الغاء اكمال الخطوة
    */
    public function toggleStepCompletion(Request $request, $stepId)
    {

        try {
            // البحث عن الخطوة باستخدام المعرف
            $step = TaskStep::findOrFail($stepId);

            if (!$step->assignedUsers->contains('id', auth()->id())) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكنك  التعامل مع هذه الخطوة  لأنك غير مكلف بها.'
                ], 403);
            }

            $taskId = $step->task_id;

            if ($step->task->status == 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'عفوا هذه المهمة مكتملة لا يمكن التعديل في خطواتها '
                ], 400);
            }


            $stepOrder = $step->step_order;
            $isChecked = filter_var($request->input('isChecked', false), FILTER_VALIDATE_BOOLEAN);

            // جلب الخطوة السابقة
            $previousStep = TaskStep::where('task_id', $taskId)
                ->where('step_order', $stepOrder - 1)
                ->first();

            // جلب الخطوة التالية
            $nextStep = TaskStep::where('task_id', $taskId)
                ->where('step_order', $stepOrder + 1)
                ->first();

            /*** 🔹 القاعدة رقم 1: لا يمكن إكمال خطوة إلا إذا كانت الخطوة السابقة مكتملة أو معتمدة ***/
            if ($isChecked && $previousStep) {
                if ($previousStep->status === 'rejected') {
                    return response()->json([
                        'success' => false,
                        'message' => 'لا يمكن إكمال هذه الخطوة، لأن الخطوة السابقة قد تم رفضها.'
                    ], 400);
                }
                if (!in_array($previousStep->status, ['completed', 'approved'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'لا يمكن إكمال هذه الخطوة، حيث أن الخطوة السابقة لم تكتمل بعد.'
                    ], 400);
                }
            }

            /*** 🔹 القاعدة رقم 2: لا يمكن إلغاء إكمال خطوة إذا كانت الخطوة التالية مكتملة أو معتمدة أو مرفوضة ***/
            if (!$isChecked && $nextStep && in_array($nextStep->status, ['completed', 'approved', 'rejected'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن إلغاء إكمال هذه الخطوة، لأن الخطوة التالية قد اكتملت أو تم اعتمادها أو رفضها.'
                ], 400);
            }

            // تحديث حالة وتواريخ الخطوة
            if ($isChecked) {
                if ($step->step_start_date === null) {
                    $step->step_start_date = ($previousStep && $previousStep->step_end_date) ? $previousStep->step_end_date : now();
                }



                $step->step_end_date = now();
                $step->status = 'completed';
                $step->completed_by = auth()->id();

                $stepData = [
                    'task_url'              => route('organization-center.tasks.show', $step->task->id),
                    'title'                 => 'قام ' . auth()->user()->name . ' بإكمال الخطوة ',
                    'user_name'             => $step->task->createdBy->name ?? '',
                    'task_name'             => $step->task->task_name ?? '',
                    'step_name'             => $step->name ?? '',
                    'assignee_name'         => auth()->user()->name ?? '',
                    'completion_date'       => $step->step_end_date ?? '',
                    'duration'              => $step->duration ?? '',
                    'office_name'           => Settings::find(1)->office_name ?? '',
                ];
                if ($step->task->createdBy && $step->task->createdBy->email) {
                    // إرسال البريد الإلكتروني في الخلفية كـ job
                    SendStepCompletionEmailJob::dispatch($stepData, $step->task->createdBy->email);
                    $step->task->createdBy->notify(new \App\Notifications\StepCompletedNotification($step));
                }

                // تحديث تاريخ بداية الخطوة التالية
                if ($nextStep) {
                    $nextStep->step_start_date = now();
                    $nextStep->status = 'in_progress';
                    $nextStep->save();

                    /*
                    |--------------------------------------------------------------------------
                    | send notification to the user who owns the next step
                    |--------------------------------------------------------------------------
                    */

                    $dueDateTime = $nextStep->task->due_date . ' ' . $nextStep->task->due_time;
                    $dueDateTime = \Carbon\Carbon::parse($dueDateTime, 'Asia/Riyadh')->toIso8601String();
                    $stepTaskData = [
                        'step_id' => $nextStep->id, // استخدام معرف الخطوة
                        'task_id' => $nextStep->task->id, // استخدام معرف الخطوة
                        'step_name' => $nextStep->task->createdBy->name . ' قام بإضافة خطوة لك باسم "' . $nextStep->name . '" في مهمة "' . $nextStep->task->task_name . '".',
                        'description' => 'تم تعيين خطوة جديدة لك في مهمة "' . $nextStep->task->task_name . '" بواسطة ' . $nextStep->task->createdBy->name . '.',
                        'task_priority' => $nextStep->task->priority_in_arabic, // استخدم نفس أولوية المهمة الأصلية
                        'endDateTime' => $dueDateTime,
                        'addDateTime' => now()->toIso8601String(),
                        'showEndDate' => true,
                        // يمكن إضافة المزيد من البيانات حسب الحاجة
                    ];

                    $stepAssignedUsersCollection = $nextStep->assignedUsers;

                    // إرسال الخطوة إلى Microsoft ToDo وتقويم
                    SyncStepsTaskWithMicrosoftJob::dispatch(
                        assignedUsers: $stepAssignedUsersCollection,
                        taskData: $stepTaskData,
                        officeName: Settings::find(1)->office_name
                    );

                    foreach ($stepAssignedUsersCollection as $assignee) {
                        $assignee->notify(new \App\Notifications\TaskCreatedNotification($nextStep->task));
                    }


                    // ارسال اشعار للمستخدم الذي يملك الخطوة التالية
                } else {
                    // تحديث تاريخ و حالة المهمة الرئيسية
                    $step->task->task_start_date = now();
                    $step->task->status = 'in_progress';
                    $step->task->save();

                    /*
                    |--------------------------------------------------------------------------
                    | snes notification to the task owner
                    |--------------------------------------------------------------------------
                    */
                    $taskData = $this->taskFormatter->formatForMicrosoft($step->task, 'create');
                    SyncTaskWithMicrosoftJob::dispatch(
                        assignedUsers: $step->task->assignedUsers,
                        taskData: $taskData,
                        officeName: Settings::find(1)->office_name
                    );

                    foreach ($step->task->assignedUsers as $assignee) {
                        $assignee->notify(new \App\Notifications\TaskCreatedNotification($step->task));
                    }
                }
            } else {
                $step->status = 'in_progress';
                $step->step_end_date = null;
                $step->completed_by = null;

                if ($nextStep) {
                    $nextStep->step_start_date = null;
                    $nextStep->status = 'pending';
                    $nextStep->save();
                } else {
                    // تحديث تاريخ و حالة المهمة الرئيسية
                    $step->task->task_start_date = null;
                    $step->task->status = 'pending';
                    $step->task->save();
                }
            }

            $step->save();

            $duration = $step->duration;

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث حالة الخطوة بنجاح.',
                'status' => $step->status,
                'step_start_date' => $step->step_start_date ? $step->step_start_date->format('Y-m-d H:i:s') : '-',
                'step_end_date' => $step->step_end_date ? $step->step_end_date->format('Y-m-d H:i:s') : '-',
                'duration' => $duration ?: '-',
                'completed_by' => $step->completedBy ?? '-'

            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث حالة الخطوة: ' . $e->getMessage()
            ], 500);
        }
    }



    /*
    |--------------------------------------------------------------------------
    | steps task
    |--------------------------------------------------------------------------
    | لاعتماد و رفض الخطوة
    */
    public function toggleStepApproval(Request $request, $stepId)
    {
        try {
            // البحث عن الخطوة باستخدام المعرف
            $step = TaskStep::findOrFail($stepId);

            if (!$step->assignedUsers->contains('id', auth()->id())) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكنك  التعامل مع هذه الخطوة  لأنك غير مكلف بها.'
                ], 403);
            }

            if ($step->task->status == 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'عفوا هذه المهمة مكتملة لا يمكن التعديل في خطواتها '
                ], 400);
            }


            $taskId = $step->task_id;
            $stepOrder = $step->step_order;

            // قراءة الإجراء المطلوب من الطلب (approve أو reject)
            $action = $request->input('action');

            // جلب الخطوة السابقة
            $previousStep = TaskStep::where('task_id', $taskId)
                ->where('step_order', $stepOrder - 1)
                ->first();

            // جلب الخطوة التالية
            $nextStep = TaskStep::where('task_id', $taskId)
                ->where('step_order', $stepOrder + 1)
                ->first();

            if ($previousStep && $previousStep->status === 'rejected') {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن اعتماد او رفض هذه الخطوة، لأن الخطوة السابقة قد تم رفضها.'
                ], 400);
            }

            /*** 🔹 القاعدة رقم 1: لا يمكن اعتماد أو رفض خطوة إلا إذا كانت الخطوة السابقة مكتملة أو معتمدة ***/
            if ($previousStep && !in_array($previousStep->status, ['completed', 'approved'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن اعتماد أو رفض هذه الخطوة لأن الخطوة السابقة لم تكتمل بعد.'
                ], 400);
            }

            /*** 🔹 القاعدة رقم 2: إذا تم اعتماد أو إكمال أو رفض خطوة، فلا يمكن تعديل الخطوة السابقة ***/
            if ($nextStep && in_array($nextStep->status, ['approved', 'completed', 'rejected'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن تعديل هذه الخطوة لأن الخطوة التالية قد تم اعتمادها أو اكتمالها أو رفضها بالفعل.'
                ], 400);
            }

            if ($action === 'approve') {


                if ($step->step_start_date === null) {
                    $step->step_start_date = ($previousStep && $previousStep->step_end_date) ? $previousStep->step_end_date : now();
                }



                $step->status = 'approved';
                $step->step_end_date = now();
                $step->completed_by = auth()->id();

                $stepData = [
                    'task_url'              => route('organization-center.tasks.show', $step->task->id),
                    'title'                 => 'قام ' . auth()->user()->name . ' بإعتماد الخطوة ',
                    'user_name'             => $step->task->createdBy->name ?? '',
                    'task_name'             => $step->task->task_name ?? '',
                    'step_name'             => $step->name ?? '',
                    'assignee_name'         => auth()->user()->name ?? '',
                    'completion_date'       => $step->step_end_date ?? '',
                    'duration'              => $step->duration ?? '',
                    'office_name'           => Settings::find(1)->office_name ?? '',
                ];
                if ($step->task->createdBy && $step->task->createdBy->email) {
                    // إرسال البريد الإلكتروني في الخلفية كـ job
                    SendStepCompletionEmailJob::dispatch($stepData, $step->task->createdBy->email);
                    $step->task->createdBy->notify(new \App\Notifications\StepCompletedNotification($step));
                }

                if ($nextStep) {
                    $nextStep->step_start_date = now();
                    $nextStep->status = 'in_progress';
                    $nextStep->save();

                    /*
                    |--------------------------------------------------------------------------
                    | send notification to the user who owns the next step
                    |--------------------------------------------------------------------------
                    */

                    $dueDateTime = $nextStep->task->due_date . ' ' . $nextStep->task->due_time;
                    $dueDateTime = \Carbon\Carbon::parse($dueDateTime, 'Asia/Riyadh')->toIso8601String();
                    $stepTaskData = [
                        'step_id' => $nextStep->id, // استخدام معرف الخطوة
                        'task_id' => $nextStep->task->id, // استخدام معرف الخطوة
                        'step_name' => $nextStep->task->createdBy->name . ' قام بإضافة خطوة لك باسم "' . $nextStep->name . '" في مهمة "' . $nextStep->task->task_name . '".',
                        'description' => 'تم تعيين خطوة جديدة لك في مهمة "' . $nextStep->task->task_name . '" بواسطة ' . $nextStep->task->createdBy->name . '.',
                        'task_priority' => $nextStep->task->priority_in_arabic, // استخدم نفس أولوية المهمة الأصلية
                        'endDateTime' => $dueDateTime,
                        'addDateTime' => now()->toIso8601String(),
                        'showEndDate' => true,
                        // يمكن إضافة المزيد من البيانات حسب الحاجة
                    ];

                    $stepAssignedUsersCollection = $nextStep->assignedUsers;

                    // إرسال الخطوة إلى Microsoft ToDo وتقويم
                    SyncStepsTaskWithMicrosoftJob::dispatch(
                        assignedUsers: $stepAssignedUsersCollection,
                        taskData: $stepTaskData,
                        officeName: Settings::find(1)->office_name
                    );

                    foreach ($stepAssignedUsersCollection as $assignee) {
                        $assignee->notify(new \App\Notifications\TaskCreatedNotification($nextStep->task));
                    }
                } else {
                    // تحديث تاريخ و حالة المهمة الرئيسية
                    $step->task->task_start_date = now();
                    $step->task->status = 'in_progress';
                    $step->task->save();

                    /*
                    |--------------------------------------------------------------------------
                    | snes notification to the task owner
                    |--------------------------------------------------------------------------
                    */
                    $taskData = $this->taskFormatter->formatForMicrosoft($step->task, 'create');
                    SyncTaskWithMicrosoftJob::dispatch(
                        assignedUsers: $step->task->assignedUsers,
                        taskData: $taskData,
                        officeName: Settings::find(1)->office_name
                    );
                }
            } elseif ($action === 'reject') {
                $step->status = 'rejected';
                $step->step_end_date = now();
                $step->completed_by = auth()->id();
                $step->reject_reason = $request->input('reject_reason');

                $stepData = [
                    'task_url'              => route('organization-center.tasks.show', $step->task->id),
                    'title'                 => 'قام ' . auth()->user()->name . ' برفض  الخطوة ',
                    'user_name'             => $step->task->createdBy->name ?? '',
                    'task_name'             => $step->task->task_name ?? '',
                    'step_name'             => $step->name ?? '',
                    'assignee_name'         => auth()->user()->name ?? '',
                    'completion_date'       => $step->step_end_date ?? '',
                    'duration'              => $step->duration ?? '',
                    'office_name'           => Settings::find(1)->office_name ?? '',
                    'reject_reason'         => $step->reject_reason ?? '',
                ];
                if ($step->task->createdBy && $step->task->createdBy->email) {
                    // إرسال البريد الإلكتروني في الخلفية كـ job
                    SendStepCompletionEmailJob::dispatch($stepData, $step->task->createdBy->email);
                    $step->task->createdBy->notify(new \App\Notifications\StepCompletedNotification($step));
                }

                if (!$nextStep) {
                    // تحديث تاريخ و حالة المهمة الرئيسية
                    $step->task->task_start_date = null;
                    $step->task->status = 'pending';
                    $step->task->save();
                }
            }

            $step->save();

            return response()->json([
                'success' => true,
                'message' => $action === 'approve' ? 'تم اعتماد الخطوة.' : 'تم رفض الخطوة.',
                'status' => $step->status,
                'step_start_date' => $step->step_start_date ? $step->step_start_date->format('Y-m-d H:i:s') : '-',
                'step_end_date' => $step->step_end_date ? $step->step_end_date->format('Y-m-d H:i:s') : '-',
                'duration' => $step->duration ?: '-',
                'completed_by' => $step->completedBy ?? '-'

            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث حالة اعتماد الخطوة: ' . $e->getMessage()
            ], 500);
        }
    }



    public function deleteAttachment($attachmentId)
    {
        try {
            // البحث عن المرفق
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

    // الكود الخاص بالتذكيرات

    // protected function scheduleReminders(Task $task)
    // {
    //     $dueDateTime = Carbon::parse($task->due_date . ' ' . $task->due_time);

    //     // جدولة التذكير قبل 30 دقيقة أولاً إذا تم اختياره
    //     if ($task->remind_before_30min) {
    //         $reminderTime = $dueDateTime->copy()->subMinutes(30);
    //         if ($reminderTime->isFuture()) {
    //             SendReminderEmail::dispatch($task, 30)->delay($reminderTime);
    //         }
    //     }

    //     // ثم جدولة التذكير قبل 5 دقائق إذا تم اختياره
    //     if ($task->remind_before_5min) {
    //         $reminderTime = $dueDateTime->copy()->subMinutes(5);
    //         if ($reminderTime->isFuture()) {
    //             SendReminderEmail::dispatch($task, 5)->delay($reminderTime);
    //         }
    //     }
    // }
}
