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

        return view('dashboard', ['portfolio' => $portfolio, 'steps' => $steps,
            'completedSteps' => count(array_filter($steps, fn ($step) => $step['complete']))]);
    }
}
