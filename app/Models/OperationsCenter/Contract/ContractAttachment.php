<?php

namespace App\Models\OperationsCenter\Contract;

use App\Models\OperationsCenter\Contract\Contract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',  // معرف العقد المرتبط
        'name',         // اسم المرفق
        'attachment',   // مسار الملف المرفق
        'file_id',
        'file_url',
        'share_link',
        'download_url'
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
}
