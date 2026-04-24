<?php

namespace App\Models\judicial_affairs;

use App\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectAttachment extends Model
{
    use HasFactory, BelongsToTenant;
    // protected $dates = ['deleted_at']; 
    protected $fillable = [
        'project_id',
        'attachment_name',
        'attachment_file',
        'attachment_type',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

}
