<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePortfolioAppearanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'theme' => ['required', Rule::in(['dark', 'light'])],
            'primary_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'appearance' => ['required', 'array:font,base_size,title_scale,colors'],
            'appearance.font' => ['required', Rule::in(array_keys(config('portfolio-appearance.fonts')))],
            'appearance.base_size' => ['required', 'integer', Rule::in([14, 16, 18])],
            'appearance.title_scale' => ['required', 'integer', Rule::in([90, 100, 110])],
            'appearance.colors' => ['present', 'array:'.implode(',', array_keys(config('portfolio-appearance.colors')))],
            'appearance.colors.*' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ];
    }
}
