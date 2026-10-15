<?php

return [
    'fonts' => [
        'system' => ['label' => 'Moderna', 'css' => 'ui-sans-serif, system-ui, sans-serif'],
        'serif' => ['label' => 'Editorial', 'css' => 'Georgia, Cambria, serif'],
        'mono' => ['label' => 'Código', 'css' => 'ui-monospace, Consolas, monospace'],
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
        'dark' => ['label' => 'Escuro original', 'theme' => 'dark', 'primary_color' => '#6366f1', 'font' => 'system', 'colors' => []],
        'light' => ['label' => 'Claro essencial', 'theme' => 'light', 'primary_color' => '#24634b', 'font' => 'system', 'colors' => []],
        'editorial' => ['label' => 'Editorial', 'theme' => 'light', 'primary_color' => '#9a441f', 'font' => 'serif', 'colors' => ['page' => '#f5efe5', 'header' => '#f5efe5', 'footer' => '#f5efe5', 'card' => '#fffaf2', 'surface' => '#fffaf2']],
        'terminal' => ['label' => 'Terminal', 'theme' => 'dark', 'primary_color' => '#52d99b', 'font' => 'mono', 'colors' => ['page' => '#0c1713', 'header' => '#0c1713', 'footer' => '#0c1713', 'surface' => '#14271f', 'card' => '#14271f']],
    ],
];
