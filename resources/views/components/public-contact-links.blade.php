@props(['portfolio'])
@php
    $links = collect([
        ['label' => 'GitHub', 'url' => $portfolio->github_url],
        ['label' => 'LinkedIn', 'url' => $portfolio->linkedin_url],
        ['label' => 'Site pessoal', 'url' => $portfolio->website_url],
    ])->filter(fn ($link) => is_string($link['url']) && preg_match('#^https?://#i', $link['url']));
@endphp
@if ($links->isNotEmpty() || $portfolio->contact_email)
    <nav class="pf-contacts" aria-label="Links de contato">
        @if ($portfolio->contact_email)
            <a class="pf-link" href="{{ $portfolio->gmail_compose_url }}" target="_blank" rel="noopener noreferrer">E-mail <span aria-hidden="true">↗</span><span class="sr-only"> (abre o Gmail em nova aba)</span></a>
        @endif
        @foreach ($links as $link)
            <a class="pf-link" href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer">{{ $link['label'] }} <span aria-hidden="true">↗</span><span class="sr-only"> (abre em nova aba)</span></a>
        @endforeach
    </nav>
@endif
