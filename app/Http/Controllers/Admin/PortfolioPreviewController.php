<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\PortfolioPage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PortfolioPreviewController extends Controller
{
    public function show(Request $request, PortfolioPage $page): Response
    {
        return $this->preview('public.portfolio', $page->data($request->user()->portfolio));
    }

    public function project(Request $request, string $project): Response
    {
        $portfolio = $request->user()->portfolio;
        $project = $portfolio->projects()->with('attachments')->findOrFail($project);

        return $this->preview('public.project', compact('portfolio', 'project'));
    }

    private function preview(string $view, array $data): Response
    {
        return response()->view($view, $data + ['preview' => true])
            ->header('Cache-Control', 'private, no-store')
            ->header('X-Robots-Tag', 'noindex, nofollow')
            ->header('X-Frame-Options', 'SAMEORIGIN')
            ->header('Content-Security-Policy', "frame-ancestors 'self'");
    }
}
