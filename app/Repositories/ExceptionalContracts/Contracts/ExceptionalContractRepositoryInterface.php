<?php

namespace App\Repositories\ExceptionalContracts\Contracts;

use App\Repositories\Base\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface ExceptionalContractRepositoryInterface extends BaseRepositoryInterface
{
    // /** فلترة بحسب الصلاحيات والبارامترات */
    // public function datatableQuery(): Builder;

    // /** استرجاع خيارات الحالة */
    // public function statusOptions(): array;

    // /** استعلام الموافقة المعلّقة لموظف معيّن */
    // public function pendingForUser(int $employeeId): Collection;
}
