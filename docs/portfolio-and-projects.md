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
- `ProjectMedia`, `UploadProjectImage` e `ProjectMediaManager`: registro de anexos, limites e seleção de imagens permitidas por projeto e sessão de edição.
- `Services/VideoEmbed`: valida links e gera URLs canônicas do YouTube/Vimeo, sem buscar conteúdo externo.
- `views/admin/projects`: listagem paginada e formulário compartilhado entre criação e edição.

## Comportamento

As configurações são atualizadas com `PATCH /admin/portfolio`. Os projetos usam rotas resource, sem uma página individual (`show`). A edição apresenta a descrição completa.

## Apresentação e publicação

O painel do portfólio permite editar nome profissional, título, biografia de até 1000 caracteres, foto e contatos públicos: e-mail, GitHub, LinkedIn e site pessoal. O e-mail de login não é utilizado como contato público. Os links aceitam HTTP e HTTPS; os textos continuam sendo exibidos com escape HTML.

A foto aceita JPEG, PNG e WebP até 10 MB e 3000 × 3000 pixels. Fica em `storage/app/public/portfolios/{portfolio_id}/photos`, usando o mesmo `storage:link` das capas. Substituição, remoção, falhas de gravação e exclusão da conta tratam a limpeza física. Fotos e capas no disco público são arquivos públicos; o estado de rascunho protege as páginas, não transforma esses arquivos em privados.

Portfólios novos e existentes começam como rascunho. A migration preenche o nome profissional inicial com o nome da conta e preserva os demais dados. A publicação é uma operação separada: `PATCH /admin/portfolio/publication`, com `is_published` booleano. Ela sempre usa o portfólio do usuário autenticado e exige um nome profissional salvo. Não é permitido apagar esse nome enquanto o portfólio estiver publicado.

Depois de publicado, alterações salvas na apresentação e nos projetos ficam visíveis imediatamente. Despublicar mantém os dados e faz ambas as páginas públicas retornarem 404, inclusive para o próprio dono. Não há uma rota de prévia de rascunho nesta entrega.

## Vitrine pública

- `GET /{slug}`: apresentação, contatos e projetos ordenados por `display_order` e depois ID, com 12 projetos por página. Os destaques recebem indicação visual, sem mudar a ordem configurada.
- `GET /{slug}/projects/{project}`: descrição completa com formatação sanitizada, capa e links de demonstração/repositório. O ID precisa pertencer ao portfólio do slug; combinações de contas diferentes retornam 404.
- Slugs inexistentes, portfólios em rascunho e projetos inexistentes retornam 404. Alterar o slug invalida os links antigos, sem redirecionamento automático.
- As respostas públicas enviam `Cache-Control: no-store` para evitar armazenamento de páginas que depois sejam despublicadas.

O layout público tem CSS próprio (`resources/css/public-portfolio.css`), componentes Blade compartilhados, tema explícito claro/escuro, cor de destaque validada e contraste calculado para o texto dos botões. As regras para telas pequenas alteram a grade de três para duas e uma coluna, reorganizam a apresentação e permitem quebra de textos longos. A preferência de tema do sistema não substitui o tema salvo. O build Vite inclui a nova entrada CSS.

### Carrossel opcional e e-mail

Em **Portfólio → Endereço e aparência**, a opção **Exibir carrossel de projetos em destaque** controla a seção anterior à lista completa. Fica desligada por padrão, inclusive para portfólios existentes. O carrossel usa somente projetos do próprio portfólio marcados como destaque, ordenados por `display_order` e ID, limitado a dez (`config/portfolio.php`). A seleção é independente da paginação; não retira os projetos da lista completa. Sem destaques, a seção não aparece. Com um destaque, a apresentação é estática.

O componente Blade combina capa, resumo e links com as cores/tema atuais. O JavaScript separado usa scroll-snap nativo, sem CDN. Com mais de um slide, oferece setas nas laterais, indicadores, teclado e autoplay a cada cinco segundos, sem controle de iniciar. Suspende durante hover, foco, toque ou enquanto a aba estiver oculta e retoma automaticamente ao terminar a interação. Respeita movimento reduzido. Sem JavaScript, os slides continuam acessíveis por rolagem horizontal. Os links usam um componente SVG reutilizável com ícones de plataforma e de ação.

