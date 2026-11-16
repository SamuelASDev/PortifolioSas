<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\CreateProjectTool;
use App\Mcp\Tools\GetPortfolioTool;
use App\Mcp\Tools\GetProjectTool;
use App\Mcp\Tools\ListProjectsTool;
use App\Mcp\Tools\UpdateProfileTool;
use App\Mcp\Tools\UpdateProjectTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Vértice')]
#[Version('1.0.0')]
#[Instructions('Gerencia o portfólio do usuário no Vértice. Comece por get-portfolio-tool para entender o perfil e as categorias aceitas. Escreva em português do Brasil, com tom profissional e sem inventar fatos: pergunte ao usuário o que não souber. Antes de editar, leia o projeto com get-project-tool e preserve o que o usuário não pediu para mudar.')]
class VerticeServer extends Server
{
    protected array $tools = [
        GetPortfolioTool::class,
        ListProjectsTool::class,
        GetProjectTool::class,
        CreateProjectTool::class,
        UpdateProjectTool::class,
        UpdateProfileTool::class,
    ];
}
