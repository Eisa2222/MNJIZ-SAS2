<?php

namespace App\Services\Survey;

use App\Contracts\ErrorHandlerInterface;
use App\Data\Survey\SurveyData;
use App\Models\Survey\Survey;
use App\Enums\Survey\SurveyStatus;
use App\Models\Survey\SurveyQuestion;
use App\Models\Survey\SurveyQuestionOption;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SurveyService
{
    public function __construct(private ErrorHandlerInterface $errorHandler) {}

    public function create(SurveyData $dto): void
    {
        $this->errorHandler->execute(function () use ($dto) {
            return DB::transaction(function () use ($dto) {

                $employeeId = $this->currentEmployeeId();

                $survey = Survey::create([
                    'title'            => $dto->title,
                    'type'             => $dto->type->value,
                    'description'      => $dto->description,
                    'message_template' => $dto->message_template,
                    'status'           => SurveyStatus::Active,
                    'created_by'       => $employeeId,
                ]);

                foreach ($dto->questions as $q) {
                    $question = $survey->questions()->create([
                        'question_text' => $q['question_text'],
                        'created_by'    => $employeeId,
                    ]);

                    $optionRows = [];

                    foreach ($q['options'] as $opt) {
                        $optionRows[] = [
                            'question_id' => $question->id,
                            'option_text' => $opt['option_text'],
                            'created_by'  => $employeeId,
                        ];
                    }

                    if (!empty($optionRows)) {
                        SurveyQuestionOption::insert($optionRows);
                    }
                }
            });
        }, 'حدث خطأ أثناء حفظ الاستبيان');
    }

    // update
    public function update(int $id, SurveyData $dto): void
    {
        $this->errorHandler->execute(function () use ($id, $dto) {
            return DB::transaction(function () use ($id, $dto) {

                $employeeId = $this->currentEmployeeId();

                $survey = Survey::with('questions')->findOrFail($id);

                $survey->update([
                    'title'            => $dto->title,
                    'type'             => $dto->type->value,
                    'description'      => $dto->description,
                    'message_template' => $dto->message_template,
                    'updated_by'       => $employeeId,
                ]);

                SurveyQuestion::where('survey_id', $survey->id)->forceDelete();

                foreach ($dto->questions as $q) {
                    $question = $survey->questions()->create([
                        'question_text' => $q['question_text'],
                        'created_by'    => $employeeId,
                    ]);

                    $rows = [];
                    foreach ($q['options'] as $opt) {
                        $rows[] = [
                            'question_id' => $question->id,
                            'option_text' => $opt['option_text'],
                            'created_by'  => $employeeId,
                        ];
                    }
                    if ($rows) {
                        SurveyQuestionOption::insert($rows);
                    }
                }
            });
        }, 'حدث خطأ أثناء تحديث الاستبيان');
    }

    // delete
    // public function delete(int $id): bool
    // {
    //     return $this->errorHandler->execute(function () use ($id) {
    //         return DB::transaction(function () use ($id) {
    //             $survey = Survey::findOrFail($id);

    //             return $survey->delete();
    //         });
    //     }, 'حدث خطأ أثناء حذف الاستبيان');
    // }



    /*
    |============================================================================
    |============================================================================
    |                          Private methods
    |============================================================================
    |============================================================================
    */
    private function currentEmployeeId(): int
    {
        $user = Auth::user();
        if (!$user || !$user->employee) {
            throw new \Exception('المستخدم غير مرتبط بموظف في النظام.');
        }
        return $user->employee->id;
    }
}