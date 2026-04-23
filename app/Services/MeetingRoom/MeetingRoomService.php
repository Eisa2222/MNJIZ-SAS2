<?php

namespace App\Services\MeetingRoom;

use App\Contracts\ErrorHandlerInterface;
use App\Data\MeetingRoom\MeetingRoomData;
use App\Models\Hr\Employees\Employees;
use App\Models\MeetingRoom\MeetingRoom;
use App\Models\OperationsCenter\Customer\Customers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MeetingRoomService
{
    public function __construct(private ErrorHandlerInterface $errorHandler) {}

    // create
    public function create(MeetingRoomData $dto)
    {
        // التحقق من عدم وجود تضارب
        $this->validateNoConflict($dto);

        $this->errorHandler->execute(function () use ($dto) {
            return DB::transaction(function () use ($dto) {
                $meeting  = new MeetingRoom([
                    'title'      => $dto->title,
                    'hall'       => $dto->hall,
                    'date'       => $dto->date,
                    'from_time'  => $dto->from_time,
                    'to_time'    => $dto->to_time,
                    'notes'      => $dto->notes,
                    'created_by' => $this->currentEmployeeId(),
                ]);

                $meeting->save();

                $this->saveParticipants($meeting, $dto->participants ?? []);
            });
        }, 'حدث خطأ أثناء حفظ الحجز');
    }

    // update
    public function update(int $id, MeetingRoomData $dto)
    {
        $this->errorHandler->execute(function () use ($id, $dto) {
            return DB::transaction(function () use ($id, $dto) {
                $meeting = MeetingRoom::findOrFail($id);

                // التحقق من عدم وجود تضارب (مع استثناء الحجز الحالي)
                $this->validateNoConflict($dto, $id);

                $meeting->update([
                    'title'      => $dto->title,
                    'hall'       => $dto->hall,
                    'date'       => $dto->date,
                    'from_time'  => $dto->from_time,
                    'to_time'    => $dto->to_time,
                    'notes'      => $dto->notes,
                    'updated_by' => $this->currentEmployeeId(),
                ]);

                $meeting->participants()->delete();

                $this->saveParticipants($meeting, $dto->participants ?? []);
            });
        }, 'حدث خطأ أثناء تحديث الحجز');
    }

    // delete
    public function delete(int $id): bool
    {
        return $this->errorHandler->execute(function () use ($id) {
            return DB::transaction(function () use ($id) {
                $booking = MeetingRoom::findOrFail($id);


                return $booking->delete();
            });
        }, 'حدث خطأ أثناء حذف الحجز');
    }

    /*
    |============================================================================
    |                          Private methods
    |============================================================================
    */

    private function saveParticipants(MeetingRoom $meeting, array $participants): void
    {
        if (!empty($participants)) {
            $rows = array_map(function ($p) {
                return [
                    'email'       => $p['email'] ?? null,
                    'type'        => $p['type'],
                    'employee_id' => $p['employee_id'] ?? null,
                    'customer_id' => $p['customer_id'] ?? null,
                ];
            }, $participants);

            $meeting->participants()->createMany($rows);
        }
    }


    private function validateNoConflict(MeetingRoomData $dto, ?int $excludeId = null): void
    {
        $query = MeetingRoom::where('hall', $dto->hall)
            ->where('date', $dto->date);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $existing = $query->where(function ($q) use ($dto) {
            $q->whereBetween('from_time', [$dto->from_time, $dto->to_time ?? '23:59'])
                ->orWhereBetween('to_time', [$dto->from_time, $dto->to_time ?? '23:59'])
                ->orWhere(function ($q) use ($dto) {
                    $q->where('from_time', '<=', $dto->from_time)
                        ->where('to_time', '>=', $dto->to_time ?? '23:59');
                });
        })->exists();

        if ($existing) {
            throw ValidationException::withMessages([
                'time' => 'يوجد تضارب مع حجز آخر في نفس الوقت'
            ]);
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
}
