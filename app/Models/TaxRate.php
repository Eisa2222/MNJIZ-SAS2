<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'display_name', 'percentage',
        'country', 'region', 'is_active', 'is_default', 'meta',
    ];

    protected $casts = [
        'percentage' => 'decimal:2',
        'is_active'  => 'bool',
        'is_default' => 'bool',
        'meta'       => 'array',
    ];

    public function compute(float $subtotal): float
    {
        return round($subtotal * ((float) $this->percentage / 100), 2);
    }

    public static function defaultActive(): ?self
    {
        return self::query()
            ->where('is_active', true)
            ->where('is_default', true)
            ->first();
    }
}
