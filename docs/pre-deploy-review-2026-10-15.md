# Revisão de preparação para publicação — 15/10/2026

## Escopo e conclusão

Revisão da estrutura, dependências, rotas, autenticação, autorização, persistência, conteúdo rico, arquivos, configuração de CI, histórico Git e documentação existente. Foram executados testes e build locais. Não houve acesso ao hPanel, alteração do banco da aplicação, deploy, commit, push ou merge. Esta rodada não incluiu nova inspeção visual no navegador nem certifica o ambiente de produção.

O aplicativo já possui uma base consistente para homologação: separação de responsabilidades, autorização por portfólio, validação de entrada, sanitização do editor, paginação, testes de múltiplas contas e recuperação de exclusões. A publicação ainda depende da configuração e validação do ambiente real, principalmente SMTP, MySQL, cron, uploads e raiz HTTP.

## Git e evolução

- Branch ativa: `dev`, commit `88fd5c2`; árvore limpa antes desta revisão.
- `main` local: `9517e5a`, ainda na criação inicial de models/migrations.
- `dev` está 17 commits à frente da `main` local.
- As referências locais indicam alinhamento de ambas com seus respectivos upstreams; não foi feito fetch, então isso não prova o estado remoto atual.
- O histórico mostra evolução de CRUD e isolamento para capas, publicação, editor rico, mídias, carrossel, aparência, compartilhamento, categorias/tags, CI e identidade visual.
- As últimas mensagens `refatoracao de algumas funcoes` descrevem pouco o conteúdo: `57b617c` inclui CI, segurança operacional e recuperação de exclusões; `88fd5c2` inclui uma grande atualização visual. Nos próximos commits, prefira mensagens que nomeiem essas entregas.
- `.env.example` é o único `.env` rastreado; não foram encontrados banco SQLite ou `public/hot` rastreados. Isso não representa auditoria completa de segredos em todo o histórico.

Antes de publicar a `main`, integre a versão revisada de `dev`. O repositório não contém um workflow de deploy.

## Por que o Validate aparece ao iniciar o servidor?

O arquivo `.github/workflows/validate.yml`, adicionado no commit `57b617c`, declara:

```yaml
on:
  push:
  pull_request:
```

Isso dispara CI em push para qualquer branch e nos eventos padrão de PR, incluindo atualização de commits. Nenhum script de `composer.json` ou `package.json` inspecionado chama GitHub Actions ou executa push ao iniciar o servidor. Os hooks locais encontrados são apenas arquivos `.sample`, e não foi identificada configuração `core.hooksPath`.

`composer run dev` inicia Artisan serve, listener de fila, Pail e Vite. `npm run dev` inicia Vite. Nenhum desses comandos, por si só, é um evento do GitHub. A coincidência de horário não comprova causalidade.

Para identificar uma execução específica, abra Actions → Validate e confira evento, branch, SHA e horário. Compare o SHA com `git log`. Pode ser um push feito junto ao início do trabalho, sincronização por outra ferramenta ou uma execução anterior ainda em andamento. Sem consultar essa execução remota não é possível atribuir qual dessas situações ocorreu.

