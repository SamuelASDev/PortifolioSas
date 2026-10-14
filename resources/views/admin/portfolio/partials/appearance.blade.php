<section class="space-y-6 border-t border-gray-200 dark:border-gray-700 pt-6" aria-labelledby="appearance-heading">
    <h3 id="appearance-heading" class="text-lg font-semibold">Endereço e aparência</h3>
    <div>
        <x-input-label for="slug" value="Endereço do portfólio" />
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 break-all">{{ url('/') }}/</p>
        <x-text-input id="slug" name="slug" class="block mt-1 w-full" :value="old('slug', $portfolio->slug)" maxlength="255" required />
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Alterar o endereço faz o link anterior deixar de funcionar.</p>
        <x-input-error :messages="$errors->get('slug')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="theme" value="Tema visual" />
        <select id="theme" name="theme" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">
            <option value="dark" @selected(old('theme', $portfolio->theme) === 'dark')>Escuro</option>
            <option value="light" @selected(old('theme', $portfolio->theme) === 'light')>Claro</option>
        </select>
        <x-input-error :messages="$errors->get('theme')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="primary_color" value="Cor de destaque" />
        <input type="color" id="primary_color" name="primary_color" value="{{ old('primary_color', $portfolio->primary_color) }}" class="mt-1 h-11 w-16 rounded border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 cursor-pointer">
        <x-input-error :messages="$errors->get('primary_color')" class="mt-2" />
    </div>
</section>
