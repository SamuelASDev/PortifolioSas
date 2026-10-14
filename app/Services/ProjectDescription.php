<?php

namespace App\Services;

use HTMLPurifier;
use HTMLPurifier_Config;

class ProjectDescription
{
    private HTMLPurifier $purifier;

    public function __construct()
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Core.Encoding', 'UTF-8');
        $config->set('HTML.Allowed', 'p,br,h2,h3,strong,b,em,i,ul,ol[start],li,blockquote,a[href|title],code,pre');
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true]);
        $config->set('URI.DefaultScheme', null);
        $config->set('URI.DisableExternalResources', true);
        $config->set('Cache.DefinitionImpl', null);
        $this->purifier = new HTMLPurifier($config);
    }

    public function sanitize(?string $html): string
    {
        return $this->purifier->purify($html ?? '');
    }

    public function html(?string $description, ?string $format = 'text'): string
    {
        if ($format === 'html') {
            return $this->sanitize($description);
        }

        return $description === null || $description === '' ? '' : '<p>'.nl2br(e($description)).'</p>';
    }

    public function text(?string $description, ?string $format = 'text'): string
    {
        if ($format !== 'html') {
            return trim($description ?? '');
        }

        $html = $this->sanitize($description);
        $spaced = preg_replace('#</(?:p|h2|h3|li|blockquote|pre)>|<br\s*/?>#i', ' ', $html);
        $text = html_entity_decode(strip_tags($spaced), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $text));
    }
}
