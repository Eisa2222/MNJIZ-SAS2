<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\TenantWelcomeMail;
use App\Models\Coupon;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SystemSetting;
use App\Models\Tenant;
use App\Services\CouponService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Stancl\Tenancy\Database\Models\Domain;

/**
 * V2 — CreateTenantJob (Multi-DB tenancy). Spec lines 199-203 + 343-350.
 *
 * Steps:
 *   1. Create Tenant row in CENTRAL DB with custom columns.
 *   2. stancl/tenancy auto-creates the per-tenant MySQL database via
 *      MySQLDatabaseManager (because Tenant implements TenantWithDatabase).
 *   3. Run tenant migrations against the new DB.
 *   4. Create the owner User INSIDE the tenant DB with a placeholder
 *      password (NEVER shared — user receives a setup link instead).
 *   5. Generate a 48h signed setup link via URL::temporarySignedRoute.
 *   6. Persist Subscription + Payment in central DB.
 *   7. If coupon used: CouponService::apply() (atomic increment + audit).
 *   8. Queue TenantWelcomeMail with the setup link.
 *   9. If notify_new_subscription enabled: notify Super Admin.
 *
 *   IDEMPOTENT: keyed off moyasar_payment_id — re-dispatching is a no-op.
 */
final class CreateTenantJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 30;

    public function __construct(public array $payload) {}

    public function handle(CouponService $coupons): void
    {
        $paymentId = (string) ($this->payload['moyasar_payment_id'] ?? '');
        if ($paymentId === '') {
            Log::warning('create_tenant_job.no_payment_id');
            return;
        }

        // Idempotency
        if (Payment::where('moyasar_payment_id', $paymentId)->exists()) {
            Log::info('create_tenant_job.already_processed', ['id' => $paymentId]);
            return;
        }

        $metadata = (array) ($this->payload['metadata'] ?? []);
        $planId   = (int) ($metadata['plan_id'] ?? 0);
        $cycle    = (string) ($metadata['billing_cycle'] ?? 'monthly');

        $plan = Plan::find($planId);
        if (! $plan) {
            Log::warning('create_tenant_job.plan_missing', ['plan_id' => $planId]);
            return;
        }

        $companyName = (string) ($metadata['company_name'] ?? 'Untitled Firm');
        $ownerName   = (string) ($metadata['owner_name']   ?? 'Owner');
        $ownerEmail  = (string) ($metadata['owner_email']  ?? '');
        $ownerPhone  = (string) ($metadata['owner_phone']  ?? '');
        $couponCode  = (string) ($metadata['coupon_code']  ?? '');

        if ($ownerEmail === '') {
            Log::warning('create_tenant_job.no_owner_email', ['payment_id' => $paymentId]);
            return;
        }

        $tenant = DB::transaction(function () use (
            $plan, $cycle, $companyName, $ownerName, $ownerEmail, $ownerPhone,
            $paymentId, $metadata, $coupons, $couponCode,
        ) {
            // 1. Create tenant in central DB (stancl auto-creates the
            //    per-tenant database via MySQLDatabaseManager).
            $tenant = Tenant::create([
                'id'           => (string) Str::uuid(),
                'company_name' => $companyName,
                'owner_name'   => $ownerName,
                'owner_email'  => $ownerEmail,
                'owner_phone'  => $ownerPhone,
                'timezone'     => 'Asia/Riyadh',
                'language'     => 'ar',
                'status'       => Tenant::STATUS_ACTIVE,
            ]);

            // 2. Attach a default subdomain (acme.mnjiz.sa pattern).
            $base   = (string) (env('TENANCY_APP_BASE_DOMAIN') ?: 'mnjiz.test');
            $slug   = Str::slug(Str::limit($companyName, 30, '')) ?: ('firm-'.$tenant->id);
            $domain = $slug.'.'.$base;
            $tenant->domains()->create(['domain' => $domain]);

            // 3. Run tenant migrations against the new DB.
            $tenant->run(function () {
                Artisan::call('migrate', [
                    '--path'     => 'database/migrations/tenant',
                    '--realpath' => false,
                    '--force'    => true,
                ]);
            });

            // 4. Create the owner User inside the tenant DB.
            $tenant->run(function () use ($ownerName, $ownerEmail, $ownerPhone) {
                DB::table('users')->insert([
                    'name'       => $ownerName,
                    'email'      => $ownerEmail,
                    'password'   => Hash::make(Str::random(40)),  // unreachable placeholder
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

            // 5. Subscription in central DB.
            $now = now();
            $endsAt = $cycle === 'yearly' ? $now->copy()->addYear() : $now->copy()->addMonth();
            $sub = Subscription::create([
                'tenant_id'       => $tenant->id,
                'plan_id'         => $plan->id,
                'billing_cycle'   => $cycle,
                'status'          => Subscription::STATUS_ACTIVE,
                'starts_at'       => $now,
                'ends_at'         => $endsAt,
                'next_billing_at' => $endsAt,
                'amount'          => (float) ($this->payload['amount'] ?? 0),
                'currency'        => (string) ($this->payload['currency'] ?? 'SAR'),
                'metadata'        => ['from_checkout' => true],
            ]);

            // 6. Coupon — atomic apply.
            if ($couponCode !== '') {
                $coupon = Coupon::where('code', $couponCode)->first();
                if ($coupon && $coupon->isValid()) {
                    $original = $cycle === 'yearly' ? (float) $plan->price_yearly : (float) $plan->price_monthly;
                    $discount = max(0.0, round($original - (float) ($this->payload['amount'] ?? 0), 2));
                    $coupons->apply($coupon, $tenant->id, $sub->id, $discount);
                    $sub->update(['coupon_id' => $coupon->id, 'discount_amount' => $discount]);
                }
            }

            // 7. Payment row.
            Payment::create([
                'tenant_id'          => $tenant->id,
                'subscription_id'    => $sub->id,
                'moyasar_payment_id' => $paymentId,
                'amount'             => (float) ($this->payload['amount'] ?? 0),
                'currency'           => (string) ($this->payload['currency'] ?? 'SAR'),
                'status'             => Payment::STATUS_PAID,
                'payment_method'     => (string) ($this->payload['payment_method'] ?? 'creditcard'),
                'moyasar_response'   => (array) ($this->payload['moyasar_response'] ?? []),
                'paid_at'            => now(),
            ]);

            return $tenant;
        });

        // 8. Welcome mail (queued separately on the mail queue).
        try {
            $setupUrl = URL::temporarySignedRoute(
                'tenant.password.setup',
                now()->addHours(48),
                ['email' => $ownerEmail],
            );
            $loginUrl = 'https://'.$tenant->domains()->first()?->domain.'/login';

            Mail::to($ownerEmail)->queue(
                new TenantWelcomeMail($tenant, $plan, $setupUrl, $loginUrl)
            );
        } catch (\Throwable $e) {
            Log::warning('create_tenant_job.welcome_mail_failed', [
                'tenant_id' => $tenant->id,
                'msg'       => $e->getMessage(),
            ]);
        }

        // 9. Optional Super Admin notification.
        if ((bool) SystemSetting::get('notify_new_subscription', false)) {
            $adminEmail = (string) (SystemSetting::get('admin_notification_email') ?? '');
            if ($adminEmail !== '') {
                try {
                    Mail::raw(
                        "اشتراك جديد: {$companyName} ({$ownerEmail}) — باقة {$plan->name}",
                        fn ($m) => $m->to($adminEmail)
                            ->subject('[MNJIZ] اشتراك جديد')
                    );
                } catch (\Throwable $e) {
                    Log::warning('create_tenant_job.admin_notify_failed', ['msg' => $e->getMessage()]);
                }
            }
        }
    }
}
