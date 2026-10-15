@props(['portfolio', 'projects', 'preview' => false])
<section id="destaques" data-featured-carousel class="pf-carousel" aria-labelledby="featured-heading" aria-roledescription="carrossel" tabindex="0">
    <div class="pf-section-heading">
        <div><p class="pf-eyebrow">Projetos em destaque</p><h2 id="featured-heading">Uma seleção do meu trabalho</h2></div>
    </div>
    <div class="pf-carousel-frame">
        <div id="featured-track" data-carousel-track class="pf-carousel-track">
            @foreach ($projects as $project)
                <article data-carousel-slide class="pf-carousel-slide" role="group" aria-roledescription="slide" aria-label="{{ $loop->iteration }} de {{ $projects->count() }}: {{ $project->title }}">
                    <div class="pf-carousel-cover">
                        @if ($project->cover_url)
                            <img src="{{ $project->cover_url }}" alt="Capa de {{ $project->title }}" width="800" height="500" loading="lazy">
                        @else
                            <span class="pf-cover-placeholder" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($project->title, 0, 1)) }}</span>
                        @endif
                    </div>
                    <div class="pf-carousel-copy">
                        <span class="pf-badge">Em destaque</span>
                        <h3>{{ $project->title }}</h3>
                        <x-project-taxonomy :project="$project" />
                        @if ($project->description_text)
                            <p class="pf-muted">
                                <span class="pf-carousel-summary-desktop">{{ \Illuminate\Support\Str::limit($project->description_text, 280) }}</span>
                                <span class="pf-carousel-summary-mobile">{{ \Illuminate\Support\Str::limit($project->description_text, 110) }}</span>
                            </p>
                        @endif
                        <div class="pf-carousel-actions">
                            <a class="pf-button" href="{{ $preview ? route('admin.portfolio.preview.project', $project->id) : route('portfolio.projects.show', [$portfolio->slug, $project->id]) }}">Ver detalhes <span class="sr-only">de {{ $project->title }}</span> <x-public-icon name="details" /></a>
                            @if (is_string($project->link_demo) && preg_match('#^https?://#i', $project->link_demo))
                                <a class="pf-button pf-button-secondary" href="{{ $project->link_demo }}" target="_blank" rel="noopener noreferrer">Acessar site <x-public-icon name="globe" /></a>
                            @endif
                            @if (is_string($project->link_github) && preg_match('#^https?://#i', $project->link_github))
                                <a class="pf-button pf-button-secondary" href="{{ $project->link_github }}" target="_blank" rel="noopener noreferrer">Código <x-public-icon name="github" /></a>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
        @if ($projects->count() > 1)
            <div data-carousel-controls class="pf-carousel-controls" hidden>
                <button data-carousel-prev type="button" class="pf-carousel-arrow" aria-label="Projeto anterior" aria-controls="featured-track"><x-public-icon name="chevron-left" /></button>
                <div class="pf-carousel-dots" role="group" aria-label="Escolher projeto em destaque">
                    @foreach ($projects as $project)
                        <button data-carousel-dot="{{ $loop->index }}" type="button" class="pf-carousel-dot" aria-label="Ir para o projeto {{ $loop->iteration }}: {{ $project->title }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}" aria-controls="featured-track"></button>
                    @endforeach
                </div>
                <button data-carousel-next type="button" class="pf-carousel-arrow" aria-label="Próximo projeto" aria-controls="featured-track"><x-public-icon name="chevron-right" /></button>
            </div>
            <p data-carousel-status class="sr-only" role="status" aria-live="polite" aria-atomic="true"></p>
        @endif
    </div>
</section>
@if ($projects->count() > 1)
    @vite('resources/js/public-carousel.js')
@endif
