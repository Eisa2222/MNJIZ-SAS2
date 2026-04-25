<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\TenantPasswordSetupRequest;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\Auth\TenantPasswordSetupService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Phase F — handles the welcome-mail "set your password" flow.
 *
 *   GET  /password/setup/{token}      → show the form
 *   POST /password/setup              → store the new password
 *
 * The GET endpoint is wrapped in `signed` middleware so a tampered URL
 * (or one older than 48h) returns 403 before reaching this controller.
 * We then double-check the token against its sha256 hash in the DB —
 * defence-in-depth against a hypothetical signed-URL middleware bypass.
 *
 * No tokens, URLs, or passwords are ever logged.
 */
final class TenantPasswordSetupController extends Controller
{
    public function __construct(
        private TenantPasswordSetupService $tokens,
    ) {}

    /**
     * Display the setup form. The token comes from the URL path; the
     * email rides as a query parameter so it's covered by the URL
     * signature.
     */
    public function show(Request $request, string $token): View
    {
        $email = (string) $request->query('email', '');

        if ($email === '' || ! $this->tokens->verify($email, $token)) {
            // Don't disclose whether email was wrong vs token expired —
            // single 403 message keeps an attacker from enumerating.
            throw new HttpException(403, __('auth.setup.invalid_or_expired'));
        }

        return view('auth.tenant-password-setup', [
            'email'    => $email,
            'token'    => $token,
            'app_name' => SystemSetting::get('app_name', 'MNJIZ'),
        ]);
    }

    /**
     * Persist the new password, activate the user, delete the token.
     * Does NOT auto-login — operator browses to the login page next
     * (consistent with the Phase 5 password-reset flow which also
     * requires an explicit re-login for clarity).
     */
    public function store(TenantPasswordSetupRequest $request): RedirectResponse
    {
        $email = (string) $request->validated('email');
        $token = (string) $request->validated('token');

        if (! $this->tokens->verify($email, $token)) {
            return back()->withErrors([
                'email' => __('auth.setup.invalid_or_expired'),
            ]);
        }

        // We deliberately bypass the BelongsToTenant scope here —
        // anyone holding a valid signed URL is by definition pre-auth.
        $user = User::query()->withoutGlobalScopes()->where('email', $email)->first();

        if (! $user) {
            // Don't leak account existence — generic invalid response.
            Log::warning('tenant.password_setup.user_missing_for_valid_token', [
                'email_domain' => substr(strrchr($email, '@') ?: '', 1),
            ]);
            return back()->withErrors([
                'email' => __('auth.setup.invalid_or_expired'),
            ]);
        }

        $user->forceFill([
            'password'             => Hash::make((string) $request->validated('password')),
            'must_change_password' => false,
            'status'               => $user->status ?? 1,    // activate inactive accounts
            'email_verified_at'    => now(),                  // implicit verification — operator owns the inbox
        ])->save();

        $this->tokens->consume($email);

        return redirect('/login')->with('status', __('auth.setup.success'));
    }
}
