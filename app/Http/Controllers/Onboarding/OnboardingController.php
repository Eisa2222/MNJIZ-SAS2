<?php

declare(strict_types=1);

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use App\Tenancy\TenantContext;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Phase 9 — first-run onboarding flow.
 *
 *   GET /onboarding/welcome  → welcome screen with checklist
 *
 * Shown immediately after a successful /register; further onboarding
 * steps (invite team, configure integrations) live in their own
 * tenant-scoped controllers and aren't part of this lightweight flow.
 */
final class OnboardingController extends Controller
{
    public function welcome(Request $request): View
    {
        $user   = $request->user();
        $tenant = TenantContext::current() ?? optional($user)->tenant;

        // Computed checklist state — cheap, runs once per page load.
        $tenantId = $tenant?->getKey();
        $checklist = [
            'account_created' => true,
            'team_invited'    => $tenantId
                ? \App\Models\User::query()->where('tenant_id', $tenantId)->count() > 1
                : false,
            'first_lawsuit'   => $tenantId
                ? \DB::table('lawsuits')->where('tenant_id', $tenantId)->exists()
                : false,
            'first_contract'  => $tenantId
                ? \DB::table('contracts')->where('tenant_id', $tenantId)->exists()
                : false,
        ];

        return view('onboarding.welcome', [
            'tenant'    => $tenant,
            'user'      => $user,
            'checklist' => $checklist,
        ]);
    }
}
