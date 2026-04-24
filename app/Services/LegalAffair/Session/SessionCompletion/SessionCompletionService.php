<?php

namespace App\Services\LegalAffair\Session\SessionCompletion;


use App\Contracts\ErrorHandlerInterface;
use App\Data\LegalAffair\Session\SessionCompletion\SessionCompletionData;
use App\Data\LegalAffair\Session\SessionData;
use App\Enums\LegalAffair\Session\SessionStatus;
use App\Enums\Survey\SurveyType;
use App\Models\LegalAffair\Session\Session;
use App\Models\LegalAffair\Lawsuit\Lawsuit;
use App\Jobs\Tasks\CreateTaskJob;
use App\Models\OperationsCenter\Contract\Contract;
use App\Models\Survey\Survey;
use App\Services\SMS\SurveySmsService;
use App\Services\SurveyService\SurveyService;
use App\Tenancy\Support\TenantStorage;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SessionCompletionService
{
    public function __construct(private ErrorHandlerInterface $errorHandler, private SurveySmsService $surveySmsService) {}


    public function completeSession(int $id, SessionCompletionData $dto): array
    {
        return $this->errorHandler->execute(function () use ($id, $dto) {
            return DB::transaction(function () use ($id, $dto) {
                $session = Session::findOrFail($id);

                // التحقق من إمكانية استكمال الجلسة
                $this->validateSessionCompletion($session);

                // رفع المرفقات
                $attachments  = $this->handleAttachments($dto);

                $sessionStatus = $this->determineSessionStatus($attachments, $session);

                // تحديث بيانات الجلسة
                $updateData = array_merge($dto->toArray(),  $attachments, [
                    'session_status'    => $sessionStatus,
                    'updated_by'        => $this->currentEmployeeId(),
                ]);


                $session->update($updateData);

                // إغلاق الدعوى إذا لزم الأمر
                if ($dto->execution_format === 'yes' && $dto->user_confirmation === 'نعم') {
                    $this->closeLawsuit($session->lawsuit_id);
                }


                return [
                    'success' => true,
                    'message' => $dto->getSuccessMessage(),
                    'session' => $session->fresh(),
                ];
            });
        }, 'حدث خطأ أثناء استكمال ضبط الجلسة');
    }

    /*
    |============================================================================
    |============================================================================
    |                           Private Methods
    |============================================================================
    |============================================================================
    */
    private function handleAttachments(SessionCompletionData $dto): array
    {
        return [
            'session_control_attached' => $this->storeAttachment($dto->session_control_attached, 'sessions'),
            'rule_attached'            => $this->storeAttachment($dto->rule_attached, 'rules'),
        ];
    }


    private function storeAttachment(?UploadedFile $file, string $folder): ?string
    {
        if (! $file) {
            return null;
        }

        // tenants/{tenant_id}/legal-affair/sessions/{folder}
        return $file->store(
            TenantStorage::path("legal-affair/sessions/{$folder}"),
            'public'
        );
    }

    private function validateSessionCompletion(Session $session): void
    {
        if (!$session->isSessionExpired()) {
            throw ValidationException::withMessages([
                'session' => 'لا يمكنك استكمال ضبط هذه الجلسة لأن تاريخ نهايتها لم يأتِ بعد.'
            ]);
        }

        if ($session->session_status === SessionStatus::Inactive) {
            throw ValidationException::withMessages([
                'session' => 'لا يمكنك استكمال ضبط هذه الجلسة لأن الجلسة مغلقة.'
            ]);
        }

        // if ($session->isCompleted()) {
        //     throw ValidationException::withMessages([
        //         'session' => 'تم استكمال ضبط هذه الجلسة مسبقاً.'
        //     ]);
        // }
    }

    private function closeLawsuit(int $lawsuitId): void
    {
        $lawsuit = Lawsuit::findOrFail($lawsuitId);
        $lawsuit->update([
            'lawsuit_status'    => 'inactive',
            'updated_by'        => $this->currentEmployeeId(),
        ]);
    }

    private function determineSessionStatus(array $attachmentPaths, Session $session)
    {
        // فحص المرفقات المطلوبة
        $hasSessionControl = $attachmentPaths['session_control_attached'] || $session->session_control_attached;
        $hasRuleAttachment = $attachmentPaths['rule_attached'] || $session->rule_attached;

        if ($hasSessionControl || $hasRuleAttachment) {
            // ارسال الاستبيان
            if ($session->primary_contract_customer_id) {
                $this->surveySmsService->sendSurveyByType(SurveyType::Session, [$session->primary_contract_customer_id]);
            }
            return SessionStatus::Inactive; // إغلاق الجلسة
        }



        return SessionStatus::Active; // تبقى نشطة حتى يتم إرفاق ضبط الجلسة
    }

    private function currentEmployeeId(): int
    {
        $user = Auth::user();
        if (!$user || !$user->employee) {
            throw new \Exception('المستخدم غير مرتبط بموظف في النظام.');
        }
        return $user->employee->id;
    }
}
