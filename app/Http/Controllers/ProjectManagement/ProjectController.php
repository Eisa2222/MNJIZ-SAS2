<?php

namespace App\Http\Controllers\ProjectManagement;


use Alkoumi\LaravelHijriDate\Hijri;
use App\Http\Controllers\Controller;
use App\Jobs\Mail\SendEmailNotificationJob;
use App\Jobs\Tasks\CreateTaskJob;
use App\Jobs\Tasks\BatchDeleteMicrosoftTaskJob;
use App\Models\general_setting\SettingsContractStatus;
use App\Models\Hr\Employees\Employees;
use App\Models\judicial_affairs\Project;
use App\Models\judicial_affairs\ProjectAttachment;
use App\Models\MeetingNote;
use App\Models\OperationsCenter\ExceptionalContract\ExceptionalContract;
use App\Models\GeneralSetting\SystemSetting\Settings;
use App\Models\LegalAffair\Lawsuit\Lawsuit;
use App\Models\OperationsCenter\Contract\Contract;
use App\Models\OrganizationCenter\Tasks\Task\Task;
use App\Models\Task\TaskAssignee;
use App\Models\User;
use App\Services\MicrosoftGraphBaseService;
use App\Services\MicrosoftTeamsService;
use App\Services\OrganizationCenter\Tasks\Task\TaskService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use App\Traits\HandlesTaskAndEvent;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
// use Beta\Microsoft\Graph\Model\Employee;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use ProjectResource;

class ProjectController extends Controller
{
    use HandlesTaskAndEvent;


    /*
    |--------------------------------------------------------------------------
    | HandlesTaskAndEvent Trait
    |--------------------------------------------------------------------------
    | لاضافة وتعديل المهام والاحداث وارسال الاشعارات في البريد الالكتروني
    */
    use HandlesTaskAndEvent;

    /*
    |--------------------------------------------------------------------------
    | الأدوار المسموح لها
    |--------------------------------------------------------------------------
    | هذه هي الادوار المخصصة لمدير المشروع وفريق المشروع
    */
    // protected  $roles = ['محامي', 'شريك مؤسس', 'مستشار قانوني', 'محامي متدرب', 'رئيس تنفيذي'];


    public $office_name;
    /*
    |--------------------------------------------------------------------------
    | constructor
    |--------------------------------------------------------------------------
    | لتحديد الصلاحيات لكل اجراء
    */
    public function __construct(private TaskService $taskService)
    {
        $this->office_name = Settings::current()->office_name;

        $this->middleware(function ($request, $next) {
            if ($request->user()->can('كل المشاريع') || $request->user()->can('المشاريع الخاصة بي') ||   $request->user()->can('الإعتماد الفني للمشاريع')) {
                return $next($request);
            }
            abort(403);
        })->only(['index', 'show']);

        $this->middleware('can:إضافة مشروع')->only(['create', 'store']);
        $this->middleware('can:تعديل مشروع')->only(['edit', 'update']);
        $this->middleware('can:حذف مشروع')->only(['destroy']);
        $this->middleware('can:الإعتماد الفني للمشاريع')->only(['complete']);
    }



    /*
    |--------------------------------------------------------------------------
    | index of projects
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | تحديد الحقول المعينة لعرضها في الجدول
        |--------------------------------------------------------------------------
        */
        $selectedFields = [
            'id',
            'project_name',
            'project_number',
            'manager_user_id',
            'start_date',
            'new_status_id',
            'technical_manager_id'
        ];


        /*
        |--------------------------------------------------------------------------
        | تهيئة البيانات للعرض
        |--------------------------------------------------------------------------
        */
        $baseQuery = Project::select($selectedFields);


        // تطبيق فلتر الصلاحيات مرة واحدة
        if (auth()->user()->can('المشاريع الخاصة بي') && !auth()->user()->can('كل المشاريع')) {
            $baseQuery->userRelated();
        }



