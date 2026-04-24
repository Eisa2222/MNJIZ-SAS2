<?php

namespace App\Jobs\Tasks;

use App\Tenancy\Concerns\TenantAwareJob;
use App\Traits\HandlesTaskAndEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateTaskJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HandlesTaskAndEvent, TenantAwareJob;

    protected $newTask;
    protected $employeeId;

    public function __construct(array $newTask)
    {
        $this->newTask = $newTask;
        $this->captureTenant();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // تنفيذ إنشاء المهمة
        $this->createTask($this->newTask);
    }
}
