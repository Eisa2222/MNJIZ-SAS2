<?php

namespace App\Services\HR\Custody\Request;

use App\Contracts\ErrorHandlerInterface;
use App\Data\Hr\Custody\Request\Assign\CustodyAssignData;
use App\Data\Hr\Custody\Request\Return\CustodyReturnData;
use App\Enums\ElectronicServices\Custody\Requests\CustodyRequestStatus;
use App\Enums\ElectronicServices\Custody\Requests\CustodyRequestType;
use App\Models\ElectronicServices\Custody\Request\CustodyRequest;
use App\Services\ElectronicServices\Custody\Log\CustodyLogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustodyRequestService
{
    public function __construct(
        private ErrorHandlerInterface $errorHandler,
        private CustodyLogService $logService, // سيتم حذفه بعد اكمال تعديل الاعتمادات

    ) {}

    /*
    |============================================================================
    |============================================================================
    |                                   Assign
    |============================================================================
    |============================================================================
    */
    public function createAssign(CustodyAssignData  $dto): void
    {
        $this->errorHandler->execute(fn() => DB::transaction(function () use ($dto) {

            $exists = CustodyRequest::where('request_type', CustodyRequestType::Assign)
                ->where('custody_item_id', $dto->custody_item_id)
                ->where('employee_id', $dto->employee_id)
                ->where('status', CustodyRequestStatus::Pending)
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'custody_item_id' => 'يوجد بالفعل طلب  قيد الانتظار لهذه العهدة.',
                ]);
            }

            $custodyRequest =  CustodyRequest::create($dto->toArray() + [
                'created_by'    => $this->currentEmployeeId(),
                'request_type'  => CustodyRequestType::Assign,
                'status'        => CustodyRequestStatus::Pending,
            ]);

            // $this->logService->log($custodyRequest->id);
        }), 'حدث خطأ أثناء إنشاء طلب العهدة');
    }

    public function updateAssign(int $id, CustodyAssignData $dto): void
    {
        $this->errorHandler->execute(function () use ($id, $dto) {
            return DB::transaction(function () use ($id, $dto) {
                $request = CustodyRequest::assign()->pending()->where('id', $id)->firstOrFail();

                if ($dto->custody_item_id !== $request->custody_item_id) {
                    $exists = CustodyRequest::where('request_type', CustodyRequestType::Assign)
                        ->where('custody_item_id', $dto->custody_item_id)
                        ->where('employee_id', $dto->employee_id)
                        ->where('status', CustodyRequestStatus::Pending)
                        ->exists();

                    if ($exists) {
                        throw ValidationException::withMessages([
                            'custody_item_id' => 'يوجد بالفعل طلب  قيد الانتظار لهذه العهدة.',
                        ]);
                    }
                }

                // فحص إمكانية التعديل
                if (!$request->status->canEditOrDelete()) {
                    throw ValidationException::withMessages([
                        'status' => 'لا يمكن تعديل الطلب بعد اعتماده أو رفضه.'
                    ]);
                }

                $request->update($dto->toArray() + [
                    'updated_by'      => $this->currentEmployeeId(),
                ]);
            });
        }, 'حدث خطأ أثناء تحديث طلب العهدة');
    }

    /*
    |============================================================================
    |============================================================================
    |                             Return
    |============================================================================
    |============================================================================
    */
    public function createReturn(CustodyReturnData  $dto): void
    {
        $this->errorHandler->execute(fn() => DB::transaction(function () use ($dto) {

            $exists = CustodyRequest::where('request_type', CustodyRequestType::Return)
                ->where('parent_request_id', $dto->parent_request_id)
                ->where('employee_id', $dto->employee_id)
                ->where('status', CustodyRequestStatus::Pending)
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'parent_request_id' => 'يوجد بالفعل طلب إرجاع قيد الانتظار لهذه العهدة.',
                ]);
            }

            $parent = CustodyRequest::findOrFail($dto->parent_request_id);

            $custodyRequest = CustodyRequest::create($dto->toArray() + [
                'created_by'        => $this->currentEmployeeId(),
                'custody_item_id'   => $parent->custody_item_id,
                'request_type'      => CustodyRequestType::Return,
                'status'            => CustodyRequestStatus::Pending,
            ]);

            // $this->logService->log($custodyRequest->id);
        }), 'حدث خطأ أثناء إنشاء طلب إرجاع عهدة');
    }

    public function updateReturn(int $id, CustodyReturnData $dto): void
    {
        $this->errorHandler->execute(function () use ($id, $dto) {
            return DB::transaction(function () use ($id, $dto) {
                //
                $request = CustodyRequest::return()->pending()->where('id', $id)->firstOrFail();

                if ($dto->parent_request_id !== $request->parent_request_id) {
                    $exists = CustodyRequest::where('request_type', CustodyRequestType::Return)
                        ->where('parent_request_id', $dto->parent_request_id)
                        ->where('employee_id', $dto->employee_id)
                        ->where('status', CustodyRequestStatus::Pending)
                        ->exists();

                    if ($exists) {
                        throw ValidationException::withMessages([
                            'parent_request_id' => 'يوجد بالفعل طلب إرجاع قيد الانتظار لهذه العهدة.',
                        ]);
                    }
                }

                //
                $parent = CustodyRequest::findOrFail($dto->parent_request_id);

                // فحص إمكانية التعديل
                if (!$request->status->canEditOrDelete()) {
                    throw ValidationException::withMessages([
                        'status' => 'لا يمكن تعديل الطلب بعد اعتماده أو رفضه.'
                    ]);
                }

                $request->update($dto->toArray() + [
                    'updated_by'      => $this->currentEmployeeId(),
                    'custody_item_id' => $parent->custody_item_id,
                ]);
            });
        }, 'حدث خطأ أثناء تحديث طلب العهدة');
    }

    /*
    |============================================================================
    |============================================================================
    |                            Public Methods
    |============================================================================
    |============================================================================
    */
    // delete
    public function delete(int $id): bool
    {
        return $this->errorHandler->execute(function () use ($id) {
            return DB::transaction(function () use ($id) {
                $request = CustodyRequest::findOrFail($id);

                if (!$request->status->canEditOrDelete()) {
                    throw ValidationException::withMessages([
                        'status' => 'لا يمكن حذف الطلب بعد اعتماده أو رفضه.'
                    ]);
                }

                return $request->delete();
            });
        }, 'حدث خطأ أثناء حذف طلب العهدة');
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
