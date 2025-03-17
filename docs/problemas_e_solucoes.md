# Problemas Encontrados e Soluções Implementadas

## Problema 1: Prelanding com Ação "folder" não carregava corretamente

**Descrição do Problema:** Quando a configuração `prelanding.action` era
definida como "folder" no `settings.json`, o sistema deveria exibir as páginas
de prelanding diretamente na URL raiz, mas ao invés disso estava redirecionando
para páginas de landing.

**Solução:** Ajuste do caminho de processamento no sistema de roteamento para
servir corretamente as páginas de prelanding quando a ação "folder" está
configurada. Foi necessário verificar e atualizar o fluxo de decisão no sistema
para garantir que a configuração correta fosse aplicada.

## Problema 2: Contagem Duplicada de Cliques em Botões de Prelanding

**Descrição do Problema:** Quando um usuário clicava no botão da página de
prelanding, o sistema estava registrando múltiplos cliques ao invés de apenas
um. Isso resultava em estatísticas de LP Clicks incorretas, apresentando valores
maiores que o esperado.

**Causa Identificada:**

1. Duplicidade de chamadas - O botão tinha dois gatilhos diferentes para
   registrar o evento:
   - Um script embutido na página prelanding que enviava uma requisição ao
     `buttonlog.php`
   - Um script injetado pelo `index.php` quando servia a página HTML que também
     registrava o clique

**Solução Implementada:**

1. **Remoção de Código Duplicado:**
   - Removido o script redundante de registro de cliques no botão das páginas
     `preland1/index.html` e `preland2/index.html`

2. **Implementação de Proteção Contra Cliques Múltiplos:**
   - Adicionado um mecanismo de proteção no arquivo `buttonlog.php` que verifica
     se um clique já foi registrado nos últimos 2 segundos
   - Utilização de cookies para armazenar o timestamp do último clique
   - Descarte de requisições consecutivas muito próximas (menos de 2 segundos)

**Resultados:**

- As estatísticas agora mostram um incremento consistente de 1 para cada clique
  real do usuário
- Os valores de LP Clicks nas estatísticas estão mais precisos e refletem o
  comportamento real dos usuários
- Não há mais registros duplicados no log de cliques

## Problema 3: Redirecionamento Inconsistente após Clique no Botão

**Descrição do Problema:** Ao clicar no botão de prelanding, o sistema às vezes
redirecionava para uma landing page diferente da especificada no link do botão.

**Causa Identificada:** O sistema estava escolhendo aleatoriamente uma landing
page ao processar o evento de clique, independentemente da landing page
especificada no atributo `href` do botão.

**Solução:** A funcionalidade foi mantida para permitir testes A/B de landing
pages, mas foi documentada para maior clareza. O comportamento atual é:

1. Quando o cookie `landing` já existe e `save_user_flow` está ativado, o
   sistema mantém o usuário na mesma landing page
2. Caso contrário, seleciona aleatoriamente uma landing page das configuradas em
   `black_land_folder_names`

## Monitoramento e Melhorias Futuras

- Continuar monitorando o comportamento do sistema para garantir que as
  estatísticas sejam precisas
- Considerar implementar um sistema de rate limiting mais robusto para proteger
  contra abusos
- Documentar claramente o comportamento do sistema de redirecionamento para os
  usuários
