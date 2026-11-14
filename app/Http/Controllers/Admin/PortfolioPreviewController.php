<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\FilterProjectsRequest;
use App\Support\PortfolioPage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PortfolioPreviewController extends Controller
{
    public function show(FilterProjectsRequest $request, PortfolioPage $page): Response
    {
        return $this->preview('public.portfolio', $page->data($request->user()->portfolio, $request->validated()));
    }

    public function about(Request $request): Response
    {
        return $this->preview('public.about', ['portfolio' => $request->user()->portfolio]);
    }

    public function contact(Request $request): Response
    {
        return $this->preview('public.contact', ['portfolio' => $request->user()->portfolio]);
    }

    public function project(Request $request, string $project): Response
    {
        $portfolio = $request->user()->portfolio;
        $project = $portfolio->projects()->with(['attachments', 'tags'])->findOrFail($project);

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
