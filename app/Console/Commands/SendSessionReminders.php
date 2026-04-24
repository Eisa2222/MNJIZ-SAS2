<?php

namespace App\Console\Commands;

use App\Console\Concerns\IteratesTenants;
use App\Models\LegalAffair\Session\Session;
use App\Models\Tenant;
use App\Services\SessionReminderService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Fires every minute via Kernel::schedule('sessions:send-reminders').
 *
 * Iterates tenants so each firm sees ONLY its own sessions — a global loop
 * here would leak one firm's docket into another's staff inbox.
 *
 * NOTE: The prior version imported App\Models\judicial_affairs\Session which
 * no longer exists in the codebase. Restored to the real namespace.
 */
class SendSessionReminders extends Command
{
    use IteratesTenants;

    protected $signature = 'sessions:send-reminders {--force-objection : Send objection reminders immediately}';

    protected $description = 'Send reminders (SMS and Email) to assigned employees 30 minutes before the session time (per tenant)';

    public function __construct(protected SessionReminderService $reminderService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->perTenant(function (Tenant $tenant) {
            try {
                $this->runForTenant($tenant);
            } catch (\Throwable $e) {
                Log::error('SendSessionReminders failed for tenant', [
                    'tenant_id' => $tenant->id,
                    'tenant'    => $tenant->slug,
                    'message'   => $e->getMessage(),
                ]);
                $this->error("[tenant:{$tenant->slug}] خطأ: {$e->getMessage()}");
            }
        });

        return self::SUCCESS;
    }

    private function runForTenant(Tenant $tenant): void
    {
        $now             = Carbon::now();
        $targetTimeStart = $now->copy()->addMinutes(30);
        $targetTimeEnd   = $now->copy()->addMinutes(31);

        // Every query below is TenantScoped by BelongsToTenant on Session.
        $sessions = Session::whereDate('session_date', $targetTimeStart->format('Y-m-d'))
            ->whereTime('session_time', '>=', $targetTimeStart->format('H:i:s'))
            ->whereTime('session_time', '<=', $targetTimeEnd->format('H:i:s'))
            ->whereNull('reminder_sent_at')
            ->get();

        if ($sessions->isNotEmpty()) {
            $this->info("[tenant:{$tenant->slug}] Found {$sessions->count()} session(s) to remind.");
        }

        $totalSent   = 0;
        $totalFailed = 0;

        foreach ($sessions as $session) {
            $results = $this->reminderService->sendAllReminders($session);

            if ($results['success'] ?? false) {
                $totalSent++;
                $session->reminder_sent_at = Carbon::now();
                $session->save();
            } else {
                $totalFailed++;
            }
        }

        // Objection-deadline reminders (tenant-scoped).
        $objectionSessions = Session::where('summary_report_status', 'حكم موضوعي')
            ->whereNotNull('last_objection_deadline')
            ->whereNull('objection_reminder_sent_at')
            ->get();

        foreach ($objectionSessions as $session) {
            $this->reminderService->maybeSendObjectionReminder($session);
        }

        if ($totalSent + $totalFailed > 0) {
            $this->info("[tenant:{$tenant->slug}] Summary — sent: {$totalSent}, failed: {$totalFailed}");
        }
    }
}
