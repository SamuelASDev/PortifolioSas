@php
    $base = app(\App\Support\SharingMetadata::class)->forPortfolio($portfolio);
    $contactUrl = ($preview ?? false) ? route('admin.portfolio.preview.contact') : route('portfolio.contact', $portfolio->slug);
    $homeUrl = ($preview ?? false) ? route('admin.portfolio.preview') : route('portfolio.show', $portfolio->slug);
    $paragraphs = $portfolio->about_paragraphs;
@endphp
<x-public-layout :portfolio="$portfolio" :preview="$preview ?? false" current="about" :metadata="['title' => 'Sobre · '.$portfolio->professional_name, 'url' => ($preview ?? false) ? $base['url'] : route('portfolio.about', $portfolio->slug)] + $base">
    <article class="pf-page pf-about" aria-labelledby="about-title">
        <header class="pf-page-heading pf-about-heading">
            <div>
                <p class="pf-eyebrow">Sobre</p>
                <h1 id="about-title">{{ $portfolio->professional_name }}</h1>
                @if ($portfolio->headline)<p class="pf-headline">{{ $portfolio->headline }}</p>@endif
                @if ($portfolio->location)<p class="pf-muted pf-location"><x-public-icon name="pin" /> {{ $portfolio->location }}</p>@endif
            </div>
            @if ($portfolio->photo_url)
                <img class="pf-portrait" src="{{ $portfolio->photo_url }}" alt="Foto de {{ $portfolio->professional_name }}" width="320" height="320">
            @endif
        </header>
        <div class="pf-about-body">
            @forelse ($paragraphs as $paragraph)
                <p @class(['pf-about-lead' => $loop->first])>{!! nl2br(e($paragraph)) !!}</p>
            @empty
                <p class="pf-muted">A história de {{ $portfolio->professional_name }} será contada aqui em breve.</p>
            @endforelse
        </div>
        <aside class="pf-page-cta" aria-label="Próximos passos">
            <p>Gostou do que viu? Conheça os trabalhos ou mande uma mensagem.</p>
            <div class="pf-page-cta-actions">
                <a class="pf-button" href="{{ $contactUrl }}">Entrar em contato</a>
                <a class="pf-button pf-button-secondary" href="{{ $homeUrl }}#projetos">Ver projetos</a>
            </div>
        </aside>
    </article>
</x-public-layout>
