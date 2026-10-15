# Consolidação — 15/10/2026

Implementação das melhorias da revisão, exceto o controle de pausa do carrossel, conforme solicitado. O código do carrossel e seu autoplay foram preservados.

## Entregue

- Verificação de e-mail efetiva: `User` implementa `MustVerifyEmail`; dashboard e todas as rotas administrativas exigem verificação. Perfil, logout e reenvio permanecem disponíveis. Cadastro envia a notificação; o fluxo de duas contas agora passa pela verificação assinada.
- Tela de verificação em português, com caminho para corrigir o e-mail da conta. Nenhuma conta existente foi verificada automaticamente.
- Resumo das descrições reaproveitado por instância de projeto e invalidado ao mudar texto ou formato. Sanitização na leitura e no salvamento preservada.
- Recuperação durável de exclusões de imagens: tabela independente `pending_image_deletions`, tentativa imediata e comando `images:retry-deletions` a cada cinco minutos, com espera crescente e registro do erro. Caminhos e discos são validados antes da exclusão.
- Política de arquivos públicos explícita no README e nos formulários de foto, capa, descrição, compartilhamento e publicação. Não houve migração de imagens para disco privado; despublicar continua bloqueando páginas, sem revogar URLs diretas.
- Página inicial do produto, com apresentação, etapas de criação e acesso a cadastro/login/painel. README específico com instalação, e-mail, tarefas agendadas, testes e estrutura.
- Pint corrigido na migration inicial de portfólios.
- GitHub Actions para PHP/SQLite, PHP/MySQL 8.4, JavaScript, build e Pint. O MySQL usa banco e usuário de teste separados; o guard de testes continua recusando o banco normal da aplicação.
- Nomes de categoria e tecnologias expansíveis no celular, sem depender de `title` ou alterar a opção por ícones compactos.
- Editor aceita imagens sob a URL-base de anexos informada pelo servidor, incluindo origem remota configurada. Teste cobre reabertura de conteúdo remoto e rejeição de outras origens, portfólios, travessia de caminho e credenciais na URL. Isso não representa implantação ou teste real em S3/CDN.
- Na inspeção visual, foi corrigido corte lateral do modal de aparência no celular: usa a largura útil disponível em vez de `100vw`.

## Validação automática

- PHP: 219 testes aprovados, 1.191 assertions, em SQLite em memória.
- JavaScript: 38 testes aprovados.
- Build Vite, Pint e `git diff --check` aprovados.
- Casos novos cobrem autorização de conta não verificada, troca de e-mail, notificação de cadastro, invalidação de resumo, exclusão falha com nova tentativa, limpeza mesmo durante indisponibilidade do banco, idempotência, recusa de caminhos perigosos e mídia remota no editor.
- CI adicionada; sua execução remota e a suíte MySQL ainda dependem de enviar a branch ao GitHub. Não foram apresentadas como execuções locais aprovadas.

## Conferência no Edge

Foi usada uma instância Laravel separada em `127.0.0.1:8011`, com SQLite e contas de teste. Nenhuma conta real foi usada para alterar conteúdo.

Conferido:

- Login e dashboard com progresso de configuração.
- Conta não verificada redirecionada de `/admin/projects` para a tela de verificação, com confirmação do reenvio no transporte de teste.
- Erro de slug reservado, preservando os campos preenchidos; correção e salvamento da apresentação.
- Editor rico, alteração de título/categoria e persistência do projeto.
- Salvamento de tema claro, prévia privada e alternância Computador/Celular.
- Modal em largura de celular, alternância Configurações/Prévia e encaixe após a correção.
- Reordenação por botões e teclado no celular, com confirmação de salvamento e ordem resultante.
- Expansão dos nomes das tecnologias no celular.
- Página inicial em desktop e 390 px, sem estouro horizontal detectado.

Limitação: o seletor de upload da extensão Edge recusou acesso ao arquivo local porque a extensão não tem “Allow access to file URLs”. Não foi alterada essa permissão. Uploads e limpeza continuam cobertos pelos testes HTTP; o fluxo completo de seleção/envio pela interface não foi certificado. Não houve envio de e-mail real: a instância de revisão usou transporte em memória.

## Banco e operação

Apenas a migration incremental `2026_10_15_060000_create_pending_image_deletions_table` foi aplicada ao banco local existente. Contagens e hashes das linhas completas de `users`, `portfolios`, `projects`, `project_media`, `tags` e `project_tag` permaneceram iguais antes/depois: 1 usuário, 1 portfólio, 2 projetos, 3 mídias, 3 tags e 3 associações.

O scheduler lista os dois comandos, mas precisa de `schedule:work` no desenvolvimento ou execução de `schedule:run` a cada minuto no servidor. A nova rotina recupera falhas registradas; não varre arquivos órfãos antigos nem substitui backup.

Se o banco estiver indisponível ao registrar uma exclusão, a aplicação ainda tenta a limpeza direta e registra o erro. A recuperação automática depende de conseguir persistir a pendência.

A instância temporária foi encerrada e o banco de revisão, os scripts e o arquivo de upload de teste foram removidos. As capturas de tela foram preservadas no diretório privado de revisão.

Nenhum commit, push ou merge foi feito nesta rodada.

## Correção posterior: imagens no editor

Foi identificada e corrigida uma regressão na validação de URLs introduzida nesta consolidação. `asset()` remove a barra final da URL-base local, mas o editor exigia essa barra para reconhecer o nome do arquivo. Isso rejeitava respostas de upload e imagens existentes ao reabrir a descrição. A validação agora normaliza o limite do diretório, mantendo a recusa de outros portfólios, diretórios e origens.

Um teste novo reproduziu a falha antes da correção; também foi ajustado o teste de inserção após upload para usar a URL-base real sem barra final. Validação posterior: 39 testes JavaScript, 39 testes PHP de mídia/capas/descrição (258 assertions), build e diff check aprovados.

## Limite de imagens: 10 MB

A pedido do usuário, fotos, capas, anexos da descrição e imagens de compartilhamento agora aceitam até 10 MB por arquivo. Validações do servidor, editor, prévia e avisos foram atualizados. Os testes HTTP verificam aceitação de 10 MB e rejeição acima desse limite. As dimensões máximas e a quota de 100 MB para anexos por portfólio foram preservadas. O PHP local permite uploads e POSTs de até 40 MB.

Validação desta alteração: 62 testes PHP (418 assertions), 39 testes JavaScript, build, Pint e diff check aprovados.
