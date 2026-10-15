<?php

namespace App\Support;

use App\Models\Portfolio;

class PortfolioPage
{
    public function data(Portfolio $portfolio): array
    {
        return [
            'portfolio' => $portfolio,
            'projects' => $portfolio->projects()->orderBy('display_order')->orderBy('id')->paginate(12),
            'featuredProjects' => $portfolio->show_featured_carousel
                ? $portfolio->projects()->where('is_featured', true)->orderBy('display_order')->orderBy('id')->limit(config('portfolio.carousel.max_projects'))->get()
                : collect(),
        ];
    }
}
