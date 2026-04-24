<?php

namespace App\Http\Controllers\LegalAffair\Lawsuit;

use Alkoumi\LaravelHijriDate\Hijri;
use App\DataTables\LegalAffairs\Lawsuit\LawsuitsDataTable;
use App\Enums\LegalAffair\Lawsuit\LawsuitStatus;
use App\Enums\LegalAffair\Session\SessionStatus;
use App\Enums\Survey\SurveyType;
use App\Http\Controllers\Controller;
use App\Http\Requests\LegalAffair\Lawsuit\StoreLawsuitRequest;
use App\Http\Requests\LegalAffair\Lawsuit\UpdateLawsuitRequest;
use App\Jobs\Mail\SendEmailNotificationJob;
use App\Jobs\Tasks\CreateTaskJob;
use App\Jobs\Tasks\BatchDeleteMicrosoftTaskJob;
use App\Models\OperationsCenter\Customer\Customers;
use App\Models\general_setting\SettingsCategories;
use App\Models\general_setting\SettingsDepartmentContractCase;
use App\Models\general_setting\SettingsLawsuitsType;
use App\Models\general_setting\SettingsMainCourt;
use App\Models\general_setting\SettingsRegion;
use App\Models\general_setting\SettingsSubcategories;
use App\Models\Hr\Employees\Employees;
use App\Models\MeetingNote;
use App\Models\GeneralSetting\SystemSetting\Settings;
use App\Models\judicial_affairs\Project;
use App\Models\LegalAffair\Lawsuit\AssignedLawsuits;
use App\Models\LegalAffair\Lawsuit\Lawsuit;
use App\Models\LegalAffair\Lawsuit\LawsuitAttachment;
use App\Models\LegalAffair\Opponent\Opponent;
use App\Models\LegalAffair\PowerOfAttorney\PowerOfAttorney;
use App\Models\LegalAffair\Session\Session;
use App\Models\OrganizationCenter\Tasks\Task\Task;
use App\Models\User;
use App\Services\MicrosoftGraphBaseService;
use App\Services\MicrosoftTeamsService;
use App\Services\SMS\SurveySmsService;
use App\Tenancy\Support\TenantStorage;
use Beta\Microsoft\Graph\Model\Employee;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Yajra\DataTables\Facades\DataTables;

class LawsuitController extends Controller
{
    // protected $graphService;
    // protected $teamsService;
    // public $office_name;


    private $route  = "legal-affairs.lawsuits";
    private $page   = "legal_affairs.lawsuits";


    public function __construct(MicrosoftGraphBaseService $graphService, MicrosoftTeamsService $teamsService, private SurveySmsService $surveySmsService)
    {
        $this->office_name = Settings::find(1)->office_name;

        $this->middleware(function ($request, $next) {
            if ($request->user()->can('كل الدعاوى') || $request->user()->can('الدعاوى الخاصة بي')) {
                return $next($request);
            }
            abort(403);
        })->only(['index', 'show']);

        $this->middleware('can:إضافة دعوى')->only(['create', 'store']);
        $this->middleware('can:تعديل دعوى')->only(['edit', 'update', 'toggleStatus']);
        $this->middleware('can:حذف دعوى')->only(['destroy']);

        $this->teamsService = $teamsService;
        $this->graphService = $graphService;
    }

    protected function getAccessToken_old()
    {
        $user = Auth::user();

        if (!$user->microsoft_token) {
            return redirect()->route('microsoft.login')->with('error', 'يرجى ربط حساب Microsoft الخاص بك.');
        }

        // الحصول على رمز الوصول الصالح
        $accessToken = $this->graphService->getValidUserAccessToken($user);

        if (!$accessToken) {
            return redirect()->route('dashboard')->with('error', 'فشل في تجديد رمز الوصول. يرجى إعادة ربط حساب Microsoft.');
        }

        // تعيين رمز الوصول في كائن Graph الخاص بـ MicrosoftTeamsService إذا كان مطلوبًا
        // $this->teamsService->setGraphAccessToken($accessToken);

        return $accessToken;
    }


