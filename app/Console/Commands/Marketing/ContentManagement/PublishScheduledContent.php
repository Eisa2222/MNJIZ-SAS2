<?php

namespace App\Console\Commands\Marketing\ContentManagement;

use App\Console\Concerns\IteratesTenants;
use App\Jobs\Marketing\ContentManagement\PublishContentJob;
use App\Models\Marketing\ContentManagement\SocialPublication\SocialPublication;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Scheduled every minute — picks up due social-media posts and dispatches
 * PublishContentJob for each. Iterates tenants so one firm's content
 * cannot be posted under another firm's LinkedIn/Twitter credentials.
 */
class PublishScheduledContent extends Command
{
    use IteratesTenants;

    protected $signature = 'content:publish-scheduled';
    protected $description = 'Queue due social publications (per tenant)';

    public function handle(): int
    {
        $this->perTenant(function (Tenant $tenant) {
            $now = Carbon::now();
            $due = SocialPublication::readyToPublish()->get();

            if ($due->isNotEmpty()) {
                Log::info('[Cron content:publish] due', [
                    'tenant' => $tenant->slug,
                    'now'    => $now->toDateTimeString(),
                    'count'  => $due->count(),
                ]);
            }

            foreach ($due as $attempt) {
                dispatch(new PublishContentJob($attempt));
            }
        });

        return Command::SUCCESS;
    }
}
