<?php

namespace App\Console\Commands\Financial\ContractPayment;

use App\Console\Concerns\IteratesTenants;
use App\Enums\OperationsCenter\Contract\Payment\PaymentStatus;
use App\Models\OperationsCenter\Contract\Payment\ContractPayment;
use App\Models\Tenant;
use App\Models\User;
use App\Services\OrganizationCenter\Tasks\Task\TaskService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class CheckContractPayments extends Command
{
    use IteratesTenants;

    protected $signature   = 'finance:send-payment-reminders';

    protected $description = 'إنشاء مهام تذكير بدفعات العقود المستحقّة وتحديث حالتها تلقائياً (لكل tenant)';

    public function __construct(protected TaskService $taskService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->perTenant(function (Tenant $tenant) {
            $this->info("[tenant:{$tenant->slug}] === بدء فحص دفعات العقود ===");
            $this->runForTenant($tenant);
        });

        return self::SUCCESS;
    }

    private function runForTenant(Tenant $tenant): void
    {
        $leadDays           = (int) Config::get('reminders.contract_payments.remind_before_days', 0);
        $targetPermissions  =       Config::get('reminders.contract_payments.notify_permissions', []);

        // Users are tenant-scoped via global scope; permissions attach via Spatie on tenant team.
        $targetUserIds = User::permission($targetPermissions)->pluck('id')->toArray();

        if (empty($targetUserIds)) {
            $this->warn("[tenant:{$tenant->slug}] لا يوجد مستخدمون لديهم الصلاحيات المطلوبة — تخطّي.");
            return;
        }

        $today      = Carbon::today();
        $targetDate = $today->copy()->addDays($leadDays)->toDateString();

        // Scoped update — TenantScope appends WHERE tenant_id = current tenant.
        $affected = ContractPayment::where('status', PaymentStatus::Scheduled)
            ->whereDate('due_date', '<', $today)
            ->update([
                'status' => PaymentStatus::Late,
            ]);

        if ($affected) {
            $this->info("[tenant:{$tenant->slug}] تم تحويل {$affected} دفعة إلى حالة متأخرة.");
        }

        $duePayments = ContractPayment::where('status', PaymentStatus::Scheduled)
            ->whereDate('due_date', $targetDate)
            ->get();

        if ($duePayments->isEmpty()) {
            $this->info("[tenant:{$tenant->slug}] لا توجد دفعات مستحقة للتنبيه اليوم.");
            return;
        }

        DB::beginTransaction();
        try {
            foreach ($duePayments as $pay) {
                $contract   = $pay->contract;
                $customer   = $contract->customer?->name ?? '-';
                $amountTxt  = $pay->calculation_type->isPercentage()
                    ? "{$pay->percentage} % من قيمة العقد"
                    : number_format($pay->fixed_amount, 2) . ' ' . $pay->currency;

                $data = [
                    'task_name'         => "تذكير: دفعة مستحقة للعقد رقم ({$contract->contract_number}) - العميل: {$customer}",
                    'priority'          => 'high',
                    'description'       => "دفعة بقيمة {$amountTxt} تستحق بتاريخ {$pay->due_date->format('Y-m-d')}.\n"
                        . "رقم العقد: {$contract->contract_number}.",
                    'task_field'        => 'contract_payments',
                    'due_date'          => $pay->due_date->toDateString(),
                    'due_time'          => Carbon::now()->format('H:i:s'),
                    'status'            => 'pending',
                    'assigned_user_ids' => $targetUserIds,
                    'task_start_date'   => Carbon::now()->toDateTimeString(),
                ];

                $this->taskService->create($data);
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("[tenant:{$tenant->slug}] حدث خطأ أثناء إنشاء المهام: {$e->getMessage()}");
            return;
        }

        $this->info("[tenant:{$tenant->slug}] تم إنشاء مهام تذكير لعدد {$duePayments->count()} دفعة.");
    }
}
