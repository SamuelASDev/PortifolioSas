# Validação local

## Consolidação de 15/10/2026

Os resultados mais recentes e a revisão visual administrativa estão em [consolidation-2026-10-15.md](consolidation-2026-10-15.md). As seções abaixo preservam o histórico das entregas anteriores.

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

## Editor de aparência e prévia privada

- PHP: 183 testes, 897 assertions. Os oito novos casos verificam autenticação, rascunhos, isolamento em duas contas, detalhes privados, paginação, ausência de escrita na prévia, validação atômica e persistência de personalizações.
- JavaScript: 21 testes. Os cinco novos casos cobrem normalização segura, cores/fontes, cancelar/reabrir, seleção de temas, tamanho do iframe, origem/janela das mensagens, salvamento com CSRF e recuperação após erro.
- Build e Pint aprovados. Migration `2026_10_15_030000_add_appearance_to_portfolios` aplicada no MySQL local. Comparação dos hashes confirmou a preservação dos campos anteriores em 1 usuário, 1 portfólio e 2 projetos; a nova coluna começa nula.
- Conferência visual pendente com o responsável: abrir Personalizar aparência, testar os quatro temas, cores e fontes; alternar Computador/Celular; abrir projeto e voltar à lista; cancelar e reabrir; salvar e conferir a página pública. Em celular, alternar Configurações/Prévia e conferir rolagem/foco. Repetir em um rascunho e confirmar que o link público continua inacessível.

## Organização visual e compartilhamento

- Suíte PHP completa: 199 testes, 1016 assertions. Os 16 novos testes cobrem lista completa, ordenação/destaques na vitrine e carrossel, duplicatas, IDs alheios, revisões antigas e atomicidade; metadados, fallbacks, escaping, canonical, uploads, substituição/remoção e limpeza na exclusão de conta/projeto.
- JavaScript: 28 testes. Os sete novos casos verificam botões/teclado, Pointer Events e cancelamento, payload e revisão após salvar, conflito sem perda da ordem local, texto seguro, imagem local e restauração do fallback.
- Build Vite, Pint e git diff --check aprovados. Migration `2026_10_15_040000_add_sharing_fields_to_portfolios_and_projects` aplicada ao MySQL; hashes dos campos anteriores de 1 usuário, 1 portfólio e 2 projetos permaneceram iguais. O Junction `public/storage` aponta para storage/app/public.
- Conferência visual com o responsável: arrastar pela alça no computador e celular, usar Subir/Descer e teclado, mudar destaques e salvar; conferir lista pública e carrossel. Em duas abas, salvar numa delas e confirmar que a lista antiga é recusada na outra. Em Compartilhamento, testar título/descrição/imagem, cancelar seleção e remover imagem personalizada; salvar e inspecionar as tags do HTML. Confirmar em duas contas que os projetos e imagens alheios não são alterados. Testes reais de cartões em redes sociais precisam de uma URL pública acessível; o ambiente local não é acessível aos robôs dessas plataformas.

## Botões de copiar e compartilhar

- Suíte PHP: 201 testes, 1041 assertions. Dois novos testes verificam os controles no painel e nas páginas públicas, a URL canônica e sua ausência em rascunhos/prévias privadas. Build, Pint e git diff --check aprovados.
- JavaScript: 36 testes no total. Os oito casos novos cobrem cópia da URL pública, payload do menu nativo, cancelamento, falta de suporte, falha/permissão negada, campo manual e bloqueio de cliques duplicados.
- Conferência visual com o responsável: copiar no painel e na página pública, colar e conferir o endereço; no celular, abrir/cancelar o menu nativo. Se houver bloqueio da área de transferência, conferir seleção do campo manual. O navegador decide se oferece compartilhamento nativo.
