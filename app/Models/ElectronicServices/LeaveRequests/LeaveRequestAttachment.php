<?php
namespace App\Models\ElectronicServices\LeaveRequests;

use App\Models\ElectronicServices\LeaveRequests\LeaveRequest;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequestAttachment extends Model
{
    use HasFactory;

    protected $table = 'leave_request_attachments';

    protected $fillable = [
        'leave_request_id',
        'file_name',
        'file_path',
    ];

    /**
     * علاقة المرفقات بطلب الإجازة.
     */

    public function leaveRequest()
    {
        return $this->belongsTo(LeaveRequest::class, 'leave_request_id');
    }
}
