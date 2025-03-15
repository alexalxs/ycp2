# Documentação Website Exemplo4 - Estilo Harper's Bazaar

## To Fix, To Do, Test, Done

### To Fix

- Nenhum item pendente

### To Do

- Nenhum item pendente

### Test

- Nenhum item em teste

### Done

- [x] Estrutura principal HTML
- [x] Estilização CSS baseada em Harper's Bazaar
- [x] Interações JavaScript
- [x] Layout responsivo
- [x] Formulário de captura de email
- [x] Animações e efeitos visuais
- [x] Seção de FAQ interativa
- [x] Design elegante de tipografia Bodoni
- [x] Reorganização do conteúdo seguindo padrão TSL
- [x] Posicionamento do Header e Hero Section na parte inferior da página
- [x] Adicionado botão que redireciona o usuário para a página de compra após
      capturar o e-mail (https://dekoola.com/ch/hack/)
- [x] Implementado indicador de processamento durante a submissão do formulário
- [x] Configurado envio de e-mail via POST para a API Autonami
- [x] Adicionado formatação em negrito (bold) para palavras de poder (Agora,
      Hoje, etc.)
- [x] Implementado uso moderado de palavras sublinhadas para expressões chave

## Componentes Principais

### Header Estilo Harper's Bazaar

- **Design:** Header com título centralizado em tipografia Bodoni, links de
  redes sociais no topo e data da edição
- **Funcionalidade:** Altera estilo ao rolar a página (fica fixo e com sombra
  sutil)
- **Responsividade:** Adapta-se a diferentes tamanhos de tela, com tipografia
  redimensionada em dispositivos móveis
- **Posicionamento:** Movido para a parte inferior da página conforme padrão TSL

### Hero Banner de Revista

- **Design:** Layout de grade com imagem de capa do livro em destaque e conteúdo
  lateral
- **Funcionalidade:** Efeito 3D na capa do livro ao passar o mouse
- **Elementos:**
  - Etiqueta de edição especial
  - Título principal em tipografia elegante
  - Subtítulo em itálico
  - Informações do autor com foto
  - Avaliações com estrelas
  - Botão principal com animação de pulso
- **Posicionamento:** Movido para a parte inferior da página conforme padrão TSL

### Layout Editorial

- **Design:** Inspirado no layout editorial de Harper's Bazaar
- **Elementos:**
  - Colunas assimétricas (7:3 e 3:7)
  - Citações em destaque com formatação especial
  - Divisores de texto elegantes
  - Numeração de seções estilo revista
  - Espaçamento generoso para leiturabilidade

### Seção do Autor

- **Design:** Box de apresentação do autor com foto circular e biografia
- **Estilo:** Fundo suave e borda sutil
- **Conteúdo:** Foto, nome, título e breve biografia profissional

### Feature Quote

- **Design:** Citação em destaque com tipografia Bodoni em itálico
- **Estilo:** Separada por divisores de linha, com aspas decorativas
- **Função:** Destacar frases importantes do conteúdo

### Boxes Informativos

- **Design:** Caixas com conteúdo destacado
- **Variações:**
  - Science Box: Fundo claro com borda lateral colorida para informações
    científicas
  - Alert Box: Fundo sutil com ícone de alerta para dicas importantes
  - Testimonial Callout: Box com depoimento e autor

### Product Showcase

- **Design:** Destaque para o produto principal com imagem e detalhes
- **Elementos:**
  - Título e subtítulo centralizados
  - Layout flexível com imagem e detalhes
  - Lista de características com ícones
  - Botão de ação secundário

### Success Stories

- **Design:** Grid de histórias de sucesso
- **Estilo:** Cards com efeito hover, ícones temáticos e citações em itálico
- **Funcionalidade:** Efeito de elevação ao passar o mouse

### FAQ Accordion

- **Design:** Lista de perguntas e respostas em estilo accordion
- **Funcionalidade:** Toggle para exibir/ocultar respostas com animação suave
- **Estilo:** Ícone de plus/minus animado, destacando a pergunta ativa

### Seção de Bônus

- **Design:** Box especial para destaque do audiobook como bônus
- **Elementos:** Tag de bônus, título, descrição e imagem ilustrativa
- **Estilo:** Fundo e borda sutilmente coloridos para destaque

### Email Capture Section

- **Design:** Container centralizado com título, subtítulo e formulário
- **Funcionalidade:** Validação de email e integração com API
- **Elementos:**
  - Informação de preço com tachado e desconto
  - Formulário com campo de email e botão
  - Mensagens de feedback (erro/sucesso)
  - Notas de segurança e ícones de pagamento
  - Depoimento final

### Footer

- **Design:** Footer minimalista preto com informações de copyright
- **Elementos:** Links para termos, políticas e contato
- **Estilo:** Tipografia clara sobre fundo escuro, com transições suaves

## Funcionalidades JavaScript

### Animações

- **`initAnimations()`**: Inicializa animações com atraso progressivo para
  diversos elementos da página
- **`setupBookHover()`**: Adiciona efeito 3D à capa do livro baseado na posição
  do mouse
- **`handleScroll()`**: Detecta posição de scroll para animar elementos e
  ajustar estilos do header
- **`addCustomCSS()`**: Adiciona estilos CSS necessários para animações e
  mensagens

### Formulário de Email

- **`setupEmailForm()`**: Configura captura do formulário e validação inicial
- **`isValidEmail()`**: Valida formato de email usando regex
- **`sendEmailToAutonami()`**: Simula envio para API e processa resposta
- **`showLoading()`**: Exibe estado de carregamento no botão de envio
- **`showMessage()`**: Mostra mensagens de sucesso ou erro ao usuário

### Interatividade

- **`setupFAQs()`**: Configura comportamento do acordeão para FAQs
- **`setupFancyHeaderScroll()`**: Altera estilo do header ao rolar a página
- **`pulseCTAButtons()`**: Inicia animação de pulso nos botões CTA após delay

## Melhorias de Design e UX

### Paleta de Cores

- **Base:** Preto e branco como cores principais, refletindo o visual clássico
  de Harper's Bazaar
- **Destaque:** Dourado/marrom como cor de destaque (--color-accent: #bb8c4a)
- **Tons:** Escalas de cinza para hierarquia de informação

### Tipografia

- **Principal:** Bodoni Moda para títulos e elementos de destaque
- **Secundária:** Lato para corpo de texto e elementos funcionais
- **Variações:** Uso estratégico de itálico, peso e tamanho para hierarquia
  visual

### Responsividade

- **Breakpoints:**
  - Desktop: 1200px máximo
  - Tablet: 992px - ajustes de grid e tamanho de fonte
  - Mobile grande: 768px - reorganização de layouts flexíveis
  - Mobile pequeno: 576px - ajustes de formulários e elementos interativos

### Acessibilidade

- **Contrastes:** Garantia de bom contraste entre texto e fundo
- **Tamanhos:** Tipografia adequada para leitura mesmo em dispositivos menores
- **Interação:** Áreas clicáveis com tamanho adequado para toque em dispositivos
  móveis
