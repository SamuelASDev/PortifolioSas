# Vértice — identidade e interface

## Direção

Nome escolhido: **Vértice**. Assinatura: **Seu trabalho, em perspectiva.** A identidade representa o ponto de encontro entre trajetória, projetos e novas possibilidades.

O símbolo é um V geométrico construído em SVG, compartilhado pelo wordmark e pelo favicon. A configuração textual fica em `config/brand.php`; o símbolo em `components/application-logo.blade.php` e `public/favicon.svg`.

## Sistema visual

- Tipografia Manrope variável, hospedada pela aplicação, com licença OFL incluída em `resources/fonts/manrope-OFL.txt`. Nenhuma requisição a serviços externos de fonte é necessária durante o uso.
- Fundo grafite, superfícies foscas, branco suave e lavanda como destaque principal. Verde claro aparece nos pontos de direção da marca; verde de status e vermelho continuam identificando publicação e ações destrutivas.
- Títulos com espaçamento compacto, textos auxiliares em tons secundários, controles com foco visível e campos com altura confortável no celular.
- Componentes comuns de marca, botões, campos e navegação em `resources/css/studio.css`. A identidade visual do produto fica em `resources/css/vertice.css`, separada da aparência configurável dos portfólios públicos.
- O V geométrico, as linhas de construção e os pequenos índices tipográficos remetem a perspectiva e direção. Seções editoriais com divisórias substituem cartões decorativos na apresentação do produto.
- Botões respondem ao pressionar. A preferência por movimento reduzido remove a transformação; não há animação decorativa contínua nem novas dependências de interface.

## Telas

- Página inicial com nova identidade, apresentação editorial, demonstração ilustrativa de portfólio e explicação das etapas.
- Login e cadastro com composição em duas colunas no desktop, elemento geométrico da marca e conteúdo em português. No celular, o formulário ganha prioridade.
- Recuperação, redefinição, confirmação de senha e verificação de e-mail seguem a mesma estrutura.
- Painel com navegação lateral no desktop, menu nativo expansível no celular, contexto da página e rodapé da marca.
- Dashboard com contagens reais e projetos recentes, sempre consultados pela relação do portfólio autenticado; primeiros passos e progresso preservados.
- Listagem de projetos com cards, capas ou placeholders, filtros e ações reorganizadas.
- Configuração do portfólio com atalhos de seção, campos em duas colunas onde houver espaço, foto e contatos reorganizados.
- Edição/criação de projetos, organização, compartilhamento, modal de aparência e conta usam as superfícies e os controles comuns.

## Temas dos portfólios públicos

O editor oferece onze temas e doze controles de composição: apresentação, grade/lista, largura, espaçamento, cantos, proporção das capas, foto, acabamento dos cartões, fundo, botões, cabeçalho e resumos. Estúdio Vértice usa grafite e lavanda; Oceano, azul claro; Galeria, branco e geometria; Editorial, papel e serifas; Terminal, verde e código. Escuro original e Claro essencial oferecem bases mais discretas.

Horizonte combina areia, argila e capas panorâmicas. Noturno usa índigo e ouro suave. Botânico combina sálvia, verde-floresta e imagens quadradas. Concreto usa cinza mineral, monoespaçada e projetos em lista.

`config/portfolio-appearance.php` centraliza o catálogo e os valores permitidos para validação, renderização e prévia. O estado continua na coluna JSON `appearance`, sem migration adicional. As predefinições restauram a composição completa e preservam a possibilidade de personalização.

A parte pública usa superfícies derivadas da paleta escolhida, acabamento próprio para listas e cartões sem borda, filtros recolhíveis e cabeçalhos responsivos. Filtros ativos permanecem abertos. A preferência por movimento reduzido desativa transformações decorativas.
