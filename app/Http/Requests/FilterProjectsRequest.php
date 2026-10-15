<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterProjectsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'featured' => ['nullable', Rule::in(['0', '1'])],
            'category' => ['nullable', Rule::in(array_keys(config('projects.categories')))],
            'tag' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
