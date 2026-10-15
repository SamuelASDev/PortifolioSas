<?php

namespace App\Http\Requests\Admin;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrganizeProjectsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Project::class);
    }

    public function rules(): array
    {
        return [
            'revision' => ['required', 'string', 'regex:/^[a-f0-9]{64}$/'],
            'projects' => ['present', 'array', 'list'],
            'projects.*' => ['required', 'array:id,is_featured'],
            'projects.*.id' => ['required', 'integer', 'distinct', Rule::exists('projects', 'id')->where('portfolio_id', $this->user()->portfolio->id)],
            'projects.*.is_featured' => ['required', 'boolean'],
        ];
    }
}
