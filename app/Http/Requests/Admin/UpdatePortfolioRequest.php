<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdatePortfolioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('slug'))) {
            $this->merge(['slug' => Str::slug($this->input('slug'))]);
        }
    }

    public function rules(): array
    {
        return [
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::notIn(config('portfolio.reserved_slugs')), Rule::unique('portfolios', 'slug')->ignore($this->user()->portfolio)],
            'theme' => ['required', Rule::in(['dark', 'light'])],
            'primary_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.not_in' => 'Este endereço é reservado. Escolha outro.',
            'slug.unique' => 'Este endereço já está em uso.',
            'primary_color.regex' => 'Informe uma cor hexadecimal no formato #RRGGBB.',
        ];
    }
}
