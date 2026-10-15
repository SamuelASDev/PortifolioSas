<?php

namespace App\Http\Requests\Admin;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSharingRequest extends FormRequest
{
    protected $errorBag = 'sharing';

    public function authorize(): bool
    {
        $project = $this->route('project');

        return $project instanceof Project ? $this->user()->can('update', $project) : $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'share_title' => ['nullable', 'string', 'max:100'],
            'share_description' => ['nullable', 'string', 'max:300'],
            'remove_share_image' => ['sometimes', 'boolean'],
            'share_image' => ['nullable', Rule::prohibitedIf(fn () => $this->boolean('remove_share_image')),
                'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.config('portfolio.sharing.max_size_kb'),
                'dimensions:max_width='.config('portfolio.sharing.max_dimension').',max_height='.config('portfolio.sharing.max_dimension')],
        ];
    }

    public function messages(): array
    {
        return [
            'share_image.mimes' => 'A imagem deve ser JPEG, PNG ou WebP.',
            'share_image.max' => 'A imagem deve ter no máximo 2 MB.',
            'share_image.dimensions' => 'A imagem deve ter no máximo 6000 × 6000 pixels.',
            'share_image.prohibited' => 'Escolha entre enviar uma imagem ou remover a atual.',
        ];
    }
}
