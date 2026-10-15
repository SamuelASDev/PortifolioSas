@props(['portfolio', 'metadata' => null, 'preview' => false])
@php
    $appearance = app(\App\Support\PortfolioAppearance::class);
    $accent = $appearance->color($portfolio->primary_color);
    $metadata ??= app(\App\Support\SharingMetadata::class)->forPortfolio($portfolio);
    $title = $metadata['title'];
    $summary = $metadata['description'];
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
    <meta property="og:url" content="{{ $metadata['url'] }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="{{ $metadata['image'] ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $title }}">
    <meta name="twitter:description" content="{{ $summary }}">
    @if ($metadata['image'])
        <meta property="og:image" content="{{ $metadata['image'] }}">
        <meta property="og:image:alt" content="{{ $title }}">
        <meta name="twitter:image" content="{{ $metadata['image'] }}">
        <meta name="twitter:image:alt" content="{{ $title }}">
    @endif
    <link rel="canonical" href="{{ $metadata['url'] }}">
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
