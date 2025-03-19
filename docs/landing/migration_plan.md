# Plano de Migração: Do order.php para form-processor.php

## Objetivo
Este documento descreve o plano para migrar o processamento de formulários das landing pages do modelo atual (com order.php em cada landing) para o novo modelo centralizado (usando form-processor.php na raiz).

## Situação Atual
- Cada landing page possui seu próprio arquivo order.php que processa os formulários
- Os formulários HTML nas landing pages apontam para "order.php" como destino
- Após o processamento, o usuário é redirecionado para thankyou.php ou thankyou.html
- Um campo de URL de redirecionamento personalizado foi adicionado às configurações, mas não está sendo utilizado pelos arquivos order.php originais

## Abordagem de Migração
Para garantir uma transição suave sem interrupções no funcionamento das landing pages, seguiremos uma abordagem em duas fases:

### Fase 1: Compatibilidade Dupla (Curto Prazo)
1. **Manter os arquivos order.php nas landing pages existentes**
   - Atualizar cada arquivo order.php para verificar o campo redirect_url nas configurações
   - Garantir que o comportamento seja idêntico ao do form-processor.php

2. **Criar um mecanismo de proxy**
   - Fazer com que os arquivos order.php existentes redirecionem as requisições para form-processor.php
   - Manter registros para análise e depuração

3. **Atualizar a interface administrativa**
   - Garantir que o campo redirect_url seja exibido corretamente na interface
   - Adicionar uma nota informando sobre a transição em andamento

### Fase 2: Migração Completa (Médio Prazo)
1. **Atualizar as landing pages**
   - Modificar os formulários HTML para apontar para "/form-processor.php" em vez de "order.php"
   - Iniciar com as landing pages mais recentes e menos usadas

2. **Implementar um fallback automático**
   - Configurar o form-processor.php para processar requisições de ambos os formatos
   - Adicionar logs para identificar landing pages que ainda usam o formato antigo

3. **Remover gradualmente os arquivos order.php**
   - Após confirmar que uma landing page foi migrada com sucesso, remover seu arquivo order.php
   - Monitorar logs de erros para detectar problemas

## Cronograma
- **Semana 1**: Preparação e implementação da Fase 1
- **Semanas 2-3**: Migração de landing pages de teste para o novo formato
- **Semanas 4-6**: Migração gradual de todas as landing pages restantes
- **Semana 7**: Verificação final e remoção de código legado

## Riscos e Mitigação
| Risco | Mitigação |
|-------|-----------|
| Interrupção no processamento de formulários | Testes abrangentes antes da implantação e sistema de fallback robusto |
| Perda de dados de leads | Registro duplicado temporário (tanto no sistema antigo quanto no novo) |
| Confusão para os desenvolvedores de landing pages | Documentação clara e período de transição generoso |

## Monitoramento
- Implementar logs detalhados para rastrear a origem das requisições
- Criar um painel para acompanhar o progresso da migração
- Configurar alertas para falhas no processamento de formulários

## Conclusão
Esta abordagem gradual garante que o sistema continue funcionando durante a transição, minimizando riscos e permitindo reverter rapidamente caso sejam encontrados problemas. 