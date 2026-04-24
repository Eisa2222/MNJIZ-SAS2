<?php

namespace App\Models;

use App\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendeeMeeting extends Model
{
    use HasFactory, BelongsToTenant;


    protected $fillable = [
        'meeting_id',
        'email',
        'type',
    ];

    /**
     * علاقة المدعوين مع الاجتماع.
     */
    public function meeting()
    {
        return $this->belongsTo(MeetingNote::class, 'meeting_id');
    }

    
}
