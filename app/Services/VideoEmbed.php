<?php

namespace App\Services;

class VideoEmbed
{
    public function resolve(string $url): ?array
    {
        $parts = parse_url($url);
        if (! is_array($parts) || ! in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
            || isset($parts['user']) || isset($parts['pass']) || isset($parts['port'])) {
            return null;
        }

        $host = strtolower($parts['host'] ?? '');
        $path = $parts['path'] ?? '';
        $id = null;
        if (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'www.youtube-nocookie.com'], true)) {
            if ($path === '/watch') {
                parse_str($parts['query'] ?? '', $query);
                $id = $query['v'] ?? null;
            } elseif (preg_match('#^/(?:embed|shorts)/([a-zA-Z0-9_-]{11})/?$#', $path, $match)) {
                $id = $match[1];
            }
        } elseif (in_array($host, ['youtu.be', 'www.youtu.be'], true)) {
            $id = trim($path, '/');
        }
        if (is_string($id) && preg_match('/^[a-zA-Z0-9_-]{11}$/', $id)) {
            return ['src' => 'https://www.youtube-nocookie.com/embed/'.$id, 'title' => 'Vídeo do YouTube'];
        }
        if (in_array($host, ['vimeo.com', 'www.vimeo.com', 'player.vimeo.com'], true)
            && preg_match('#^/(?:video/)?([0-9]{1,12})/?$#', $path, $match)) {
            return ['src' => 'https://player.vimeo.com/video/'.$match[1], 'title' => 'Vídeo do Vimeo'];
        }

        return null;
    }
}
