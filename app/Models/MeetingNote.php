<?php

namespace App\Models;

use App\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingNote extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'event_id',
        'user_id',
        'meeting_points',
        'meeting_outputs',
        'meeting_field',
        'project_id',
        'lawsuits_id',
        'meeting_name',
        'meeting_start_date',
        'meeting_end_date',
    ];

    /**
     * العلاقة مع المستخدم الذي أنشأ الاجتماع.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getMeetingFieldLabelAttribute()
    {
        switch ($this->meeting_field) {
            case 'projects':
                return 'مشاريع';
            case 'lawsuits':
                return 'دعاوى';
            case 'public':
                return 'عام';
            default:
                return 'غير محدد'; // قيمة افتراضية إذا كانت القيمة غير معروفة
        }
    }

    public function attendeeMeetings()
    {
        return $this->hasMany(AttendeeMeeting::class, 'meeting_id');
    }


    public function getParticipantTypes()
    {
        return $this->attendeeMeetings()->pluck('type')->unique()->toArray();
    }

}
