<?php

namespace App\Actions;

use App\Models\Project;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;

/**
 * Recebe uma imagem enviada por uma IA (upload por link ou download por URL)
 * e a define como capa ou a deixa pronta para entrar na descrição do projeto.
 */
class StoreAiImage
{
    public function __construct(private SaveProject $save, private UploadProjectImage $upload) {}

    /** Token de mídia fixo por projeto, para a IA anexar imagens sem lidar com tokens. */
    public static function mediaToken(Project $project): string
    {
        return Uuid::uuid5(Uuid::NAMESPACE_URL, config('app.key').'/ai-media/'.$project->id)->toString();
    }

    public function handle(Project $project, string $kind, UploadedFile $file): array
    {
        $max = $kind === 'cover' ? config('portfolio.covers.max_size_kb') : config('portfolio.attachments.max_size_kb');
        Validator::make(['image' => $file], ['image' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.$max, 'dimensions:max_width=6000,max_height=6000']], [
            'image.image' => 'O arquivo não é uma imagem válida.',
            'image.mimes' => 'Use JPEG, PNG ou WebP.',
            'image.max' => 'A imagem deve ter no máximo '.intdiv($max, 1024).' MB.',
            'image.dimensions' => 'A imagem deve ter no máximo 6000 × 6000 pixels.',
        ])->validate();
        $portfolio = $project->portfolio;

        if ($kind === 'cover') {
            $saved = $this->save->handle($portfolio, [], $project, $file);

            return ['message' => 'Capa definida.', 'cover_url' => $saved->cover_url];
        }

        $media = $this->upload->handle($portfolio, $file, self::mediaToken($project), $project);

        return [
            'message' => 'Imagem enviada. Para exibi-la, inclua o trecho "html" na descrição do projeto (description_format "html") com update-project-tool, trocando o alt por uma descrição da imagem. Se quiser legenda visível, acrescente <figcaption>...</figcaption> dentro do figure. Imagens não usadas em 24 horas são apagadas.',
            'media_id' => $media->id,
            'url' => $media->url,
            'html' => '<figure><img data-media-id="'.$media->id.'" alt="Descreva a imagem"></figure>',
        ];
    }
}
