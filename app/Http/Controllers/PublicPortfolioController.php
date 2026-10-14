<?php

namespace App\Http\Controllers;

use App\Models\Portfolio;
use Illuminate\Http\Response;

class PublicPortfolioController extends Controller
{
    public function show(string $slug): Response
    {
        $portfolio = Portfolio::published()->where('slug', $slug)->firstOrFail();
        $projects = $portfolio->projects()->orderBy('display_order')->orderBy('id')->paginate(12);

        return response()->view('public.portfolio', compact('portfolio', 'projects'))
            ->header('Cache-Control', 'no-store');
    }

    public function project(string $slug, string $project): Response
    {
        $portfolio = Portfolio::published()->where('slug', $slug)->firstOrFail();
        $project = $portfolio->projects()->findOrFail($project);

        return response()->view('public.project', compact('portfolio', 'project'))
            ->header('Cache-Control', 'no-store');
    }
}
