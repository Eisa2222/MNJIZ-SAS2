<?php

namespace App\Services\GeneralSetting\SystemSetting;

use App\Contracts\ErrorHandlerInterface;
use App\Models\GeneralSetting\SystemSetting\CompanyAttachment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SystemSettingsServices
{
    public function __construct(private ErrorHandlerInterface $errorHandler) {}

    public function updateCompanyAttachments($request): void
    {
        $this->errorHandler->execute(function () use ($request) {
            return DB::transaction(function () use ($request) {

                $validated = $request->validated();

                $attachment = CompanyAttachment::first();
                if (!$attachment) {
                    $attachment = new CompanyAttachment();
                }

                // حقول الملفات
                $fileFields = [
                    'commercial_register',
                    'insurance',
                    'chamber_of_commerce',
                    'balady',
                    'tawteen',
                    'wage_protection',
                    'incorporation_contract',
                    'national_address'
                ];

                // معالجة رفع الملفات
                foreach ($fileFields as $field) {
                    if ($request->hasFile($field)) {
                        // حذف الملف القديم إذا كان موجوداً
                        if ($attachment->{$field}) {
                            $this->deleteOldFile($attachment->{$field});
                        }

                        // رفع الملف الجديد
                        $fileName = $this->uploadFile($request->file($field), $field);
                        $validated[$field] = $fileName;
                    }
                }

                // حقول التواريخ
                $dateFields = [
                    'commercial_register_end_date',
                    'insurance_end_date',
                    'chamber_end_date',
                    'balady_end_date',
                    'tawteen_end_date',
                    'wage_protection_end_date'
                ];

                // معالجة التواريخ
                foreach ($dateFields as $field) {
                    if (array_key_exists($field, $validated)) {
                        $attachment->{$field} = $validated[$field];
                    }
                }

                // حفظ الملفات الجديدة
                foreach ($fileFields as $field) {
                    if (isset($validated[$field])) {
                        $attachment->{$field} = $validated[$field];
                    }
                }

                $attachment->save();

                return $attachment;
            });
        }, 'حدث خطأ أثناء تحديث مرفقات المنشأة');
    }





    /*
    |============================================================================
    |============================================================================
    |                           Private Methods
    |============================================================================
    |============================================================================
    */
    private function uploadFile($file, string $fieldName): string
    {
        $fileName = time() . '_' . $fieldName . '.' . $file->getClientOriginalExtension();
        $file->storeAs('public/company_attachment', $fileName);
        return 'company_attachment/' . $fileName;
    }

    private function deleteOldFile(string $fileName): void
    {
        if (Storage::exists('public/' . $fileName)) {
            Storage::delete('public/' . $fileName);
        }
    }
}
