<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalCase extends Model
{
    use HasFactory;
    protected $fillable = [
        'client_id',
        'category_id',
        'opponent_name',
        'opponent_phone',
        'opponent_address',
        'opponent_lawyer',
        'opponent_lawyer_phone',
        'case_category_id',
        'lawyer_id',
        'case_subject',
        'litigation_stage_id',
        'case_status',
        'court_id',
        'court_case_number',
        'contract_date',
        'contract_value',
        'tax',
        'total_amount_including_tax',
        'contract_terms',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // علاقة مع تصنيف الموكل (Category)
    public function category()
    {
        return $this->belongsTo(Categories::class);
    }

    // علاقة مع تصنيف القضية (Category)
    public function caseCategory()
    {
        return $this->belongsTo(Categories::class, 'case_category_id');
    }

    // علاقة مع المحامي (User)
    public function lawyer()
    {
        return $this->belongsTo(User::class, 'lawyer_id');
    }

    // علاقة مع مرحلة التقاضي (LitigationStage)
    public function litigationStage()
    {
        return $this->belongsTo(LitigationStage::class);
    }

    // علاقة مع المحكمة (Court)
    public function court()
    {
        return $this->belongsTo(Court::class);
    }
}
