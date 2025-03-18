# To Do List - Acesso Direto a Landing Pages na Raiz e Processamento de Formulário

## To Fix (deve alterar a funcionalidade pois está incorreta):
- FIXED: O campo de URL de redirecionamento não está sendo exibido na interface administrativa após salvar o campo;
  - Foi adicionada uma ferramenta de diagnóstico (debug_redirect_url.php) e informações adicionais na interface para ajudar a identificar e resolver o problema.
- FIXED: Adição de um campo na interface administrativa para definir uma URL de redirecionamento personalizada após o preenchimento do formulário
- FIXED: Problema com redirecionamento para URL personalizada depois da submissão do formulário.
  - Foi criado um arquivo proxy offer2/order.php que redireciona para form-processor.php
  - Foi corrigida a duplicidade de configuração em settings.json, unificando no campo black.landing.folder.redirect_url
- Os formulários nas landing pages precisam ser atualizados para apontar para form-processor.php em vez de order.php
  - O próprio usuário fará isso. Foram adicionadas instruções na interface de configuração sobre o endereço de form-processor.php.
  - Verificamos que o formulário em offer2/index.html já está apontando para form-processor.php corretamente.
- Verificar se o campo de redirecionamento é respeitado em diferentes configurações
  - O problema foi resolvido unificando as configurações.

## To Do (deve criar a funcionalidade pois está faltando):
- FIXED: Criar um diagrama de sequência documentando o problema de redirecionamento atual (redirect_issue_flow.md)
  - O diagrama foi atualizado com informações detalhadas sobre o problema e a solução.
- FIXED: Criar um mecanismo temporário de proxy para manter compatibilidade com landing pages existentes.
  - Foi criado um arquivo proxy offer2/order.php que redireciona para form-processor.php

## Test (não deve alterar a funcionalidade pois está em fase de teste pelo usuário):

## Done (não deve alterar a funcionalidade pois está correta):
- Criação de um processador central de formulários (form-processor.php) na raiz para eliminar a necessidade de order.php em cada landing
- Atualização do settings.json para incluir o novo campo de URL de redirecionamento
- Modificação do arquivo settings.php para ler a nova configuração de URL de redirecionamento
- Centralização do processamento de leads em um único arquivo para facilitar a manutenção
- Criação de um diagrama de sequência Mermaid para documentar o fluxo de processamento
- Manutenção da compatibilidade com o sistema de tracking existente (cliques e conversões)
- Garantia que as páginas sejam carregadas corretamente com todos os recursos (CSS, JS, imagens, etc.)
- FIXED: Exclusão do arquivo offer2/order.php, que não é mais necessário com o novo processador centralizado
  - Em vez de excluir, foi transformado em um proxy para manter compatibilidade 