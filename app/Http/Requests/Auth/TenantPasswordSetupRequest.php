<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Phase F — validates the POST /password/setup payload.
 *
 * The signed URL signature + per-token hash check are enforced inside
 * the controller (they're not Form Request concerns). This Request only
 * validates the SHAPE of the new-password input.
 *
 * authorize() returns true — the URL signature itself is the
 * authorisation gate.
 */
final class TenantPasswordSetupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'                 => ['required', 'email', 'max:191'],
            'token'                 => ['required', 'string', 'size:64'],   // 32 random bytes hex
            'password'              => ['required', 'confirmed', Password::min(8)],
            'password_confirmation' => ['required', 'string'],
        ];
    }
}
