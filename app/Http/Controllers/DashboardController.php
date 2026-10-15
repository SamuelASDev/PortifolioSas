<?php

namespace App\Http\Controllers;

use App\Support\PortfolioOnboarding;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, PortfolioOnboarding $onboarding): View
    {
        $portfolio = $request->user()->portfolio;
        $steps = $onboarding->steps($portfolio);
        $portfolio->loadCount(['projects', 'projects as featured_projects_count' => fn ($query) => $query->where('is_featured', true)]);
        $recentProjects = $portfolio->projects()->orderByDesc('updated_at')->orderByDesc('id')->limit(3)->get();

        return view('dashboard', ['portfolio' => $portfolio, 'steps' => $steps,
            'recentProjects' => $recentProjects,
            'completedSteps' => count(array_filter($steps, fn ($step) => $step['complete']))]);
    }
}
