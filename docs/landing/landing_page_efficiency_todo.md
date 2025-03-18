# To Do List - Análise de Eficiência de Landing Pages para Geração de Leads

## To Fix (deve alterar a funcionalidade pois está incorreta):

## To Do (deve criar a funcionalidade pois está faltando):
- Adicionar código para calcular o custo por lead para cada landing page (quando dados de custo estiverem disponíveis)

## Test (não deve alterar a funcionalidade pois está em fase de teste pelo usuário):

## Done (não deve alterar a funcionalidade pois está correta):
- Funcionalidade básica de rastreamento de leads já implementada e operacional
- Sistema de registro de leads no banco de dados funcionando corretamente
- Página de estatísticas `/admin/statistics.php` exibe dados gerais de conversão
- Implementação de testes A/B entre landing pages com resultados visíveis na tabela "Landing"
- Cálculo de taxa de conversão (CR%) para cada landing page já implementado
- Tabela comparativa entre diferentes landing pages já disponível em `/admin/statistics.php`
- Funcionalidade "Is Best%" que indica probabilisticamente qual landing page tem melhor desempenho
- Estrutura de dados adequada para armazenar e recuperar métricas por landing page
- Sistema de rastreamento de leads vinculados às landing pages específicas
- Processamento e validação dos dados do formulário funcionam corretamente
- Métricas separadas para diferentes tipos de conversão (Lead, Purchase, Reject, Trash)
- Página dedicada `/admin/landing_efficiency.php` que apresenta dados comparativos entre diferentes landing pages
- Visualização em gráficos de barras para facilitar a comparação visual das métricas de conversão entre landing pages
- Adicionados filtros por período (dia, semana, mês, últimos 30 dias, personalizado) para análise mais granular de desempenho
- Implementada exportação de dados em CSV para análise externa
- Criado um painel visual com indicadores de desempenho (KPIs) para cada landing page
- Implementado um sistema de classificação automática das landing pages baseado em taxa de conversão
- Cálculo de probabilidade bayesiana para determinar qual landing page tem melhor desempenho
- Visualização de dados em gráficos circulares (pie/doughnut) para comparação de distribuição de leads e cliques
- Interface intuitiva com opção de ordenação por diferentes métricas (cliques, leads, taxa de conversão)
- Destaque visual para a landing page com melhor desempenho
- Adicionado link no painel original (statistics.php) enviando para a página de análise de eficiência 