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
- `Services/PortfolioImageStorage`: implementação compartilhada de armazenamento das capas e fotos, com coleções separadas.
- `Actions/SavePortfolio` e `SetPortfolioPublication`: persistência da apresentação e publicação explícita, com bloqueio do registro durante a transação.
- `Controllers/PublicPortfolioController`: resolve somente portfólios publicados e busca cada projeto pela relação do portfólio solicitado.
- `Services/ProjectDescription`: sanitização de HTML e geração das versões segura e de texto da descrição.
- `resources/js/editors/project-description.js`: editor Tiptap, barra de formatação, links e sincronização com o formulário.
- `views/admin/projects`: listagem paginada e formulário compartilhado entre criação e edição.

## Comportamento

As configurações são atualizadas com `PATCH /admin/portfolio`. Os projetos usam rotas resource, sem uma página individual (`show`). A edição apresenta a descrição completa.

## Apresentação e publicação

O painel do portfólio permite editar nome profissional, título, biografia de até 1000 caracteres, foto e contatos públicos: e-mail, GitHub, LinkedIn e site pessoal. O e-mail de login não é utilizado como contato público. Os links aceitam HTTP e HTTPS; os textos continuam sendo exibidos com escape HTML.

A foto aceita JPEG, PNG e WebP até 2 MB e 3000 × 3000 pixels. Fica em `storage/app/public/portfolios/{portfolio_id}/photos`, usando o mesmo `storage:link` das capas. Substituição, remoção, falhas de gravação e exclusão da conta tratam a limpeza física. Fotos e capas no disco público são arquivos públicos; o estado de rascunho protege as páginas, não transforma esses arquivos em privados.

Portfólios novos e existentes começam como rascunho. A migration preenche o nome profissional inicial com o nome da conta e preserva os demais dados. A publicação é uma operação separada: `PATCH /admin/portfolio/publication`, com `is_published` booleano. Ela sempre usa o portfólio do usuário autenticado e exige um nome profissional salvo. Não é permitido apagar esse nome enquanto o portfólio estiver publicado.

Depois de publicado, alterações salvas na apresentação e nos projetos ficam visíveis imediatamente. Despublicar mantém os dados e faz ambas as páginas públicas retornarem 404, inclusive para o próprio dono. Não há uma rota de prévia de rascunho nesta entrega.

## Vitrine pública

- `GET /{slug}`: apresentação, contatos e projetos ordenados por `display_order` e depois ID, com 12 projetos por página. Os destaques recebem indicação visual, sem mudar a ordem configurada.
- `GET /{slug}/projects/{project}`: descrição completa com formatação sanitizada, capa e links de demonstração/repositório. O ID precisa pertencer ao portfólio do slug; combinações de contas diferentes retornam 404.
- Slugs inexistentes, portfólios em rascunho e projetos inexistentes retornam 404. Alterar o slug invalida os links antigos, sem redirecionamento automático.
- As respostas públicas enviam `Cache-Control: no-store` para evitar armazenamento de páginas que depois sejam despublicadas.

O layout público tem CSS próprio (`resources/css/public-portfolio.css`), componentes Blade compartilhados, tema explícito claro/escuro, cor de destaque validada e contraste calculado para o texto dos botões. As regras para telas pequenas alteram a grade de três para duas e uma coluna, reorganizam a apresentação e permitem quebra de textos longos. A preferência de tema do sistema não substitui o tema salvo. O build Vite inclui a nova entrada CSS.

A listagem administrativa exibe dez projetos por página, ordenados por `display_order` crescente e depois por ID. O destaque é uma marca independente da ordem. A descrição aceita texto simples legado ou HTML limitado do editor rico. Os resumos são sempre texto escapado. Links aceitam apenas HTTP e HTTPS.

## Capas dos projetos

