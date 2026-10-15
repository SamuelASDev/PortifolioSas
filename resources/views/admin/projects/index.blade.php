<x-app-layout>
    <x-slot name="header"><div class="flex flex-wrap items-center justify-between gap-4"><h1>Meus projetos</h1><div class="flex flex-wrap gap-3"><a href="{{ route('admin.projects.organize') }}" class="studio-button studio-button-secondary">Organizar destaques</a><a href="{{ route('admin.projects.create') }}" class="studio-button studio-button-primary">Novo projeto <span aria-hidden="true">＋</span></a></div></div></x-slot>
    <div class="space-y-6">
        @if(session('status'))<div role="status" class="studio-notice">{{ session('status') }}</div>@endif
        <x-project-filters :filters="$filters" :tags="$filterTags" :categories="$filterCategories" :action="route('admin.projects.index')" variant="admin" />
        <p class="studio-results-count" role="status">{{ $projects->total() }} de {{ $projectCount }} projetos <span>Seu trabalho, em movimento.</span></p>
        <div class="studio-project-list">
            @forelse($projects as $project)
                <article class="studio-panel studio-project-entry"><div class="studio-project-cover">@if($project->cover_url)<img src="{{ $project->cover_url }}" alt="Capa de {{ $project->title }}" loading="lazy">@else<x-public-icon name="layout" /><span>Seu próximo destaque</span>@endif</div><div class="studio-project-info"><div class="studio-project-title"><h3>{{ $project->title }}</h3>@if($project->is_featured)<span class="studio-status is-featured"><x-public-icon name="star" /> Em destaque</span>@endif</div><x-project-taxonomy :project="$project" variant="admin" />@if($project->description_text)<p class="studio-project-excerpt">{{ \Illuminate\Support\Str::limit($project->description_text, 200) }}</p>@endif<p class="studio-project-position">Ordem: {{ $project->display_order }}</p></div><div class="studio-project-actions"><a href="{{ route('admin.projects.edit',$project) }}" aria-label="Editar {{ $project->title }}" class="studio-button studio-button-secondary">Editar <span aria-hidden="true">↗</span></a><form method="POST" action="{{ route('admin.projects.destroy',$project) }}" x-data @submit.prevent="if (confirm('Excluir este projeto? Esta ação não pode ser desfeita.')) $el.submit()">@csrf @method('DELETE')<button type="submit" class="studio-project-delete" aria-label="Excluir {{ $project->title }}">Excluir</button></form></div></article>
            @empty
                <div class="studio-panel studio-empty"><x-public-icon name="layout" /><h3>{{ $projectCount ? 'Vamos tentar outro olhar?' : 'Uma boa história começa com um projeto.' }}</h3><p class="mt-2">{{ $projectCount ? 'Nenhum projeto encontrado com esses filtros.' : 'Você ainda não cadastrou projetos. Comece apresentando algo que criou.' }}</p>@if(!$projectCount)<a href="{{ route('admin.projects.create') }}" class="studio-button studio-button-primary mt-5">Criar meu primeiro projeto ↗</a>@endif</div>
            @endforelse
        </div>
        {{ $projects->links() }}
    </div>
</x-app-layout>
