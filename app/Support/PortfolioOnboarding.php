<?php

namespace App\Support;

use App\Models\Portfolio;

class PortfolioOnboarding
{
    public function steps(Portfolio $portfolio): array
    {
        $hasProjects = $portfolio->projects()->exists();

        return [
            ['title' => 'Complete sua apresentação', 'description' => 'Defina seu nome profissional, título e biografia curta.',
                'complete' => filled($portfolio->professional_name) && filled($portfolio->headline) && filled($portfolio->bio),
                'url' => route('admin.portfolio.edit').'#presentation-heading', 'icon' => 'profile'],
            ['title' => 'Cadastre seu primeiro projeto', 'description' => 'Conte o que você construiu e adicione tecnologias e imagens.',
                'complete' => $hasProjects, 'url' => route($hasProjects ? 'admin.projects.index' : 'admin.projects.create'), 'icon' => 'code'],
            ['title' => 'Publique seu portfólio', 'description' => 'Confira a prévia e publique quando estiver pronto para compartilhar.',
                'complete' => $portfolio->is_published, 'url' => route('admin.portfolio.edit').'#publication-heading', 'icon' => 'globe'],
        ];
    }
}
