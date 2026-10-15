@csrf
<div>
    <x-input-label for="cover_image" value="Capa do projeto (opcional)" />
    <x-upload-privacy-note class="mt-2" />
    @if ($project->cover_url)
        <img src="{{ $project->cover_url }}" alt="Capa atual de {{ $project->title }}" class="mt-2 w-full max-w-sm h-48 object-cover rounded-lg">
        <label for="remove_cover" class="mt-3 flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
            <input id="remove_cover" type="checkbox" name="remove_cover" value="1" @checked(old('remove_cover', false)) class="rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-indigo-600 focus:ring-indigo-500">
            Remover capa atual
        </label>
    @endif
    <input id="cover_image" name="cover_image" type="file" accept="image/jpeg,image/png,image/webp" aria-describedby="cover_help" class="mt-3 block w-full text-sm text-gray-700 dark:text-gray-300 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-600 file:px-4 file:py-2 file:text-white">
    <p id="cover_help" class="mt-2 text-sm text-gray-500 dark:text-gray-400">JPEG, PNG ou WebP. Até 10 MB e 6000 × 6000 pixels. Após um erro de validação, selecione o arquivo novamente.</p>
    <x-input-error :messages="$errors->get('cover_image')" class="mt-2" />
    <x-input-error :messages="$errors->get('remove_cover')" class="mt-2" />
</div>
<div>
    <x-input-label for="title" value="Título" />
    <x-text-input id="title" name="title" class="block mt-1 w-full" :value="old('title', $project->title)" required maxlength="255" />
    <x-input-error :messages="$errors->get('title')" class="mt-2" />
</div>
<x-project-description-editor :project="$project" />
@include('admin.projects.partials.taxonomy')
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
