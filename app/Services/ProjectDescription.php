<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use HTMLPurifier;
use HTMLPurifier_Config;

class ProjectDescription
{
    private HTMLPurifier $purifier;

    public function __construct()
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Core.Encoding', 'UTF-8');
        $config->set('HTML.Allowed', 'p,br,h2,h3,strong,b,em,i,ul,ol[start],li,blockquote,a[href|title],code,pre,figure,figcaption,img[src|alt|title|data-media-id|loading],iframe[src|title|allowfullscreen|loading|referrerpolicy]');
        $config->set('HTML.SafeIframe', true);
        $config->set('URI.SafeIframeRegexp', '#\Ahttps://(?:www\.youtube-nocookie\.com/embed/[a-zA-Z0-9_-]{11}|player\.vimeo\.com/video/[0-9]{1,12})\z#');
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true]);
        $config->set('URI.DefaultScheme', null);
        $config->set('URI.DisableExternalResources', false);
        $config->set('Cache.DefinitionImpl', null);
        $definition = $config->getHTMLDefinition(true);
        $definition->addElement('figure', 'Block', 'Flow', 'Common');
        $definition->addElement('figcaption', 'Block', 'Flow', 'Common');
        $definition->addAttribute('img', 'data-media-id', 'Text');
        $definition->addAttribute('img', 'loading', 'Enum#lazy');
        $definition->addAttribute('iframe', 'allowfullscreen', 'Bool');
        $definition->addAttribute('iframe', 'loading', 'Enum#lazy');
        $definition->addAttribute('iframe', 'referrerpolicy', 'Enum#strict-origin-when-cross-origin');
        $this->purifier = new HTMLPurifier($config);
    }

    public function sanitize(?string $html, iterable $media = []): string
    {
        $media = is_array($media) ? $media : iterator_to_array($media);
        $clean = $this->purifier->purify($this->normalizeMedia($html ?? '', $media));

        // Confere novamente as mídias após a normalização de HTML malformado.
        return $this->purifier->purify($this->normalizeMedia($clean, $media));
    }

    public function html(?string $description, ?string $format = 'text', iterable $media = []): string
    {
        if ($format === 'html') {
            return $this->sanitize($description, $media);
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

    public function mediaIds(string $html): array
    {
        if (! preg_match('/<img\b/i', $html)) {
            return [];
        }
        $dom = new DOMDocument;
        $dom->loadHTML('<?xml encoding="UTF-8"><html><body>'.$html.'</body></html>', LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        $ids = [];
        foreach ($dom->getElementsByTagName('img') as $image) {
            $id = $image->getAttribute('data-media-id');
            if (preg_match('/^[a-f0-9-]{36}$/i', $id)) {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }

    public function hasContent(string $html): bool
    {
        return $this->text($html, 'html') !== '' || preg_match('/<(?:img|iframe)\b/i', $html);
    }

    private function normalizeMedia(string $html, iterable $media): string
    {
        if (! preg_match('/<(?:img|iframe|figure)\b/i', $html)) {
            return $html;
        }
        $known = [];
        foreach ($media as $item) {
            if (dirname($item->path) === 'portfolios/'.$item->portfolio_id.'/attachments'
                && preg_match('/^[a-zA-Z0-9_-]+\.(?:jpg|jpeg|png|webp)$/', basename($item->path))) {
                $known[$item->id] = $item;
            }
        }
        $dom = new DOMDocument;
        $dom->loadHTML('<?xml encoding="UTF-8"><html><body>'.$html.'</body></html>', LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        foreach (iterator_to_array($dom->getElementsByTagName('img')) as $image) {
            $record = $known[$image->getAttribute('data-media-id')] ?? null;
            if (! $record) {
                $image->parentNode->removeChild($image);

                continue;
            }
            $alt = mb_substr($image->getAttribute('alt'), 0, 255);
            $this->resetAttributes($image, ['src' => $record->url, 'data-media-id' => $record->id, 'alt' => $alt, 'loading' => 'lazy']);
        }
        foreach (iterator_to_array($dom->getElementsByTagName('figure')) as $figure) {
            if ($figure->getElementsByTagName('img')->length === 0) {
                $figure->parentNode?->removeChild($figure);
            }
        }
        foreach (iterator_to_array($dom->getElementsByTagName('iframe')) as $frame) {
            $video = (new VideoEmbed)->resolve($frame->getAttribute('src'));
            if (! $video) {
                $frame->parentNode->removeChild($frame);

                continue;
            }
            while ($frame->firstChild) {
                $frame->removeChild($frame->firstChild);
            }
            $this->resetAttributes($frame, $video + ['allowfullscreen' => 'allowfullscreen', 'loading' => 'lazy', 'referrerpolicy' => 'strict-origin-when-cross-origin']);
        }
        $body = $dom->getElementsByTagName('body')->item(0);
        if (! $body) {
            return '';
        }
        $result = '';
        foreach ($body->childNodes as $node) {
            $result .= $dom->saveHTML($node);
        }

        return $result;
    }

    private function resetAttributes(DOMElement $element, array $attributes): void
    {
        foreach (iterator_to_array($element->attributes) as $attribute) {
            $element->removeAttribute($attribute->name);
        }
        foreach ($attributes as $name => $value) {
            $element->setAttribute($name, $value);
        }
    }
}
