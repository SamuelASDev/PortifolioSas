<?php

namespace App\Support;

use App\Models\Portfolio;

class PortfolioAppearance
{
    public function settings(Portfolio $portfolio): array
    {
        $values = $portfolio->appearance ?? [];

        return [
            'theme' => $portfolio->theme === 'light' ? 'light' : 'dark',
            'primary_color' => $this->color($portfolio->primary_color),
            'appearance' => [
                'font' => array_key_exists($values['font'] ?? '', config('portfolio-appearance.fonts')) ? $values['font'] : 'system',
                'base_size' => in_array($values['base_size'] ?? null, [14, 16, 18], true) ? $values['base_size'] : 16,
                'title_scale' => in_array($values['title_scale'] ?? null, [90, 100, 110], true) ? $values['title_scale'] : 100,
                ...collect(config('portfolio-appearance.options'))->mapWithKeys(fn ($option, $key) => [$key => array_key_exists($values[$key] ?? '', $option['values']) ? $values[$key] : $option['default']])->all(),
                'colors' => array_filter(array_intersect_key($values['colors'] ?? [], config('portfolio-appearance.colors')), fn ($color) => is_string($color) && preg_match('/^#[0-9a-fA-F]{6}$/', $color)),
            ],
        ];
    }

    public function styles(Portfolio $portfolio): string
    {
        $settings = $this->settings($portfolio);
        $values = $settings['appearance'];
        $colors = array_replace(config('portfolio-appearance.palettes.'.$settings['theme']), $values['colors']);
        $styles = ['accent' => $settings['primary_color'], 'accent-ink' => $this->foreground($settings['primary_color'])] + $colors;
        $styles['font'] = config('portfolio-appearance.fonts.'.$values['font'].'.css');
        $styles['base-size'] = $values['base_size'].'px';
        $styles['title-scale'] = $values['title_scale'] / 100;

        return collect($styles)->map(fn ($value, $key) => '--'.$key.': '.$value.';')->implode(' ');
    }

    public function color(?string $color): string
    {
        return is_string($color) && preg_match('/^#[0-9a-fA-F]{6}$/', $color) ? $color : '#6366f1';
    }

    public function foreground(string $color): string
    {
        $rgb = array_map(function ($channel) {
            $value = hexdec($channel) / 255;

            return $value <= 0.04045 ? $value / 12.92 : (($value + 0.055) / 1.055) ** 2.4;
        }, str_split(substr($this->color($color), 1), 2));
        $luminance = 0.2126 * $rgb[0] + 0.7152 * $rgb[1] + 0.0722 * $rgb[2];

        return ($luminance + 0.05) / 0.05 >= 1.05 / ($luminance + 0.05) ? '#000000' : '#ffffff';
    }
}
