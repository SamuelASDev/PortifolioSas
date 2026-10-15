<section class="space-y-6 border-t border-gray-200 dark:border-gray-700 pt-6" aria-labelledby="appearance-heading">
    <h3 id="appearance-heading" class="text-lg font-semibold">Endereço e aparência</h3>
    <div>
        <input type="hidden" name="show_featured_carousel" value="0">
        <label for="show_featured_carousel" class="flex items-start gap-3 text-sm font-medium">
            <input id="show_featured_carousel" type="checkbox" name="show_featured_carousel" value="1" @checked(old('show_featured_carousel', $portfolio->show_featured_carousel)) class="mt-1 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-indigo-600 focus:ring-indigo-500" aria-describedby="carousel-help">
            Exibir carrossel de projetos em destaque
        </label>
        <p id="carousel-help" class="mt-2 text-sm text-gray-500 dark:text-gray-400">Aparece antes da lista de projetos. Marque “Destacar este projeto” nos projetos desejados. Exibe até {{ config('portfolio.carousel.max_projects') }} destaques, na ordem de exibição configurada.</p>
        <x-input-error :messages="$errors->get('show_featured_carousel')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="slug" value="Endereço do portfólio" />
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 break-all">{{ url('/') }}/</p>
        <x-text-input id="slug" name="slug" class="block mt-1 w-full" :value="old('slug', $portfolio->slug)" maxlength="255" required />
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Alterar o endereço faz o link anterior deixar de funcionar.</p>
        <x-input-error :messages="$errors->get('slug')" class="mt-2" />
    </div>
    <input type="hidden" id="theme" name="theme" value="{{ old('theme', $portfolio->theme) }}">
    <input type="hidden" id="primary_color" name="primary_color" value="{{ old('primary_color', $portfolio->primary_color) }}">
    <div class="space-y-3">
        <p class="text-sm text-gray-500 dark:text-gray-400">Escolha temas, fontes e cores em uma prévia privada com seus projetos salvos.</p>
        <x-secondary-button type="button" data-open-appearance disabled>Personalizar aparência</x-secondary-button>
        <a href="{{ route('admin.portfolio.preview') }}" target="_blank" rel="noopener" class="block text-sm underline">Abrir prévia salva em nova aba</a>
        <x-input-error :messages="$errors->get('theme')" class="mt-2" />
        <x-input-error :messages="$errors->get('primary_color')" class="mt-2" />
    </div>
</section>
