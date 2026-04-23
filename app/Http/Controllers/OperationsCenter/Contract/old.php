<?php

namespace App\Http\Controllers\judicial_affairs;

use Alkoumi\LaravelHijriDate\Hijri;
use App\Helpers\SettingsHelper;
use App\Http\Controllers\Controller;
use App\Jobs\DeleteFileFromOneDriveJob;
use App\Jobs\RenameFileOnOneDriveJob;
use App\Jobs\Tasks\CreateTaskJob;
use App\Jobs\UploadFileToOneDriveJob;
use App\Models\ContractAttachment;
use App\Models\general_setting\SettingsContractStatus;
use App\Models\general_setting\SettingsTemplate;
use App\Models\hr\employees\Employees;
use App\Models\Item;
use App\Models\judicial_affairs\Contract;
use App\Models\judicial_affairs\ContractApprovalLog;
use App\Models\judicial_affairs\Offers as Judicial_affairsOffers;
use App\Models\task\Task;
use App\Services\FilesService;
use App\Services\MicrosoftGraphBaseService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\HandlesTaskAndEvent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;


class ContractController extends Controller
{
    use HandlesTaskAndEvent;


    public function __construct()
    {
        $this->middleware('can:العقود')->only(['index', 'show']);
        $this->middleware('can:أضافة عقد')->only(['create', 'store']);
        $this->middleware('can:تعديل عقد')->only(['edit', 'update']);
        $this->middleware('can:حذف عقد')->only(['destroy']);
        $this->middleware('can:الأرشيف')->only(['trashed']);
    }

    protected function getAccessToken()
    {
        $user = Auth::user();

        if (!$user->microsoft_token) {
            return redirect()->route('microsoft.login')->with('error', 'يرجى ربط حساب Microsoft الخاص بك.');
        }
        $graphService = app(MicrosoftGraphBaseService::class);
        $accessToken = $graphService->getValidUserAccessToken($user);

        if (!$accessToken) {
            return redirect()->route('dashboard')->with('error', 'فشل في تجديد رمز الوصول. يرجى إعادة ربط حساب Microsoft.');
        }
        return $accessToken;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $route = 'contracts';
        try {
            if ($request->ajax()) {
                $query = Contract::select([
                    'id',
                    'contract_name',
                    'contract_number',
                    'customer_id',
                    'contract_start_date',
                    'expected_closure_date',
                    'contract_manager_id',
                    'offer_id',
                ]);

                // تطبيق الفلاتر إذا كانت موجودة
                if ($request->has('status') && $request->status != '') {
                    $query->where('contract_status_id', $request->status);
                }


                if ($request->has('customer') && $request->customer != '') {
                    $query->where('customer_id', $request->customer)
                        ->orWhereHas('mainContract', function ($q) use ($request) {
                            $q->where('customer_id', $request->customer);
                        });
                }


                if ($request->has('employee') && $request->employee != '') {
                    $query->where('contract_manager_id', $request->employee);
                }

                $data = $query->select('contracts.*');

                return datatables()->of($data)
                    ->addIndexColumn()
                    ->addColumn('checkbox', function ($row) {
                        return '<input type="checkbox" class="row-checkbox" value="' . $row->id . '">';
                    })

                    ->addColumn('contract_name', function ($row) {
                        return '<a href="' . route('contracts.show', $row->id) . '">
                                ' . e($row->contract_name) . '
                            </a>';
                    })
                    ->addColumn('status', function ($row) {
                        $status = '';
                        $tooltip = '';

                        switch ($row->status) {
                            case 'approved':
                                $status = 'معتمد';
                                $tooltip = 'تم اعتماد العقد بالكامل.';
                                break;

                            case 'pending':
                                $status = 'قيد الانتظار';
                                $nextApprover = $this->getNextApprover($row->id);
                                $tooltip = $nextApprover ? 'الاعتماد القادم لدى: ' . $nextApprover : 'لا يوجد معلومات عن المعتمد التالي.';
                                break;

                            case 'rejected':
                                $status = 'مرفوض';
                                $tooltip = 'تم رفض العقد.';
                                break;

                            default:
                                $status = 'غير معروف';
                                $tooltip = 'حالة العقد غير معروفة.';
                                break;
                        }

                        return '<span style="cursor: pointer" title="' . e($tooltip) . '">' . $status . '</span>';
                    })
                    ->addColumn('contract_manager_id', function ($row) {
                        return $row->contractManager ? $row->contractManager->name : 'غير متوفر';
                    })
                    ->addColumn('customer_id', function ($row) {

                        if ($row->contract_type == 'main') {
                            return $row->customer ? $row->customer->name : 'غير متوفر';
                        } else {
                            $mainContract = Contract::find($row->main_contract_id);
                            return $row->mainContract ? $row->mainContract->customer->name : 'غير متوفر';
                        }
                    })
                    ->addColumn('offer_id', function ($row) {
                        return $row->offer ? $row->offer->offer_name : 'غير متوفر';
                    })
                    ->addColumn('action', function ($row) {
                        $editUrl = route('contracts.edit', $row->id);
                        $deleteUrl = route('contracts.destroy', $row->id);
                        $formId = 'delete-form-' . $row->id;
                        $csrfField = csrf_field();
                        $methodField = method_field('DELETE');

                        $buttons = '<div class="d-flex gap-2">';
                        // تحقق من صلاحية التعديل
                        if (auth()->user()->can('تعديل عقد')) {
                            $buttons .= '<a href="' . $editUrl . '" class="btn btn-sm text-secondary"><i class="ti ti-edit"></i></a>';
                        }

                        // تحقق من صلاحية الحذف
                        if (auth()->user()->can('حذف عقد')) {
                            $buttons .= '<button type="button" class="btn btn-sm text-secondary" onclick="confirmDelete(' . $row->id . ')">
                                                <i class="ti ti-trash"></i>
                                             </button>
                                             <form id="' . $formId . '" action="' . $deleteUrl . '" method="POST" style="display: none;">
                                                ' . $csrfField . '
                                                ' . $methodField . '
                                             </form>';
                        }

                        $buttons .= '</div>';

                        return $buttons;
                    })
                    ->editColumn('created_at', function ($row) {
                        // تنسيق تاريخ الإنشاء إذا كان متوفراً
                        return $row->created_at ? $row->created_at->format('d/m/Y') : '';
                    })

                    // الترتيب
                    ->orderColumn('customer_id', function ($query, $order) {
                        $query->orderBy('customer_id', $order);
                    })
                    ->orderColumn('contract_manager_id', function ($query, $order) {
                        $query->orderBy('contract_manager_id', $order);
                    })
                    ->orderColumn('offer_id', function ($query, $order) {
                        $query->orderBy('offer_id', $order);
                    })

                    // filtters
                    ->filterColumn('customer_id', function ($query, $keyword) {
                        // تمكين البحث بناءً على اسم العميل
                        $query->whereHas('customer', function ($q) use ($keyword) {
                            $q->where('name', 'like', "%{$keyword}%");
                        });
                    })
                    ->filterColumn('contract_manager_id', function ($query, $keyword) {
                        // تمكين البحث بناءً على اسم مسؤول العقد
                        $query->whereHas('contractManager', function ($q) use ($keyword) {
                            $q->where('name', 'like', "%{$keyword}%");
                        });
                    })
                    ->filterColumn('offer_id', function ($query, $keyword) {
                        // تمكين البحث بناءً على اسم مرحلة العرض
                        $query->whereHas('offer', function ($q) use ($keyword) {
                            $q->where('offer_name', 'like', "%{$keyword}%");
                        });
                    })
                    ->rawColumns(['action', 'status', 'contract_name'])
                    ->make(true);
            }
            // إحصائيات العقود
            $totalContracts = Contract::count();
            $newContracts = Contract::where('contract_status_id', 1)->count();

            $lateContracts = Contract::where('contract_status_id', 4)->count();

            $closedContracts = Contract::where('contract_status_id', 5)->count();

            return view('judicial_affairs.contracts.index', compact('route', 'totalContracts', 'newContracts', 'lateContracts', 'closedContracts'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    } // end of index

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employees = Employees::select(['id', 'name'])->get();
        $offers = Judicial_affairsOffers::where('status', 'approved')->whereDoesntHave('contracts')->select(['id', 'offer_name'])->get();
        $settings_contract_status = SettingsContractStatus::where('status', 'active')->select(['id', 'name'])->get();

        $mainContracts = Contract::where('contract_type', 'main')->get();

        return view('judicial_affairs.contracts.create', compact('employees', 'offers', 'settings_contract_status', 'mainContracts'));
    }


    public function getMainContractDetails($id)
    {
        $contract = Contract::with(['offer', 'customer', 'relationshipManager'])->find($id);

        if (!$contract || $contract->contract_type !== 'main') {
            return response()->json(['error' => 'العقد غير موجود أو ليس عقدًا رئيسيًا.'], 404);
        }

        return response()->json([
            'offer' => [
                'offer_id' => $contract->offer->id,
                'offer_name' => $contract->offer->offer_name,
            ],
            'customer' => [
                'id' => $contract->customer->id,
                'name' => $contract->customer->name,
            ],
            'relationship_manager' => [
                'id' => $contract->relationship_manager_id,
                'name' => $contract->relationshipManager->name,
            ],
            'technical_offer' => $contract->offer->technical_offer,
            'financial_offer' => $contract->offer->financial_offer,
        ]);
    }


    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'contract_type' => 'required|in:main,supplementary',
            'main_contract_id' => 'nullable|required_if:contract_type,supplementary|exists:contracts,id',

            'contract_name' => 'required|string|max:255',
            'contract_manager_id' => 'nullable|exists:employees,id',
            'contract_status_id' => 'nullable|exists:settings_contract_statuses,id',
            'offer_id' => 'nullable|required_if:contract_type,main|exists:offers,id',

            'contract_start_date' => 'required|date',
            'contract_end_date' => 'nullable|date',
            'expected_closure_date' => 'required|date',

            'technical_offer' => '',
            'financial_offer' => '',

            'additional_attachments.*.name' => 'required|string|max:255',
            'additional_attachments.*.file' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
            'is_private_and_secret' => 'nullable',
        ], [
            'contract_name.required' => 'حقل اسم العقد مطلوب.',
            'contract_name.string' => 'يجب أن يكون اسم العقد نصًا صحيحًا.',
            'contract_name.max' => 'اسم العقد يجب ألا يتجاوز 255 حرفًا.',

            'contract_manager_id.exists' => 'مدير العقد المحدد غير موجود في النظام.',

            'contract_status_id.exists' => 'حالة العقد المحددة غير موجودة في النظام.',

            'offer_id.required' => 'حقل العرض مطلوب.',
            'offer_id.exists' => 'العرض المحدد غير موجود في النظام.',

            'contract_start_date.required' => 'حقل تاريخ بداية العقد مطلوب.',
            'contract_start_date.date' => 'تاريخ بداية العقد غير صالح.',

            'contract_end_date.date' => 'تاريخ نهاية العقد غير صالح.',
            'contract_end_date.after' => 'يجب أن يكون تاريخ نهاية العقد بعد تاريخ بداية العقد.',

            'expected_closure_date.required' => 'حقل تاريخ الإغلاق المتوقع مطلوب.',
            'expected_closure_date.date' => 'تاريخ الإغلاق المتوقع غير صالح.',
            'expected_closure_date.after' => 'يجب أن يكون تاريخ الإغلاق المتوقع بعد تاريخ بداية العقد.',

            'additional_attachments.*.name.required' => 'اسم المرفق الإضافي مطلوب.',
            'additional_attachments.*.name.string' => 'يجب أن يكون اسم المرفق نصًا صحيحًا.',
            'additional_attachments.*.name.max' => 'اسم المرفق يجب ألا يتجاوز 255 حرفًا.',

            'additional_attachments.*.file.required' => 'المرفق مطلوب.',
            'additional_attachments.*.file.file' => 'يجب أن يكون المرفق ملفًا صالحًا.',
            'additional_attachments.*.file.mimes' => 'يجب أن يكون المرفق من نوع JPG, JPEG, PNG, PDF, DOC, DOCX.',
            'additional_attachments.*.file.max' => 'حجم المرفق يجب ألا يتجاوز 2 ميجابايت.',
        ]);



