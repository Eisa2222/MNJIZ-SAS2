<?php

namespace App\Http\Controllers\OperationsCenter\Customer;

use Alkoumi\LaravelHijriDate\Hijri;
use App\Data\OperationsCenter\Customer\CustomerData;
use App\DataTables\OperationsCenter\Customer\CustomersDataTable;
use App\Enums\OperationsCenter\Customer\CustomerType;
use App\Enums\Survey\SurveyResponse\SurveyResponseStatus;
use App\Helpers\General;
use App\Http\Controllers\Controller;
use App\Http\Requests\OperationsCenter\Customers\StoreCustomerRequest;
use App\Http\Requests\OperationsCenter\Customers\UpdatCustomerRequest;
use App\Models\general_setting\SettingsClientStatus;
use App\Models\general_setting\SettingsCountry;
use App\Models\general_setting\SettingsDepartmentContractCase;
use App\Models\general_setting\SettingsMarketingChannel;
use App\Models\general_setting\SettingsRegion;
use App\Models\general_setting\SettingsSector;
use App\Models\general_setting\SettingsSocial;
use App\Models\Hr\Employees\Employees;
use App\Models\MessageLog;
use App\Models\OperationsCenter\Customer\Customers;
use App\Models\Survey\SurveyResponse;
use App\Services\OperationsCenter\Customer\CustomerService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class CustomersController extends Controller
{
    private $page = "operations_center.customers";


    public function __construct(private CustomerService $customerService)
    {
        $this->middleware(function ($request, $next) {
            if ($request->user()->can('كل العملاء') || $request->user()->can('العملاء الخاصين بي')) {
                return $next($request);
            }
            abort(403);
        })->only(['index', 'show']);

        $this->middleware('can:إضافة عميل')->only(['create', 'store']);
        $this->middleware('can:تعديل عميل')->only(['edit', 'update']);
        $this->middleware('can:حذف عميل')->only(['destroy']);
        $this->middleware('can:إسال SMS للعملاء')->only(['sendSms']);
    }


    public function index(CustomersDataTable $dataTable)
    {
        $query = Customers::query();
        try {
            if (auth()->user()->can('العملاء الخاصين بي') && !auth()->user()->can('كل العملاء')) {
                $query->where('created_by', auth()->id());
            }

            // Statistics
            $typeCounts = $query
                ->select('customer_type')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('customer_type')
                ->pluck('count', 'customer_type')
                ->toArray();

            $totalCustomers         = array_sum($typeCounts);
            $individualCustomers    = $typeCounts[CustomerType::Individual->value] ?? 0;
            $companyCustomers       = $typeCounts[CustomerType::Company->value] ?? 0;

            // // Filters
            $customerType               = CustomerType::options();
            $statuses                   = SettingsClientStatus::select(['id', 'name'])->get();
            $employees                  = Employees::active()->select('id', 'name', 'nickname')->get();


            return $dataTable->render($this->page . '.index', compact(
                // Statistics
                'totalCustomers',
                'individualCustomers',
                'companyCustomers',
                // // Filters
                'customerType',
                'statuses',
                'employees',
            ));
        } catch (\Exception $e) {
            Log::error($e);

            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    }


    public function create()
    {
        $statuses               = SettingsClientStatus::where('status', 'active')->select(['id', 'name'])->get();
        $countries              = SettingsCountry::where('status', 'active')->select(['id', 'name'])->get();
        $sectors                = SettingsSector::where('status', 'active')->select(['id', 'name'])->get();
        $marketingChannels      = SettingsMarketingChannel::where('status', 'active')->select(['id', 'name'])->get();
        $employees              = Employees::active()->select('id', 'name', 'nickname')->get();
        $regions                = SettingsRegion::where('status', 'active')->select(['id', 'name'])->get();
        $customers              = Customers::select(['id', 'name'])->get();
        $socials                = SettingsSocial::where('status', 'active')->select(['id', 'name'])->get();
        $categories             = SettingsDepartmentContractCase::where('status', 'active')->select(['id', 'name'])->orderBy('id', 'desc')->get();


        return view('operations_center.customers.create', compact(
            'statuses',
            'countries',
            'sectors',
            'marketingChannels',
            'employees',
            'regions',
            'customers',
            'socials',
            'categories'
        ));
    }


    public function store(StoreCustomerRequest $request)
    {
        try {
            $dto = new CustomerData($request->validated());

            $this->customerService->createCustomer($dto);

            return redirect()->route('operations-center.customers.index')->with('success', 'تم إضافة العميل بنجاح.');
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }


    public function edit(string $id)
    {
        $customer               = Customers::findOrFail($id);
        $statuses               = SettingsClientStatus::where('status', 'active')->select(['id', 'name'])->get();
        $countries              = SettingsCountry::where('status', 'active')->select(['id', 'name'])->get();
        $sectors                = SettingsSector::where('status', 'active')->select(['id', 'name'])->get();
        $marketingChannels      = SettingsMarketingChannel::where('status', 'active')->select(['id', 'name'])->get();
        $employees              = Employees::active()->select('id', 'name', 'nickname')->get();
        $regions                = SettingsRegion::where('status', 'active')->select(['id', 'name'])->get();
        $customers              = Customers::select(['id', 'name'])->get();
        $socials                = SettingsSocial::where('status', 'active')->select(['id', 'name'])->get();
        $categories             = SettingsDepartmentContractCase::where('status', 'active')->select(['id', 'name'])->orderBy('id', 'desc')->get();


        return view($this->page . '.edit', compact(
            'customer',
            'statuses',
            'countries',
            'sectors',
            'marketingChannels',
            'employees',
            'regions',
            'customers',
            'socials',
            'categories'
        ));
    }


    public function show(Customers $customer)
    {
        $offersCount            = $customer->offers()->count();
        $contractsCount         = $customer->contracts()->count();
        $powerOfAttorneysCount  = $customer->powerOfAttorneys()->count();
        $lawsuitsCount          = $customer->lawsuitsAsPlaintiff()->count() + $customer->lawsuitsAsDefendant()->count();

        // إضافة استبيانات العميل
        $surveyResponses = $customer->surveyResponses()
            ->with(['survey'])
            ->orderBy('created_at', 'desc')
            ->get();

        $surveysCount           = $surveyResponses->count();
        $completedSurveysCount  = $surveyResponses->where('status', SurveyResponseStatus::Completed)->count();


        return view($this->page . '.show', compact(
            'customer',
            'offersCount',
            'contractsCount',
            'powerOfAttorneysCount',
            'lawsuitsCount',
            'surveyResponses',
            'surveysCount',
            'completedSurveysCount'
        ));
    }


    public function update(UpdatCustomerRequest $request, Customers $customer)
    {

        try {
            $dto = new CustomerData($request->validated());

            $this->customerService->updateCustomer($customer->id, $dto);

            return redirect()->route('operations-center.customers.index')->with('success', 'تم تحديث بيانات العميل بنجاح.');
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }


    public function destroy(string $id)
    {
        try {
            $customer = Customers::findOrFail($id);

            $hasContracts = $customer->contracts()->exists();
            $hasOffers = $customer->offers()->exists();
            $lawsuitsAsPlaintiff = $customer->lawsuitsAsPlaintiff()->exists();
            $lawsuitsAsDefendant = $customer->lawsuitsAsDefendant()->exists();

            // إذا كان لديه أي من هذه العلاقات
            if ($hasContracts || $hasOffers || $lawsuitsAsPlaintiff || $lawsuitsAsDefendant) {
                return redirect()->route('operations-center.customers.index')->with('error', 'لا يمكن حذف العميل لارتباطه بسجلات أخرى.');
            }

            $customer->delete();

            if (request()->ajax()) {
                return response()->json(['success' => 'تم حذف العميل بنجاح.']);
            }

            return redirect()->route('operations-center.customers.index')->with('success', 'تم حذف العميل بنجاح.');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json(['error' => 'حدث خطأ أثناء حذف العميل.'], 500);
            }

            return redirect()->route('operations-center.customers.index')->with('error', 'حدث خطأ أثناء حذف العميل.');
        }
    }


    public function getSurvey(SurveyResponse $survey_response)
    {
        // تحميل العلاقات المطلوبة
        $survey_response->load([
            'survey.questions.options',
            'customer',
            'answers'
        ]);

        // التحقق من وجود الاستبيان
        if (!$survey_response->survey) {
            return redirect()->back()->with('error', 'الاستبيان غير موجود');
        }

        // التحقق من حالة الإكمال
        if ($survey_response->status->value !== 'completed') {
            return redirect()->back()->with('error', 'الاستبيان غير مكتمل بعد');
        }

        // بناء خريطة الإجابات المختارة
        $selectedMap = [];
        foreach ($survey_response->answers as $answer) {
            if ($answer->option_id) {
                $selectedMap[$answer->question_id] = $answer->option_id;
            }
        }

        return view($this->page . '.survey', compact(
            'survey_response',
            'selectedMap'
        ));
    }


    /*
    |--------------------------------------------------------------------------
    | trashed
    |--------------------------------------------------------------------------
    */
    public function trashed(Request $request)
    {
        try {
            if ($request->ajax()) {
                $customers = Customers::onlyTrashed()
                    ->with(['user', 'clientStatus', 'country'])
                    ->select([
                        'id',
                        'name',
                        'nationality',
                        'status',
                        'email',
                        'created_at',
                        'user_id',
                        'deleted_at',
                    ])->orderBy('id', 'desc');

                return DataTables::of($customers)
                    ->addIndexColumn()
                    ->addColumn('added_by', function ($row) {
                        return $row->user ? $row->user->name : 'غير محدد';
                    })
                    ->addColumn('nationality', function ($row) {
                        return $row->country ? $row->country->name : 'غير محدد';
                    })
                    ->addColumn('status', function ($row) {
                        return $row->clientStatus ? $row->clientStatus->name : 'غير محدد';
                    })
                    ->editColumn('created_at', function ($row) {
                        return Carbon::parse($row->created_at)->locale('ar')->translatedFormat('d F Y');
                    })
                    ->editColumn('deleted_at', function ($row) {
                        return $row->deleted_at ? Hijri::ShortDate($row->deleted_at) : '';
                    })
                    ->addColumn('action', function ($row) {
                        $restoreUrl = route('operations-center.customers.restore', $row->id);
                        $forceDeleteUrl = route('operations-center.customers.forceDelete', $row->id);
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

            return view('operations_center.customers.trashed');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | restore
    |--------------------------------------------------------------------------
    */
    public function restore($id)
    {
        try {
            $customer = Customers::onlyTrashed()->findOrFail($id);

            // استعادة العميل
            $customer->restore();

            return redirect()->route('operations-center.customers.trashed')->with('success', 'تم استعادة العميل بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء استعادة العميل. يرجى المحاولة لاحقاً.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | force delete
    |--------------------------------------------------------------------------
    */
    public function forceDelete($id)
    {
        try {
            $customer = Customers::onlyTrashed()->findOrFail($id);
            // تحقق من عدم وجود سجلات مرتبطة تمنع الحذف
            // حذف العميل نهائيًا
            $customer->forceDelete();

            return redirect()->route('operations-center.customers.trashed')->with('success', 'تم حذف العميل نهائيًا بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء حذف العميل نهائيًا. يرجى المحاولة لاحقاً.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | send SMS
    |--------------------------------------------------------------------------
    */
    public function sendSms(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1600',
            'customer_ids' => 'required|array',
            'customer_ids.*' => 'integer|exists:customers,id',
        ], [
            'message.required' => 'حقل الرسالة مطلوب.',
            'message.max' => 'حقل الرسالة لا يجب أن يتجاوز 1600 حرف.',
            'customer_ids.required' => 'يجب تحديد عملاء لإرسال الرسالة.',
            'customer_ids.array' => 'صيغة معرفات العملاء غير صحيحة.',
            'customer_ids.*.exists' => 'العميل المحدد غير موجود.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $message = $request->input('message');
        $customerIds = $request->input('customer_ids');

        $customers = Customers::whereIn('id', $customerIds)->get();
        $failedNumbers = [];
        $successfulRecipients = [];

        foreach ($customers as $customer) {
            if ($customer->contact_number) {
                $formattedNumber = ltrim($customer->contact_number, '0');
                $sent = General::sendSMS($message, $formattedNumber);

                if ($sent) {
                    $successfulRecipients[] = [
                        'type' => 'customer',
                        'id' => $customer->id,
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
            return redirect()->route('operations-center.customers.index')->with('success', 'تم إرسال الرسائل النصية بنجاح.');
        } else {
            return redirect()->route('operations-center.customers.index')->with('error', 'فشل إرسال الرسائل إلى بعض الأرقام. الرجاء مراجعة السجل.');
        }
    }
}
