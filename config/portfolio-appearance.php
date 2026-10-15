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
        'studio' => ['label' => 'Estúdio Vértice', 'theme' => 'dark', 'primary_color' => '#b5a0ff', 'font' => 'manrope', 'appearance' => ['hero' => 'split', 'grid' => 'two', 'backdrop' => 'glow', 'cards' => 'elevated'], 'colors' => ['page' => '#10121b', 'header' => '#10121b', 'footer' => '#10121b', 'surface' => '#191d2b', 'card' => '#191d2b']],
        'ocean' => ['label' => 'Oceano', 'theme' => 'light', 'primary_color' => '#075e78', 'font' => 'manrope', 'appearance' => ['hero' => 'centered', 'portrait' => 'circle', 'grid' => 'two', 'backdrop' => 'grid'], 'colors' => ['page' => '#f0f7f8', 'header' => '#f0f7f8', 'footer' => '#f0f7f8']],
        'gallery' => ['label' => 'Galeria minimalista', 'theme' => 'light', 'primary_color' => '#222222', 'font' => 'manrope', 'appearance' => ['hero' => 'editorial', 'corners' => 'sharp', 'grid' => 'two', 'cards' => 'flat', 'backdrop' => 'plain', 'spacing' => 'airy'], 'colors' => []],
        'dark' => ['label' => 'Escuro original', 'theme' => 'dark', 'primary_color' => '#6366f1', 'font' => 'system', 'colors' => []],
        'light' => ['label' => 'Claro essencial', 'theme' => 'light', 'primary_color' => '#24634b', 'font' => 'system', 'colors' => []],
        'editorial' => ['label' => 'Editorial', 'theme' => 'light', 'primary_color' => '#9a441f', 'font' => 'serif', 'colors' => ['page' => '#f5efe5', 'header' => '#f5efe5', 'footer' => '#f5efe5', 'card' => '#fffaf2', 'surface' => '#fffaf2']],
        'terminal' => ['label' => 'Terminal', 'theme' => 'dark', 'primary_color' => '#52d99b', 'font' => 'mono', 'colors' => ['page' => '#0c1713', 'header' => '#0c1713', 'footer' => '#0c1713', 'surface' => '#14271f', 'card' => '#14271f']],
    ],
];