        $contract_start_date = $this->convertToGregorian($validatedData['contract_start_date']);
        $contract_end_date = isset($validatedData['contract_end_date']) ? $this->convertToGregorian($validatedData['contract_end_date']) : null;
        $expected_closure_date = $this->convertToGregorian($validatedData['expected_closure_date']);

        // التحقق من تواريخ البداية والنهاية إذا كانت النهاية موجودة
        if ($contract_end_date && Carbon::parse($contract_end_date)->lt(Carbon::parse($contract_start_date))) {
            return redirect()->back()->with('error', 'تاريخ نهاية العقد يجب أن يكون بعد أو مساوي تاريخ بداية العقد.')->withInput();
        }

        // التحقق من تاريخ الإغلاق المتوقع مع تاريخ البداية
        if (Carbon::parse($expected_closure_date)->lt(Carbon::parse($contract_start_date))) {
            return redirect()->back()->with('error', 'تاريخ الإغلاق المتوقع يجب أن يكون بعد أو مساوي تاريخ بداية العقد.')->withInput();
        }



        // اذا كان العقد الرئيسي
        if ($validatedData['contract_type'] == 'main') {
            // جلب العرض المرتبط
            $offer = Judicial_affairsOffers::findOrFail($validatedData['offer_id']);
            $offer->technical_offer = $validatedData['technical_offer'];
            $offer->financial_offer = $validatedData['financial_offer'];
            $offer->save();

            // إنشاء العقد باستخدام البيانات المحققة والمعلومات المرتبطة بالعرض
            $contract = Contract::create(array_merge($validatedData, [
                'customer_id' => $offer->customer_id,
                'relationship_manager_id' => $offer->relationship_manager_id,
                'created_by' => auth()->user()->employee->id ?? null, // تعيين created_by
                'contract_number' => str_replace('Q', 'C', $offer->offer_number),
                'is_private_and_secret' => $request->has('is_private_and_secret'),

            ]));

            // اذا كان العقد ملحق
        } else if ($validatedData['contract_type'] == 'supplementary') {
            $main_contract = Contract::findOrFail($validatedData['main_contract_id']);

            $contract = Contract::create(array_merge($validatedData, [
                'supplementary_technical_offer' => $validatedData['technical_offer'],
                'supplementary_financial_offer' => $validatedData['financial_offer'],

                'created_by' => auth()->user()->employee->id ?? null, // تعيين created_by
                'contract_number' => $main_contract->contract_number . '-' . $main_contract->supplementaryContracts->count() + 1,
                'is_private_and_secret' => $request->has('is_private_and_secret'),

            ]));
        }
        // الحصول على بيانات الوصول (Access Token) والمستخدم
        $accessToken = $this->getAccessToken();
        $filesService = app(FilesService::class);
        $userId = $filesService->getUserIdByEmail($accessToken, Auth::user()->email);
        $folderId = $filesService->getOrCreateFolder($accessToken, $userId, 'root', 'العقود');
        // التحقق من وجود مرفقات إضافية
        if (isset($validatedData['additional_attachments']) && is_array($validatedData['additional_attachments'])) {
            foreach ($validatedData['additional_attachments'] as $attachment) {
                $fileExtension          = $attachment['file']->getClientOriginalExtension();
                $fileNameWithExtension  = $attachment['name'] . '.' . $fileExtension;
                $storedFilePath         =  $attachment['file']->store('uploads/attachments');
                $fullFilePath           = storage_path('app/' . $storedFilePath);

                // حفظ معلومات المرفق في قاعدة البيانات بدون file_id و file_url
                $contractAttachment = ContractAttachment::create([
                    'contract_id' => $contract->id,
                    'name' => $fileNameWithExtension,
                ]);

                UploadFileToOneDriveJob::dispatch(
                    Auth::user()->id, // معرف المستخدم في النظام
                    $userId,
                    $folderId,
                    $fullFilePath,
                    $fileNameWithExtension,
                    $contractAttachment->id
                );
            }
        }

