<?php

namespace App\Traits;

use App\Jobs\Tasks\SyncTaskWithMicrosoftJob;
use App\Jobs\Tasks\BatchDeleteMicrosoftTaskJob;
use App\Models\GeneralSetting\SystemSetting\Settings;
use App\Models\Task\Task;
use App\Models\Task\TaskEvent;
use App\Models\User;
use App\Notifications\TaskCreatedNotification;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Facades\Log;

trait HandlesTaskAndEvent
{
    /**
     * إنشاء المهمة في النظام
     *
     * @param array $newTask
     * @param int $employeeId
     * @return \App\Models\Task\Task
     */
    private function createTask(array $newTask): Task
    {
        // بدء معاملة قاعدة البيانات
        DB::beginTransaction();

        try {
            // إنشاء المهمة
            $task = Task::create([
                'status' => 'in_progress',
                'task_name' => $newTask['task_name'],
                'priority' => $newTask['priority'],
                'description' => $newTask['description'],
                'task_field' => $newTask['task_field'],
                'offer_id' => $newTask['offer_id'] ?? null,
                'contract_id' => $newTask['contract_id'] ?? null,
                'project_id' => $newTask['project_id'] ?? null,
                'lawsuit_id' => $newTask['lawsuit_id'] ?? null,
                'session_id' => $newTask['session_id'] ?? null,
                'power_of_attorney_id' => $newTask['power_of_attorney_id'] ?? null,
                'due_date' => $newTask['due_date'] ?? Carbon::now()->toDateString(), // للحصول على التاريخ الحالي
                'due_time' => $newTask['due_time'] ?? Carbon::now()->toTimeString(), // للحصول على الوقت الحالي
                'created_by' => $newTask['created_by'],
                'type_task' => $newTask['type_task'] ?? null,
                'task_start_date' => now(),
            ]);

            TaskEvent::create([
                'task_id' => $task->id,
                'user_id' => $newTask['created_by'],
                'event_type' => 'create',
                'message' => 'تم إضافة المهمة'
            ]);



            if (!empty($newTask['assigned_user_id'])) {
                $task->assignedUsers()->attach($newTask['assigned_user_id']);
            }

            // تحويل التاريخ ليتناسب مع مايكروسوفت
            $due_date_iso = Carbon::parse($task->due_date . ' ' . $task->due_time, 'Asia/Riyadh')->toIso8601String();

            $taskData = [
                // البيانات الخاصة بالمهمة
                'task_id' => $task->id,
                'task_name' => $task->task_name,
                'task_priority' => $task->priority_in_arabic,
                'description' => $task->description ?? null,
                'endDateTime' => $due_date_iso,
                'addDateTime' => $due_date_iso,
                'showEndDate' => false,

            ];

            // ارسال الاشعار
            foreach ($task->assignedUsers as $assignee) {
                $assignee->notify(new \App\Notifications\TaskCreatedNotification($task));
            }


            $this->addTaskAndEventAndSendEmail($task->assignedUsers, $taskData);


            // إنهاء المعاملة
            DB::commit();

            return $task;
        } catch (\Exception $e) {
            // التراجع عن المعاملة في حالة وجود خطأ
            DB::rollBack();
            throw $e;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | addTaskAndEventAndSendEmail
    |--------------------------------------------------------------------------
    | لاضافة المهمة و الحدث في مايكروسوفت
    */
    private function addTaskAndEventAndSendEmail($assignedUser, $taskData)
    {
        SyncTaskWithMicrosoftJob::dispatch($assignedUser, $taskData, Settings::find(1)->office_name);
    }


    // delete Contrat
    private function deleteTask($tasks, $model)
    {
        foreach ($tasks as $task) {
            // تحويل البيانات إلى المنسق المطلوب
            $assignedUsersArray = [];

            foreach ($task->assignedUsers as $user) {
                $assignedUsersArray[] = [
                    'email' => $user->email,
                    'graph_task_id' => optional($user->pivot)->graph_task_id,
                    'graph_list_id' => optional($user->pivot)->graph_list_id,
                    'graph_event_id' => optional($user->pivot)->graph_event_id
                ];
            }

            app(Dispatcher::class)
                ->dispatchNow(new BatchDeleteMicrosoftTaskJob(
                    task: $task,
                    removedUsersData: $assignedUsersArray
                ));
        }

        $model->forceDelete();
    }


    // private function justDeleteTask($task)
    // {
    //     // جلب كل المعيّنين
    //     $assignees = $task->assignedUsers;
    //     foreach ($assignees as $assignee) {
    //         app(\Illuminate\Contracts\Bus\Dispatcher::class)
    //             ->dispatchNow(new BatchDeleteMicrosoftTaskJob($task, $assignee));
    //     }
    // }
}
