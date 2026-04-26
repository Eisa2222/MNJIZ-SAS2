<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // ─── Hotfix: Tenant-Aware Password Reset ──────────────────────
        // Same root cause as login: `Password::sendResetLink()` resolves
        // the user via Eloquent under `BelongsToTenant` global scope, so
        // any user not in the URL-resolved (default) tenant is invisible
        // and gets a generic "user not found" response.
        //
        // Fix: look up the user without scopes, set the right tenant
        // context, then call sendResetLink — which now sees the user.
        // No password disclosure here; we only use the lookup to bind
        // the correct tenant. The reset email + token still flow through
        // Laravel's standard PasswordBroker.
        $candidate = User::query()->withoutGlobalScopes()
            ->where('email', $request->input('email'))
            ->first();

        if ($candidate && $candidate->tenant_id) {
            $tenant = Tenant::find($candidate->tenant_id);
            if ($tenant) {
                TenantContext::set($tenant);
            }
        }
        // ──────────────────────────────────────────────────────────────

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', __($status))
                    : back()->withInput($request->only('email'))
                            ->withErrors(['email' => __($status)]);
    }
}
