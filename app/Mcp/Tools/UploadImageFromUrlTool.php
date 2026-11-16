<?php

namespace App\Mcp\Tools;

use App\Actions\StoreAiImage;
use App\Support\SafeImageDownloader;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Baixa uma imagem de um endereço público (http/https) e a usa como capa ("cover") ou imagem da descrição ("image") de um projeto. JPEG, PNG ou WebP, até 10 MB e 6000 × 6000 px.')]
class UploadImageFromUrlTool extends PortfolioTool
{
    public function handle(Request $request, SafeImageDownloader $downloader, StoreAiImage $store): Response
    {
        $data = $request->validate([
            'project_id' => ['required', 'integer'],
            'kind' => ['required', 'in:cover,image'],
            'url' => ['required', 'url:http,https', 'max:2048'],
        ]);
        $project = $this->portfolio($request)->projects()->find($data['project_id']);
        if (! $project) {
            return Response::error('Projeto não encontrado neste portfólio.');
        }
        $file = $downloader->download($data['url']);
        if (is_string($file)) {
            return Response::error($file);
        }

        try {
            return Response::json($store->handle($project, $data['kind'], $file));
        } finally {
            @unlink($file->getPathname());
        }
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'project_id' => $schema->integer()->description('ID do projeto.')->required(),
            'kind' => $schema->string()->enum(['cover', 'image'])->description('"cover" para a capa, "image" para uma imagem dentro da descrição.')->required(),
            'url' => $schema->string()->description('Endereço público da imagem.')->required(),
        ];
    }
}
