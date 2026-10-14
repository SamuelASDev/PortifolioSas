@props(['project'])
@php
    $format = old('description_format', $project->description_format ?? 'text') === 'html' ? 'html' : 'text';
    $value = old('description', $project->description ?? '');
    $value = is_string($value) ? $value : '';
    if ($format === 'html') {
        $value = app(\App\Services\ProjectDescription::class)->sanitize($value);
    }
@endphp
<div data-description-editor class="description-editor">
    <x-input-label id="description-label" for="description" value="Descrição" />
    <input type="hidden" name="description_format" value="{{ $format }}" data-description-format>
    <div data-editor-ui hidden class="description-editor-shell">
        <div data-editor-toolbar class="description-editor-toolbar" role="toolbar" aria-label="Formatação da descrição">
            @foreach ([
                'paragraph' => ['Texto', 'Parágrafo'], 'h2' => ['Título', 'Título de seção'], 'h3' => ['Subtítulo', 'Subtítulo'],
                'bold' => ['Negrito', 'Negrito (Ctrl+B)'], 'italic' => ['Itálico', 'Itálico (Ctrl+I)'],
                'bulletList' => ['• Lista', 'Lista com marcadores'], 'orderedList' => ['1. Lista', 'Lista numerada'],
                'blockquote' => ['Citação', 'Citação'], 'code' => ['Código', 'Código em linha'], 'codeBlock' => ['Bloco de código', 'Bloco de código'],
                'link' => ['Link', 'Inserir ou editar link'], 'unlink' => ['Remover link', 'Remover link'],
                'undo' => ['Desfazer', 'Desfazer'], 'redo' => ['Refazer', 'Refazer'], 'clear' => ['Limpar formato', 'Limpar formatação'],
            ] as $command => [$text, $label])
                <button type="button" data-editor-command="{{ $command }}" aria-label="{{ $label }}" title="{{ $label }}" @if ($command === 'link') aria-expanded="false" aria-controls="description-link-panel" @endif>{{ $text }}</button>
            @endforeach
        </div>
        <div id="description-link-panel" data-link-panel hidden class="description-editor-link-panel">
            <label for="description-link" class="text-sm font-medium">Endereço do link</label>
            <div class="flex flex-wrap items-center gap-2">
                <input id="description-link" type="url" data-link-input placeholder="https://exemplo.com" class="min-w-0 flex-1 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" aria-describedby="description-link-error">
                <button type="button" data-link-apply class="description-editor-link-button">Aplicar</button>
                <button type="button" data-link-cancel class="description-editor-link-button">Cancelar</button>
            </div>
            <p id="description-link-error" data-link-error hidden role="alert" class="text-sm text-red-600 dark:text-red-400">Informe um endereço completo começando com http:// ou https://.</p>
        </div>
        <div data-editor-content></div>
    </div>
    <textarea id="description" name="description" rows="8" maxlength="50000" data-description-source data-initial-format="{{ $format }}" aria-invalid="{{ $errors->has('description') ? 'true' : 'false' }}" aria-describedby="description-help description-error" class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500">{{ $value }}</textarea>
    <p id="description-help" class="mt-2 text-sm text-gray-500 dark:text-gray-400">Organize a explicação com títulos, listas, citações e links. Até 50.000 caracteres. Para a imagem do projeto, use o campo de capa.</p>
    <p data-editor-status hidden role="alert" class="mt-2 text-sm text-red-600 dark:text-red-400"></p>
    <x-input-error id="description-error" :messages="$errors->get('description')" class="mt-2" />
    <x-input-error :messages="$errors->get('description_format')" class="mt-2" />
</div>
