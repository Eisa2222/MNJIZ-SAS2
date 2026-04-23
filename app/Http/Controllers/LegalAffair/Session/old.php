<?php

namespace App\Http\Controllers\LegalAffair\Session;

use Alkoumi\LaravelHijriDate\Hijri;
use App\DataTables\LegalAffairs\Session\SessionsDataTable;
use App\Enums\LegalAffair\Session\SessionStatus;
use App\Http\Controllers\Controller;
use App\Models\OperationsCenter\Customer\Customers;
use App\Models\general_setting\SettingsEntityRank;
use App\Models\general_setting\SettingsSessionType;
use App\Models\general_setting\SettingsTypeRulings;
use App\Models\Hr\Employees\Employees;
use App\Models\GeneralSetting\SystemSetting\Settings;
use App\Models\User;
use App\Services\EmailService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Jobs\Tasks\CreateTaskJob;
use App\Jobs\Tasks\BatchDeleteMicrosoftTaskJob;
use App\Jobs\Tasks\BatchUpdateMicrosoftTaskJob;
use App\Jobs\Tasks\SyncTaskWithMicrosoftJob;
use App\Models\LegalAffair\Lawsuit\Lawsuit;
use App\Models\LegalAffair\Session\Session;
use App\Models\Task\Task;

class SessionController extends Controller
{


    private $route  = "legal-affairs.sessions";
    private $page   = "legal_affairs.sessions";

    public function __construct()
    {
        $this->middleware('can:ارشيف الجلسات')->only(['trashed']);
    }

