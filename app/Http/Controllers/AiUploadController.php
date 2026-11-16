<?php

namespace App\Http\Controllers;

use App\Actions\StoreAiImage;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/** Recebe o arquivo enviado pela IA no link temporário gerado por request-image-upload-tool. */
class AiUploadController extends Controller
{
    public function __invoke(Request $request, Project $project, string $kind, string $nonce, StoreAiImage $store): JsonResponse
    {
        $request->headers->set('Accept', 'application/json'); // erros sempre em JSON para o curl da IA
        $request->validate(['file' => ['required', 'file']], ['file.required' => 'Envie a imagem no campo "file" (multipart/form-data).']);
        abort_unless($project->portfolio->user->ai_enabled, 403, 'O acesso por IA está desligado neste portfólio.');
        abort_unless(Cache::add('ai-upload:'.$nonce, true, now()->addHour()), 410, 'Este link de upload já foi usado. Peça um novo.');

        return response()->json($store->handle($project, $kind, $request->file('file')), 201);
    }
}
