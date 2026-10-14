<?php

namespace App\Http\Middleware;

use App\Actions\EnsurePortfolio;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasPortfolio
{
    public function handle(Request $request, Closure $next): Response
    {
        $portfolio = app(EnsurePortfolio::class)->handle($request->user());
        $request->user()->setRelation('portfolio', $portfolio);

        return $next($request);
    }
}
