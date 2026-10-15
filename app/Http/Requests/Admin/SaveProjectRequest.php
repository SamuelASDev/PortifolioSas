<?php

namespace App\Http\Requests\Admin;

use App\Models\Project;
use App\Rules\ProjectTagList;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');

        return $project instanceof Project
            ? $this->user()->can('update', $project)
            : $this->user()->can('create', Project::class);
    }

    public function rules(): array
    {
        return [
            'category' => ['nullable', Rule::in(array_keys(config('projects.categories')))],
            'tags' => ['sometimes', 'nullable', 'string', 'max:500', new ProjectTagList],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['present_with:description_format', 'nullable', 'string', 'max:50000'],
            'description_format' => ['sometimes', 'required', Rule::in(['text', 'html'])],
            'media_token' => ['sometimes', 'required', 'uuid'],
            'link_demo' => ['nullable', 'url:http,https', 'max:255'],
            'link_github' => ['nullable', 'url:http,https', 'max:255'],
            'is_featured' => ['required', 'boolean'],
            'display_order' => ['required', 'integer', 'min:0', 'max:2147483647'],
            'cover_image' => [
                'nullable',
                Rule::prohibitedIf(fn () => $this->boolean('remove_cover')),
                'file', 'image', 'mimes:jpg,jpeg,png,webp',
                'max:'.config('portfolio.covers.max_size_kb'),
                'dimensions:max_width='.config('portfolio.covers.max_dimension').',max_height='.config('portfolio.covers.max_dimension'),
            ],
            'remove_cover' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'cover_image.image' => 'Selecione uma imagem válida.',
            'cover_image.mimes' => 'A capa deve ser JPEG, PNG ou WebP.',
            'cover_image.max' => 'A capa deve ter no máximo 10 MB.',
            'cover_image.dimensions' => 'A capa deve ter no máximo 6000 × 6000 pixels.',
            'cover_image.prohibited' => 'Escolha entre enviar uma nova capa ou remover a atual.',
        ];
    }
}
