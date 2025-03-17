# Lista de Tarefas: Prelanding com Ação "folder"

Este documento lista as tarefas relacionadas à funcionalidade de prelanding com
configuração "folder", destacando o que já foi implementado, o que precisa ser
corrigido, o que está pendente e o que está em fase de teste.

# To Fix (deve alterar a funcionalidade pois está incorreta):

# To Do (deve criar a funcionalidade pois está faltando):

# Test (não deve alterar a funcionalidade pois está em fase de teste pelo usuário):

# Done (não deve alterar a funcionalidade pois está correta):

- Exibição da página de prelanding diretamente na URL raiz quando a opção
  "folder" está configurada
- Seleção aleatória da prelanding a partir da lista configurada em
  `black_preland_folder_names`
- Suporte para servir todos os recursos necessários (CSS, JS, imagens) a partir
  da pasta de prelanding
- Adição automática da tag `<base>` para garantir que links relativos funcionem
  corretamente
- Implementação de script para rastreamento de cliques no botão CTA via
  `buttonlog.php`
- Redirecionamento para a landing page após o clique no botão CTA
- Manutenção da compatibilidade com o sistema de teste A/B entre diferentes
  prelandings
- Resposta correta para requisições de arquivos que não existem (erro 404)
- Definição adequada de tipos MIME para arquivos servidos
- Suporte para inclusão de arquivos PHP dentro da pasta prelanding
- Processamento correto de cabeçalhos HTTP para recursos estáticos e dinâmicos
- Persistência da prelanding selecionada via cookie para manter consistência
  entre sessões, com suporte para teste A/B quando não há cookie definido
- Registro de visualização da prelanding para estatísticas com subID
- Registro de eventos de clique em log dedicado para análise posterior
- Corrigido o warning "Undefined array key" na página de estatísticas
  (statistics.php linha 157) adicionando uma verificação para garantir que a
  chave existe antes de acessá-la.
- Ao abrir a prelanding o valor de Traffic é adicionado corretamente,
  registrando apenas uma vez por visitante através do cookie 'visited_' para
  evitar contagem dupla.
- Corrigido o erro na página de estatísticas "The number of successful actions
  should not exceed the total number of actions" que impedia a visualização das
  estatísticas. A correção garante que o número de ações bem-sucedidas nunca
  excede o número total de ações.
- Ao abrir a landing os valores de Clicks são adicionados corretamente,
  registrando apenas uma vez por visitante através do cookie 'visited_landing_'
  para evitar contagem dupla.
- Corrigida a atribuição incorreta de clicks como "unknown" na coluna Landing em
  estatística, garantindo que o nome correto da landing seja registrado ao
  clicar no botão da prelanding.
- Implementado suporte para especificação direta da landing no payload JSON ao
  enviar o evento de click para buttonlog.php, permitindo maior controle sobre o
  destino do usuário e melhorando a precisão das estatísticas.
- Corrigido o problema na exibição de estatísticas que mostrava apenas 1 clique
  para landing mesmo quando havia mais registros. A correção agora utiliza todos
  os dados de LPCTR para calcular os totais precisos de cliques para cada
  landing.

## Implementações Específicas

### Modificações no index.php

1. **Verificação de Configuração Prelanding Primeiro**
   - Modificamos o fluxo de verificação para dar prioridade à configuração de
     prelanding
   - O sistema agora verifica `$black_preland_action === 'folder'` antes de
     verificar a landing

2. **Melhoria na função serve_file()**
   - Normalização de caminhos para prevenir directory traversal
   - Suporte para diversos tipos MIME
   - Tratamento especial para arquivos HTML com adição de tag base
   - Adição de script de tracking de cliques para elementos com id="ctaButton"
   - Correção do JavaScript para processar a resposta JSON e redirecionar
     corretamente

3. **Tratamento de Cookies e Testes A/B**
   - Uso de função `ywbsetcookie()` para definir cookie 'prelanding'
   - Persistência de seleção entre sessões quando $save_user_flow está ativado
   - Suporte a parâmetro key na URL para forçar uma prelanding específica
   - Seleção aleatória para teste A/B quando não há cookie ou $save_user_flow
     está desativado

4. **Controle de Contagem de Acessos**
   - Implementação de cookies 'visited_' para rastrear visualizações únicas em
     prelandings
   - Implementação de cookies 'visited_landing_' para rastrear visualizações
     únicas em landings
   - Prevenção de contagem duplicada tanto no Traffic quanto nos Clicks

### Implementação do buttonlog.php

1. **Criação e Registro de Logs**
   - Criação de diretório `/logs` se não existir
   - Registro de eventos em `/logs/button_clicks.log` com subID
   - Registro detalhado do timestamp do servidor e do cliente

2. **Processamento de Requisições POST**
   - Captura de dados JSON enviados pelo cliente
   - Extração de informações: evento, prelanding, timestamp
   - Validação dos dados recebidos
   - Suporte para especificação direta da landing no payload JSON

3. **Redirecionamento para Landing**
   - Seleção de landing page baseada na configuração do sistema
   - Suporte para consistência de seleção via cookies quando $save_user_flow
     está ativado
   - Teste A/B entre landings quando não há cookie ou $save_user_flow está
     desativado
   - Compatibilidade com landing especificada diretamente no payload
   - Verificação adicional para garantir que a landing selecionada é válida
   - Retorno de JSON com URL de redirecionamento

4. **Integração com Sistema de Estatísticas**
   - Registro de cliques na tabela LPCTR para análise de taxa de conversão
   - Correção do problema de contagem dupla (Traffic e LP Clicks)
   - Registro apenas do clique LP sem afetar o contador de Traffic
   - Associação correta entre prelanding e landing na tabela de estatísticas
   - Dados visíveis corretamente no painel de administração com CTR calculado
     adequadamente

### Melhorias nas Estatísticas

1. **Contabilização Precisa de Cliques**
   - Uso de todos os registros LPCTR disponíveis para calcular totais precisos
   - Garantia de que cada landing configurada apareça nas estatísticas, mesmo
     sem cliques
   - Correção na forma como os cliques são contados, sem filtrar por data
   - Evita inconsistências entre a contagem real e a exibida no painel
