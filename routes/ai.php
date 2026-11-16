<?php

use App\Http\Controllers\AiUploadController;
use App\Http\Middleware\AuthenticateAiAccess;
use App\Mcp\Servers\VerticeServer;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;
use Laravel\Mcp\Facades\Mcp;

// Servidor MCP do Vértice: aceita token manual (vtx_...) ou OAuth (Passport).
Mcp::oauthRoutes();

Mcp::web('/mcp', VerticeServer::class)
    ->middleware([AuthenticateAiAccess::class, 'throttle:60,1']);

// Upload de imagem pela IA: link assinado, temporário e de uso único (gerado por request-image-upload-tool).
Route::post('/mcp/upload/{project}/{kind}/{nonce}', AiUploadController::class)
    ->whereNumber('project')->whereIn('kind', ['cover', 'image'])
    ->middleware(['signed', 'throttle:20,1', SubstituteBindings::class])->name('ai.upload');
