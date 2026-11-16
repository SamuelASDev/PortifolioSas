<?php

namespace App\Mcp\Tools;

use App\Actions\SaveProject;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Cria um projeto no portfólio. Ele aparece no fim da lista e, se o portfólio estiver publicado, fica visível na hora. A capa e as imagens são enviadas pelo painel.')]
class CreateProjectTool extends PortfolioTool
{
    public function handle(Request $request, SaveProject $save): Response
    {
        $data = $request->validate($this->projectRules());
        $portfolio = $this->portfolio($request);
        $data += ['is_featured' => false, 'display_order' => (int) $portfolio->projects()->max('display_order') + 1];
        if (array_key_exists('description', $data)) {
            $data['description_format'] ??= 'text';
        }

        $project = $save->handle($portfolio, $data);

        return Response::json(['message' => 'Projeto criado.'] + $this->projectData($project->fresh()));
    }

    public function schema(JsonSchema $schema): array
    {
        $fields = $this->projectSchema($schema);
        $fields['title']->required();

        return $fields;
    }
}
