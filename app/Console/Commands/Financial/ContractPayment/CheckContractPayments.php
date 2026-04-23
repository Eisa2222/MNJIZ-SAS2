<?php

namespace App\Console\Commands\Financial\ContractPayment;

use App\Enums\OperationsCenter\Contract\Payment\PaymentStatus;
use App\Models\OperationsCenter\Contract\Payment\ContractPayment;
use App\Models\User;
use App\Services\OrganizationCenter\Tasks\Task\TaskService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class CheckContractPayments extends Command
{
    protected $signature   = 'finance:send-payment-reminders';

    protected $description = 'إنشاء مهام تذكير بدفعات العقود المستحقّة وتحديث حالتها تلقائياً';

    public function __construct(protected TaskService $taskService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $leadDays           = (int) Config::get('reminders.contract_payments.remind_before_days', 0);
        $targetPermissions  =       Config::get('reminders.contract_payments.notify_permissions', []);

        $targetUserIds = User::permission($targetPermissions)->pluck('id')->toArray();


        if (empty($targetUserIds)) {
            $this->error('لا يوجد مستخدمون لديهم الصلاحيات المطلوبة للتنبيه.');
            return 1;
        }

        $today      = Carbon::today();

        $targetDate = $today->copy()->addDays($leadDays)->toDateString();

        $affected = ContractPayment::where('status', PaymentStatus::Scheduled)
            ->whereDate('due_date', '<', $today)
            ->update([
                'status'     => PaymentStatus::Late,
            ]);

        if ($affected) {
            $this->info("تم تحويل {$affected} دفعة إلى حالة متأخرة.");
        }


        $duePayments = ContractPayment::where('status', PaymentStatus::Scheduled)->whereDate('due_date', $targetDate)
            ->get();

        if ($duePayments->isEmpty()) {
            $this->info('لا توجد دفعات مستحقة للتنبيه اليوم.');
            return 0;
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
                    'task_name'   => "تذكير: دفعة مستحقة للعقد رقم ({$contract->contract_number}) - العميل: {$customer}",
                    'priority'    => 'high',
                    'description' => "دفعة بقيمة {$amountTxt} تستحق بتاريخ {$pay->due_date->format('Y-m-d')}.\n"
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
            $this->error("حدث خطأ أثناء إنشاء المهام: {$e->getMessage()}");
            return 1;
        }

        $this->info("تم إنشاء مهام تذكير لعدد {$duePayments->count()} دفعة.");
        return 0;
    }
}
