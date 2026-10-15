<?php

namespace App\Support;

use App\Models\Portfolio;
use App\Models\Project;
use Illuminate\Support\Str;

class ProjectTags
{
    public function parse(?string $text): array
    {
        $tags = [];
        foreach (explode(',', $text ?? '') as $name) {
            $name = Str::squish($name);
            if ($name !== '') {
                $tags[Str::lower($name)] ??= $name;
            }
        }

        return $tags;
    }

    public function sync(Portfolio $portfolio, Project $project, ?string $text): void
    {
        $ids = [];
        foreach ($this->parse($text) as $key => $name) {
            $ids[] = $portfolio->tags()->firstOrCreate(['key' => $key], ['name' => $name])->id;
        }
        $project->tags()->sync($ids);
        $portfolio->tags()->doesntHave('projects')->delete();
    }

    public function icon(string $name): string
    {
        $key = Str::lower($name);
        if (preg_match('/^php(?:\s*\d+(?:\.\d+)*)?$/', $key)) {
            return 'php';
        }

        return config('projects.tag_icons.'.$key, 'tag');
    }
}
