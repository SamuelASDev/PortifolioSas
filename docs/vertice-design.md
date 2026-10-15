# Vértice — identidade e interface

## Direção

Nome escolhido: **Vértice**. Assinatura: **Seu trabalho, em perspectiva.** A identidade representa o ponto de encontro entre trajetória, projetos e novas possibilidades.

O símbolo é um V geométrico construído em SVG, compartilhado pelo wordmark e pelo favicon. A configuração textual fica em `config/brand.php`; o símbolo em `components/application-logo.blade.php` e `public/favicon.svg`.

## Sistema visual

- Tipografia Manrope variável, hospedada pela aplicação, com licença OFL incluída em `resources/fonts/manrope-OFL.txt`. Nenhuma requisição a serviços externos de fonte é necessária durante o uso.
- Fundo azul quase preto, superfícies discretamente elevadas, bordas suaves e violeta como destaque principal. Verde suave identifica publicação/conclusão e vermelho discreto identifica ações destrutivas.
- Títulos com espaçamento compacto, textos auxiliares em tons secundários, controles com foco visível e campos com altura confortável no celular.
- Componentes comuns de marca, botões, campos e navegação; estilos centralizados em `resources/css/studio.css`, separados da aparência configurável dos portfólios públicos.

## Telas

- Página inicial com nova identidade, apresentação editorial, demonstração ilustrativa de portfólio e explicação das etapas.
- Login e cadastro com composição em duas colunas no desktop, elemento geométrico da marca e conteúdo em português. No celular, o formulário ganha prioridade.
- Recuperação, redefinição, confirmação de senha e verificação de e-mail seguem a mesma estrutura.
- Painel com navegação lateral no desktop, menu nativo expansível no celular, contexto da página e rodapé da marca.
- Dashboard com contagens reais e projetos recentes, sempre consultados pela relação do portfólio autenticado; primeiros passos e progresso preservados.
- Listagem de projetos com cards, capas ou placeholders, filtros e ações reorganizadas.
- Configuração do portfólio com atalhos de seção, campos em duas colunas onde houver espaço, foto e contatos reorganizados.
- Edição/criação de projetos, organização, compartilhamento, modal de aparência e conta usam as superfícies e os controles comuns.

## Validação

219 testes PHP (1.191 assertions), 39 testes JavaScript, build Vite e Pint aprovados. A fonte é empacotada pelo Vite, com nome versionado.

A conferência visual foi feita no Edge com SQLite e conta de demonstração em uma instância separada, sem editar dados da conta existente. Foram usados desktop de 1440 px e larguras de celular de 390 e 320 px; login também foi observado na janela normal. Foram conferidos navegação, dashboard, lista, apresentação, editor, aparência e autenticação. Pequenos ajustes de encaixe e tipografia foram feitos durante a inspeção.

Não houve alteração nas regras de upload, no envio de e-mail ou no comportamento do carrossel nesta entrega.

A instância de demonstração foi encerrada e seu banco e script temporários foram removidos após a conferência. As capturas de tela ficaram no armazenamento privado local. Nenhum commit, push ou merge foi executado.


## Public portfolio customization

The appearance editor now includes seven presets, the self-hosted Manrope font, and twelve composition controls: hero layout, project grid/list, content width, spacing, corner style, cover ratio, portrait shape, card treatment, hero backdrop, button treatment, sticky header, and project excerpt visibility.

`config/portfolio-appearance.php` is the shared whitelist for PHP validation, server rendering, and live preview. Settings remain in the existing `appearance` JSON column; no database migration is required. Older appearance payloads preserve newer layout fields. Presets intentionally reset layout choices to their configured defaults. Saving updates the published appearance when applicable; canceling discards the draft.

Responsive grids collapse on small screens. Reduced-motion preferences disable decorative transitions. Existing content, publication status, privacy boundaries, and carousel navigation remain intact.
