<?php

namespace App\Models\OperationsCenter\Offer;

use App\Models\Hr\Employees\Employees;
use Illuminate\Database\Eloquent\Model;

class OfferApprovalLog extends Model
{
    // اسم الجدول في قاعدة البيانات (إن كان مختلفًا عن الافتراضي)
    protected $table = 'offer_approval_logs';

    // الحقول المسموح بتعبئتها
    protected $fillable = [
        'offer_id',
        'employee_id',
        'action',
        'reason',
    ];

    /**
     * العلاقة مع نموذج العروض (Offers)
     */
    public function offer()
    {
        return $this->belongsTo(Offers::class, 'offer_id');
    }

    /**
     * العلاقة مع الموظف (المعتمد)
     */
    public function employee()
    {
        return $this->belongsTo(Employees::class, 'employee_id');
    }
}