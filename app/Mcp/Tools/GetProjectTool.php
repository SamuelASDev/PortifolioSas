<?php

namespace App\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
#[Description('Mostra um projeto completo, incluindo a descrição e o formato dela.')]
class GetProjectTool extends PortfolioTool
{
    public function handle(Request $request): Response
    {
        $id = $request->validate(['id' => ['required', 'integer']])['id'];
        $project = $this->portfolio($request)->projects()->find($id);

        return $project ? Response::json($this->projectData($project, true)) : Response::error('Projeto não encontrado neste portfólio.');
    }

    public function schema(JsonSchema $schema): array
    {
        return ['id' => $schema->integer()->description('ID do projeto (veja list-projects-tool).')->required()];
    }
}
