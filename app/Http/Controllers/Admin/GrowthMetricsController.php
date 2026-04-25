<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * Phase 9 — Super Admin GTM dashboard metrics.
 *
 *   GET /admin/api/growth   → JSON
 *
 * Aggregates the numbers a SaaS founder watches every morning:
 *   - tenants total / active / suspended
 *   - subscriptions trialing / active / past_due / canceled / expired
 *   - signups today / 7-day / 30-day
 *   - MRR (monthly recurring revenue)
 *   - churn (canceled in last 30 days vs. active 30 days ago)
 *   - failed payments in last 7 days
 *
 * Read-only. Designed to back a small overview widget on the admin
 * dashboard, NOT to be a deep analytics product. Numbers come straight
 * from production tables — no caching at this layer (each call is a
 * handful of indexed COUNT queries).
 */
final class GrowthMetricsController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json([
            'tenants'           => $this->tenantStats(),
            'subscriptions'     => $this->subscriptionStats(),
            'signups'           => $this->signupStats(),
            'mrr'               => $this->mrr(),
            'churn_last_30d'    => $this->churnRate(),
            'failed_payments_7d'=> $this->failedPayments7d(),
            'generated_at'      => now()->toIso8601String(),
        ]);
    }

    private function tenantStats(): array
    {
        return [
            'total'      => (int) DB::table('tenants')->count(),
            'active'     => (int) DB::table('tenants')->where('status', 'active')->count(),
            'suspended'  => (int) DB::table('tenants')->where('status', 'suspended')->count(),
        ];
    }

    private function subscriptionStats(): array
    {
        $byStatus = DB::table('subscriptions')
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        return [
            'trialing'  => (int) ($byStatus['trialing']  ?? 0),
            'active'    => (int) ($byStatus['active']    ?? 0),
            'past_due'  => (int) ($byStatus['past_due']  ?? 0),
            'canceled'  => (int) ($byStatus['canceled']  ?? 0),
            'expired'   => (int) ($byStatus['expired']   ?? 0),
        ];
    }

    private function signupStats(): array
    {
        $now = now();
        return [
            'today'  => (int) DB::table('tenants')->where('created_at', '>=', $now->copy()->startOfDay())->count(),
            'last_7' => (int) DB::table('tenants')->where('created_at', '>=', $now->copy()->subDays(7))->count(),
            'last_30'=> (int) DB::table('tenants')->where('created_at', '>=', $now->copy()->subDays(30))->count(),
        ];
    }

    /**
     * Monthly Recurring Revenue. Sum of plan.price_monthly across all
     * subscriptions with status=active or trialing (treat trial as
     * pipeline; conservative variant would exclude trialing).
     *
     * Yearly subscriptions are normalized to monthly equivalent by
     * dividing the plan's yearly price by 12 — this matches how SaaS
     * boards typically read MRR.
     */
    private function mrr(): float
    {
        $rows = DB::table('subscriptions as s')
            ->join('plans as p', 's.plan_id', '=', 'p.id')
            ->whereIn('s.status', ['active', 'trialing'])
            ->select('s.billing_cycle as cycle', 'p.price_monthly', 'p.price_yearly')
            ->get();

        $mrr = 0.0;
        foreach ($rows as $r) {
            $mrr += $r->cycle === 'yearly'
                ? (float) $r->price_yearly / 12.0
                : (float) $r->price_monthly;
        }

        return round($mrr, 2);
    }

    /**
     * Naive churn = (canceled in last 30d) / (subscriptions active 30d ago).
     * Returns 0 when denominator is 0 (early-stage SaaS).
     */
    private function churnRate(): float
    {
        $cutoff = now()->subDays(30);

        $canceled = (int) DB::table('subscriptions')
            ->where('status', 'canceled')
            ->where('canceled_at', '>=', $cutoff)
            ->count();

        $activeAgo = (int) DB::table('subscriptions')
            ->where('created_at', '<', $cutoff)
            ->whereIn('status', ['active', 'trialing', 'past_due'])
            ->count();

        return $activeAgo === 0 ? 0.0 : round(($canceled / $activeAgo) * 100, 2);
    }

    private function failedPayments7d(): int
    {
        if (! \Schema::hasTable('payments')) return 0;

        return (int) DB::table('payments')
            ->where('status', 'failed')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();
    }
}
