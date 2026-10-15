# Portfólio

Aplicação Laravel para criar e publicar portfólios profissionais. Cada conta tem sua apresentação, projetos, tecnologias e aparência, com isolamento entre usuários.

## Funcionalidades

- Cadastro, login, recuperação de senha e verificação obrigatória de e-mail no painel.
- Apresentação, foto, contatos, endereço personalizado e publicação/despublicação.
- Projetos com capas, editor rico, imagens gerenciadas e vídeos de YouTube/Vimeo.
- Categorias, tags, filtros, destaques e organização por mouse, toque ou teclado.
- Temas, cores e fontes com prévia privada; metadados e ações de compartilhamento.

## Requisitos e instalação

PHP 8.2+ com PDO, SQLite/MySQL, DOM, mbstring e suporte a imagens; Composer; Node.js 22. O frontend usa Vite, Tailwind, Alpine e Tiptap.

```sh
composer install
cp .env.example .env
php artisan key:generate
```

Configure o banco e `APP_URL` em `.env`. Para SQLite, crie o arquivo `database/database.sqlite`; para MySQL, crie um banco vazio e configure suas credenciais. Depois:

```sh
php artisan migrate
php artisan storage:link
npm ci
npm run build
php artisan serve
```

No PowerShell, `Copy-Item .env.example .env` substitui `cp`. Em desenvolvimento, `npm run dev` habilita atualização do frontend. Faça backup antes de aplicar migrations em instalações existentes.

## E-mail e publicação

O dashboard e todas as rotas `/admin` exigem e-mail verificado. Perfil, logout e reenvio de verificação continuam acessíveis. A atualização do e-mail exige nova verificação. Contas existentes não são marcadas como verificadas automaticamente.

Em desenvolvimento, `MAIL_MAILER=log` grava o e-mail em `storage/logs/laravel.log`; abra o link de verificação nessa mensagem. Em produção, configure um transporte real e valide o envio antes da implantação. Não exponha logs, links de verificação ou credenciais.

## Privacidade das imagens

**Os uploads são públicos, inclusive em rascunhos.** A prévia e as páginas de rascunhos exigem autorização, mas fotos, capas, anexos e imagens de compartilhamento usam URLs públicas. Despublicar oculta as páginas, sem revogar URLs de arquivos já compartilhadas. Envie apenas imagens destinadas à divulgação. Cópias e caches externos não são revogados ao remover arquivos.

Essa política aparece junto aos uploads no painel. Um produto que precise guardar documentos confidenciais deve adotar armazenamento privado e entrega autorizada antes de receber esses arquivos.

## Tarefas agendadas e recuperação de arquivos

```sh
php artisan schedule:work
```

Em produção, execute `php artisan schedule:run` a cada minuto pelo agendador do servidor. A aplicação declara:

- `project-media:prune`: diariamente, remove uploads temporários abandonados há mais de 24 horas.
- `images:retry-deletions`: a cada cinco minutos, repete exclusões que falharam, com espera crescente até 60 minutos.

As exclusões tentadas são registradas em `pending_image_deletions` antes do acesso ao disco. Sucesso remove a pendência; falha preserva caminho, tentativas e último erro. A tabela não tem vínculo em cascata com a conta, permitindo tentar novamente após sua exclusão. O comando retorna falha se alguma exclusão tentada continuar pendente. Monitore essa tabela e os logs. O mecanismo não descobre automaticamente arquivos órfãos anteriores à sua instalação ou falhas de processo anteriores ao registro da pendência.

Se o banco estiver indisponível, a aplicação ainda tenta excluir o arquivo diretamente. Sem persistir a pendência, não é possível garantir uma nova tentativa automática.

## Validação

```sh
php artisan test --compact
npm run test:js
npm run build
php vendor/bin/pint --test
git diff --check
```

Por padrão, os testes exigem `testing` e SQLite em memória, sem usar o banco local. O GitHub Actions executa também a suíte em MySQL 8.4 isolado. `phpunit.mysql.xml` é destrutivo para o banco de teste e só é aceito com opt-in `PORTFOLIO_MYSQL_TESTS=1`, banco `portfolio_ci_test`, usuário `portfolio_test` e host `127.0.0.1`. Nunca adapte esse arquivo para um banco real.

A CI valida PHP, JavaScript, build e Pint. A execução remota só ocorre depois de enviar a branch ao GitHub.

## Organização

- `app/Actions`: operações de escrita, transações e integração com arquivos.
- `app/Http/Requests`: validação e autorização de entrada.
- `app/Policies`: isolamento de projetos.
- `app/Services`: descrições, mídia, armazenamento e exclusões pendentes.
- `app/Support`: aparência, metadados, filtros, tags e primeiros passos.
- `resources/views` e `resources/js`: painel, vitrine e interações.
- `docs/portfolio-and-projects.md`: fluxos e decisões de implementação.
- `docs/project-tags-and-onboarding.md`: categorias, filtros e dashboard.
- `docs/validation.md`: histórico de validação.
- `docs/review-2026-10-15.md`: diagnóstico que originou a rodada de consolidação.
- `docs/consolidation-2026-10-15.md`: mudanças implementadas e resultados de validação.

Antes de integrar `dev` em `main`, valide o painel em desktop/celular, os fluxos de duas contas, o e-mail real e o scheduler no ambiente de homologação.
