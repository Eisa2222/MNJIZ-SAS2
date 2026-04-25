<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Phase B — login flow for the new `super_admin` guard.
 *
 * Identical responsibilities to App\Http\Controllers\Admin\Auth\LoginController
 * (Phase 3) but bound to:
 *   - guard:        super_admin
 *   - route names:  super-admin.login / super-admin.logout / super-admin.dashboard
 *   - view:         admin.auth.login   (re-uses the existing Vuexy Blade —
 *                                       form action is parameterized on
 *                                       route() so the same view serves both
 *                                       /admin/login and /super-admin/login)
 */
final class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('admin.auth.login', [
            'loginAction' => route('super-admin.login.attempt'),
            'guard'       => 'super_admin',
        ]);
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('super-admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('super_admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('super-admin.login');
    }
}
