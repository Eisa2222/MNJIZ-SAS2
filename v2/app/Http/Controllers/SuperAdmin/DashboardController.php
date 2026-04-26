<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Tenant;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * V2 — Super Admin dashboard. Spec line 218.
 */
final class DashboardController extends Controller
{
    public function index(): View
    {
        $now = CarbonImmutable::now();
        $monthStart = $now->startOfMonth();

        $stats = [
            'tenants_total'      => Tenant::count(),
            'tenants_active'     => Tenant::where('status', Tenant::STATUS_ACTIVE)->count(),
            'tenants_suspended'  => Tenant::where('status', Tenant::STATUS_SUSPENDED)->count(),
            'subs_active'        => Subscription::where('status', Subscription::STATUS_ACTIVE)->count(),
            'subs_trialing'      => Subscription::where('status', Subscription::STATUS_TRIALING)->count(),
            'subs_expiring_soon' => Subscription::whereIn('status', ['active', 'trialing'])
                ->whereBetween('ends_at', [$now, $now->addDays(7)])
                ->count(),
            'revenue_total'      => (float) Payment::where('status', 'paid')->sum('amount'),
            'revenue_month'      => (float) Payment::where('status', 'paid')
                ->where('paid_at', '>=', $monthStart)->sum('amount'),
        ];

        // Last 12 months revenue chart
        $revenueChart = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = $now->subMonths($i)->startOfMonth();
            $revenueChart[$month->format('Y-m')] = (float) Payment::where('status', 'paid')
                ->whereBetween('paid_at', [$month, $month->endOfMonth()])
                ->sum('amount');
        }

        $recentTenants = Tenant::query()->latest()->limit(10)->get();

        return view('super-admin.dashboard', compact('stats', 'revenueChart', 'recentTenants'));
    }
}
