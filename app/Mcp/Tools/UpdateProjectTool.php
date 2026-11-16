<?php

namespace App\Mcp\Tools;

use App\Actions\SaveProject;
use App\Actions\StoreAiImage;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;

#[IsIdempotent]
#[Description('Edita um projeto existente. Envie só os campos que devem mudar; os demais ficam como estão.')]
class UpdateProjectTool extends PortfolioTool
{
    public function handle(Request $request, SaveProject $save): Response
    {
        $rules = collect($this->projectRules())->map(fn ($rule) => ['sometimes', ...$rule])->all();
        $data = $request->validate(['id' => ['required', 'integer']] + $rules);
        $portfolio = $this->portfolio($request);
        $project = $portfolio->projects()->find($data['id']);
        if (! $project) {
            return Response::error('Projeto não encontrado neste portfólio.');
        }
        unset($data['id']);
        if (array_key_exists('description', $data)) {
            $data['description_format'] ??= 'text';
            $data['media_token'] = StoreAiImage::mediaToken($project);
        }

        $saved = $save->handle($portfolio, $data, $project);

        return Response::json(['message' => 'Projeto atualizado.'] + $this->projectData($saved->fresh()));
    }

    public function schema(JsonSchema $schema): array
    {
        return ['id' => $schema->integer()->description('ID do projeto.')->required()] + $this->projectSchema($schema);
    }
}
