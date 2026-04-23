<?php

namespace App\Models\Task;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskUser extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | table name 
    |--------------------------------------------------------------------------
    */
    protected $table = 'task_user';



    /*
    |--------------------------------------------------------------------------
    | fillable
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'task_id',
        'user_id',
        'assigned_at',
        'user_attachment',
        'graph_list_id',
        'graph_task_id',
        'graph_event_id'
    ];
}