        /*
            |--------------------------------------------------------------------------
            | ازالة الكاش بعد اضافة العقد
            |--------------------------------------------------------------------------
            */
        Cache::forget('new_contracts_count');




        // جلب بيانات الاعتماد من جدول Item
        $itemForContract = Item::where('type', 'contract')->first();

        if (
            !$itemForContract ||
            (!$itemForContract->approver1_id && !$itemForContract->approver2_id && !$itemForContract->approver3_id)
        ) {
            // لا يوجد معتمدين، اعتمد العقد تلقائيًا
            $contract->status = 'approved';
            $contract->save();

            // تسجيل عملية الاعتماد في سجل الاعتمادات
            ContractApprovalLog::create([
                'contract_id' => $contract->id,
                'employee_id' => auth()->user()->employee->id,
                'action' => 'approved',
                'reason' => 'تم اعتماد العقد تلقائيًا لعدم وجود معتمدين.',
            ]);
        } else {
            if ($itemForContract->approver1_id) {

                // اضافة المهمة للمتعمد الاول
                $newTask = [
                    'task_name' => 'لديك عقد جديد باسم (' . $contract->contract_name . ') يحتاج إلى إعتمادك',
                    'priority' => 'high',
                    'description' => 'تم إضافة العقد (' . $contract->contract_name . ') بواسطة ' . Auth::user()->name . ' والعقد في انتظار إعتمادك.',
                    'task_field' => 'contracts',
                    'contract_id' => $contract->id,
                    'assigned_user_id' => $itemForContract->approver1_id,
                    'created_by_user_id' => auth()->id(),

                ];
                // ارسال اشعار للمستخدم فقط إذا كان approver1_id موجودًا
                CreateTaskJob::dispatch($newTask, $itemForContract->approver1_id);
            }
        }

