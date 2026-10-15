<x-public-layout :portfolio="$portfolio" :preview="$preview ?? false">
    <section class="pf-hero" aria-labelledby="presentation-title">
        <div class="pf-hero-copy">
            <p class="pf-eyebrow">Portfólio profissional</p>
            <h1 id="presentation-title">{{ $portfolio->professional_name }}</h1>
            @if ($portfolio->headline)
                <p class="pf-headline">{{ $portfolio->headline }}</p>
            @endif
            @if ($portfolio->bio)
                <p class="pf-bio pf-muted">{{ $portfolio->bio }}</p>
            @endif
            <div class="pf-hero-actions">
                <a class="pf-button" href="#projetos">Conheça meus projetos <span aria-hidden="true">↓</span></a>
                <x-public-contact-links :portfolio="$portfolio" />
            </div>
        </div>
        @if ($portfolio->photo_url)
            <img class="pf-portrait" src="{{ $portfolio->photo_url }}" alt="Foto de {{ $portfolio->professional_name }}" width="320" height="320" fetchpriority="high">
        @endif
    </section>
    @if ($featuredProjects->isNotEmpty())
        <x-featured-project-carousel :portfolio="$portfolio" :projects="$featuredProjects" :preview="$preview ?? false" />
    @endif
    <section id="projetos" class="pf-projects" aria-labelledby="projects-title">
        <div class="pf-section-heading">
            <div><p class="pf-eyebrow">Trabalhos e experiências</p><h2 id="projects-title">Meus projetos</h2></div>
            <p class="pf-muted">{{ $projects->total() }} {{ $projects->total() === 1 ? 'projeto' : 'projetos' }}</p>
        </div>
        <div class="pf-grid">
            @forelse ($projects as $project)
                <x-public-project-card :portfolio="$portfolio" :project="$project" :preview="$preview ?? false" />
            @empty
                <p class="pf-empty pf-muted">Novos projetos serão apresentados aqui em breve.</p>
            @endforelse
        </div>
        @if ($projects->hasPages())
            <nav class="pf-pagination" aria-label="Paginação dos projetos">
                @if ($projects->onFirstPage())
                    <span class="pf-muted" aria-disabled="true">Anterior</span>
                @else
                    <a class="pf-link" href="{{ $projects->previousPageUrl() }}#projetos" rel="prev">Anterior</a>
                @endif
                <span class="pf-muted">Página {{ $projects->currentPage() }} de {{ $projects->lastPage() }}</span>
                @if ($projects->hasMorePages())
                    <a class="pf-link" href="{{ $projects->nextPageUrl() }}#projetos" rel="next">Próxima</a>
                @else
                    <span class="pf-muted" aria-disabled="true">Próxima</span>
                @endif
            </nav>
        @endif
    </section>
</x-public-layout>
