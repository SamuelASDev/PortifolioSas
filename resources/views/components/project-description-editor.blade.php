@props(['project'])
@php
    $format = old('description_format', $project->description_format ?? 'text') === 'html' ? 'html' : 'text';
    $value = old('description', $project->description ?? '');
    $value = is_string($value) ? $value : '';
    $mediaToken = old('media_token');
    $mediaToken = is_string($mediaToken) && \Illuminate\Support\Str::isUuid($mediaToken) ? $mediaToken : (string) \Illuminate\Support\Str::uuid();
    if ($format === 'html') {
        $media = app(\App\Services\ProjectMediaManager::class)->available(auth()->user()->portfolio, $project->exists ? $project : null, $mediaToken);
        $value = app(\App\Services\ProjectDescription::class)->sanitize($value, $media);
    }
@endphp
<div data-description-editor data-media-base-url="{{ app(\App\Services\ProjectAttachmentStorage::class)->url('portfolios/'.auth()->user()->portfolio->id.'/attachments/') }}" data-upload-url="{{ route('admin.project-media.store') }}" data-video-url="{{ route('admin.project-media.video') }}" data-project-id="{{ $project->id }}" class="description-editor">
    <x-input-label id="description-label" for="description" value="Descrição" />
    <input type="hidden" name="description_format" value="{{ $format }}" data-description-format>
    <input type="hidden" name="media_token" value="{{ $mediaToken }}" data-media-token>
    <div data-editor-ui hidden class="description-editor-shell">
        <div data-editor-toolbar class="description-editor-toolbar" role="toolbar" aria-label="Formatação da descrição">
            @foreach ([
                'paragraph' => ['Texto', 'Parágrafo'], 'h2' => ['Título', 'Título de seção'], 'h3' => ['Subtítulo', 'Subtítulo'],
                'bold' => ['Negrito', 'Negrito (Ctrl+B)'], 'italic' => ['Itálico', 'Itálico (Ctrl+I)'],
                'bulletList' => ['• Lista', 'Lista com marcadores'], 'orderedList' => ['1. Lista', 'Lista numerada'],
                'blockquote' => ['Citação', 'Citação'], 'code' => ['Código', 'Código em linha'], 'codeBlock' => ['Bloco de código', 'Bloco de código'],
                'link' => ['Link', 'Inserir ou editar link'], 'unlink' => ['Remover link', 'Remover link'],
                'image' => ['Imagem', 'Inserir imagem'], 'video' => ['Vídeo', 'Inserir vídeo do YouTube ou Vimeo'],
                'undo' => ['Desfazer', 'Desfazer'], 'redo' => ['Refazer', 'Refazer'], 'clear' => ['Limpar formato', 'Limpar formatação'],
            ] as $command => [$text, $label])
                <button type="button" data-editor-command="{{ $command }}" aria-label="{{ $label }}" title="{{ $label }}" @if (in_array($command, ['link', 'image', 'video'])) aria-expanded="false" aria-controls="description-{{ $command }}-panel" @endif>{{ $text }}</button>
            @endforeach
        </div>
        <div id="description-image-panel" data-image-panel hidden class="description-editor-link-panel">
            <p class="text-sm font-medium">Inserir imagem na descrição</p>
            <x-upload-privacy-note />
            <label for="description-image" class="text-sm">Arquivo JPEG, PNG ou WebP (até 10 MB)</label>
            <input id="description-image" type="file" data-image-input accept="image/jpeg,image/png,image/webp" class="block w-full text-sm">
            <label for="description-image-alt" class="block text-sm">Descrição da imagem (texto alternativo)</label>
            <input id="description-image-alt" data-image-alt type="text" maxlength="255" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
            <label for="description-image-caption" class="block text-sm">Legenda (opcional)</label>
            <input id="description-image-caption" data-image-caption type="text" maxlength="255" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
            <button type="button" data-image-insert class="description-editor-link-button">Enviar e inserir</button>
            <button type="button" data-media-cancel class="description-editor-link-button">Cancelar</button>
        </div>
        <div id="description-video-panel" data-video-panel hidden class="description-editor-link-panel">
            <label for="description-video" class="text-sm font-medium">Link do YouTube ou Vimeo</label>
            <input id="description-video" data-video-input type="text" inputmode="url" placeholder="https://www.youtube.com/watch?v=..." class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
            <button type="button" data-video-insert class="description-editor-link-button">Inserir vídeo</button>
            <button type="button" data-media-cancel class="description-editor-link-button">Cancelar</button>
        </div>
        <div id="description-link-panel" data-link-panel hidden class="description-editor-link-panel">
            <label for="description-link" class="text-sm font-medium">Endereço do link</label>
            <div class="flex flex-wrap items-center gap-2">
                <input id="description-link" type="text" inputmode="url" data-link-input placeholder="https://exemplo.com" class="min-w-0 flex-1 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" aria-describedby="description-link-error">
                <button type="button" data-link-apply class="description-editor-link-button">Aplicar</button>
                <button type="button" data-link-cancel class="description-editor-link-button">Cancelar</button>
            </div>
            <p id="description-link-error" data-link-error hidden role="alert" class="text-sm text-red-600 dark:text-red-400">Informe um endereço completo começando com http:// ou https://.</p>
        </div>
        <div data-editor-content></div>
    </div>
    <textarea id="description" name="description" rows="8" maxlength="50000" data-description-source data-initial-format="{{ $format }}" aria-invalid="{{ $errors->has('description') ? 'true' : 'false' }}" aria-describedby="description-help description-error" class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500">{{ $value }}</textarea>
    <p id="description-help" class="mt-2 text-sm text-gray-500 dark:text-gray-400">Insira textos, imagens e vídeos na posição do cursor. Salve o projeto para confirmar as alterações. Imagens removidas são apagadas após salvar.</p>
    <p data-editor-status hidden role="alert" class="mt-2 text-sm text-red-600 dark:text-red-400"></p>
    <x-input-error id="description-error" :messages="$errors->get('description')" class="mt-2" />
    <x-input-error :messages="$errors->get('description_format')" class="mt-2" />
    <x-input-error :messages="$errors->get('media_token')" class="mt-2" />
</div>
