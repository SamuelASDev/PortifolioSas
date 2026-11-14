<?php

namespace App\Support;

use Illuminate\Support\Str;

class TechIcons
{
    private ?array $icons = null;

    public function all(): array
    {
        return $this->icons ??= json_decode(file_get_contents(resource_path('data/tech-icons.json')), true) ?? [];
    }

    public function find(string $slug): ?array
    {
        return $this->all()[$slug] ?? null;
    }

    /** Converte o nome digitado (ex.: "Node.js", "C++", "PHP 8") no slug do Simple Icons. */
    public function slugFor(string $name): ?string
    {
        $key = Str::lower(Str::squish($name));
        $candidates = [$key, preg_replace('/\s*v?\d+(?:\.\d+)*$/', '', $key)];
        foreach ($candidates as $candidate) {
            $alias = config('projects.tag_aliases.'.$candidate);
            $slug = $alias ?? preg_replace('/[^a-z0-9]/', '', strtr($candidate, ['+' => 'plus', '.' => 'dot', '&' => 'and', '#' => 'sharp']));
            if ($slug !== '' && $this->find($slug)) {
                return $slug;
            }
        }

        return null;
    }
}
