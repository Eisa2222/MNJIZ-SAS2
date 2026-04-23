<?php

namespace App\Http\Controllers\Survey;

use App\Data\Survey\SurveyData;
use App\DataTables\Survey\SurveyDataTable;
use App\Enums\Survey\SurveyStatus;
use App\Enums\Survey\SurveyType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Survey\SurveyRequest;
use App\Models\Hr\Employees\Employees;
use App\Models\Survey\Survey;
use App\Models\Survey\SurveyAnswer;
use App\Models\Survey\SurveyQuestion;
use App\Models\Survey\SurveyResponse;
use App\Services\Survey\SurveyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SurveyController extends Controller
{
    private $route  = "surveys";
    private $page   = "surveys";


    public function __construct(private SurveyService $service)
    {
        // $this->middleware('can:إدارة السلف')->only(['index', 'show']);
        // $this->middleware('can:إضافة سلفة')->only(['create', 'store']);
        // $this->middleware('can:تعديل سلفة')->only(['edit', 'update']);
        // $this->middleware('can:حذف سلفة')->only(['destroy']);
    }
    /*
    |============================================================================
    | Index
    |============================================================================
    */
    public function index(SurveyDataTable $dataTable)
    {
        try {

            // Statistics
            // $statusCounts = Advance::query()
            //     ->select('status')
            //     ->selectRaw('COUNT(*) as count')
            //     ->groupBy('status')
            //     ->pluck('count', 'status')
            //     ->toArray();

            // $totalAdvance = array_sum($statusCounts);
            // $pendingAdvance  = $statusCounts[AdvanceStatus::Pending->value] ?? 0;
            // $approvedAdvance = $statusCounts[AdvanceStatus::Approved->value] ?? 0;
            // $rejectedAdvance = $statusCounts[AdvanceStatus::Rejected->value] ?? 0;

            // // Filters
            // $employees        = Employees::active()->select('id', 'name', 'nickname')->get();
            // $advanceTypes     = AdvanceType::options();
            // $advanceStatus    = AdvanceStatus::options();
            return $dataTable->render($this->page . '.index');
            // return $dataTable->render('hr.advances.index', compact(
            //     // Statistics
            //     'totalAdvance',
            //     'pendingAdvance',
            //     'approvedAdvance',
            //     'rejectedAdvance',
            //     // Filters
            //     'employees',
            //     'advanceTypes',
            //     'advanceStatus'
            // ));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    }


    /*
    |============================================================================
    | create
    |============================================================================
    */
    // public function create()
    // {
    //     $surveyTypes = SurveyType::options();
    //     return view($this->page . '.create', compact('surveyTypes'));
    // }

    /*
    |============================================================================
    | store
    |============================================================================
    */
    // public function store(SurveyRequest $request)
    // {
    //     try {
    //         $dto = new SurveyData($request->validated());

    //         $this->service->create($dto);

    //         return redirect()->route($this->route . '.index')->with('success', 'تم الإضافة بنجاح.');
    //     } catch (\Exception $e) {
    //         return redirect()->back()->withInput()->with('error', $e->getMessage());
    //     }
    // }


    /*
    |============================================================================
    | show
    |============================================================================
    */
    public function show(Survey $survey)
    {
        return view($this->page . '.show', compact('survey'));
    }




    /*
    |============================================================================
    | edit
    |============================================================================
    */
    public function edit(Survey $survey)
    {
        $surveyTypes = SurveyType::options();

        return view($this->page . '.edit', compact('survey', 'surveyTypes'));
    }


    /*
    |============================================================================
    | Update
    |============================================================================
    */
    public function update(SurveyRequest $request, int $id)
    {
        try {
            $dto = new SurveyData($request->validated());

            $this->service->update($id, $dto);

            return redirect()->route($this->route . '.index')->with('success', 'تم التحديث بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }


    public function toggleStatus(Survey $survey)
    {
        try {
            $employeeId = auth()->user()?->employee?->id;
            $new = $survey->status === SurveyStatus::Active
                ? SurveyStatus::InActive
                : SurveyStatus::Active;

            $survey->update([
                'status'     => $new,
                'updated_by' => $employeeId,
            ]);

            return response()->json([
                'success' => true,
                'status'  => $new->value,
                'label'   => $new->label(),
                'color'   => $new->color(),
            ]);
        } catch (\Throwable $e) {
            Log::error($e);
            return response()->json(['success' => false, 'message' => 'تعذّر تغيير الحالة.'], 500);
        }
    }


    public function statistics(Survey $survey)
    {
        $survey->load(['questions.options', 'createdBy', 'updatedBy']);

        // إحصائيات عامة
        $totalSent = SurveyResponse::where('survey_id', $survey->id)->count();
        $totalCompleted = SurveyResponse::where('survey_id', $survey->id)
            ->where('status', 'completed')->count();
        $completionRate = $totalSent > 0 ? round(($totalCompleted / $totalSent) * 100, 1) : 0;

        // إحصائيات مفصلة لكل سؤال
        $questionsStats = [];
        foreach ($survey->questions as $question) {
            $questionStats = [
                'question' => $question,
                'total_answers' => 0,
                'options_stats' => []
            ];

            // حساب إجابات كل خيار
            foreach ($question->options as $option) {
                $optionCount = SurveyAnswer::where('question_id', $question->id)
                    ->where('option_id', $option->id)->count();

                $questionStats['options_stats'][] = [
                    'option' => $option,
                    'count' => $optionCount,
                    'percentage' => $totalCompleted > 0 ? round(($optionCount / $totalCompleted) * 100, 1) : 0
                ];

                $questionStats['total_answers'] += $optionCount;
            }

            // الإجابات النصية (إن وجدت)
            $textAnswers = SurveyAnswer::where('question_id', $question->id)
                ->whereNull('option_id')
                ->count();

            $questionsStats[] = $questionStats;
        }

        return view($this->page . '.statistics', compact(
            'survey',
            'totalSent',
            'totalCompleted',
            'completionRate',
            'questionsStats'
        ));
    }


    public function questionDetails(Survey $survey, SurveyQuestion $question)
    {
        $question->load(['options']);

        // إحصائيات مفصلة للسؤال
        $optionsDetails = [];

        foreach ($question->options as $option) {
            $answers = SurveyAnswer::where('question_id', $question->id)
                ->where('option_id', $option->id)
                ->with(['surveyResponse.customer'])
                ->get();

            $optionsDetails[] = [
                'option' => $option,
                'count' => $answers->count(),
                'customers' => $answers->map(function ($answer) {
                    return [
                        'customer'      => $answer->surveyResponse->customer,
                        'answered_at'   => $answer->surveyResponse->completed_at,
                        'ip_address'    => $answer->surveyResponse->ip_address
                    ];
                })
            ];
        }



        return view('surveys.question-details', compact(
            'survey',
            'question',
            'optionsDetails',
        ));
    }
}
