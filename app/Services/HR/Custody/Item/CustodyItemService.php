<?php

namespace App\Services\HR\Custody\Item;

use App\Contracts\ErrorHandlerInterface;
use App\Data\Hr\Custody\Item\CustodyItemData;
use App\Data\Hr\Custody\Item\UpdateCustodyItemData;
use App\Enums\Hr\Custody\Item\CustodyItemStatus;
use App\Enums\Hr\Custody\Item\CustodyUseStatus;
use App\Models\Hr\Custody\Item\CustodyItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustodyItemService
{
    public function __construct(private ErrorHandlerInterface $errorHandler) {}

    public function createCustodyItem(CustodyItemData $dto)
    {
        return $this->errorHandler->execute(function () use ($dto) {
            return DB::transaction(function () use ($dto) {

                foreach ($dto->serial_numbers as $serialNumber) {
                    $asset = new CustodyItem([
                        'name'                  => $dto->name,
                        'serial_number'         => $serialNumber,
                        'price'                 => $dto->price,
                        'asset_category_id'     => $dto->asset_category_id,
                        'storage_location_id'   => $dto->storage_location_id,
                        'description'           => $dto->description,
                        'custody_status'        => CustodyItemStatus::New->value,
                        'use_status'            => CustodyUseStatus::Available->value,
                        'created_by'            => $this->currentEmployeeId(),
                    ]);

                    $asset->save();
                }
            });
        }, 'حدث خطأ أثناء حفظ الاصل');
    }

    public function updateCustodyItem(int $id, UpdateCustodyItemData $dto): void
    {
        $this->errorHandler->execute(function () use ($id, $dto) {
            return DB::transaction(function () use ($id, $dto) {
                $asset = CustodyItem::findOrFail($id);

                if ($asset->use_status != CustodyUseStatus::InUse && $dto->use_status === CustodyUseStatus::InUse) {
                    throw ValidationException::withMessages([
                        'use_status' => 'يجب اسناد الاصل لموظف لتغيير حالة الاستخدام لقيد الاستخدام',
                    ]);
                }

                if ($asset->use_status == CustodyUseStatus::InUse && $dto->use_status != $asset->use_status) {
                    throw ValidationException::withMessages([
                        'use_status' => 'لا يمكن تعديل حالة الاستخدام عندما يكون الأصل في حالة "قيد الاستخدام".',
                    ]);
                }


                $asset->update([
                    'name'                  => $dto->name,
                    'serial_number'         => $dto->serial_number,
                    'price'                 => $dto->price,
                    'asset_category_id'     => $dto->asset_category_id,
                    'storage_location_id'   => $dto->storage_location_id,
                    'use_status'            => $dto->use_status->value,
                    'updated_by'            => $this->currentEmployeeId(),
                    'description'           => $dto->description,
                ]);
            });
        }, 'حدث خطأ أثناء تحديث الاصل');
    }

    public function deleteCustodyItem(int $id): bool
    {
        return $this->errorHandler->execute(function () use ($id) {
            return DB::transaction(function () use ($id) {
                $asset = CustodyItem::findOrFail($id);

                return $asset->delete();
            });
        }, 'حدث خطأ أثناء حذف الاصل');
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
