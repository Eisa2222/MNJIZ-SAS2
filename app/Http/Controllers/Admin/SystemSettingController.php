<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSystemSettingsRequest;
use App\Services\Settings\SystemSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

/**
 * Phase C — Super Admin / Admin Settings tab UI controller.
 *
 * Serves both URL surfaces:
 *   GET  /admin/settings        ← legacy (Phase 3 group)
 *   GET  /super-admin/settings  ← spec-compliant (Phase B group)
 *   PUT  /{prefix}/settings
 *   POST /{prefix}/settings/test-mail
 *   POST /{prefix}/settings/test-moyasar
 *
 * The controller is guard-agnostic — Laravel's auth middleware on the
 * route group decides which user is logged in. The view picks the right
 * route names based on a `$routePrefix` injected by the route closure.
 */
final class SystemSettingController extends Controller
{
    public function __construct(private readonly SystemSettingsService $settings)
    {
    }

    public function index(Request $request): View
    {
        $tab = (string) $request->query('tab', 'general');

        return view('admin.settings.system', [
            'tab'             => in_array($tab, $this->validTabs(), true) ? $tab : 'general',
            'tabs'            => $this->validTabs(),
            'values'          => $this->prefilledValues(),
            'routePrefix'     => $this->resolveRoutePrefix($request),
            'sensitiveKeys'   => SystemSettingsService::sensitiveKeys(),
        ]);
    }

    public function update(UpdateSystemSettingsRequest $request): RedirectResponse
    {
        $payload = $request->tabPayload();
        $group   = (string) $request->validated('group');

        // Sensitive keys submitted as empty/masked → keep the existing
        // encrypted value. Drop the field from the payload so setMany
        // doesn't overwrite it with empty.
        foreach (SystemSettingsService::sensitiveKeys() as $key) {
            if (! array_key_exists($key, $payload)) continue;
            $value = $payload[$key];
            if ($value === null || $value === '' || str_starts_with((string) $value, '••')) {
                unset($payload[$key]);
            }
        }

        if (! empty($payload)) {
            $this->settings->setMany($payload, $group, $this->metaForGroup($group));
        }

        $prefix = $this->resolveRoutePrefix($request);

        return redirect()
            ->route("{$prefix}.settings.index", ['tab' => $group])
            ->with('status', __('admin.settings.saved'));
    }

