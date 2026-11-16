<?php

namespace App\Mcp\Tools;

use App\Actions\SavePortfolio;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;

#[IsIdempotent]
#[Description('Atualiza os textos do portfólio: nome, título profissional, bio curta, página Sobre, mensagem da página Contato e localização. Envie só o que deve mudar.')]
class UpdateProfileTool extends PortfolioTool
{
    public function handle(Request $request, SavePortfolio $save): Response
    {
        $data = $request->validate([
            'professional_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'headline' => ['sometimes', 'nullable', 'string', 'max:160'],
            'bio' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'about' => ['sometimes', 'nullable', 'string', 'max:10000'],
            'contact_intro' => ['sometimes', 'nullable', 'string', 'max:600'],
            'location' => ['sometimes', 'nullable', 'string', 'max:120'],
        ]);
        if ($data === []) {
            return Response::error('Informe ao menos um campo para atualizar.');
        }

        $save->handle($this->portfolio($request), $data);

        return Response::text('Portfólio atualizado: '.implode(', ', array_keys($data)).'.');
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'professional_name' => $schema->string()->description('Nome exibido no portfólio.'),
            'headline' => $schema->string()->description('Título profissional curto (até 160 caracteres).'),
            'bio' => $schema->string()->description('Bio curta da página inicial (até 1000 caracteres).'),
            'about' => $schema->string()->description('Texto da página Sobre (até 10000). Linhas em branco separam parágrafos; o primeiro ganha destaque.'),
            'contact_intro' => $schema->string()->description('Mensagem da página Contato (até 600 caracteres).'),
            'location' => $schema->string()->description('Cidade/região, ex.: "Recife, PE".'),
        ];
    }
}