Com um PR aberto, o mesmo envio pode gerar uma execução por push e outra por PR. Isso é compatível com os gatilhos atuais. [Referência dos eventos do GitHub](https://docs.github.com/en/actions/reference/workflows-and-actions/events-that-trigger-workflows).

Recomendações para otimizar a CI, preservadas como propostas nesta revisão:

1. Adicionar `concurrency` para cancelar execuções antigas do mesmo PR/branch.
2. Avaliar push somente em `main` e `dev`, mantendo PRs, caso branches de trabalho gerem muito ruído.
3. Evitar repetir build e Pint em toda a matriz de banco: atualmente há três builds por evento, e Pint roda duas vezes.
4. Evitar iniciar MySQL no job SQLite, que atualmente também recebe o serviço.
5. Validar PHP 8.2 na matriz se essa continuar sendo a versão mínima suportada; hoje a CI usa apenas PHP 8.3.

O workflow foi preservado: a revisão não desativou verificações nem alterou a política de branches.

## Prioridades antes da publicação

| Prioridade | Evidência e impacto | Ação recomendada |
| --- | --- | --- |
| Alta | `.env.example` usa ambiente local, debug ativo e e-mail em log; o painel exige confirmação | Configurar ambiente de produção e SMTP; testar cadastro, verificação e recuperação |
| Alta | `public/build` é ignorado pelo Git | Gerar assets no pipeline/servidor ou incluí-los no pacote do release |
| Alta | Laravel espera entrada em `public/index.php` | Manter a raiz da aplicação fora da exposição HTTP e conferir regras de reescrita |
| Alta | Limpeza e recuperação dependem de `routes/console.php` | Configurar cron a cada minuto e acompanhar execuções reais |
| Alta | Os testes locais executados foram SQLite; a CI contém suíte MySQL | Confirmar execução verde em MySQL e homologar com o banco escolhido |
| Alta | Banco e uploads são persistentes e não fazem parte do release Git | Criar backup conjunto com chave de aplicação e testar restauração |
| Média | POST de cadastro não possui throttle explícito; contas não verificadas também persistem no banco | Adicionar limitação de cadastro e avaliar proteção contra automação conforme uso |
| Média | Upload de anexos possui throttle e quota, mas fotos/capas/compartilhamento não têm quota total equivalente | Definir limites globais por conta e acompanhar consumo de disco antes de abrir cadastro ao público |
| Média | Uploads são públicos mesmo em rascunho | Confirmar que essa política atende ao produto; armazenamento privado exige mudança de implementação |
| Média | `ProjectDescription` configura `Cache.DefinitionImpl=null`; páginas públicas usam `Cache-Control: no-store` | Medir custo de sanitização e respostas antes de otimizar; qualquer cache deve invalidar ao editar/despublicar |
| Média | Não há geração de miniaturas nos serviços de armazenamento inspecionados; imagens podem chegar a 10 MB | Criar variantes menores e avaliar reprocessamento de imagens para reduzir tráfego, memória e metadados preservados |
| Baixa | `composer.json` mantém nome e descrição do skeleton Laravel | Atualizar metadados do pacote em uma mudança própria, mantendo lock consistente |
| Baixa | MIT declarada sem arquivo LICENSE próprio | Definir titular/ano e formalizar licença conforme intenção de distribuição |

O reset de senha utiliza a proteção do broker do Laravel, e o login tem rate limiting próprio. A recomendação de cadastro acima se refere à ausência de um limite explícito nessa rota; não implica ausência de toda proteção na autenticação.

## Melhorias após a primeira homologação

- Centralizar procedimento de release e recuperação, registrando SHA e resultado dos checks.
- Monitorar erros HTTP, falhas SMTP, espaço em disco e `pending_image_deletions`.
- Acrescentar sitemap de portfólios publicados e revisar indexação conforme a estratégia do produto; canonical e metadados sociais já existem.
- Medir desempenho com dados representativos antes de criar caches adicionais ou refatorações amplas.
- Rever dependências redundantes: há `@tailwindcss/vite` 4 instalado, mas o Vite usa plugin Laravel e os estilos usam Tailwind 3 via PostCSS. Remover dependência sem uso deve vir acompanhada de build e atualização do lockfile.
- Ampliar a política de senha se o produto exigir regras além do padrão de registro do Breeze.

## Validação desta rodada

Ambiente local: Windows, PHP 8.2.12 e Node.js 24.20.0.

| Verificação | Resultado |
| --- | --- |
| PHPUnit com `phpunit.xml` | 220 testes, 1.244 assertions, aprovados em SQLite em memória |
| `npm run test:js` | 40 testes aprovados |
| `npm run build` | Aprovado, assets de produção gerados |
| `php vendor/bin/pint --test` | Aprovado |
| `composer validate --strict` | Aprovado |
| `composer audit --locked --no-interaction` | Nenhum advisory conhecido encontrado |
| `npm audit --omit=dev` | Nenhuma vulnerabilidade conhecida nas dependências de produção |
| `git diff --check` | Aprovado ao concluir a documentação |

As auditorias dependem dos advisories disponíveis no momento e não substituem revisão de segurança. A auditoria npm acima exclui dependências de desenvolvimento.

Não foram executados nesta rodada: MySQL local, Actions remoto, SMTP real, cron da Hostinger, restauração de backup ou testes de interface em navegador. Os registros de verificações visuais anteriores estão em `docs/consolidation-2026-10-15.md` e não devem ser confundidos com validação nova.

## Entregas

README reescrito com visão do produto, tecnologias, instalação, arquitetura, limites e operação. Guia `docs/deploy-hostinger.md` criado com sequência de implantação e conferência. Código funcional e workflow preservados.

## Atualização após autorização de implementação

Esta seção substitui o status de propostas descrito acima nos itens implementados:

- Validate alterado para execução exclusivamente manual (`workflow_dispatch`), conforme solicitação. Para o botão Run workflow aparecer, o arquivo precisa estar integrado à branch padrão do repositório.
- CI com cancelamento de execuções anteriores da mesma referência, frontend compilado uma vez e compartilhado como artefato, Pint isolado e MySQL iniciado somente no job que o utiliza.
- Matriz PHP expandida para SQLite em PHP 8.2/8.3 e MySQL em PHP 8.3.
- Cadastro limitado a cinco tentativas por minuto por IP, incluindo entradas inválidas, com teste de bloqueio e independência entre IPs.
- Metadados Composer atualizados para o Vértice; lockfile sincronizado sem atualização de pacotes Composer.
- Removido `@tailwindcss/vite` 4, não utilizado pela configuração Tailwind 3/PostCSS atual.
- Arquivo MIT LICENSE adicionado em conformidade com a licença já declarada pelo projeto.
- README com apresentação para GitHub, badges estáticos, navegação e operação manual da CI.
- `public/.user.ini` adiciona limites de upload para ambientes PHP CGI/FPM; valores efetivos dependem do servidor/plano.
- Arquivo de produção criado em `C:/Users/samue/Desktop/Vertice-Hostinger/.env`, fora do repositório, com os dados fornecidos, chave exclusiva para a instalação nova, debug desativado e cookies seguros. O domínio e SMTP permanecem identificados para preenchimento.
- Teste remoto limitado a abertura de conexão e intenção de `SELECT 1`: autenticação recusada com código 1045. Nenhuma migration ou alteração de dados foi executada. Confirmar credenciais e permissões no hPanel; conectividade de rede não implica autenticação válida.
- `.env` local preservado; o hash SHA-256 foi comparado antes/depois, sem mudança.

Validação após as mudanças: PHPUnit com 221 testes e 1.252 assertions, 40 testes JavaScript, build Vite, Pint, validação estrita do Composer e diff check aprovados. A CI modificada não foi executada no GitHub nesta rodada.

A auditoria npm completa identificou sete entradas de vulnerabilidade (cinco altas e duas moderadas), na cadeia de ferramentas de desenvolvimento do Tailwind 3: braces, chokidar, fast-glob, micromatch, postcss-nested, postcss-selector-parser e tailwindcss. Isso não representa sete falhas independentes no aplicativo. O npm propõe migração maior para Tailwind 4; ela exige adaptação e conferência visual própria e não foi aplicada automaticamente. A auditoria npm de produção e a auditoria Composer seguem separadas desses avisos.

Continuam pendentes de ambiente real: domínio, SMTP, autenticação MySQL válida, deploy, cron e restauração de backup. Quota global, variantes de imagens, medição de desempenho e sitemap permanecem melhorias de evolução, sem implementação nesta rodada. A política atual de uploads públicos foi preservada; o aplicativo não foi convertido em armazenamento privado.
