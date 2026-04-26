<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    /*
    |--------------------------------------------------------------------------
    | Schedule
    |--------------------------------------------------------------------------
    | Define the application's command schedule.
    */
    protected function schedule(Schedule $schedule): void
    {
        // جدولة الأمر ليعمل يوميًا في منتصف الليل
        // يحتاج مراجعة
        $schedule->command('powerattorneys:update-expired')->dailyAt('00:00');

        // $schedule->command('sms:send-session-reminders')->everyMinute()->withoutOverlapping();

        $schedule->command('sessions:send-reminders')->everyMinute()->withoutOverlapping();

        /*
        |--------------------------------------------------------------------------
        | Generate Payroll
        |--------------------------------------------------------------------------
        */
        $schedule->command('payroll:generate-wps')->daily();

        /*
        |--------------------------------------------------------------------------
        | Daily Leave Accrual For Leave Balances MAnagement
        |--------------------------------------------------------------------------
        */
        $schedule->command('leave:daily-accrual')->dailyAt('00:05')->withoutOverlapping();

        // الخاص بالتحضير
        $schedule->command('attendance:generate-daily-attendance')
            ->dailyAt('02:00')
            ->onOneServer();

        /*
        |--------------------------------------------------------------------------
        | Send Expiry Reminders For Hr Section
        |--------------------------------------------------------------------------
        */
        $schedule->command('hr:send-expiry-reminders')->daily()->withoutOverlapping()->onOneServer();

        //  تذكيرات الموارد البشرية
        $schedule->command('hr:send-expiry-reminders')
            ->daily()
            ->withoutOverlapping()
            ->onOneServer();


        // نشر المحتوى على منصات التواصل
        $schedule->command('content:publish-scheduled')
            ->everyMinute()
            ->withoutOverlapping()
            ->runInBackground();


        // تنبيهات دفعات العقود
        $schedule->command('finance:send-payment-reminders')
            ->daily()
            ->withoutOverlapping()
            ->onOneServer();

        /*
        |--------------------------------------------------------------------------
        | SaaS Billing (Phase 5)
        |--------------------------------------------------------------------------
        | Runs after the day boundary so we don't race end-of-day invoices.
        */
        $schedule->command('billing:charge-due-subscriptions')
            ->dailyAt('00:10')
            ->withoutOverlapping()
            ->onOneServer();

        $schedule->command('billing:mark-past-due')
            ->dailyAt('00:15')
            ->withoutOverlapping()
            ->onOneServer();

        $schedule->command('billing:expire-grace-period')
            ->dailyAt('00:20')
            ->withoutOverlapping()
            ->onOneServer();

        $schedule->command('billing:send-renewal-reminders')
            ->dailyAt('09:00')
            ->withoutOverlapping()
            ->onOneServer();

        /*
        |--------------------------------------------------------------------------
        | SaaS Trial Lifecycle (Phase G)
        |--------------------------------------------------------------------------
        | Two daily commands keep trialing subscriptions in sync with the
        | operator-facing trial settings (Phase C SystemSetting Trial tab).
        |
        |   00:00 — saas:check-trial-expiry     (runs BEFORE the Phase 5
        |                                        00:10–00:20 billing window)
        |   08:00 — saas:send-trial-warnings    (runs BEFORE renewals 09:00)
        |
        | Both are idempotent — see the command class doc-blocks for the
        | per-row tracking columns + `meta.trial_warning_days_sent` array.
        */
        $schedule->command('saas:check-trial-expiry')
            ->dailyAt('00:00')
            ->withoutOverlapping()
            ->onOneServer();

        $schedule->command('saas:send-trial-warnings')
            ->dailyAt('08:00')
            ->withoutOverlapping()
            ->onOneServer();
    }


    protected $commands = [
        \App\Console\Commands\HR\WPS\GeneratePayrollCommand::class,
    ];


    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