O link **E-mail**, compartilhado pela apresentação e pelo rodapé, abre a composição do Gmail em nova aba com o destinatário definido em `contact_email`. A query é codificada para preservar endereços com `+` e outros caracteres válidos. Não usa o e-mail privado da conta nem envia mensagens automaticamente.

A listagem administrativa exibe dez projetos por página, ordenados por `display_order` crescente e depois por ID. O destaque é uma marca independente da ordem. A descrição aceita texto simples legado ou HTML limitado do editor rico. Os resumos são sempre texto escapado. Links aceitam apenas HTTP e HTTPS.

## Capas dos projetos

As capas são opcionais. JPEG, PNG e WebP são aceitos, até 10 MB e 6000 × 6000 pixels. A validação confere o conteúdo da imagem; um caminho de arquivo enviado como texto não é aceito. Os limites e o disco estão em `config/portfolio.php`.

Os arquivos recebem nomes gerados pelo servidor e ficam em `storage/app/public/portfolios/{portfolio_id}/covers`. Os formulários usam POST multipart; a edição usa method spoofing para PUT. A imagem atual aparece na edição e na listagem. A remoção é explícita e não pode ser solicitada junto com um novo upload.

Se uma gravação no banco falhar após o upload, a nova imagem é removida e a capa anterior é preservada. A substituição remove a antiga depois de salvar a nova referência. Excluir o projeto ou a conta também remove suas capas. Falhas de exclusão no disco são registradas no log para limpeza posterior, sem desfazer uma operação já concluída no banco. Exclusões diretas via SQL ou Eloquent fora dessas Actions não fazem a limpeza física.

Execute `php artisan storage:link` em cada instalação/deploy. Ele conecta `public/storage` a `storage/app/public`; no Windows pode resultar em uma junction. O document root do servidor deve apontar para `public`. No disco local as URLs usam o endereço da requisição, preservando a porta do Artisan; ajuste `APP_URL` ao domínio real no deploy. As capas desse disco são públicas, enquanto a autorização protege sua edição e exclusão.

