<?php

namespace App\Support;

use App\Models\Portfolio;

class PortfolioPage
{
    public function data(Portfolio $portfolio, array $filters = []): array
    {
        return [
            'portfolio' => $portfolio,
            'filters' => $filters,
            'projectCount' => $portfolio->projects()->count(),
            'projects' => $portfolio->projects()->with('tags')->filter($filters)->orderBy('display_order')->orderBy('id')->paginate(12)->appends($filters),
            'featuredProjects' => $portfolio->show_featured_carousel
                ? $portfolio->projects()->with('tags')->where('is_featured', true)->orderBy('display_order')->orderBy('id')->limit(config('portfolio.carousel.max_projects'))->get()
                : collect(),
        ] + app(ProjectFilterOptions::class)->forPortfolio($portfolio);
    }
}