As capas são opcionais. JPEG, PNG e WebP são aceitos, até 2 MB e 6000 × 6000 pixels. A validação confere o conteúdo da imagem; um caminho de arquivo enviado como texto não é aceito. Os limites e o disco estão em `config/portfolio.php`.

Os arquivos recebem nomes gerados pelo servidor e ficam em `storage/app/public/portfolios/{portfolio_id}/covers`. Os formulários usam POST multipart; a edição usa method spoofing para PUT. A imagem atual aparece na edição e na listagem. A remoção é explícita e não pode ser solicitada junto com um novo upload.

Se uma gravação no banco falhar após o upload, a nova imagem é removida e a capa anterior é preservada. A substituição remove a antiga depois de salvar a nova referência. Excluir o projeto ou a conta também remove suas capas. Falhas de exclusão no disco são registradas no log para limpeza posterior, sem desfazer uma operação já concluída no banco. Exclusões diretas via SQL ou Eloquent fora dessas Actions não fazem a limpeza física.

Execute `php artisan storage:link` em cada instalação/deploy. Ele conecta `public/storage` a `storage/app/public`; no Windows pode resultar em uma junction. O document root do servidor deve apontar para `public`. No disco local as URLs usam o endereço da requisição, preservando a porta do Artisan; ajuste `APP_URL` ao domínio real no deploy. As capas desse disco são públicas, enquanto a autorização protege sua edição e exclusão.

Referência: [disco público do Laravel](https://laravel.com/docs/12.x/filesystem#the-public-disk).

## Editor de descrição

O formulário usa Tiptap, carregado sob demanda apenas nas telas com editor. Oferece parágrafos, títulos de nível 2/3, negrito, itálico, listas, citações, código, links HTTP/HTTPS, desfazer/refazer e limpeza de formatação. O formulário continua enviando `description`, acompanhado por `description_format` (`text` ou `html`). O limite continua em 50.000 caracteres incluindo a marcação. Há campo de texto de fallback se o JavaScript não carregar.

A migration adiciona `description_format` com padrão `text`, sem reescrever descrições existentes. Isso preserva quebras de linha, entidades e exemplos com tags literais. Ao abrir um registro antigo, o editor recebe nós de texto, nunca interpreta a string antiga como HTML. A conversão para HTML acontece ao salvar pelo editor. Requisições sem marcador de formato continuam tratando a descrição enviada como texto simples.

`SaveProject` sanitiza HTML com `ezyang/htmlpurifier` antes de salvar. `ProjectDescription` aplica a mesma política novamente ao renderizar. A lista permitida é restrita a parágrafos, títulos 2/3, negrito/itálico, listas, citações, links e código. Scripts, eventos, estilos, iframes, imagens e atributos não permitidos são removidos. Só links com esquema HTTP/HTTPS explícito são preservados. A versão HTML segura é exposta por `description_html`; cards, painel e metadados usam `description_text`, escapado pelo Blade.

Imagens e vídeos no corpo da descrição ficam para uma entrega posterior. Os uploads de foto profissional e capa continuam independentes do editor.

Referências: [Tiptap com JavaScript](https://tiptap.dev/docs/editor/getting-started/install/vanilla-javascript), [configuração do HTML Purifier](https://htmlpurifier.org/live/configdoc/plain.html).

## Verificação

```sh
php artisan test
npm run test:js
vendor/bin/pint --test
npm run build
```

O PHPUnit define o ambiente `testing` e SQLite em memória. Os testes cobrem cadastro transacional, criação para contas antigas, restrição única, normalização e reserva de slugs, cores, CRUD, paginação, ordenação, escape HTML e isolamento entre usuários.

A suíte também cobre apresentação, publicação/despublicação, ciclo de vida da foto, contatos explícitos, privacidade do e-mail da conta, rascunhos inacessíveis, slugs e projetos inexistentes, escopo do projeto por slug, temas, paginação pública e descrição completa. A revisão visual no navegador/celular fica com o responsável pelo projeto.
