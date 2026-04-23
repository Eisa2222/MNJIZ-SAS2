<?php

namespace App\Services\HR\CompanyPolicy;


use App\Contracts\ErrorHandlerInterface;
use App\Data\Hr\Companypolicy\CompanypolicyData;
use App\Data\Hr\Companypolicy\CompanypolicyUpdateData;
use App\Models\Hr\CompanyPolicy\CompanyPolicy;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CompanyPolicyService
{
    public function __construct(private ErrorHandlerInterface $errorHandler) {}

    public function create(CompanypolicyData $dto): void
    {
        $this->errorHandler->execute(function () use ($dto) {
            return DB::transaction(function () use ($dto) {

                foreach ($dto->name as $key => $name) {
                    if (isset($dto->file[$key])) {
                        $doc = new CompanyPolicy([
                            'name'           => $name,
                            'file_path'      => $this->uploadFile($dto->file[$key]),
                            'is_mandatory'   => $dto->is_mandatory[$key] ?? false,
                            'created_by'     => $this->currentEmployeeId(),
                        ]);

                        $doc->save();
                    }
                }
            });
        }, 'حدث خطأ أثناء حفظ السياسات واللوائح');
    }

    public function update(int $id, CompanypolicyUpdateData $dto): void
    {
        $this->errorHandler->execute(function () use ($id, $dto) {
            return DB::transaction(function () use ($id, $dto) {
                $doc = CompanyPolicy::findOrFail($id);

                $updateData = [
                    'name'          => $dto->name,
                    'is_mandatory'  => $dto->is_mandatory,
                    'updated_by'    => $this->currentEmployeeId(),
                ];

                if ($dto->file) {

                    $this->deleteFile($doc->file_path);

                    $updateData['file_path'] = $this->uploadFile($dto->file);
                }

                $doc->update($updateData);
            });
        }, 'حدث خطأ أثناء تحديث السياسات واللوائح');
    }

    public function delete(int $id): bool
    {
        return $this->errorHandler->execute(function () use ($id) {
            return DB::transaction(function () use ($id) {
                $doc = CompanyPolicy::findOrFail($id);

                $this->deleteFile($doc->file_path);

                return $doc->delete();
            });
        }, 'حدث خطأ أثناء حذف السياسات واللوائح');
    }

    /*
    |============================================================================
    |                          Private Helper Methods
    |============================================================================
    */
    private function uploadFile(UploadedFile $file): string
    {
        return $file->store('company_policy', 'public');
    }

    private function deleteFile(?string $filePath): void
    {
        if ($filePath && Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
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
