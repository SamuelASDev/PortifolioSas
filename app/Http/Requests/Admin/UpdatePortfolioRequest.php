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
            'show_featured_carousel' => ['sometimes', 'boolean'],
            'primary_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'professional_name' => ['nullable', 'string', 'max:255'],
            'headline' => ['nullable', 'string', 'max:160'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'github_url' => ['nullable', 'url:http,https', 'max:255'],
            'linkedin_url' => ['nullable', 'url:http,https', 'max:255'],
            'website_url' => ['nullable', 'url:http,https', 'max:255'],
            'photo' => [
                'nullable', Rule::prohibitedIf(fn () => $this->boolean('remove_photo')),
                'file', 'image', 'mimes:jpg,jpeg,png,webp',
                'max:'.config('portfolio.photos.max_size_kb'),
                'dimensions:max_width='.config('portfolio.photos.max_dimension').',max_height='.config('portfolio.photos.max_dimension'),
            ],
            'remove_photo' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.not_in' => 'Este endereço é reservado. Escolha outro.',
            'slug.unique' => 'Este endereço já está em uso.',
            'primary_color.regex' => 'Informe uma cor hexadecimal no formato #RRGGBB.',
            'photo.image' => 'Selecione uma foto válida.',
            'photo.mimes' => 'A foto deve ser JPEG, PNG ou WebP.',
            'photo.max' => 'A foto deve ter no máximo 10 MB.',
            'photo.dimensions' => 'A foto deve ter no máximo 3000 × 3000 pixels.',
            'photo.prohibited' => 'Escolha entre enviar uma nova foto ou remover a atual.',
        ];
    }
}
