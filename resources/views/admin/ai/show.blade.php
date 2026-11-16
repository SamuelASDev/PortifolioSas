@php
    $token = $newToken ?: 'SEU_TOKEN';
    $clients = [
        'claude' => ['label' => 'Claude (site e app)', 'oauth' => true, 'steps' => [
            'No Claude, abra Configurações → Conectores → Adicionar conector personalizado.',
            'Dê o nome "Vértice" e cole o endereço abaixo.',
            'Clique em Conectar, entre na sua conta do Vértice e autorize.',
        ], 'commands' => [['Endereço do conector', $mcpUrl]]],
        'claude-code' => ['label' => 'Claude Code', 'oauth' => true, 'steps' => [
            'Rode o comando no terminal. Depois, dentro do Claude Code, digite /mcp e escolha "vertice" para autorizar no navegador.',
            'Prefere token? Use o segundo comando no lugar do primeiro.',
        ], 'commands' => [
            ['Com login (OAuth)', "claude mcp add --transport http vertice {$mcpUrl}"],
            ['Com token', "claude mcp add --transport http vertice {$mcpUrl} --header \"Authorization: Bearer {$token}\""],
        ]],
        'codex' => ['label' => 'Codex', 'oauth' => true, 'steps' => [
            'Rode os dois comandos: o primeiro adiciona o servidor, o segundo abre o navegador para autorizar.',
            'Prefere token? Salve-o na variável VERTICE_TOKEN e use o terceiro comando.',
        ], 'commands' => [
            ['Adicionar', "codex mcp add vertice --url {$mcpUrl}"],
            ['Autorizar (OAuth)', 'codex mcp login vertice'],
            ['Com token', "codex mcp add vertice --url {$mcpUrl} --bearer-token-env-var VERTICE_TOKEN"],
        ]],
        'other' => ['label' => 'Cursor e outros', 'oauth' => false, 'steps' => [
            'Na configuração de servidores MCP do seu editor ou assistente, adicione:',
        ], 'commands' => [
            ['Configuração (JSON)', json_encode(['mcpServers' => ['vertice' => ['url' => $mcpUrl, 'headers' => ['Authorization' => "Bearer {$token}"]]]], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)],
        ]],
    ];
    $abilities = [
        ['profile', 'Ler seu portfólio', 'Nome, apresentação, Sobre, Contato e links.'],
        ['code', 'Listar e ler projetos', 'Para entender o que você já publicou.'],
        ['pen', 'Criar projetos', 'Título, descrição, categoria, situação, tecnologias e links.'],
        ['gear', 'Editar projetos', 'Só os campos que você pedir.'],
        ['book', 'Atualizar seus textos', 'Título profissional, bio, página Sobre e mensagem de Contato.'],
        ['layout', 'Enviar imagens', 'Capa e imagens da descrição, por um link temporário de uso único ou por URL pública.'],
    ];
