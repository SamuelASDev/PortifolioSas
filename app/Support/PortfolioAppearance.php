<?php

namespace App\Support;

class PortfolioAppearance
{
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
