# Checklist para Integração de Formulário na Landing Page

## Resumo do Fluxo de Funcionamento

1. O usuário acessa a landing page através do sistema
2. O sistema exibe a landing page com o formulário original sem modificações
3. O usuário preenche e envia o formulário
4. O sistema intercepta o envio através do arquivo `send.php`
5. Os dados do formulário são extraídos e um subid é associado
6. O sistema verifica se é um lead duplicado usando cookies
7. Para leads não duplicados:
   - Registra o lead no banco de dados
   - Define cookies com os dados do lead
   - Envia os dados para o script original do formulário
   - Redireciona para a página de agradecimento adequada
8. Para leads duplicados:
   - Redireciona diretamente para a página de agradecimento