<?php

namespace App\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
#[Description('Mostra os dados do portfólio (nome, apresentação, Sobre, Contato, links e publicação) e as categorias e situações aceitas nos projetos.')]
class GetPortfolioTool extends PortfolioTool
{
    public function handle(Request $request): Response
    {
        $portfolio = $this->portfolio($request);

        return Response::json([
            'professional_name' => $portfolio->professional_name,
            'headline' => $portfolio->headline,
            'bio' => $portfolio->bio,
            'about' => $portfolio->about,
            'contact_intro' => $portfolio->contact_intro,
            'location' => $portfolio->location,
            'links' => $portfolio->only(['contact_email', 'github_url', 'linkedin_url', 'website_url', 'instagram_url', 'whatsapp']),
            'is_published' => $portfolio->is_published,
            'public_url' => route('portfolio.show', $portfolio->slug),
            'projects_count' => $portfolio->projects()->count(),
            'categories' => collect(config('projects.categories'))->map(fn ($category) => $category['label'])->all(),
            'statuses' => config('projects.statuses'),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
