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

## Apresentação e vitrine pública

- Suíte: 111 testes, 527 assertions. Os 34 novos testes verificam apresentação, foto, publicação, páginas públicas, isolamento, HTML escapado, tema/cor e paginação.
- Migration incremental aplicada no MySQL local. Snapshot privado criado antes em `storage/app/private/backups/before-presentation-20261014-233238.json` (ignorado pelo Git).
- Comparação posterior: os campos anteriores do usuário, portfólio e projeto foram preservados; as contagens seguem 1/1/1. O portfólio existente permanece como rascunho.
- A publicação e a despublicação foram verificadas em testes HTTP com SQLite, sem publicar a conta local automaticamente.
- A revisão visual ficou com o usuário, conforme combinado. Roteiro sugerido:

1. Em **Portfólio**, preencher nome, título, biografia, contatos e foto; salvar e conferir os valores após recarregar.
2. Publicar e abrir o link mostrado no painel, tanto no tema claro quanto no escuro.
3. Conferir desktop e larguras de 320, 390, 768 e 1280 px: navegação, quebra de nomes/URLs longos, retrato, cards e botões.
4. Abrir um projeto e conferir descrição completa, capa, links externos e retorno para a listagem.
5. Despublicar e recarregar os dois links em janela anônima: ambos devem retornar 404.
6. Repetir com outra conta e conferir que a combinação do slug de uma com o ID de projeto da outra retorna 404.

## Editor de descrição

- Suíte PHP: 138 testes, 626 assertions. Os 27 novos testes cobrem formatação, sanitização de scripts/atributos/URIs, leitura defensiva do banco, textos legados, resumos, metadados, validação e isolamento.
- Testes JavaScript: 7 testes com Node/JSDOM verificam a importação literal do texto antigo, marcação existente, sincronização do formulário, negrito, links, limite de tamanho e conteúdo vazio. Esses testes de documento/eventos não substituem revisão visual.
- Migration incremental aplicada no MySQL local, com snapshot privado anterior em `storage/app/private/backups/before-richtext-20261014-235024.json`. Campos anteriores e contagens 1/1/1 preservados; o projeto existente recebeu formato `text`.
- Roteiro visual para o responsável: abrir uma descrição antiga, conferir texto e quebras de linha; aplicar títulos/listas/citações/links/código; salvar e reabrir; conferir a página pública e os resumos; provocar erro de validação em outro campo e conferir que o texto continua disponível. No celular, verificar quebra da barra de ferramentas e uso do painel de links.

## Mídia no corpo da descrição

- Suíte PHP: 169 testes, 766 assertions; JavaScript: 11 testes. Incluem vinculação ao criar/salvar, preservação após falhas, exclusão após confirmação, uploads de outras abas, limites, isolamento entre contas/projetos e limpeza de temporários.
- Verificados players canônicos, rejeição de hosts e atributos perigosos, inserção no cursor, legendas, persistência dos nós no editor e bloqueio de envio enquanto o upload está em andamento.
- Migration `create_project_media_table` aplicada no MySQL local. As contagens e hashes completos de `users`, `portfolios` e `projects` foram comparados antes/depois e permaneceram iguais (1/1/1). A tabela nova começou vazia.
- `public/storage` continua conectado a `storage/app/public`. A tarefa diária `project-media:prune` aparece em `php artisan schedule:list`.
- Revisão visual com o responsável: inserir uma imagem entre dois parágrafos e um vídeo por link; salvar/reabrir; conferir a página pública no celular; excluir um bloco e salvar; confirmar que uma edição cancelada não apaga a imagem já salva. Testar um vídeo público com incorporação permitida pelo provedor.

## Carrossel opcional e Gmail

- Referência visual consultada: `C:\Users\samue\Desktop\Programação\Portifolio\index.html`. O arquivo de referência foi apenas lido.
- Suíte PHP: 175 testes, 803 assertions. JavaScript: 16 testes, incluindo navegação, autoplay, pausa, teclado, scroll, resize, movimento reduzido e limpeza de timers.
- Migration aplicada no MySQL. Hashes dos campos anteriores de usuários, portfólios e projetos permaneceram iguais antes/depois; a nova configuração começa desativada.
- Build Vite e Pint passaram. O novo módulo público é carregado apenas quando há mais de um slide.
- Revisão visual com o responsável: ativar a opção no portfólio e marcar dois projetos como destaque; conferir posição acima da lista, capa, links, temas e layout no celular; navegar e pausar a rotação; desativar a opção e confirmar que a seção desaparece. Clicar em E-mail deve abrir o Gmail com o destinatário configurado, sem enviar a mensagem.
