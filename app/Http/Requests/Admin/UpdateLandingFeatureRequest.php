<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * Phase D — same rules as Store; kept as a separate class so future
 * tweaks (e.g. allowing slug changes or adding moderation flags) don't
 * leak across create vs. update.
 */
final class UpdateLandingFeatureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('admin')->check()
            || Auth::guard('super_admin')->check();
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:1000'],
            'icon'        => ['nullable', 'string', 'max:64'],
            'image'       => ['nullable', 'string', 'max:500'],
            'is_active'   => ['nullable', 'boolean'],
            'sort_order'  => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active'  => $this->boolean('is_active', false),
            'sort_order' => (int) ($this->input('sort_order', 0)),
        ]);
    }
}
