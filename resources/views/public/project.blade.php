<x-public-layout :preview="$preview ?? false" :portfolio="$portfolio" :metadata="app(\App\Support\SharingMetadata::class)->forProject($portfolio, $project)">
    <article class="pf-project-detail">
        <a class="pf-link pf-back" href="{{ ($preview ?? false) ? route('admin.portfolio.preview') : route('portfolio.show', $portfolio->slug) }}#projetos">← Voltar para os projetos</a>
        <header class="pf-project-heading">
            @if ($project->is_featured)<span class="pf-badge">Em destaque</span>@endif
            <h1>{{ $project->title }}</h1>
            <p class="pf-muted">Um projeto de {{ $portfolio->professional_name }}</p>
            <x-project-taxonomy :project="$project" />
            @if ($project->link_demo || $project->link_github || !($preview ?? false))
                <nav class="pf-project-actions" aria-label="Links do projeto">
                    @if (is_string($project->link_demo) && preg_match('#^https?://#i', $project->link_demo))
                        <a class="pf-button" href="{{ $project->link_demo }}" target="_blank" rel="noopener noreferrer">Ver demonstração <x-public-icon name="globe" /><span class="sr-only"> (abre em nova aba)</span></a>
                    @endif
                    @if (is_string($project->link_github) && preg_match('#^https?://#i', $project->link_github))
                        <a class="pf-button pf-button-secondary" href="{{ $project->link_github }}" target="_blank" rel="noopener noreferrer">Ver repositório <x-public-icon name="github" /><span class="sr-only"> (abre em nova aba)</span></a>
                    @endif
                    @if (!($preview ?? false))
                        <x-share-actions :metadata="app(\App\Support\SharingMetadata::class)->forProject($portfolio, $project)" variant="compact" :show-copy="false" />
                    @endif
                </nav>
            @endif
        </header>
        @if ($project->cover_url)
            <img class="pf-project-cover" src="{{ $project->cover_url }}" alt="Capa de {{ $project->title }}" width="1200" height="750" fetchpriority="high">
        @endif
        <section class="pf-project-description" aria-labelledby="description-title">
            <h2 id="description-title">Sobre o projeto</h2>
            @php($descriptionHtml = $project->description_html)
            @if ($descriptionHtml)
                <div class="project-prose">{!! $descriptionHtml !!}</div>
            @else
                <p class="pf-muted">A descrição deste projeto será adicionada em breve.</p>
            @endif
        </section>
    </article>
</x-public-layout>
