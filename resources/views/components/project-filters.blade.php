@props(['filters', 'tags', 'categories', 'action', 'variant' => 'public'])
@php
    $public = $variant === 'public';
    $inputClass = $public ? 'pf-filter-input' : 'mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm';
@endphp
<form method="GET" action="{{ $action }}{{ $public ? '#projetos' : '' }}" class="{{ $public ? 'pf-project-filters' : 'studio-panel studio-project-filters space-y-4' }}" aria-label="Filtrar projetos">
    <div class="{{ $public ? 'pf-filter-fields' : 'grid gap-4 sm:grid-cols-2 lg:grid-cols-4' }}">
        <label class="{{ $public ? 'pf-filter-label' : 'block text-sm' }}"><span><x-public-icon name="search" /> Buscar por título</span><input type="search" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="100" placeholder="Nome do projeto" class="{{ $inputClass }}"></label>
        <label class="{{ $public ? 'pf-filter-label' : 'block text-sm' }}"><span><x-public-icon name="tag" /> Categoria</span><select name="category" class="{{ $inputClass }}"><option value="">Todas as categorias</option>@foreach ($categories as $key => $category)<option value="{{ $key }}" @selected(($filters['category'] ?? '') === $key)>{{ $category['label'] }}</option>@endforeach</select></label>
        <label class="{{ $public ? 'pf-filter-label' : 'block text-sm' }}"><span><x-public-icon name="code" /> Tecnologia / tag</span><select name="tag" class="{{ $inputClass }}"><option value="">Todas as tags</option>@foreach ($tags as $tag)<option value="{{ $tag->id }}" @selected((string) ($filters['tag'] ?? '') === (string) $tag->id)>{{ $tag->name }}</option>@endforeach</select></label>
        <label class="{{ $public ? 'pf-filter-label' : 'block text-sm' }}"><span><x-public-icon name="star" /> Destaques</span><select name="featured" class="{{ $inputClass }}"><option value="">Todos os projetos</option><option value="1" @selected((string) ($filters['featured'] ?? '') === '1')>Em destaque</option><option value="0" @selected((string) ($filters['featured'] ?? '') === '0')>Sem destaque</option></select></label>
    </div>
    <div class="{{ $public ? 'pf-filter-actions' : 'flex flex-wrap gap-3 items-center' }}"><button type="submit" class="{{ $public ? 'pf-button pf-button-secondary' : 'appearance-control gap-2' }}"><x-public-icon name="filter" /> Filtrar</button><a href="{{ $action }}{{ $public ? '#projetos' : '' }}" class="{{ $public ? 'pf-link' : 'text-sm underline' }}">Limpar filtros</a></div>
</form>
