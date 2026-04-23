<?php

namespace App\Models\judicial_affairs;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Document extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;
    protected $dates = ['deleted_at']; 
    protected $fillable = [
        'documentable_id', 'documentable_type', 'file_name', 'file_path', 'file_type',
        'uploaded_by', 'uploaded_at', 'description', 'notes', 'created_by',
        'updated_by', 'deleted_by'
    ];


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['file_name', 'file_type'])
            ->useLogName('document')
            ->setDescriptionForEvent(fn(string $eventName) => "The document has been {$eventName}");
    }


    public function documentable()
    {
        return $this->morphTo();
    }
}
