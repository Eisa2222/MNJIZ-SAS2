<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\StartImpersonationAction;
use App\Actions\Admin\StopImpersonationAction;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class ImpersonationController extends Controller
{
    /**
     * POST /admin/tenants/{tenant}/impersonate/{user}
     * Auth: admin guard.
     */
    public function start(
        Tenant $tenant,
        int $userId,
        Request $request,
        StartImpersonationAction $action
    ): RedirectResponse {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin, 403);

        $user = User::withoutTenancy()->findOrFail($userId);

        $action->execute(
            admin:   $admin,
            tenant:  $tenant,
            user:    $user,
            request: $request,
            reason:  $request->input('reason'),
        );

        return redirect()->to(url('/t/'.$tenant->slug))
            ->with('status', "You are now impersonating {$user->email}.");
    }

    /**
     * POST /admin/impersonation/stop
     * Auth: NOT admin-gated — this is called from the tenant UI by the
     * impersonated session, which has no admin cookie. It only succeeds if
     * session('impersonation') exists, which itself is tamper-resistant
     * (signed session cookie).
     */
    public function stop(Request $request, StopImpersonationAction $action): RedirectResponse
    {
        $log = $action->execute($request);

        if (! $log) {
            return redirect()->route('admin.login');
        }

        return redirect()->route('admin.tenants.show', $log->tenant)
            ->with('status', 'Impersonation ended.');
    }
}
