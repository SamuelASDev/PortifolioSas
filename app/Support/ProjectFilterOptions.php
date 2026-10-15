<?php

namespace App\Support;

use App\Models\Portfolio;

class ProjectFilterOptions
{
    public function forPortfolio(Portfolio $portfolio): array
    {
        return [
            'filterTags' => $portfolio->tags()->whereHas('projects', fn ($query) => $query->where('portfolio_id', $portfolio->id))->orderBy('name')->get(),
            'filterCategories' => array_intersect_key(config('projects.categories'), array_flip($portfolio->projects()->whereNotNull('category')->distinct()->pluck('category')->all())),
        ];
    }
}
