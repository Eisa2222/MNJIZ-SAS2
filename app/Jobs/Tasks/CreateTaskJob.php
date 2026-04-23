<?php

namespace App\Jobs\Tasks;

use App\Traits\HandlesTaskAndEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateTaskJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HandlesTaskAndEvent;

    protected $newTask;
    protected $employeeId;

    /**
     * Create a new job instance.
     *
     * @param array $newTask
     * @param int $employeeId
     */
    public function __construct(array $newTask)
    {
        $this->newTask = $newTask;
        // $this->employeeId = $employeeId;
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
