# Publicação do Vértice na Hostinger

Guia operacional para este repositório. O plano, o domínio e os caminhos da conta ainda precisam ser confirmados no hPanel. A revisão não acessou a hospedagem nem realizou deploy.

## 1. Preparar a versão

1. Revise a branch `dev` e execute as validações do README.
2. Integre a versão aprovada na branch escolhida para produção. Na revisão de 15/10/2026, a `main` local ainda não contém o aplicativo completo.
3. Registre o commit publicado para permitir rastreamento e retorno à versão anterior.
4. Faça backup do banco, de `storage/app/public` e do `.env` existente antes de atualizar.

O workflow Validate não realiza deploy. Uma integração Git na hospedagem precisa ter branch e procedimento de instalação/build definidos separadamente.

## 2. Conferir o ambiente

Confirme PHP 8.2+, extensões, Composer, MySQL, acesso SSH, cron e suporte ao link de storage no plano contratado. Prefira alinhar a versão do PHP web com a CLI e com a versão validada em homologação; a CI atual usa PHP 8.3.

Para compilar no servidor, use Node compatível com o README. Se não estiver disponível, compile em ambiente de build e envie `public/build/` junto ao release. Essa pasta é ignorada pelo Git: um clone sozinho não traz os assets.

```sh
php -v
php -m
composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
composer check-platform-reqs --no-dev
```

Se necessário, compile os assets em ambiente de build:

```sh
npm ci
npm run build
```

O build precisa das dependências de desenvolvimento do npm. O servidor web não precisa de `node_modules` depois da compilação.

## 3. Diretório público

A raiz HTTP deve apontar para `public/`, com as regras de reescrita e o arquivo `.htaccess` dessa pasta. Não exponha a raiz Laravel, `.env`, `.git`, `vendor` ou logs.

Quando o plano exigir `public_html`, adapte a estrutura ao plano mantendo a aplicação fora da pasta pública. Separar/copiar `public/` exige ajustar os caminhos de bootstrap em `index.php` e o public path do Laravel, além de conferir build e storage. Prefira preservar a estrutura original quando a configuração permitir. Este repositório ainda não contém uma adaptação específica para essa estrutura.

Referências: [deploy Laravel na Hostinger](https://www.hostinger.com/support/6152127-how-to-deploy-laravel-8-at-hostinger/) e [deploy Laravel 12](https://laravel.com/docs/12.x/deployment). O artigo da Hostinger é voltado ao Laravel 8; os requisitos e comandos devem seguir a versão 12 usada aqui.

## 4. Ambiente de produção

Crie o `.env` no servidor, sem versioná-lo. Exemplo parcial, com valores a substituir:

```dotenv
APP_NAME="Vértice"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seu-dominio.com
APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=en
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=host-informado-no-hpanel
DB_PORT=3306
DB_DATABASE=banco-da-aplicacao
DB_USERNAME=usuario-da-aplicacao
DB_PASSWORD="substituir"

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
CACHE_STORE=database
QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_SCHEME=smtp
MAIL_HOST=host-smtp-do-provedor
MAIL_PORT=587
MAIL_USERNAME=usuario-smtp
MAIL_PASSWORD="substituir"
MAIL_FROM_ADDRESS=contato@seu-dominio.com
MAIL_FROM_NAME="Vértice"
```

Use host, porta e esquema indicados pelo provedor SMTP; o exemplo não representa credenciais ou configuração universal da Hostinger. Para TLS implícito, confira o uso de `smtps` com seu provedor. `pt_BR` não instala traduções ausentes automaticamente.

Gere `APP_KEY` com `php artisan key:generate` apenas na primeira instalação. Preserve a chave nas atualizações e no backup. Não copie a chave de um tutorial nem rode o script `setup` em produção.

## 5. Banco, storage e caches

Com o `.env` definitivo configurado e backup concluído, execute na raiz da aplicação:

```sh
php artisan config:clear
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

Garanta escrita em `storage/` e `bootstrap/cache/`, sem recorrer a permissão global `777`. Preserve banco e uploads ao trocar releases. Confirme que não existe `public/hot` no pacote de produção, pois ele aponta o navegador para um servidor Vite de desenvolvimento.

O PHP deve permitir arquivos de 10 MB e requisições maiores que isso, por causa do formulário e outros campos. Exemplo inicial: `upload_max_filesize=10M` e `post_max_size=40M`, ajustados ao plano e à quantidade de arquivos enviados em cada requisição. Confira também memória e limites HTTP: imagens de alta resolução podem consumir muito mais memória que seu tamanho em disco.

## 6. Cron e filas

Configure execução a cada minuto. Modelo de crontab para uma instalação que preserva a estrutura original:

```cron
* * * * * /caminho/do/php /caminho/absoluto/portfolio/artisan schedule:run
```

No hPanel, selecione a frequência nos campos do agendador e informe somente o comando, com os caminhos reais da conta. Consulte [cron na Hostinger](https://support.hostinger.com/en/articles/1583465-how-to-set-up-a-cron-job-at-hostinger).

Confira `php artisan schedule:list` e acompanhe as primeiras execuções. Os comandos atuais cuidam de uploads temporários e exclusões pendentes; não dependem de um worker de fila.

O projeto configura fila de banco, mas os fluxos atuais de e-mail usam as notificações padrão sem jobs próprios enfileirados identificados nesta revisão. Se notificações/jobs passarem a implementar processamento assíncrono, configure um worker compatível com o plano e monitore jobs falhos. Não use `composer run dev`, Vite dev server ou `artisan serve` como servidor de produção.

## 7. Conferência após publicar

- Abrir home, login e `/up` por HTTPS, sem erros de assets ou conteúdo misto.
- Cadastrar conta, receber e confirmar e-mail e testar recuperação de senha.
- Criar/editar projeto e enviar foto, capa, anexo e imagem de compartilhamento.
- Validar aparência, prévia, publicação/despublicação, filtros e ordem em desktop e celular.
- Com duas contas, confirmar que uma não acessa nem altera os projetos da outra.
- Confirmar que `.env`, `.git/config` e logs não são servidos publicamente.
- Verificar cron, logs, pendências de exclusão e restauração de backup.
- Confirmar metadados e URLs com domínio HTTPS real.

## 8. Atualizações e recuperação

Registre release e migrations aplicadas. Em atualizações que exijam interrupção, planeje manutenção com `php artisan down` e retorno com `php artisan up` após os checks. Preserve `.env`, chave e uploads.

Voltar apenas o código pode ser insuficiente se o esquema do banco mudou. Mantenha backup restaurável e avalie compatibilidade das migrations antes de reverter; não use `migrate:fresh` em produção.

## Arquivo de ambiente preparado nesta estação

Foi criado `C:/Users/samue/Desktop/Vertice-Hostinger/.env`, fora do Git, com configuração de produção e chave independente para a instalação nova. Não publique nem envie essa pasta ao GitHub. O `.env` local do projeto permanece com sua configuração original.

Preencha o domínio HTTPS e o SMTP antes de colocar o aplicativo em uso. A chave já gerada deve ser preservada; se existir instalação anterior, utilize sua chave original. O teste remoto de MySQL foi recusado por autenticação (1045), sem alteração de dados. Confirme usuário, senha e permissões no hPanel. Um teste a partir do servidor ainda é necessário.

O diretório público inclui `.user.ini` com upload de 10 MB e POST de 40 MB para PHP CGI/FPM. O painel/plano pode impor outros limites; confira os valores efetivos após publicar.
