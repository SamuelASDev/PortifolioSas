<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Organizar projetos e destaques</h2></x-slot>
    <div class="py-12"><div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <form data-project-organization data-revision="{{ $revision }}" action="{{ route('admin.projects.organize.update') }}" class="p-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm space-y-6 text-gray-900 dark:text-gray-100">
            <div class="space-y-2"><h3 class="text-lg font-semibold">Sua seleção, na sua ordem</h3><p class="text-sm text-gray-500 dark:text-gray-400">Arraste pela alça ou use os botões Subir e Descer. Todos os projetos estão nesta lista. A ordem será usada na vitrine e no carrossel, que exibe até {{ config('portfolio.carousel.max_projects') }} destaques.</p><p class="text-sm text-gray-500 dark:text-gray-400">As alterações só entram em vigor ao salvar. Se o portfólio estiver publicado, serão exibidas imediatamente.</p></div>
            <ol data-project-list class="project-organization-list space-y-3" aria-label="Ordem dos projetos">
                @foreach ($projects as $project)
                    <li data-project-row data-project-id="{{ $project->id }}" class="project-organization-row border border-gray-300 dark:border-gray-600 rounded-lg p-3 flex gap-3 items-start">
                        <button type="button" data-project-handle class="project-drag-handle appearance-control" aria-label="Arrastar {{ $project->title }}; use as setas para mover" title="Arrastar para ordenar">⠿</button>
                        <div class="min-w-0 flex-1 space-y-2">
                            <p class="font-semibold break-words"><span data-project-position>{{ $loop->iteration }}</span>. {{ $project->title }}</p>
                            <label class="flex gap-2 items-center text-sm"><input type="checkbox" data-project-featured @checked($project->is_featured) class="rounded dark:bg-gray-900">Em destaque</label>
                            <div class="flex flex-wrap gap-2"><button type="button" data-project-up class="appearance-control" aria-label="Subir {{ $project->title }}">↑ Subir</button><button type="button" data-project-down class="appearance-control" aria-label="Descer {{ $project->title }}">↓ Descer</button><a href="{{ route('admin.projects.edit', $project) }}" class="appearance-control">Editar</a></div>
                        </div>
                        @if ($project->cover_url)<img src="{{ $project->cover_url }}" alt="" class="hidden sm:block w-24 h-16 object-cover rounded" loading="lazy">@endif
                    </li>
                @endforeach
            </ol>
            @if ($projects->isEmpty())<p>Você ainda não cadastrou projetos.</p>@endif
            <p data-organization-status role="status" aria-live="polite" class="text-sm"></p>
            <div class="flex flex-wrap gap-3"><x-primary-button data-organization-save disabled>Salvar ordem e destaques</x-primary-button><a href="{{ route('admin.projects.index') }}" class="appearance-control">Voltar</a><a href="{{ route('admin.portfolio.preview') }}" target="_blank" rel="noopener" class="appearance-control">Ver prévia salva</a></div>
            <noscript><p>Ative o JavaScript para organizar por arrastar. Você também pode editar a ordem e o destaque no formulário de cada projeto.</p></noscript>
        </form>
    </div></div>
</x-app-layout>