    public function index(LawsuitsDataTable $dataTable)
    {
        $query = Lawsuit::query();
        try {

            if (auth()->user()->can('الدعاوى الخاصة بي') && !auth()->user()->can('كل الدعاوى')) {
                $query->where(function ($q) {
                    $q->where('created_by', auth()->id())
                        ->orWhereHas('assignedEmployees', function ($q2) {
                            $q2->where('user_id', auth()->id());
                        });
                });
            }

            // Statistics
            $statusCounts = $query
                ->select('lawsuit_status')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('lawsuit_status')
                ->pluck('count', 'lawsuit_status')
                ->toArray();

            $totalLawsuits          = array_sum($statusCounts);
            $activeLawsuits         = $statusCounts[LawsuitStatus::Active->value] ?? 0;
            $inActiveLawsuits       = $statusCounts[LawsuitStatus::Inactive->value] ?? 0;
            $sessions               = Session::whereHas('lawsuit', function ($query) {
                $query->where('lawsuit_status', LawsuitStatus::Active->value);
            })->count();

            // Filters
            $projects           = Project::select(['id', 'project_name'])->get();
            $lawsuitsType       = SettingsLawsuitsType::select(['id', 'name'])->get();
            $mainCourt          = SettingsMainCourt::select(['id', 'name'])->get();


            return $dataTable->render($this->page . '.index', compact(
                // Statistics
                'totalLawsuits',
                'activeLawsuits',
                'inActiveLawsuits',
                'sessions',
                // Filters
                'projects',
                'lawsuitsType',
                'mainCourt',
            ));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    }


    public function create()
    {
        $categories             = SettingsDepartmentContractCase::where('status', 'active')->select(['id', 'name'])->orderBy('id', 'desc')->get();
        $projects               = Project::select(['id', 'project_name'])->orderBy('id', 'desc')->get();
        $settings_main_courts   = SettingsMainCourt::where('status', 'active')->select(['id', 'name'])->orderBy('id', 'desc')->get();
        $power_of_attorneys     = PowerOfAttorney::where('status', 'active')->select(['id', 'power_name', 'power_number'])->orderBy('id', 'desc')->get();
        $settings_regions       = SettingsRegion::where('status', 'active')->select(['id', 'name'])->orderBy('id', 'desc')->get();
        $mainCategories         = SettingsCategories::where('status', 'active')->select(['id', 'name'])->where('status', 'active')->orderBy('id', 'desc')->get();

        $opponents              = Opponent::select(['id', 'name'])->orderBy('id', 'desc')->get()->map(function ($item) {
            $item->thisType = 'opponent';
            return $item;
        });

        $customers              = Customers::select(['id', 'name'])->orderBy('id', 'desc')->get()->map(function ($item) {
            $item->thisType = 'customer';
            return $item;
        });

        // دمج القائمتين
        $mergedList = $opponents->concat($customers);


        return view($this->page . '.create', compact(
            'categories',
            'projects',
            'settings_main_courts',
            'settings_regions',
            'mergedList',
            'power_of_attorneys',
            'mainCategories',
        ));
    }


    public function store(StoreLawsuitRequest $request)
    {
        $validatedData = $request->validated();

        DB::beginTransaction();

        try {
            $lawsuitData = array_merge([
                'name'                          => $validatedData['name'],
                'lawsuit_number'                => $validatedData['lawsuit_number'],
                'project_id'                    => $validatedData['project_id'],
                'main_courts_id'                => $validatedData['main_courts_id'],
                'regions_id'                    => $validatedData['regions_id'],
                'category_id'                   => $validatedData['category_id'],
                'subcategory_id'                => $validatedData['subcategory_id'],
                'lawsuit_type_id'               => $validatedData['lawsuit_type_id'],
                'created_by'                    => Auth::id(),
                // الحقول الاختيارية
                'circle'                        => $validatedData['circle']             ?? null,
                'lawsuit_subject'               => $validatedData['lawsuit_subject']    ?? null,
                'plaintiff_requests'            => $validatedData['plaintiff_requests'] ?? null,
                'lawsuit_proofs'                => $validatedData['lawsuit_proofs']     ?? null,
            ]);

            // إنشاء سجل جديد في قاعدة البيانات
            $lawsuit = Lawsuit::create($lawsuitData);

            if ($request->has('assigned_to')) {
                $lawsuit->assignedEmployees()->attach($validatedData['assigned_to']);

                $assignedUserIds = $lawsuit->assignedEmployees()->pluck('user_id')->toArray();

                foreach ($assignedUserIds as $userId) {
                    $user_for_task = User::find($userId);
                    $newTask = [
                        'user_name' => $user_for_task->name,
                        'title' => 'تم تعيينك في فريق العمل الخاص بدعوى: (' . $lawsuit->name . ').',
                        'description' => 'قام ' . Auth::user()->name . '  بتعيينك في فريق العمل الخاص بدعوى (' . $lawsuit->name . ').',
                        'office_name' => $this->office_name

                    ];
                    SendEmailNotificationJob::dispatch([
                        'email' => $user_for_task->email,
                    ], $newTask);
                }
            }
            // للوكالات
            if ($request->has('power_of_attorney_id')) {
                $lawsuit->powerOfAttorneys()->sync($request->power_of_attorney_id);
            }


            // إضافة المدعين إلى جدول lawsuit_plaintiffs
            // Raw DB::table() here bypasses the Model global scope, so we
            // MUST add tenant_id explicitly to keep the row tenant-bound.
            $currentTenantId = \App\Tenancy\TenantContext::currentId();
            foreach ($validatedData['plaintiff_id'] as $plaintiff) {
                list($plaintiffId, $plaintiffType) = explode('-', $plaintiff); // تقسيم المدعي (معرف - نوع)
                DB::table('lawsuit_plaintiffs')->insert([
                    'tenant_id' => $currentTenantId,
                    'lawsuit_id' => $lawsuit->id,
                    'plaintiff_id' => $plaintiffId,
                    'plaintiff_type' => $plaintiffType == 'customer' ? 'App\Models\OperationsCenter\Customer' : 'App\Models\LegalAffair\Opponent',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // إضافة المدعى عليهم إلى جدول lawsuit_defendants
            foreach ($validatedData['defendant_id'] as $defendant) {
                list($defendantId, $defendantType) = explode('-', $defendant); // تقسيم المدعى عليه (معرف - نوع)
                DB::table('lawsuit_defendants')->insert([
                    'tenant_id' => $currentTenantId,
                    'lawsuit_id' => $lawsuit->id,
                    'defendant_id' => $defendantId,
                    'defendant_type' => $defendantType == 'customer' ? 'App\Models\OperationsCenter\Customer' : 'App\Models\LegalAffair\Opponent',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            return redirect()->route('legal-affairs.lawsuits.index')->with('success', 'تم إنشاء الدعوى بنجاح.');
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollBack();
            return redirect()->back()->with('error', 'حدث خطأ أثناء إنشاء الدعوى. الرجاء المحاولة مرة أخرى.');
        }
    }


    public function show(Request $request, string $id)
    {
        $lawsuit = Lawsuit::findOrFail($id);


        if (!auth()->user()->can('كل الدعاوى')) {
            $isCreator  = $lawsuit->created_by === auth()->id();
            $isAssigned = $lawsuit->assignedEmployees->contains('user_id', auth()->id());

            if (!($isCreator || $isAssigned)) {
                abort(404);
            }
        }

        $acceptedAssignedTeam = $lawsuit->assignedEmployees
            ->count(); // حساب العدد

        $userId = Auth::id();

        // التحقق مما إذا كان المستخدم مكلفًا بالدعوى وحالته مقبولة
        $isAssigned = AssignedLawsuits::where('lawsuit_id', $lawsuit->id)
            ->where('assigned_to', $userId)
            ->where('status', 'accepted')
            ->exists();


        $manager_user = Lawsuit::find($lawsuit->id)->project()
            ->where('manager_user_id', Auth::id())
            ->exists();

        $lastLawsuit = Lawsuit::with(['sessions' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }])->findOrFail($id);

        // جلب آخر جلسة
        $latestSession = $lastLawsuit->sessions()->latest('created_at')->first();

        // التحقق مما إذا كانت آخر جلسة نشطة
        $hasActiveSession = $latestSession && $latestSession->session_status->value === 'active';
        // جلب الموظف المرتبط بالمستخدم المتصل
        $currentEmployee = Employees::where('user_id', auth()->id())->first();
        // جلب أول 5 جلسات
        $sessions = Session::with(['project', 'lawsuit'])
            ->where('lawsuit_id', $id)
            ->orderBy('id', 'desc')
            ->paginate(12);

        $sessions_task = Session::select(['id', 'session_name'])
            ->where('lawsuit_id', $id)
            ->orderBy('id', 'desc')->get();

        $project = $lawsuit->project;


        $employees = $lawsuit->assignedEmployees()->get();
        $users = User::select('id', 'name')->get();


        // الحصول على مدير المشروع (افتراضاً أن هناك علاقة projectManager في نموذج القضية)
        $projectManager = $lawsuit->project->manager_user->employee;

        // إذا كنت ترغب في دمج المدير مع قائمة الموظفين، يمكنك التحقق من عدم تكراره ثم دمجه:
        if ($projectManager && !$employees->contains($projectManager->id)) {
            $employees->push($projectManager);
        }

        // tasks
        $predefinedTasks = [
            'تحرير الدعوى',
            'كتابة مذكرة الرد',
            'التحضير للجلسة',
            'إبلاغ الأصيل بالحضور',
            'تكوين فريق الدعوى',

        ];

        // جلب جميع المهام المسندة للمشروع (بدون تجزئة) لاستخدامها في التحقق من المسندة
        $allAssignedTasks = Task::where('task_field', 'lawsuits')
            ->where('lawsuit_id', $lawsuit->id)
            ->get();

        // إنشاء مصفوفة تربط اسم المهمة بمعرفها باستخدام جميع المهام المسندة
        $assignedTasksByName = $allAssignedTasks->keyBy('task_name');


        $meeting = MeetingNote::where('lawsuits_id', $id)->get();

        return view($this->page . '.show',  compact(
            'lawsuit',
            'project',
            'sessions',
            'hasActiveSession',
            'employees',
            'users',
            'currentEmployee',
            'sessions_task',
            // tasks
            'predefinedTasks',
            'assignedTasksByName',
            // 'acceptedAssignedTeam',
            'meeting',
        ));
    }


    public function edit(string $id)
    {
        $lawsuit = Lawsuit::findOrFail($id);

        $categories             = SettingsDepartmentContractCase::where('status', 'active')->select(['id', 'name'])->orderBy('id', 'desc')->get();
        $projects               = Project::select(['id', 'project_name'])->orderBy('id', 'desc')->get();
        $settings_main_courts   = SettingsMainCourt::where('status', 'active')->select(['id', 'name'])->orderBy('id', 'desc')->get();
        $settings_regions       = SettingsRegion::where('status', 'active')->select(['id', 'name'])->orderBy('id', 'desc')->get();
        $power_of_attorneys     = PowerOfAttorney::where('status', 'active')->select(['id', 'power_name', 'power_number'])->orderBy('id', 'desc')->get();
        $mainCategories         = SettingsCategories::where('status', 'active')->select(['id', 'name'])->where('status', 'active')->orderBy('id', 'desc')->get();
        $subcategories          = SettingsSubcategories::where('status', 'active')->where('category_id', $lawsuit->category_id)->orderBy('id', 'desc')->get();
        $lawsuitTypes           = SettingsLawsuitsType::where('status', 'active')->where('subcategory_id', $lawsuit->subcategory_id)->orderBy('id', 'desc')->get();


        $opponents = Opponent::select(['id', 'name'])->orderBy('id', 'desc')->get()->map(function ($item) {
            $item->thisType = 'opponent';
            return $item;
        });

        $customers = Customers::select(['id', 'name'])->orderBy('id', 'desc')->get()->map(function ($item) {
            $item->thisType = 'customer';
            return $item;
        });

        // دمج القائمتين
        $mergedList = $opponents->concat($customers);

        // جلب المدعين والمدعى عليهم الحاليين
        // Defense-in-depth: even though $lawsuit is already tenant-scoped,
        // these raw DB::table() queries bypass the scope — we add an explicit
        // tenant_id filter so a leak can't happen if the parent row assumption breaks.
        $currentTenantId = \App\Tenancy\TenantContext::currentId();
        $existingPlaintiffs = DB::table('lawsuit_plaintiffs')
            ->where('lawsuit_id', $id)
            ->where('tenant_id', $currentTenantId)
            ->get()
            ->map(function ($plaintiff) {
                return $plaintiff->plaintiff_id . '-' . $plaintiff->plaintiff_type;
            })->toArray();

        $existingDefendants = DB::table('lawsuit_defendants')
            ->where('lawsuit_id', $id)
            ->where('tenant_id', $currentTenantId)
            ->get()
            ->map(function ($defendant) {
                return $defendant->defendant_id . '-' . $defendant->defendant_type;
            })->toArray();

        //

        $assignedTeamMembers = $lawsuit->assignedEmployees;

        return view($this->page . '.edit', compact(
            'lawsuit',
            'categories',
            'projects',
            'settings_main_courts',
            'settings_regions',
            'mergedList',
            'power_of_attorneys',
            'mainCategories',
            'subcategories',
            'lawsuitTypes',
            'existingPlaintiffs',
            'existingDefendants',
            'assignedTeamMembers'
        ));
    }


    public function update(UpdateLawsuitRequest $request, Lawsuit $lawsuit)
    {
        $validatedData = $request->validated();

        $lawsuitData = array_merge([
            'name'                              => $validatedData['name'],
            'lawsuit_number'                    => $validatedData['lawsuit_number'],
            'project_id'                        => $validatedData['project_id'],
            'main_courts_id'                    => $validatedData['main_courts_id'],
            'regions_id'                        => $validatedData['regions_id'],
            'category_id'                       => $validatedData['category_id'],
            'subcategory_id'                    => $validatedData['subcategory_id'],
            'lawsuit_type_id'                   => $validatedData['lawsuit_type_id'],
            'circle'                            => $validatedData['circle'] ?? null,
            'lawsuit_subject'                   => $validatedData['lawsuit_subject'] ?? null,
            'plaintiff_requests'                => $validatedData['plaintiff_requests'] ?? null,
            'lawsuit_proofs'                    => $validatedData['lawsuit_proofs'] ?? null,
            'updated_by'                        => Auth::id(),

        ]);

        $lawsuit->update($lawsuitData);


        if ($request->has('assigned_to')) {

            $newAssignedUsers = $validatedData['assigned_to'];

            $lawsuit->assignedEmployees()->sync($newAssignedUsers);

            $assignedUserIds = $lawsuit->assignedEmployees()->pluck('user_id')->toArray();

            foreach ($assignedUserIds as $userId) {
                $user_for_task = User::find($userId);
                $newTask = [
                    'user_name' => $user_for_task->name,
                    'title' => 'تم تعيينك في فريق العمل الخاص بدعوى: (' . $lawsuit->name . ').',
                    'description' => 'قام ' . Auth::user()->name . '  بتعيينك في فريق العمل الخاص بدعوى (' . $lawsuit->name . ').',
                    'office_name' => $this->office_name

                ];
                SendEmailNotificationJob::dispatch([
                    'email' => $user_for_task->email,
                ], $newTask);
            }
        } else {
            $lawsuit->assignedEmployees()->detach();
        }

        // للوكالات
        if ($request->has('power_of_attorney_id')) {
            $lawsuit->powerOfAttorneys()->sync($request->power_of_attorney_id);
        }


        // تحديث المدعين في جدول lawsuit_plaintiffs
        // Raw DB bypasses global scope — restrict delete+insert to this tenant
        // so we can never wipe or add a row belonging to another firm.
        $currentTenantId = \App\Tenancy\TenantContext::currentId();

        DB::table('lawsuit_plaintiffs')
            ->where('lawsuit_id', $lawsuit->id)
            ->where('tenant_id', $currentTenantId)
            ->delete();
        foreach ($validatedData['plaintiff_id'] as $plaintiff) {
            list($plaintiffId, $plaintiffType) = explode('-', $plaintiff); // تقسيم المدعي (معرف - نوع)
            DB::table('lawsuit_plaintiffs')->insert([
                'tenant_id' => $currentTenantId,
                'lawsuit_id' => $lawsuit->id,
                'plaintiff_id' => $plaintiffId,
                'plaintiff_type' => $plaintiffType == 'customer' ? 'App\Models\OperationsCenter\Customer' : 'App\Models\LegalAffair\Opponent',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // تحديث المدعى عليهم في جدول lawsuit_defendants
        DB::table('lawsuit_defendants')
            ->where('lawsuit_id', $lawsuit->id)
            ->where('tenant_id', $currentTenantId)
            ->delete();
        foreach ($validatedData['defendant_id'] as $defendant) {
            list($defendantId, $defendantType) = explode('-', $defendant); // تقسيم المدعى عليه (معرف - نوع)
            DB::table('lawsuit_defendants')->insert([
                'tenant_id' => $currentTenantId,
                'lawsuit_id' => $lawsuit->id,
                'defendant_id' => $defendantId,
                'defendant_type' => $defendantType == 'customer' ? 'App\Models\OperationsCenter\Customer' : 'App\Models\LegalAffair\Opponent',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }


        // إرجاع إعادة توجيه مع رسالة نجاح
        return redirect()->route('legal-affairs.lawsuits.index')->with('success', 'تم تحديث الدعوى بنجاح');
    }


    public function destroy(Lawsuit $lawsuit)
    {
        $hasSessions = $lawsuit->sessions()->exists();

        if ($hasSessions) {
            return redirect()->back()->with('error', 'لا يمكن حذف الدعوى لارتباطها بسجلات أخرى.');
        }
        $lawsuit->delete();

        return redirect()->back()->with('success', 'تم حذف الدعوى بنجاح!');
    }

    /*
    |============================================================================
    |============================================================================
    |                          Other methods
    |============================================================================
    |============================================================================
    */

    // موضوع الدعوي
    public function lawsuit_subject(Request $request, Lawsuit $lawsuit)
    {
        // معالجة تحديث البيانات
        $validated = $request->validate([
            'lawsuit_subject'           => 'nullable|string',
            'plaintiff_requests'        => 'nullable|string',
            'lawsuit_proofs'            => 'nullable|string',

        ], [
            'lawsuit_subject.string'    => 'يجب أن يكون موضوع الدعوى نصًا صالحًا.',
            'plaintiff_requests.string' => 'يجب أن تكون طلبات المدعي نصًا صالحًا.',
            'lawsuit_proofs.string'     => 'يجب أن تكون اسانيد  الدعوى نصًا صالحًا.',
        ]);


        $lawsuit->fill($validated);

        if ($lawsuit->isDirty()) {
            $changes = $lawsuit->getDirty();
            $lawsuit->save();

            return response()->json([
                'success' => true,
                'message' => 'تم حفظ موضوع الدعوى بنجاح.',
                'changed_fields' => $changes,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'لم يتم إجراء أي تغييرات.',
        ]);
    }


    // لحذف مرفقات الدعوى
    public function thisAttachmentsDestroy($id, Request $request)
    {
        $userId     = Auth::id();
        $lawsuit    = Lawsuit::findOrFail($id);

        if ($request->type == "decision") {
            // حذف الملف من التخزين
            if (Storage::disk('public')->exists($lawsuit->decision_attachment)) {
                Storage::disk('public')->delete($lawsuit->decision_attachment);
            }
            // حذف السجل من قاعدة البيانات
            $lawsuit->decision = "";
            $lawsuit->decision_attachment = "";
            $lawsuit->save();

            // إرسال رسالة نجاح
            return response()->json(['message' => 'تم حذف المرفق بنجاح.']);
        } elseif ($request->type == "request") {
            // حذف الملف من التخزين
            if (Storage::disk('public')->exists($lawsuit->request_attachment)) {
                Storage::disk('public')->delete($lawsuit->request_attachment);
            }
            // حذف السجل من قاعدة البيانات
            $lawsuit->request = "";
            $lawsuit->request_attachment = "";
            $lawsuit->save();

            // إرسال رسالة نجاح
            return response()->json(['message' => 'تم حذف المرفق بنجاح.']);
        } elseif ($request->type == "defense_memo") {
            // حذف الملف من التخزين
            if (Storage::disk('public')->exists($lawsuit->defense_memo_attachment)) {
                Storage::disk('public')->delete($lawsuit->defense_memo_attachment);
            }
            // حذف السجل من قاعدة البيانات
            $lawsuit->defense_memo = "";
            $lawsuit->defense_memo_attachment = "";
            $lawsuit->save();

            // إرسال رسالة نجاح
            return response()->json(['message' => 'تم حذف المرفق بنجاح.']);
        } else {
            // إرسال رسالة خطأ بصيغة JSON إذا لم يكن النوع "decision"
            return response()->json(['message' => 'نوع الطلب غير معروف.'], 400);
        }
    }

    // الطلبات
    public function saveRequests(Request $request, Lawsuit $lawsuit)
    {
        $validated = $request->validate([
            'request'               => 'required|string|max:1000',
            'request_attachment'    => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:2048',
        ]);

        if ($request->hasFile('request_attachment')) {
            // Tenant-prefixed path: tenants/{tenant_id}/legal-affair/lawsuits/attachments
            $path = $request->file('request_attachment')
                ->store(TenantStorage::path('legal-affair/lawsuits/attachments'), 'public');
            $validated['request_attachment'] = $path;
        }

        // التحقق من الحقول المعدلة فقط
        $lawsuit->fill($validated);
        if ($lawsuit->isDirty()) {
            $changes = $lawsuit->getDirty();
            $lawsuit->save();

            return response()->json([
                'success' => true,
                'message' => 'تم حفظ الطلبات بنجاح.',
                'request_attachment' => $lawsuit->request_attachment, // إرجاع مسار المرفق الجديد
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'لم يتم إجراء أي تغييرات.',
        ]);
    }



































    /*
    |--------------------------------------------------------------------------
    | get lawsuits tasks
    |--------------------------------------------------------------------------
    | لجب مهام الدعوى
    */
    public function getLawsuitTask($lawsuitId, Request $request)
    {
        $lawsuit = Lawsuit::findOrFail($lawsuitId);

        try {
            if ($request->ajax()) {
                $tasks = Task::with(['steps' => function ($query) {
                    $query->orderBy('step_order'); // تغيير من 'order' إلى 'step_order'
                }])->where('task_field', 'lawsuits')->where('lawsuit_id', $lawsuit->id)->select([
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
                        $truncated = mb_substr($row->task_name, 0, 20) . (mb_strlen($row->task_name) > 20 ? '...' : '');
                        return '<b><a href="' . route("tasks.show", $row->id) . '">' . e($truncated) . '</a></b>';
                    })

                    ->editColumn('priority', function ($row) {
                        $style = 'display:inline-block; width: 65px; text-align: center;';
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
                        return '<span class="badge rounded-pill bg-gradient-light text-dark px-3 py-1">
                                  <i class="ti ti-tag text-muted me-1"></i> ' . e($row->task_field_in_arabic) . '
                                </span>';
                    })

                    // حالة الاستكمال
                    ->addColumn('complete_checkbox', function ($row) {
                        // الحصول على معرف المستخدم الحالي
                        $userId = auth()->id();
                        // التأكد من أن المهمة تحتوي على علاقة assignedUsers وأن المستخدم موجود ضمنها
                        if (!$row->assignedUsers->contains('id', $userId) && $row->created_by != $userId) {
                            return '
                            <div class="form-check" title="عفوا ليس لديك صلاحيات اكمال او الغاء اكمال هذه المهمة">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    disabled
                                >
                            </div>
                            ';
                        }

                        // بناء HTML زر الاستكمال إذا كان المستخدم مكلفاً
                        $checked = $row->status === 'completed' ? 'checked' : '';
                        return '
                            <div class="form-check">
                                <input
                                    class="form-check-input task-complete-checkbox"
                                    type="checkbox"
                                    data-task-id="' . $row->id . '"
                                    ' . $checked . '
                                >
                            </div>
                        ';
                    })


                    // المتبقي  و التاخير
                    ->addColumn('remaining_days', function ($row) {

                        if ($row->status == 'completed') {
                            return $row->duration;
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
                            return '<span class="badge bg-danger">تم رفض الاعتماد</span>';
                        } else {
                            return '<span class="badge ' . $status['class'] . '">' . $status['text'] . '</span>';
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
                            $stepsHtml .= '<tr>';
                            if ($step->needs_approval) {
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
                                $checkboxHtml  = '<div class="px-1"><small>';
                                // تحقق مما إذا كان المستخدم الحالي من المكلفين بالخطوة
                                $isAssignedToStep = false;
                                $currentUserId = Auth::id();

                                // تحقق مما إذا كان المستخدم الحالي من المكلفين بالخطوة
                                if ($step->assignedUsers && count($step->assignedUsers) > 0) {
                                    foreach ($step->assignedUsers as $user) {
                                        if ($user->id == $currentUserId) {
                                            $isAssignedToStep = true;
                                            break;
                                        }
                                    }
                                }

                                // إضافة خصائص التعطيل والتلميح بناءً على حالة التكليف
                                $disabledAttr = !$isAssignedToStep ? 'disabled' : '';
                                $tooltipAttr = !$isAssignedToStep ? 'data-bs-toggle="tooltip" data-bs-placement="top" title="فقط المكلفين بالخطوة يمكنهم تحديثها"' : '';

                                // إنشاء مربع الاختيار مع التقييدات المناسبة
                                $checkboxHtml = '<input class="form-check-input custom-item step-complete-checkbox"
                                                    type="checkbox"
                                                    name="step_complete"
                                                    id="step_complete_' . $step->id . '"
                                                    title="' . ($isAssignedToStep ? 'إكمال الخطوة' : 'فقط المكلفين بالخطوة يمكنهم تحديثها') . '"
                                                    ' . (in_array($step->status, ['completed', 'approved']) ? 'checked' : '') . '
                                                    data-step-id="' . $step->id . '"
                                                    ' . $disabledAttr . '
                                                    ' . $tooltipAttr . '>';

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
                        return $row->createdBy ? $row->createdBy->name : 'غير محدد';
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
                                <img class="avatar avatar-task rounded-circle"
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
                                     style="width: 40px; height: 40px; border-radius: 50%;  margin-left: -16px; background: #ccc; display: flex; align-items: center; justify-content: center; font-size: 12px; cursor: pointer;">
                                    +' . $remaining . '
                                </div>';
                        }

                        $usersHtml .= '</div>';

                        return $usersHtml;
                    })

                    // في ملف TaskController.php - تحديث دالة إضافة عمود action
                    ->addColumn('action', function ($row) {
                        if ($row->status === 'completed') {
                            return '<span class="text-muted">مكتملة - لا يمكن التعديل أو الحذف</span>';
                        } elseif ($row->steps->contains('status', 'rejected')) {
                            return '<span class="text-muted">تم رفض الاعتماد - لا يمكن التعديل أو الحذف</span>';
                        }
                        if ($row->created_by != auth()->id()) {
                            return '<span class="text-muted">غير مصرح بالتعديل أو الحذف</span>';
                        }
                        $editOffcanvas = '
                                        <div class="offcanvas offcanvas-end offcanvas-edit" tabindex="-1" id="editTaskOffcanvas' . $row->id . '"
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

                        return '<div class="d-flex justify-content-center">' .
                            (auth()->user()->can("تعديل مهمة")
                                ? '<button class="btn btn-sm text-secondary" data-task-id="' . $row->id . '" title="تعديل المهمة" data-bs-toggle="offcanvas" data-bs-target="#editTaskOffcanvas' . $row->id . '">
                                        <i class="ti ti-edit"></i>
                                   </button>'
                                : ''
                            ) .
                            (auth()->user()->can("حذف مهمة")
                                ? '<button onclick="confirmDeleteTask(' . $row->id . ')" class="btn btn-sm text-secondary">
                                        <i class="ti ti-trash"></i>
                                   </button>'
                                : ''
                            ) .
                            '</div>' . $editOffcanvas;
                    })
                    ->rawColumns(['complete_checkbox', 'task_name', 'task_field', 'priority', 'remaining_days', 'status', 'steps_data', 'assigned_users', 'action'])
                    ->make(true);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    } // end of getProjectTask


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
                  data-image="' . $profilePicture . '">' . $user->name . '</option>';
        }

        return $html;
    }



    public function getLawsuitMeeting($projectId)
    {
        $project = Project::findOrFail($projectId);
        $meeting = MeetingNote::where('project_id', $projectId)->get();

        return view('judicial_affairs.projects.meeting.show', compact('project', 'meeting'));
    }



    public function showFile($filePath)
    {
        $fullPath = storage_path('app/public/' . $filePath);

        if (!file_exists($fullPath)) {
            return abort(404, 'الملف غير موجود: ' . $fullPath);
        }

        $response = response()->file($fullPath, [
            'Content-Type' => mime_content_type($fullPath),
            'Content-Disposition' => 'inline; filename="' . basename($fullPath) . '"',
        ]);

        // تعديل الهيدرز باستخدام headers->set()
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }





    public function trashed(Request $request)
    {
        $route = 'lawsuits'; // لتحديد المسار الخاص بالدعاوى

        if ($request->ajax()) {
            try {
                // جلب الدعاوى المحذوفة فقط باستخدام onlyTrashed
                $query = Lawsuit::onlyTrashed()
                    ->with([
                        'department_contract_cases', // جلب العلاقة المرتبطة بنوع الدعوى
                        'project', // جلب المشروع المرتبط
                    ])
                    ->select([
                        'id',
                        'name',
                        'lawsuit_number',
                        'department_contract_cases_id',
                        'project_id',
                        'deleted_at', // التأكد من جلب عمود deleted_at
                    ])
                    ->orderBy('id', 'desc'); // ترتيب النتائج تنازليًا

                return datatables()->of($query)
                    ->addIndexColumn() // لإضافة عمود ترقيم
                    ->addColumn('name', function ($row) {
                        $url = route('legal-affairs.lawsuits.show', $row->id);
                        return $row->name ? '<a href="' . $url . '">' . e($row->name) . '</a>' : 'غير متوفر';
                    })
                    ->addColumn('department_contract_cases_id', function ($row) {
                        return $row->department_contract_cases ? $row->department_contract_cases->name : 'غير متوفر';
                    })
                    ->addColumn('project_id', function ($row) {
                        return $row->project ? '<a href="' . route('projects.show', $row->project->id) . '">' . e($row->project->project_name) . '</a>' : 'غير متوفر';
                    })
                    ->addColumn('deleted_at', function ($row) {
                        return $row->deleted_at ? \Carbon\Carbon::parse($row->deleted_at)->format('d/m/Y') : '';
                    })
                    ->addColumn('action', function ($row) {
                        $restoreUrl = route('legal-affairs.lawsuits.restore', $row->id);
                        $forceDeleteUrl = route('legal-affairs.lawsuits.forceDelete', $row->id);
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
                    ->rawColumns(['action', 'name', 'project_id']) // السماح بعرض HTML في هذه الأعمدة
                    ->make(true);
            } catch (\Exception $e) {
                // التعامل مع الأخطاء إذا حدثت
                Log::error('Error fetching trashed lawsuits: ' . $e->getMessage());
                return response()->json(['error' => 'حدث خطأ أثناء جلب البيانات.'], 500);
            }
        }

        return view($this->page . '.trashed', compact('route'));
    }


    public function restore($id)
    {
        try {
            $lawsuit = Lawsuit::onlyTrashed()->findOrFail($id);
            $lawsuit->restore();

            return redirect()->route('legal-affairs.lawsuits.trashed')->with('success', 'تم استعادة الدعوى بنجاح.');
        } catch (\Exception $e) {

            return redirect()->back()->with('error', 'حدث خطأ أثناء استعادة الدعوى. يرجى المحاولة لاحقاً.');
        }
    }


    public function forceDelete($id)
    {
        try {
            $lawsuit = Lawsuit::onlyTrashed()->findOrFail($id);

            $tasks = Task::where('lawsuit_id', $lawsuit->id)->get();


            // foreach ($tasks as $task) {
            //     // استرجاع المستخدم المرتبط بالمهمة
            //     $oldUser = User::find($task->assigned_user_id);

            //     if ($oldUser) {
            //         // حذف المهمة للمستخدم
            //         app(\Illuminate\Contracts\Bus\Dispatcher::class)->dispatchNow(
            //             new BatchDeleteMicrosoftTaskJob($task, $oldUser)
            //         );
            //     }
            // }

            // حذف الدعوى نهائيًا
            $lawsuit->forceDelete();

            return redirect()->route('legal-affairs.lawsuits.trashed')->with('success', 'تم حذف الدعوى نهائيًا بنجاح.');
        } catch (\Exception $e) {

            return redirect()->back()->with('error', 'حدث خطأ أثناء حذف الدعوى نهائيًا. يرجى المحاولة لاحقاً.');
        }
    }





    // أطراف الدعوى
    public function lawsuitParties(Request $request, Lawsuit $lawsuit)
    {

        // الحصول على معرف المستخدم الحالي
        $userId = Auth::id();

        // التحقق مما إذا كان المستخدم مكلفًا بالدعوى وحالته مقبولة
        $isAssigned = AssignedLawsuits::where('lawsuit_id', $lawsuit->id)
            ->where('assigned_to', $userId)
            ->where('status', 'accepted')
            ->exists();

        $manager_user = $lawsuit->project()
            ->where('manager_user_id', Auth::id())
            ->exists();
        // السماح للمستخدمين ذوي دور Admin بالوصول بدون تحقق إضافي
        // if (!$isAssigned && !Auth::user()->hasAnyRole(['Admin', 'شريك مؤسس']) && !$manager_user) {
        //     abort(403, 'غير مصرح لك بالدخول إلى هذه الصفحة.');
        // }

        // تحميل العلاقات مسبقاً لتحسين الأداء
        $lawsuit->load(['plaintiffsCustomers', 'plaintiffsOpponents', 'defendantsCustomers', 'defendantsOpponents']);

        // جلب جميع المدعين من كلا الجدولين مع نوعهم
        $plaintiffs = $lawsuit->allPlaintiffs;

        // جلب جميع المدعى عليهم من كلا الجدولين مع نوعهم
        $defendants = $lawsuit->allDefendants;

        // تمرير المتغيرات إلى العرض
        return view($this->page . '.sections.lawsuit_parties', compact('lawsuit', 'plaintiffs', 'defendants'));
    }

    // الجلسات
    // public function getSessions(Request $request, string $id)
    // {

    //     // الحصول على معرف المستخدم الحالي
    //     $userId = Auth::id();

    //     // التحقق مما إذا كان المستخدم مكلفًا بالدعوى وحالته مقبولة
    //     $isAssigned = AssignedLawsuits::where('lawsuit_id', $id)
    //         ->where('assigned_to', $userId)
    //         ->where('status', 'accepted')
    //         ->exists();


    //     $manager_user = Lawsuit::find($id)->project()
    //         ->where('manager_user_id', Auth::id())
    //         ->exists();
    //     // السماح للمستخدمين ذوي دور Admin بالوصول بدون تحقق إضافي
    //     if (!$isAssigned && !Auth::user()->hasAnyRole(['Admin','شريك مؤسس']) && !$manager_user) {
    //         abort(403, 'غير مصرح لك بالدخول إلى هذه الصفحة.');
    //     }




    //     $lawsuit = Lawsuit::findOrFail($id);

    //     $lastLawsuit = Lawsuit::with(['sessions' => function ($query) {
    //         $query->orderBy('created_at', 'desc');
    //     }])->findOrFail($id);

    //     // جلب آخر جلسة
    //     $latestSession = $lastLawsuit->sessions()->latest('created_at')->first();

    //     // التحقق مما إذا كانت آخر جلسة نشطة
    //     $hasActiveSession = $latestSession && $latestSession->session_status === 'نشطة';
    //     // جلب الموظف المرتبط بالمستخدم المتصل
    //     $currentEmployee = Employees::where('user_id', auth()->id())->first();
    //     // جلب أول 5 جلسات
    //     $sessions = Session::with(['project', 'lawsuit'])
    //         ->where('lawsuit_id', $id)
    //         ->orderBy('id', 'desc')
    //         ->paginate(12);

    //     $sessions_task = Session::select(['id', 'session_name'])
    //         ->where('lawsuit_id', $id)
    //         ->orderBy('id', 'desc')->get();

    //     $project = $lawsuit->project;
    //     $employees = $lawsuit->assignedEmployees()->wherePivot('status', 'accepted')->get();


    //     return view($this->page.'.sections.sessions', compact('lawsuit', 'project', 'sessions', 'hasActiveSession', 'employees', 'currentEmployee', 'sessions_task'));
    // }


    // الأحكام
    public function judgments(Request $request, Lawsuit $lawsuit)
    {

        // الحصول على معرف المستخدم الحالي
        $userId = Auth::id();

        // التحقق مما إذا كان المستخدم مكلفًا بالدعوى وحالته مقبولة
        $isAssigned = AssignedLawsuits::where('lawsuit_id', $lawsuit->id)
            ->where('assigned_to', $userId)
            ->where('status', 'accepted')
            ->exists();

        $manager_user = $lawsuit->project()
            ->where('manager_user_id', Auth::id())
            ->exists();
        // السماح للمستخدمين ذوي دور Admin بالوصول بدون تحقق إضافي
        // if (!$isAssigned && !Auth::user()->hasAnyRole(['Admin', 'شريك مؤسس']) && !$manager_user) {
        //     abort(403, 'غير مصرح لك بالدخول إلى هذه الصفحة.');
        // }

        // $sessions = Lawsuit::sessions('session_status', 'مغلقة')->get();

        $sessions = Session::with(['project', 'lawsuit'])
            ->where('lawsuit_id', $lawsuit->id)
            ->orderBy('id', 'desc')
            ->paginate(12);

        return view($this->page . '.sections.judgments', compact('lawsuit', 'sessions'));
    }


    // القرارات
    public function decisions(Request $request, Lawsuit $lawsuit)
    {
        // // الحصول على معرف المستخدم الحالي
        // $userId = Auth::id();

        // // التحقق مما إذا كان المستخدم مكلفًا بالدعوى وحالته مقبولة
        // $isAssigned = AssignedLawsuits::where('lawsuit_id', $lawsuit->id)
        //     ->where('assigned_to', $userId)
        //     ->where('status', 'accepted')
        //     ->exists();

        // $manager_user = $lawsuit->project()
        //     ->where('manager_user_id', Auth::id())
        //     ->exists();
        // // السماح للمستخدمين ذوي دور Admin بالوصول بدون تحقق إضافي
        // if (!$isAssigned && !Auth::user()->hasAnyRole(['Admin','شريك مؤسس']) && !$manager_user) {
        //     abort(403, 'غير مصرح لك بالدخول إلى هذه الصفحة.');
        // }

        $validated = $request->validate([
            'decision' => 'required|string|max:1000',
            'decision_attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:2048',
        ]);

        if ($request->hasFile('decision_attachment')) {
            $path = $request->file('decision_attachment')->store('attachments', 'public');
            $validated['decision_attachment'] = $path;
        }


        // التحقق من الحقول المعدلة فقط
        $lawsuit->fill($validated);
        if ($lawsuit->isDirty()) {
            $changes = $lawsuit->getDirty();
            $lawsuit->save();


            return response()->json([
                'success' => true,
                'message' => 'تم حفظ الطلبات بنجاح.',
                'decision_attachment' => $lawsuit->decision_attachment, // إرجاع مسار المرفق الجديد


            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'لم يتم إجراء أي تغييرات.',
        ]);
    }

    // مهام الدعوى




    //لاضافة مرفقاتةاضافية
    public function storeAttachments(Request $request, $lawsuitId)
    {


        $request->validate([
            'attachment_name' => 'required|string|max:255',
            'attachment_file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:2048',
        ], [
            'attachment_name.required' => 'اسم المرفق مطلوب.',
            'attachment_name.string' => 'يجب أن يكون اسم المرفق نصاً.',
            'attachment_name.max' => 'اسم المرفق يجب أن لا يتجاوز 255 حرفاً.',
            'attachment_file.required' => 'يجب تحميل ملف للمرفق.',
            'attachment_file.file' => 'يجب أن يكون المرفق ملفًا صالحًا.',
            'attachment_file.mimes' => 'يجب أن يكون المرفق أحد الأنواع التالية: pdf, jpg, jpeg, png, doc, docx.',
            'attachment_file.max' => 'يجب أن لا يتجاوز حجم المرفق 2 ميجابايت.',
        ]);

        $path = $request->file('attachment_file')->store('lawsuit_attachments', 'public');

        $attachment = LawsuitAttachment::create([
            'lawsuit_id' => $lawsuitId,
            'attachment_name' => $request->attachment_name,
            'file_path' => $path,
        ]);


        return response()->json([
            'success' => true,
            'message' => 'تم إضافة المرفق بنجاح.',
            'attachment' => [
                'id' => $attachment->id,
                'attachment_name' => $attachment->attachment_name,
                'file_url' => asset('storage/' . $attachment->file_path),
            ],
        ]);
    }

    // لحذف المرفقات الاضافية
    public function attachmentsDestroy($id)
    {

        $attachment = LawsuitAttachment::findOrFail($id);

        // حذف الملف من التخزين
        if (Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }
        // حذف السجل من قاعدة البيانات
        $attachment->delete();

        // إرسال رسالة نجاح
        return response()->json(['message' => 'تم حذف المرفق بنجاح.']);
    }



    // لتغيير حالة الدعوى
    public function toggleStatus($id)
    {
        try {
            $lawsuit = Lawsuit::findOrFail($id);

            if ($lawsuit->sessions()->where('session_status', SessionStatus::Active)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن تغيير حالة الدعوى لوجود جلسات نشطة. يرجى إنهاء الجلسات أولاً.'
                ], 500);
            }

            $oldStatus = $lawsuit->lawsuit_status;

            $lawsuit->lawsuit_status = $lawsuit->lawsuit_status === LawsuitStatus::Active ? LawsuitStatus::Inactive : LawsuitStatus::Active;
            $lawsuit->save();

            if ($oldStatus === LawsuitStatus::Active && $lawsuit->lawsuit_status === LawsuitStatus::Inactive) {
                if ($lawsuit->primary_contract_customer_id) {
                    $this->surveySmsService->sendSurveyByType(SurveyType::Lawsuit, [$lawsuit->primary_contract_customer_id]);
                }
            }

            return response()->json([
                'success' => true,
                'status' => $lawsuit->lawsuit_status->value,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تغيير الحالة.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    // لعرض المكلفين

    public function assignedEmployeesPage(Request $request, $id)
    {
        $lawsuit = Lawsuit::findOrFail($id);
        $route = 'lawsuits.assignedEmployees'; // لاستخدامه في العرض

        if ($request->ajax()) {
            $assignedEmployees = AssignedLawsuits::where('lawsuit_id', $lawsuit->id)->get();
            return datatables()->of($assignedEmployees)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="row-checkbox" value="' . $row->id . '">';
                })
                ->addColumn('assigned_to', function ($row) {
                    return '<a href="' . route('account.employee.profile', $row->assignedTo->employee->id) . '">' . $row->assignedTo->employee->name . '</a>';
                })
                ->addColumn('user_accepted_id', function ($row) {
                    return $row->user_accepted_id ? '<a href="' . route('account.employee.profile', $row->userAccepted->employee->id) . '">' . $row->userAccepted->employee->name . '</a>' : 'لا يوجد';
                })
                ->addColumn('status', function ($row) {
                    if ($row->status == 'accepted') {
                        return '<span class="badge bg-success">مقبول</span>';
                    } elseif ($row->status == 'rejected') {
                        return '<span class="badge bg-danger">مرفوض</span>';
                    } else {
                        return '<span class="badge bg-secondary">قيد الانتظار</span>';
                    }
                })
                ->addColumn('action', function ($row) {
                    $buttons = '<div class="d-flex justify-content-center">';
                    if ($row->status != 'accepted') {
                        $buttons .= '<button class="btn btn-sm btn-success accept-single" data-id="' . $row->id . '">قبول</button>';
                    }
                    if ($row->status != 'rejected') {
                        $buttons .= '<button class="btn btn-sm btn-danger reject-single" data-id="' . $row->id . '">رفض</button>';
                    }
                    $buttons .= '</div>';

                    return $buttons;
                })

                ->rawColumns(['checkbox', 'status', 'action', 'assigned_to', 'user_accepted_id'])
                ->make(true);
        }

        return view($this->page . '.assigned_employees', compact('lawsuit', 'route'));
    }



    // public function acceptAssignedEmployees(Request $request, $lawsuitId)
    // {
    //     $assignedLawsuitIds = $request->input('ids', []);
    //     $userId = Auth::id();

    //     if (empty($assignedLawsuitIds)) {
    //         return response()->json(['error' => 'لم يتم تحديد أي مكلفين.'], 400);
    //     }

    //     foreach ($assignedLawsuitIds as $assignedLawsuitId) {
    //         $assignedLawsuit = AssignedLawsuits::where('id', $assignedLawsuitId)
    //             ->where('lawsuit_id', $lawsuitId)
    //             ->first();

    //         if ($assignedLawsuit) {
    //             $assignedLawsuit->status = 'accepted';
    //             $assignedLawsuit->user_accepted_id = $userId;
    //             $assignedLawsuit->save();
    //         }
    //     }

    //     return response()->json(['success' => 'تم قبول المكلفين المحددين.']);
    // }

    public function acceptAssignedEmployees(Request $request, $lawsuitId)
    {
        $assignedLawsuitIds = $request->input('ids', []);
        $userId = Auth::id();

        if (empty($assignedLawsuitIds)) {
            return response()->json(['error' => 'لم يتم تحديد أي مكلفين.'], 400);
        }

        DB::beginTransaction();

        try {
            $lawsuit = Lawsuit::findOrFail($lawsuitId);

            foreach ($assignedLawsuitIds as $assignedLawsuitId) {
                $assignedLawsuit = AssignedLawsuits::where('id', $assignedLawsuitId)
                    ->where('lawsuit_id', $lawsuitId)
                    ->first();

                if ($assignedLawsuit) {
                    $assignedLawsuit->status = 'accepted';
                    $assignedLawsuit->user_accepted_id = $userId;
                    $assignedLawsuit->save();
                }
            }

            // تحديث حالة الدعوى بناءً على حالة المكلفين
            $lawsuit->updateStatusBasedOnAssignedEmployees();

            DB::commit();
            /*
                |--------------------------------------------------------------------------
                | ازالة الكاش بعد اضافة الدعوى
                |--------------------------------------------------------------------------
                */
            Cache::forget('pending_lawsuits_count');


            return response()->json(['success' => 'تم قبول المكلفين بنجاح.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'حدث خطأ أثناء قبول المكلفين.'], 500);
        }
    }


    // public function rejectAssignedEmployees(Request $request, $lawsuitId)
    // {
    //     $assignedLawsuitIds = $request->input('ids', []);
    //     $userId = Auth::id();

    //     if (empty($assignedLawsuitIds)) {
    //         return response()->json(['error' => 'لم يتم تحديد أي مكلفين.'], 400);
    //     }

    //     foreach ($assignedLawsuitIds as $assignedLawsuitId) {
    //         $assignedLawsuit = AssignedLawsuits::where('id', $assignedLawsuitId)
    //             ->where('lawsuit_id', $lawsuitId)
    //             ->first();

    //         if ($assignedLawsuit) {
    //             $assignedLawsuit->status = 'rejected';
    //             $assignedLawsuit->user_accepted_id = $userId;
    //             $assignedLawsuit->save();
    //         }
    //     }

    //     return response()->json(['success' => 'تم رفض المكلفين المحددين.']);
    // }


    public function rejectAssignedEmployees(Request $request, $lawsuitId)
    {
        $assignedLawsuitIds = $request->input('ids', []);
        $userId = Auth::id();

        if (empty($assignedLawsuitIds)) {
            return response()->json(['error' => 'لم يتم تحديد أي مكلفين.'], 400);
        }

        DB::beginTransaction();

        try {
            $lawsuit = Lawsuit::findOrFail($lawsuitId);

            foreach ($assignedLawsuitIds as $assignedLawsuitId) {
                $assignedLawsuit = AssignedLawsuits::where('id', $assignedLawsuitId)
                    ->where('lawsuit_id', $lawsuitId)
                    ->first();

                if ($assignedLawsuit) {
                    $assignedLawsuit->status = 'rejected';
                    $assignedLawsuit->user_accepted_id = $userId;
                    $assignedLawsuit->save();
                }
            }

            // تحديث حالة الدعوى بناءً على حالة المكلفين
            $lawsuit->updateStatusBasedOnAssignedEmployees();

            DB::commit();

            /*
                |--------------------------------------------------------------------------
                | ازالة الكاش بعد اضافة الدعوى
                |--------------------------------------------------------------------------
                */
            Cache::forget('pending_lawsuits_count');


            return response()->json(['success' => 'تم رفض المكلفين المحددين بنجاح.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'حدث خطأ أثناء رفض المكلفين.'], 500);
        }
    }


    public function show_note_notification(Request $request, Lawsuit $lawsuit, string $lawsuit_note, string $note_reply)
    {

        $employees = Employees::all();

        LawsuitNote::findOrFail($lawsuit_note);
        $note_comment_id = LawsuitNoteReply::findOrFail($note_reply);

        $userMentioned = NoteCommentMention::where('note_comment_id', $note_comment_id->id)
            ->where('mentioned_user_id', Auth::id())
            ->exists();

        if (!$userMentioned) {
            // إذا لم يكن للمستخدم الحالي `mention` في التعليق، منعه من الوصول
            return abort(401);
        }

        // جلب الملاحظات بترتيب تنازلي مع تقسيم الصفحات (5 ملاحظات لكل صفحة)
        $notes = $lawsuit->notes()->orderBy('created_at', 'desc')->paginate(6);

        return view($this->page . '.sections.notes', compact('lawsuit', 'notes', 'employees', 'lawsuit_note', 'note_reply'));
    }



    // الدالة الخاصة باعتماد مكلفي الدعاوى

    // public function approvals(Request $request)
    // {

    //     $route = 'lawsuits';
    //     try {
    //         if ($request->ajax()) {
    //             $selectedFields = [
    //                 'id',
    //                 'name',
    //                 'lawsuit_number',
    //                 'lawsuit_type_id',
    //                 'project_id',
    //                 'created_at',
    //                 'lawsuit_status',
    //                 'main_courts_id',
    //             ];

    //             // بناء الاستعلام بناءً على دور المستخدم
    //             $query = Lawsuit::select($selectedFields)->where('lawsuit_status', 'pending');

    //             if (!(Auth::user()->hasAnyRole(['Admin', 'شريك مؤسس']) || Auth::user()->hasPermissionTo('اعتماد المكلفين'))) {
    //                 $userId = Auth::id();

    //                 // تخزين معرف المستخدم في متغير لاستخدامه داخل الـ Closure
    //                 $query->whereHas('project', function ($q) {
    //                     $q->where('manager_user_id', Auth::id()); // شرط أن يكون مدير المشروع يساوي 1
    //                 })->orWhere(function ($q) use ($userId) {
    //                     $q->where('user_id', $userId)
    //                         ->orWhereHas('assignedEmployees', function ($q2) use ($userId) {
    //                             $q2->where('assigned_to', $userId)
    //                                 ->where('status', 'accepted');
    //                         });
    //                 });
    //             }



    //             if ($request->has('project') && $request->project != '') {
    //                 $query->where('project_id', $request->project);
    //             }

    //             if ($request->has('department') && $request->department != '') {
    //                 $query->where('lawsuit_type_id', $request->department);
    //             }
    //             if ($request->has('court') && $request->court != '') {
    //                 $query->where('main_courts_id', $request->court);
    //             }

    //             return datatables()->of($query)
    //                 ->addIndexColumn() // إضافة الترقيم
    //                 ->addColumn('checkbox', function ($row) {
    //                     return '<input type="checkbox" class="row-checkbox" value="' . $row->id . '">';
    //                 })

    //                 ->addColumn('name', function ($row) {
    //                     // بناء رابط URL الخاص بك، يمكنك تعديل 'your.route.name' و $row->id حسب حاجتك
    //                     $url = route('legal-affairs.lawsuits.show', $row->id);

    //                     // إنشاء عنصر <a> مع اسم المستخدم كرابط
    //                     return '<a href="' . $url . '">' . e($row->name) . '</a>';
    //                 })
    //                 ->addColumn('lawsuit_type_id', function ($row) {
    //                     return $row->lawsuit_type ? $row->lawsuit_type->name : 'غير متوفر';
    //                 })
    //                 ->addColumn('project_id', function ($row) {
    //                     $url = route('projects.show', $row->project->id);
    //                     return '<a href="' . $url . '">' . e($row->project->project_name) . '</a>';
    //                 })

    //                 ->addColumn('lawsuit_status', function ($row) {
    //                     switch ($row->lawsuit_status) {
    //                         case 'active':
    //                             $badge = '<span class="cursor-pointer badge bg-success status-toggle" data-id="' . $row->id . '">نشط</span>';
    //                             break;
    //                         case 'pending':
    //                             $badge = '<span class="badge bg-warning">في انتظار اعتماد المكلفين</span>';
    //                             break;
    //                         case 'inactive':
    //                             $badge = '<span class="cursor-pointer badge bg-danger status-toggle" data-id="' . $row->id . '">مغلق</span>';
    //                             break;
    //                         case 'rejected':
    //                             $badge = '<span class="badge bg-secondary">تم رفض المكلفين</span>';
    //                             break;
    //                         default:
    //                             $badge = '<span class="badge bg-secondary">غير معروف</span>';
    //                             break;
    //                     }
    //                     return $badge;
    //                 })

    //                 // للترتيب
    //                 ->orderColumn('name', function ($query, $order) {
    //                     $query->orderBy('name', $order); // ترتيب بناءً على name
    //                 })

    //                 ->orderColumn('lawsuit_type_id', function ($query, $order) {
    //                     $query->orderBy('lawsuit_type_id', $order);
    //                 })

    //                 ->orderColumn('project_id', function ($query, $order) {
    //                     $query->orderBy('project_id', $order);
    //                 })

    //                 ->orderColumn('lawsuit_status', function ($query, $order) {
    //                     $query->orderBy('lawsuit_status', $order); // ترتيب بناءً على lawsuit_status
    //                 })

    //                 // filters
    //                 ->filterColumn('name', function ($query, $keyword) {
    //                     $query->where('name', 'like', "%{$keyword}%");
    //                 })
    //                 ->filterColumn('lawsuit_type_id', function ($query, $keyword) {
    //                     // تمكين البحث بناءً على اسم العميل
    //                     $query->whereHas('lawsuit_type', function ($q) use ($keyword) {
    //                         $q->where('name', 'like', "%{$keyword}%");
    //                     });
    //                 })
    //                 ->filterColumn('project_id', function ($query, $keyword) {
    //                     // تمكين البحث بناءً على اسم العميل
    //                     $query->whereHas('project', function ($q) use ($keyword) {
    //                         $q->where('project_name', 'like', "%{$keyword}%");
    //                     });
    //                 })
    //                 ->addColumn('action', function ($row) {
    //                     $editUrl = route('legal-affairs.lawsuits.edit', $row->id);
    //                     $deleteUrl = route('legal-affairs.lawsuits.destroy', $row->id);
    //                     $assignedEmployeesUrl = route('legal-affairs.lawsuits.assignedEmployees', $row->id);

    //                     $formId = 'delete-form-' . $row->id;
    //                     $csrfField = csrf_field();
    //                     $methodField = method_field('DELETE');
    //                     $buttons = '<div class="d-flex justify-content-center">';
    //                     $buttons .= '<a href="' . $assignedEmployeesUrl . '" class="btn btn-sm text-secondary"><i class="ti ti-users"></i></a>';                        // }
    //                     $buttons .= '</div>';

    //                     return $buttons;
    //                 })
    //                 ->editColumn('created_at', function ($row) {
    //                     // تنسيق تاريخ الإنشاء إذا كان متوفراً
    //                     return $row->created_at ? $row->created_at->format('d/m/Y') : '';
    //                 })
    //                 ->rawColumns(['action', 'lawsuit_status', 'name', 'project_id'])
    //                 ->make(true);
    //         }

    //         if (Auth::user()->hasAnyRole(['Admin', 'شريك مؤسس']) || Auth::user()->hasPermissionTo('اعتماد المكلفين')) {
    //             // للمستخدمين الذين لديهم دور admin، عرض جميع السجلات
    //             $totalLawsuits = Lawsuit::count();

    //             $personal_status = Lawsuit::where('main_courts_id', 10)->count();

    //             $execution_court = Lawsuit::where('main_courts_id', 1)->count();

    //             $high_court = Lawsuit::where('main_courts_id', 2)->count();
    //         } else {
    //             $userId = Auth::id();

    //             // للمستخدمين العاديين، عرض السجلات الخاصة بهم فقط
    //             $totalLawsuits = Lawsuit::whereHas('project', function ($q) {
    //                 $q->where('manager_user_id', Auth::id()); // شرط أن يكون مدير المشروع يساوي 1
    //             })->orWhere(function ($q) use ($userId) {
    //                 $q->where('user_id', $userId) // المستخدم الذي انشى الدعوى وهو مدير المشروع
    //                     ->orWhereHas('assignedEmployees', function ($q2) use ($userId) {
    //                         $q2->where('assigned_to', $userId)
    //                             ->where('status', 'accepted');
    //                     });
    //             })->count();

    //             $personal_status = Lawsuit::where('main_courts_id', 10)

    //                 ->whereHas('project', function ($q) {
    //                     $q->where('manager_user_id', Auth::id()); // شرط أن يكون مدير المشروع يساوي 1
    //                 })->orWhere(function ($q) use ($userId) {
    //                     $q->where('user_id', $userId)
    //                         ->orWhereHas('assignedEmployees', function ($q2) use ($userId) {
    //                             $q2->where('assigned_to', $userId)
    //                                 ->where('status', 'accepted');
    //                         });
    //                 })->count();

    //             $execution_court = Lawsuit::where('main_courts_id', 1)

    //                 ->whereHas('project', function ($q) {
    //                     $q->where('manager_user_id', Auth::id()); // شرط أن يكون مدير المشروع يساوي 1
    //                 })->orWhere(function ($q) use ($userId) {
    //                     $q->where('user_id', $userId)
    //                         ->orWhereHas('assignedEmployees', function ($q2) use ($userId) {
    //                             $q2->where('assigned_to', $userId)
    //                                 ->where('status', 'accepted');
    //                         });
    //                 })->count();

    //             $high_court = Lawsuit::where('main_courts_id', 2)

    //                 ->whereHas('project', function ($q) {
    //                     $q->where('manager_user_id', Auth::id()); // شرط أن يكون مدير المشروع يساوي 1
    //                 })->orWhere(function ($q) use ($userId) {
    //                     $q->where('user_id', $userId)
    //                         ->orWhereHas('assignedEmployees', function ($q2) use ($userId) {
    //                             $q2->where('assigned_to', $userId)
    //                                 ->where('status', 'accepted');
    //                         });
    //                 })->count();
    //         }

    //         $roles = ['محامي', 'شريك', 'مستشار قانوني', 'محامي متدرب', 'رئيس تنفيذي'];

    //         $employees = Employees::where('user_id', '!=', Auth::user()->id)
    //             ->whereHas('user.roles', function ($query) use ($roles) {
    //                 $query->whereIn('name', $roles); // فلترة بناءً على الأدوار
    //             })
    //             ->with('user')
    //             ->select(['id', 'name', 'user_id'])
    //             ->get();

    //         $lawsuits = Lawsuit::select('id', 'name')->get();
    //         return view($this->page . '.approvals', compact(
    //             'route',
    //             'totalLawsuits',
    //             'personal_status',
    //             'execution_court',
    //             'high_court',
    //             'employees',
    //             'lawsuits',
    //             // 'currentEmployee'
    //         ));
    //     } catch (\Exception $e) {
    //         return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
    //     }
    // }


    /*
    |--------------------------------------------------------------------------
    | تحسين الكود
    |--------------------------------------------------------------------------
    */



    /*
    |============================================================================
    |============================================================================
    |                               API
    |============================================================================
    |============================================================================
    */
    // لجلب التصنيفات الفرعية و انواع الدعاوى
    public function getSubcategories($categoryId)
    {
        $subcategories = SettingsSubcategories::where('status', 'active')->where('category_id', $categoryId)->get();
        return response()->json($subcategories);
    }

    public function getLawsuitTypes($subcategoryId)
    {
        $lawsuitTypes = SettingsLawsuitsType::where('status', 'active')->where('subcategory_id', $subcategoryId)->get();
        return response()->json($lawsuitTypes);
    }

    public function getLawsuitsJson()
    {
        $lawsuits = Lawsuit::select('id', 'name')->get();
        return response()->json([
            'success'   => true,
            'data'      => $lawsuits,
        ]);
    }
}
