<!DOCTYPE html>
<html lang="pt-BR" class="dark">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ request()->routeIs('register') ? 'Crie seu espaço' : (request()->routeIs('login') ? 'Bem-vindo de volta' : 'Sua conta') }} · {{ config('brand.name') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="studio studio-auth">
    <div class="studio-auth-story">
        <x-brand />
        <div class="studio-auth-story-copy"><span class="studio-eyebrow">SEU TRABALHO, EM PERSPECTIVA</span><div class="studio-auth-story-title">Grandes histórias<br>começam com<br><em>o seu trabalho.</em></div><p>Um espaço para suas ideias ganharem forma, seus projetos serem vistos e novas oportunidades acontecerem.</p><div class="studio-auth-art" aria-hidden="true"><span class="studio-art-frame"></span><svg viewBox="0 0 300 220" fill="none"><path d="m38 32 90 157h43L262 32h-63l-49 86-49-86Z" stroke="currentColor" stroke-width="1"/><path d="m101 32 49 86 20 35 70-121" stroke="currentColor" stroke-width="1"/><circle cx="239" cy="51" r="9" fill="#c7f3c5"/></svg><span class="studio-art-caption">IDEIAS → PROJETOS → POSSIBILIDADES</span></div></div>
        <p class="studio-auth-story-footer">Sua trajetória merece uma boa apresentação.</p>
    </div>
    <main class="studio-auth-main" id="conteudo">
        <div class="studio-auth-mobile-brand"><x-brand /></div>
        <a href="{{ url('/') }}" class="studio-auth-back">← Voltar ao início</a>
        <div class="studio-auth-card">{{ $slot }}</div>
        <footer class="studio-auth-footer">{{ config('brand.name') }} · Um espaço que é seu.</footer>
    </main>
</body>
</html>
