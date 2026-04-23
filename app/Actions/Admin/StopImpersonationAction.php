<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Models\ImpersonationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Reverses StartImpersonationAction:
 *   1. Close ImpersonationLog (ended_at=now).
 *   2. Log out of `web` guard (target tenant user).
 *   3. Clear `impersonation.*` session keys.
 *   4. Admin is still authenticated on `admin` guard → redirect to /admin.
 *
 * Idempotent: calling stop when no impersonation is active returns null.
 */
final class StopImpersonationAction
{
    public function execute(Request $request): ?ImpersonationLog
    {
        $data = session('impersonation');

        if (! $data) {
            return null;
        }

        return DB::transaction(function () use ($request, $data) {
            /** @var ImpersonationLog|null $log */
            $log = ImpersonationLog::query()->find($data['log_id'] ?? null);

            if ($log && $log->isActive()) {
                $log->update(['ended_at' => now()]);
            }

            Auth::guard('web')->logout();
            $request->session()->forget('impersonation');
            $request->session()->regenerate();

            if ($log) {
                activity('impersonation')
                    ->performedOn($log->user)
                    ->withProperties([
                        'impersonation_log_id' => $log->id,
                        'tenant_id'            => $log->tenant_id,
                        'duration_seconds'     => $log->started_at->diffInSeconds(now()),
                    ])
                    ->event('impersonation.stopped')
                    ->log("Impersonation ended (log #{$log->id}).");
            }

            return $log;
        });
    }
}
