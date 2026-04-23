<?php

namespace App\Console\Commands;

use App\Services\SessionReminderService;
use Illuminate\Console\Command;
use App\Models\judicial_affairs\Session;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendSessionReminders extends Command
{
    /*
    |--------------------------------------------------------------------------
    | Signature
    |--------------------------------------------------------------------------
    */
    // protected $signature = 'sessions:send-reminders';

    // فى SendSessionReminders.php
    protected $signature = 'sessions:send-reminders {--force-objection : Send objection reminders immediately}';


    /*
    |--------------------------------------------------------------------------
    | Description
    |--------------------------------------------------------------------------
    */
    protected $description = 'Send reminders (SMS and Email) to assigned employees 30 minutes before the session time';

    /*
    |--------------------------------------------------------------------------
    | Reminder Services
    |--------------------------------------------------------------------------
    */
    protected $reminderService;

    /*
    |--------------------------------------------------------------------------
    | Construct
    |--------------------------------------------------------------------------
    */
    public function __construct(SessionReminderService $reminderService)
    {
        parent::__construct();
        $this->reminderService = $reminderService;
    }

    /*
    |--------------------------------------------------------------------------
    | Handle
    |--------------------------------------------------------------------------
    */
    public function handle()
    {
        try {
            $now = Carbon::now();
            $targetTimeStart = $now->copy()->addMinutes(30);
            $targetTimeEnd = $now->copy()->addMinutes(31);

            // استرجاع الجلسات المطابقة للمعايير
            $sessions = Session::whereDate('session_date', $targetTimeStart->format('Y-m-d'))
                ->whereTime('session_time', '>=', $targetTimeStart->format('H:i:s'))
                ->whereTime('session_time', '<=', $targetTimeEnd->format('H:i:s'))
                ->whereNull('reminder_sent_at')
                ->get();

            $this->info("Found {$sessions->count()} sessions to send reminders for.");

            $totalSent = 0;
            $totalFailed = 0;

            foreach ($sessions as $session) {
                $results = $this->reminderService->sendAllReminders($session);

                if ($results['success']) {
                    $totalSent++;
                    $this->info("✓ Successfully sent reminders for session: {$session->session_name}");

                    $session->reminder_sent_at = Carbon::now();
                    $session->save();
                } else {
                    $totalFailed++;
                    $this->error("✗ Failed to send reminders for session: {$session->session_name}");
                }

                // تفاصيل النتائج لكل قناة
                if (!empty($results['sms']['recipients'])) {
                    $this->line("  - SMS sent to " . count($results['sms']['recipients']) . " recipients");
                }
                if (!empty($results['email']['recipients'])) {
                    $this->line("  - Email sent to " . count($results['email']['recipients']) . " recipients");
                }
                if (!empty($results['sms']['failed']) || !empty($results['email']['failed'])) {
                    $this->line("  - Failed: SMS (" . count($results['sms']['failed']) . "), Email (" . count($results['email']['failed']) . ")");
                }
            }

            /*===========================================================
            | ② فحص وإرسال تذكير «آخر مهلة للاعتراض» (‑30 دقيقة)
            *==========================================================*/
            
            $objectionSessions = Session::where('summary_report_status', 'حكم موضوعي')
                ->whereNotNull('last_objection_deadline')
                ->whereNull('objection_reminder_sent_at')
                ->get();

            $this->info("Found {$objectionSessions->count()} objection‑deadline sessions.");

            foreach ($objectionSessions as $session) {
                // Only send if 30 minutes remain (internal check)
                $this->reminderService->maybeSendObjectionReminder($session);
            }
            
            /*===========================================================
            | ② فحص وإرسال تذكير «آخر مهلة للاعتراض» (‑30 دقيقة)
            *==========================================================*/

            $this->info("Session reminders processing completed.");
            $this->info("Summary: Sent: {$totalSent}, Failed: {$totalFailed}, Total: {$sessions->count()}");

            return 0;
        } catch (\Exception $e) {
            Log::error('An unexpected error occurred in SendSessionReminders', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error('An error occurred while processing the session reminders: ' . $e->getMessage());
            return 1;
        }
    }
}