<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/**
 * Platform-wide setting. Central-only — never scoped by tenant.
 *
 * Encryption: when is_encrypted=true the stored column is cipher-text; the
 * $value attribute accessor transparently decrypts. Keep raw ciphertext with
 * getRawValue() if you need to debug.
 *
 * Cast: string|int|bool|json|array
 */
class CentralSetting extends Model
{
    use HasFactory;

    protected $table = 'central_settings';

    protected $fillable = [
        'key',
        'value',
        'group',
        'is_encrypted',
        'cast',
        'description',
    ];

    protected $casts = [
        'is_encrypted' => 'bool',
    ];

    public function getValueAttribute($raw): mixed
    {
        if ($raw === null) {
            return null;
        }

        $value = $this->is_encrypted ? $this->tryDecrypt($raw) : $raw;

        return $this->castOut($value);
    }

    public function setValueAttribute($value): void
    {
        if ($value === null) {
            $this->attributes['value'] = null;

            return;
        }

        $serialized = $this->castIn($value);

        $this->attributes['value'] = $this->is_encrypted
            ? Crypt::encryptString((string) $serialized)
            : $serialized;
    }

    public function getRawValue(): ?string
    {
        return $this->attributes['value'] ?? null;
    }

    protected function castIn(mixed $value): string
    {
        return match ($this->cast) {
            'json', 'array' => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'bool'          => $value ? '1' : '0',
            'int'           => (string) (int) $value,
            default         => (string) $value,
        };
    }

    protected function castOut(string $value): mixed
    {
        return match ($this->cast) {
            'json', 'array' => json_decode($value, true),
            'bool'          => (bool) $value,
            'int'           => (int) $value,
            default         => $value,
        };
    }

    protected function tryDecrypt(string $raw): string
    {
        try {
            return Crypt::decryptString($raw);
        } catch (\Throwable $e) {
            report($e);

            return '';
        }
    }
}
