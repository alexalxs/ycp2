# Resumo dos Status de Leads: Hold, Reject e Trash

## Resultados dos Testes e Análise

A partir dos testes realizados e da análise do código-fonte, verificamos como os diferentes estados de leads são contabilizados e processados no sistema.

### 1. Flow de Status do Lead

Realizamos os seguintes testes:

1. **Criação de Lead**: Criamos um lead de teste via API
   - O lead foi registrado automaticamente com status "123456789" (problema de validação)
   - Foi contabilizado como Conversão nas estatísticas

2. **Atualização para Reject**: 
   - Utilizamos o endpoint `postback.php` para atualizar o status para "Reject"
   - O status do lead foi atualizado no banco de dados
   - Na interface de estatísticas, o contador de "Reject" foi incrementado de 0 para 1

3. **Atualização para Trash**:
   - Alteramos o status para "Trash" via postback
   - O status do lead foi atualizado no banco de dados
   - Na interface de estatísticas, o contador de "Trash" foi incrementado de 0 para 1
   - Observamos que o contador de "Reject" voltou para 0, indicando que o sistema rastreia o último estado

### 2. Efeito nas Métricas

Quando alteramos o status para "Trash", notamos mudanças nas métricas calculadas:
- **App% (w/o trash)**: Aumentou de 11.11% para 12.50%
- **App% (total)**: Permaneceu em 11.11%

Isso confirma que a métrica "App% (w/o trash)" exclui leads classificados como "Trash" ao calcular a taxa de aprovação.

## Explicação dos Status

### Hold (Lead)

No sistema, o estado "Hold" é representado pelo status "Lead". Este é o estado inicial de qualquer lead registrado.

- **Comportamento observado**: 
  - Leads novos são registrados como "Lead" por padrão
  - Representam leads que ainda não foram processados completamente pela plataforma externa
  - Permanecem neste estado até que um postback atualize seu status

### Reject

O status "Reject" indica leads que foram explicitamente rejeitados.

- **Comportamento observado**:
  - O status só muda para "Reject" via postback específico
  - Representa leads válidos que não atenderam a critérios específicos da plataforma
  - Afeta diretamente as métricas de aprovação no sistema

### Trash

O status "Trash" representa leads considerados inválidos ou fraudulentos.

- **Comportamento observado**:
  - Também só é atualizado via postback
  - Leads neste estado são excluídos de certas métricas de performance (App% w/o trash)
  - Representa leads de baixa qualidade que não devem ser considerados para análise

## Problemas Identificados

1. **Validação Inicial**: Nosso teste mostrou que leads criados diretamente via API não passam por validação adequada de status, sendo registrados com valores incorretos como "123456789"

2. **Contabilização Inconsistente**: Ao mudar o status de "Reject" para "Trash", o contador de "Reject" foi zerado, indicando que o sistema não mantém um histórico preciso de transições de estado

3. **Falta de Notificação**: Não há mecanismo visível para notificar usuários sobre mudanças de status nos leads

## Recomendações

1. **Implementar Validação**: Adicionar validação de status na criação de leads para garantir que apenas valores válidos ("Lead", "Purchase", "Reject", "Trash") sejam aceitos

2. **Histórico de Estados**: Implementar um sistema de log para rastrear todas as mudanças de estado dos leads

3. **Dashboard de Monitoramento**: Criar um painel específico para monitorar transições de estado e identificar padrões problemáticos

4. **Automatização**: Implementar regras automáticas para classificação de leads com base em critérios específicos, reduzindo a necessidade de atualização manual via postbacks 