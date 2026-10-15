<?php

namespace App\Support;

use App\Models\Portfolio;
use App\Models\Project;
use App\Services\SharingImageStorage;
use Illuminate\Support\Str;

class SharingMetadata
{
    public function __construct(private SharingImageStorage $images) {}

    public function forPortfolio(Portfolio $portfolio): array
    {
        return $this->resolve($portfolio);
    }

    public function forProject(Portfolio $portfolio, Project $project): array
    {
        return $this->resolve($portfolio, $project);
    }

    private function resolve(Portfolio $portfolio, ?Project $project = null): array
    {
        $record = $project ?? $portfolio;
        $fallbackTitle = $project ? $project->title.' — '.$portfolio->professional_name : $portfolio->professional_name.' — Portfólio';
        $fallbackDescription = Str::limit(Str::squish(($project ? $project->description_text : $portfolio->bio) ?: 'Conheça meus projetos e minha trajetória profissional.'), 160);
        $fallbackImage = $project
            ? ($project->cover_url ?? $this->images->url($portfolio->share_image_path) ?? $portfolio->photo_url)
            : $portfolio->photo_url;

        return [
            'title' => $record->share_title ?: $fallbackTitle,
            'description' => $record->share_description ?: $fallbackDescription,
            'image' => $this->images->url($record->share_image_path) ?? $fallbackImage,
            'url' => $project ? route('portfolio.projects.show', [$portfolio->slug, $project->id]) : route('portfolio.show', $portfolio->slug),
            'fallback_title' => $fallbackTitle, 'fallback_description' => $fallbackDescription, 'fallback_image' => $fallbackImage,
        ];
    }
}
