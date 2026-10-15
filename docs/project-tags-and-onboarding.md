# Primeiros passos, categorias, tecnologias e filtros

O dashboard usa `DashboardController` e `PortfolioOnboarding` para calcular três etapas a partir dos dados salvos da conta: apresentação (nome profissional, título e bio), primeiro projeto e publicação. Há progresso, estado concluído/pendente e atalhos para revisar cada etapa. Acesso exige login; `EnsureUserHasPortfolio` também atende contas antigas sem portfólio. Nenhuma etapa publica conteúdo automaticamente.

## Classificação dos projetos

Em Novo/Editar projeto, a categoria é opcional e vem do catálogo `config/projects.php` (site, aplicação, dashboard, landing page, e-commerce, mobile, jogo, automação e outro). Tecnologias são tags livres separadas por vírgulas, limitadas a oito por projeto e trinta caracteres por tag. Espaços e repetições sem diferença de maiúsculas/minúsculas são normalizados, preservando a primeira grafia. C++, C# e .NET são aceitos como tags diferentes.

`ProjectTagList` valida o texto; `ProjectTags` normaliza e sincroniza dentro da transação de `SaveProject`. Tags pertencem a um portfólio e têm chave única nesse escopo. A relação many-to-many usa `project_tag`, com chaves estrangeiras e exclusão em cascata. Limpar o campo remove as associações; omitir o campo numa atualização mantém as tags atuais. Tags sem projetos são removidas durante a sincronização/exclusão de projeto. A exclusão de conta elimina apenas seus registros.

O componente `project-taxonomy` mostra categoria e tecnologias no painel, nos cartões, no carrossel e no detalhe do projeto. Ícones SVG locais são definidos em `public-icon`; tecnologias conhecidas usam símbolos associados e tags livres usam o ícone de etiqueta. A referência `C:\Users\samue\Desktop\Programação\Portifolio\index.html` foi apenas lida, especificamente a apresentação das tecnologias.

Até 640 pixels de largura, as etiquetas mostram apenas os ícones, com os nomes preservados para leitores de tela e no atributo title. Em telas maiores, mostram ícone e nome.

## Filtros

`FilterProjectsRequest` valida os parâmetros GET: `q` (título, até 100 caracteres), `category`, `tag` (ID) e `featured` (`1` ou `0`). `Project::scopeFilter` centraliza a consulta e combina os filtros com AND. A busca usa parâmetros vinculados e trata %, _ e ! como caracteres literais. O formulário compartilhado funciona sem JavaScript, oferece limpar filtros e preserva os parâmetros validados na paginação.

As consultas começam sempre na relação de projetos do portfólio atual. `ProjectFilterOptions` lista somente categorias utilizadas e tags associadas aos projetos dessa conta. Um ID de tag de outra conta não retorna seus projetos nem revela sua etiqueta. A prévia privada compartilha os filtros da vitrine pública; rascunhos continuam inacessíveis nas rotas públicas. Os filtros atingem a lista de projetos abaixo do carrossel; a seleção de destaques do carrossel permanece independente. Os metadados e links de compartilhamento continuam usando a URL canônica sem parâmetros.

## Validação desta entrega

- PHP: 212 testes, 1134 assertions. Onze novos casos cobrem normalização/limites, validação, preservação/limpeza, isolamento entre contas, filtros combinados, busca literal, paginação, prévia, cascata e progresso do dashboard.
- JavaScript: 37 testes aprovados. Build Vite, Pint e git diff --check aprovados.
- Migration `2026_10_15_050000_add_project_categories_and_tags` aplicada no MySQL. Comparação de hashes confirmou a preservação dos campos anteriores em 1 usuário, 1 portfólio e 2 projetos. Projetos existentes começam sem categoria e sem tags. Uma consulta de busca literal também foi executada em leitura no MySQL.
- Conferência visual com o responsável: preencher categoria e tags num projeto existente, conferir as etiquetas no painel/carrossel/cartão/detalhe e nos temas claro/escuro; combinar e limpar filtros, navegar entre páginas e repetir na prévia. No celular, conferir quebra de linhas das etiquetas e formulário. Verificar as três etapas do dashboard, antes e depois de completar a apresentação, cadastrar projeto e publicar.
