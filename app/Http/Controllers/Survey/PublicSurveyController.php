<?php

namespace App\Http\Controllers\Survey;

use App\Helpers\SettingsHelper;
use App\Http\Controllers\Controller;
use App\Models\GeneralSetting\SystemSetting\Settings;
use App\Models\Survey\SurveyAnswer;
use App\Models\Survey\SurveyResponse;
use Beta\Microsoft\Graph\ManagedTenants\Model\Setting;
use Illuminate\Http\Request;

class PublicSurveyController extends Controller
{
    public function show(string $token)
    {
        $surveyResponse = SurveyResponse::where('token', $token)
            ->with(['survey.questions.options', 'customer'])
            ->first();

        if (!$surveyResponse) {
            return view('public-pages.not-found');
        }

        if ($surveyResponse->isCompleted()) {
            return view('public-pages.not-found');
        }

        $settings = Settings::current();

        return view('public-pages.surveys.index', compact('surveyResponse', 'settings'));
    }

    public function submit(Request $request, string $token)
    {
        $surveyResponse = SurveyResponse::where('token', $token)->first();

        if (!$surveyResponse  || $surveyResponse->isCompleted()) {
            return redirect()->back()->with('error', 'الاستبيان غير متاح');
        }

        // التحقق من الإجابات
        $request->validate([
            'answers'   => 'required|array',
            'answers.*' => 'required'
        ]);

        foreach ($request->answers as $questionId => $answer) {
            SurveyAnswer::create([
                'survey_response_id'    => $surveyResponse->id,
                'question_id'           => $questionId,
                'option_id'             => is_numeric($answer) ? $answer : null,
            ]);
        }

        // حفظ الإجابات
        $surveyResponse->update([
            'responses'         => $request->answers,
            'status'            => 'completed',
            'completed_at'      => now(),
            'ip_address'        => $request->ip(),
            'user_agent'        => $request->userAgent()
        ]);

        return view('public-pages.surveys.thank-you');
    }
}