        if ($request->ajax()) {

            $query = clone $baseQuery;

            /*
            |--------------------------------------------------------------------------
            | الفلاتر
            |--------------------------------------------------------------------------
            */
            if ($request->has('new_status_id') && $request->new_status_id != '') {
                $query->where('new_status_id', $request->new_status_id);
            }

            // الفلتر الخاص بالعقود (مع علاقة Many-to-Many)
            if ($request->has('contract_id') && $request->contract_id != '') {
                $query->whereHas('contracts', function ($q) use ($request) {
                    $q->where('contracts.id', $request->contract_id);
                });
            }


            if ($request->has('employee') && $request->employee != '') {
                $query->where('manager_user_id', $request->employee);
            }


            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="row-checkbox" value="' . $row->id . '">';
                })

                ->addColumn('project_name', function (Project $project) {
                    return
                        '<a href="' . route('projects.show', $project->id) . '">' . $project->project_name . '</a> ';
                })
                ->addColumn('contract_id', function (Project $project) {
                    return $project->contract->contract_name ?? "";
                })
                ->addColumn('manager_user_id', function (Project $project) {
                    return $project->manager_user ? '<a href="' . route('account.employee.profile', $project->manager_user->employee->id) . '">' . $project->manager_user->employee->name . '</a>' : "لم يتم تعيينه بعد ";
                })
                ->addColumn('new_status_id', function (Project $project) {
                    if ($project->manager_user_id == NULL) {
                        return '<span title=" مدير الشؤون الفنية ' . $project->technical_manager->employee->name . '">في انتظار استكمال المشروع</span>';
                    } else {
                        return '<a href="#" class="change-status" data-id="' . $project->id . '" data-status="' . $project->new_status_id . '">' . $project->project_status->name . '</a>';
                    }
                })
                // order
                ->orderColumn('project_name', function ($query, $order) {
                    $query->orderBy('project_name', $order);
                })
                // ->orderColumn('contract_id', function ($query, $order) {
                //     $query->orderBy('contract_id', $order);
                // })
                ->orderColumn('manager_user_id', function ($query, $order) {
                    $query->orderBy('manager_user_id', $order);
                })
                // fillters
                ->filterColumn('project_name', function ($query, $keyword) {
                    $query->where('project_name', 'like', "%{$keyword}%");
                })
                // ->filterColumn('contract_id', function ($query, $keyword) {

                //     $query->whereHas('contract', function ($q) use ($keyword) {
                //         $q->where('contract_name', 'like', "%{$keyword}%");
                //     });
                // })
                ->filterColumn('manager_user_id', function ($query, $keyword) {

                    $query->whereHas('manager_user', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })

                ->addColumn('action', function ($row) {
                    $editUrl = route('projects.edit', $row->id);
                    $completeUrl = route('projects.complete', $row->id);
                    $deleteUrl = route('projects.destroy', $row->id);
                    $formId = 'delete-form-' . $row->id;
                    $csrfField = csrf_field();
                    $methodField = method_field('DELETE');

                    $buttons = '<div class="">';

                    if (auth()->user()->can('الإعتماد الفني للمشاريع')) {
                        $buttons .= '<a title="اضغط هنا لاستكمال المشروع"  href="' . $completeUrl . '" class="btn btn-sm text-secondary"><i class="ti ti-file-check"></i></a>';
                    }

                    if (auth()->user()->can('تعديل مشروع')) {
                        $buttons .= '<a title="اضغط هنا لتعديل المشروع"  href="' . $editUrl . '" class="btn btn-sm text-secondary"><i class="ti ti-edit"></i></a>';
                    }

                    if (auth()->user()->can('حذف مشروع')) {
                        $buttons .= '<a title="اضغط هنا لحذف المشروع"  href="javascript:void(0);" onclick="confirmDelete(' . $row->id . ')" class="btn btn-sm text-secondary">
                                            <i class="ti ti-trash"></i>
                                         </a>
                                         <form id="' . $formId . '" action="' . $deleteUrl . '" method="POST" style="display: none;">
                                            ' . $csrfField . '
                                            ' . $methodField . '
                                         </form>';
                    }

                    if (!auth()->user()->can('الإعتماد الفني للمشاريع') && !auth()->user()->can('تعديل مشروع') && !auth()->user()->can('حذف مشروع')) {
                        $buttons .= "<small>ليس لديك صلاحيات</small>";
                    }

                    $buttons .= '</div>';

                    return $buttons;
                })
                ->rawColumns(['new_status_id', 'action', 'project_name', 'manager_user_id'])
                ->make(true);
        }



        $employees = Employees::active()->select('name', 'id', 'user_id', 'nickname')->get();

        $totalProjects = $baseQuery->count();

        $on_track_projects =  (clone $baseQuery)->where('new_status_id', 2)->count();
        $closed_projects =  (clone $baseQuery)->where('new_status_id', 5)->count();
        $new_projects =  (clone $baseQuery)->where('new_status_id', 1)->count();

        // filters
        $contract_status = SettingsContractStatus::select('id', 'name')->orderBy('id', 'desc')->get();
        $contracts = Contract::select('id', 'contract_name')->orderBy('id', 'desc')->get();

        return view('judicial_affairs.projects.index', compact(
            'totalProjects',
            'on_track_projects',
            'closed_projects',
            'new_projects',
            'employees',
            'contract_status',
            'contracts'
        ));
    } // end of index



    /*
    |--------------------------------------------------------------------------
    | Create a new project
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        // اذا لم يكون هناك عقود معتمدة فلا يمكن اضافة مشروع
        $contracts = Contract::where('contract_type', 'main')
            ->where('status', 'approved')
            ->select(['id', 'contract_name'])
            ->orderBy('id', 'desc')
            ->get();

        // التحقق من وجود عقود معتمدة باستخدام نفس المتغير
        if ($contracts->isEmpty()) {
            return redirect()->route('projects.index')
                ->with('warning', 'عذراً، لا يمكن إضافة مشروع جديد حالياً لعدم وجود عقود معتمدة.');
        }


        /*
        |--------------------------------------------------------------------------
        | تحديد مدير الشؤون الفنية للمشاريع
        |--------------------------------------------------------------------------
        | وتحديد هل لديه الصلاحية ام لا
        |--------------------------------------------------------------------------
        | وتحديد هل الصلاحية ممنوحة ام منزوعة
        |--------------------------------------------------------------------------
         */
        $technicalApprovalUsers = Employees::active()->whereHas('user', function ($query) {
            $query->whereHas('roles', function ($q) {
                $q->whereHas('permissions', function ($p) {
                    $p->where('name', 'الإعتماد الفني للمشاريع');
                });
            })->orWhereHas('additionalPermissions', function ($q) {
                $q->where('name', 'الإعتماد الفني للمشاريع');
            });
        })->get();

        $validUsers = $technicalApprovalUsers->filter(function ($employee) {
            return $employee->user->hasPermissionTo('الإعتماد الفني للمشاريع');
        });

        if ($validUsers->isEmpty()) {
            return redirect()->route('projects.index')->with('warning', 'عفواً، لا يمكن إضافة مشروع جديد حالياً لعدم وجود مدير الشؤون الفنية للمشاريع.');
        }

        $technical_manager_id = $validUsers->map(function ($employee) {
            return [
                'id'        => $employee->id,
                'name'      => $employee->name,
                'user_id'   => $employee->user_id,
            ];
        });


        /*
        |--------------------------------------------------------------------------
        | الموظفين
        |--------------------------------------------------------------------------
        | لمدير المشروع و فريق المشروع
        |--------------------------------------------------------------------------
        */
        $employees = Employees::active()->with('user')
            ->select(['id', 'name', 'user_id', 'nickname'])
            ->get();



        $typeOptions = Project::getTypeContractOptions();

        $exceptionalContracts = ExceptionalContract::where('status', ExceptionalContract::STATUS_APPROVED)->select('id', 'contract_name')->orderBy('id', 'desc')->get();

        /*
        |--------------------------------------------------------------------------
        |  فتح صفحة الاضافة
        |--------------------------------------------------------------------------
        */
        return view('judicial_affairs.projects.create', compact(
            'employees',
            'contracts',
            'technical_manager_id',
            'typeOptions',
            'exceptionalContracts'
        ));
    } // end of create



    /*
    |--------------------------------------------------------------------------
    | لانشاء رقم التسلسلي
    |--------------------------------------------------------------------------
    */
    function generateUniqueProjectNumber()
    {
        // استرجاع أكبر رقم مشروع موجود في قاعدة البيانات
        $maxProjectNumber = DB::table('projects')
            ->selectRaw('MAX(CAST(SUBSTRING(project_number, 2) AS UNSIGNED)) AS max_number')
            ->value('max_number');

        // التحقق من وجود مشروع سابق
        $lastNumber = $maxProjectNumber ? intval($maxProjectNumber) : 0;

        // زيادة الرقم للحصول على الرقم الجديد
        $newNumber = $lastNumber + 1;

        // تنسيق الرقم الجديد ليظهر بالشكل P0001
        $formattedNumber = 'P' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);

        return $formattedNumber;
    }



    /*
    |--------------------------------------------------------------------------
    | store a new project
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        if ($request->user()->can('إضافة مشروع')) {
            $rules = [
                'project_name'              => 'required|string|max:255',
                'project_type'              => 'required|in:consulting,legal,consulting_legal',
                'contract_type'             => 'required|in:main_contract,exceptional_contract',
                'secondary_contract_ids'    => 'nullable|array',
                'secondary_contract_ids.*'  => 'exists:contracts,id',
                'technical_manager_id'      => 'required|exists:users,id',
            ];

            if ($request->contract_type === Project::TYPE_CONTRACT_MAIN) {
                $rules['primary_contract_id']     = 'required|exists:contracts,id';
            } else {
                $rules['exceptional_contract_id'] = 'required|exists:exceptional_contracts,id';
            }
        }

        if ($request->user()->can('الإعتماد الفني للمشاريع')) {
            $rules['manager_user_id']       = 'required|integer';
            $rules['description']           = 'nullable|string';
            $rules['scope_of_work']         = 'nullable|string';
            $rules['financial_claim']       = 'nullable|numeric';
            $rules['non_financial_claim']   = 'nullable|string';
            $rules['other_claim']           = 'nullable|string';
        }

        // قواعد التحقق لفريق المشروع
        $rules['team_members']      = 'nullable|array';
        $rules['team_members.*']    = 'integer';

        $validatedData = $request->validate($rules);

        try {

            DB::beginTransaction();

            if ($request->user()->can('إضافة مشروع')) {

                if ($validatedData['contract_type'] === Project::TYPE_CONTRACT_MAIN) {
                    // عقد رئيسي
                    $primaryContract = Contract::findOrFail($validatedData['primary_contract_id']);
                    $startDate       = $primaryContract->contract_start_date;
                    $closureDate     = $primaryContract->expected_closure_date;
                } else {
                    $startDate   = null;
                    $closureDate = null;
                }



                $newProjectNumber = $this->generateUniqueProjectNumber();

                $project = Project::create([
                    'project_number'            => $newProjectNumber,
                    'project_name'              => $validatedData['project_name'],
                    'project_type'              => $validatedData['project_type'],
                    'technical_manager_id'      => $validatedData['technical_manager_id'],
                    'start_date'                => $startDate,
                    'contractual_closure'       => $closureDate,
                    'created_by'                => Auth::user()->id,
                    'new_status_id'             => 1,
                    'contract_type'             => $validatedData['contract_type'],
                    'exceptional_contract_id'   => $validatedData['exceptional_contract_id'] ?? null,
                ]);


                if ($request->contract_type == Project::TYPE_CONTRACT_MAIN) {
                    $project->contracts()->attach($validatedData['primary_contract_id'], [
                        'contract_type' => 'primary'
                    ]);
                }

                // إذا كان هناك عقود فرعية مختارة
                if ($request->filled('secondary_contract_ids')) {
                    foreach ($validatedData['secondary_contract_ids'] as $secondaryId) {
                        $project->contracts()->attach($secondaryId, [
                            'contract_type' => 'secondary'
                        ]);
                    }
                }


                $this->taskService->createApprovalTask("projects", $project, $project->technical_manager_id);
            }

            if ($request->user()->can('الإعتماد الفني للمشاريع') && isset($project)) {
                $project->update([
                    'manager_user_id' => $validatedData['manager_user_id'],
                    'financial_claim' => $validatedData['financial_claim'] ?? null,
                    'non_financial_claim' => $validatedData['non_financial_claim'] ?? null,
                    'other_claim' => $validatedData['other_claim'] ?? null,
                    'description' => $validatedData['description'] ?? null,
                    'scope_of_work' => $validatedData['scope_of_work'] ?? null,
                    'complate_user_id' => Auth::user()->id,
                    // 'status' => 'ongoing',
                ]);


                // اضافة المهمة للمتعمد الاول
                $manager = User::find($project->manager_user_id);

                $newTask = [
                    'user_name'     => $manager->name,
                    'title'         => 'تم تعينك كمدير للمشروع: (' . $project->project_name . ').',
                    'description'   => 'قام ' . Auth::user()->name . ' بتعينك كمدير للمشروع (' . $project->project_name . ').',
                    'office_name'   => $this->office_name

                ];
                SendEmailNotificationJob::dispatch([
                    'email' => $manager->email,
                ], $newTask);
            }


            // معالجة فريق المشروع
            if ($request->has('team_members')) {
                $project->teamMembers()->sync($validatedData['team_members']);

                foreach ($validatedData['team_members'] as $teamMemberId) {
                    $user = User::find($teamMemberId);

                    $newTask = [
                        'user_name' => $user->name,
                        'title' => 'تم تعينك في فريق المشروع: (' . $project->project_name . ').',
                        'description' => 'قام ' . Auth::user()->name . ' بتعينك كعضو في فريق المشروع (' . $project->project_name . ').',
                        'office_name' => $this->office_name
                    ];
                    SendEmailNotificationJob::dispatch([
                        'email' => $user->email,
                    ], $newTask);
                }
            }

            DB::commit();

            return redirect()->route('projects.index')->with('success', 'تم إضافة المشروع بنجاح.');
        } catch (\Exception $e) {

            Log::error('Error in ProjectController@store: ' . $e->getMessage(), [
                'request' => $request->all(),
                'user_id' => Auth::id(),
            ]);
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'حدث خطأ أثناء حفظ المشروع: ' . $e->getMessage()])->withInput();
        }
    } // end of store




    /*
    |--------------------------------------------------------------------------
    | edit a project
    |--------------------------------------------------------------------------
    */
    public function edit(Project $project)
    {

        if (auth()->user()->can('المشاريع الخاصة بي') && !auth()->user()->can('كل المشاريع')) {
            $userRelatedProject = Project::where('id', $project->id)->userRelated()->first();

            if (!$userRelatedProject) {
                abort(404);
            }
        }


        /*
        |--------------------------------------------------------------------------
        | اذا لم يكون هناك عقود معتمدة فلا يمكن اضافة مشروع
        |--------------------------------------------------------------------------
        */

        $contracts = Contract::where('contract_type', 'main')
            ->where('status', 'approved')
            ->select(['id', 'contract_name'])
            ->orderBy('id', 'desc')
            ->get();

        // التحقق من وجود عقود معتمدة باستخدام نفس المتغير
        if ($contracts->isEmpty()) {
            return redirect()->route('projects.index')
                ->with('warning', 'عذراً، لا يمكن إضافة مشروع جديد حالياً لعدم وجود عقود معتمدة.');
        }



        /*
        |--------------------------------------------------------------------------
        | تحديد مدير الشؤون الفنية للمشاريع
        |--------------------------------------------------------------------------
        | وتحديد هل لديه الصلاحية ام لا
        |--------------------------------------------------------------------------
        | وتحديد هل الصلاحية ممنوحة ام منزوعة
        |--------------------------------------------------------------------------
         */
        $technicalApprovalUsers = Employees::active()->whereHas('user', function ($query) {
            $query->whereHas('roles', function ($q) {
                $q->whereHas('permissions', function ($p) {
                    $p->where('name', 'الإعتماد الفني للمشاريع');
                });
            })->orWhereHas('additionalPermissions', function ($q) {
                $q->where('name', 'الإعتماد الفني للمشاريع');
            });
        })->get();

        $validUsers = $technicalApprovalUsers->filter(function ($employee) {
            return $employee->user->hasPermissionTo('الإعتماد الفني للمشاريع');
        });

        if ($validUsers->isEmpty()) {
            return redirect()->route('projects.index')->with('warning', 'عفواً، لا يمكن تعديل المشروع حالياً لعدم وجود مدير الشؤون الفنية للمشاريع.');
        }

        $technical_manager_id = $validUsers->map(function ($employee) {
            return [
                'id' => $employee->id,
                'name' => $employee->name,
                'user_id' => $employee->user_id,
            ];
        });



        /*
        |--------------------------------------------------------------------------
        | الموظفين
        |--------------------------------------------------------------------------
        | لمدير المشروع و فريق المشروع
        |--------------------------------------------------------------------------
        */
        $employees = Employees::active()->with('user')
            ->select(['id', 'name', 'user_id', 'nickname'])
            ->get();




        /*
        |--------------------------------------------------------------------------
        |جلب العقد الرئيسي و الفرعي المرتبط بالمشروع
        |--------------------------------------------------------------------------
        */
        $primaryContract = $project->contracts()
            ->wherePivot('contract_type', 'primary')
            ->first();


        $secondaryContracts = $project->contracts()
            ->wherePivot('contract_type', 'secondary')
            ->pluck('contracts.id') // تحديد الجدول الذي يحتوي على العمود
            ->toArray();

        // يمكنك استخدام null في حال لم يكن هناك عقد رئيسي
        $primaryContractId = optional($primaryContract)->id;
        $secondaryContractsIds = $secondaryContracts; // مصفوفة IDs



        $typeOptions = Project::getTypeContractOptions();

        $exceptionalContracts = ExceptionalContract::where('status', ExceptionalContract::STATUS_APPROVED)->select('id', 'contract_name')->orderBy('id', 'desc')->get();


        /*
        |--------------------------------------------------------------------------
        |  فتح صفحة التعديل
        |--------------------------------------------------------------------------
        */
        return view('judicial_affairs.projects.edit', compact(
            'project',
            'employees',
            'contracts',
            'technical_manager_id',
            'primaryContractId',
            'secondaryContractsIds',
            'typeOptions',
            'exceptionalContracts',
        ));
    } // end of edit




    /*
    |--------------------------------------------------------------------------
    | complete a project
    |--------------------------------------------------------------------------
    | خاصة باستكمال المشاريع
    | خاصة  لمن لديه صلاحية الإعتماد الفني للمشاريع
    */
    public function complete(string $id)
    {
        /*
        |--------------------------------------------------------------------------
        | find the project
        |--------------------------------------------------------------------------
        */
        $project = Project::findOrFail($id);




        /*
        |--------------------------------------------------------------------------
        |جلب العقد الرئيسي و الفرعي المرتبط بالمشروع
        |--------------------------------------------------------------------------
        */
        // العقد الرئيسي
        $primaryContract = $project->contracts()
            ->wherePivot('contract_type', 'primary')
            ->first();

        // العقود الثانوية
        $secondaryContracts = $project->contracts()
            ->wherePivot('contract_type', 'secondary')
            ->get();


        $employees = Employees::active()->with('user')
            ->select(['id', 'name', 'user_id', 'nickname'])
            ->get();

        $typeOptions = Project::getTypeContractOptions();

        $exceptionalContracts = ExceptionalContract::where('status', ExceptionalContract::STATUS_APPROVED)->select('id', 'contract_name')->orderBy('id', 'desc')->get();
        $contracts = Contract::where('contract_type', 'main')
            ->where('status', 'approved')
            ->select(['id', 'contract_name'])
            ->orderBy('id', 'desc')
            ->get();

        return view('judicial_affairs.projects.complete', compact(
            'project',
            'employees',
            'primaryContract',
            'secondaryContracts',
            'typeOptions',
            'exceptionalContracts',
            'contracts'
        ));
    } // end of complete



    /*
    |--------------------------------------------------------------------------
    |  complete store of a project
    |--------------------------------------------------------------------------
    | Description of this section.
    */
    public function completeStore(Request $request, string $id)
    {

        // التحقق من التفويض - تأكد من أن المستخدم يمكنه تحديث المشروع
        $project = Project::findOrFail($id);

        $validatedData = $request->validate(
            [
                'manager_user_id' => 'required|integer',
                'description' => 'nullable|string',
                'scope_of_work' => 'nullable|string',
                'financial_claim' => 'nullable|numeric',
                'non_financial_claim' => 'nullable|string',
                'other_claim' => 'nullable|string',
                'team_members' => 'nullable|array',
                'team_members.*' => 'integer',

            ],
            [
                'manager_user_id.required'     => 'يرجى اختيار الموظف.',
                'manager_user_id.integer'      => 'رقم الموظف يجب أن يكون رقماً صحيحاً.',
                'manager_user_id.exists'       => 'الموظف المحدد غير موجود.',
                'description.string'           => 'الوصف يجب أن يكون نصاً.',
                'scope_of_work.string'         => 'نطاق العمل يجب أن يكون نصاً.',
                'financial_claim.numeric'      => 'المطالبة المالية يجب أن تكون رقمية.',
                'non_financial_claim.string'   => 'المطالبة غير المالية يجب أن تكون نصاً.',
                'other_claim.string'           => 'المطالبة الأخرى يجب أن تكون نصاً.',
                'team_members.array'           => 'أعضاء الفريق يجب أن يكونوا مصفوفة.',
                'team_members.*.integer'       => 'كل عضو في الفريق يجب أن يكون رقماً صحيحاً.',
                'team_members.*.exists'        => 'عضو الفريق المحدد غير موجود.',

            ]
        );

        // بدء معاملة قاعدة البيانات
        DB::beginTransaction();

        try {

            $project->update([
                'manager_user_id'      => $validatedData['manager_user_id'],
                'description'          => $validatedData['description'] ?? null,
                'scope_of_work'        => $validatedData['scope_of_work'] ?? null,
                'financial_claim'      => $validatedData['financial_claim'] ?? null,
                'non_financial_claim'  => $validatedData['non_financial_claim'] ?? null,
                'other_claim'          => $validatedData['other_claim'] ?? null,
                'complate_user_id'    => Auth::id(), // تصحيح اسم المتغير
                // 'status' => 'ongoing',

            ]);



            // معالجة فريق المشروع
            if (isset($validatedData['team_members'])) {
                $project->teamMembers()->sync($validatedData['team_members']);

                // إرسال إشعارات لأعضاء الفريق
                foreach ($validatedData['team_members'] as $teamMemberId) {
                    $user = User::find($teamMemberId);

                    $newTask = [
                        'user_name' => $user->name,
                        'title' => 'تم تعينك في فريق المشروع: (' . $project->project_name . ').',
                        'description' => 'قام ' . Auth::user()->name . ' بتعينك كعضو في فريق المشروع (' . $project->project_name . ').',
                        'office_name' => $this->office_name
                    ];
                    SendEmailNotificationJob::dispatch([
                        'email' => $user->email,
                    ], $newTask);
                }
            }


            // اضافة المهمة للمتعمد الاول
            $manager = User::find($project->manager_user_id);

            $newTask = [
                'user_name' => $manager->name,
                'title' => 'تم تعينك كمدير للمشروع: (' . $project->project_name . ').',
                'description' => 'قام ' . Auth::user()->name . ' بتعينك كمدير للمشروع (' . $project->project_name . ').',
                'office_name' => $this->office_name

            ];
            SendEmailNotificationJob::dispatch([
                'email' => $manager->email,
            ], $newTask);

            // تأكيد المعاملة
            DB::commit();

            return redirect()->route('projects.index')->with('success', 'تم اكمال بيانات  المشروع بنجاح.');
        } catch (\Exception $e) {
            // التراجع عن المعاملة في حالة حدوث خطأ
            DB::rollBack();

            // تسجيل الخطأ لأغراض التصحيح
            Log::error('خطأ في إكمال المشروع: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء إكمال بيانات المشروع. يرجى المحاولة مرة أخرى.');
        }
    } // end of completeStore



    /*
    |--------------------------------------------------------------------------
    | update a project
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $rules = [];

        if ($request->user()->can('إضافة مشروع')) {
            $rules = [
                'project_name'              => 'required|string|max:255',
                'project_type'              => 'required|in:consulting,legal,consulting_legal',
                'contract_type'             => 'required|in:main_contract,exceptional_contract',
                'secondary_contract_ids'    => 'nullable|array',
                'secondary_contract_ids.*'  => 'exists:contracts,id',
                'technical_manager_id'      => 'required|exists:users,id',
            ];

            if ($request->contract_type === Project::TYPE_CONTRACT_MAIN) {
                $rules['primary_contract_id']     = 'required|exists:contracts,id';
            } else {
                $rules['exceptional_contract_id'] = 'required|exists:exceptional_contracts,id';
            }
        }

        if ($request->user()->can('الإعتماد الفني للمشاريع')) {
            $rules['manager_user_id'] = 'required|integer';
            $rules['description'] = 'nullable|string';
            $rules['scope_of_work'] = 'nullable|string';

            $rules['financial_claim'] = 'nullable|numeric';
            $rules['non_financial_claim'] = 'nullable|string';
            $rules['other_claim'] = 'nullable|string';
        }
        // قواعد التحقق لفريق المشروع
        $rules['team_members'] = 'nullable|array';
        $rules['team_members.*'] = 'integer';


        $validatedData = $request->validate($rules);

        try {
            DB::beginTransaction();

            if ($request->user()->can('إضافة مشروع')) {

                if ($validatedData['contract_type'] === Project::TYPE_CONTRACT_MAIN) {
                    // عقد رئيسي
                    $primaryContract = Contract::findOrFail($validatedData['primary_contract_id']);
                    $startDate       = $primaryContract->contract_start_date;
                    $closureDate     = $primaryContract->expected_closure_date;
                } else {
                    $startDate   = null;
                    $closureDate = null;
                }

                // تحديث إشعار مدير الشؤون الفنية إذا تغير المدير الفني
                if ($project->getOriginal('technical_manager_id') != $validatedData['technical_manager_id']) {

                    $this->taskService->createApprovalTask("projects", $project, $validatedData['technical_manager_id']);
                }


                $project->update([
                    'project_name'          => $validatedData['project_name'],
                    'project_type'          => $validatedData['project_type'],
                    'technical_manager_id'  => $validatedData['technical_manager_id'],
                    'start_date'            => $startDate,
                    'contractual_closure'   => $closureDate,
                    'contract_type'             => $validatedData['contract_type'],
                    'exceptional_contract_id'   => $validatedData['exceptional_contract_id'] ?? null,
                ]);


                // -----------------------------
                // (2) تحديث العلاقة مع العقود في Pivot Table
                // -----------------------------
                // إزالة كل العقود السابقة:
                $project->contracts()->detach();



                if ($request->contract_type == Project::TYPE_CONTRACT_MAIN) {
                    $project->contracts()->attach($validatedData['primary_contract_id'], [
                        'contract_type' => 'primary'
                    ]);
                }



                // إضافة العقود الفرعية (Secondary) إن وُجدت
                if (!empty($validatedData['secondary_contract_ids'])) {
                    foreach ($validatedData['secondary_contract_ids'] as $secId) {
                        $project->contracts()->attach($secId, [
                            'contract_type' => 'secondary'
                        ]);
                    }
                }
            }

            if ($request->user()->can('الإعتماد الفني للمشاريع')) {

                // التحقق مما إذا تم تغيير مدير المشروع
                if ($project->getOriginal('manager_user_id') != $validatedData['manager_user_id']) {

                    // اضافة المهمة
                    $manager = User::find($validatedData['manager_user_id']);

                    $newTask = [
                        'user_name' => $manager->name,
                        'title' => 'تم تعينك كمدير للمشروع: (' . $project->project_name . ').',
                        'description' => 'قام ' . Auth::user()->name . ' بتعينك كمدير للمشروع (' . $project->project_name . ').',
                        'office_name' => $this->office_name

                    ];
                    SendEmailNotificationJob::dispatch([
                        'email' => $manager->email,
                    ], $newTask);

                    //
                    $project->manager_user_id = $validatedData['manager_user_id'];
                }

                $project->update([
                    // 'manager_user_id' => $validatedData['manager_user_id'],
                    'financial_claim' => $validatedData['financial_claim'] ?? null,
                    'non_financial_claim' => $validatedData['non_financial_claim'] ?? null,
                    'other_claim' => $validatedData['other_claim'] ?? null,
                    'description' => $validatedData['description'] ?? null,
                    'scope_of_work' => $validatedData['scope_of_work'] ?? null,
                    'complate_user_id' => Auth::user()->id,
                ]);
            }


            if ($request->has('team_members')) {
                $project->teamMembers()->sync($validatedData['team_members']);

                foreach ($validatedData['team_members'] as $teamMemberId) {
                    $user = User::find($teamMemberId);

                    $newTask = [
                        'user_name' => $user->name,
                        'title' => 'تم تعينك في فريق المشروع: (' . $project->project_name . ').',
                        'description' => 'قام ' . Auth::user()->name . ' بتعينك كعضو في فريق المشروع (' . $project->project_name . ').',
                        'office_name' => $this->office_name
                    ];
                    SendEmailNotificationJob::dispatch([
                        'email' => $user->email,
                    ], $newTask);
                }
            } else {
                // تفريغ الفريق باستخدام detach
                $project->teamMembers()->detach();
            }


            // إتمام المعاملة بنجاح
            DB::commit();
            return redirect()->route('projects.index')->with('success', 'تم تحديث المشروع بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'حدث خطأ أثناء تحديث المشروع: ' . $e->getMessage()])->withInput();
        }
    } // end of update



    /*
    |--------------------------------------------------------------------------
    | delete a project
    |--------------------------------------------------------------------------
    */
    public function destroy(Project $project)
    {
        $hasLawsuits = $project->lawsuits()->exists();

        // إذا كان لديه أي من هذه العلاقات
        if ($hasLawsuits) {
            return redirect()->route('projects.index')->with('error', 'لا يمكن حذف المشروع لارتباطه بسجلات أخرى.');
        }

        // حذف مرفق العقد إذا كان موجودًا
        if ($project->contract_attachment) {
            Storage::disk('public')->delete($project->contract_attachment);
        }
        $project->delete();






        return redirect()->route('projects.index')->with('success', 'تم حذف المشروع بنجاح.');
    } // end of destroy



    /*
    |--------------------------------------------------------------------------
    | update a project status
    |--------------------------------------------------------------------------
    | خاصة بتحديث حالة المشروع
    */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'new_status_id' => 'required|exists:settings_contract_statuses,id',
        ]);

        $project = Project::findOrFail($id);
        $project->new_status_id = $request->new_status_id;

        // تحديث تاريخ الإغلاق إذا كانت الحالة 'closed' فقط
        if ($request->new_status_id == 5) {
            $project->end_date = now();
        } else {
            $project->end_date = null;
        }

        $project->save();

        // التحقق مما إذا كان الطلب AJAX
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث حالة المشروع بنجاح.'
            ]);
        }

        return redirect()->route('projects.index')->with('success', 'تم تحديث حالة المشروع بنجاح.');
    }



    /*
    |--------------------------------------------------------------------------
    | delete a project attachment
    |--------------------------------------------------------------------------
    | حذف مرفق المشروع
    */
    public function deleteAttachment($id)
    {
        $attachment = ProjectAttachment::findOrFail($id);
        Storage::disk('public')->delete($attachment->attachment_file);
        $attachment->delete();

        return response()->json(['success' => 'تم حذف المرفق بنجاح.']);
    } // end of deleteAttachment


    /*
    |--------------------------------------------------------------------------
    | show a project
    |--------------------------------------------------------------------------
    | عرض بيانات المشروع
    */
    public function show(Project $project)
    {
        if (auth()->user()->can('المشاريع الخاصة بي') && !auth()->user()->can('كل المشاريع')) {
            $userRelatedProject = Project::where('id', $project->id)->userRelated()->first();

            if (!$userRelatedProject) {
                abort(404);
            }
        }


        $project->load('teamMembers');
        return view('judicial_affairs.projects.show', compact('project'));
    } // end of show



    /*
    |--------------------------------------------------------------------------
    | trashed projects
    |--------------------------------------------------------------------------
    | المشاريع المحذوفة
    */
    public function trashed(Request $request)
    {
        if ($request->ajax()) {
            // تحميل العلاقات الصحيحة: 'customer' و 'manager_user'
            $query = Project::onlyTrashed()->with(['customer', 'manager_user'])->orderBy('id', 'desc');

            $data = $query->select('projects.*');

            return DataTables::of($data)
                ->addIndexColumn()
                // ->addColumn('start_date_formatted', function (Project $project) {
                //     return $project->start_date ? Carbon::parse($project->start_date)->locale('ar')->isoFormat('D MMMM YYYY') : '';
                // })
                ->addColumn('project_name_link', function (Project $project) {
                    return '<a href="' . route('projects.show', $project->id) . '">' . e($project->project_name) . '</a>';
                })
                ->addColumn('customer_name', function (Project $project) {
                    return $project->customer->name ?? 'غير متوفر';
                })
                ->addColumn('manager_name', function (Project $project) {
                    return $project->manager_user ? '<a href="' . route('account.employee.profile', $project->manager_user->employee->id) . '">' . e($project->manager_user->name) . '</a>' : 'غير متوفر';
                })
                // ->addColumn('new_status_id', function (Project $project) {
                //     return $project->getStatusInArabic();
                // })
                ->addColumn('deleted_at_formatted', function (Project $project) {
                    return $project->deleted_at ? Hijri::ShortDate($project->deleted_at) : '';
                })
                ->addColumn('action', function ($row) {
                    $restoreUrl = route('projects.restore', $row->id);
                    $forceDeleteUrl = route('projects.forceDelete', $row->id);
                    return '
                        <a href="javascript:void(0);" onclick="confirmRestore(' . $row->id . ')" class="btn btn-sm text-success">
                            <i class="ti ti-rotate"></i> استعادة
                        </a>
                        <a href="javascript:void(0);" onclick="confirmForceDelete(' . $row->id . ')" class="btn btn-sm text-danger">
                            <i class="ti ti-trash"></i> حذف نهائي
                        </a>
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
                ->rawColumns(['project_name_link', 'manager_name', 'action'])
                ->make(true);
        }

        return view('judicial_affairs.projects.trashed');
    } // end of trashed



    /*
    |--------------------------------------------------------------------------
    | restore a project
    |--------------------------------------------------------------------------
    | استعادة المشروع
    */
    public function restore($id)
    {
        try {
            $project = Project::onlyTrashed()->findOrFail($id);
            $project->restore();

            return redirect()->route('projects.trashed')->with('success', 'تم استعادة المشروع بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء استعادة المشروع. يرجى المحاولة لاحقاً.');
        }
    } // end of restore



    /*
    |--------------------------------------------------------------------------
    | force delete a project
    |--------------------------------------------------------------------------
    | الحذف النهائي للمشروع
    */
    public function forceDelete($id)
    {
        try {
            $project = Project::onlyTrashed()->findOrFail($id);

            // حذف المرفقات المرتبطة
            if ($project->attachments()->exists()) {
                foreach ($project->attachments as $attachment) {

                    // حذف الملف من التخزين
                    Storage::disk('public')->delete($attachment->attachment_file);

                    // حذف السجل من قاعدة البيانات
                    $attachment->forceDelete();
                }
            }

            $technical_task = Task::where('project_id', $project->id)
                ->where('type_task', 'PROJECT_TECHNICAL_MANAGER')
                ->first();

            if ($technical_task) {


                $formattedUsersData = $technical_task->assignedUsers->map(function ($user) {
                    return [
                        'email' => $user->email,
                        'graph_task_id' => $user->pivot->graph_task_id ?? null,
                        'graph_list_id' => $user->pivot->graph_list_id ?? null,
                        'graph_event_id' => $user->pivot->graph_event_id ?? null
                    ];
                })->toArray();
                BatchDeleteMicrosoftTaskJob::dispatchSync($technical_task, $formattedUsersData);

                $technical_task->delete();
            }

            // حذف المشروع نهائيًا
            $project->forceDelete();
            Log::info("تم حذف المشروع نهائيًا: ID: {$id}");

            return redirect()->route('projects.trashed')->with('success', 'تم حذف المشروع والمرفقات المرتبطة به نهائيًا بنجاح.');
        } catch (\Exception $e) {
            Log::error("حدث خطأ أثناء حذف المشروع: " . $e->getMessage());

            return redirect()->back()->with('error', 'حدث خطأ أثناء حذف المشروع نهائيًا. يرجى المحاولة لاحقاً.');
        }
    }



    /*
    |--------------------------------------------------------------------------
    | get project lawsuits
    |--------------------------------------------------------------------------
    | عرض الدعوى الخاصة بالمشروع
    */
    public function getLawsuits(Request $request, Project $project)
    {
        // التحقق من وجود المشروع
        if ($project) {
            // البحث في القضايا المرتبطة بالمشروع
            $lawsuits = $project->lawsuits()
                ->when($request->input('search'), function ($query, $search) {
                    return $query->where('lawsuit_number', 'like', '%' . $search . '%');
                })
                ->paginate(5);
        }
        // تمرير البيانات إلى العرض
        return view('judicial_affairs.projects.lawsuits.show', compact('project', 'lawsuits'));
    } // end of getLawsuits



    /*
    |--------------------------------------------------------------------------
    | دعاوى المشروع json
    |--------------------------------------------------------------------------
    |
    */
    public function getRelatedLawsuits($projectId)
    {
        $lawsuits = Lawsuit::where('project_id', $projectId)->get();

        return response()->json($lawsuits);
    }



    /*
    |--------------------------------------------------------------------------
    | get project team
    |--------------------------------------------------------------------------
    | عرض الفريق الخاص بالمشروع
    */
    // فريق المشروع
    public function getTeams(Project $project)
    {
        return view('judicial_affairs.projects.teams', compact('project'));
    } // end of getTeams

    // لجلب تاريخ البد وتاريخ الاغلاق التعاقدي بعد اختيار العقد

    /*
    |--------------------------------------------------------------------------
    | get contract details
    |--------------------------------------------------------------------------
    | لعرض تفاصيل العقد
    */
    public function getContractDetails($id)
    {
        $contract = Contract::find($id);
        if ($contract) {
            $attachments = is_string($contract->attachments) ? json_decode($contract->attachments, true) : $contract->attachments;

            // تحقق مما إذا كانت المرفقات مصفوفة
            if (!is_array($attachments)) {
                $attachments = [$attachments];
            }
            return response()->json([
                'contract_start_date' => $contract->contract_start_date,
                'expected_closure_date' => $contract->expected_closure_date,
                'attachments' => $attachments,
            ]);
        } else {
            return response()->json([], 404);
        }
    } // end of getContractDetails



    /*
    |--------------------------------------------------------------------------
    | get project tasks
    |--------------------------------------------------------------------------
    | لجب مهام المشروع
    */
    public function getProjectTask($projectId, Request $request)
    {

        $project = Project::findOrFail($projectId);

        $users = User::select('id', 'name')->get();

        $predefinedTasks = [
            'دراسة المشروع',
            'اصدار وكالات',
            'قيد الدعوى',
            'تكوين فريق المشروع',
            'ارسال اخطار',
            'قيد طلب صلح',
            'استكمال المستندات',
        ];

        // جلب جميع المهام المسندة للمشروع (بدون تجزئة) لاستخدامها في التحقق من المسندة
        $allAssignedTasks = Task::where('task_field', 'projects')
            ->where('project_id', $project->id)
            ->get();

        // إنشاء مصفوفة تربط اسم المهمة بمعرفها باستخدام جميع المهام المسندة
        $assignedTasksByName = $allAssignedTasks->keyBy('task_name');



        try {
            if ($request->ajax()) {
                $tasks = Task::with(['steps' => function ($query) {
                    $query->orderBy('step_order'); // تغيير من 'order' إلى 'step_order'
                }])->where('task_field', 'projects')->where('project_id', $project->id)->select([
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

                        return '<div class="d-flex gap-2">' .
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

            // الحصول على الموظف الحالي إذا كان مرتبطًا
            $currentEmployee = auth()->user();

            $totalTasks = Task::count();
            $completedTasks = Task::where('status', 'completed')->count();
            $inProgressTasks = Task::where('status', 'in_progress')->count();
            $pendingTasks = Task::where('status', 'pending')->count();

            // الحصول على جميع الموظفين
            $users = User::select('id', 'name')->get();
            return view('judicial_affairs.projects.tasks.show', compact(
                'currentEmployee',
                'users',
                'totalTasks',
                'completedTasks',
                'inProgressTasks',
                'pendingTasks',
                'project',
                'predefinedTasks',
                'assignedTasksByName',
            ));
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

    public function getProjectMeeting($projectId)
    {
        $project = Project::findOrFail($projectId);
        $meeting = MeetingNote::where('project_id', $projectId)->get();

        return view('judicial_affairs.projects.meeting.show', compact('project', 'meeting'));
    }




    /*
    |--------------------------------------------------------------------------
    | check active cases
    |--------------------------------------------------------------------------
    | للتحقق من وجود دعاوى نشطة
    */
    public function checkActiveCases($id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        // تحقق من وجود دعاوى نشطة
        $hasActiveCases = $project->lawsuits()->where('lawsuit_status', 'active')->exists();

        return response()->json(['hasActiveCases' => $hasActiveCases]);
    } // end of checkActiveCases



    /*
    |--------------------------------------------------------------------------
    | get team members
    |--------------------------------------------------------------------------
    | لجلب كل اعضاء فريق المشروع
    */
    public function getTeamMembers($id)
    {
        try {
            $project = Project::findOrFail($id);

            $teamUsers = $project->teamMembers()->get();

            $teamMembers = $teamUsers->map(function ($user) {
                return array_merge(
                    $user->toArray(),
                    ['employee' => $user->employee->only(['id', 'name', 'nickname'])]
                );
            });

            return response()->json([
                'success' => true,
                'data' => $teamMembers,
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
    | approve a project
    |--------------------------------------------------------------------------
    | لعرض المشاريع التي بحاجة الى اعتماد
    | يتم عرض المشاريع التي لم تكتمل بعد وهي المشاريع التي لم يتم تحديد مدير المشروع فيها
    */
    public function approvals(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | للتاكد من ان المستخدم لديه الصلاحية لعرض هذه الصفحة
        |--------------------------------------------------------------------------
        | الصلاحيات هي
        | 1- Admin
        | 2- الذين لديهم صلاحية الإعتماد الفني للمشاريع
        */
        if (!Auth::user()->hasPermissionTo('الإعتماد الفني للمشاريع') && !Auth::user()->hasRole('Admin')) {
            abort(404);
        }



        /*
        |--------------------------------------------------------------------------
        | تحديد الحقول المعينة لعرضها في الجدول
        |--------------------------------------------------------------------------
        */
        $selectedFields = [
            'id',
            'project_name',
            'project_number',
            'manager_user_id',
            'start_date',
            'new_status_id',
            'technical_manager_id'
        ];



        /*
        |--------------------------------------------------------------------------
        | اذا كان مدير النظام فيعرض له كل البيانات واذا كان مدير فني يعرض له فقط المشاريع التي يديرها
        |--------------------------------------------------------------------------
        | Description of this section.
        */
        if (Auth::user()->hasRole('Admin')) {
            $query = Project::select($selectedFields)->where('manager_user_id', null);
        } else {
            $query = Project::select($selectedFields)->where('manager_user_id', null)->where('technical_manager_id', Auth::user()->id);
        }



        /*
        |--------------------------------------------------------------------------
        | تهيئة البيانات للعرض
        |--------------------------------------------------------------------------
        */
        if ($request->ajax()) {

            if ($request->has('new_status_id') && $request->new_status_id != '') {
                $query->where('new_status_id', $request->new_status_id);
            }

            if ($request->has('employee') && $request->employee != '') {
                $query->where('manager_user_id', $request->employee);
            }



            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="row-checkbox" value="' . $row->id . '">';
                })

                ->addColumn('project_name', function (Project $project) {
                    return
                        '<a href="' . route('projects.show', $project->id) . '">' . $project->project_name . '</a> ';
                })


                ->addColumn('new_status_id', function (Project $project) {
                    return '<span title=" مدير الشؤون الفنية ' . $project->technical_manager->name . '">في انتظار استكمال المشروع</span>';
                })

                // order
                ->orderColumn('project_name', function ($query, $order) {
                    $query->orderBy('project_name', $order);
                })

                ->orderColumn('manager_user_id', function ($query, $order) {
                    $query->orderBy('manager_user_id', $order);
                })
                // fillters
                ->filterColumn('project_name', function ($query, $keyword) {
                    $query->where('project_name', 'like', "%{$keyword}%");
                })

                ->filterColumn('manager_user_id', function ($query, $keyword) {

                    $query->whereHas('manager_user', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })

                ->addColumn('action', function ($row) {
                    $editUrl = route('projects.edit', $row->id);
                    $completeUrl = route('projects.complete', $row->id);
                    $deleteUrl = route('projects.destroy', $row->id);
                    $formId = 'delete-form-' . $row->id;
                    $csrfField = csrf_field();
                    $methodField = method_field('DELETE');

                    $buttons = '<div class="d-flex gap-2">';

                    if (auth()->user()->can('الإعتماد الفني للمشاريع')) {
                        $buttons .= '<a title="اضغط هنا لاستكمال المشروع"  href="' . $completeUrl . '" class="btn btn-sm text-secondary"><i class="ti ti-file-check"></i></a>';
                    }


                    $buttons .= '</div>';

                    return $buttons;
                })
                ->rawColumns(['new_status_id', 'action', 'project_name', 'manager_user_id'])
                ->make(true);
        }



        /*
        |--------------------------------------------------------------------------
        | البيانات الإحصائية
        |--------------------------------------------------------------------------
        */
        $totalProjects = $query->count();
        $last30DaysProjects = $query->where('created_at', '>=', Carbon::now()->subDays(30))->count();
        // $ongoingProjects = $query->where('status', 'ongoing')->count();
        // $completedProjects = $query->where('status', 'completed')->count();
        // $canceledProjects = $query->where('status', 'canceled')->count();

        return view('judicial_affairs.projects.approvals', [
            'totalProjects' => $totalProjects,
            'last30DaysProjects' => $last30DaysProjects,
            // 'ongoingProjects' => $ongoingProjects,
            // 'completedProjects' => $completedProjects,
            // 'canceledProjects' => $canceledProjects,

        ]);
    } // end of approvals

    /*
    |--------------------------------------------------------------------------
    | تحسين الكود
    |--------------------------------------------------------------------------
    */


    /*
    |============================================================================
    |============================================================================
    |                            API
    |============================================================================
    |============================================================================
    */
    public function getProjectsJson()
    {
        $projects = Project::select('id', 'project_name')->get();
        return response()->json([
            'success'   => true,
            'data'      => $projects,
        ]);
    }
}
