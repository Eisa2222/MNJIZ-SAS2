<?php

namespace App\Services\LegalAffair\Opponent;

use App\Contracts\ErrorHandlerInterface;
use App\Data\LegalAffair\Opponent\OpponentData;
use App\Enums\LegalAffair\Opponent\OpponentType;
use App\Models\LegalAffair\Opponent\Opponent;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class OpponentService
{
    public function __construct(private ErrorHandlerInterface $errorHandler) {}

    public function createOpponent(OpponentData $dto): void
    {
        $this->errorHandler->execute(function () use ($dto) {
            return DB::transaction(function () use ($dto) {
                $opponent = new Opponent([
                    'name'                   => $dto->name,
                    'email'                  => $dto->email,
                    'contact_number'                  => $dto->contact_number,
                    'bio'                    => $dto->bio,
                    'settings_region_id'     => $dto->settingsRegionId,
                    'type'                   => $dto->type,
                    'commercial_registration' => $dto->commercialRegistration,
                    'unified_number'         => $dto->unifiedNumber,
                    'identity_number'        => $dto->identityNumber,
                    'created_by'             => Auth::user()->id,
                ]);

                $opponent->save();

                if ($dto->type === OpponentType::Company && !empty($dto->authorizations)) {
                    foreach ($dto->authorizations as $authorization) {
                        $opponent->authorizations()->create($authorization);
                    }
                }

                return $opponent;
            });
        }, 'حدث خطأ أثناء حفظ الخصم');
    }

    public function updateOpponent(int $id, OpponentData $dto): void
    {
        $this->errorHandler->execute(function () use ($id, $dto) {
            return DB::transaction(function () use ($id, $dto) {
                $opponent = Opponent::findOrFail($id);

                $this->handleTypeChange($opponent, $dto);

                $opponent->update([
                    'name'                   => $dto->name,
                    'email'                  => $dto->email,
                    'contact_number'                  => $dto->contact_number,
                    'bio'                    => $dto->bio,
                    'settings_region_id'     => $dto->settingsRegionId,
                    'type'                   => $dto->type,
                    'commercial_registration' => $dto->commercialRegistration,
                    'unified_number'         => $dto->unifiedNumber,
                    'identity_number'        => $dto->identityNumber,
                    'updated_by'             => Auth::user()->id,

                ]);

                $this->handleAuthorizations($opponent, $dto);

                return $opponent;
            });
        }, 'حدث خطأ أثناء تحديث بيانات الخصم');
    }

    // public function deleteOpponent(int $id): bool
    // {
    //     return $this->errorHandler->execute(function () use ($id) {
    //         return DB::transaction(function () use ($id) {
    //             $opponent = Opponent::findOrFail($id);

    //             // التحقق من إمكانية الحذف
    //             $this->validateCustomerDeletion($opponent);

    //             return $opponent->delete();
    //         });
    //     }, 'حدث خطأ أثناء حذف الخصم');
    // }


    /*
    |============================================================================
    |                          Private methods
    |============================================================================
    */

    private function handleTypeChange(Opponent $opponent, OpponentData $dto): void
    {
        // إذا تم التغيير من مؤسسة إلى فرد
        if ($opponent->customer_type === OpponentType::Company && $dto->type === OpponentType::Individual) {
            $opponent->commercial_registration_number = null;
            $opponent->unified_number = null;
            $opponent->authorizations()->delete();
        }

        // إذا تم التغيير من فرد إلى مؤسسة
        if ($opponent->customer_type === OpponentType::Individual && $dto->type === OpponentType::Company) {
            $opponent->title = null;
            $opponent->civil_registry_number = null;
        }
    }

    private function handleAuthorizations(Opponent $opponent, OpponentData $dto): void
    {
        if ($dto->type === OpponentType::Company) {
            $opponent->authorizations()->delete();

            if (!empty($dto->authorizations)) {
                foreach ($dto->authorizations as $authorization) {
                    $opponent->authorizations()->create($authorization);
                }
            }
        } else {
            $opponent->authorizations()->delete();
        }
    }

    // private function validateCustomerDeletion(Opponent $opponent): void
    // {

    //     $hasLawsuitsAsPlaintiff = $opponent->lawsuitsAsPlaintiff()->exists();
    //     $hasLawsuitsAsDefendant = $opponent->lawsuitsAsDefendant()->exists();

    //     if ($hasLawsuitsAsPlaintiff || $hasLawsuitsAsDefendant) {
    //         throw ValidationException::withMessages([
    //             'customer' => 'لا يمكن حذف الخصم لارتباطه بسجلات أخرى.'
    //         ]);
    //     }
    // }
}
