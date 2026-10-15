<div align="center">

# Vértice

**Seu trabalho, em perspectiva.**

Plataforma de portfólios profissionais com publicação, editor de projetos e identidade visual personalizável.

![PHP 8.2+](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat-square&logo=php&logoColor=white)
![Laravel 12](https://img.shields.io/badge/Laravel-12-FF2D20?style=flat-square&logo=laravel&logoColor=white)
![Tailwind CSS 3](https://img.shields.io/badge/Tailwind_CSS-3-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=flat-square&logo=mysql&logoColor=white)
![License MIT](https://img.shields.io/badge/License-MIT-334155?style=flat-square)

[Funcionalidades](#funcionalidades) · [Tecnologias](#tecnologias) · [Instalação](#instalação-local) · [Publicação](#publicação) · [Documentação](#documentação-complementar)

</div>

## Sobre o projeto

Aplicação web para criar, personalizar e publicar portfólios profissionais. O Vértice reúne apresentação pessoal, projetos e contatos em uma vitrine responsiva, com um painel para gerenciar o conteúdo sem editar código.

Cada conta possui um portfólio, com endereço próprio, projetos e identidade visual. O isolamento entre usuários ocorre na aplicação, por relacionamentos, validações e autorização, em um banco compartilhado.

## Funcionalidades

- **Conta:** cadastro com limite de tentativas, login, recuperação de senha, verificação obrigatória de e-mail no painel e exclusão da conta.
- **Apresentação:** nome profissional, título, biografia, foto, contatos e links externos.
- **Publicação:** endereço personalizado por slug, publicação/despublicação e prévia privada autenticada.
- **Projetos:** capas, links de demonstração e código, categorias, tags, busca e filtros.
- **Editor rico:** formatação, listas, links, imagens com legenda e vídeos incorporados de YouTube/Vimeo.
- **Organização:** destaques, carrossel opcional e ordenação por mouse, toque ou teclado.
- **Aparência:** temas claro/escuro, cores, tipografia e opções de layout com prévia em tempo real.
- **Compartilhamento:** título, descrição e imagem personalizados, Open Graph/Twitter, cópia de links e compartilhamento nativo quando disponível.
- **Operação:** limpeza de uploads temporários e recuperação de exclusões de imagens que falharam.

O dashboard apresenta primeiros passos para orientar a configuração. A vitrine pública inclui paginação. Não há cobrança ou planos de assinatura implementados.

## Tecnologias

| Camada | Tecnologias e finalidade |
| --- | --- |
| Backend | PHP 8.2+ e Laravel 12: rotas, autenticação, validação e serviços |
| Renderização | Blade, com páginas renderizadas no servidor |
| Interações | Alpine.js e JavaScript modular |
| Estilos | Tailwind CSS 3, CSS próprio e fonte Manrope incluída no projeto |
| Editor | Tiptap 3, baseado em ProseMirror |
| Conteúdo seguro | HTML Purifier, validação de URLs e mídias no servidor |
| Persistência | Eloquent ORM e migrations; SQLite e suíte específica para MySQL |
| Assets | Vite 7, PostCSS e Autoprefixer |
| Qualidade | PHPUnit 11, Node.js Test Runner, jsdom e Laravel Pint |
| CI | GitHub Actions com PHP/SQLite, PHP/MySQL, JavaScript e build |

Laravel Breeze fornece a base dos fluxos de autenticação. As versões efetivamente instaladas estão em `composer.lock` e `package-lock.json`.

## Requisitos

- PHP 8.2+, Composer 2 e extensões exigidas pelo Laravel, incluindo DOM, Fileinfo, Mbstring, OpenSSL, PDO e XML; driver PDO do banco escolhido e GD para imagens/testes.
- Node.js 22.22.2+ na linha 22 ou 24.15.0+ na linha 24, compatível com o lockfile atual.
- SQLite ou MySQL; a CI inclui MySQL 8.4.
- Escrita em `storage/` e `bootstrap/cache/`.

Node.js é necessário para testes e compilação. As páginas de produção são atendidas pelo PHP, com assets já compilados.

## Instalação local

```sh
git clone https://github.com/SamuelASDev/PortifolioSas.git
cd PortifolioSas
git switch dev
composer install
cp .env.example .env
```

No PowerShell, use `Copy-Item .env.example .env`. Configure `APP_NAME="Vértice"`, `APP_URL` e o banco no `.env`. Para SQLite, crie o arquivo vazio `database/database.sqlite` se necessário. Para MySQL, crie um banco e preencha as variáveis `DB_*` correspondentes.

```sh
php artisan key:generate
php artisan migrate
php artisan storage:link
npm ci
npm run build
php artisan serve
```

Abra o endereço informado pelo Artisan, cadastre uma conta e confirme o e-mail. Para atualizar o frontend automaticamente, execute `npm run dev` em outro terminal.

`composer run dev` inicia servidor, listener de fila, Pail e Vite juntos. O Pail depende de PCNTL; no Windows nativo, prefira `php artisan serve` e `npm run dev` em terminais separados.

`composer run setup` instala dependências, gera chave, aplica migrations e compila assets. Use apenas em instalação nova após configurar o ambiente; ele não é um procedimento de atualização de produção.

## E-mail e acesso

Dashboard e rotas `/admin` exigem e-mail verificado. Perfil, logout e reenvio da confirmação continuam acessíveis. Trocar o e-mail exige nova verificação; contas existentes não são verificadas automaticamente.

Em desenvolvimento, `MAIL_MAILER=log` grava a mensagem em `storage/logs/laravel.log`; abra o link de confirmação registrado ali. Em produção, configure SMTP e teste confirmação de conta e recuperação de senha. Não publique logs, links de autenticação ou credenciais.

## Rotas principais

| Endereço | Finalidade |
| --- | --- |
| `/` | Apresentação do produto |
| `/register` e `/login` | Cadastro e autenticação |
| `/dashboard` | Visão geral e primeiros passos |
| `/admin/portfolio` | Apresentação, aparência e publicação |
| `/admin/projects` | Gerenciamento de projetos |
| `/admin/projects/organize` | Ordenação e destaques |
| `/{slug}` | Portfólio publicado |
| `/{slug}/projects/{project}` | Detalhes de projeto |
| `/up` | Verificação básica de inicialização |

Consulte todas as rotas com `php artisan route:list`. `/up` não certifica SMTP, cron ou todos os serviços.

## Uploads e privacidade

**Os uploads são públicos, inclusive em rascunhos.** A prévia exige autorização, mas fotos, capas, anexos e imagens de compartilhamento usam URLs públicas. Despublicar oculta páginas e não revoga URLs diretas ou cópias em caches externos.

- JPEG, PNG e WebP, até 10 MB por arquivo.
- Foto: até 3000 × 3000 pixels; capas e imagens de compartilhamento: até 6000 × 6000.
- Anexos da descrição: até 20 imagens por projeto, 100 arquivos e 100 MB por portfólio.
- Limites em `config/portfolio.php`; a quota de anexos não cobre todos os tipos de upload da conta.

Configure também os limites do PHP e do servidor HTTP. Conteúdo confidencial exige armazenamento privado e entrega autorizada antes de ser recebido pelo aplicativo.

## Tarefas agendadas

No desenvolvimento, quando necessário, execute `php artisan schedule:work`. Em produção, configure `php artisan schedule:run` a cada minuto no cron.

| Comando | Frequência | Responsabilidade |
| --- | --- | --- |
| `project-media:prune` | Diária | Remover uploads temporários abandonados há mais de 24 horas |
| `images:retry-deletions` | A cada 5 minutos | Repetir exclusões de imagens que falharam |

As exclusões são registradas em `pending_image_deletions`. Sucesso remove a pendência; falha preserva caminho, tentativas e erro, com espera crescente até 60 minutos. A tabela é independente da conta, permitindo limpeza após sua exclusão. Monitore pendências e logs; o comando retorna falha se uma exclusão tentada continuar pendente.

Se o banco estiver indisponível, a aplicação ainda tenta excluir diretamente, mas sem registro persistido não há garantia de nova tentativa. A rotina não descobre arquivos órfãos antigos nem substitui backup.

## Testes e integração contínua

```sh
php vendor/bin/phpunit --configuration phpunit.xml
npm run test:js
npm run build
php vendor/bin/pint --test
composer validate --strict
git diff --check
```

Os testes padrão usam `testing` e SQLite em memória. A suíte recusa o banco normal da aplicação. `phpunit.mysql.xml` exige opt-in `PORTFOLIO_MYSQL_TESTS=1`, host `127.0.0.1`, banco `portfolio_ci_test` e usuário `portfolio_test`. Esse banco é descartável: nunca adapte a configuração para dados reais.

O workflow [Validate](.github/workflows/validate.yml) é executado **manualmente**, com `workflow_dispatch`. Não há gatilhos automáticos por push ou pull request. Depois de integrar o workflow à branch padrão do GitHub, use **Actions → Validate → Run workflow** e selecione a branch desejada. A matriz verifica PHP 8.2/8.3 com SQLite e PHP 8.3 com MySQL 8.4; o frontend é compilado uma única vez e compartilhado como artefato entre os jobs.

A CI valida o código; não publica o aplicativo na Hostinger.

## Publicação

- [Guia de publicação na Hostinger](docs/deploy-hostinger.md): ambiente, diretório público, build, banco, SMTP, cron e backups.
- [Diagnóstico de preparação](docs/pre-deploy-review-2026-10-15.md): histórico Git, melhorias priorizadas e validação desta revisão.

O deploy deve utilizar uma versão revisada, com assets de produção, banco configurado, SMTP funcional e tarefas agendadas. Credenciais ficam exclusivamente no `.env` do servidor. Consulte o guia antes da primeira instalação e preserve banco, uploads e chave da aplicação nas atualizações.

## Organização do código

| Diretório | Responsabilidade |
| --- | --- |
| `app/Actions` | Operações de escrita e transações |
| `app/Http` | Controllers, validações e middleware |
| `app/Models` | Entidades e relacionamentos |
| `app/Policies` | Autorização de projetos |
| `app/Services` | Conteúdo rico, mídia e armazenamento |
| `app/Support` | Aparência, metadados, filtros e primeiros passos |
| `app/Console/Commands` | Limpeza e recuperação de exclusões |
| `database/migrations` | Evolução do banco |
| `resources/views`, `resources/js`, `resources/css` | Interface e interações |
| `tests` | Suítes PHP e JavaScript |
| `.github/workflows` | Integração contínua |

## Documentação complementar

- [Portfólios e projetos](docs/portfolio-and-projects.md)
- [Categorias, tags e primeiros passos](docs/project-tags-and-onboarding.md)
- [Identidade visual e componentes](docs/vertice-design.md)
- [Histórico de validação](docs/validation.md)
- [Revisão anterior](docs/review-2026-10-15.md)
- [Consolidação e limitações conhecidas](docs/consolidation-2026-10-15.md)

## Autoria e licenciamento

Desenvolvido por [SamuelASDev](https://github.com/SamuelASDev). As dependências mantêm suas licenças; a licença da fonte Manrope está em `resources/fonts/manrope-OFL.txt`. Aplicativo distribuído sob a [licença MIT](LICENSE).
