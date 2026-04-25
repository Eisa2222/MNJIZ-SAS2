<?php

declare(strict_types=1);

namespace App\Http\Requests\SuperAdmin;

use App\Models\SuperAdmin;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Phase B — Form Request for /super-admin/login.
 *
 * Mirrors the existing Admin\LoginRequest 1:1 but authenticates against
 * the new `super_admin` guard. Two distinct guards / two distinct
 * sessions / two distinct rate-limit keys — but the same admin row in
 * the database backs both, so a single set of credentials works for
 * either entry point.
 *
 * Project conventions honored:
 *   - Form Request (not inline validation)
 *   - Rate limiter keyed by email + IP (Phase 8 `login` config)
 *   - ValidationException with `auth.failed` translation key
 */
final class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'    => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $credentials = $this->only('email', 'password');

        if (! Auth::guard('super_admin')->attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey(), 60);

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        /** @var SuperAdmin $admin */
        $admin = Auth::guard('super_admin')->user();

        if (! $admin->isActive()) {
            Auth::guard('super_admin')->logout();

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        // Stamp last-login fields. Same column layout as legacy admin login.
        $admin->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $this->ip(),
        ])->save();

        RateLimiter::clear($this->throttleKey());
    }

    private function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    private function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')) . '|' . $this->ip());
    }
}