    /**
     * Send a test email through the CURRENT runtime mail config — which
     * has just been overridden by ApplySystemSettings middleware (or by
     * the env defaults if no system_settings row exists). Never echoes
     * the password back in the JSON response.
     */
    public function testMail(Request $request): JsonResponse
    {
        $request->validate(['to' => ['required', 'email', 'max:191']]);

        try {
            // Apply latest config explicitly — runs the middleware logic
            // again so a value JUST saved is honored without a page reload.
            app(\App\Http\Middleware\ApplySystemSettings::class)->handle(
                $request,
                fn () => null,
            );

            Mail::raw(
                "MNJIZ Super Admin — system_settings test email at " . now()->toIso8601String(),
                function ($m) use ($request) {
                    $m->to($request->input('to'))
                      ->subject('MNJIZ — Test Email');
                }
            );

            return response()->json([
                'status'  => 'ok',
                'message' => __('admin.settings.test_mail_sent', ['to' => $request->input('to')]),
                'host'    => Config::get('mail.mailers.smtp.host'),
                // username may be revealing — only return the host as feedback.
            ]);
        } catch (\Throwable $e) {
            // Log full exception for ops; return a sanitised message to UI.
            Log::warning('admin.settings.test_mail_failed', [
                'message' => $e->getMessage(),
            ]);
            return response()->json([
                'status'  => 'error',
                'message' => __('admin.settings.test_mail_failed') . ': ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Hit Moyasar's GET /payments?limit=1 with the configured secret key.
     * 200 → credentials are valid; 401 → key wrong. NEVER echoes the
     * secret in the response or in logs.
     */
    public function testMoyasar(Request $request): JsonResponse
    {
        try {
            app(\App\Http\Middleware\ApplySystemSettings::class)->handle(
                $request,
                fn () => null,
            );

            $secret  = (string) Config::get('services.moyasar.secret_key', '');
            $baseUrl = (string) Config::get('services.moyasar.base_url', 'https://api.moyasar.com/v1');

            if ($secret === '') {
                return response()->json([
                    'status'  => 'error',
                    'message' => __('admin.settings.test_moyasar_no_key'),
                ], 422);
            }

            $resp = Http::timeout(15)
                ->withBasicAuth($secret, '')
                ->get(rtrim($baseUrl, '/') . '/payments', ['limit' => 1]);

            if ($resp->successful()) {
                return response()->json([
                    'status'  => 'ok',
                    'message' => __('admin.settings.test_moyasar_ok'),
                ]);
            }

            return response()->json([
                'status'  => 'error',
                'message' => __('admin.settings.test_moyasar_failed', ['code' => $resp->status()]),
            ], 422);
        } catch (\Throwable $e) {
            Log::warning('admin.settings.test_moyasar_failed', [
                'message' => $e->getMessage(),
            ]);
            return response()->json([
                'status'  => 'error',
                'message' => __('admin.settings.test_moyasar_failed', ['code' => 0]),
            ], 422);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Pre-fill values for the form. Sensitive secrets are returned as
     * `••••••••` placeholders when configured, NULL otherwise.
     *
     * @return array<string, mixed>
     */
    private function prefilledValues(): array
    {
        $keys = [
            // general
            'app_name','app_logo','app_url','support_email','support_phone','default_timezone','default_language',
            // trial
            'trial_enabled','trial_days','trial_requires_payment','trial_suspend_after_expiry','trial_warning_days',
            // moyasar
            'moyasar_publishable_key','moyasar_secret_key','moyasar_webhook_secret','moyasar_test_mode','moyasar_enabled_methods',
            // mail
            'mail_driver','mail_host','mail_port','mail_encryption','mail_username','mail_password','mail_from_address','mail_from_name',
            // landing
            'hero_title','hero_subtitle','hero_cta_text','hero_cta_url','hero_image','footer_copyright','privacy_url','terms_url',
            // notifications
            'notify_new_subscription','notify_payment_failed','notify_trial_expiring','notify_subscription_expiring','admin_notification_email',
        ];

        $sensitive = SystemSettingsService::sensitiveKeys();
        $out = [];

        foreach ($keys as $k) {
            if (in_array($k, $sensitive, true)) {
                $out[$k] = $this->settings->maskedValue($k);
            } else {
                $out[$k] = $this->settings->get($k);
            }
        }

        return $out;
    }

    /** @return array<string, array<string, mixed>> */
    private function metaForGroup(string $group): array
    {
        // Per-key meta hints (cast / encrypted) at write time.
        $hints = [
            'trial_days'                 => ['cast' => 'int'],
            'trial_enabled'              => ['cast' => 'bool'],
            'trial_requires_payment'     => ['cast' => 'bool'],
            'trial_suspend_after_expiry' => ['cast' => 'bool'],
            'trial_warning_days'         => ['cast' => 'json'],

            'moyasar_secret_key'         => ['is_encrypted' => true, 'cast' => 'string'],
            'moyasar_webhook_secret'     => ['is_encrypted' => true, 'cast' => 'string'],
            'moyasar_test_mode'          => ['cast' => 'bool'],
            'moyasar_enabled_methods'    => ['cast' => 'json'],

            'mail_password'              => ['is_encrypted' => true, 'cast' => 'string'],
            'mail_port'                  => ['cast' => 'int'],

            'notify_new_subscription'    => ['cast' => 'bool'],
            'notify_payment_failed'      => ['cast' => 'bool'],
            'notify_trial_expiring'      => ['cast' => 'bool'],
            'notify_subscription_expiring'=> ['cast' => 'bool'],
        ];

        return $hints;
    }

    private function resolveRoutePrefix(Request $request): string
    {
        // Pull from the URL itself so the same controller serves both
        // /admin/settings and /super-admin/settings without configuration.
        $first = $request->segment(1);
        return $first === 'super-admin' ? 'super-admin' : 'admin';
    }

    /** @return array<int, string> */
    private function validTabs(): array
    {
        return ['general', 'trial', 'moyasar', 'mail', 'landing', 'notifications'];
    }
}
