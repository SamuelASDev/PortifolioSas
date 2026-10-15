@props(['metadata', 'variant' => 'public', 'showCopy' => true])
@php($public = $variant !== 'admin')
<div data-share-actions class="{{ $public ? 'pf-share-actions'.($variant === 'link' ? ' pf-share-actions-inline' : '') : 'space-y-3' }}">
    <script type="application/json" data-share-payload>{!! \Illuminate\Support\Js::encode(\Illuminate\Support\Arr::only($metadata, ['title', 'description', 'url'])) !!}</script>
    <div class="{{ $public ? 'pf-share-buttons' : 'flex flex-wrap gap-3' }}" role="group" aria-label="Compartilhar link público">
        @if ($showCopy)
            <button type="button" data-copy-link disabled class="{{ $public ? 'pf-button pf-button-secondary' : 'appearance-control gap-2' }}"><x-public-icon name="copy" /> Copiar link</button>
        @endif
        <button type="button" data-share-link disabled class="{{ $variant === 'link' ? 'pf-link pf-share-link' : ($public ? 'pf-button pf-button-secondary' : 'appearance-control gap-2') }}">Compartilhar <x-public-icon name="share" /></button>
    </div>
    <label data-share-manual class="{{ $public ? 'pf-share-manual' : 'block text-sm' }}">
        <span class="sr-only">Link público para copiar</span>
        <input data-share-url type="url" readonly value="{{ $metadata['url'] }}" aria-label="Link público para copiar" class="{{ $public ? 'pf-share-url' : 'block w-full rounded-md border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm' }}">
    </label>
    <p data-share-status role="status" aria-live="polite" class="{{ $public ? 'pf-muted pf-share-status' : 'text-sm text-gray-500 dark:text-gray-400' }}"></p>
</div>
@once
    @vite('resources/js/share-links.js')
@endonce
