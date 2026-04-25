<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Phase C — Form Request for the Super Admin Settings tab forms.
 *
 * Validation is permissive on purpose: the operator may fill any subset
 * of fields (each tab is its own POST in the UI). The `group` query/param
 * tells the controller which slice of fields to persist.
 *
 * Sensitive fields (`mail_password`, `moyasar_secret_key`,
 * `moyasar_webhook_secret`) — we accept either a non-empty new value OR
 * an empty string. The controller treats empty as "leave unchanged"
 * (so masking doesn't accidentally wipe real secrets when the form is
 * re-submitted with a `••••••••` placeholder).
 */
final class UpdateSystemSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route-level middleware (auth + admin.role) already restricts.
        return true;
    }

    public function rules(): array
    {
        return [
            'group' => ['required', 'string', 'in:general,trial,moyasar,mail,landing,notifications'],

            // ── general ──────────────────────────────────────────────
            'app_name'          => ['nullable', 'string', 'max:120'],
            'app_logo'          => ['nullable', 'string', 'max:500'],
            'app_url'           => ['nullable', 'url', 'max:500'],
            'support_email'     => ['nullable', 'email', 'max:191'],
            'support_phone'     => ['nullable', 'string', 'max:32'],
            'default_timezone'  => ['nullable', 'string', 'max:64'],
            'default_language'  => ['nullable', 'in:ar,en'],

            // ── trial ────────────────────────────────────────────────
            'trial_enabled'              => ['nullable', 'boolean'],
            'trial_days'                 => ['nullable', 'integer', 'min:0', 'max:365'],
            'trial_requires_payment'     => ['nullable', 'boolean'],
            'trial_suspend_after_expiry' => ['nullable', 'boolean'],
            'trial_warning_days'         => ['nullable', 'array'],
            'trial_warning_days.*'       => ['integer', 'min:0', 'max:30'],

            // ── moyasar ──────────────────────────────────────────────
            'moyasar_publishable_key' => ['nullable', 'string', 'max:255'],
            'moyasar_secret_key'      => ['nullable', 'string', 'max:255'],
            'moyasar_webhook_secret'  => ['nullable', 'string', 'max:255'],
            'moyasar_test_mode'       => ['nullable', 'boolean'],
            'moyasar_enabled_methods' => ['nullable', 'array'],
            'moyasar_enabled_methods.*' => ['string', 'in:creditcard,applepay,stcpay'],

            // ── mail ─────────────────────────────────────────────────
            'mail_driver'        => ['nullable', 'in:smtp,log,array,sendmail'],
            'mail_host'          => ['nullable', 'string', 'max:191'],
            'mail_port'          => ['nullable', 'integer', 'between:1,65535'],
            'mail_encryption'    => ['nullable', 'in:tls,ssl,null,'],
            'mail_username'      => ['nullable', 'string', 'max:191'],
            'mail_password'      => ['nullable', 'string', 'max:191'],
            'mail_from_address'  => ['nullable', 'email', 'max:191'],
            'mail_from_name'     => ['nullable', 'string', 'max:120'],

            // ── landing ──────────────────────────────────────────────
            'hero_title'         => ['nullable', 'string', 'max:255'],
            'hero_subtitle'      => ['nullable', 'string', 'max:500'],
            'hero_cta_text'      => ['nullable', 'string', 'max:64'],
            'hero_cta_url'       => ['nullable', 'string', 'max:500'],
            'hero_image'         => ['nullable', 'string', 'max:500'],
            'footer_copyright'   => ['nullable', 'string', 'max:255'],
            'privacy_url'        => ['nullable', 'url', 'max:500'],
            'terms_url'          => ['nullable', 'url', 'max:500'],

            // ── notifications ────────────────────────────────────────
            'notify_new_subscription'     => ['nullable', 'boolean'],
            'notify_payment_failed'       => ['nullable', 'boolean'],
            'notify_trial_expiring'       => ['nullable', 'boolean'],
            'notify_subscription_expiring'=> ['nullable', 'boolean'],
            'admin_notification_email'    => ['nullable', 'email', 'max:191'],
        ];
    }

    /**
     * Returns the subset of validated fields that belong to the submitted
     * tab — keeps `setMany` from accidentally writing unrelated keys when
     * a partial form payload is submitted.
     *
     * @return array<string, mixed>
     */
    public function tabPayload(): array
    {
        $byGroup = [
            'general'       => ['app_name','app_logo','app_url','support_email','support_phone','default_timezone','default_language'],
            'trial'         => ['trial_enabled','trial_days','trial_requires_payment','trial_suspend_after_expiry','trial_warning_days'],
            'moyasar'       => ['moyasar_publishable_key','moyasar_secret_key','moyasar_webhook_secret','moyasar_test_mode','moyasar_enabled_methods'],
            'mail'          => ['mail_driver','mail_host','mail_port','mail_encryption','mail_username','mail_password','mail_from_address','mail_from_name'],
            'landing'       => ['hero_title','hero_subtitle','hero_cta_text','hero_cta_url','hero_image','footer_copyright','privacy_url','terms_url'],
            'notifications' => ['notify_new_subscription','notify_payment_failed','notify_trial_expiring','notify_subscription_expiring','admin_notification_email'],
        ];

        $group = (string) $this->validated('group');
        $keys  = $byGroup[$group] ?? [];

        return array_intersect_key($this->validated(), array_flip($keys));
    }
}
