<?php

namespace App\Models;

use App\Models\LegalAffair\Lawsuit\Lawsuit;
use App\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Memo extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'lawsuit_id',
        'type',
        'text',
        'attachment',
    ];

    public function lawsuit()
    {
        return $this->belongsTo(Lawsuit::class);
    }
}
