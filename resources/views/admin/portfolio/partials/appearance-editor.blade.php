<dialog data-appearance-editor class="appearance-dialog bg-white text-gray-900 dark:bg-gray-900 dark:text-gray-100" aria-labelledby="appearance-editor-title">
    <script type="application/json" data-appearance-config>{!! \Illuminate\Support\Js::encode(['schema' => config('portfolio-appearance'), 'initial' => app(\App\Support\PortfolioAppearance::class)->settings($portfolio)]) !!}</script>
    <form data-appearance-form action="{{ route('admin.portfolio.appearance.update') }}" class="appearance-editor">
        <header class="appearance-editor-header">
            <div><h2 id="appearance-editor-title" class="text-lg font-semibold">Personalizar aparência</h2><p class="text-sm text-gray-500 dark:text-gray-400">Prévia privada com o conteúdo salvo. Ajustes só são aplicados ao salvar.</p></div>
            <button type="button" data-appearance-close class="appearance-control" aria-label="Fechar e descartar alterações">✕</button>
        </header>
        <nav class="appearance-mobile-tabs" aria-label="Área do editor">
            <button type="button" class="appearance-control" data-appearance-pane="settings" aria-pressed="true">Configurações</button>
            <button type="button" class="appearance-control" data-appearance-pane="preview" aria-pressed="false">Prévia</button>
        </nav>
        <div class="appearance-editor-body" data-pane="settings">
            <aside class="appearance-settings" aria-label="Configurações de aparência">
                <a href="{{ route('admin.projects.organize') }}" target="_blank" rel="noopener" class="appearance-control">Organizar projetos e destaques</a>
                <p class="text-xs text-gray-500 dark:text-gray-400">Depois de salvar a organização, use Início para atualizar a prévia.</p>
                <label class="block text-sm font-medium" for="appearance-preset">Temas prontos</label>
                <select id="appearance-preset" data-appearance-preset class="appearance-input">
                    <option value="">Personalizado</option>
                    @foreach (config('portfolio-appearance.presets') as $key => $preset)
                        <option value="{{ $key }}">{{ $preset['label'] }}</option>
                    @endforeach
                </select>
                <button type="button" data-appearance-reset class="appearance-control w-full">Restaurar tema selecionado</button>
                <details class="appearance-layout-options" open>
                    <summary class="font-semibold">Layout e acabamento</summary>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Combine as opções e confira o resultado no computador e no celular.</p>
                    @foreach (config('portfolio-appearance.options') as $key => $option)
                        <label class="block text-sm" for="appearance-{{ $key }}">{{ $option['label'] }}</label>
                        <select id="appearance-{{ $key }}" data-appearance-field="{{ $key }}" class="appearance-input">
                            @foreach ($option['values'] as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    @endforeach
                </details>
                <fieldset class="space-y-3 border-t border-gray-300 dark:border-gray-700 pt-4">
                    <legend class="font-semibold pt-4">Tipografia</legend>
                    <label class="block text-sm" for="appearance-font">Fonte geral</label>
                    <select id="appearance-font" data-appearance-field="font" class="appearance-input">
                        @foreach (config('portfolio-appearance.fonts') as $key => $font)
                            <option value="{{ $key }}">{{ $font['label'] }}</option>
                        @endforeach
                    </select>
                    <label class="block text-sm" for="appearance-base-size">Tamanho geral</label>
                    <select id="appearance-base-size" data-appearance-field="base_size" class="appearance-input"><option value="14">Compacto</option><option value="16">Padrão</option><option value="18">Ampliado</option></select>
                    <label class="block text-sm" for="appearance-title-scale">Tamanho dos títulos</label>
                    <select id="appearance-title-scale" data-appearance-field="title_scale" class="appearance-input"><option value="90">Discreto</option><option value="100">Padrão</option><option value="110">Expressivo</option></select>
                </fieldset>
                <fieldset class="space-y-3 border-t border-gray-300 dark:border-gray-700 pt-4">
                    <legend class="font-semibold pt-4">Cores</legend>
                    <label class="block text-sm" for="appearance-theme">Base do tema</label>
                    <select id="appearance-theme" data-appearance-theme class="appearance-input"><option value="dark">Escura</option><option value="light">Clara</option></select>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Trocar a base restaura as cores das áreas. A fonte e a cor de destaque são mantidas.</p>
                    <label class="appearance-color-row" for="appearance-accent"><span>Destaque e botões</span><input id="appearance-accent" type="color" data-appearance-accent></label>
                    @foreach (config('portfolio-appearance.colors') as $key => $label)
                        <label class="appearance-color-row" for="appearance-color-{{ $key }}"><span>{{ $label }}</span><input id="appearance-color-{{ $key }}" type="color" data-appearance-color="{{ $key }}"></label>
                    @endforeach
                    <p data-appearance-contrast class="text-sm text-amber-700 dark:text-amber-300" hidden>Alguns textos têm pouco contraste com o fundo. Confira a legibilidade antes de salvar.</p>
                </fieldset>
            </aside>
            <section class="appearance-preview" aria-label="Prévia do portfólio">
                <div class="appearance-preview-toolbar">
                    <div class="flex gap-2" role="group" aria-label="Tamanho da prévia">
                        <button type="button" class="appearance-control" data-appearance-device="desktop" aria-pressed="true">Computador</button>
                        <button type="button" class="appearance-control" data-appearance-device="mobile" aria-pressed="false">Celular</button>
                    </div>
                    <button type="button" class="appearance-control" data-appearance-home>Início</button>
                </div>
                <div class="appearance-preview-canvas" data-appearance-canvas>
                    <iframe data-appearance-frame data-src="{{ route('admin.portfolio.preview') }}" title="Prévia privada do seu portfólio" class="appearance-preview-frame"></iframe>
                </div>
            </section>
        </div>
        <footer class="appearance-editor-footer">
            <div><p data-appearance-status role="status" class="text-sm" aria-live="polite"></p><p class="text-xs text-gray-500 dark:text-gray-400">{{ $portfolio->is_published ? 'Ao salvar, a aparência do site publicado será atualizada.' : 'Salvar mantém seu portfólio em rascunho.' }}</p></div>
            <div class="flex gap-2"><button type="button" data-appearance-close class="appearance-control">Cancelar</button><x-primary-button data-appearance-save>Salvar aparência</x-primary-button></div>
        </footer>
    </form>
</dialog>
