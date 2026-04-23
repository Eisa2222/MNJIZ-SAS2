<?php

namespace App\Services\OperationsCenter\ExceptionalContract;

use App\Models\OperationsCenter\ExceptionalContract\ExceptionalContract;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ExceptionalContractService
{
    /**
     * Create a new exceptional contract.
     *
     * @param array $data
     * @return ExceptionalContract
     */
    public function createContract(array $data): ExceptionalContract
    {
        $data['created_by_id'] = Auth::user()->employee->id;
        $data['status']        = ExceptionalContract::STATUS_PENDING;

        return ExceptionalContract::create($data);
    }

    /**
     * Update an existing exceptional contract and reset its approvals.
     *
     * @param ExceptionalContract $contract
     * @param array $data
     * @return ExceptionalContract
     */
    public function updateContract(ExceptionalContract $contract, array $data): ExceptionalContract
    {
        $data['updated_by_id'] = Auth::user()->employee->id;
        $contract->update($data);

        $contract->resetApprovals();
        $contract->changeStatus(ExceptionalContract::STATUS_PENDING);

        return $contract;
    }

    /**
     * Generate processed HTML for show template.
     *
     * @param ExceptionalContract $contract
     * @return string
     */
    public function renderTemplate(ExceptionalContract $contract): string
    {
        $data = [
            'project_name'  => $contract->project_name,
            'customer_name' => $contract->customer->name,
            'employee_name' => $contract->employee->raw_name,
            'scope_of_work' => $contract->scope_of_work,
            'reasons'       => $contract->reasons,
            'equivalent'    => $contract->equivalent,
            'current_date'  => Carbon::now(),
        ];

        return view('operations_center.exceptional_contract.templates.show_template', $data)
            ->render();
    }
}
