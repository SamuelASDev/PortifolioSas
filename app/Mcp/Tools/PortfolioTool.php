<?php

namespace App\Mcp\Tools;

use App\Models\Portfolio;
use App\Models\Project;
use App\Rules\ProjectTagList;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Laravel\Mcp\Request;
use Laravel\Mcp\Server\Tool;

abstract class PortfolioTool extends Tool
{
    protected function portfolio(Request $request): Portfolio
    {
        return $request->user()->portfolio;
    }

    protected function projectData(Project $project, bool $full = false): array
    {
        $project->loadMissing('portfolio', 'tags');
        $data = [
            'id' => $project->id,
            'title' => $project->title,
            'category' => $project->category,
            'status' => $project->status,
            'tags' => $project->tags->pluck('name')->all(),
            'is_featured' => $project->is_featured,
            'display_order' => $project->display_order,
            'link_demo' => $project->link_demo,
            'link_github' => $project->link_github,
            'admin_url' => route('admin.projects.edit', $project),
            'public_url' => route('portfolio.projects.show', [$project->portfolio->slug, $project->id]),
        ];

        return $full
            ? $data + ['description' => $project->description, 'description_format' => $project->description_format ?? 'text']
            : $data + ['excerpt' => Str::limit((string) $project->description_text, 160)];
    }

    /** Regras de projeto compartilhadas por criar e editar (mesmas do formulário do painel). */
    protected function projectRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:50000'],
            'description_format' => ['sometimes', Rule::in(['text', 'html'])],
            'category' => ['nullable', Rule::in(array_keys(config('projects.categories')))],
            'status' => ['nullable', Rule::in(array_keys(config('projects.statuses')))],
            'tags' => ['nullable', 'string', 'max:500', new ProjectTagList],
            'link_demo' => ['nullable', 'url:http,https', 'max:255'],
            'link_github' => ['nullable', 'url:http,https', 'max:255'],
            'is_featured' => ['sometimes', 'boolean'],
        ];
    }

    protected function projectSchema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->description('Título do projeto (até 255 caracteres).'),
            'description' => $schema->string()->description('Descrição completa. Em "text", linhas em branco separam parágrafos. Em "html", use apenas p, h2, h3, strong, em, ul, ol, li, a, blockquote, code e as imagens (figure/img com data-media-id) devolvidas pelas ferramentas de upload.'),
            'description_format' => $schema->string()->enum(['text', 'html'])->description('Formato da descrição. Padrão: text.'),
            'category' => $schema->string()->enum(array_keys(config('projects.categories')))->description('Categoria do projeto.'),
            'status' => $schema->string()->enum(array_keys(config('projects.statuses')))->description('Situação: completed, in_development, maintenance, paused ou planned.'),
            'tags' => $schema->string()->description('Tecnologias separadas por vírgula, no máximo 8 (ex.: "Laravel, Vue, MySQL").'),
            'link_demo' => $schema->string()->description('URL pública do projeto (http/https).'),
            'link_github' => $schema->string()->description('URL do repositório (http/https).'),
            'is_featured' => $schema->boolean()->description('Exibir entre os destaques do portfólio.'),
        ];
    }
}
