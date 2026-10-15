<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Meus projetos</h2>
            <a href="{{ route('admin.projects.organize') }}" class="px-4 py-2 border border-indigo-500 text-indigo-700 dark:text-indigo-300 rounded-md text-sm font-semibold">Organizar projetos e destaques</a>
            <a href="{{ route('admin.projects.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-sm font-semibold">Novo projeto</a>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div role="status" class="p-4 bg-green-900 border border-green-500 text-white rounded-lg">{{ session('status') }}</div>
            @endif
            <x-project-filters :filters="$filters" :tags="$filterTags" :categories="$filterCategories" :action="route('admin.projects.index')" variant="admin" />
            <p class="text-sm text-gray-600 dark:text-gray-300" role="status">{{ $projects->total() }} de {{ $projectCount }} projetos</p>
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($projects as $project)
                    <article class="p-6 flex flex-col sm:flex-row gap-4 sm:items-start sm:justify-between">
                        <div class="min-w-0 space-y-2">
                            @if ($project->cover_url)
                                <img src="{{ $project->cover_url }}" alt="Capa de {{ $project->title }}" loading="lazy" class="w-full sm:w-56 h-32 object-cover rounded-lg">
                            @endif
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100 break-words">{{ $project->title }}</h3>
                            <x-project-taxonomy :project="$project" variant="admin" />
                            <p class="text-sm text-gray-500 dark:text-gray-400">Ordem: {{ $project->display_order }} @if ($project->is_featured) · Em destaque @endif</p>
                            @if ($project->description_text)
                                <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line break-words">{{ \Illuminate\Support\Str::limit($project->description_text, 300) }}</p>
                            @endif
                        </div>
                        <div class="flex shrink-0 items-center gap-4">
                            <a href="{{ route('admin.projects.edit', $project) }}" aria-label="Editar {{ $project->title }}" class="text-sm text-indigo-600 dark:text-indigo-400 underline">Editar</a>
                            <form method="POST" action="{{ route('admin.projects.destroy', $project) }}" x-data @submit.prevent="if (confirm('Excluir este projeto? Esta ação não pode ser desfeita.')) $el.submit()">
                                @csrf
                                @method('DELETE')
                                <x-danger-button aria-label="Excluir {{ $project->title }}">Excluir</x-danger-button>
                            </form>
                        </div>
                    </article>
                @empty
                    <p class="p-6 text-gray-600 dark:text-gray-300">{{ $projectCount ? 'Nenhum projeto encontrado com esses filtros.' : 'Você ainda não cadastrou projetos. Clique em “Novo projeto” para começar.' }}</p>
                @endforelse
            </div>
            {{ $projects->links() }}
        </div>
    </div>
</x-app-layout>
