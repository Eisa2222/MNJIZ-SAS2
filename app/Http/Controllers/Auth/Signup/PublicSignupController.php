<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth\Signup;

use App\Actions\Billing\Subscription\StartTrialAction;
use App\Enums\Billing\BillingCycle;
use App\Enums\Billing\PaymentGateway;
use App\Http\Controllers\Controller;
use App\Jobs\CreateTenantJob;
use App\Mail\Marketing\WelcomeMail;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Phase 9 — public signup pipeline.
 *
 *   GET  /register     → registration form
 *   POST /register     → atomically creates:
 *                          1. Tenant  (slug auto-generated from firm name)
 *                          2. owner User  (auto-attached to that tenant)
 *                          3. trialing Subscription on the requested plan
 *                             (defaults to Pro if not specified)
 *                          4. Welcome email (queued)
 *
 * Whole flow is wrapped in a DB transaction so a failure on any step
 * leaves no partial state.
 *
 * Throttled by `throttle:login` middleware in routes/central.php so
 * automated abuse (mass tenant creation) is rate-limited per email+IP.
 */
final class PublicSignupController extends Controller
{
    public function show(Request $request): Renderable
    {
        return view('auth.signup', [
            'plans'       => Plan::query()->where('is_active', true)->where('is_free', false)->orderBy('sort_order')->get(),
            'pre_plan'    => $request->query('plan'),  // optional ?plan=pro from pricing page
        ]);
    }

    public function store(Request $request, StartTrialAction $startTrial): RedirectResponse
    {
        $useSetupLink = (bool) Config::get('tenancy.signup.use_setup_link', false);

        $rules = [
            'firm_name' => ['required', 'string', 'min:2', 'max:120'],
            'name'      => ['required', 'string', 'min:2', 'max:120'],
            'email'     => ['required', 'email', 'max:191', Rule::unique('users', 'email')],
            'plan'      => ['nullable', 'string', Rule::exists('plans', 'slug')->where('is_active', true)->where('is_free', false)],
            'cycle'     => ['nullable', 'in:monthly,yearly'],
            'terms'     => ['required', 'accepted'],
        ];

        // Phase F — when setup-link mode is on, the signup form no longer
        // collects a password; the user picks one via the welcome-mail
        // signed URL. Legacy mode keeps the inline-password requirement
        // so RegistrationTest etc. still pass.
        if (! $useSetupLink) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        } else {
            $rules['phone'] = ['nullable', 'string', 'max:32'];
        }

        $data = $request->validate($rules);

        // Default plan: Pro (slug=professional, the featured one). Fall through
        // to the first sellable plan if Pro doesn't exist (e.g. test envs that
        // haven't run DefaultPlansSeeder).
        $planSlug = $data['plan'] ?? 'professional';
        $plan = Plan::query()->where('slug', $planSlug)->first()
              ?? Plan::query()->where('is_active', true)->where('is_free', false)->orderBy('sort_order')->firstOrFail();

        $cycle = ($data['cycle'] ?? 'monthly') === 'yearly'
            ? BillingCycle::Yearly
            : BillingCycle::Monthly;

        try {
            // Phase F: when setup-link mode is on, we DO NOT create the
            // owner user inline — CreateTenantJob does that asynchronously
            // (with a placeholder password the operator never sees).
            // Legacy path still creates the user inline so test baselines
            // (Auth\RegistrationTest, signup auto-login expectations) hold.
            [$tenant, $user, $subscription] = DB::transaction(function () use ($data, $plan, $cycle, $startTrial, $useSetupLink) {
                $tenant = Tenant::create([
                    'name'   => $data['firm_name'],
                    'slug'   => $this->generateUniqueSlug($data['firm_name']),
                    'status' => 'active',
                ]);

                $user = null;
                if (! $useSetupLink) {
                    $user = TenantContext::runAs($tenant, fn () => User::create([
                        'name'                => $data['name'],
                        'email'               => $data['email'],
                        'password'            => Hash::make($data['password']),
                        'nationality'         => 'SA',
                        'tour_completed'      => 0,
                        'tour_task_completed' => 0,
                    ]));
                }

                $subscription = $startTrial->execute(
                    $tenant,
                    $plan,
                    $cycle,
                    PaymentGateway::Moyasar
                );

                return [$tenant, $user, $subscription];
            });
        } catch (\Throwable $e) {
            Log::error('signup.failed', [
                'email'     => $data['email'],
                'firm_name' => $data['firm_name'],
                'message'   => $e->getMessage(),
            ]);
            return back()
                ->withInput($request->only('firm_name', 'name', 'email', 'plan', 'cycle'))
                ->withErrors(['email' => __('Could not complete signup. Please try again.')]);
        }

        // ─── Phase F branch ────────────────────────────────────────────
        if ($useSetupLink) {
            CreateTenantJob::dispatch([
                'tenant_id'       => $tenant->id,
                'plan_id'         => $plan->id,
                'billing_cycle'   => $cycle->value,
                'owner_name'      => $data['name'],
                'owner_email'     => $data['email'],
                'owner_phone'     => $data['phone'] ?? '',
                'source'          => 'trial',
                'subscription_id' => $subscription->id,
                'payment_id'      => null,
                'coupon_code'     => null,
            ]);

            return redirect()->route('checkout.account-pending', [
                'email' => $data['email'],
            ]);
        }

        // ─── Legacy path (preserved) ───────────────────────────────────
        try {
            Mail::to($user->email)->queue(new WelcomeMail($tenant, $user, $subscription));
        } catch (\Throwable $e) {
            Log::warning('signup.welcome_email_failed', [
                'tenant_id' => $tenant->id,
                'message'   => $e->getMessage(),
            ]);
        }

        auth()->login($user);

        return redirect()
            ->route('onboarding.welcome')
            ->with('signup.success', __('Welcome to MNJIZ! Your :days-day trial has started.', ['days' => $plan->trial_days ?? 14]));
    }

    /**
     * Build a URL-safe slug from the firm name. Adds a 4-char suffix on
     * collision so two "Al-Burhan Law" registrations don't conflict.
     */
    private function generateUniqueSlug(string $firmName): string
    {
        $base = Str::slug($firmName, '-', 'ar');

        // ASCII-fallback for slugs that come back empty under Arabic-only names.
        if ($base === '') {
            $base = 'firm-' . Str::lower(Str::random(6));
        }

        $candidate = $base;
        while (Tenant::query()->where('slug', $candidate)->exists()) {
            $candidate = $base . '-' . Str::lower(Str::random(4));
        }
        return $candidate;
    }
}
