# Validação local

## Base do portfólio e CRUD

- Branch: `dev`.
- Suíte inicial: 59 testes, 215 assertions, com SQLite em memória.
- Banco local conferido em 14/10/2026: MySQL, 1 usuário, 1 portfólio e 1 projeto. A contagem não comprova recuperação de dados anteriores.
- Testes de duas contas cobrem isolamento da listagem, edição, atualização, exclusão e tentativa de escolher outro proprietário.
- Build Vite e formatação Pint validados.

## Pendência visual

Não foi possível fazer a revisão em navegador: a sessão não disponibiliza navegadores conectados e as tentativas de abrir IAB e Chrome retornaram indisponibilidade. Testes HTTP/Blade não substituem conferência visual.

Quando houver navegador disponível, conferir em desktop e em largura de celular:

1. Cadastrar duas contas e verificar criação do portfólio.
2. Alterar slug, tema e cor; conferir mensagens de validação e persistência.
3. Criar, editar, destacar, ordenar e excluir projetos.
4. Confirmar paginação e menu mobile.
5. Tentar abrir a edição de um projeto da outra conta.
6. Enviar uma capa, substituir, remover e excluir o projeto; conferir a imagem por `/storage/...`.
