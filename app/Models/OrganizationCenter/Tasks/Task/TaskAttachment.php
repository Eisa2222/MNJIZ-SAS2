<?php

namespace App\Models\OrganizationCenter\Tasks\Task;


use App\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskAttachment extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'task_id',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
    ];
}
