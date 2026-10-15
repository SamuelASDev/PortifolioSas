@props(['portfolio', 'pageTitle' => null, 'description' => null, 'image' => null, 'preview' => false])
@php
    $appearance = app(\App\Support\PortfolioAppearance::class);
    $accent = $appearance->color($portfolio->primary_color);
    $title = $pageTitle ?? $portfolio->professional_name.' — Portfólio';
    $summary = \Illuminate\Support\Str::limit($description ?? $portfolio->bio ?? 'Conheça meus projetos e minha trajetória profissional.', 160);
@endphp
<!DOCTYPE html>
<html lang="pt-BR" style="font-size: {{ $appearance->settings($portfolio)['appearance']['base_size'] }}px">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @if ($preview)
        <meta name="robots" content="noindex, nofollow">
        <script type="application/json" data-preview-schema>{!! \Illuminate\Support\Js::encode(config('portfolio-appearance')) !!}</script>
        @vite('resources/js/public-preview.js')
    @endif
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $summary }}">
    <meta name="theme-color" content="{{ $accent }}">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $summary }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    @if ($image ?? $portfolio->photo_url)
        <meta property="og:image" content="{{ $image ?? $portfolio->photo_url }}">
    @endif
    <link rel="canonical" href="{{ url()->current() }}">
    @vite('resources/css/public-portfolio.css')
</head>
<body class="pf-site" data-theme="{{ $portfolio->theme === 'light' ? 'light' : 'dark' }}" style="{{ $appearance->styles($portfolio) }}">
    <a class="pf-skip" href="#conteudo">Pular para o conteúdo</a>
    <header class="pf-header">
        <div class="pf-container pf-header-inner">
            <a class="pf-brand" href="{{ $preview ? route('admin.portfolio.preview') : route('portfolio.show', $portfolio->slug) }}">{{ $portfolio->professional_name }}</a>
            <nav aria-label="Navegação principal">
                <a class="pf-link" href="{{ $preview ? route('admin.portfolio.preview') : route('portfolio.show', $portfolio->slug) }}#projetos">Projetos</a>
            </nav>
        </div>
    </header>
    <main id="conteudo" class="pf-container" tabindex="-1">{{ $slot }}</main>
    <footer class="pf-footer">
        <div class="pf-container pf-footer-inner">
            <p>© {{ date('Y') }} {{ $portfolio->professional_name }}</p>
            <x-public-contact-links :portfolio="$portfolio" />
        </div>
    </footer>
</body>
</html>
