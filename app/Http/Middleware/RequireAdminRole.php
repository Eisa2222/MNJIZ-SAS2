<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Restricts admin routes to specific Admin roles. Apply as:
 *
 *     Route::middleware('admin.role:super_admin,support')->group(...)
 *
 * The `auth:admin` middleware runs FIRST and ensures an admin is signed in;
 * this layer then enforces which roles are allowed.
 *
 * Implementation note: we read from the 'admin' guard explicitly rather than
 * auth()->user() so there's no chance of accidentally accepting a web-guard
 * tenant user whose email happens to match an admin record.
 */
final class RequireAdminRole
{
    public function handle(Request $request, Closure $next, string ...$allowedRoles)
    {
        /** @var Admin|null $admin */
        $admin = Auth::guard('admin')->user();

        if (! $admin) {
            throw new HttpException(403, 'Admin authentication required.');
        }

        if (! $admin->isActive()) {
            throw new HttpException(403, 'Your admin account is suspended.');
        }

        // Default when no roles supplied: any active admin allowed.
        if (empty($allowedRoles)) {
            return $next($request);
        }

        if (! in_array($admin->role, $allowedRoles, true)) {
            throw new HttpException(
                403,
                "This action requires one of: ".implode(', ', $allowedRoles)
                .". Your role: '{$admin->role}'."
            );
        }

        return $next($request);
    }
}