@endphp
<x-app-layout>
    <x-slot name="header"><h1>Integrações com IA</h1></x-slot>
    <div class="ai-page">
        @if (session('status'))<p class="ai-flash" role="status">{{ session('status') }}</p>@endif
        @if ($errors->any())<p class="ai-flash is-error" role="alert">{{ $errors->first() }}</p>@endif

        <section class="studio-panel ai-switch-panel" aria-labelledby="ai-switch-title">
            <div>
                <p class="studio-eyebrow">MCP · CLAUDE, CODEX E OUTROS</p>
                <h2 id="ai-switch-title">Deixe uma IA cuidar do seu portfólio</h2>
                <p class="ai-muted">Conecte o assistente que você já usa e peça coisas como “cadastra o projeto deste repositório” ou “reescreve meu Sobre”. Você pode desligar a qualquer momento: o acesso é cortado na hora, para todas as IAs.</p>
            </div>
            <form method="POST" action="{{ route('admin.ai.update') }}" class="ai-switch-form">
                @csrf @method('PATCH')
                <input type="hidden" name="enabled" value="{{ $enabled ? 0 : 1 }}">
                <span @class(['studio-status', 'is-live' => $enabled])>{{ $enabled ? 'Ligado' : 'Desligado' }}</span>
                <button type="submit" class="ai-toggle" role="switch" aria-checked="{{ $enabled ? 'true' : 'false' }}" aria-label="{{ $enabled ? 'Desligar' : 'Ligar' }} acesso por IA"><span></span></button>
            </form>
        </section>

        <div class="ai-grid">
            <section class="studio-panel" aria-labelledby="ai-connect-title" x-data="{ tab: 'claude', copied: null, copy(text, key) { navigator.clipboard.writeText(text).then(() => { this.copied = key; setTimeout(() => this.copied = null, 1600); }); } }">
                <div class="studio-section-heading"><div><h3 id="ai-connect-title">Conectar sua IA</h3><p>Escolha onde você usa a IA e siga os passos.</p></div><x-public-icon name="sparkles" /></div>
                @unless ($enabled)<p class="ai-flash is-warning">O acesso está desligado. Ligue a chave acima antes de conectar.</p>@endunless
                <div class="appearance-segmented ai-tabs" role="tablist" aria-label="Assistente">
                    @foreach ($clients as $key => $client)
                        <button type="button" role="tab" :aria-selected="tab === '{{ $key }}'" @click="tab = '{{ $key }}'" :class="{ 'is-active': tab === '{{ $key }}' }">{{ $client['label'] }}</button>
                    @endforeach
                </div>
                @foreach ($clients as $key => $client)
                    <div role="tabpanel" x-show="tab === '{{ $key }}'" @if (! $loop->first) x-cloak @endif class="ai-tabpanel">
                        <ol class="ai-steps">@foreach ($client['steps'] as $step)<li>{{ $step }}</li>@endforeach</ol>
                        @foreach ($client['commands'] as $index => [$label, $command])
                            <div class="ai-command">
                                <div class="ai-command-head"><span>{{ $label }}</span><button type="button" class="appearance-control" @click="copy(@js($command), '{{ $key.$index }}')"><x-public-icon name="copy" /> <span x-text="copied === '{{ $key.$index }}' ? 'Copiado!' : 'Copiar'">Copiar</span></button></div>
                                <pre><code>{{ $command }}</code></pre>
                            </div>
                        @endforeach
                        @if ($client['oauth'])<p class="ai-muted ai-small">O login (OAuth) só funciona com o Vértice publicado em HTTPS. Em testes locais, use o token.</p>@endif
                    </div>
                @endforeach
            </section>

            <section class="studio-panel" aria-labelledby="ai-abilities-title">
                <div class="studio-section-heading"><div><h3 id="ai-abilities-title">O que a IA pode fazer</h3><p>Nada além disso: ela não apaga projetos, não publica e não mexe na sua conta.</p></div></div>
                <ul class="ai-abilities">
                    @foreach ($abilities as [$icon, $title, $text])<li><span class="studio-step-icon"><x-public-icon :name="$icon" /></span><div><strong>{{ $title }}</strong><small>{{ $text }}</small></div></li>@endforeach
                </ul>
            </section>
        </div>

        <section class="studio-panel" aria-labelledby="ai-tokens-title">
            <div class="studio-section-heading"><div><h3 id="ai-tokens-title">Tokens de acesso</h3><p>Para assistentes que pedem uma chave. Cada token dá acesso total às funções acima; não compartilhe.</p></div><x-public-icon name="gear" /></div>
            @if ($newToken)
                <div class="ai-new-token" x-data="{ copied: false }" role="status">
                    <strong>Seu novo token. Copie agora: ele não será mostrado de novo.</strong>
                    <div class="ai-command"><pre><code>{{ $newToken }}</code></pre></div>
                    <button type="button" class="studio-button studio-button-primary" @click="navigator.clipboard.writeText(@js($newToken)).then(() => copied = true)"><span x-text="copied ? 'Copiado!' : 'Copiar token'">Copiar token</span></button>
                    <p class="ai-muted ai-small">Os comandos em “Conectar sua IA” já estão com este token preenchido.</p>
                </div>
            @endif
            <form method="POST" action="{{ route('admin.ai.tokens.store') }}" class="ai-token-form">
                @csrf
                <label for="ai-token-name" class="sr-only">Nome do token</label>
                <input id="ai-token-name" name="name" required maxlength="60" placeholder="Ex.: Claude Code no notebook" class="ai-input" value="{{ old('name') }}">
                <button type="submit" class="studio-button studio-button-primary">Gerar token</button>
            </form>
            @if ($tokens->isNotEmpty() || $apps->isNotEmpty())
                <ul class="ai-list">
                    @foreach ($tokens as $item)
                        <li>
                            <div><strong>{{ $item->name }}</strong><small>Token · criado {{ $item->created_at->diffForHumans() }} · {{ $item->last_used_at ? 'usado '.$item->last_used_at->diffForHumans() : 'nunca usado' }}</small></div>
                            <form method="POST" action="{{ route('admin.ai.tokens.destroy', $item) }}" onsubmit="return confirm('Revogar este token? A IA que usa ele perde o acesso.')">@csrf @method('DELETE')<button type="submit" class="appearance-control">Revogar</button></form>
                        </li>
                    @endforeach
                    @foreach ($apps as $app)
                        <li>
                            <div><strong>{{ $app->client?->name ?? 'Aplicativo' }}</strong><small>Conectado por login · {{ $app->created_at->diffForHumans() }}</small></div>
                            <form method="POST" action="{{ route('admin.ai.apps.destroy', $app->client_id) }}" onsubmit="return confirm('Desconectar este aplicativo?')">@csrf @method('DELETE')<button type="submit" class="appearance-control">Desconectar</button></form>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="ai-muted">Nenhuma IA conectada ainda.</p>
            @endif
        </section>
    </div>
</x-app-layout>
