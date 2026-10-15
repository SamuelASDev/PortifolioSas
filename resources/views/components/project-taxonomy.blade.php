@props(['project', 'variant' => 'public'])
@php($category = config('projects.categories.'.$project->category))
@if (($project->category && $category) || $project->tags->isNotEmpty())
    <ul class="project-taxonomy {{ $variant === 'public' ? 'pf-project-tags' : 'flex flex-wrap gap-2 text-xs text-gray-700 dark:text-gray-300' }}" aria-label="Categoria e tecnologias">
        @if ($project->category && $category)
            <li title="{{ $category['label'] }}" class="{{ $variant === 'public' ? 'pf-project-tag pf-project-category' : 'inline-flex items-center gap-1 rounded-md border border-indigo-400 px-2 py-1' }}"><x-public-icon :name="$category['icon']" /><span class="sr-only min-[641px]:not-sr-only">{{ $category['label'] }}</span></li>
        @endif
        @foreach ($project->tags as $tag)
            <li title="{{ $tag->name }}" class="{{ $variant === 'public' ? 'pf-project-tag' : 'inline-flex items-center gap-1 rounded-md border border-gray-400 dark:border-gray-600 px-2 py-1' }}"><x-public-icon :name="app(\App\Support\ProjectTags::class)->icon($tag->name)" /><span class="sr-only min-[641px]:not-sr-only">{{ $tag->name }}</span></li>
        @endforeach
    </ul>
    <details class="project-taxonomy-names min-[641px]:hidden">
        <summary class="text-xs cursor-pointer mt-2 underline underline-offset-4">Ver categoria e tecnologias</summary>
        <p class="text-xs mt-2">{{ collect([$project->category && $category ? $category['label'] : null])->merge($project->tags->pluck('name'))->filter()->implode(' · ') }}</p>
    </details>
@endif
