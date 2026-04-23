<?php

namespace App\DTOs\Tasks\Task;

use App\Enums\Tasks\Task\TaskField;
use App\Enums\Tasks\Task\TaskPriority;
use App\Enums\Tasks\Task\TaskStatus;
use App\Models\Task\Task;
use DateTimeInterface;

final class TaskDto
{
    public function __construct(
        public int                $id,
        public string             $task_name,
        public TaskPriority       $priority,
        public ?string            $description,
        public TaskField          $task_field,
        public TaskStatus         $status,
        public string             $due_date,
        public string             $due_time,
        public ?DateTimeInterface $task_start_date,
        public ?DateTimeInterface $task_end_date,
        public int                $created_by,
        public ?int               $completed_by,
        public ?string            $attachment,
        public ?string            $type_task,
        public DateTimeInterface  $created_at,
        public DateTimeInterface  $updated_at,
        public ?DateTimeInterface $deleted_at,
    ) {}

    public static function fromModel(Task $task): self
    {
        return new self(
            id: $task->id,
            task_name: $task->task_name,
            priority: TaskPriority::from($task->priority),
            description: $task->description,
            task_field: TaskField::from($task->task_field),
            status: TaskStatus::from($task->status),
            due_date: $task->due_date->toDateString(),
            due_time: $task->due_time->format('H:i:s'),
            task_start_date: $task->task_start_date,
            task_end_date: $task->task_end_date,
            created_by: $task->created_by,
            completed_by: $task->completed_by,
            attachment: $task->attachment,
            type_task: $task->type_task,
            created_at: $task->created_at,
            updated_at: $task->updated_at,
            deleted_at: $task->deleted_at,
        );
    }
}
