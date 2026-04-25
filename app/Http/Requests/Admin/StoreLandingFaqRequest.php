<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * Phase D — validate the create form for a landing-page FAQ entry.
 *
 * The `answer` accepts longer text than `question` because operators
 * sometimes paste short policy paragraphs. HTML is currently NOT allowed
 * (Blade `{{ }}` escapes on render); when we do accept HTML we'll switch
 * to a safe-list filter rather than relaxing the rule here.
 */
final class StoreLandingFaqRequest extends FormRequest
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
            'is_active'  => $this->boolean('is_active', true),
            'sort_order' => (int) ($this->input('sort_order', 0)),
        ]);
    }
}
