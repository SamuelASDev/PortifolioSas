<?php

use App\Http\Middleware\AuthenticateAiAccess;
use App\Mcp\Servers\VerticeServer;
use Laravel\Mcp\Facades\Mcp;

// Servidor MCP do Vértice: aceita token manual (vtx_...) ou OAuth (Passport).
Mcp::oauthRoutes();

Mcp::web('/mcp', VerticeServer::class)
    ->middleware([AuthenticateAiAccess::class, 'throttle:60,1']);
