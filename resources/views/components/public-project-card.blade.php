@props(['portfolio', 'project'])
<article class="pf-card {{ $project->is_featured ? 'pf-card-featured' : '' }}">
    <a class="pf-card-image" href="{{ route('portfolio.projects.show', [$portfolio->slug, $project->id]) }}" tabindex="-1" aria-hidden="true">
        @if ($project->cover_url)
            <img src="{{ $project->cover_url }}" alt="" loading="lazy" width="640" height="400">
        @else
            <span class="pf-cover-placeholder" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($project->title, 0, 1)) }}</span>
        @endif
    </a>
    <div class="pf-card-content">
        @if ($project->is_featured)
            <span class="pf-badge">Em destaque</span>
        @endif
        <h3><a class="pf-title-link" href="{{ route('portfolio.projects.show', [$portfolio->slug, $project->id]) }}">{{ $project->title }}</a></h3>
        @if ($project->description_text)
            <p class="pf-muted pf-excerpt">{{ \Illuminate\Support\Str::limit($project->description_text, 180) }}</p>
        @endif
        <a class="pf-link pf-card-more" href="{{ route('portfolio.projects.show', [$portfolio->slug, $project->id]) }}">Conhecer projeto <x-public-icon name="details" /><span class="sr-only">: {{ $project->title }}</span></a>
    </div>
</article>
