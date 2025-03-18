# To Do List - Integração de Postback S2S para Registro de Leads

## To Fix (deve alterar a funcionalidade pois está incorreta):
- ✅ A função `add_lead()` em `db.php` não possui lógica para enviar postbacks S2S
- O arquivo `postback.php` está configurado apenas para receber postbacks, não para enviar
- ✅ Não há comunicação entre o registro de lead e o webhook configurado em `settings.json`

## To Do (deve criar a funcionalidade pois está faltando):
- ✅ Criar uma função `trigger_s2s_postback()` que envia dados para webhooks configurados
- ✅ Modificar a função `add_lead()` para chamar a função de postback após o registro do lead
- Implementar um mecanismo de fila e retry para garantir que os postbacks sejam enviados mesmo em caso de falhas temporárias
- ✅ Adicionar logs específicos para o envio de postbacks para facilitar o debugging
- ✅ Criar uma pasta para armazenar logs de postbacks caso não exista (`/pblogs/`)

## Implementado:
- Função `trigger_s2s_postback()` criada em `db.php` para enviar postbacks S2S para webhooks
- Modificação de `add_lead()`, `update_lead()`, `update_lead_status()` e `add_email()` para acionar postbacks
- Logs de postbacks criados na pasta `/pblogs/` (criada automaticamente se não existir)
- Substituição de macros na URL do postback: `{subid}`, `{prelanding}`, `{landing}`, `{domain}`, `{status}`
- Tratamento adequado de métodos GET e POST para envio de postbacks
- Registro de logs detalhados de sucesso e falha no envio de postbacks

## Test (não deve alterar a funcionalidade pois está em fase de teste pelo usuário):
- A captura e registro de leads no banco de dados local está funcionando corretamente
- A configuração de postbacks em `settings.json` está correta
- A redireção para a página de agradecimento funciona adequadamente
- Testes com diferentes navegadores e dispositivos mostraram comportamento consistente
- ✅ Verificar se os postbacks S2S estão sendo enviados verificando os logs em `/pblogs/`

## Done (não deve alterar a funcionalidade pois está correta):
- O formulário de captura de dados na landing page está funcionando corretamente
- A estrutura de armazenamento de leads no banco de dados JSON está adequada
- A configuração de postbacks no arquivo `settings.json` está definida corretamente
- A página de agradecimento (`thankyou.php`) está sendo exibida após a submissão do formulário
- As estatísticas de leads estão sendo registradas no painel de administração 
- ✅ Integração da função `trigger_s2s_postback()` com todas as funções relevantes de leads 