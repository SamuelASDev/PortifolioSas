# Validação local

## Base do portfólio e CRUD

- Branch: `dev`.
- Suíte inicial: 59 testes, 215 assertions, com SQLite em memória.
- Banco local conferido em 14/10/2026: MySQL, 1 usuário, 1 portfólio e 1 projeto. A contagem não comprova recuperação de dados anteriores.
- Testes de duas contas cobrem isolamento da listagem, edição, atualização, exclusão e tentativa de escolher outro proprietário.
- Build Vite e formatação Pint validados.

## Upload de capas

- Suíte final: 77 testes, 355 assertions, usando SQLite em memória e disco público fake para os uploads.
- Fluxo HTTP de duas contas cadastra, configura, cria projetos, alterna login/logout e confirma isolamento.
- Validados upload, substituição, remoção, exclusão do projeto/conta, limites de arquivo e dimensões, rejeição de SVG e conteúdo falso, tentativa de enviar caminhos e falhas de gravação no banco/disco.
- `php artisan storage:link` executado e destino da junction confirmado.
- Uma imagem temporária foi servida por `http://127.0.0.1:8000/storage/...`: HTTP 200, `image/png`, hash idêntico ao arquivo original. Os arquivos dessa verificação foram removidos.
- Banco MySQL conferido novamente após os testes: 1 usuário, 1 portfólio, 1 projeto, sem portfólios ou projetos órfãos.
- Build Vite e Pint passaram. A revisão visual abaixo continua pendente.

## Pendência visual

Não foi possível fazer a revisão em navegador: a sessão não disponibiliza navegadores conectados e as tentativas de abrir IAB e Chrome retornaram indisponibilidade. Testes HTTP/Blade não substituem conferência visual.

Quando houver navegador disponível, conferir em desktop e em largura de celular:

1. Cadastrar duas contas e verificar criação do portfólio.
2. Alterar slug, tema e cor; conferir mensagens de validação e persistência.
3. Criar, editar, destacar, ordenar e excluir projetos.
4. Confirmar paginação e menu mobile.
5. Tentar abrir a edição de um projeto da outra conta.
6. Enviar uma capa, substituir, remover e excluir o projeto; conferir a imagem por `/storage/...`.
