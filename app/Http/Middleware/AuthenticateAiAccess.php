<?php

namespace App\Http\Middleware;

use App\Models\AiAccessToken;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Mcp\Server\Registrar;
use Symfony\Component\HttpFoundation\Response;

/**
 * Autentica a IA pelo token manual (vtx_...) ou por um token OAuth (Passport),
 * e só libera o acesso se o usuário mantiver a integração ligada.
 */
class AuthenticateAiAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $bearer = (string) $request->bearerToken();
        $user = null;

        if ($token = AiAccessToken::findByPlain($bearer)) {
            $user = $token->user;
            if (! $token->last_used_at || $token->last_used_at->lt(now()->subMinute())) {
                $token->forceFill(['last_used_at' => now()])->save();
            }
        } elseif ($bearer !== '' && ! str_starts_with($bearer, AiAccessToken::PREFIX)) {
            $candidate = Auth::guard('api')->user();
            $user = $candidate instanceof User && $candidate->tokenCan(Registrar::OAUTH_SCOPE) ? $candidate : null;
        }

        if (! $user) {
            return response()->json(['message' => 'Token de acesso ausente ou inválido.'], 401);
        }
        if (! $user->ai_enabled || ! $user->portfolio) {
            return response()->json(['message' => 'O acesso por IA está desligado neste portfólio. Ative-o em Integrações com IA no painel.'], 403);
        }

        Auth::setUser($user);

        return $next($request);
    }
}
