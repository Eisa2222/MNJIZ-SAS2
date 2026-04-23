<?php

namespace App\Services\LegalAffair\Session;

use App\Contracts\ErrorHandlerInterface;
use App\Data\LegalAffair\Session\SessionData;
use App\Enums\LegalAffair\Session\SessionStatus;
use App\Models\LegalAffair\Session\Session;
use App\Models\LegalAffair\Lawsuit\Lawsuit;
use App\Jobs\Tasks\CreateTaskJob;
use App\Services\OrganizationCenter\Tasks\Task\TaskService;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SessionService
{
    public function __construct(private ErrorHandlerInterface $errorHandler, private TaskService $taskService) {}

    public function create(SessionData $dto): void
    {
        $this->errorHandler->execute(function () use ($dto) {
            return DB::transaction(function () use ($dto) {

                // التحقق من وجود جلسة نشطة للدعوى
                $this->validateActiveSession($dto->lawsuit_id);

                $this->validateSessionDate($dto->session_date, $dto->session_time);

                $sessionName = $this->generateSessionName($dto->lawsuit_id);


                // إنشاء الجلسة
                $session = Session::create([
                    'project_id'                => $dto->project_id,
                    'lawsuit_id'                => $dto->lawsuit_id,
                    'session_name'              => $sessionName,
                    'entity_ranks_id'           => $dto->entity_ranks_id,
                    'session_date'              => $dto->session_date->format('Y-m-d'),
                    'session_time'              => $dto->session_time,
                    'session_status'            => SessionStatus::Active,
                    'created_by'                => $this->currentEmployeeId(),
                ]);

                // ربط المكلفين
                $this->syncAssignedEmployees($session, $dto->assigned_to);

                // إنشاء مهمة للمكلفين
                $this->taskService->createApprovalTask("sessions", $session, $dto->assigned_to);

                return $session;
            });
        }, 'حدث خطأ أثناء إنشاء الجلسة');
    }

    public function update(int $id, SessionData $dto)
    {
        $this->errorHandler->execute(function () use ($id, $dto) {
            return DB::transaction(function () use ($id, $dto) {
                $session = Session::findOrFail($id);

                // التحقق من إمكانية التعديل
                $this->validateSessionEdit($session);

                $this->validateSessionDate($dto->session_date, $dto->session_time);

                // توليد اسم الجلسة الجديد إذا تغيرت الدعوى
                $sessionName = $session->session_name;

                if ($session->lawsuit_id !== $dto->lawsuit_id) {
                    $sessionName = $this->generateSessionName($dto->lawsuit_id, $session->id);
                }

                // تحديث بيانات الجلسة
                $session->update([
                    'project_id'                => $dto->project_id,
                    'lawsuit_id'                => $dto->lawsuit_id,
                    'session_name'              => $sessionName,
                    'entity_ranks_id'           => $dto->entity_ranks_id,
                    'session_date'              => $dto->session_date->format('Y-m-d'),
                    'session_time'              => $dto->session_time,
                    'updated_by'                => $this->currentEmployeeId(),
                ]);

                // تحديث المكلفين
                $this->updateAssignedEmployees($session, $dto->assigned_to);

                $this->taskService->createApprovalTask("sessions", $session, $dto->assigned_to);
            });
        }, 'حدث خطأ أثناء تحديث الجلسة');
    }

    public function delete(int $id): bool
    {
        return $this->errorHandler->execute(function () use ($id) {
            return DB::transaction(function () use ($id) {
                $session = Session::findOrFail($id);

                // التحقق من إمكانية الحذف
                $this->validateSessionDelete($session);

                // حذف المرفقات
                if ($session->session_control_attached) {
                    Storage::disk('public')->delete($session->session_control_attached);
                }
                if ($session->rule_attached) {
                    Storage::disk('public')->delete($session->rule_attached);
                }

                return $session->delete();
            });
        }, 'حدث خطأ أثناء حذف الجلسة');
    }

    public function objection(int $id): array
    {
        return $this->errorHandler->execute(function () use ($id) {
            $session = Session::findOrFail($id);

            $session->update([
                'objection_status'  => true,
                'session_status'    => SessionStatus::Inactive,
                'updated_by'        => $this->currentEmployeeId()
            ]);

            return [
                'success' => true,
                'message' => 'تم تقديم الاعتراض وإغلاق الجلسة',
            ];
        }, 'حدث خطأ أثناء تقديم الاعتراض ');
    }


    /*
    |============================================================================
    |============================================================================
    |                           Private Methods
    |============================================================================
    |============================================================================
    */
    private function validateSessionDate($sessionDate, ?string $sessionTime = null): void
    {
        $now = Carbon::now();

        // تحويل التاريخ إلى Carbon مع التعامل مع التواريخ الهجرية
        $sessionDateTime = $this->convertToCarbon($sessionDate);

        // إذا كان هناك وقت محدد، أضفه للتاريخ
        if ($sessionTime) {
            try {
                $timeParts = explode(':', $sessionTime);
                $sessionDateTime->setTime((int)$timeParts[0], (int)$timeParts[1], 0);
            } catch (\Exception $e) {
                // في حالة فشل تحليل الوقت، نتحقق من التاريخ فقط
            }
        } else {
            // إذا لم يكن هناك وقت محدد، نضع نهاية اليوم للمقارنة
            $sessionDateTime->endOfDay();
        }

        // التحقق من أن التاريخ والوقت في المستقبل
        if ($sessionDateTime->lte($now)) {
            throw ValidationException::withMessages([
                'session_date' => 'لا يمكن إضافة جلسة في تاريخ ووقت قد انتهى بالفعل. يرجى اختيار تاريخ ووقت في المستقبل.'
            ]);
        }

        // تحقق إضافي: منع إضافة جلسات في نفس اليوم إذا مر أكثر من نصف اليوم
        if ($sessionDateTime->isToday() && !$sessionTime) {
            $halfDay = $now->copy()->startOfDay()->addHours(12);
            if ($now->gte($halfDay)) {
                throw ValidationException::withMessages([
                    'session_date' => 'لا يمكن إضافة جلسة لليوم الحالي بدون تحديد وقت محدد بعد منتصف اليوم.'
                ]);
            }
        }
    }

    private function convertToCarbon($date): Carbon
    {
        $dateString = $date->format('Y-m-d');

        // التحقق من أن التاريخ هجري أم ميلادي
        if ($this->isHijriDate($dateString)) {
            // تحويل التاريخ الهجري إلى ميلادي
            return $this->convertHijriToGregorian($dateString);
        }

        // التاريخ ميلادي، تحويل مباشر
        return Carbon::parse($dateString);
    }

    private function isHijriDate($date): bool
    {
        return preg_match('/^(13|14|15)\d{2}-\d{2}-\d{2}$/', $date);
    }

    private function convertHijriToGregorian(string $hijriDate): Carbon
    {
        // تقسيم التاريخ الهجري إلى أجزاء
        list($year, $month, $day) = explode('-', $hijriDate);

        // استخدام نفس المكتبة المستخدمة في Session Model
        $gregorianDate = \Alkoumi\LaravelHijriDate\Hijri::DateToGregorianFromDMY($day, $month, $year);

        return Carbon::parse($gregorianDate);
    }

    private function validateActiveSession(int $lawsuitId): void
    {
        $lawsuit = Lawsuit::findOrFail($lawsuitId);

        $latestSession = $lawsuit->sessions()->latest('created_at')->first();

        $hasActiveSession = $latestSession && $latestSession->session_status === SessionStatus::Active;

        if ($hasActiveSession) {
            throw ValidationException::withMessages([
                'session' => 'لا يمكنك إضافة جلسة جديدة لأن هناك جلسة لم تغلق بعد.'
            ]);
        }
    }

    private function validateSessionEdit(Session $session): void
    {
        $sessionDateTime = $session->getSessionDateTime();

        if ($sessionDateTime && $sessionDateTime->lte(Carbon::now())) {
            throw ValidationException::withMessages([
                'session' => 'لا يمكنك تعديل هذه الجلسة لأنها قد انتهت.'
            ]);
        }
    }

    private function validateSessionDelete(Session $session): void
    {
        $sessionDateTime = $session->getSessionDateTime();

        if ($sessionDateTime && $sessionDateTime->lte(Carbon::now())) {
            throw ValidationException::withMessages([
                'session' => 'لا يمكنك حذف هذه الجلسة لأنها قد انتهت.'
            ]);
        }
    }

    private function syncAssignedEmployees(Session $session, array $assignedEmployees): void
    {
        $currentUserId = Auth::id();
        $attachData = [];

        foreach ($assignedEmployees as $employeeId) {
            $attachData[$employeeId] = ['user_added_id' => $currentUserId];
        }

        $session->assignedUsers()->attach($attachData);
    }

    private function updateAssignedEmployees(Session $session, array $newAssignedEmployees): void
    {
        $currentUserId = Auth::id();
        $oldAssignedUsers = $session->assignedUsers()->pluck('users.id')->toArray();

        if ($oldAssignedUsers != $newAssignedEmployees) {
            // تحديث المكلفين
            $syncData = [];
            foreach ($newAssignedEmployees as $employeeId) {
                $syncData[$employeeId] = ['user_added_id' => $currentUserId];
            }

            $session->assignedUsers()->sync($syncData);

            // تحديث المهام المرتبطة
            // $this->updateSessionTasks($session, $newAssignedEmployees, $oldAssignedUsers);
        }
    }


    private function currentEmployeeId(): int
    {
        $user = Auth::user();
        if (!$user || !$user->employee) {
            throw new \Exception('المستخدم غير مرتبط بموظف في النظام.');
        }
        return $user->employee->id;
    }

    private function generateSessionName(int $lawsuitId, ?int $excludeSessionId = null): string
    {
        $lawsuit = Lawsuit::findOrFail($lawsuitId);

        $query = Session::withTrashed()->where('lawsuit_id', $lawsuitId);

        // استثناء الجلسة الحالية في حالة التعديل
        if ($excludeSessionId) {
            $query->where('id', '!=', $excludeSessionId);
        }

        $sessionCount = $query->count() + 1;

        return "جلسة رقم " . $sessionCount . " في دعوى " . $lawsuit->name;
    }
}
