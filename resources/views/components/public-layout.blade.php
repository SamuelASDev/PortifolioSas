@props(['portfolio', 'metadata' => null, 'preview' => false])
@php
    $appearance = app(\App\Support\PortfolioAppearance::class);
    $design = $appearance->settings($portfolio)['appearance'];
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
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
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
<body @foreach (config('portfolio-appearance.options') as $key => $option) data-{{ $key }}="{{ $design[$key] }}" @endforeach class="pf-site" data-theme="{{ $portfolio->theme === 'light' ? 'light' : 'dark' }}" style="{{ $appearance->styles($portfolio) }}">
    <a class="pf-skip" href="#conteudo">Pular para o conteúdo</a>
    <header class="pf-header">
        <div class="pf-container pf-header-inner">
            <a class="pf-brand" href="{{ $preview ? route('admin.portfolio.preview') : route('portfolio.show', $portfolio->slug) }}"><span class="pf-brand-mark" aria-hidden="true">{{ mb_substr($portfolio->professional_name, 0, 1) }}</span><span>{{ $portfolio->professional_name }}</span></a>
            <nav aria-label="Navegação principal">
                <a class="pf-link" href="{{ $preview ? route('admin.portfolio.preview') : route('portfolio.show', $portfolio->slug) }}#projetos">Projetos</a>
            </nav>
        </div>
    </header>
    <main id="conteudo" class="pf-container" tabindex="-1">{{ $slot }}</main>
    <footer class="pf-footer">
        <div class="pf-container pf-footer-inner">
            <p class="pf-footer-owner">© {{ date('Y') }} {{ $portfolio->professional_name }}</p>
            <div class="pf-footer-signature">
                <div class="pf-footer-credit">
                    <a class="pf-footer-brand" href="{{ url('/') }}" aria-label="Conheça o {{ config('brand.name') }}">
                        <img src="{{ asset('favicon.svg') }}" alt="" width="28" height="28" loading="lazy">
                        <span>© {{ date('Y') }} {{ config('brand.name') }}</span>
                    </a>
                    <p class="pf-footer-byline">Desenvolvido por {{ config('brand.creator.name') }}</p>
                </div>
                <nav class="pf-creator-links" aria-label="Contatos do desenvolvedor do Vértice">
                    <a class="pf-creator-link" href="mailto:{{ config('brand.creator.email') }}" aria-label="Enviar e-mail ao desenvolvedor" title="E-mail de Samuel"><x-public-icon name="email" /></a>
                    <a class="pf-creator-link" href="{{ config('brand.creator.github_url') }}" target="_blank" rel="noopener noreferrer" aria-label="GitHub do desenvolvedor (abre em nova aba)" title="GitHub de Samuel"><x-public-icon name="github" /></a>
                    <a class="pf-creator-link" href="{{ config('brand.creator.linkedin_url') }}" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn do desenvolvedor (abre em nova aba)" title="LinkedIn de Samuel"><x-public-icon name="linkedin" /></a>
                    <a class="pf-creator-link" href="{{ config('brand.creator.portfolio_url') }}" target="_blank" rel="noopener noreferrer" aria-label="Portfólio do desenvolvedor (abre em nova aba)" title="Portfólio de Samuel"><x-public-icon name="globe" /></a>
                </nav>
            </div>
        </div>
    </footer>
</body>
</html>
