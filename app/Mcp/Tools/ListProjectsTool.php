<?php

namespace App\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
#[Description('Lista os projetos do portfólio na ordem de exibição, com um resumo da descrição. Aceita busca opcional pelo título.')]
class ListProjectsTool extends PortfolioTool
{
    public function handle(Request $request): Response
    {
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:100']]);
        $projects = $this->portfolio($request)->projects()->with('tags')
            ->filter(['q' => $filters['search'] ?? null])
            ->orderBy('display_order')->orderBy('id')->limit(100)->get();

        return Response::json($projects->map(fn ($project) => $this->projectData($project))->all());
    }

    public function schema(JsonSchema $schema): array
    {
        return ['search' => $schema->string()->description('Trecho do título para filtrar.')];
    }
}
