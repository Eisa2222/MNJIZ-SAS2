<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase G — adds two tracking columns to `subscriptions` so the trial
 * lifecycle commands (`saas:check-trial-expiry`, `saas:send-trial-warnings`)
 * can be idempotently re-run without spamming the operator's mailbox.
 *
 *   trial_warning_sent_at   → last time a trial-expiry warning was sent
 *                              (per-subscription "did we ever warn this one")
 *   trial_expired_notified_at → set the moment the expired email was queued
 *                              (one-shot — re-runs of the expiry command
 *                              that find this column non-null skip the mail)
 *
 * Per-milestone tracking (e.g. "we already sent the 7-day warning, but
 * the 3-day one is still pending") rides in the existing `meta` JSON
 * column under `meta.trial_warning_days_sent = [7, 3, ...]` — Phase 5
 * already gave us that JSON column on subscriptions, so no schema
 * change is required for it.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            if (! Schema::hasColumn('subscriptions', 'trial_warning_sent_at')) {
                $table->timestamp('trial_warning_sent_at')->nullable()->after('trial_ends_at');
            }
            if (! Schema::hasColumn('subscriptions', 'trial_expired_notified_at')) {
                $table->timestamp('trial_expired_notified_at')->nullable()->after('trial_warning_sent_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('subscriptions', 'trial_expired_notified_at')) {
                $table->dropColumn('trial_expired_notified_at');
            }
            if (Schema::hasColumn('subscriptions', 'trial_warning_sent_at')) {
                $table->dropColumn('trial_warning_sent_at');
            }
        });
    }
};