Referência: [disco público do Laravel](https://laravel.com/docs/12.x/filesystem#the-public-disk).

## Editor de descrição

O formulário usa Tiptap, carregado sob demanda apenas nas telas com editor. Oferece parágrafos, títulos de nível 2/3, negrito, itálico, listas, citações, código, links HTTP/HTTPS, desfazer/refazer e limpeza de formatação. O formulário continua enviando `description`, acompanhado por `description_format` (`text` ou `html`). O limite continua em 50.000 caracteres incluindo a marcação. Há campo de texto de fallback se o JavaScript não carregar.

A migration adiciona `description_format` com padrão `text`, sem reescrever descrições existentes. Isso preserva quebras de linha, entidades e exemplos com tags literais. Ao abrir um registro antigo, o editor recebe nós de texto, nunca interpreta a string antiga como HTML. A conversão para HTML acontece ao salvar pelo editor. Requisições sem marcador de formato continuam tratando a descrição enviada como texto simples.

`SaveProject` sanitiza HTML com `ezyang/htmlpurifier` antes de salvar. `ProjectDescription` aplica a mesma política novamente ao renderizar. A lista permitida é restrita à formatação suportada e às mídias autorizadas descritas abaixo. Scripts, eventos, estilos, imagens externas, players desconhecidos e atributos não permitidos são removidos. Só links com esquema HTTP/HTTPS explícito são preservados. A versão HTML segura é exposta por `description_html`; cards, painel e metadados usam `description_text`, escapado pelo Blade.

### Imagens e vídeos entre os textos

Os botões **Imagem** e **Vídeo** inserem blocos na posição do cursor. A imagem recebe descrição alternativa e legenda opcional. Os blocos podem ser selecionados e removidos no editor; as imagens são responsivas e os players mantêm proporção 16:9. Foto profissional e capa continuam independentes.

O upload usa `POST /admin/project-media`, com arquivo, UUID da sessão de edição e ID opcional de projeto. Aceita JPEG/PNG/WebP de até 10 MB e 6000 × 6000 pixels. Os limites configuráveis são 20 anexos por projeto, 100 arquivos e 100 MB por portfólio. O servidor define o proprietário e os nomes dos arquivos em `storage/app/public/portfolios/{portfolio_id}/attachments`, usando o `storage:link` existente.

Uploads novos ficam temporários. Salvar a descrição vincula apenas os IDs autorizados daquele projeto ou daquela sessão. O servidor reconstrói cada `src` com o caminho registrado; não confia na URL enviada pelo cliente. Não é permitido reutilizar imagens de outra conta ou de outro projeto. O HTML de exemplos de código não é tratado como referência de anexo.

Uma imagem removida do texto só tem registro e arquivo excluídos depois de salvar com sucesso. Erros de validação preservam uploads e texto para nova tentativa. Uploads temporários de outras abas não são apagados pelo salvamento atual. Excluir projeto ou conta também remove os anexos; falhas de exclusão física seguem registradas em log.

`POST /admin/project-media/video` aceita links públicos do YouTube (watch, shorts, youtu.be) e Vimeo. Gera somente URLs HTTPS de `www.youtube-nocookie.com/embed/{id}` ou `player.vimeo.com/video/{id}`. Players são reconstruídos sem `srcdoc`, eventos, autoplay ou permissões arbitrárias; o HTML Purifier também verifica os hosts. Não há upload direto de vídeo. A reprodução depende da disponibilidade e permissão de incorporação no provedor.

Uploads temporários abandonados há 24 horas são removidos por `php artisan project-media:prune`. A tarefa está agendada diariamente em `routes/console.php`; em produção, o scheduler do Laravel precisa estar ativo. Durante o desenvolvimento pode executar o comando manualmente. Anexos já vinculados não expiram, mesmo em portfólios em rascunho. Os arquivos do disco público continuam acessíveis por seus links diretos, conforme a política já usada para capas e fotos.

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

## Editor de aparência e prévia privada

Em Endereço e aparência, “Personalizar aparência” abre um dialog com configurações e iframe. Há quatro combinações prontas (Escuro original, Claro essencial, Editorial e Terminal), três famílias de fontes locais, tamanhos gerais/títulos e cores por área. O catálogo fica em `config/portfolio-appearance.php`; portfólios sem personalização mantêm a aparência anterior.

`GET /admin/portfolio/preview` e `/admin/portfolio/preview/projects/{project}` exigem autenticação e usam exclusivamente o portfólio da conta atual. A consulta da lista é compartilhada em `PortfolioPage`, e os componentes Blade são os mesmos da vitrine pública. A paginação e os links de detalhes permanecem nas rotas privadas. As respostas usam no-store, noindex/nofollow e frame-ancestors self. As rotas públicas continuam retornando 404 para rascunhos.

O módulo `appearance/editor.js` mantém um estado temporário, separado do estado salvo, e transmite ajustes por postMessage restrito à origem e à janela do iframe. `appearance/settings.js` valida valores e aplica variáveis CSS; a renderização inicial usa `PortfolioAppearance`. Os modos computador/celular usam viewports reais de 1100/390 pixels, escalados para caber na janela. A prévia mostra o conteúdo já salvo, incluindo imagens, carrossel e projetos.

“Salvar aparência” chama `PATCH /admin/portfolio/appearance`, com Form Request específico e Action transacional. Apenas aparência, tema e cor de destaque são atualizados; publicação, apresentação e projetos permanecem sob seus fluxos próprios. Cancelar/Escape descartam os ajustes posteriores ao último salvamento. Salvar um rascunho não publica; salvar um portfólio já publicado atualiza sua aparência imediatamente. A janela avisa sobre esse comportamento. Não há uma segunda versão publicada do conteúdo.

Os valores são limitados a cores hexadecimais e fontes/tamanhos do catálogo. O editor alerta sobre contraste baixo e calcula automaticamente o texto dos botões sobre a cor de destaque.

## Organização visual de projetos

Em Meus projetos, “Organizar projetos e destaques” abre `GET /admin/projects/organize` com todos os projetos da conta, sem a limitação da paginação. Há arrastar pela alça com Pointer Events, botões Subir/Descer e navegação por setas quando a alça tem foco. A lista permite alterar os destaques no mesmo fluxo. A janela de aparência também tem um atalho; após salvar a ordem, Início recarrega a prévia.

O módulo `projects/organization.js` mantém as mudanças no DOM até “Salvar ordem e destaques”. `PATCH /admin/projects/organize` recebe a lista completa de IDs, flags e uma revisão. O Form Request valida formato e propriedade de cada ID. A Action bloqueia o portfólio durante a transação, exige a lista exata dos projetos existentes e verifica a revisão; listas antigas retornam 409, sem escrita parcial. A revisão cobre IDs, ordem, destaque e updated_at. A ordem é normalizada para 0..N-1, compartilhada pela vitrine e pelo carrossel. Nenhum conteúdo ou arquivo é alterado nesse fluxo.

## Compartilhamento de portfólio e projetos

“Copiar link” e “Compartilhar” aparecem nos formulários administrativos de compartilhamento de portfólios publicados. Nas páginas públicas, aparece somente “Compartilhar”: no portfólio, com o estilo dos contatos; no projeto, junto dos botões de demonstração/repositório. O componente `share-actions` usa os metadados salvos e a URL pública canônica, sem parâmetros de navegação ou endereços administrativos. As prévias privadas não exibem esses controles; rascunhos mostram no painel a orientação de publicar primeiro.

`sharing/actions.js` tenta o menu nativo via Web Share API quando disponível; sem suporte ou em caso de falha, tenta copiar o link. Cancelar o menu não copia nada. Quando a área de transferência não está disponível ou é negada, um campo readonly é revelado, focado e selecionado para cópia manual. Sem JavaScript, esse campo permanece visível. A ação envia o link e o texto ao menu do dispositivo, sem anexar o arquivo da imagem: as plataformas obtêm o cartão pelas tags da página pública. O módulo `share-links.js` é carregado uma única vez por página.

As telas Meu portfólio e Editar projeto possuem um formulário independente de Compartilhamento, com título (100 caracteres), descrição (300) e imagem opcionais. O componente compartilhado mostra uma simulação que acompanha o texto e a seleção local da imagem. As plataformas podem recortar imagens e usar cache; a simulação não pretende reproduzir exatamente um serviço específico.

`PATCH /admin/portfolio/sharing` e `/admin/projects/{project}/sharing` usam `UpdateSharingRequest` (erros no bag sharing), Policy para projetos e `SaveSharing`. Uploads JPEG/PNG/WebP têm limite de 10 MB e 6000 × 6000; recomenda-se 1200 × 630. `SharingImageStorage` reutiliza o armazenamento central, salvando em `portfolios/{id}/sharing` no disco public, servido pelo link `public/storage`. A imagem antiga é excluída após salvar; falhas na transação descartam a nova. Remoção de projeto/conta limpa suas imagens e preserva arquivos de outras contas.

`SharingMetadata` centraliza título, descrição, imagem e URL canônica para HTML, Open Graph e Twitter Cards, com escaping no Blade. Campos vazios usam nome/título e resumo original. A imagem usa primeiro a personalizada do registro; projetos sem imagem própria usam capa, imagem personalizada do portfólio ou foto de apresentação, nessa ordem. Portfólios sem imagem personalizada usam a foto. Sem imagem, o card é summary. Canonicals apontam para a URL pública sem parâmetros, inclusive na prévia privada, que mantém noindex/nofollow. A configuração não publica rascunhos nem altera o texto visível dos projetos.
