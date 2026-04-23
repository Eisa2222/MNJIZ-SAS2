<?php

namespace App\Models\LegalAffair\Opponent;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpponentAuthorization extends Model
{
    use HasFactory;

    protected $table = 'opponent_authorizations'; // اسم الجدول الجديد

    protected $fillable = [
        'opponent_id',
        'name',
        'identity_number',
        'phone',
        'email',
    ];

    public function opponent()
    {
        return $this->belongsTo(Opponent::class, 'opponent_id');
    }
}
