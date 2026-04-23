<?php

namespace App\Services\ElectronicServices\Custody\Log;

use App\Contracts\ErrorHandlerInterface;
use App\Enums\ElectronicServices\Custody\Log\CustodyLogAction;
use App\Enums\ElectronicServices\Custody\Requests\CustodyRequestStatus;
use App\Enums\ElectronicServices\Custody\Requests\CustodyRequestType;
use App\Enums\Hr\Custody\Item\CustodyItemStatus;
use App\Enums\Hr\Custody\Item\CustodyUseStatus;
use App\Models\ElectronicServices\Custody\Log\CustodyLog;
use App\Models\ElectronicServices\Custody\Request\CustodyRequest;
use App\Models\Hr\Custody\Item\CustodyItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustodyLogService
{
    public function __construct(private ErrorHandlerInterface $errorHandler) {}


    // بعد الاعتماد
    public function log(int $requestId): void
    {
        $this->errorHandler->execute(fn() => DB::transaction(function () use ($requestId) {
            // جلب طلب العهدة
            $request = CustodyRequest::findOrFail($requestId);

            // تحديد الإجراء وحالات العنصر والطلب
            $action = null;
            $newStatus = null;


            match ($request->request_type) {
                CustodyRequestType::Assign => [
                    $action             = CustodyLogAction::Checkout,
                    $newStatus          = CustodyUseStatus::InUse,
                ],
                CustodyRequestType::Return => [
                    $action             = CustodyLogAction::Checkin,
                    $newStatus          = CustodyUseStatus::Available,
                ],
                default => throw ValidationException::withMessages([
                    'request_type' => "نوع الطلب غير مدعوم للتسجيل: {$request->request_type}"
                ]),
            };

            // تحديث حالة الأصل
            $item = CustodyItem::findOrFail($request->custody_item_id);
            $item->update(['use_status' => $newStatus, 'custody_status' => CustodyItemStatus::Used]);

            if ($request->request_type === CustodyRequestType::Return) {
                $parent = $request->parentRequest;
                if ($parent) {
                    $parent->update(['status' => CustodyRequestStatus::Returned]);
                }
            }

            // تسجيل السجل
            CustodyLog::create([
                'custody_request_id' => $requestId,
                'action'             => $action->value,
                'log_date'           => now(),
            ]);
        }), 'حدث خطأ أثناء تسجيل حركة العهدة');
    }

    // اذا تم الغاء الاعتماد بعد الاعتماد
    public function reverseLog(int $requestId): void
    {
        $this->errorHandler->execute(fn() => DB::transaction(function () use ($requestId) {
            // جلب طلب العهدة
            $request = CustodyRequest::findOrFail($requestId);

            // التحقق من وجود سجل مرتبط بالطلب
            $existingLog = CustodyLog::where('custody_request_id', $requestId)->first();

            if (!$existingLog) {
                throw ValidationException::withMessages([
                    'log' => 'لا يوجد سجل لهذا الطلب لإلغائه'
                ]);
            }

            // تحديد الحالة المعكوسة
            $reverseAction      = null;
            $reverseUseStatus   = null;

            match ($request->request_type) {
                CustodyRequestType::Assign => [
                    $reverseAction          = CustodyLogAction::CheckoutCanceled,
                    $reverseUseStatus       = CustodyUseStatus::Available,
                ],
                CustodyRequestType::Return => [
                    $reverseAction = CustodyLogAction::CheckinCanceled,
                    $reverseUseStatus = CustodyUseStatus::InUse,
                ],
                default => throw ValidationException::withMessages([
                    'request_type' => "نوع الطلب غير مدعوم لإلغاء التسجيل: {$request->request_type}"
                ]),
            };

            $item = CustodyItem::findOrFail($request->custody_item_id);
            $item->update([
                'use_status' => $reverseUseStatus,
            ]);

            // تسجيل سجل الإلغاء
            CustodyLog::create([
                'custody_request_id'    => $requestId,
                'action'                => $reverseAction->value,
                'log_date'              => now(),
                'notes'                 => "تم إلغاء الاعتماد"
            ]);

        }), 'حدث خطأ أثناء إلغاء تسجيل حركة العهدة');
    }
}
