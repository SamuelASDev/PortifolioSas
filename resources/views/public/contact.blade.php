@php
    $base = app(\App\Support\SharingMetadata::class)->forPortfolio($portfolio);
    $display = fn (?string $url) => $url ? rtrim(preg_replace('#^https?://(www\.)?#i', '', $url), '/') : null;
    $channels = collect([
        ['icon' => 'email', 'label' => 'E-mail', 'value' => $portfolio->contact_email, 'url' => $portfolio->gmail_compose_url, 'note' => 'abre o Gmail em nova aba'],
        ['icon' => 'phone', 'label' => 'WhatsApp', 'value' => $portfolio->whatsapp, 'url' => $portfolio->whatsapp_url, 'note' => 'abre o WhatsApp em nova aba'],
        ['icon' => 'linkedin', 'label' => 'LinkedIn', 'value' => $display($portfolio->linkedin_url), 'url' => $portfolio->linkedin_url],
        ['icon' => 'github', 'label' => 'GitHub', 'value' => $display($portfolio->github_url), 'url' => $portfolio->github_url],
        ['icon' => 'instagram', 'label' => 'Instagram', 'value' => $display($portfolio->instagram_url), 'url' => $portfolio->instagram_url],
        ['icon' => 'globe', 'label' => 'Site pessoal', 'value' => $display($portfolio->website_url), 'url' => $portfolio->website_url],
    ])->filter(fn ($channel) => $channel['value'] && is_string($channel['url']) && preg_match('#^https?://#i', $channel['url']));
@endphp
<x-public-layout :portfolio="$portfolio" :preview="$preview ?? false" current="contact" :metadata="['title' => 'Contato · '.$portfolio->professional_name, 'url' => ($preview ?? false) ? $base['url'] : route('portfolio.contact', $portfolio->slug)] + $base">
    <section class="pf-page pf-contact" aria-labelledby="contact-title">
        <header class="pf-page-heading">
            <p class="pf-eyebrow">Contato</p>
            <h1 id="contact-title">Vamos conversar</h1>
            <p class="pf-bio pf-muted">{{ $portfolio->contact_intro ?: 'Escolha o canal que preferir para falar com '.$portfolio->professional_name.'.' }}</p>
            @if ($portfolio->location)<p class="pf-muted pf-location"><x-public-icon name="pin" /> {{ $portfolio->location }}</p>@endif
        </header>
        @if ($channels->isNotEmpty())
            <ul class="pf-contact-grid" role="list">
                @foreach ($channels as $channel)
                    <li>
                        <a class="pf-contact-card" href="{{ $channel['url'] }}" target="_blank" rel="noopener noreferrer">
                            <span class="pf-contact-icon"><x-public-icon :name="$channel['icon']" /></span>
                            <span class="pf-contact-text"><strong>{{ $channel['label'] }}</strong><span class="pf-muted">{{ $channel['value'] }}</span></span>
                            <span class="sr-only"> ({{ $channel['note'] ?? 'abre em nova aba' }})</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="pf-empty pf-muted">Os canais de contato serão adicionados em breve.</p>
        @endif
    </section>
</x-public-layout>
