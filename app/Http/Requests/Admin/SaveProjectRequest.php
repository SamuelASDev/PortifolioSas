<?php

namespace App\Http\Requests\Admin;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;

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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:50000'],
            'link_demo' => ['nullable', 'url:http,https', 'max:255'],
            'link_github' => ['nullable', 'url:http,https', 'max:255'],
            'is_featured' => ['required', 'boolean'],
            'display_order' => ['required', 'integer', 'min:0', 'max:2147483647'],
        ];
    }
}
