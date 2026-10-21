<?php

return [
    'fonts' => [
        'manrope' => ['label' => 'Manrope / Estúdio', 'css' => '"Manrope", ui-sans-serif, system-ui, sans-serif'],
        'system' => ['label' => 'Moderna', 'css' => 'ui-sans-serif, system-ui, sans-serif'],
        'serif' => ['label' => 'Editorial', 'css' => 'Georgia, Cambria, serif'],
        'mono' => ['label' => 'Código', 'css' => 'ui-monospace, Consolas, monospace'],
    ],
    'options' => [
        'hero' => ['label' => 'Composição da apresentação', 'default' => 'split', 'values' => ['split' => 'Lado a lado', 'centered' => 'Centralizada', 'editorial' => 'Editorial']],
        'grid' => ['label' => 'Projetos', 'default' => 'three', 'values' => ['three' => 'Três colunas', 'two' => 'Duas colunas', 'list' => 'Lista visual']],
        'width' => ['label' => 'Largura do conteúdo', 'default' => 'standard', 'values' => ['compact' => 'Compacta', 'standard' => 'Equilibrada', 'wide' => 'Ampla']],
        'spacing' => ['label' => 'Espaçamento', 'default' => 'comfortable', 'values' => ['compact' => 'Compacto', 'comfortable' => 'Confortável', 'airy' => 'Generoso']],
        'corners' => ['label' => 'Cantos', 'default' => 'soft', 'values' => ['sharp' => 'Retos', 'soft' => 'Suaves', 'round' => 'Arredondados']],
        'ratio' => ['label' => 'Formato das capas', 'default' => 'landscape', 'values' => ['landscape' => 'Paisagem', 'square' => 'Quadrado', 'wide' => 'Panorâmico']],
        'portrait' => ['label' => 'Formato da foto', 'default' => 'rounded', 'values' => ['rounded' => 'Arredondada', 'circle' => 'Circular', 'square' => 'Reta']],
        'cards' => ['label' => 'Acabamento dos projetos', 'default' => 'outlined', 'values' => ['outlined' => 'Borda discreta', 'flat' => 'Sem borda', 'elevated' => 'Com profundidade']],
        'backdrop' => ['label' => 'Fundo da apresentação', 'default' => 'glow', 'values' => ['plain' => 'Liso', 'glow' => 'Luz suave', 'grid' => 'Grade sutil']],
        'buttons' => ['label' => 'Botões', 'default' => 'solid', 'values' => ['solid' => 'Preenchidos', 'outline' => 'Contornados']],
        'header' => ['label' => 'Cabeçalho', 'default' => 'normal', 'values' => ['normal' => 'Discreto', 'sticky' => 'Fixo durante a rolagem']],
        'excerpts' => ['label' => 'Resumos nos projetos', 'default' => 'show', 'values' => ['show' => 'Exibir', 'hide' => 'Ocultar']],
    ],
    'colors' => [
        'page' => 'Fundo da página', 'surface' => 'Carrossel e superfícies',
        'card' => 'Cartões dos projetos', 'header' => 'Cabeçalho', 'footer' => 'Rodapé',
        'ink' => 'Texto principal', 'title' => 'Títulos', 'muted' => 'Texto secundário',
        'line' => 'Bordas',
    ],
    'palettes' => [
        'dark' => ['page' => '#101216', 'surface' => '#191c22', 'card' => '#191c22', 'header' => '#101216', 'footer' => '#101216', 'ink' => '#f3f4f6', 'title' => '#f3f4f6', 'muted' => '#b2b8c3', 'line' => '#343943'],
        'light' => ['page' => '#fafaf8', 'surface' => '#ffffff', 'card' => '#ffffff', 'header' => '#fafaf8', 'footer' => '#fafaf8', 'ink' => '#18201d', 'title' => '#18201d', 'muted' => '#505c56', 'line' => '#dce1db'],
    ],
    'presets' => [
        'studio' => [
            'label' => 'Estúdio Vértice', 'description' => 'Grafite e lavanda. Composição ampla, projetos em dupla e geometria discreta.',
            'theme' => 'dark', 'primary_color' => '#b6a0f5', 'font' => 'manrope', 'title_scale' => 110,
            'appearance' => ['hero' => 'split', 'grid' => 'two', 'backdrop' => 'grid', 'cards' => 'flat', 'corners' => 'soft', 'spacing' => 'airy', 'portrait' => 'square', 'buttons' => 'solid'],
            'colors' => ['page' => '#101413', 'header' => '#101413', 'footer' => '#101413', 'surface' => '#1b2420', 'card' => '#1b2420', 'ink' => '#f0f2eb', 'title' => '#f0f2eb', 'muted' => '#acb9af', 'line' => '#36483d'],
        ],
        'ocean' => [
            'label' => 'Oceano', 'description' => 'Azul profundo sobre névoa clara. Retrato circular e apresentação centralizada.',
            'theme' => 'light', 'primary_color' => '#07566e', 'font' => 'manrope',
            'appearance' => ['hero' => 'centered', 'portrait' => 'circle', 'grid' => 'two', 'backdrop' => 'plain', 'corners' => 'round', 'cards' => 'outlined', 'width' => 'compact', 'buttons' => 'solid'],
            'colors' => ['page' => '#edf5f6', 'header' => '#edf5f6', 'footer' => '#e1edef', 'surface' => '#f8fcfc', 'card' => '#f8fcfc', 'ink' => '#18343e', 'title' => '#15313d', 'muted' => '#49636c', 'line' => '#bccfd3'],
        ],
        'gallery' => [
            'label' => 'Galeria minimalista', 'description' => 'Branco de galeria, capas quadradas e bastante respiro para o trabalho falar.',
            'theme' => 'light', 'primary_color' => '#252525', 'font' => 'manrope', 'title_scale' => 110,
            'appearance' => ['hero' => 'editorial', 'corners' => 'sharp', 'portrait' => 'square', 'grid' => 'two', 'ratio' => 'square', 'cards' => 'flat', 'backdrop' => 'plain', 'spacing' => 'airy', 'width' => 'wide', 'buttons' => 'outline', 'excerpts' => 'hide'],
            'colors' => ['page' => '#f7f7f2', 'header' => '#f7f7f2', 'footer' => '#f7f7f2', 'surface' => '#e9eae3', 'card' => '#f7f7f2', 'ink' => '#252525', 'title' => '#202020', 'muted' => '#5b5c56', 'line' => '#c9cbc1'],
        ],
        'dark' => [
            'label' => 'Escuro original', 'description' => 'Azul-noturno e índigo. Uma vitrine direta, com três colunas e bordas sutis.',
            'theme' => 'dark', 'primary_color' => '#a5acff', 'font' => 'system',
            'appearance' => ['hero' => 'split', 'grid' => 'three', 'backdrop' => 'glow', 'cards' => 'outlined', 'spacing' => 'comfortable'],
            'colors' => [],
        ],
        'light' => [
            'label' => 'Claro essencial', 'description' => 'Branco e verde profundo. Leitura confortável, linhas limpas e foco no conteúdo.',
            'theme' => 'light', 'primary_color' => '#24634b', 'font' => 'system',
            'appearance' => ['hero' => 'split', 'grid' => 'two', 'cards' => 'outlined', 'backdrop' => 'plain', 'corners' => 'soft', 'portrait' => 'circle'],
            'colors' => [],
        ],
        'editorial' => [
            'label' => 'Editorial', 'description' => 'Papel, terracota e títulos serifados. Projetos em lista, como uma publicação.',
            'theme' => 'light', 'primary_color' => '#93421f', 'font' => 'serif', 'title_scale' => 110,
            'appearance' => ['hero' => 'editorial', 'grid' => 'list', 'cards' => 'flat', 'corners' => 'sharp', 'portrait' => 'square', 'backdrop' => 'plain', 'spacing' => 'airy', 'ratio' => 'landscape', 'buttons' => 'outline'],
            'colors' => ['page' => '#f5efe5', 'header' => '#f5efe5', 'footer' => '#eee4d7', 'card' => '#fffaf2', 'surface' => '#fffaf2', 'ink' => '#372c25', 'title' => '#30231c', 'muted' => '#6d594d', 'line' => '#c8b6a4'],
        ],
        'terminal' => [
            'label' => 'Terminal', 'description' => 'Verde-fósforo, tipografia de código e grade técnica. Direto ao projeto.',
            'theme' => 'dark', 'primary_color' => '#75e0a7', 'font' => 'mono', 'title_scale' => 90,
            'appearance' => ['hero' => 'editorial', 'grid' => 'list', 'spacing' => 'compact', 'corners' => 'sharp', 'portrait' => 'square', 'cards' => 'outlined', 'backdrop' => 'grid', 'buttons' => 'outline', 'header' => 'sticky', 'width' => 'compact'],
            'colors' => ['page' => '#0c1713', 'header' => '#0c1713', 'footer' => '#0c1713', 'surface' => '#14271f', 'card' => '#14271f', 'ink' => '#dcf2e4', 'title' => '#e5f9ed', 'muted' => '#a4c4b1', 'line' => '#345741'],
        ],
        'horizon' => [
            'label' => 'Horizonte', 'description' => 'Argila e areia, títulos serifados e capas panorâmicas. Calor e espaço para criar.',
            'theme' => 'light', 'primary_color' => '#a23d28', 'font' => 'serif', 'title_scale' => 110,
            'appearance' => ['hero' => 'centered', 'grid' => 'two', 'spacing' => 'airy', 'corners' => 'soft', 'portrait' => 'rounded', 'ratio' => 'wide', 'cards' => 'flat', 'backdrop' => 'plain', 'buttons' => 'solid'],
            'colors' => ['page' => '#fbf1e9', 'header' => '#fbf1e9', 'footer' => '#f0dfd1', 'surface' => '#f5e4d5', 'card' => '#fff9f4', 'ink' => '#463028', 'title' => '#6c2c20', 'muted' => '#79594a', 'line' => '#d1b5a3'],
        ],
        'nocturne' => [
            'label' => 'Noturno', 'description' => 'Índigo e ouro suave. Capas de cinema e uma presença mais intimista.',
            'theme' => 'dark', 'primary_color' => '#e6c778', 'font' => 'manrope', 'title_scale' => 110,
            'appearance' => ['hero' => 'split', 'grid' => 'two', 'ratio' => 'wide', 'cards' => 'elevated', 'backdrop' => 'glow', 'corners' => 'soft', 'spacing' => 'airy', 'buttons' => 'outline', 'header' => 'sticky'],
            'colors' => ['page' => '#151624', 'header' => '#151624', 'footer' => '#11121e', 'surface' => '#232538', 'card' => '#232538', 'ink' => '#eeecf3', 'title' => '#f6eedb', 'muted' => '#b5b3c6', 'line' => '#47485d'],
        ],
        'botanical' => [
            'label' => 'Botânico', 'description' => 'Sálvia e verde-floresta. Retrato circular, imagens quadradas e ritmo tranquilo.',
            'theme' => 'light', 'primary_color' => '#31583f', 'font' => 'manrope',
            'appearance' => ['hero' => 'split', 'grid' => 'two', 'ratio' => 'square', 'cards' => 'flat', 'backdrop' => 'plain', 'portrait' => 'circle', 'corners' => 'round', 'spacing' => 'airy', 'width' => 'compact'],
            'colors' => ['page' => '#edf1e8', 'header' => '#edf1e8', 'footer' => '#e1e8d9', 'surface' => '#f8faf2', 'card' => '#f8faf2', 'ink' => '#28382b', 'title' => '#273e2c', 'muted' => '#53644e', 'line' => '#bac7b0'],
        ],
        'concrete' => [
            'label' => 'Concreto', 'description' => 'Grafite sobre cinza mineral. Linhas retas, composição técnica e projetos em lista.',
            'theme' => 'light', 'primary_color' => '#292d2c', 'font' => 'mono',
            'appearance' => ['hero' => 'editorial', 'grid' => 'list', 'ratio' => 'wide', 'cards' => 'outlined', 'backdrop' => 'grid', 'corners' => 'sharp', 'portrait' => 'square', 'spacing' => 'compact', 'width' => 'wide', 'buttons' => 'solid', 'header' => 'sticky'],
            'colors' => ['page' => '#e9eae6', 'header' => '#e9eae6', 'footer' => '#dedfd9', 'surface' => '#f5f5f0', 'card' => '#f5f5f0', 'ink' => '#292d2c', 'title' => '#1f2422', 'muted' => '#555e57', 'line' => '#b3b9ae'],
        ],
    ],
];
