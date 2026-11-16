<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiAccessToken;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Laravel\Passport\Token;

class AiAccessController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();
        $apps = Token::with('client')->where('user_id', $user->id)->where('revoked', false)
            ->where('expires_at', '>', now())->latest()->get()->unique('client_id')->values();

        return view('admin.ai.show', [
            'enabled' => $user->ai_enabled,
            'tokens' => $user->aiAccessTokens()->get(),
            'apps' => $apps,
            'newToken' => session('ai_token'),
            'mcpUrl' => url('/mcp'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->user()->forceFill(['ai_enabled' => $request->boolean('enabled')])->save();

        return back()->with('status', $request->boolean('enabled') ? 'Acesso por IA ligado.' : 'Acesso por IA desligado. Nenhuma IA consegue mexer no seu portfólio agora.');
    }

    public function storeToken(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'days' => ['required', 'integer', 'in:'.implode(',', array_keys(AiAccessToken::DURATIONS))],
        ]);
        abort_if($request->user()->aiAccessTokens()->count() >= 10, 422, 'Limite de 10 tokens. Revogue um antes de criar outro.');
        [, $plain] = AiAccessToken::issue($request->user(), $data['name'], (int) $data['days']);

        return back()->with('ai_token', $plain);
    }

    public function destroyToken(Request $request, AiAccessToken $token): RedirectResponse
    {
        abort_unless($token->user_id === $request->user()->id, 404);
        $token->delete();

        return back()->with('status', 'Token revogado.');
    }

    public function destroyApp(Request $request, string $client): RedirectResponse
    {
        Token::where('user_id', $request->user()->id)->where('client_id', $client)->get()
            ->each(fn (Token $token) => $token->revoke() && $token->refreshToken?->revoke());

        return back()->with('status', 'Aplicativo desconectado.');
    }
}
