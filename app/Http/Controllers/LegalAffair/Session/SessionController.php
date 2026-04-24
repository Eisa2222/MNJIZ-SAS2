<?php

namespace App\Http\Controllers\LegalAffair\Session;

use Alkoumi\LaravelHijriDate\Hijri;
use App\Data\LegalAffair\Session\SessionData;
use App\DataTables\LegalAffairs\Session\SessionsDataTable;
use App\Enums\LegalAffair\Lawsuit\LawsuitStatus;
use App\Enums\LegalAffair\Session\SessionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\LegalAffair\Session\StoreSessionRequest;
use App\Models\OperationsCenter\Customer\Customers;
use App\Models\general_setting\SettingsEntityRank;
use App\Models\general_setting\SettingsSessionType;
use App\Models\GeneralSetting\SystemSetting\Settings;
use App\Models\User;
use App\Services\EmailService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Jobs\Tasks\SyncTaskWithMicrosoftJob;
use App\Models\judicial_affairs\Project;
use App\Models\LegalAffair\Lawsuit\Lawsuit;
use App\Models\LegalAffair\Session\Session;
use App\Models\Task\Task;
use App\Services\LegalAffair\Session\SessionService;

class SessionController extends Controller
{
    private $route  = "legal-affairs.sessions";
    private $page   = "legal_affairs.sessions";

    public function __construct(private SessionService $service)
    {
        $this->middleware(function ($request, $next) {
            if ($request->user()->can('كل الجلسات') || $request->user()->can('الجلسات الخاصة بي')) {
                return $next($request);
            }
            abort(403);
        })->only(['index', 'show']);

        $this->middleware('can:إضافة جلسة')->only(['create', 'store']);
        $this->middleware('can:تعديل جلسة')->only(['edit', 'update']);
        $this->middleware('can:حذف جلسة')->only(['destroy']);
    }

