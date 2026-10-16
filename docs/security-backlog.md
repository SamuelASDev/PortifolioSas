# Pendências de segurança para revisão futura

Registrado em 15/10/2026, após revisão do código local, auditoria de dependências,
execução de testes e consulta passiva aos cabeçalhos de `/login` em produção.
Nenhuma correção foi aplicada nesta revisão, conforme decisão do responsável.
Não foi identificada falha crítica no escopo analisado; isso não garante ausência
de outras vulnerabilidades nem substitui testes autenticados em produção.

## Prioridades

- [ ] **Cota total de armazenamento por conta:** incluir capas, fotos, imagens de
  compartilhamento e anexos na mesma cota. Hoje, os limites de anexos não abrangem
  todas as imagens. Avaliar limites de projetos e frequência de criação/uploads
  para reduzir abuso e esgotamento do espaço da hospedagem.
- [ ] **Privacidade das mídias:** avaliar armazenamento privado e entrega por rota
  com autorização/verificação de publicação. Atualmente, despublicar bloqueia as
  páginas, mas não revoga o acesso direto a imagens no disco público. Links de
  imagens de rascunhos também podem ser acessados por quem os conhece.
- [ ] **Recuperação de senha:** padronizar a resposta para e-mails existentes e
  inexistentes e adicionar limites por IP e endereço. O broker já possui intervalo
  de 60 segundos por conta, mas a rota não tem limitação explícita por IP.
- [ ] **Cabeçalhos de segurança:** configurar proteção contra enquadramento
  (`frame-ancestors`/`X-Frame-Options`), preservando a prévia interna. Avaliar HSTS,
  `X-Content-Type-Options` e uma CSP adequada. Na resposta de login consultada,
  a CSP continha apenas `upgrade-insecure-requests` e os demais cabeçalhos citados
  não foram observados.
- [ ] **Dependências de build:** reavaliar os alertas de `braces` e
  `postcss-selector-parser` na cadeia do Tailwind. Planejar atualização compatível,
  sem executar `npm audit fix --force` sem avaliar a migração.

## Evidências e validação da revisão

- `composer audit --locked --format=json`: sem alertas conhecidos.
- `npm audit`: 7 pacotes sinalizados (5 altos e 2 moderados), derivados de dois
  problemas na cadeia de desenvolvimento/build. Não foi identificado caminho de
  exploração desses problemas por visitantes do site PHP publicado.
- `npm audit --omit=dev`: sem alertas.
- Testes JavaScript: 40 passaram.
- Testes PHP: 221 executados, com uma falha na expectativa sobre links `mailto:`
  no rodapé (`FeaturedCarouselTest`). A falha isolada não demonstrou vazamento de
  e-mail privado; revisar a expectativa do teste separadamente.
- Cookie de sessão observado em produção: `Secure`, `HttpOnly` e `SameSite=Lax`.
- Código com controles explícitos de autorização por proprietário, escopo por
  portfólio, sanitização do editor e validação de imagens e vídeos.

Os resultados de dependências e produção representam a data da revisão e devem
ser verificados novamente quando estas tarefas forem realizadas.

Referências dos alertas:

- https://github.com/advisories/GHSA-vfj7-8cjw-p6xm
- https://github.com/advisories/GHSA-rj75-hqrm-r3gf
