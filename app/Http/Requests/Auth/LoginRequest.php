<?php

namespace App\Http\Requests\Auth;

use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // تحديد الحقل الذي سيتم استخدامه: email أو phone
        $credentials = filter_var($this->input('email'), FILTER_VALIDATE_EMAIL) ? ['email' => $this->input('email')] : ['phone' => $this->input('email')];
        $credentials['password'] = $this->input('password');

        // ─── Hotfix: Tenant-Aware Login ───────────────────────────────
        // Root cause: User uses BelongsToTenant global scope. The default
        // `Auth::attempt()` queries via Eloquent → scope filters by the
        // CURRENT tenant context (which is whatever the URL resolver
        // picked, typically the default tenant). For users in other
        // tenants the query returns no rows → login fails.
        //
        // Fix: bypass the scope ONLY for the email/phone lookup (no
        // password disclosure here — we don't return the user, just
        // its tenant), set the correct tenant context, then run the
        // canonical Auth::attempt() — which now sees the user under
        // the right scope and validates the password the standard way.
        //
        // Security: `users.email` is globally unique (migration
        // constraint), so the lookup is single-row and doesn't leak
        // anything an attacker couldn't already enumerate via the
        // existing /forgot-password endpoint.
        $lookup = User::query()->withoutGlobalScopes();
        if (filter_var($this->input('email'), FILTER_VALIDATE_EMAIL)) {
            $lookup->where('email', $this->input('email'));
        } else {
            $lookup->where('phone', $this->input('email'));
        }
        $candidate = $lookup->first();

        if ($candidate && $candidate->tenant_id) {
            $tenant = Tenant::find($candidate->tenant_id);
            if ($tenant) {
                TenantContext::set($tenant);
            }
        }
        // ──────────────────────────────────────────────────────────────

        // التحقق من محاولة تسجيل الدخول
        if (!Auth::attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'بيانات الدخول غير صحيحة.',
            ]);
        }

        // التحقق من حالة المستخدم
        $user = Auth::user();
        if ($user->status !== 'active') {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'حسابك غير نشط. الرجاء التواصل مع الإدارة.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => 'لقد قمت بمحاولات كثيرة. يرجى المحاولة بعد ' . ceil($seconds / 60) . ' دقيقة.',
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
