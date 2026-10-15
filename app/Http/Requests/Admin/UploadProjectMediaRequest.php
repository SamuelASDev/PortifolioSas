<?php

namespace App\Http\Requests\Admin;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;

class UploadProjectMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Project::class);
    }

    public function rules(): array
    {
        return [
            'image' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp',
                'max:'.config('portfolio.attachments.max_size_kb'),
                'dimensions:max_width=6000,max_height=6000'],
            'upload_token' => ['required', 'uuid'],
            'project_id' => ['nullable', 'integer'],
        ];
    }

    public function messages(): array
    {
        return ['image.max' => 'A imagem deve ter no máximo 2 MB.', 'image.mimes' => 'Use uma imagem JPEG, PNG ou WebP.'];
    }
}
