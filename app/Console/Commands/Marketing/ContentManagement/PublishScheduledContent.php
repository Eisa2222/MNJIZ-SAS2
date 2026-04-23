<?php

namespace App\Console\Commands\Marketing\ContentManagement;

use Illuminate\Console\Command;
use App\Models\Marketing\ContentManagement\SocialPublication\SocialPublication;
use Carbon\Carbon;
use App\Jobs\Marketing\ContentManagement\PublishContentJob;
use Illuminate\Support\Facades\Log;

class PublishScheduledContent extends Command
{
    protected $signature = 'content:publish-scheduled';
    protected $description = 'Queue due social publications';

    public function handle(): int
    {
        $now = Carbon::now();
        Log::info('[Cron] بدأ التنفيذ', ['now' => $now->toDateTimeString()]);

        $due = SocialPublication::readyToPublish()->get();


        Log::info('[Cron] عدد الصفوف المستحقّة', ['count' => $due->count()]);

        foreach ($due as $attempt) {
            dispatch(new PublishContentJob($attempt));
        }

        return Command::SUCCESS;
    }
}
