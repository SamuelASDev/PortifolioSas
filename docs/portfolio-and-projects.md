# Portfólios e projetos

## Desenvolvimento local

```sh
php artisan migrate
php artisan storage:link
npm install
npm run build
php artisan serve
```

O banco local deve estar configurado no `.env`. A migration incremental adiciona unicidade a `portfolios.user_id`; se já houver duplicações, ela interrompe a execução sem excluir registros. Resolva os conflitos antes de executá-la novamente.

## Organização

- `Actions/EnsurePortfolio`: garante um portfólio por usuário. No cadastro, participa da transação de criação da conta. Gera um slug inicial com nome e UUID; o usuário pode personalizá-lo depois.
- `Middleware/EnsureUserHasPortfolio`: atende contas antigas sem portfólio ao acessar `/admin`, inclusive em requisições de gravação diretas. A criação usa bloqueio do usuário e restrição única no banco.
- `Requests/Admin`: normalização e validação da entrada. A lista de slugs reservados fica em `config/portfolio.php`.
- `Policies/ProjectPolicy`: autorização por proprietário. As consultas de listagem e criação usam a relação do usuário autenticado; IDs enviados no formulário não escolhem o proprietário.
- `Controllers/Admin`: coordenação das operações e respostas HTTP.
- `Actions/SaveProject`, `DeleteProject` e `DeleteUserAccount`: transações e ciclo de vida das capas.
- `Services/ProjectCoverStorage`: disco, diretório, nomes gerados, URLs e exclusão limitada à pasta do proprietário.
- `views/admin/projects`: listagem paginada e formulário compartilhado entre criação e edição.

## Comportamento

As configurações são atualizadas com `PATCH /admin/portfolio`. Os projetos usam rotas resource, sem uma página individual (`show`). A edição apresenta a descrição completa.

A listagem exibe dez projetos por página, ordenados por `display_order` crescente e depois por ID. O destaque é uma marca independente da ordem. A descrição é texto simples e é exibida com escape HTML. Links aceitam apenas HTTP e HTTPS. O editor rico fica para uma etapa posterior.

## Capas dos projetos

As capas são opcionais. JPEG, PNG e WebP são aceitos, até 2 MB e 6000 × 6000 pixels. A validação confere o conteúdo da imagem; um caminho de arquivo enviado como texto não é aceito. Os limites e o disco estão em `config/portfolio.php`.

Os arquivos recebem nomes gerados pelo servidor e ficam em `storage/app/public/portfolios/{portfolio_id}/covers`. Os formulários usam POST multipart; a edição usa method spoofing para PUT. A imagem atual aparece na edição e na listagem. A remoção é explícita e não pode ser solicitada junto com um novo upload.

Se uma gravação no banco falhar após o upload, a nova imagem é removida e a capa anterior é preservada. A substituição remove a antiga depois de salvar a nova referência. Excluir o projeto ou a conta também remove suas capas. Falhas de exclusão no disco são registradas no log para limpeza posterior, sem desfazer uma operação já concluída no banco. Exclusões diretas via SQL ou Eloquent fora dessas Actions não fazem a limpeza física.

Execute `php artisan storage:link` em cada instalação/deploy. Ele conecta `public/storage` a `storage/app/public`; no Windows pode resultar em uma junction. O document root do servidor deve apontar para `public`. No disco local as URLs usam o endereço da requisição, preservando a porta do Artisan; ajuste `APP_URL` ao domínio real no deploy. As capas desse disco são públicas, enquanto a autorização protege sua edição e exclusão.

Referência: [disco público do Laravel](https://laravel.com/docs/12.x/filesystem#the-public-disk).

## Verificação

```sh
php artisan test
vendor/bin/pint --test
npm run build
```

O PHPUnit define o ambiente `testing` e SQLite em memória. Os testes cobrem cadastro transacional, criação para contas antigas, restrição única, normalização e reserva de slugs, cores, CRUD, paginação, ordenação, escape HTML e isolamento entre usuários.
