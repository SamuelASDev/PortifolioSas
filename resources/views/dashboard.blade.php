<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Seu portfólio</h2></x-slot>
    <div class="py-12"><div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <section class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm space-y-5 text-gray-900 dark:text-gray-100" aria-labelledby="onboarding-heading">
            <div class="flex flex-wrap items-start justify-between gap-4"><div><h3 id="onboarding-heading" class="text-xl font-semibold">{{ $completedSteps === count($steps) ? 'Seu portfólio está pronto para compartilhar' : 'Prepare seu portfólio' }}</h3><p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $completedSteps }} de {{ count($steps) }} etapas concluídas. Seu progresso acompanha as informações salvas.</p></div><span class="rounded-full border border-gray-400 px-3 py-1 text-sm">{{ $portfolio->is_published ? 'Publicado' : 'Rascunho' }}</span></div>
            <progress value="{{ $completedSteps }}" max="{{ count($steps) }}" class="w-full h-3 accent-indigo-600" aria-label="Progresso da configuração"></progress>
            <ol class="space-y-3">
                @foreach ($steps as $step)
                    <li class="p-4 rounded-lg border border-gray-300 dark:border-gray-600 flex items-start gap-3">
                        <span class="mt-1 {{ $step['complete'] ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400' }}"><x-public-icon :name="$step['complete'] ? 'check' : $step['icon']" /></span>
                        <div class="min-w-0 flex-1"><h4 class="font-semibold">{{ $step['title'] }}</h4><p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $step['description'] }}</p><p class="text-xs mt-2 {{ $step['complete'] ? 'text-green-700 dark:text-green-400' : 'text-gray-500 dark:text-gray-400' }}">{{ $step['complete'] ? 'Concluído' : 'Pendente' }}</p><a href="{{ $step['url'] }}" class="inline-flex items-center min-h-11 text-sm underline text-indigo-700 dark:text-indigo-300">{{ $step['complete'] ? 'Revisar' : 'Configurar' }}</a></div>
                    </li>
                @endforeach
            </ol>
            <div class="flex flex-wrap gap-3"><a href="{{ route('admin.portfolio.preview') }}" class="appearance-control gap-2"><x-public-icon name="layout" /> Ver prévia privada</a><a href="{{ route('admin.projects.index') }}" class="appearance-control gap-2"><x-public-icon name="code" /> Meus projetos</a>@if ($portfolio->is_published)<a href="{{ route('portfolio.show', $portfolio->slug) }}" class="appearance-control gap-2"><x-public-icon name="globe" /> Abrir portfólio público</a>@endif</div>
        </section>
    </div></div>
</x-app-layout>
