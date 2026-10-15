@props(['portfolio'])
@php
    $links = collect([
        ['icon' => 'github', 'label' => 'GitHub', 'url' => $portfolio->github_url],
        ['icon' => 'linkedin', 'label' => 'LinkedIn', 'url' => $portfolio->linkedin_url],
        ['icon' => 'globe', 'label' => 'Site pessoal', 'url' => $portfolio->website_url],
    ])->filter(fn ($link) => is_string($link['url']) && preg_match('#^https?://#i', $link['url']));
@endphp
@if ($links->isNotEmpty() || $portfolio->contact_email)
    <nav class="pf-contacts" aria-label="Links de contato">
        @if ($portfolio->contact_email)
            <a class="pf-link" href="{{ $portfolio->gmail_compose_url }}" target="_blank" rel="noopener noreferrer">E-mail <x-public-icon name="email" /><span class="sr-only"> (abre o Gmail em nova aba)</span></a>
        @endif
        @foreach ($links as $link)
            <a class="pf-link" href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer">{{ $link['label'] }} <x-public-icon :name="$link['icon']" /><span class="sr-only"> (abre em nova aba)</span></a>
        @endforeach
    </nav>
@endif
