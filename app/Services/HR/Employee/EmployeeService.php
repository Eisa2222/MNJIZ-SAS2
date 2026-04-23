<?php

namespace App\Services\HR\Employee;

use App\Contracts\ErrorHandlerInterface;
use App\Data\Hr\Employee\EmployeeData;
use App\Models\Hr\Employees\Employees;
use App\Models\Hr\Employees\EmployeeSalaryHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;


class EmployeeService
{
    public function __construct(
        private ErrorHandlerInterface $errorHandler
    ) {}



    public function createEmployee(EmployeeData $dto)
    {
        $this->errorHandler->execute(function () use ($dto) {
            return DB::transaction(function () use ($dto) {





                //
                // إنشاء المستخدم
                $user = $this->createUser($dto);

                // تحديث بيانات الموظف
                $dto->user_id    = $user->id;
                $dto->job_title  = $this->getJobTitleFromRoles($dto->roles);

                // تحديد نسبة التأمينات من الإعدادات إذا لزم الأمر
                // $this->setInsurancePercentage($dto);

                // إنشاء الموظف
                $employee = Employees::create($dto->toArray());

                // تسجيل سجل الراتب
                $this->recordSalaryHistory($employee);
            });
        }, 'حدث خطأ أثناء اضافة الموظف ');
    }

    public function archive($employeeId)
    {
        return $this->errorHandler->execute(function () use ($employeeId) {
            return DB::transaction(function () use ($employeeId) {
                $employee = Employees::findOrFail($employeeId);

                if (!$employee->user) {
                    throw ValidationException::withMessages([
                        'status' => "المستخدم غير موجود."
                    ]);
                }

                if ($employee->user->status === 'inactive') {
                    throw ValidationException::withMessages([
                        'status' => "الموظف مؤرشف بالفعل."
                    ]);
                }

                $employee->user->update([
                    'status' => 'inactive',
                ]);
            });
        }, 'حدث خطأ أثناء نقل الموظف للأرشيف');
    }



    public function restore($employeeId)
    {
        return $this->errorHandler->execute(function () use ($employeeId) {
            return DB::transaction(function () use ($employeeId) {
                $employee = Employees::findOrFail($employeeId);

                if (!$employee->user) {
                    throw ValidationException::withMessages([
                        'status' => "المستخدم غير موجود."
                    ]);
                }

                if ($employee->user->status === 'active') {
                    throw ValidationException::withMessages([
                        'status' => "الموظف غير مؤرشف ."
                    ]);
                }

                $employee->user->update([
                    'status' => 'active',
                ]);
            });
        }, 'حدث خطأ أثناء استعادة الموظف من الأرشيف');
    }

    /*
    |============================================================================
    |============================================================================
    |                           Private Methods
    |============================================================================
    |============================================================================
    */
    private function createUser(EmployeeData $dto): User
    {
        $randomPassword = Str::random(12);

        $user = User::create([
            'name'                 => $dto->name,
            'email'                => $dto->work_email,
            'password'             => Hash::make($randomPassword),
            'must_change_password' => false,
            'phone'                => $dto->mobile,
            'status'               => 'active',
            'nationality'          => $dto->nationality,
            'image'                => $dto->profile_picture,
        ]);

        // تعيين الأدوار
        $roles = Role::whereIn('id', (array)$dto->roles)->get();
        $user->assignRole($roles);
        $user->update(['job' => $roles->pluck('name')->join(', ')]);

        return $user;
    }

    private function getJobTitleFromRoles(int $roleId): string
    {
        $role = Role::find($roleId);
        return $role ? $role->name : '';
    }

    //   تحديد نسبة التأمينات
    private function setInsurancePercentage(EmployeeData $dto): void {}

    // تسجيل سجل الراتب
    private function recordSalaryHistory(Employees $employee): void
    {
        EmployeeSalaryHistory::create([
            'employee_id'              => $employee->id,
            'basic_salary'             => $employee->basic_salary ?? 0,
            'transportation_allowance' => $employee->transportation_allowance ?? 0,
            'housing_allowance'        => $employee->housing_allowance ?? 0,
            'other_allowances'         => $employee->other_allowances ?? 0,
            'effective_from'           => Carbon::now(),
        ]);
    }

    /**
     * التحقق من تغيير الراتب
     */
    private function isSalaryChanged(array $oldSalaryData, EmployeeData $dto): bool
    {
        return ($dto->basic_salary ?? $oldSalaryData['basic_salary']) != $oldSalaryData['basic_salary'] ||
            ($dto->transportation_allowance ?? $oldSalaryData['transportation_allowance']) != $oldSalaryData['transportation_allowance'] ||
            ($dto->housing_allowance ?? $oldSalaryData['housing_allowance']) != $oldSalaryData['housing_allowance'] ||
            ($dto->other_allowances ?? $oldSalaryData['other_allowances']) != $oldSalaryData['other_allowances'];
    }
}