    public function index(SessionsDataTable $dataTable)
    {
        $query = Session::query();

        try {
            if (auth()->user()->can('الجلسات الخاصة بي') && !auth()->user()->can('كل الجلسات')) {
                $userId         = auth()->user()->id;
                $employeeId = auth()->user()->employee->id;

                $query->where(function ($q) use ($userId, $employeeId) {
                    // الجلسات التي أنشأها المستخدم
                    $q->where('created_by', $employeeId)
                        // أو الجلسات التي هو مكلف فيها
                        ->orWhereHas('assignedUsers', function ($subQuery) use ($userId) {
                            $subQuery->where('assigned_to', $userId);
                        });
                });
            }


            // Statistics
            $statusCounts = $query
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


    public function create()
    {
        $projects = Project::whereHas('lawsuits', function ($query) {
            $query->where('lawsuit_status', LawsuitStatus::Active);
        })->select('id', 'project_name')->get();

        $settings_entity_ranks  = SettingsEntityRank::select(['id', 'name'])->get();

        return view($this->page . '.create', compact('projects', 'settings_entity_ranks'));
    }


    public function store(StoreSessionRequest $request)
    {
        try {
            $dto = new SessionData($request->validated());

            $this->service->create($dto);

            return redirect()->route($this->route . '.index')->with('success', 'تم اضافة الجلسة');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }


    public function show(string $id)
    {
        $session    = Session::findOrFail($id);
        $employees  = $session->lawsuit->assignedEmployees()->get();

        if (auth()->user()->can('الجلسات الخاصة بي') && !auth()->user()->can('كل الجلسات')) {
            $userId     = auth()->user()->id;
            $employeeId = auth()->user()->employee->id;

            $userRelatedSession = Session::where('id', $session->id)
                ->where(function ($q) use ($userId, $employeeId) {
                    $q->where('created_by', $employeeId)
                        ->orWhereHas('assignedUsers', function ($subQuery) use ($userId) {
                            $subQuery->where('assigned_to', $userId);
                        });
                })->first();

            if (!$userRelatedSession) {
                abort(404);
            }
        }

        return view($this->page . '.show', compact('session', 'employees'));
    }

    public function edit(string $id)
    {
        $session = Session::findOrFail($id);

        if (auth()->user()->can('الجلسات الخاصة بي') && !auth()->user()->can('كل الجلسات')) {
            $userId     = auth()->user()->id;
            $employeeId = auth()->user()->employee->id;

            $userRelatedSession = Session::where('id', $session->id)
                ->where(function ($q) use ($userId, $employeeId) {
                    $q->where('created_by', $employeeId)
                        ->orWhereHas('assignedUsers', function ($subQuery) use ($userId) {
                            $subQuery->where('assigned_to', $userId);
                        });
                })->first();

            if (!$userRelatedSession) {
                abort(404);
            }
        }

        $sessionDateTime = $session->getSessionDateTime();

        if ($sessionDateTime) {
            if ($sessionDateTime->lte(Carbon::now())) {
                return redirect()->route($this->route . '.index')
                    ->with('error', 'لا يمكنك تعديل هذه الجلسة لأنها قد انتهت.');
            }
        }


        $projects = Project::whereHas('lawsuits', function ($query) {
            $query->where('lawsuit_status', LawsuitStatus::Active);
        })->select('id', 'project_name')->get();

        $settings_entity_ranks  = SettingsEntityRank::select(['id', 'name'])->get();


        return view($this->page . '.edit', compact('session', 'projects', 'settings_entity_ranks'));
    }


    public function update(StoreSessionRequest $request, Session $session)
    {
        try {
            $dto = new SessionData($request->validated());

            $this->service->update($session->id, $dto);

            return redirect()->route($this->route . '.index')
                ->with('success', 'تم تحديث الجلسة بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }


    public function destroy(Session $session)
    {
        if (Auth::check()) {
            $session->delete();
            return redirect()->back()->with('success', 'تم حذف الجلسة  بنجاح')->withFragment('sessions');
        }

        return redirect()->back()->with('error', 'غير مصرح لك بحذف هذا الجلسة ')->withFragment('sessions');
    }


    public function objection(Session $session)
    {
        try {
            $this->service->objection($session->id);

            return response()->json([
                'success' => true,
                'message' => 'تم تقديم الاعتراض وإغلاق الجلسة',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
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
                    $url = route('legal-affairs.lawsuit_section.sessions', $row->id);
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
        // Defense-in-depth: restrict raw DB reads to current tenant so
        // a stale $session->lawsuit link can never leak cross-firm rows.
        $currentTenantId = \App\Tenancy\TenantContext::currentId();
        $plaintiffs = DB::table('lawsuit_plaintiffs')
            ->where('lawsuit_id', $session->lawsuit->id)
            ->where('tenant_id', $currentTenantId)
            ->get();
        $defendants = DB::table('lawsuit_defendants')
            ->where('lawsuit_id', $session->lawsuit->id)
            ->where('tenant_id', $currentTenantId)
            ->get();

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
    |============================================================================
    |============================================================================
    |                              API
    |============================================================================
    |============================================================================
    */
    public function getLawsuits(Request $request)
    {
        $projectId = $request->get('project_id');

        if (!$projectId) {
            return response()->json(['lawsuits' => []]);
        }

        $lawsuits = Lawsuit::where('lawsuit_status', LawsuitStatus::Active)->where('project_id', $projectId)
            ->select('id', 'name')
            ->get();

        return response()->json(['lawsuits' => $lawsuits]);
    }

    public function getLawsuitDetails(Request $request)
    {
        $lawsuitId = $request->get('lawsuit_id');

        if (!$lawsuitId) {
            return response()->json(['employees' => [], 'session_name' => '']);
        }


        $lawsuit = Lawsuit::with(['project.manager_user'])->find($lawsuitId);


        if (!$lawsuit) {
            return response()->json(['employees' => [], 'session_name' => '']);
        }
        $employees =  $lawsuit->assignedEmployees()->get();

        $projectManager = $lawsuit->project->manager_user->employee;


        if ($projectManager && !$employees->contains($projectManager->id)) {
            $employees->push($projectManager);
        }

        // dd($employees);

        $sessionCount = Session::withTrashed()
            ->where('lawsuit_id', $lawsuitId)
            ->count() + 1;

        $sessionName = "جلسة رقم {$sessionCount} في دعوى {$lawsuit->name}";

        return response()->json([
            'employees'     => $employees->toArray(),
            'manager_id'    => $projectManager->id,
            'session_name'  => $sessionName
        ]);
    }
}
