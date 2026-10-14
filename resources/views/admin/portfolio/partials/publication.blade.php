<section class="p-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm space-y-4 text-gray-900 dark:text-gray-100" aria-labelledby="publication-heading">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h3 id="publication-heading" class="text-lg font-semibold">Publicação</h3>
        <span class="text-sm font-semibold">{{ $portfolio->is_published ? 'Publicado' : 'Rascunho' }}</span>
    </div>
    @if ($portfolio->is_published)
        <p class="text-sm text-gray-600 dark:text-gray-300">Seu portfólio está público. Alterações salvas aparecem imediatamente.</p>
        <a href="{{ route('portfolio.show', $portfolio->slug) }}" target="_blank" rel="noopener noreferrer" class="block text-sm text-indigo-600 dark:text-indigo-400 underline break-all">{{ route('portfolio.show', $portfolio->slug) }}</a>
    @else
        <p class="text-sm text-gray-600 dark:text-gray-300">Seu portfólio e as páginas dos projetos ainda não estão disponíveis ao público. Salve a apresentação antes de publicar.</p>
    @endif
    <form method="POST" action="{{ route('admin.portfolio.publication.update') }}">
        @csrf
        @method('PATCH')
        <input type="hidden" name="is_published" value="{{ $portfolio->is_published ? '0' : '1' }}">
        @if ($portfolio->is_published)
            <x-secondary-button type="submit">Despublicar portfólio</x-secondary-button>
        @else
            <x-primary-button>Publicar portfólio</x-primary-button>
        @endif
        <x-input-error :messages="$errors->get('is_published')" class="mt-2" />
    </form>
</section>
