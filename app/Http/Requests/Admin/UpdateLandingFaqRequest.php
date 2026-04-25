<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

final class UpdateLandingFaqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('admin')->check()
            || Auth::guard('super_admin')->check();
    }

    public function rules(): array
    {
        return [
            'question'   => ['required', 'string', 'max:500'],
            'answer'     => ['required', 'string', 'max:4000'],
            'is_active'  => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
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
