<fieldset class="space-y-4 border-t border-gray-300 dark:border-gray-700 pt-4">
    <legend class="font-semibold text-gray-900 dark:text-gray-100 pt-4">Categoria e tecnologias</legend>
    <div>
        <x-input-label for="category" value="Categoria (opcional)" />
        <select id="category" name="category" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100"><option value="">Sem categoria</option>@foreach (config('projects.categories') as $key => $category)<option value="{{ $key }}" @selected(old('category', $project->category) === $key)>{{ $category['label'] }}</option>@endforeach</select>
        <x-input-error :messages="$errors->get('category')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="tags" value="Tecnologias / tags (opcional)" />
        <x-text-input id="tags" name="tags" class="mt-1 block w-full" :value="old('tags', $project->exists ? $project->tags->pluck('name')->implode(', ') : '')" maxlength="500" placeholder="Laravel, PHP 8, MySQL, Tailwind CSS" aria-describedby="tags-help" />
        <p id="tags-help" class="mt-2 text-sm text-gray-500 dark:text-gray-400">Separe por vírgulas. Até 8 tags, com 30 caracteres cada. Repetições são unificadas. Os ícones aparecem automaticamente.</p>
        <x-input-error :messages="$errors->get('tags')" class="mt-2" />
    </div>
</fieldset>
