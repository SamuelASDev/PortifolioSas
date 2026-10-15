@php
    $pageTitle = match (true) {
        request()->routeIs('dashboard') => 'Visão geral',
        request()->routeIs('admin.projects.create') => 'Novo projeto',
        request()->routeIs('admin.projects.edit') => 'Editar projeto',
        request()->routeIs('admin.projects.organize') => 'Organizar projetos',
        request()->routeIs('admin.projects.*') => 'Projetos',
        request()->routeIs('profile.*') => 'Minha conta',
        default => 'Meu portfólio',
    };
    $pageDescription = match (true) {
        request()->routeIs('dashboard') => 'Um novo olhar para o que você está construindo.',
        request()->routeIs('admin.projects.*') => 'Cada projeto tem uma história. Dê espaço para a sua.',
        request()->routeIs('profile.*') => 'Cuide dos seus dados e da segurança do seu espaço.',
        default => 'Sua identidade, seus detalhes, sua melhor apresentação.',
    };
@endphp
<!DOCTYPE html>
<html lang="pt-BR" class="dark">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle }} · {{ config('brand.name') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="studio studio-admin">
    <a href="#studio-content" class="studio-skip">Pular para o conteúdo</a>
    @include('layouts.navigation')
    <div class="studio-workspace">
        <div class="studio-topbar"><span>Seu espaço <span class="studio-breadcrumb-slash">/</span> <strong>{{ $pageTitle }}</strong></span><a href="{{ url('/') }}" class="studio-topbar-signature">{{ config('brand.tagline') }} <span aria-hidden="true">↗</span></a></div>
        <main id="studio-content" class="studio-main" tabindex="-1">
            @isset($header)<header class="studio-page-heading"><p class="studio-eyebrow">PAINEL CRIATIVO</p><div>{{ $header }}</div><p>{{ $pageDescription }}</p></header>@endisset
            {{ $slot }}
        </main>
        <footer class="studio-workspace-footer"><span>Feito para o seu próximo passo.</span><span>{{ config('brand.name') }} © {{ date('Y') }}</span></footer>
    </div>
</body>
</html>
