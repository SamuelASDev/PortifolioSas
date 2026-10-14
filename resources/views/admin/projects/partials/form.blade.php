@csrf
<div>
    <x-input-label for="title" value="Título" />
    <x-text-input id="title" name="title" class="block mt-1 w-full" :value="old('title', $project->title)" required maxlength="255" />
    <x-input-error :messages="$errors->get('title')" class="mt-2" />
</div>
<div>
    <x-input-label for="description" value="Descrição" />
    <textarea id="description" name="description" rows="8" maxlength="50000" class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $project->description) }}</textarea>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Texto simples, sem formatação HTML.</p>
    <x-input-error :messages="$errors->get('description')" class="mt-2" />
</div>
<div>
    <x-input-label for="link_demo" value="Link da demonstração (opcional)" />
    <x-text-input id="link_demo" name="link_demo" type="url" class="block mt-1 w-full" :value="old('link_demo', $project->link_demo)" maxlength="255" placeholder="https://" />
    <x-input-error :messages="$errors->get('link_demo')" class="mt-2" />
</div>
<div>
    <x-input-label for="link_github" value="Link do repositório (opcional)" />
    <x-text-input id="link_github" name="link_github" type="url" class="block mt-1 w-full" :value="old('link_github', $project->link_github)" maxlength="255" placeholder="https://github.com/" />
    <x-input-error :messages="$errors->get('link_github')" class="mt-2" />
</div>
<div>
    <x-input-label for="display_order" value="Ordem de exibição" />
    <x-text-input id="display_order" name="display_order" type="number" min="0" max="2147483647" step="1" class="block mt-1 w-full" :value="old('display_order', $project->display_order)" required />
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Menores valores aparecem primeiro. Empates seguem a ordem de criação.</p>
    <x-input-error :messages="$errors->get('display_order')" class="mt-2" />
</div>
<div>
    <input type="hidden" name="is_featured" value="0">
    <label for="is_featured" class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
        <input id="is_featured" name="is_featured" type="checkbox" value="1" @checked(old('is_featured', $project->is_featured)) class="rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-indigo-600 focus:ring-indigo-500">
        Destacar este projeto
    </label>
    <x-input-error :messages="$errors->get('is_featured')" class="mt-2" />
</div>
<div class="flex items-center gap-4">
    <x-primary-button>Salvar projeto</x-primary-button>
    <a href="{{ route('admin.projects.index') }}" class="text-sm text-gray-600 dark:text-gray-400 underline">Cancelar</a>
</div>
