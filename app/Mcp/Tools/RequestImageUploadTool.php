<?php

namespace App\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Gera um link temporário (10 minutos, uso único) para enviar uma imagem do computador como capa ("cover") ou imagem da descrição ("image") de um projeto. Envie o arquivo com o comando curl retornado. JPEG, PNG ou WebP, até 10 MB e 6000 × 6000 px. Use quando você tiver acesso a um terminal; caso contrário, use upload-image-from-url-tool.')]
class RequestImageUploadTool extends PortfolioTool
{
    public function handle(Request $request): Response
    {
        $data = $request->validate(['project_id' => ['required', 'integer'], 'kind' => ['required', 'in:cover,image']]);
        $project = $this->portfolio($request)->projects()->find($data['project_id']);
        if (! $project) {
            return Response::error('Projeto não encontrado neste portfólio.');
        }
        $url = URL::temporarySignedRoute('ai.upload', now()->addMinutes(10), [$project, $data['kind'], Str::random(32)]);

        return Response::json([
            'upload_url' => $url,
            'expires_in_minutes' => 10,
            'command' => 'curl -sS -X POST -F "file=@CAMINHO_DA_IMAGEM.png" "'.$url.'"',
            'next' => $data['kind'] === 'cover'
                ? 'Depois do envio, a capa já fica definida.'
                : 'A resposta traz um trecho "html": inclua-o na descrição com update-project-tool (description_format "html").',
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'project_id' => $schema->integer()->description('ID do projeto.')->required(),
            'kind' => $schema->string()->enum(['cover', 'image'])->description('"cover" para a capa, "image" para uma imagem dentro da descrição.')->required(),
        ];
    }
}
