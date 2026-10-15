<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterProjectsRequest;
use App\Models\Portfolio;
use App\Support\PortfolioPage;
use Illuminate\Http\Response;

class PublicPortfolioController extends Controller
{
    public function show(FilterProjectsRequest $request, string $slug, PortfolioPage $page): Response
    {
        $portfolio = Portfolio::published()->where('slug', $slug)->firstOrFail();

        return response()->view('public.portfolio', $page->data($portfolio, $request->validated()))
            ->header('Cache-Control', 'no-store');
    }

    public function project(string $slug, string $project): Response
    {
        $portfolio = Portfolio::published()->where('slug', $slug)->firstOrFail();
        $project = $portfolio->projects()->with(['attachments', 'tags'])->findOrFail($project);

        return response()->view('public.project', compact('portfolio', 'project'))
            ->header('Cache-Control', 'no-store');
    }
}
