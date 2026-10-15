@props(['portfolio', 'project' => null])
@php
    $record = $project ?? $portfolio;
    $service = app(\App\Support\SharingMetadata::class);
    $metadata = $project ? $service->forProject($portfolio, $project) : $service->forPortfolio($portfolio);
    $sharingErrors = $errors->getBag('sharing');
@endphp
<section data-sharing-settings class="p-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm space-y-6 text-gray-900 dark:text-gray-100" aria-labelledby="sharing-heading">
    <h3 id="sharing-heading" class="text-lg font-semibold">Compartilhamento</h3>
    <p class="text-sm text-gray-500 dark:text-gray-400">Personalize o cartão que representa este {{ $project ? 'projeto' : 'portfólio' }} ao compartilhar o link. Campos vazios usam o conteúdo original. Salvar não publica rascunhos.</p>
    <form method="POST" enctype="multipart/form-data" action="{{ $project ? route('admin.projects.sharing.update', $project) : route('admin.portfolio.sharing.update') }}" class="space-y-5">
        @csrf
        @method('PATCH')
        <div>
            <x-input-label for="share_title" value="Título de compartilhamento (opcional)" />
            <x-text-input id="share_title" name="share_title" data-share-title class="mt-1 block w-full" maxlength="100" :value="old('share_title', $record->share_title)" :placeholder="$metadata['fallback_title']" />
            <x-input-error :messages="$sharingErrors->get('share_title')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="share_description" value="Descrição de compartilhamento (opcional)" />
            <textarea id="share_description" name="share_description" data-share-description maxlength="300" rows="3" placeholder="{{ $metadata['fallback_description'] }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100">{{ old('share_description', $record->share_description) }}</textarea>
            <x-input-error :messages="$sharingErrors->get('share_description')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="share_image" value="Imagem de compartilhamento (opcional)" />
            <input id="share_image" name="share_image" data-share-image type="file" accept="image/jpeg,image/png,image/webp" class="mt-2 block w-full text-sm" aria-describedby="share-image-help">
            <p id="share-image-help" class="mt-2 text-sm text-gray-500 dark:text-gray-400">JPEG, PNG ou WebP, até 2 MB e 6000 × 6000 pixels. Recomendado: 1200 × 630 pixels. Após erro de validação, selecione o arquivo novamente.</p>
            @if ($record->share_image_path)
                <label class="mt-3 flex gap-2 items-center text-sm"><input type="checkbox" name="remove_share_image" data-share-remove value="1" @checked(old('remove_share_image', false)) class="rounded dark:bg-gray-900">Remover imagem personalizada e usar a {{ $project ? 'capa ou foto disponível' : 'foto de apresentação' }}</label>
            @endif
            <x-input-error :messages="$sharingErrors->get('share_image')" class="mt-2" />
            <x-input-error :messages="$sharingErrors->get('remove_share_image')" class="mt-2" />
        </div>
        <div class="max-w-lg overflow-hidden rounded-xl border border-gray-300 dark:border-gray-600" data-sharing-card>
            <img data-share-preview-image @if ($metadata['image']) src="{{ $metadata['image'] }}" @else hidden @endif alt="Prévia da imagem de compartilhamento" class="w-full aspect-[1200/630] object-cover">
            <div class="p-4 space-y-2 break-words"><p class="text-xs text-gray-500 dark:text-gray-400">{{ parse_url($metadata['url'], PHP_URL_HOST) }}</p><p data-share-preview-title class="font-semibold">{{ old('share_title') ?: $metadata['title'] }}</p><p data-share-preview-description class="text-sm text-gray-500 dark:text-gray-400">{{ old('share_description') ?: $metadata['description'] }}</p></div>
        </div>
        <script type="application/json" data-sharing-defaults>{!! \Illuminate\Support\Js::encode($metadata) !!}</script>
        <p class="text-xs text-gray-500 dark:text-gray-400">Simulação do cartão. Cada plataforma pode recortar a imagem e manter uma versão em cache.</p>
        <x-primary-button>Salvar compartilhamento</x-primary-button>
    </form>
</section>
