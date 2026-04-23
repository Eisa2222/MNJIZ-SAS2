<?php

namespace App\Http\Controllers\LegalAffair\Session\SessionCompletion;


use Alkoumi\LaravelHijriDate\Hijri;
use App\Data\LegalAffair\Session\SessionCompletion\SessionCompletionData;
use App\Enums\LegalAffair\Lawsuit\LawsuitStatus;
use App\Enums\LegalAffair\Session\SessionCompletion\SummaryReportStatus;
use App\Enums\LegalAffair\Session\SessionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\LegalAffair\Session\SessionCompletion\StoreSessionCompletionRequest;
use App\Http\Requests\LegalAffair\Session\StoreSessionRequest;
use App\Models\general_setting\SettingsEntityRank;
use App\Models\general_setting\SettingsSessionType;
use App\Models\general_setting\SettingsTypeRulings;
use App\Models\Hr\Employees\Employees;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\LegalAffair\Lawsuit\Lawsuit;
use App\Models\LegalAffair\Session\Session;
use App\Services\LegalAffair\Session\SessionCompletion\SessionCompletionService;

class SessionCompletionController extends Controller
{
    private $route  = "legal-affairs.sessions";
    private $page   = "legal_affairs.sessions.session_completion";

    public function __construct(private SessionCompletionService $service) {}


    public function completion(string $id)
    {
        $session = Session::findOrfail($id);

        if (!$session->isSessionExpired()) {
            return redirect()->route($this->route . '.index')
                ->with('error', 'لا يمكنك ضبط استكمال هذه الجلسة لأن تاريخ نهايتها لم يأتِ بعد.')->withFragment('sessions');
        }

        if ($session->session_status == SessionStatus::Inactive) {
            return redirect()->route($this->route . '.index')
                ->with('error', 'لا يمكنك ضبط استكمال هذه الجلسة لأن الجلسة مغلقة.')->withFragment('sessions');
        }

        $sessionType            = SettingsSessionType::where('status', 'active')->select(['id', 'name'])->get();
        $ruleType               = SettingsTypeRulings::where('status', 'active')->select(['id', 'name'])->get();
        $summaryReportStatus    = SummaryReportStatus::options();

        return view($this->page . '.completion', compact('session', 'sessionType', 'ruleType', 'summaryReportStatus'));
    }


    // Store
    public function store(StoreSessionCompletionRequest $request, Session $session)
    {
        if (!$session->isSessionExpired()) {
            return redirect()->route($this->route . '.index')
                ->with('error', 'لا يمكنك ضبط استكمال هذه الجلسة لأن تاريخ نهايتها لم يأتِ بعد.')->withFragment('sessions');
        }

        if ($session->session_status == SessionStatus::Inactive) {
            return redirect()->route($this->route . '.index')
                ->with('error', 'لا يمكنك ضبط استكمال هذه الجلسة لأن الجلسة مغلقة.')->withFragment('sessions');
        }


        try {
            $validated = $request->validated();

            $dto = new SessionCompletionData([
                'summary_report_status'    => $validated['summary_report_status'],
                'last_objection_deadline'  => $validated['last_objection_deadline'] ?? null,
                'session_type'             => $validated['session_type'],
                'rule_type'                => $validated['rule_type'] ?? null,
                'execution_format'         => $validated['execution_format'] ?? null,
                'expected_execution_date'  => $validated['expected_execution_date'] ?? null,
                'execution_minutes'        => $validated['execution_minutes'],
                'notes'                    => $validated['notes'] ?? null,
                'session_control_attached' => $request->file('session_control_attached'),
                'rule_attached'            => $request->file('rule_attached'),
                'user_confirmation'        => $validated['user_confirmation'] ?? null,
            ]);

            $this->service->completeSession($session->id, $dto);

            return redirect()->route($this->route . '.index')
                ->with('success', 'تم إكمال الجلسة بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }


        if ($request->isMethod('put')) {
            $filePaths = [];




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



                return redirect()->route('legal-affairs.lawsuits.show', $session->lawsuit->id)
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

        return view('judicial_affairs.lawsuits.sections.set_session_completion', compact('session', 'employees', 'settings_entity_ranks', 'ruleType', 'sessionType'));
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



                return redirect()->route('legal-affairs.lawsuits.show', $session->lawsuit->id)
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
