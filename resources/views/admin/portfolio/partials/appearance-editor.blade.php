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
                @php
                    $choice = function (string $field, array $values, string $name) {
                        $html = '<div class="appearance-segmented" role="radiogroup">';
                        foreach ($values as $value => $label) {
                            $html .= '<label><input type="radio" name="'.e($name).'" value="'.e($value).'" data-appearance-field="'.e($field).'"><span>'.e($label).'</span></label>';
                        }
                        return $html.'</div>';
                    };
                    $options = config('portfolio-appearance.options');
                    $groups = ['Layout da página' => ['hero', 'grid', 'width', 'spacing', 'header', 'excerpts'], 'Detalhes visuais' => ['corners', 'ratio', 'portrait', 'cards', 'backdrop', 'buttons']];
                @endphp
                <input type="hidden" data-appearance-preset value="">
                <section class="appearance-step" aria-labelledby="appearance-step-theme">
                    <h3 id="appearance-step-theme" class="appearance-step-title"><span>1</span> Comece por um tema</h3>
                    <p data-preset-description class="appearance-preset-description" aria-live="polite">Escolha um tema e ajuste os detalhes nas etapas abaixo.</p>
                    <div class="appearance-preset-grid" role="group" aria-label="Temas prontos">
                        @foreach (config('portfolio-appearance.presets') as $key => $preset)
                            @php($palette = array_replace(config('portfolio-appearance.palettes.'.$preset['theme']), $preset['colors']))
                            <button type="button" class="appearance-preset-card" data-preset-choice="{{ $key }}" aria-pressed="false" aria-label="Aplicar tema {{ $preset['label'] }}">
                                <span class="appearance-preset-art" aria-hidden="true" data-sample-layout="{{ $preset['appearance']['hero'] ?? 'split' }}" style="--sample-bg: {{ $palette['page'] }}; --sample-card: {{ $palette['surface'] }}; --sample-ink: {{ $palette['title'] }}; --sample-accent: {{ $preset['primary_color'] }}; --sample-font: {{ config('portfolio-appearance.fonts.'.$preset['font'].'.css') }}">
                                    <span class="appearance-sample-title">Aa<span>.</span></span><span class="appearance-sample-photo"></span>
                                    <span class="appearance-sample-projects"><i></i><i></i></span>
                                </span>
                                <span class="appearance-preset-name">{{ $preset['label'] }}</span>
                            </button>
                        @endforeach
                    </div>
                    <button type="button" data-appearance-reset class="appearance-control w-full">Desfazer ajustes do tema</button>
                </section>
                <section class="appearance-step" aria-labelledby="appearance-step-colors">
                    <h3 id="appearance-step-colors" class="appearance-step-title"><span>2</span> Cores</h3>
                    <p class="appearance-field-label">Fundo</p>
                    <div class="appearance-segmented" role="radiogroup" aria-label="Base do tema">
                        <label><input type="radio" name="appearance-theme" value="dark" data-appearance-theme><span>Escuro</span></label>
                        <label><input type="radio" name="appearance-theme" value="light" data-appearance-theme><span>Claro</span></label>
                    </div>
                    <label class="appearance-color-row appearance-color-main" for="appearance-accent"><span>Cor de destaque<small>Botões, links e detalhes</small></span><input id="appearance-accent" type="color" data-appearance-accent></label>
                    <details class="appearance-more">
                        <summary>Ajustar cada área</summary>
                        <p class="appearance-hint">Trocar o fundo restaura estas cores.</p>
                        @foreach (config('portfolio-appearance.colors') as $key => $label)
                            <label class="appearance-color-row" for="appearance-color-{{ $key }}"><span>{{ $label }}</span><input id="appearance-color-{{ $key }}" type="color" data-appearance-color="{{ $key }}"></label>
                        @endforeach
                    </details>
                    <p data-appearance-contrast class="appearance-warning" hidden>Alguns textos têm pouco contraste com o fundo. Confira a legibilidade antes de salvar.</p>
                </section>
                <section class="appearance-step" aria-labelledby="appearance-step-type">
                    <h3 id="appearance-step-type" class="appearance-step-title"><span>3</span> Texto</h3>
                    <p class="appearance-field-label">Fonte</p>
                    <div class="appearance-font-grid" role="radiogroup" aria-label="Fonte geral">
                        @foreach (config('portfolio-appearance.fonts') as $key => $font)
                            <label><input type="radio" name="appearance-font" value="{{ $key }}" data-appearance-field="font"><span><b style="font-family: {{ $font['css'] }}">Aa</b>{{ $font['label'] }}</span></label>
                        @endforeach
                    </div>
                    <p class="appearance-field-label">Tamanho do texto</p>
                    {!! $choice('base_size', [14 => 'Compacto', 16 => 'Padrão', 18 => 'Ampliado'], 'appearance-base-size') !!}
                    <p class="appearance-field-label">Tamanho dos títulos</p>
                    {!! $choice('title_scale', [90 => 'Discreto', 100 => 'Padrão', 110 => 'Expressivo'], 'appearance-title-scale') !!}
                </section>
                @foreach ($groups as $title => $keys)
                    <details class="appearance-step appearance-step-collapsible" @if ($loop->first) open @endif>
                        <summary class="appearance-step-title"><span>{{ $loop->iteration + 3 }}</span> {{ $title }}</summary>
                        @foreach ($keys as $key)
                            <p class="appearance-field-label">{{ $options[$key]['label'] }}</p>
                            {!! $choice($key, $options[$key]['values'], 'appearance-'.$key) !!}
                        @endforeach
                    </details>
                @endforeach
                <a href="{{ route('admin.projects.organize') }}" target="_blank" rel="noopener" class="appearance-control">Organizar projetos e destaques</a>
            </aside>
            <section class="appearance-preview" aria-label="Prévia do portfólio">
                <div class="appearance-preview-toolbar">
                    <div class="flex gap-2" role="group" aria-label="Tamanho da prévia">
                        <button type="button" class="appearance-control" data-appearance-device="desktop" aria-pressed="true">Computador</button>
                        <button type="button" class="appearance-control" data-appearance-device="mobile" aria-pressed="false">Celular</button>
                    </div>
                    <div class="flex gap-2" role="group" aria-label="Página exibida na prévia">
                        <button type="button" class="appearance-control" data-appearance-page="{{ route('admin.portfolio.preview') }}" aria-pressed="true">Início</button>
                        <button type="button" class="appearance-control" data-appearance-page="{{ route('admin.portfolio.preview.about') }}" aria-pressed="false">Sobre</button>
                        <button type="button" class="appearance-control" data-appearance-page="{{ route('admin.portfolio.preview.contact') }}" aria-pressed="false">Contato</button>
                    </div>
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
