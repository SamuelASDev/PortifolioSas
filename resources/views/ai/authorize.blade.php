<x-guest-layout>
    <p class="studio-eyebrow">CONECTAR IA</p>
    <h1 class="studio-auth-title">{{ $client->name }}<br>quer acessar<br>seu portfólio.</h1>
    <p class="ai-muted">Com sua permissão, esse assistente poderá ler seu portfólio, criar e editar projetos, enviar imagens e atualizar seus textos (Sobre, Contato, bio). Ele não pode apagar projetos, publicar o portfólio nem mexer na sua conta.</p>
    <p class="ai-muted ai-small">Conectado como {{ $user->email }}. Você pode desconectar a qualquer momento no painel.</p>
    @unless ($user->ai_enabled)
        <p class="ai-flash is-warning">O acesso por IA está desligado na sua conta. Ligue-o para continuar.</p>
        <form method="POST" action="{{ route('admin.ai.update') }}" class="ai-authorize-actions">
            @csrf @method('PATCH')
            <input type="hidden" name="enabled" value="1">
            <button type="submit" class="studio-button studio-button-primary w-full">Ligar acesso por IA</button>
        </form>
    @else
    <div class="ai-authorize-actions">
        <form method="POST" action="{{ route('passport.authorizations.approve') }}">
            @csrf
            <input type="hidden" name="state" value="{{ $request->state }}">
            <input type="hidden" name="client_id" value="{{ $client->getKey() }}">
            <input type="hidden" name="auth_token" value="{{ $authToken }}">
            <button type="submit" class="studio-button studio-button-primary w-full">Autorizar</button>
        </form>
        <form method="POST" action="{{ route('passport.authorizations.deny') }}">
            @csrf @method('DELETE')
            <input type="hidden" name="state" value="{{ $request->state }}">
            <input type="hidden" name="client_id" value="{{ $client->getKey() }}">
            <input type="hidden" name="auth_token" value="{{ $authToken }}">
            <button type="submit" class="studio-button studio-button-secondary w-full">Cancelar</button>
        </form>
    </div>
    @endunless
</x-guest-layout>
