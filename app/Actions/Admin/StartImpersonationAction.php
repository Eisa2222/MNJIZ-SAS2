<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Models\Admin;
use App\Models\ImpersonationLog;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Flow:
 *   1. Verify admin can impersonate (role gate).
 *   2. Verify target user belongs to target tenant.
 *   3. Open ImpersonationLog (started_at=now, ended_at=null).
 *   4. Stash impersonation metadata in session.
 *   5. Log the target user into the `web` guard WITHOUT touching the `admin`
 *      guard — admin stays signed in so we can restore them on stop.
 *   6. Fire activity log entry.
 *
 * The admin and the impersonated user are logged in simultaneously but in
 * different guards → no session collision.
 */
final class StartImpersonationAction
{
    public function execute(Admin $admin, Tenant $tenant, User $user, Request $request, ?string $reason = null): ImpersonationLog
    {
        if (! $admin->canImpersonate()) {
            throw new RuntimeException("Admin '{$admin->email}' is not allowed to impersonate.");
        }

        if (! $tenant->isActive()) {
            throw new RuntimeException("Cannot impersonate into a suspended tenant ('{$tenant->slug}').");
        }

        // Critical: fetch user WITHOUT tenant scope (admin flow crosses tenants)
        // then verify membership explicitly.
        $user = User::withoutTenancy()->findOrFail($user->getKey());
        if ((int) $user->tenant_id !== (int) $tenant->id) {
            throw new RuntimeException("User #{$user->id} does not belong to tenant '{$tenant->slug}'.");
        }

        return DB::transaction(function () use ($admin, $tenant, $user, $request, $reason) {
            $log = ImpersonationLog::create([
                'admin_id'   => $admin->id,
                'tenant_id'  => $tenant->id,
                'user_id'    => $user->id,
                'reason'     => $reason,
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 512),
                'started_at' => now(),
            ]);

            session()->put('impersonation', [
                'log_id'     => $log->id,
                'admin_id'   => $admin->id,
                'tenant_id'  => $tenant->id,
                'tenant_slug'=> $tenant->slug,
                'user_id'    => $user->id,
                'started_at' => $log->started_at->toISOString(),
            ]);

            // Establish the target tenant + log in as the tenant user.
            TenantContext::set($tenant);
            Auth::guard('web')->loginUsingId($user->id);

            activity('impersonation')
                ->causedBy($admin)
                ->performedOn($user)
                ->withProperties([
                    'impersonation_log_id' => $log->id,
                    'tenant_id'            => $tenant->id,
                    'tenant_slug'          => $tenant->slug,
                    'ip_address'           => $request->ip(),
                ])
                ->event('impersonation.started')
                ->log("Admin #{$admin->id} started impersonating user #{$user->id} in tenant '{$tenant->slug}'.");

            return $log;
        });
    }
}
