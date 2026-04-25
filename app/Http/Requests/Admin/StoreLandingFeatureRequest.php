<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * Phase D — validate the create form for a landing-page Feature block.
 *
 * Authorisation matches the established pattern: this FormRequest only
 * runs from inside the `admin.role:super_admin` route group, so the route
 * middleware already gated access. We still verify *some* admin guard is
 * authenticated as defence-in-depth.
 */
final class StoreLandingFeatureRequest extends FormRequest
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

    /**
     * Treat unchecked checkbox + missing sort_order as their natural defaults.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active'  => $this->boolean('is_active', true),
            'sort_order' => (int) ($this->input('sort_order', 0)),
        ]);
    }
}
