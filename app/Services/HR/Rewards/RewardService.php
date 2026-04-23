<?php

namespace App\Services\HR\Rewards;

use App\Contracts\ErrorHandlerInterface;
use App\Data\Hr\Rewards\RewardData;
use App\Enums\Hr\Reward\RewardStatus;
use App\Models\Hr\Rewards\Reward;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RewardService
{
    public function __construct(
        private ErrorHandlerInterface $errorHandler
    ) {}

    // create
    public function createReward(RewardData $dto): void
    {
        $this->errorHandler->execute(function () use ($dto) {
            return DB::transaction(function () use ($dto) {
                $serial = $this->generateRewardNumber();

                $reward = new Reward([
                    'reward_number'    => $serial,
                    'employee_id'      => $dto->employee_id,
                    'reward_type'      => $dto->reward_type->value ?? $dto->reward_type,
                    'amount'           => $dto->amount,
                    'reward_date'      => $dto->reward_date->format('Y-m-d'),
                    'status'           => RewardStatus::Pending->value,
                    'notes'            => $dto->notes,
                    'created_by'       => $this->currentEmployeeId(),
                ]);

                $reward->save();
            });
        }, 'حدث خطأ أثناء حفظ المكافأة');
    }


    // update
    public function updateReward(int $id, RewardData $dto): void
    {
        $this->errorHandler->execute(function () use ($id, $dto) {
            return DB::transaction(function () use ($id, $dto) {
                $reward = Reward::findOrFail($id);

                // فحص إمكانية التعديل
                if (!$reward->status->canEdit()) {
                    throw ValidationException::withMessages([
                        'status' => ' لا يمكن تعديل المكافأة في حالة ' . $reward->status->label()
                    ]);
                }

                $reward->update([
                    'employee_id'   => $dto->employee_id,
                    'reward_type'   => $dto->reward_type->value ?? $dto->reward_type,
                    'amount'        => $dto->amount,
                    'reward_date'   => $dto->reward_date->format('Y-m-d'),
                    'notes'         => $dto->notes,
                    'updated_by'    => $this->currentEmployeeId(),
                ]);
            });
        }, 'حدث خطأ أثناء تحديث المكافأة');
    }


    // delete
    public function deleteReward(int $id): bool
    {
        return $this->errorHandler->execute(function () use ($id) {
            return DB::transaction(function () use ($id) {
                $reward = Reward::findOrFail($id);

                if (!$reward->status->canDelete()) {
                    throw ValidationException::withMessages([
                        'status' => " لا يمكن حذف المكافأة في حالة " . $reward->status->label()
                    ]);
                }

                return $reward->delete();
            });
        }, 'حدث خطأ أثناء حذف المكافأة');
    }



    /*
    |============================================================================
    |============================================================================
    |                        Pravate Methods
    |============================================================================
    |============================================================================
    */
    private function generateRewardNumber(): string
    {
        $last = Reward::withTrashed()
            ->select('reward_number')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        $next = $last && str_starts_with($last->reward_number, 'REW-')
            ? ((int) str_replace('REW-', '', $last->reward_number) + 1) : 1;

        return 'REW-' . str_pad($next, 4, '0', STR_PAD_LEFT);
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
