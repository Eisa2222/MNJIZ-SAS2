<?php

namespace App\Repositories\ExceptionalContracts\Eloquent;

use App\Models\OperationsCenter\ExceptionalContract\ExceptionalContract;
use App\Repositories\Base\Eloquent\BaseRepository;
use App\Repositories\ExceptionalContracts\Contracts\ExceptionalContractRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ExceptionalContractRepository extends BaseRepository
implements ExceptionalContractRepositoryInterface
{
    public function __construct(ExceptionalContract $model)
    {
        parent::__construct($model);
    }

    /* ------------------------------------------------------------------ */
    /*   استعلام DataTable                                                */
    /* ------------------------------------------------------------------ */
    public function datatableQuery(): Builder
    {
        return $this->model
            ->with(['customer', 'employee'])
            ->latest();
    }

    /* ------------------------------------------------------------------ */
    public function statusOptions(): array
    {
        return ExceptionalContract::getStatusOptions();
    }

    /* ------------------------------------------------------------------ */
    public function pendingForUser(int $employeeId): Collection
    {
        return $this->model->whereHas('approvals', function ($q) use ($employeeId) {
            $q->where('approver_id', $employeeId)
                ->where('status', ExceptionalContract::STATUS_PENDING);
        })->get();
    }
}
