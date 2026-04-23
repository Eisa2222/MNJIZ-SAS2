<?php

namespace App\Services\ElectronicServices\PurchaseRequests;


use App\Contracts\ErrorHandlerInterface;
use App\Data\ElectronicServices\PurchaseRequests\EmployeePurchaseRequestsData;
use App\Enums\ElectronicServices\PurchaseRequests\PurchaseRequestsStatus;
use App\Models\ElectronicServices\PurchaseRequests\PurchaseRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmployeePurchaseRequestsService
{
    public function __construct(private ErrorHandlerInterface $errorHandler) {}

    // create
    public function createRequest(EmployeePurchaseRequestsData $dto): void
    {
        $this->errorHandler->execute(function () use ($dto) {
            return DB::transaction(function () use ($dto) {

                PurchaseRequest::create($dto->toArray() + [
                    'created_by'    => $this->currentEmployeeId(),
                    'employee_id'   => $this->currentEmployeeId(),
                    'status'        => PurchaseRequestsStatus::Pending,
                ]);
            });
        }, 'حدث خطأ أثناء حفظ الطلب');
    }


    // update
    public function updateRequest(int $id, EmployeePurchaseRequestsData $dto): void
    {
        $this->errorHandler->execute(function () use ($id, $dto) {
            return DB::transaction(function () use ($id, $dto) {
                $request = PurchaseRequest::findOrFail($id);

                // فحص إمكانية التعديل
                if (!$request->status->canEditOrDelete()) {
                    throw ValidationException::withMessages([
                        'status' => ' لا يمكن التعديل في هذه الحالة '
                    ]);
                }

                $request->update($dto->toArray() + [
                    'updated_by'      => $this->currentEmployeeId(),
                ]);
            });
        }, 'حدث خطأ أثناء تحديث ');
    }


    // delete
    public function deleteRequest(int $id)
    {
        return $this->errorHandler->execute(function () use ($id) {
            return DB::transaction(function () use ($id) {
                $request = PurchaseRequest::findOrFail($id);

                if (!$request->status->canEditOrDelete()) {
                    throw ValidationException::withMessages([
                        'status' => ' لا يمكن الحذف في هذه الحالة '
                    ]);
                }

                $request->delete();
            });
        }, 'حدث خطأ أثناء الحذف ');
    }



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
