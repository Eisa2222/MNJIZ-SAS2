<?php

namespace App\Services\LegalAffair\PowerOfAttorney;

use App\Contracts\ErrorHandlerInterface;
use App\Data\LegalAffair\PowerOfAttorney\PowerOfAttorneyData;
use App\Enums\LegalAffair\PowerOfAttorney\PowerOfAttorneyStatus;
use App\Models\LegalAffair\PowerOfAttorney\PowerOfAttorney;
use Alkoumi\LaravelHijriDate\Hijri;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Tenancy\Support\TenantStorage;

class PowerOfAttorneyService
{
    public function __construct(private ErrorHandlerInterface $errorHandler) {}

    public function create(PowerOfAttorneyData $dto, $fileAttachment = null): void
    {
        $this->errorHandler->execute(function () use ($dto, $fileAttachment) {
            return DB::transaction(function () use ($dto, $fileAttachment) {

                $this->validateDates($dto);

                // معالجة المرفق
                $filePath = $this->handleFileUpload($fileAttachment);

                $powerAttorney = PowerOfAttorney::create([
                    'power_name'      => $dto->power_name,
                    'power_number'    => $dto->power_number,
                    'date_issued'     => $dto->date_issued->format('Y-m-d'),
                    'date_expiry'     => $dto->date_expiry?->format('Y-m-d'),
                    'file_attachment' => $filePath,
                    'notes'           => $dto->notes,
                    'status'          => PowerOfAttorneyStatus::Active->value,
                    'created_by'      => Auth::id(),
                ]);

                // ربط الوكلاء
                $this->syncAgents($powerAttorney, $dto->agents);

                // ربط العملاء
                $this->syncCustomers($powerAttorney, $dto->customers);

                // تحديث حالة الوكالة
                $this->updatePowerAttorneyStatus($powerAttorney);

                return $powerAttorney;
            });
        }, 'حدث خطأ أثناء إنشاء الوكالة');
    }

    public function update(int $id, PowerOfAttorneyData $dto, $fileAttachment = null): void
    {
        $this->errorHandler->execute(function () use ($id, $dto, $fileAttachment) {
            return DB::transaction(function () use ($id, $dto, $fileAttachment) {
                $powerAttorney = PowerOfAttorney::findOrFail($id);

                $this->validateDates($dto);

                // معالجة المرفق الجديد
                $filePath = $this->handleFileUpdate($fileAttachment, $powerAttorney);

                // تحديث بيانات الوكالة
                $powerAttorney->update([
                    'power_name'      => $dto->power_name,
                    'power_number'    => $dto->power_number,
                    'date_issued'     => $dto->date_issued->format('Y-m-d'),
                    'date_expiry'     => $dto->date_expiry?->format('Y-m-d'),
                    'file_attachment' => $filePath ?? $powerAttorney->file_attachment,
                    'notes'           => $dto->notes,
                    'updated_by'      => Auth::id(),
                ]);

                // تحديث الوكلاء
                $this->syncAgents($powerAttorney, $dto->agents);

                // تحديث العملاء
                $this->syncCustomers($powerAttorney, $dto->customers);

                // تحديث حالة الوكالة
                $this->updatePowerAttorneyStatus($powerAttorney);

                return $powerAttorney;
            });
        }, 'حدث خطأ أثناء تحديث الوكالة');
    }

    public function delete(int $id): bool
    {
        return $this->errorHandler->execute(function () use ($id) {
            return DB::transaction(function () use ($id) {
                $powerAttorney = PowerOfAttorney::findOrFail($id);

                // التحقق من إمكانية الحذف
                if ($powerAttorney->lawsuits()->exists()) {
                    throw ValidationException::withMessages([
                        'power_attorney' => 'لا يمكن حذف الوكالة لارتباطها بسجلات أخرى.'
                    ]);
                }

                // حذف المرفق إذا كان موجود
                if ($powerAttorney->file_attachment) {
                    Storage::disk('public')->delete($powerAttorney->file_attachment);
                }

                return $powerAttorney->delete();
            });
        }, 'حدث خطأ أثناء حذف الوكالة');
    }

    public function updateStatus(int $id): array
    {
        return $this->errorHandler->execute(function () use ($id) {
            $powerAttorney = PowerOfAttorney::findOrFail($id);

            // تبديل الحالة بين Active و Revoked فقط
            if ($powerAttorney->status === PowerOfAttorneyStatus::Active) {
                $powerAttorney->status = PowerOfAttorneyStatus::Revoked;
            } else {
                $powerAttorney->status = PowerOfAttorneyStatus::Active;
            }

            $powerAttorney->save();

            return [
                'success' => true,
                'status'  => $powerAttorney->status->value,
                'message' => 'تم تحديث حالة الوكالة بنجاح'
            ];
        }, 'حدث خطأ أثناء تحديث حالة الوكالة');
    }

    /*
    |============================================================================
    |============================================================================
    |                           Private Methods
    |============================================================================
    |============================================================================
    */
    private function validateDates(PowerOfAttorneyData $dto): void
    {
        if ($dto->date_expiry && $dto->date_expiry < $dto->date_issued) {
            throw ValidationException::withMessages([
                'date_expiry' => 'تاريخ الانتهاء يجب أن يكون بعد أو مساوي لتاريخ الإصدار.'
            ]);
        }
    }

    private function handleFileUpload($file): ?string
    {
        if ($file) {
            // tenants/{tenant_id}/legal-affair/power-of-attorneys
            return $file->store(TenantStorage::path('legal-affair/power-of-attorneys'), 'public');
        }
        return null;
    }

    private function handleFileUpdate($file, PowerOfAttorney $powerAttorney): ?string
    {
        if ($file) {
            // حذف الملف القديم
            if ($powerAttorney->file_attachment) {
                Storage::disk('public')->delete($powerAttorney->file_attachment);
            }
            // رفع الملف الجديد تحت مسار tenant-prefixed
            return $file->store(TenantStorage::path('legal-affair/power-of-attorneys'), 'public');
        }
        return null;
    }

    private function syncAgents(PowerOfAttorney $powerAttorney, array $agents): void
    {
        $agentsData = collect($agents)->mapWithKeys(function ($employeeId) {
            return [$employeeId => ['user_id' => Auth::id()]];
        })->toArray();

        $powerAttorney->agents()->sync($agentsData);
    }

    private function syncCustomers(PowerOfAttorney $powerAttorney, array $customers): void
    {
        $customersData = collect($customers)->mapWithKeys(function ($customerId) {
            return [$customerId => ['user_id' => Auth::id()]];
        })->toArray();

        $powerAttorney->customers()->sync($customersData);
    }

    private function updatePowerAttorneyStatus(PowerOfAttorney $powerAttorney): void
    {
        if (!$powerAttorney->date_expiry) {
            return;
        }

        $today = Carbon::today();
        $expiryDate = Carbon::parse($powerAttorney->date_expiry);

        if ($expiryDate->lte($today) && $powerAttorney->status !== PowerOfAttorneyStatus::Expired) {
            $powerAttorney->status = PowerOfAttorneyStatus::Expired;
            $powerAttorney->save();
        }
    }
}