        // إعادة التوجيه مع رسالة نجاح
        return redirect()->route('contracts.index')->with('success', 'تم إضافة العقد بنجاح!');
    }







    public function show(string $id)
    {
        $contract = Contract::findOrFail($id);
        $itemForContract = Item::where('type', 'contract')->first();

        // تحديد مراحل الاعتماد بناءً على المعتمدين النشطين
        $approvers = [];

        if ($itemForContract) {
            if ($itemForContract->approver1_id) {
                $approvers[] = [
                    'field' => 'approver1_approved',
                    'label' => 'المعتمد الأول',
                    'id' => $itemForContract->approver1_id,
                ];
            }
            if ($itemForContract->approver2_id) {
                $approvers[] = [
                    'field' => 'approver2_approved',
                    'label' => 'المعتمد الثاني',
                    'id' => $itemForContract->approver2_id,
                ];
            }
            if ($itemForContract->approver3_id) {
                $approvers[] = [
                    'field' => 'approver3_approved',
                    'label' => 'المعتمد الثالث',
                    'id' => $itemForContract->approver3_id,
                ];
            }
        }

        // التحقق مما إذا كان هناك أي معتمد قد رفض العقد
        $hasRejected = false;
        $rejectionField = null;
        $rejectionReason = null;
        $rejectionApproverName = null;
        foreach ($approvers as $approver) {
            if ($contract->{$approver['field']} === 'rejected') {
                $hasRejected = true;
                $rejectionField = $approver['field'];
                $rejectionReason = $contract->{'approver' . substr($approver['field'], 9, 1) . '_rejection_reason'};

                // جلب اسم المعتمد الذي قام بالرفض
                $employee = \App\Models\hr\employees\Employees::find($approver['id']);
                $rejectionApproverName = $employee ? $employee->name : 'غير متوفر';
                break;
            }
        }


        // تحديد المعتمد الحالي (الذي يجب عليه الاعتماد أو الرفض)
        $currentApproval = null;
        foreach ($approvers as $approver) {
            if ($contract->{$approver['field']} === null) {
                $currentApproval = $approver;
                break;
            }
        }

        // تحديد صلاحيات المستخدم الحالي
        $currentUserId = auth()->user()->employee->id ?? null;
        $canApprove = false;
        $canReject = false;
        $canRevoke = false;

        if ($currentApproval && !$hasRejected) {
            if ($currentApproval['id'] === $currentUserId) {
                $canApprove = true;
                $canReject = true;
            }
        }

        // تحديد ما إذا كان يمكن للمستخدم إلغاء الاعتماد
        if (!$hasRejected) {
            // إيجاد آخر معتمد قام بالاعتماد
            $lastApprovedApprover = null;
            foreach (array_reverse($approvers) as $approver) {
                if ($contract->{$approver['field']} === 'approved') {
                    $lastApprovedApprover = $approver;
                    break;
                }
            }
            if ($lastApprovedApprover && $lastApprovedApprover['id'] === $currentUserId) {
                $canRevoke = true;
            }
        }

        $lastApprovedField = $lastApprovedApprover['field'] ?? null;

        // جلب سجلات الاعتماد
        $approvalLogs = $contract->approvalLogs()->with('employee')->get();


        // النموذج الخاص بالعرض
        $template = SettingsTemplate::where('template_type', 'contracts')->first();

        $data = [
            // customer data
            'customer_name' => $contract->contract_type === 'main'
                ? $contract->customer->name
                : ($contract->mainContract ? $contract->mainContract->customer->name : '-'),
            'civil_registry' => $contract->contract_type === 'main'
                ? $contract->customer->civil_registry
                : ($contract->mainContract ? $contract->mainContract->customer->civil_registry : '-'),
            'address' => $contract->contract_type === 'main'
                ? ($contract->customer->region->name ?? '-')
                : ($contract->mainContract && $contract->mainContract->customer->region ? $contract->mainContract->customer->region->name : '-'),
            'contactNumber' => $contract->contract_type === 'main'
                ? $contract->customer->contactNumber
                : ($contract->mainContract ? $contract->mainContract->customer->contactNumber : '-'),
            'email' => $contract->contract_type === 'main'
                ? $contract->customer->email
                : ($contract->mainContract ? $contract->mainContract->customer->email : '-'),

            // contract data
            'contract_name' => $contract->contract_name,
            'contract_number' => $contract->contract_number,
            'expected_closure_date' => $contract->expected_closure_date,
            'contract_start_date' => $contract->contract_start_date,
            'contract_end_date' => $contract->contract_end_date,
            // offer data
            // offer data
            'technical_offer' => $contract->contract_type === 'main'
                ? ($contract->offer ? $contract->offer->technical_offer : '-')
                : $contract->supplementary_technical_offer,
            'financial_offer' => $contract->contract_type === 'main'
                ? ($contract->offer ? $contract->offer->financial_offer : '-')
                : $contract->supplementary_financial_offer,

            // date
            'current_date' => Carbon::now(),

        ];

        // استبدال المتغيرات بالقيم الفعلية
        $processedContent = $this->replaceVariables($template->content, $data);

        $processedContent = str_replace('   ', '&nbsp;&nbsp;&nbsp;', $processedContent);


        return view('judicial_affairs.contracts.show', compact(
            'contract',
            'approvers',
            'canApprove',
            'canReject',
            'canRevoke',
            'currentApproval',
            'hasRejected',
            'rejectionField',
            'rejectionReason',
            'rejectionApproverName',
            'lastApprovedField',
            'approvalLogs',
            'processedContent'
        ));
    }



    /*
    |--------------------------------------------------------------------------
    | replaceVariables
    |--------------------------------------------------------------------------
    | خاصة بتحويل المتغيرات في النموذج إلى قيمها الفعلية.
    */
    protected function replaceVariables($content, $data)
    {
        return preg_replace_callback('/{{\s*(.*?)\s*}}/', function ($matches) use ($data) {
            $variableName = $matches[1];

            switch ($variableName) {
                case 'contract_name':
                    return $data['contract_name'];

                case 'contract_number':
                    return $data['contract_number'];

                case 'customer_name':
                    return $data['customer_name'];

                case 'civil_registry':
                    return $data['civil_registry'];

                case 'address':
                    return $data['address'];

                case 'contactNumber':
                    return $data['contactNumber'];

                case 'email':
                    return $data['email'];

                case 'expected_closure_date':
                    return $data['expected_closure_date'];

                case 'contract_start_date':
                    return $data['contract_start_date'];

                case 'contract_end_date':
                    return $data['contract_end_date'];

                case 'technical_offer':
                    return $data['technical_offer'];

                case 'financial_offer':
                    return $data['financial_offer'];

                case 'current_date':
                    return $data['current_date']->format('Y-m-d');


                    // أضف المزيد من المتغيرات حسب الحاجة
                default:
                    return $matches[0]; // إرجاع المتغير كما هو إذا لم يتم العثور على تطابق
            }
        }, $content);
    }



    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        // البحث عن العقد الحالي
        $contract = Contract::findOrFail($id);

        // التحقق من وجود عروض معتمدة غير مرتبطة بعقود أو العرض المرتبط بهذا العقد


        // استرجاع العروض
        $offers = Judicial_affairsOffers::where('status', 'approved')
            ->where(function ($query) use ($contract) {
                $query->whereDoesntHave('contracts') // العروض غير المرتبطة بعقود
                    ->orWhere('id', $contract->offer_id); // العرض المرتبط بالعقد الحالي
            })->select(['id', 'offer_name'])->get();

        $employees = Employees::select(['id', 'name'])->get();
        $settings_contract_status = SettingsContractStatus::where('status', 'active')->select(['id', 'name'])->get();

        // الحصول على معلمة from_show من الـ URL
        $from_show = $request->query('from_show', false);


        $mainContracts = Contract::where('contract_type', 'main')
            ->where('id', '!=', $contract->id) // استثناء العقد الحالي إذا كان رئيسياً
            ->select(['id', 'contract_name', 'contract_number'])
            ->get();


        return view('judicial_affairs.contracts.edit', compact('employees', 'offers', 'settings_contract_status', 'contract', 'from_show', 'mainContracts'));
    } // end of edit




    /*
    |--------------------------------------------------------------------------
    | update contract
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        // جلب العقد القائم
        $contract = Contract::findOrFail($id);

        // التحقق من صحة البيانات المدخلة
        $validatedData = $request->validate(
            [
                'contract_type' => 'required|in:main,supplementary',
                'main_contract_id' => 'nullable|required_if:contract_type,supplementary|exists:contracts,id',

                'contract_name' => 'required|string|max:255',
                'contract_manager_id' => 'nullable|exists:employees,id',
                'contract_status_id' => 'nullable|exists:settings_contract_statuses,id',
                'offer_id' => 'nullable|required_if:contract_type,main|exists:offers,id',

                'contract_start_date' => 'required|date',
                'contract_end_date' => 'nullable|date',
                'expected_closure_date' => 'required|date',

                'technical_offer' => '',
                'financial_offer' => '',

                'existing_attachment_names.*' => 'required|string|max:255',
                'attachment_names.*' => 'nullable|string|max:255',
                'attachment_files.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',

                'delete_attachments.*' => 'nullable|exists:contract_attachments,id',

                'is_private_and_secret' => 'nullable',

            ],
            [
                'contract_name.required' => 'حقل اسم العقد مطلوب.',
                'contract_name.string' => 'يجب أن يكون اسم العقد نصًا صحيحًا.',
                'contract_name.max' => 'اسم العقد يجب ألا يتجاوز 255 حرفًا.',

                'contract_manager_id.exists' => 'مدير العقد المحدد غير موجود في النظام.',

                'contract_status_id.exists' => 'حالة العقد المحددة غير موجودة في النظام.',

                'offer_id.required' => 'حقل العرض مطلوب.',
                'offer_id.exists' => 'العرض المحدد غير موجود في النظام.',

                'contract_start_date.required' => 'حقل تاريخ بداية العقد مطلوب.',
                'contract_start_date.date' => 'تاريخ بداية العقد غير صالح.',

                'contract_end_date.date' => 'تاريخ نهاية العقد غير صالح.',
                'contract_end_date.after' => 'يجب أن يكون تاريخ نهاية العقد بعد تاريخ بداية العقد.',

                'expected_closure_date.required' => 'حقل تاريخ الإغلاق المتوقع مطلوب.',
                'expected_closure_date.date' => 'تاريخ الإغلاق المتوقع غير صالح.',
                'expected_closure_date.after' => 'يجب أن يكون تاريخ الإغلاق المتوقع بعد تاريخ بداية العقد.',

                'attachment_names.*.string' => 'اسم المرفق يجب أن يكون نصًا.',
                'attachment_names.*.max' => 'اسم المرفق يجب ألا يتجاوز 255 حرفًا.',

                'attachment_files.*.file' => 'المرفق يجب أن يكون ملفًا.',
                'attachment_files.*.mimes' => 'المرفق يجب أن يكون بصيغة: pdf, doc, docx, jpg, jpeg, png.',
                'attachment_files.*.max' => 'حجم المرفق يجب ألا يتجاوز 5 ميجابايت.',
            ]
        );


        if (
            $contract->contract_type === 'main' && // العقد الحالي نوعه رئيسي
            $validatedData['contract_type'] === 'supplementary' && // النوع الجديد هو ملحق
            $contract->supplementaryContracts()->exists() // يحتوي على عقود فرعية
        ) {
            return redirect()->back()->with('error', 'لا يمكن تعديل العقد الرئيسي إلى عقد ملحق لأنه يحتوي على عقود فرعية.')->withInput();
        }


        $contract_start_date = $this->convertToGregorian($validatedData['contract_start_date']);
        $contract_end_date = isset($validatedData['contract_end_date']) ? $this->convertToGregorian($validatedData['contract_end_date']) : null;
        $expected_closure_date = $this->convertToGregorian($validatedData['expected_closure_date']);

        // التحقق من تواريخ البداية والنهاية إذا كانت النهاية موجودة
        if ($contract_end_date && Carbon::parse($contract_end_date)->lt(Carbon::parse($contract_start_date))) {
            return redirect()->back()->with('error', 'تاريخ نهاية العقد يجب أن يكون بعد أو مساوي تاريخ بداية العقد.')->withInput();
        }

        // التحقق من تاريخ الإغلاق المتوقع مع تاريخ البداية
        if (Carbon::parse($expected_closure_date)->lt(Carbon::parse($contract_start_date))) {
            return redirect()->back()->with('error', 'تاريخ الإغلاق المتوقع يجب أن يكون بعد أو مساوي تاريخ بداية العقد.')->withInput();
        }

        if ($contract->contract_type !== $validatedData['contract_type']) {
            if ($validatedData['contract_type'] === 'main') {
                // إذا تم تغيير العقد إلى رئيسي
                $offer = Judicial_affairsOffers::findOrFail($validatedData['offer_id']);
                $newContractNumber = str_replace('Q', 'C', $offer->offer_number); // تحويل رقم العرض إلى رقم عقد
                $contract->update(['contract_number' => $newContractNumber]);

                // نفريغ الحقول الخاصة بالعقود الفرعية
                $contract->update([
                    'main_contract_id' => null,
                    'supplementary_technical_offer' => null,
                    'supplementary_financial_offer' => null,
                ]);
            } elseif ($validatedData['contract_type'] === 'supplementary') {
                // إذا تم تغيير العقد إلى ملحق
                $mainContract = Contract::findOrFail($validatedData['main_contract_id']);
                $supplementaryCount = $mainContract->supplementaryContracts()->count() + 1;
                $newContractNumber = $mainContract->contract_number . '-' . $supplementaryCount;
                $contract->update(['contract_number' => $newContractNumber]);
                // تفريغ الحقول الخاصة بالعقود الرئيسية
                $contract->update([
                    'offer_id' => null,
                    'customer_id' => null,
                    'relationship_manager_id' => null,
                ]);
            }
        }


        // تحديث بيانات العقد الرئيسي أو الملحق
        if ($validatedData['contract_type'] == 'main') {
            // تحديث العرض المرتبط بالعقد الرئيسي
            $offer = Judicial_affairsOffers::findOrFail($validatedData['offer_id']);
            $offer->update([
                'technical_offer' => $validatedData['technical_offer'],
                'financial_offer' => $validatedData['financial_offer'],
            ]);

            // تحديث بيانات العقد الرئيسي
            $contract->update(array_merge($validatedData, [
                'customer_id' => $offer->customer_id,
                'relationship_manager_id' => $offer->relationship_manager_id,
                'updated_by' => auth()->user()->employee->id ?? null, // تعيين updated_by
                'is_private_and_secret' => $request->has('is_private_and_secret'),

            ]));
        } elseif ($validatedData['contract_type'] == 'supplementary') {
            $mainContract = Contract::findOrFail($validatedData['main_contract_id']);

            $contract->update(array_merge($validatedData, [
                'supplementary_technical_offer' => $validatedData['technical_offer'],
                'supplementary_financial_offer' => $validatedData['financial_offer'],
                'updated_by' => auth()->user()->employee->id ?? null, // تعيين updated_by
                'is_private_and_secret' => $request->has('is_private_and_secret'),

            ]));
        }

        // لجزئية الجديدةا
        // إذا كان الـ CheckBox مفعّل وأتى من شاشة التفاصيل
        if ($request->has('reset_approvals') && $request->input('from_show') === 'true') {
            // التحقق من أن المستخدم هو Admin أو الشخص الذي أضاف العقد
            if (!auth()->user()->hasRole('Admin') && auth()->user()->employee->id !== $contract->created_by) {
                return redirect()->back()->with('error', 'ليس لديك الصلاحية لإعادة الاعتماد.');
            }

            // حقول الاعتماد
            $fields = [
                'approver1_approved',
                'approver2_approved',
                'approver3_approved',
            ];

            // إعادة تمكين الاعتماد المرفوض بإعداد الحقول إلى 'pending'
            foreach ($fields as $approvedField) {
                if ($contract->$approvedField === 'rejected') {
                    $contract->$approvedField = null; // إعادة الحالة إلى null
                }
            }

            // تحديث الحالة الإجمالية
            if ($contract->status === 'rejected') {
                $contract->status = 'pending';
            }
            $contract->save();

            // تسجيل عملية إعادة الاعتماد في سجل الاعتمادات
            ContractApprovalLog::create([
                'contract_id' => $contract->id,
                'employee_id' => auth()->user()->employee->id,
                'action'      => 'updated',
                'reason'      => 'تم تعديل العقد وإعادة تعيين حالة الاعتماد.',
            ]);
        }


        /*
            |--------------------------------------------------------------------------
            | ازالة الكاش بعد تعديل العقد
            |--------------------------------------------------------------------------
            */
        Cache::forget('new_contracts_count');
        // الجزئية الخاصة ب  rest  للرفض


        $filesService = app(FilesService::class);
        $graphService = app(MicrosoftGraphBaseService::class);
        $userIdInSystem = Auth::user()->id;
        $user = Auth::user();
        $accessToken = $graphService->getValidUserAccessToken($user);
        $userId = $filesService->getUserIdByEmail($accessToken, $user->email);
        $folderId = $filesService->getOrCreateFolder($accessToken, $userId, 'root', 'العقود');

        // حذف المرفقات المحددة
        if ($request->has('delete_attachments')) {
            foreach ($validatedData['delete_attachments'] as $attachmentId) {
                $attachment = ContractAttachment::findOrFail($attachmentId);
                if (!$filesService->fileExists($accessToken, $userId, $attachment->file_id)) {
                    $attachment->delete(); // حذف السجل من قاعدة البيانات حتى لو الملف غير موجود
                    continue;
                }
                // إرسال مهمة حذف الملف إلى الطابور
                DeleteFileFromOneDriveJob::dispatch(
                    $userIdInSystem,
                    $userId,
                    $attachment->file_id,
                    $attachment->id
                )->onConnection('database');
            }
        }

        // تحديث أسماء المرفقات الحالية
        if ($request->has('existing_attachment_names')) {
            foreach ($validatedData['existing_attachment_names'] as $attachmentId => $name) {
                $attachment = ContractAttachment::findOrFail($attachmentId);
                $oldName = $attachment->name;

                // استخراج الامتداد
                $fileExtension = pathinfo($oldName, PATHINFO_EXTENSION);
                $newFileName = $name;
                if (pathinfo($newFileName, PATHINFO_EXTENSION) === '') {
                    $newFileName .= '.' . $fileExtension;
                }

                // إرسال مهمة إعادة تسمية الملف إلى الطابور
                RenameFileOnOneDriveJob::dispatch(
                    $userIdInSystem,
                    $userId,
                    $attachment->file_id,
                    $newFileName,
                    $attachment->id
                )->onConnection('database');
            }
        }

        // إضافة مرفقات جديدة
        if ($request->has('attachment_names') && $request->hasFile('attachment_files')) {
            foreach ($validatedData['attachment_names'] as $index => $name) {
                $file = $request->file('attachment_files')[$index];
                $fileExtension = $file->getClientOriginalExtension();
                $fileNameWithExtension = $name . '.' . $fileExtension;

                // حفظ الملف في مسار دائم
                $storedFilePath = $file->store('uploads/attachments');
                $fullFilePath = storage_path('app/' . $storedFilePath);

                // حفظ معلومات المرفق في قاعدة البيانات بدون file_id و file_url
                $attachment = ContractAttachment::create([
                    'contract_id' => $contract->id,
                    'name' => $fileNameWithExtension,
                ]);
                // إرسال مهمة رفع الملف إلى الطابور
                UploadFileToOneDriveJob::dispatch(
                    $userIdInSystem,
                    $userId,
                    $folderId,
                    $fullFilePath,
                    $fileNameWithExtension,
                    $attachment->id
                )->onConnection('database');
            }
        }
        return redirect()->route('contracts.index')->with('success', 'تم تحديث العقد بنجاح!');
    } // end of update



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // البحث عن العقد باستخدام المعرف وحذفه
        $contract = Contract::findOrFail($id);
        $hasProject = $contract->projects()->exists();
        $hasSupplementaryContracts = $contract->supplementaryContracts()->exists();


        // إذا كان لديه أي من هذه العلاقات
        if ($hasProject || $hasSupplementaryContracts) {
            return redirect()->route('contracts.index')->with('error', 'لا يمكن حذف العقد لارتباطه بسجلات أخرى.');
        }
        $contract->delete();

        /*
            |--------------------------------------------------------------------------
            | ازالة الكاش بعد حذف العقد
            |--------------------------------------------------------------------------
            */
        Cache::forget('new_contracts_count');


        return redirect()->route('contracts.index')->with('success', 'تم حذف العقد بنجاح!');
    } //end of destroy



    public function trashed(Request $request)
    {
        try {
            if ($request->ajax()) {
                $contracts = Contract::onlyTrashed()
                    ->with(['customer', 'contractManager', 'offer'])
                    ->select([
                        'id',
                        'contract_name',
                        'contract_number',
                        'customer_id',
                        'contract_start_date',
                        'expected_closure_date',
                        'contract_manager_id',
                        'offer_id',
                        'deleted_at',
                    ])->orderBy('id', 'desc');

                return datatables()->of($contracts)
                    ->addIndexColumn()
                    ->addColumn('customer_name', function ($row) {
                        return $row->customer ? $row->customer->name : 'غير متوفر';
                    })
                    ->addColumn('employee_name', function ($row) {
                        return $row->contractManager ? $row->contractManager->name : 'غير متوفر';
                    })
                    ->addColumn('offer_name', function ($row) {
                        return $row->offer ? $row->offer->offer_name : 'غير متوفر';
                    })
                    ->editColumn('deleted_at', function ($row) {
                        return $row->deleted_at ? Hijri::ShortDate($row->deleted_at) : '';
                    })
                    ->addColumn('action', function ($row) {
                        $restoreUrl = route('contracts.restore', $row->id);
                        $forceDeleteUrl = route('contracts.forceDelete', $row->id);
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
                    ->rawColumns(['action'])
                    ->make(true);
            }

            return view('judicial_affairs.contracts.trashed');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    }



    public function restore($id)
    {
        try {
            $contract = Contract::onlyTrashed()->findOrFail($id);
            $contract->restore();
            return redirect()->route('contracts.trashed')->with('success', 'تم استعادة العقد بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء استعادة العقد. يرجى المحاولة لاحقاً.');
        }
    }


    public function forceDelete($id)
    {
        $contract = Contract::onlyTrashed()->findOrFail($id);
        $graphService = app(MicrosoftGraphBaseService::class);
        $filesService = app(FilesService::class);
        $user = Auth::user();
        $userIdInSystem = $user->id;

        $accessToken = $graphService->getValidUserAccessToken($user);
        if (!$accessToken) {
            return redirect()->back()->with('error', 'فشل في الحصول على رمز الوصول.')->withInput();
        }

        $userId = $filesService->getUserIdByEmail($accessToken, $user->email);
        if (!$userId) {
            return redirect()->back()->with('error', 'فشل في الحصول على معرف المستخدم.')->withInput();
        }
        // حذف المرفقات المرتبطة
        foreach ($contract->attachments as $attachment) {
            // إرسال مهمة حذف الملف إلى الطابور
            DeleteFileFromOneDriveJob::dispatch(
                $userIdInSystem,
                $userId,
                $attachment->file_id,
                $attachment->id
            )->onConnection('database');
        }

        $tasks = Task::where('contract_id', $contract->id)->get();

        $this->deleteTask($tasks, $contract);


        return redirect()->route('contracts.trashed')->with('success', 'تم حذف العقد نهائيًا بنجاح، وجاري حذف المرفقات  !');
    }


    /*
    |--------------------------------------------------------------------------
    | لجلب العميل و مسؤول العلاقة حسب العرض الذي تم اختياره
    |--------------------------------------------------------------------------
    | Description of this section.
    */
    public function getOfferDetails($id)
    {
        $offer = Judicial_affairsOffers::find($id);
        if ($offer) {
            $customer = $offer->customer;
            $relationshipManager = $offer->relationshipManager;

            $technical_offer = $offer->technical_offer;
            $financial_offer = $offer->financial_offer;

            return response()->json([
                'customer' => $customer,
                'relationship_manager' => $relationshipManager,

                'technical_offer' => $technical_offer,
                'financial_offer' => $financial_offer,
            ]);
        }
        return response()->json(null, 404);
    }



    protected function convertToGregorian($value)
    {
        if ($this->isHijriDate($value)) {
            // استبدال أي فواصل مائلة `/` بشرطات `-` لتوحيد التنسيق
            $value = str_replace('/', '-', $value);
            // تقسيم التاريخ الهجري إلى سنة، شهر، ويوم
            list($year, $month, $day) = explode('-', $value);
            // تحويل التاريخ الهجري إلى ميلادي باستخدام المكتبة
            $gregorianDate = Hijri::DateToGregorianFromDMY($day, $month, $year);
            // تخزين التاريخ الميلادي في قاعدة البيانات بتنسيق 'Y-m-d'
            return Carbon::parse($gregorianDate)->format('Y-m-d');
        } else {
            // إذا كان التاريخ ميلاديًا بالفعل، قم بتخزينه مباشرة
            return Carbon::parse($value)->format('Y-m-d');
        }
    }



    protected function isHijriDate($date)
    {
        // التحقق مما إذا كان التاريخ الهجري بتنسيق YYYY-MM-DD أو YYYY/MM/DD
        if (preg_match('/^\d{4}[-\/]\d{2}[-\/]\d{2}$/', $date)) {
            $year = substr($date, 0, 4);
            // التحقق مما إذا كانت السنة هجريّة (على سبيل المثال، أكبر من 1300 وأقل من 1600)
            if ($year >= 1300 && $year <= 1600) {
                return true;
            }
        }
        return false;
    }


    // الدالة الخاصة بعرض العقود التب تحتاج الى اعتمادات
    public function approvals(Request $request)
    {
        $route = 'contracts';
        try {
            if ($request->ajax()) {
                $query = Contract::select([
                    'id',
                    'contract_name',
                    'contract_number',
                    'customer_id',
                    'contract_start_date',
                    'expected_closure_date',
                    'contract_manager_id',
                    'offer_id',
                ])->where('status', 'pending');

                // تطبيق الفلاتر إذا كانت موجودة
                if ($request->has('status') && $request->status != '') {
                    $query->where('contract_status_id', $request->status);
                }

                if ($request->has('customer') && $request->customer != '') {
                    $query->where('customer_id', $request->customer);
                }

                if ($request->has('employee') && $request->employee != '') {
                    $query->where('contract_manager_id', $request->employee);
                }

                $data = $query->select('contracts.*');

                return datatables()->of($data)
                    ->addIndexColumn()
                    ->addColumn('checkbox', function ($row) {
                        return '<input type="checkbox" class="row-checkbox" value="' . $row->id . '">';
                    })

                    ->addColumn('contract_name', function ($row) {
                        return '<a href="' . route('contracts.show', $row->id) . '">
                                ' . e($row->contract_name) . '
                            </a>';
                    })
                    ->addColumn('status', function ($row) {
                        $status = '';
                        $tooltip = '';

                        switch ($row->status) {
                            case 'approved':
                                $status = 'معتمد';
                                $tooltip = 'تم اعتماد العقد بالكامل.';
                                break;

                            case 'pending':
                                $status = 'قيد الانتظار';
                                $nextApprover = $this->getNextApprover($row->id);
                                $tooltip = $nextApprover ? 'الاعتماد القادم لدى: ' . $nextApprover : 'لا يوجد معلومات عن المعتمد التالي.';
                                break;

                            case 'rejected':
                                $status = 'مرفوض';
                                $tooltip = 'تم رفض العقد.';
                                break;

                            default:
                                $status = 'غير معروف';
                                $tooltip = 'حالة العقد غير معروفة.';
                                break;
                        }

                        return '<span style="cursor: pointer" title="' . e($tooltip) . '">' . $status . '</span>';
                    })


                    ->addColumn('contract_manager_id', function ($row) {
                        return $row->contractManager ? $row->contractManager->name : 'غير متوفر';
                    })
                    ->addColumn('customer_id', function ($row) {

                        return $row->customer ? $row->customer->name : 'غير متوفر';
                    })
                    ->addColumn('offer_id', function ($row) {
                        return $row->offer ? $row->offer->offer_name : 'غير متوفر';
                    })
                    ->editColumn('created_at', function ($row) {
                        // تنسيق تاريخ الإنشاء إذا كان متوفراً
                        return $row->created_at ? $row->created_at->format('d/m/Y') : '';
                    })

                    // الترتيب
                    ->orderColumn('customer_id', function ($query, $order) {
                        $query->orderBy('customer_id', $order);
                    })
                    ->orderColumn('contract_manager_id', function ($query, $order) {
                        $query->orderBy('contract_manager_id', $order);
                    })
                    ->orderColumn('offer_id', function ($query, $order) {
                        $query->orderBy('offer_id', $order);
                    })

                    // filtters
                    ->filterColumn('customer_id', function ($query, $keyword) {
                        // تمكين البحث بناءً على اسم العميل
                        $query->whereHas('customer', function ($q) use ($keyword) {
                            $q->where('name', 'like', "%{$keyword}%");
                        });
                    })
                    ->filterColumn('contract_manager_id', function ($query, $keyword) {
                        // تمكين البحث بناءً على اسم مسؤول العقد
                        $query->whereHas('contractManager', function ($q) use ($keyword) {
                            $q->where('name', 'like', "%{$keyword}%");
                        });
                    })
                    ->filterColumn('offer_id', function ($query, $keyword) {
                        // تمكين البحث بناءً على اسم مرحلة العرض
                        $query->whereHas('offer', function ($q) use ($keyword) {
                            $q->where('offer_name', 'like', "%{$keyword}%");
                        });
                    })
                    ->rawColumns(['action', 'status', 'contract_name'])
                    ->make(true);
            }
            // إحصائيات العقود
            $totalContracts = Contract::count();
            $newContracts = Contract::where('contract_status_id', 1)->count();

            $lateContracts = Contract::where('contract_status_id', 4)->count();

            $closedContracts = Contract::where('contract_status_id', 5)->count();

            return view('judicial_affairs.contracts.approvals', compact('route', 'totalContracts', 'newContracts', 'lateContracts', 'closedContracts'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    } // end of index

    // الدالة التي تعرض العقود التي تحتاج الى اعتماد
    public function approve($id, $field)
    {

        try {
            $contract = Contract::findOrFail($id);
            $approvalMappings = [
                'approver1_approved' => 'approver1_id',
                'approver2_approved' => 'approver2_id',
                'approver3_approved' => 'approver3_id',
            ];

            $signingFields = [
                'approver1_approved' => 'approver1_id_signed',
                'approver2_approved' => 'approver2_id_signed',
                'approver3_approved' => 'approver3_id_signed',
            ];

            if (!array_key_exists($field, $approvalMappings)) {
                return response()->json(['error' => 'حقل اعتماد غير صالح.'], 400);
            }

            $itemForContract = Item::where('type', 'contract')->first();
            if (!$itemForContract) {
                return response()->json(['error' => 'لا توجد بيانات اعتماد متاحة لهذا العقد.'], 400);
            }

            $approverIdField = $approvalMappings[$field];
            $assignedEmployeeId = $itemForContract->$approverIdField;
            $currentUserEmployeeId = Auth::user()->employee->id ?? null;

            if ($assignedEmployeeId != $currentUserEmployeeId && !Auth::user()->hasRole('Admin')) {
                return response()->json(['error' => 'ليس لديك الصلاحية لاعتماد هذه الخطوة.'], 403);
            }

            // التحقق من أن التوقيع الخاص بالمستخدم موجود
            $currentUser = \App\Models\hr\employees\Employees::find($currentUserEmployeeId);
            if (!$currentUser || !$currentUser->signature) {
                return response()->json(['error' => 'لا يمكن اعتماد العقد. يرجى إضافة توقيعك أولاً.'], 400);
            }

            if ($contract->$field !== null) {
                return response()->json(['error' => 'هذه المرحلة قد تم التعامل معها بالفعل.'], 400);
            }

            $steps = array_keys($approvalMappings);
            $currentStepIndex = array_search($field, $steps);
            if ($currentStepIndex > 0) {
                $previousStep = $steps[$currentStepIndex - 1];
                if ($contract->$previousStep !== 'approved') {
                    return response()->json(['error' => 'لا يمكن اعتماد هذه الخطوة قبل اعتماد الخطوة السابقة.'], 400);
                }
            }

            $contract->$field = 'approved';
            $contract->{$signingFields[$field]} = $currentUserEmployeeId;
            $contract->save();

            Cache::forget('new_contracts_count');

            ContractApprovalLog::create([
                'contract_id' => $contract->id,
                'employee_id' => $currentUserEmployeeId,
                'action'      => 'approved',
                'reason'      => null,
            ]);

            $nextStep = $steps[$currentStepIndex + 1] ?? null;

            if ($nextStep) {
                $nextApproverId = $approvalMappings[$nextStep];
                if ($itemForContract->$nextApproverId) {
                    // اضافة المهمة للمتعمد الاول
                    $newTask = [
                        'task_name' => 'لديك عقد جديد باسم (' . $contract->contract_name . ') يحتاج إلى إعتمادك',
                        'priority' => 'high',
                        'description' => 'تم اعتماد العقد بواسطة المعتمد السابق ' . Auth::user()->name . ' والآن العقد في انتظار اعتمادك.',
                        'task_field' => 'contracts',
                        'contract_id' => $contract->id,
                        'assigned_user_id' => $itemForContract->$nextApproverId,
                        'created_by_user_id' => auth()->id(),
                    ];

                    // ارسال اشعار للمستخدم فقط إذا كان approver1_id موجودًا
                    log::info($itemForContract->$nextApproverId);
                    CreateTaskJob::dispatch($newTask, $itemForContract->$nextApproverId);

                    //
                }
            }

            $contract->updateContractStatus();

            return response()->json(['success' => 'تم اعتماد الخطوة بنجاح.']);
        } catch (\Exception $e) {
            Log::error('Error in approve method: ' . $e->getMessage());
            return response()->json(['error' => 'حدث خطأ أثناء تنفيذ الإجراء.'], 500);
        }
    }

    public function reject($id, $field, Request $request)
    {
        try {
            $contract = Contract::findOrFail($id);
            $approvalMappings = [
                'approver1_approved' => 'approver1_id',
                'approver2_approved' => 'approver2_id',
                'approver3_approved' => 'approver3_id',
            ];

            if (!array_key_exists($field, $approvalMappings)) {
                return response()->json(['error' => 'حقل اعتماد غير صالح.'], 400);
            }

            $itemForContract = Item::where('type', 'contract')->first();
            if (!$itemForContract) {
                return response()->json(['error' => 'لا توجد بيانات اعتماد متاحة لهذا العقد.'], 400);
            }

            $approverIdField = $approvalMappings[$field];
            $assignedEmployeeId = $itemForContract->$approverIdField;
            $currentUserEmployeeId = Auth::user()->employee->id ?? null;

            if ($assignedEmployeeId != $currentUserEmployeeId && !Auth::user()->hasRole('Admin')) {
                return response()->json(['error' => 'ليس لديك الصلاحية لرفض هذه الخطوة.'], 403);
            }

            if ($contract->$field !== null) {
                return response()->json(['error' => 'هذه المرحلة قد تم التعامل معها بالفعل.'], 400);
            }

            $contract->$field = 'rejected';
            $contract->save();

            Cache::forget('new_contracts_count');

            ContractApprovalLog::create([
                'contract_id' => $contract->id,
                'employee_id' => $currentUserEmployeeId,
                'action'      => 'rejected',
                'reason'      => $request->rejection_reason,
            ]);

            $contract->updateContractStatus();

            return response()->json(['success' => 'تم رفض الخطوة بنجاح.']);
        } catch (\Exception $e) {
            Log::error('Error in reject method: ' . $e->getMessage());
            return response()->json(['error' => 'حدث خطأ أثناء تنفيذ الإجراء.'], 500);
        }
    }


    public function revoke($id, $field)
    {
        try {
            $contract = Contract::findOrFail($id);
            $approvalMappings = [
                'approver1_approved' => 'approver1_id',
                'approver2_approved' => 'approver2_id',
                'approver3_approved' => 'approver3_id',
            ];

            if (!array_key_exists($field, $approvalMappings)) {
                return response()->json(['error' => 'حقل اعتماد غير صالح.'], 400);
            }

            $itemForContract = Item::where('type', 'contract')->first();
            if (!$itemForContract) {
                return response()->json(['error' => 'لا توجد بيانات اعتماد متاحة لهذا العقد.'], 400);
            }

            $approverIdField = $approvalMappings[$field];
            $assignedEmployeeId = $itemForContract->$approverIdField;
            $currentUserEmployeeId = Auth::user()->employee->id ?? null;

            // التحقق من الصلاحيات
            if ($assignedEmployeeId != $currentUserEmployeeId && !Auth::user()->hasRole('Admin')) {
                return response()->json(['error' => 'ليس لديك الصلاحية لإلغاء هذه الخطوة.'], 403);
            }

            // التحقق من أن الحقل معتمد حاليًا
            if ($contract->$field !== 'approved') {
                return response()->json(['error' => 'هذه المرحلة ليست معتمدة حاليًا.'], 400);
            }

            // إلغاء الاعتماد
            $contract->$field = null;
            $contract->save();

            /*
            |--------------------------------------------------------------------------
            | ازالة الكاش بعد إلغاء اعتماد العقد
            |--------------------------------------------------------------------------
            */
            Cache::forget('new_contracts_count');

            // تسجيل الإجراء في السجل
            ContractApprovalLog::create([
                'contract_id'    => $contract->id,
                'employee_id'    => $currentUserEmployeeId,
                'action'         => 'revoked',
                'reason'         => null, // إذا كانت الأسباب محذوفة، اجعلها دائمًا null
            ]);

            // تحديث حالة العقد
            $contract->updateContractStatus();

            return response()->json(['success' => 'تم إلغاء الاعتماد بنجاح.']);
        } catch (\Exception $e) {
            Log::error('Error in revoke method: ' . $e->getMessage());
            return response()->json(['error' => 'حدث خطأ أثناء تنفيذ الإجراء.'], 500);
        }
    }





    // الدالة الخاصة بتحديدالمعتمد القادم للعقد
    private function getNextApprover($contractId)
    {
        // جلب العقد
        $contract = Contract::find($contractId);

        if (!$contract) {
            return null;
        }

        // جلب بيانات الاعتماد من جدول Item
        $itemForContract = Item::where('type', 'contract')->first();

        if (!$itemForContract) {
            return null;
        }

        // تحقق من حالة المعتمدين بالتسلسل
        $approvalsConfig = [
            'approver1_approved' => $itemForContract->approver1_id,
            'approver2_approved' => $itemForContract->approver2_id,
            'approver3_approved' => $itemForContract->approver3_id,
        ];

        foreach ($approvalsConfig as $approvalField => $approverId) {
            if ($contract->$approvalField === null && $approverId) {
                $approver = Employees::find($approverId);
                return $approver ? $approver->name : 'غير متوفر';
            }
        }

        return null; // إذا لم يكن هناك معتمد قيد الانتظار
    }

    /*
|--------------------------------------------------------------------------
| export contract to pdf
|--------------------------------------------------------------------------
| Export contract to pdf
*/

    public function exportToPDF($id)
    {
        try {
            $contract = Contract::findOrFail($id);
            $template = SettingsTemplate::where('template_type', 'contracts')->first();

            $data = [
                'customer_name' => $contract->customer->name,
                'civil_registry' => $contract->customer->civil_registry,
                'address' => $contract->customer->region->name ?? '-',
                'contactNumber' => $contract->customer->contactNumber,
                'email' => $contract->customer->email,
                'contract_name' => $contract->contract_name,
                'contract_number' => $contract->contract_number,
                'expected_closure_date' => $contract->expected_closure_date,
                'contract_start_date' => $contract->contract_start_date,
                'contract_end_date' => $contract->contract_end_date,
                'technical_offer' => $contract->offer->technical_offer,
                'financial_offer' => $contract->offer->financial_offer,
                'current_date' => Carbon::now(),
            ];

            $processedContent = $this->replaceVariables($template->content, $data);
            $processedContent = html_entity_decode($processedContent);
            $processedContent = str_replace('   ', '&nbsp;&nbsp;&nbsp;', $processedContent);

            $seal = \App\Models\Settings::first()->signature ?? null;
            $sealPath = $seal ? storage_path('app/public/' . $seal) : null;
            $headerPath = storage_path('app/public/' . SettingsHelper::get('horizontal_header_image'));
            $footerPath = storage_path('app/public/' . SettingsHelper::get('horizontal_footer_image'));

            $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
            $fontDirs = $defaultConfig['fontDir'];

            $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
            $fontData = $defaultFontConfig['fontdata'];

            $config = [
                'mode' => 'utf-8',
                'format' => 'A4',
                'default_font' => 'almarai',
                'margin_left' => 0,
                'margin_right' => 0,
                'margin_top' => 30,
                'margin_bottom' => 35,
                'margin_header' => 0,
                'margin_footer' => 0,
                'orientation' => 'P',
                'fontDir' => array_merge($fontDirs, [
                    public_path('fonts/Almarai'),
                ]),
                'fontdata' => array_merge($fontData, [
                    'almarai' => [
                        'R' => 'Almarai-Regular.ttf',
                        'B' => 'Almarai-Bold.ttf',
                        'L' => 'Almarai-Light.ttf',
                        'useOTL' => 0xFF,
                        'useKashida' => 75,
                    ]
                ]),
                'default_font_size' => 12,
                'tempDir' => storage_path('app/public/temp')
            ];

            $mpdf = new \Mpdf\Mpdf($config);
            $mpdf->SetDirectionality('rtl');

            $stylesheet = '
            body {
                font-family: almarai;
                font-size: 12px;
                line-height: 1.5;
                direction: rtl;
            }
            .page-header {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                height: 60px;
            }
            .footer-container {
                width: 100%;
                padding: 0;
                margin: 0;
                position: fixed;
                bottom: 0;
            }
            .footer-container img {
                width: 100%;
                height: 80px;
                object-fit: cover;
            }
            .content {
                margin-top: 70px;
                margin-bottom: 70px;
                padding: 0;
                margin-left: 40px !important;
                margin-right: 40px !important;
            }
            .reference-number {
                text-align: left;
                margin-bottom: 20px;
                margin-left: 20px;
                margin-right: 20px;
                font-size: 12px;
            }
            .ql-align-right {
                text-align: right;
            }
            .ql-align-center {
                text-align: center;
            }
            .ql-align-justify {
                text-align: justify;
            }
            .ql-direction-rtl {
                direction: rtl;
            }
        ';

            $mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);

            if (file_exists($headerPath)) {
                $headerContent = '
                    <div style="width: 100%; text-align: right; padding: 25px 20px 35px 5px;">
                        <img src="' . $headerPath . '" style="height: 90px; max-width: 250px;" />
                    </div>';
                $mpdf->SetHTMLHeader($headerContent);
            }
            if (file_exists($footerPath)) {
                $footerContent = '
                <div class="footer-container">
                    <img src="' . $footerPath . '" />
                </div>';
                $mpdf->SetHTMLFooter($footerContent);
            }

            $content = '
            <div class="reference-number">
                الرقم المرجعي للعقد (' . $contract->contract_number . ')
            </div>
            <div class="content">
                ' . $processedContent . '
            </div>';


            if ($contract->status === 'approved' && $sealPath && file_exists($sealPath)) {
                $content .= '
                <div style="text-align: left; margin-top: 20px !important; margin-left: 30px; !important">
                    <img src="' . $sealPath . '" style="max-width: 150px; opacity: 0.8;" />
                </div>';
            }

            $mpdf->WriteHTML($content);

            return response()->streamDownload(
                function () use ($mpdf) {
                    echo $mpdf->Output('', 'S');
                },
                'عقد_' . $contract->contract_name . '.pdf',
                ['Content-Type' => 'application/pdf']
            );
        } catch (\Exception $e) {
            Log::error('Error generating PDF: ' . $e->getMessage());
            return redirect()->back()->with('error', 'حدث خطأ أثناء تصدير العقد. يرجى المحاولة لاحقاً.');
        }
    }
}
