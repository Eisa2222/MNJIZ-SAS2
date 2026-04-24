<?php

namespace App\Http\Controllers\Hr\Employees;

use Alkoumi\LaravelHijriDate\Hijri;
use App\DataTables\Hr\Employee\EmployeesDataTable;
use App\Enums\Hr\Employee\ContractType;
use App\Enums\Hr\Employee\InsuranceStatus;
use App\Enums\Hr\Employee\KnowledgeArea;
use App\Enums\Hr\Employee\LicenseType;
use App\Enums\Hr\Employee\QualificationDegree;
use App\Enums\Hr\Employee\TrialPeriod;
use App\Helpers\General;
use App\Helpers\SettingsHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Hr\Employee\StoreEmployeeRequest;
use App\Http\Requests\Hr\Employee\UpdateEmployeeRequest;
use App\Models\general_setting\SettingsBanks;
use App\Models\general_setting\SettingsCountry;
use App\Models\general_setting\SettingsHRClassification;
use App\Models\general_setting\SettingsHrStatus;
use App\Models\general_setting\SettingsLeaveType;
use App\Models\Hr\Employees\Employees;
use App\Models\Hr\Employees\EmployeeSalaryHistory;
use App\Models\Hr\LeaveBalance;
use App\Models\judicial_affairs\Project;
use App\Models\MessageLog;
use App\Models\GeneralSetting\SystemSetting\Settings;
use App\Models\Task\Task;
use App\Models\User;
use App\Services\EmailService;
use App\Services\HR\Employee\EmployeeService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class EmployeesController extends Controller
{
    private $route = "hr.employees";
    private $page = "hr.employees";

    public function __construct(private EmployeeService $employeeService)
    {
        $this->middleware(function ($request, $next) {
            if ($request->user()->can('كل الموظفين')) {
                return $next($request);
            }
            abort(403);
        })->only(['index', 'show']);

        $this->middleware('can:إضافة موظف')->only(['create', 'store']);
        $this->middleware('can:تعديل موظف')->only(['edit', 'update']);
        $this->middleware('can:حذف موظف')->only(['destroy']);
    }


    /*
    |============================================================================
    | Index
    |============================================================================
    */
    public function index(EmployeesDataTable $dataTable, Request $request)
    {
        try {
            if ($request->ajax()) return $dataTable->ajax();

            // Statistics
            $statusCounts = Employees::with('user.roles')->whereHas('user', function ($query) {
                $query->where('status', 'active');
            })->select('hr_status_id')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('hr_status_id')
                ->pluck('count', 'hr_status_id')
                ->toArray();


            $totalEmployees                 =  array_sum($statusCounts);
            $unavailableEmployees           = $statusCounts[4] ?? 0;
            $availableEmployees             = $statusCounts[3] ?? 0;
            $partiallyAvailableEmployees    = $statusCounts[2] ?? 0;
            $currentEmployees               = $statusCounts[1] ?? 0;

            // Filters
            $hrStatus   = SettingsHrStatus::select('id', 'name')->get();
            $roles      = Role::select('id', 'name')->get();

            return $dataTable->render($this->page . '.index', compact(
                'totalEmployees',
                'unavailableEmployees',
                'availableEmployees',
                'partiallyAvailableEmployees',
                'currentEmployees',
                //
                'hrStatus',
                'roles',
            ));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    }



    /*
    |--------------------------------------------------------------------------
    | crate function
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $statuses   = SettingsHrStatus::where('status', 'active')->select(['id', 'name'])->orderBy('id', 'desc')->get();
        $countries  = SettingsCountry::where('status', 'active')->select(['id', 'name'])->orderBy('id', 'asc')->get();
        $roles      = Role::all();
        $banks      = SettingsBanks::where('status', 'active')->select(['id', 'name'])->orderBy('id', 'desc')->get();

        $contractType        = ContractType::options();
        $licenseType         = LicenseType::options();
        $trialPeriod         = TrialPeriod::options();
        $qualificationDegree = QualificationDegree::options();
        $knowledgeArea       = KnowledgeArea::options();
        $insuranceStatus     = InsuranceStatus::options();

        $lastNationalNumber = Employees::max('national_number');
        $nextNationalNumber = $lastNationalNumber ? $lastNationalNumber + 1 : 1;

        return view('hr.employees.create', compact(
            'statuses',
            'countries',
            'roles',
            'banks',
            'contractType',
            'licenseType',
            'trialPeriod',
            'qualificationDegree',
            'knowledgeArea',
            'insuranceStatus',
            'nextNationalNumber'
        ));
    }


    /*
    |--------------------------------------------------------------------------
    | store function
    |--------------------------------------------------------------------------
    */
    public function store(StoreEmployeeRequest $request)
    {
        $data = $request->validated();

        $folderName = 'employees/' . Str::slug($data['name'], '_');

        // حقول المرفقات
        $fileFields = [
            'profile_picture'             => 'profile_pictures',
            'resume'                      => 'resumes',
            'qualification_certificate'   => 'qualification_certificates',
            'contract_attachment'         => 'contract_attachments',
            'id_attachment'               => 'id_attachments',
            'bank_account_attachment'     => 'bank_account_attachments',
            'national_address_attachment' => 'national_address_attachments',
            'signature'                   => 'signatures',
        ];


        DB::beginTransaction();

        try {

            // معالجة المرفقات
            foreach ($fileFields as $field => $subdir) {
                if ($request->hasFile($field)) {
                    $data[$field] = $request->file($field)
                        ->store("{$folderName}/{$subdir}", 'public');
                }
            }

            // رفع المرفقات الإضافية
            if ($request->has('additional_attachments')) {
                $additional = [];
                foreach ($request->additional_attachments as $attachment) {
                    if (isset($attachment['file'], $attachment['name'])) {
                        $path = $attachment['file']
                            ->store("{$folderName}/additional_attachments", 'public');
                        $additional[] = [
                            'name' => $attachment['name'],
                            'file' => $path,
                        ];
                    }
                }
                $data['additional_attachments'] = $additional;
            }


            // إنشاء المستخدم
            $randomPassword = Str::random(12);
            $user = User::create([
                'name'                 => $data['name'],
                'email'                => $data['work_email'],
                'password'             => Hash::make($randomPassword),
                'must_change_password' => false,
                'phone'                => $data['mobile'] ?? null,
                'status'               => 'active',
                'nationality'          => $data['nationality'],
                'image'                => $data['profile_picture'] ?? null,
            ]);


            $roles = Role::whereIn('id', (array)$data['roles'])->get();
            $rolesNames = $roles->pluck('name')->join(', ');
            $user->assignRole($roles);
            $user->update(['job' => $rolesNames]);

            $lastNationalNumber = Employees::max('national_number');
            $nextNationalNumber = $lastNationalNumber ? $lastNationalNumber + 1 : 1;



            // إنشاء الموظف
            $data['user_id']         = $user->id;
            $data['job_title']       = $rolesNames;
            $data['national_number'] = $nextNationalNumber;

            // نسبة التامينات من الاعدادات اذا كان مشمول في التامينات
            if ($request->input('insurance_status') == "added") {
                $data['insurance_percentage'] = SettingsHelper::get('insurance_percentage');
            }

            $employee = Employees::create($data);

            // اضافة سجل الرواتب
            $this->recordSalaryHistory($employee);


            // إرسال إشعار كلمة المرور أول مرة
            $settings = Settings::current();
            if ($settings && $settings->main_email) {
                $this->sendPasswordResetFirstNotification($user->id);
                $message = 'تم إضافة الموظف وإرسال إشعار له بنجاح.';
            } else {
                $message = 'تم إضافة الموظف بنجاح، لكن لم يتم إرسال الإشعار (تحقق من إعدادات مايكروسوفت).';
            }

            DB::commit();
            return redirect()->route('hr.employees.index')->with('success', $message);
        } catch (\Throwable $e) {
            Log::error('Employee store error: ' . $e->getMessage());
            DB::rollBack();
            return back()->withInput()->withErrors(['general' => 'حدث خطأ أثناء إضافة الموظف. حاول مرة أخرى.']);
        }
    }





    /*
    |--------------------------------------------------------------------------
    | edit function
    |--------------------------------------------------------------------------
    */
    public function edit($id)
    {
        $employee   = Employees::findOrFail($id);
        $statuses   = SettingsHrStatus::where('status', 'active')->select(['id', 'name'])->orderBy('id', 'desc')->get();
        $countries  = SettingsCountry::where('status', 'active')->select(['id', 'name'])->orderBy('id', 'asc')->get();
        $roles      = Role::all();
        $user       = User::findOrFail($employee->user_id);
        $banks      = SettingsBanks::where('status', 'active')->select(['id', 'name'])->orderBy('id', 'desc')->get();

        $contractType        = ContractType::options();
        $licenseType         = LicenseType::options();
        $trialPeriod         = TrialPeriod::options();
        $qualificationDegree = QualificationDegree::options();
        $knowledgeArea       = KnowledgeArea::options();
        $insuranceStatus     = InsuranceStatus::options();


        return view('hr.employees.edit', compact(
            'employee',
            'statuses',
            'countries',
            'roles',
            'user',
            'banks',
            'contractType',
            'licenseType',
            'trialPeriod',
            'qualificationDegree',
            'knowledgeArea',
            'insuranceStatus'
        ));
    }



    /*
    |--------------------------------------------------------------------------
    | update function
    |--------------------------------------------------------------------------
    */
    public function update(UpdateEmployeeRequest $request,  Employees $employee)
    {
        $data = $request->validated();

        // لتخزين قيم المرتب و البدلات
        $old = $employee->only([
            'basic_salary',
            'transportation_allowance',
            'housing_allowance',
            'other_allowances',
        ]);


        $folderName = 'employees/' . Str::slug($data['name'] ?? $employee->name, '_');

        $fileFields = [
            'profile_picture'             => 'profile_pictures',
            'resume'                      => 'resumes',
            'qualification_certificate'   => 'qualification_certificates',
            'contract_attachment'         => 'contract_attachments',
            'id_attachment'               => 'id_attachments',
            'bank_account_attachment'     => 'bank_account_attachments',
            'national_address_attachment' => 'national_address_attachments',
            'signature'                   => 'signatures',
        ];

        DB::beginTransaction();

        try {
            // رفع/تحديث الملفات الأساسية
            foreach ($fileFields as $field => $subdir) {
                if ($request->hasFile($field)) {
                    $data[$field] = $request->file($field)
                        ->store("{$folderName}/{$subdir}", 'public');
                }
            }


            // معالجة المرفقات الإضافية
            if ($request->has('additional_attachments') || $request->has('deleted_attachments')) {
                $deletedAttachments = $request->input('deleted_attachments') ?
                    explode(',', $request->input('deleted_attachments')) : [];

                // ابدأ بنسخة جديدة من المرفقات الحالية
                $additionalAttachments = [];
                $currentAttachments = $employee->additional_attachments ?? [];

                // احتفظ بالمرفقات غير المحذوفة
                foreach ($currentAttachments as $index => $attachment) {
                    if (!in_array((string)$index, $deletedAttachments)) {
                        $additionalAttachments[$index] = $attachment;
                    } else {
                        // حذف الملف من التخزين إذا كان موجوداً
                        if (isset($attachment['file'])) {
                            Storage::disk('public')->delete($attachment['file']);
                        }
                    }
                }

                // أضف أو حدّث المرفقات الجديدة
                if ($request->has('additional_attachments')) {
                    foreach ($request->additional_attachments as $index => $attachment) {
                        // تخطي المرفقات المحذوفة
                        if (in_array((string)$index, $deletedAttachments)) {
                            continue;
                        }

                        if (isset($attachment['file']) && $attachment['file']) {
                            // إذا كان هناك ملف قديم، احذفه
                            if (isset($additionalAttachments[$index]['file'])) {
                                Storage::disk('public')->delete($additionalAttachments[$index]['file']);
                            }

                            // احفظ الملف الجديد
                            $employeeFolder = 'employees/' . str_replace(' ', '_', $employee->name);
                            $path = $attachment['file']->store($employeeFolder . '/additional_attachments', 'public');
                            $additionalAttachments[$index] = [
                                'name' => $attachment['name'],
                                'file' => $path,
                            ];
                        } elseif (isset($attachment['name'])) {
                            // حدّث الاسم فقط إذا كان المرفق موجوداً
                            if (isset($additionalAttachments[$index])) {
                                $additionalAttachments[$index]['name'] = $attachment['name'];
                            }
                        }
                    }
                }

                // إعادة ترتيب المصفوفة لتجنب الفجوات
                $data['additional_attachments'] = array_values($additionalAttachments);
            }

            // تحديث بيانات المستخدم المرتبط
            $user = $employee->user;

            if (!$user) {
                return redirect()->back()->withErrors(['user' => 'لم يتم العثور على المستخدم المرتبط بالموظف.'])->withInput();
            }

            $userPayload = [];
            if (isset($data['name']))             $userPayload['name']  = $data['name'];
            if (isset($data['work_email']))       $userPayload['email'] = $data['work_email'];
            if (isset($data['mobile']))           $userPayload['phone'] = $data['mobile'];
            if (isset($data['nationality']))      $userPayload['nationality'] = $data['nationality'];
            if (isset($data['profile_picture']))  $userPayload['image'] = $data['profile_picture'];

            if (!empty($userPayload)) {
                $user->update($userPayload);
            }

            // تحديث الأدوار إذا تم تمريرها
            if (isset($data['roles'])) {
                $roles = Role::where('id', $data['roles'])->get();
                $user->syncRoles($roles);
                $data['job_title'] = $roles->pluck('name')->join(', ');
                $user->update(['job' => $data['job_title']]);
            }

            // تأكد من تمرير user_id و job_title إلى بيانات الموظف
            $data['user_id'] = $user->id;
            if (!isset($data['job_title']) && isset($employee->job_title)) {
                $data['job_title'] = $employee->job_title;
            }

            if ($request->input('insurance_status') == "added") {
                $data['insurance_percentage'] = SettingsHelper::get('insurance_percentage');
            } else {
                $data['insurance_percentage'] = 0;
            }

            // تحديث سجل الموظف
            $employee->update($data);

            // التاكد هل تم تحديث المرتب لتحديثه في السجل
            if (
                ($data['basic_salary']              ?? $old['basic_salary']) != $old['basic_salary'] ||
                ($data['transportation_allowance']  ?? $old['transportation_allowance']) != $old['transportation_allowance'] ||
                ($data['housing_allowance']         ?? $old['housing_allowance']) != $old['housing_allowance'] ||
                ($data['other_allowances']          ?? $old['other_allowances']) != $old['other_allowances']
            ) {
                $this->recordSalaryHistory($employee);
            }

            DB::commit();
            return redirect()->route('hr.employees.index')->with('success', 'تم تحديث بيانات الموظف بنجاح.');
        } catch (\Throwable $e) {
            Log::error('Employee update error: ' . $e->getMessage());
            DB::rollBack();
            return back()->withInput()->withErrors(['general' => 'حدث خطأ أثناء تحديث بيانات الموظف. حاول مرة أخرى.']);
        }
    }



    /*
    |--------------------------------------------------------------------------
    | destroy function (Soft Delete)
    |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        $employee = Employees::findOrFail($id);

        // تحقق من وجود المستخدم المرتبط وقم بحذفه
        if ($employee->user) {
            $employee->user->delete();
        }
        // حذف سجل الموظف (استخدام Soft Delete)
        $employee->delete();

        return redirect()->route('hr.employees.index')->with('success', 'تم حذف الموظف  بنجاح.');
    }



    /*
    |--------------------------------------------------------------------------
    | trash function
    |--------------------------------------------------------------------------
    */
    public function trashed(Request $request)
    {
        try {
            if ($request->ajax()) {
                $query = Employees::onlyTrashed()->with('user')->select([
                    'id',
                    'name',
                    'nickname',
                    'job_title',
                    'work_email',
                    'mobile',
                    'deleted_at',
                    'user_id',
                ]);

                // تطبيق الفلاتر إذا كانت موجودة
                if ($request->status) {
                    $query->where('hr_status_id', $request->status);
                }

                if ($request->job_title) {
                    $query->where('job_title', $request->job_title);
                }

                if ($request->insurance_status) {
                    $query->where('insurance_status', $request->insurance_status);
                }

                return datatables()->of($query)
                    ->addIndexColumn()
                    ->addColumn('checkbox', function ($row) {
                        return '<input type="checkbox" class="row-checkbox" value="' . $row->id . '">';
                    })
                    ->editColumn('deleted_at', function ($row) {
                        return $row->deleted_at ? Hijri::ShortDate($row->deleted_at) : '';
                    })
                    ->editColumn('name', function ($row) {
                        $profileUrl = route('account.employee.profile', $row->id);
                        return $row->raw_name;
                    })
                    ->addColumn('action', function ($row) {
                        $restoreUrl = route('hr.employees.restore', $row->id);
                        $forceDeleteUrl = route('hr.employees.forceDelete', $row->id);
                        return '
                        <a href="javascript:void(0);" onclick="confirmRestore(' . $row->id . ')" class="btn btn-sm text-success" title="استعادة">
                            <i class="ti ti-rotate"></i> استعادة
                        </a>
                        <a href="javascript:void(0);" onclick="confirmForceDelete(' . $row->id . ')" class="btn btn-sm text-danger" title="حذف نهائي">
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
                    ->rawColumns(['action', 'name'])
                    ->make(true);
            }

            return view('hr.employees.trashed');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    }



    /*
    |--------------------------------------------------------------------------
    | restore function
    |--------------------------------------------------------------------------
    */
    public function restore($id)
    {
        $employee = Employees::withTrashed()->findOrFail($id);

        $employee->restore();

        return redirect()->route('hr.employees.index')->with('success', 'تم استعادة الموظف بنجاح.');
    }



    /*
    |--------------------------------------------------------------------------
    | forceDelete function
    |--------------------------------------------------------------------------
    */
    public function forceDelete($id)
    {
        try {
            $employee = Employees::onlyTrashed()->findOrFail($id);

            // حذف المستخدم المرتبط
            if ($employee->user) {
                // حذف المرفقات المرتبطة بالمستخدم إذا وجدت
                if ($employee->profile_picture && Storage::disk('public')->exists($employee->profile_picture)) {
                    Storage::disk('public')->delete($employee->profile_picture);
                }

                // حذف المستخدم نهائيًا
                $employee->user->forceDelete();
            }

            // حذف المرفقات الأخرى إذا وجدت
            if ($employee->resume && Storage::disk('public')->exists($employee->resume)) {
                Storage::disk('public')->delete($employee->resume);
            }

            // حذف سجل الموظف نهائيًا
            $employee->forceDelete();

            return redirect()->route('hr.employees.trashed')->with('success', 'تم حذف الموظف نهائيًا بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء حذف الموظف نهائيًا. يرجى المحاولة لاحقاً.');
        }
    }



    /*
    |--------------------------------------------------------------------------
    | reset password function
    |--------------------------------------------------------------------------
    */
    public function sendPasswordResetFirstNotification($userId)
    {
        $settings = Settings::current();

        if ($settings->main_email == "") {
            return response()->json([
                'success' => false,
                'message' => 'اعدادات مايكروسوفت غير مكتملة الرجاء مراجعة الاعدادات و المحاولة مرة اخرى'
            ], 400);
        }

        // إنشاء رمز إعادة تعيين كلمة المرور
        $user = User::find($userId);
        $token = Password::createToken($user);

        $emailService = app(EmailService::class);
        // تمرير كائن المستخدم والتوكن إلى خدمة البريد الإلكتروني
        $emailService->sendPasswordResetFirstNotification($user, $token, $settings);
    }



    /*
    |--------------------------------------------------------------------------
    | download attachment function
    |--------------------------------------------------------------------------
    */
    public function downloadAttachments($id)
    {
        try {
            // الحصول على اسم الموظف من قاعدة البيانات
            $employee = Employees::findOrFail($id);
            $folderName = str_replace(' ', '_', $employee->name);

            // تحديد المسار الكامل للمرفقات داخل storage/app/public
            $attachmentsPath = storage_path("app/public/employees/{$folderName}");
            $zipFilePath = storage_path("app/attachments_{$id}.zip");

            // تحقق مما إذا كان المجلد موجودًا
            if (!is_dir($attachmentsPath)) {
                return response()->json(['error' => 'Attachments folder does not exist'], 404);
            }

            // إنشاء ملف ZIP وضغط المرفقات
            $zip = new \ZipArchive();
            if ($zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                // إضافة الملفات إلى ZIP
                $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($attachmentsPath));
                foreach ($files as $file) {
                    // تخطي المجلدات
                    if (!$file->isDir()) {
                        $filePath = $file->getRealPath();
                        $relativePath = 'employees/' . $folderName . '/' . $file->getFilename();
                        $zip->addFile($filePath, $relativePath);
                    }
                }

                // إغلاق ملف ZIP بعد إضافة جميع الملفات
                $zip->close();
                // تحقق مما إذا كان ملف ZIP موجودًا بعد الإنشاء
                if (file_exists($zipFilePath)) {
                    return response()->download($zipFilePath)->deleteFileAfterSend(true);
                } else {
                    return response()->json(['error' => 'ZIP file does not exist after creation'], 500);
                }
            } else {
                return response()->json(['error' => 'Failed to create ZIP file'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while downloading the attachments'], 500);
        }
    }



    /*
    |--------------------------------------------------------------------------
    | send sms function
    |--------------------------------------------------------------------------
    */
    public function sendSms(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1600',
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'integer|exists:employees,id',
        ], [
            'message.required' => 'حقل الرسالة مطلوب.',
            'message.max' => 'حقل الرسالة لا يجب أن يتجاوز 1600 حرف.',
            'employee_ids.required' => 'يجب تحديد موظفين لإرسال الرسالة.',
            'employee_ids.array' => 'صيغة معرفات الموظفين غير صحيحة.',
            'employee_ids.*.exists' => 'الموظف المحدد غير موجود.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $message = $request->input('message');
        $employeeIds = $request->input('employee_ids');

        $employees = Employees::whereIn('id', $employeeIds)->get();
        $failedNumbers = [];
        $successfulRecipients = [];

        foreach ($employees as $employee) {
            if ($employee->mobile) {
                $formattedNumber = ltrim($employee->mobile, '0');
                $sent = General::sendSMS($message, $formattedNumber);

                if ($sent) {
                    $successfulRecipients[] = [
                        'type' => 'employee',
                        'id' => $employee->id,
                    ];
                } else {
                    $failedNumbers[] = $formattedNumber;
                }
            }
        }

        // تسجيل الرسالة في جدول message_logs
        MessageLog::create([
            'sender_id' => Auth::id(),
            'message_text' => $message,
            'platform' => 'SMS',
            'recipients' => $successfulRecipients,
        ]);

        if (empty($failedNumbers)) {
            return response()->json(['success' => 'تم إرسال الرسائل النصية بنجاح.']);
        } else {
            return response()->json(['error' => 'فشل إرسال الرسائل إلى بعض الأرقام. الرجاء مراجعة السجل.'], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | اضافة سجل الرواتب
    |--------------------------------------------------------------------------
    */
    private function recordSalaryHistory(Employees $employee): void
    {
        EmployeeSalaryHistory::create([
            'employee_id'              => $employee->id,
            'basic_salary'             => $employee->basic_salary            ?? 0,
            'transportation_allowance' => $employee->transportation_allowance ?? 0,
            'housing_allowance'        => $employee->housing_allowance       ?? 0,
            'other_allowances'         => $employee->other_allowances        ?? 0,
            'effective_from'           => Carbon::now(),
        ]);
    }


    /*
    |============================================================================
    |============================================================================
    |                          Archive Employee
    |============================================================================
    |============================================================================
    */
    public function archive($employeeId)
    {
        try {
            $this->employeeService->archive($employeeId);

            if (request()->expectsJson()) {
                return response()->json(['success' => 'تم نقل الموظف إلى الأرشيف'], 200);
            }
        } catch (\Throwable $e) {
            $msg = $e->getMessage() ?: 'حدث خطأ ما';
            return request()->expectsJson()
                ? response()->json(['error' => $msg], 500)
                : back()->withInput()->with('error', $msg);
        }
    }


    public function showBusinessCard($employeeId)
    {
        $employee = Employees::findOrFail($employeeId);



        $settings = Settings::current();

        $profileUrl = route('public-profile', $employee->id);

        $qrCode = QrCode::format('svg')
            ->size(200)
            ->margin(1)
            ->errorCorrection('H')
            ->generate($profileUrl);


        return view('hr.employees.business-card', compact('employee', 'settings', 'qrCode'));
    }

    // /**
    //  * تحميل بطاقة العمل كـ PDF
    //  */
    // public function downloadBusinessCardPDF($employeeId)
    // {
    //     $employee = Employees::with(['user', 'department', 'position'])->findOrFail($employeeId);

    //     $pdf = PDF::loadView('hr.employees.business-card-pdf', compact('employee'));

    //     // تحديد حجم البطاقة (بطاقة عمل قياسية)
    //     $pdf->setPaper([0, 0, 252, 144], 'landscape'); // 3.5 x 2 inch

    //     return $pdf->download('business-card-' . $employee->name . '.pdf');
    // }

    // /**
    //  * طباعة بطاقات متعددة
    //  */
    // public function printMultipleCards(Request $request)
    // {
    //     $employeeIds = $request->input('employee_ids', []);
    //     $employees = Employees::with(['user', 'department', 'position'])
    //         ->whereIn('id', $employeeIds)
    //         ->get();

    //     if ($employees->isEmpty()) {
    //         return redirect()->back()->with('error', 'لم يتم العثور على موظفين.');
    //     }

    //     $pdf = PDF::loadView('hr.employees.multiple-business-cards', compact('employees'));
    //     $pdf->setPaper('A4', 'portrait');

    //     return $pdf->download('business-cards-batch.pdf');
    // }

    // /**
    //  * معاينة بطاقة العمل قبل الطباعة
    //  */
    // public function previewBusinessCard($employeeId)
    // {
    //     $employee = Employees::with(['user', 'department', 'position'])->findOrFail($employeeId);

    //     return view('hr.employees.business-card-preview', compact('employee'));
    // }
}