    public function index(SessionsDataTable $dataTable)
    {
        try {
            // Statistics
            $statusCounts = Session::query()
                ->select('session_status')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('session_status')
                ->pluck('count', 'session_status')
                ->toArray();

            $totalSessions               = array_sum($statusCounts);
            $activeSessions              = $statusCounts[SessionStatus::Active->value] ?? 0;
            $inactiveSessions            = $statusCounts[SessionStatus::Inactive->value] ?? 0;
            $PendingSessionControl       = $statusCounts[SessionStatus::PendingSessionControl->value] ?? 0;

            // Filters
            $sessionStatus          = SessionStatus::options();
            $settings_entity_ranks  = SettingsEntityRank::select(['id', 'name'])->get();
            $settings_session_type  = SettingsSessionType::select(['id', 'name'])->get();

            return $dataTable->render($this->page . '.index', [

                'route'                 => $this->route,
                'totalSessions'         => $totalSessions,
                'activeSessions'        => $activeSessions,
                'inactiveSessions'      => $inactiveSessions,
                'PendingSessionControl' => $PendingSessionControl,


                'sessionStatus'                     => $sessionStatus,
                'settings_entity_ranks'             => $settings_entity_ranks,
                'settings_session_type'             => $settings_session_type,

            ]);
        } catch (\Exception $e) {
            Log::error($e);

            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    }


    // public function createWithLawsuit($id)
    // {
    //     $lawsuit = Lawsuit::find($id);

    //     $latestSession = $lawsuit->sessions()->latest('created_at')->first();

    //     $hasActiveSession = $latestSession && $latestSession->session_status === 'نشطة';

    //     if ($hasActiveSession) {
    //         return redirect()->route('lawsuits.show', $lawsuit->id)
    //             ->with('error', 'لا يمكنك إضافة جلسة جديدة لأن هناك جلسة لم تغلق بعد.')->withFragment('sessions');
    //     }

    //     $sessionCount = Session::where('lawsuit_id', $lawsuit->id)->count() + 1;

    //     $sessionName = "جلسة رقم" . $sessionCount . " في دعوى " . $lawsuit->name;

    //     $employees = $lawsuit->assignedEmployees()->get();
    //     $projectManager = $lawsuit->project->manager_user->employee;

    //     if ($projectManager && !$employees->contains($projectManager->id)) {
    //         $employees->push($projectManager);
    //     }

    //     $settings_entity_ranks = SettingsEntityRank::select(['id', 'name'])->get();
    //     return view($this->page . '.create', compact('settings_entity_ranks'));
    // }


    public function createWithLawsuit(Lawsuit $lawsuit)
    {
        $latestSession = $lawsuit->sessions()->latest('created_at')->first();

        $hasActiveSession = $latestSession && $latestSession->session_status === 'نشطة';

        if ($hasActiveSession) {
            return redirect()->route('legal-affairs.lawsuits.show', $lawsuit->id)
                ->with('error', 'لا يمكنك إضافة جلسة جديدة لأن هناك جلسة لم تغلق بعد.')->withFragment('sessions');
        }

        $sessionCount = Session::where('lawsuit_id', $lawsuit->id)->count() + 1;

        $sessionName = "جلسة رقم" . $sessionCount . " في دعوى " . $lawsuit->name;

        $employees = $lawsuit->assignedEmployees()->get();
        $projectManager = $lawsuit->project->manager_user->employee;

        if ($projectManager && !$employees->contains($projectManager->id)) {
            $employees->push($projectManager);
        }

        $settings_entity_ranks = SettingsEntityRank::select(['id', 'name'])->get();
        return view($this->page . '.create', compact('lawsuit', 'employees', 'settings_entity_ranks', 'sessionName'));
    }


    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $lawsuit = Lawsuit::findOrFail($request->lawsuit);

        $latestSession = $lawsuit->sessions()->latest('created_at')->first();

        $hasActiveSession = $latestSession && $latestSession->session_status === 'نشطة';

        if ($hasActiveSession) {
            return redirect()->route('lawsuits.show', $lawsuit->id)
                ->with('error', 'لا يمكنك إضافة جلسة جديدة لأن هناك جلسة لم تغلق بعد.')->withFragment('sessions');
        }

        $validated = $request->validate(
            [
                'session_date' => 'required|date',
                'session_time' => 'required|date_format:H:i',
                'entity_ranks_id' => 'required|exists:settings_entity_ranks,id',
                'assigned_to' => 'required|array',
                'assigned_to.*' => 'exists:users,id',
            ],
            [
                'assigned_to.required' => 'يجب اختيار على الأقل مكلف واحد.',
                'assigned_to.*.exists' => 'أحد الموظفين المختارين غير موجود.',
            ]
        );

        $validated['project_id'] = $lawsuit->project->id;
        $validated['lawsuit_id'] = $lawsuit->id;

        DB::beginTransaction();

        try {
            $session = Session::create($validated);

            $assignedEmployees = $validated['assigned_to'];
            $currentUserId = Auth::id();

            $attachData = [];
            foreach ($assignedEmployees as $employeeId) {
                $attachData[$employeeId] = ['user_added_id' => $currentUserId];
            }

            $session->assignedEmployees()->attach($attachData);

            $assignedUserIds = $session->assignedEmployees()->pluck('users.id')->toArray();
            $newTask = [
                'task_name' => 'تم إضافة جلسة جديدة: (' . $session->session_name . ') وتم اضافتك مكلف  فيها',
                'priority' => 'high',
                'description' => 'قام ' . Auth::user()->name . ' بتكليفك في جلسة   (' . $session->session_name . ').',
                'task_field' => 'sessions',
                'due_date' => $session->session_date,
                'due_time'  => $session->session_time,

                'session_id' => $session->id,
                'assigned_user_id' => $assignedUserIds,
                'created_by' => auth()->id(),
                'type_task' => 'SESSION_ASSIGNED'

            ];
            CreateTaskJob::dispatch($newTask);
            DB::commit();

            return redirect()->route('lawsuits.show', $request->lawsuit)->with('success', 'تم إنشاء الجلسة بنجاح وربط الموظفين المكلفين.')->withFragment('sessions');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating session: ' . $e->getMessage());
            return redirect()->back()->withInput()->withErrors(['error' => 'حدث خطأ أثناء إنشاء الجلسة. يرجى المحاولة مرة أخرى.'])->withFragment('sessions');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Send Session Creaed Notification
    |--------------------------------------------------------------------------
    */
    public function sendSessionCreatedNotification($userId, $taskData, $session)
    {
        $settings = Settings::find(1);

        if ($settings->main_email == "") {
            return response()->json([
                'success' => false,
                'message' => 'اعدادات مايكروسوفت غير مكتملة الرجاء مراجعة الاعدادات و المحاولة مرة اخرى'
            ], 400);
        }


        $user = User::find($userId);

        $emailService = app(EmailService::class);
        $emailService->sendSessionCreatedNotification($user, $settings, $taskData, $session);
    }


    /*
    |--------------------------------------------------------------------------
    | Show Session Notification
    |--------------------------------------------------------------------------
    */
    // public function show_session_notification(Request $request, string $lawsuit_id, string $session_notification_id, string $comment_id)
    // {

    //     $lawsuit = Lawsuit::findOrFail($lawsuit_id);

    //     Session::findOrFail($session_notification_id);

    //     $comment = SessionComment::findOrFail($comment_id);

    //     $currentEmployee = Employees::where('user_id', auth()->id())->first();

    //     $userMentioned = SessionCommentMention::where('session_comment_id', $comment->id)
    //         ->where('mentioned_user_id', Auth::id())
    //         ->exists();

    //     if (!$userMentioned) {
    //         return abort(401);
    //     }

    //     $lastLawsuit = Lawsuit::with(['sessions' => function ($query) {
    //         $query->orderBy('created_at', 'desc');
    //     }])->findOrFail($lawsuit_id);

    //     $latestSession = $lastLawsuit->sessions()->latest('created_at')->first();

    //     $hasActiveSession = $latestSession && $latestSession->session_status === 'نشطة';

    //     $sessions = Session::with(['project', 'lawsuit'])
    //         ->where('lawsuit_id', $lawsuit_id)
    //         ->orderBy('id', 'desc')
    //         ->paginate(12);

    //     $sessions_task = Session::with(['project', 'lawsuit'])
    //         ->where('lawsuit_id', $lawsuit_id)
    //         ->orderBy('id', 'desc');

    //     $project = $lawsuit->project;
    //     $employees = $lawsuit->assignedEmployees()->wherePivot('status', 'accepted')->get();

    //     $sessions_task = Session::select(['id', 'session_name'])
    //         ->where('lawsuit_id', $lawsuit_id)
    //         ->orderBy('id', 'desc')->get();


    //     return view('judicial_affairs.lawsuits.sections.sessions', compact(
    //         'lawsuit',
    //         'project',
    //         'sessions',
    //         'hasActiveSession',
    //         'employees',
    //         'session_notification_id',
    //         'comment_id',
    //         'currentEmployee',
    //         'sessions_task'
    //     ));
    // }

    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */
    public function edit(string $id)
    {
        $session = Session::findOrFail($id);
        $sessionDateTime = $session->getSessionDateTime();

        if ($sessionDateTime) {
            if ($sessionDateTime->lte(Carbon::now())) {
                return redirect()->route('lawsuits.show', $session->lawsuit->id)
                    ->with('error', 'لا يمكنك تعديل هذه الجلسة لأنها قد انتهت.')->withFragment('sessions');
            }
        } else {
            return redirect()->route('lawsuits.show', $session->lawsuit->id)
                ->with('error', 'تاريخ أو وقت الجلسة غير محدد أو غير صالح.')->withFragment('sessions');
        }

        $employees = $session->lawsuit->assignedEmployees()->get();
        $projectManager = $session->lawsuit->project->manager_user->employee;

        if ($projectManager && !$employees->contains($projectManager->id)) {
            $employees->push($projectManager);
        }

        $settings_entity_ranks = SettingsEntityRank::select(['id', 'name'])->get();

        return view('judicial_affairs.sessions.edit', compact('session', 'employees', 'settings_entity_ranks'));
    }


    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, Session $session)
    {
        if ($session->session_date && $session->session_time) {
            $sessionDateTimeString = $session->session_date . ' ' . $session->session_time;

            $sessionDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $sessionDateTimeString);

            if ($sessionDateTime->lte(Carbon::now())) {
                return redirect()->route('lawsuits.show', $session->lawsuit->id)
                    ->with('error', 'لا يمكنك تعديل هذه الجلسة لأنها قد انتهت.')->withFragment('sessions');
            }
        } else {
            return redirect()->route('lawsuits.show', $session->lawsuit->id)
                ->with('error', 'تاريخ أو وقت الجلسة غير محدد.')->withFragment('sessions');
        }

        $validated = $request->validate(
            [
                'session_date' => 'required|date',
                'session_time' => 'required|date_format:H:i',
                'entity_ranks_id' => 'required|exists:settings_entity_ranks,id',
                'assigned_to' => 'required|array',
            ],
            [
                'assigned_to.required' => 'يجب اختيار على الأقل مكلف واحد.',
                'assigned_to.*.exists' => 'أحد الموظفين المختارين غير موجود.',
            ]
        );

        $lawsuit = Lawsuit::findOrFail($request->lawsuit);

        $validated['project_id'] = $lawsuit->project->id;
        $validated['lawsuit_id'] = $lawsuit->id;

        DB::beginTransaction();

        try {
            $session->update($validated);
            $assignedEmployees = $validated['assigned_to'];
            $currentUserId = Auth::id();

            $syncData = [];
            foreach ($assignedEmployees as $employeeId) {
                $syncData[$employeeId] = ['user_added_id' => $currentUserId];
            }

            if ($request->has('assigned_to')) {
                $oldAssignedUsers = $session->assignedEmployees()->pluck('users.id')->toArray();

                $newAssignedUsers = $validated['assigned_to'];

                if ($oldAssignedUsers != $newAssignedUsers) {
                    $removedUsers = array_diff($oldAssignedUsers, $newAssignedUsers);

                    $session->assignedEmployees()->sync($syncData);

                    $assignedUserIds = $session->assignedEmployees()->pluck('users.id')->toArray();
                }

                if ($oldAssignedUsers != $newAssignedUsers) {

                    $task = Task::where('session_id', $session->id)
                        ->where('type_task', 'SESSION_ASSIGNED')
                        ->first();

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

                    $task->assignedUsers()->sync($validated['assigned_to']);

                    $newUsers = $task->assignedUsers()->get();

                    $addedUsers = $newUsers->reject(function ($user) use ($previousUsers) {
                        return $previousUsers->contains('id', $user->id);
                    });

                    $removedUsersData = $previousUsersData->reject(function ($userData) use ($newUsers) {
                        return $newUsers->contains('id', $userData['id']);
                    });

                    $existingUsers = $newUsers->reject(function ($user) use ($addedUsers) {
                        return $addedUsers->contains('id', $user->id);
                    });

                    $newTask = [
                        'task_id' => $task->id,
                        'task_name' => 'تم إضافة جلسة جديدة: (' . $session->session_name . ') وتم اضافتك مكلف  فيها',
                        'task_priority' => 'high',
                        'description' => 'قام ' . Auth::user()->name . ' بتكليفك في جلسة   (' . $session->session_name . ').',
                        'endDateTime' => $this->formatDateTime($task),
                        'showEndDate' => true,
                    ];

                    if ($addedUsers->isNotEmpty()) {
                        SyncTaskWithMicrosoftJob::dispatch(
                            assignedUsers: $addedUsers,
                            taskData: $newTask,
                            officeName: Settings::find(1)->office_name
                        );
                    }

                    if ($removedUsersData->isNotEmpty()) {
                        BatchDeleteMicrosoftTaskJob::dispatch(
                            task: $task,
                            removedUsersData: $removedUsersData->all()
                        );
                    }

                    if ($existingUsers->isNotEmpty()) {
                        BatchUpdateMicrosoftTaskJob::dispatch(
                            task: $task,
                            taskData: $newTask,
                            officeName: Settings::find(1)->office_name,
                            existingUsers: $existingUsers,
                        );
                    }
                }
            }

            DB::commit();

            return redirect()->route('lawsuits.show', $request->lawsuit)->with('success', 'تم تحديث الجلسة بنجاح .')->withFragment('sessions');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors(['error' => 'حدث خطأ أثناء تحديث الجلسة. يرجى المحاولة مرة أخرى.'])->withFragment('sessions');
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Format Date Time
    |--------------------------------------------------------------------------
    */
    private function formatDateTime(Task $task): string
    {
        return Carbon::parse($task->due_date . ' ' . $task->due_time)
            ->setTimezone('Asia/Riyadh')
            ->toIso8601String();
    }

    /*
    |--------------------------------------------------------------------------
    | Destroy
    |--------------------------------------------------------------------------
    */
    public function destroy(Session $session)
    {
        if (Auth::check()) {
            $session->delete();
            return redirect()->back()->with('success', 'تم حذف الجلسة  بنجاح')->withFragment('sessions');
        }

        return redirect()->back()->with('error', 'غير مصرح لك بحذف هذا الجلسة ')->withFragment('sessions');
    }

    /*
    |--------------------------------------------------------------------------
    | Trashed
    |--------------------------------------------------------------------------
    */
    public function trashed(Request $request)
    {
        $route = 'sessions'; // تأكد من أن المتغير يشير إلى الجلسات

        if ($request->ajax()) {
            $sessions = Session::onlyTrashed()
                ->with([
                    'lawsuit.department_contract_cases',
                    'lawsuit.project',
                    'lawsuit.entitie',
                    'assignedEmployees'
                ])
                ->select([
                    'id',
                    'session_name',
                    'lawsuit_id',
                    'session_date', // assuming 'gregorian_date' is cast from 'session_date'
                    'deleted_at',
                    // أضف الحقول الأخرى التي تحتاجها من جدول الجلسات
                ])
                ->orderBy('id', 'desc');

            return datatables()->of($sessions)
                ->addIndexColumn()
                ->addColumn('session_name', function ($row) {
                    $url = route('lawsuit_section.sessions', $row->id);
                    return $row->session_name ? '<a href="' . $url . '">' . e($row->session_name) . '</a>' : 'غير متوفر';
                })
                ->addColumn('employee_name', function ($row) {
                    return $row->assignedEmployees->pluck('name')->join(', ');
                })
                ->addColumn('lawsuit_name', function ($row) {
                    return $row->lawsuit ? $row->lawsuit->name : 'غير متوفر';
                })
                ->addColumn('gregorian_date', function ($row) {
                    return $row->gregorian_date ? $row->gregorian_date->format('d/m/Y') : 'غير متوفر';
                })
                ->addColumn('deleted_at_formatted', function ($row) {
                    return $row->deleted_at ? Hijri::Date($row->deleted_at) : '';
                })
                ->addColumn('action', function ($row) {
                    $restoreUrl = route('sessions.restore', $row->id);
                    $forceDeleteUrl = route('sessions.forceDelete', $row->id);
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
                ->rawColumns(['action', 'session_name', 'lawsuit_name'])
                ->make(true);
        }

        return view('judicial_affairs.lawsuits.sections.trashed', compact('route'));
    }

    /*
    |--------------------------------------------------------------------------
    | Restore
    |--------------------------------------------------------------------------
    */
    public function restore($id)
    {
        try {
            $session = Session::onlyTrashed()->findOrFail($id);
            $session->restore();

            return redirect()->route('sessions.trashed')->with('success', 'تم استعادة الجلسة بنجاح.');
        } catch (\Exception $e) {

            return redirect()->back()->with('error', 'حدث خطأ أثناء استعادة الجلسة. يرجى المحاولة لاحقاً.');
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Force Delete
    |--------------------------------------------------------------------------
    */
    public function forceDelete($id)
    {
        try {
            $session = Session::onlyTrashed()->findOrFail($id);

            $tasks = Task::where('session_id', $session->id)->get();

            // حذف الجلسة نهائيًا
            $session->forceDelete();

            return redirect()->route('sessions.trashed')->with('success', 'تم حذف الجلسة نهائيًا بنجاح.');
        } catch (\Exception $e) {

            return redirect()->back()->with('error', 'حدث خطأ أثناء حذف الجلسة نهائيًا. يرجى المحاولة لاحقاً.');
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Chat With
    |--------------------------------------------------------------------------
    */
    public function chatWith()
    {
        return view('judicial_affairs.lawsuits.sections.chat');
    }

    /*
    |--------------------------------------------------------------------------
    | Send Wha
    |--------------------------------------------------------------------------
    */
    public function sendWhatsappMessage($session)
    {
        $plaintiffs = DB::table('lawsuit_plaintiffs')->where('lawsuit_id', $session->lawsuit->id)->get();
        $defendants = DB::table('lawsuit_defendants')->where('lawsuit_id', $session->lawsuit->id)->get();

        $customerIds = [];

        foreach ($plaintiffs as $plaintiff) {
            if ($plaintiff->plaintiff_type == 'App\\Models\\business_development\\Customers') {
                $customerIds[] = $plaintiff->plaintiff_id;
            }
        }

        foreach ($defendants as $defendant) {
            if ($defendant->defendant_type == 'App\\Models\\business_development\\Customers') {
                $customerIds[] = $defendant->defendant_id;
            }
        }

        $customerIds = array_unique($customerIds);

        if (!empty($customerIds)) {
            $customers = Customers::with(['relationshipManagerEmployee', 'authorizations'])
                ->whereIn('id', $customerIds)
                ->get();

            foreach ($customers as $customer) {
                $relationshipManager = $customer->relationshipManagerEmployee;

                if ($relationshipManager instanceof \App\Models\Hr\Employees\Employees) {
                    $relationshipManagerName = $relationshipManager->name ?? 'غير محدد';
                    $relationshipManagerMobile = $relationshipManager->mobile ?? 'غير متوفر';
                    $relationshipManagerEmail = $relationshipManager->work_email ?? 'غير متوفر';
                } else {
                    $relationshipManagerName = 'غير محدد';
                    $relationshipManagerMobile = 'غير متوفر';
                    $relationshipManagerEmail = 'غير متوفر';
                }

                $mediaUrl = $session->session_control_attached
                    ? basename($session->session_control_attached)
                    : null;

                $messageData = [
                    'session_name' => $session->session_name ?? 'غير محدد',
                    'summary_report' => $session->summary_report_status ?? 'غير محدد',
                    'notes' => $session->notes ?? 'لا توجد ملاحظات',
                    'relationship_manager_name' => $relationshipManagerName,
                    'relationship_manager_mobile' => $relationshipManagerMobile,
                    'relationship_manager_email' => $relationshipManagerEmail,
                    'media_url' => $mediaUrl,
                ];

                $whatsAppController = new \App\Http\Controllers\whatsapp\WhatsAppController();

                // إرسال للعميل نفسه (بغض النظر عن نوعه)
                if ($customer->contact_number) {
                    $requestForCustomer = new Request(array_merge(
                        $messageData,
                        ['customer_ids' => [$customer->id]]
                    ));
                    $whatsAppController->sendMassMessage($requestForCustomer);
                }

                // إذا كان العميل مؤسسة، أرسل للمفوضين
                if ($customer->customer_type === 'company') {
                    foreach ($customer->authorizations as $authorization) {
                        if ($authorization->phone) {
                            // إنشاء طلب جديد لكل مفوض
                            $requestForAuth = new Request(array_merge(
                                $messageData,
                                ['customer_ids' => [$customer->id]]
                            ));
                            // إرسال الرسالة للمفوض
                            $whatsAppController->sendMassMessage($requestForAuth, $authorization->phone);
                        }
                    }
                }
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Set Session Completion
    |--------------------------------------------------------------------------
    */
    public function set_session_completion(Request $request, Session $session)
    {
        $sessionDateTime = $session->getSessionDateTime();

        if ($sessionDateTime) {
            if ($sessionDateTime->gt(Carbon::now())) {
                return redirect()->route('lawsuit_section.sessions', $session->lawsuit->id)
                    ->with('error', 'لا يمكنك ضبط استكمال هذه الجلسة لأن تاريخ نهايتها لم يأتِ بعد.')->withFragment('sessions');
            }
        } else {
            return redirect()->route('lawsuit_section.sessions', $session->lawsuit->id)
                ->with('error', 'تاريخ أو وقت الجلسة غير محدد أو غير صالح.')->withFragment('sessions');
        }

        if ($session->session_status == 'مغلقة') {
            return redirect()->route('lawsuit_section.sessions', $session->lawsuit->id)
                ->with('error', 'لا يمكنك ضبط استكمال هذه الجلسة لأن الجلسة مغلقة.')->withFragment('sessions');
        }

        if ($request->isMethod('put')) {
            $filePaths = [];

            $rules = [
                'summary_report_status' => 'required',
                'session_type' => 'nullable',
                'execution_minutes' => 'required',
                'notes' => 'nullable',
                'session_control_attached' => 'nullable|file|max:2048',
                'last_objection_deadline' => 'nullable|date',
                'rule_type' => 'nullable|integer',
                'execution_format' => 'nullable',
                'rule_attached' => 'nullable|file|max:2048',
                'expected_execution_date' => 'nullable|date',
            ];

            if ($request->input('summary_report_status') == 'حكم موضوعي' || $request->input('summary_report_status') == 'حكم شكلي') {
                $rules['last_objection_deadline'] = 'required|date';
            }

            if ($request->input('session_type') == '1') {
                $rules['rule_type'] = 'nullable';
                $rules['execution_format'] = 'nullable';
                $rules['expected_execution_date'] = 'nullable';
            } elseif ($request->input('session_type') == '2') {
                $rules['rule_type'] = 'required';

                if ($request->input('rule_type') != '1') {
                    $rules['execution_format'] = 'nullable';
                    $rules['expected_execution_date'] = 'nullable';

                    if ($request->input('execution_format') == 'نعم') {
                        $rules['expected_execution_date'] = 'nullable';
                    }
                } elseif ($request->input('rule_type') == '1') {
                    $rules['execution_format'] = 'required';
                    if ($request->input('execution_format') == 'لا') {
                        $rules['expected_execution_date'] = 'required';
                    }
                }
            }

            $validated = $request->validate($rules);

            if ($request->input('summary_report_status') != 'حكم موضوعي' && $request->input('summary_report_status') != 'حكم شكلي') {
                $validated['last_objection_deadline'] = null;
            }

            if ($validated['session_type'] == '1') {
                $validated['rule_type'] = null;
                $validated['execution_format'] = null;
                $validated['expected_execution_date'] = null;
            } elseif ($validated['session_type'] == '2') {
                if ($validated['rule_type'] != '1') {
                    $validated['execution_format'] = null;
                    $validated['expected_execution_date'] = null;
                } elseif ($validated['rule_type'] == '1' && $validated['execution_format'] == 'نعم') {
                    $validated['expected_execution_date'] = null;
                }
            }

            if ($request->user_confirmation == 'نعم') {
                $session->lawsuit->lawsuit_status = 'inactive';
            } elseif ($request->user_confirmation == 'لا') {
                $session->lawsuit->lawsuit_status = 'active';
            }

            if (!empty($request->last_objection_deadline)) {
                $dateValue = $request->last_objection_deadline;

                if (preg_match('/^(13|14|15)\d{2}-\d{2}-\d{2}$/', $dateValue)) {
                    $gregorianDate = Hijri::DateToGregorianFromDMY(
                        substr($dateValue, -2), // اليوم
                        substr($dateValue, 5, 2), // الشهر
                        substr($dateValue, 0, 4) // السنة
                    );
                    $parsedDate = Carbon::parse($gregorianDate);
                } else {
                    // إذا كان التاريخ ميلادياً
                    $parsedDate = Carbon::parse($dateValue);
                }

                // التحقق إذا كان التاريخ أقل من أو يساوي اليوم
                if ($parsedDate->lte(Carbon::today())) {
                    return redirect()->back()
                        ->with('error', 'تاريخ آخر مهلة للاعتراض يجب أن يكون أكبر من تاريخ اليوم ')->withFragment('sessions');
                }
            }



            if ($request->hasFile('session_control_attached') || $request->hasFile('rule_attached')) {
                $validated['session_status'] = 'مغلقة';

                // هنا  يتم اغلاق الجلسة

                // هنا  يتم اغلاق الجلسة
            }



            if ($request->hasFile('session_control_attached')) {
                if ($session->session_control_attached) {
                    Storage::disk('public')->delete($session->session_control_attached);
                }
                $sessionFile = $request->file('session_control_attached');
                $filePaths['session_control_attached'] = $sessionFile->store('uploads/sessions', 'public');
            }

            if ($request->hasFile('rule_attached')) {
                if ($session->rule_attached) {
                    Storage::disk('public')->delete($session->rule_attached);
                }
                $ruleFile = $request->file('rule_attached');
                $filePaths['rule_attached'] = $ruleFile->store('uploads/rules', 'public');
            }

            $validated['session_control_attached'] = $filePaths['session_control_attached'] ?? $session->session_control_attached;
            $validated['rule_attached'] = $filePaths['rule_attached'] ?? $session->rule_attached;

            DB::beginTransaction();
            try {
                $session->update($validated);

                if (isset($session->lawsuit) && $session->lawsuit->isDirty('lawsuit_status')) {
                    $session->lawsuit->save();
                }

                DB::commit();


                // عند ارفاق ملف ضبط الجلسة
                if ($request->hasFile('session_control_attached')) {
                    $this->sendWhatsappMessage($session);
                }



                return redirect()->route('lawsuits.show', $session->lawsuit->id)
                    ->with('success', 'تم حفظ التعديلات بنجاح .')->withFragment('sessions');
            } catch (\Exception $e) {
                DB::rollBack();

                Log::error('Error occurred in set_session_completion:', [
                    'error_message' => $e->getMessage(),
                    'error_trace' => $e->getTraceAsString(),
                    'session_id' => $session->id,
                    'user_id' => auth()->id(),
                    'request_data' => $request->all(),
                ]);

                return redirect()->back()
                    ->with('error', 'حدث خطأ أثناء حفظ التعديلات. يرجى المحاولة مرة أخرى.')->withFragment('sessions');
            }
        }

        $sessionType = SettingsSessionType::select(['id', 'name'])->get();
        $ruleType = SettingsTypeRulings::select(['id', 'name'])->get();
        $employees = Employees::active()->select('id', 'name', 'nickname')->get();
        $settings_entity_ranks = SettingsEntityRank::select(['id', 'name'])->get();

        return view('judicial_affairs.lawsuits.sections.set_session_completion', compact('session', 'employees', 'settings_entity_ranks', 'ruleType', 'sessionType'));
    }

    /*
    |--------------------------------------------------------------------------
    | Submit Objection
    |--------------------------------------------------------------------------
    */
    public function submitObjection(Request $request)
    {
        $session = Session::find($request->input('session_id'));

        if ($session) {
            // تعديل حالة الاعتراض
            $session->objection_status = true; // تعيين الاعتراض كـ "تم الاعتراض"
            $session->session_status = 'مغلقة'; // تعيين الاعتراض كـ "تم الاعتراض"

            $session->save();

            return response()->json(['success' => true, 'message' => 'تم تقديم الاعتراض  واغلاق الجلسة!']);
        } else {
            return response()->json(['success' => false, 'message' => 'الجلسة غير موجودة.'], 404);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Close Lawsuit
    |--------------------------------------------------------------------------
    */
    public function closeLawsuit(Request $request)
    {
        $lawsuit = Lawsuit::find($request->lawsuit_id);

        $lawsuit->lawsuit_status = false;
        $lawsuit->save();

        return response()->json(['success' => true, 'message' => 'تم إغلاق الدعوى بنجاح.']);
    }
}
