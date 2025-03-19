# To Do List - Recepção de Postbacks de Plataformas Externas

## To Fix (deve alterar a funcionalidade pois está incorreta):

## To Do (deve criar a funcionalidade pois está faltando):

## Test (não deve alterar a funcionalidade pois está em fase de teste pelo usuário):
- Implementação da insensibilidade a maiúsculas/minúsculas (case-insensitive) para os status de postback
- Uso do status original quando não houver correspondência no mapeamento configurado

## Done (não deve alterar a funcionalidade pois está correta):
- Corrigido o problema da variável $curLink não definida na função add_postback_log
- Aprimorado o mapeamento de status para suportar variações nas strings recebidas (case-insensitive)
- Garantido que $inner_status nunca fique vazio para prevenir erros no log
- Recepção de postbacks via métodos GET e POST implementada em `postback.php`
- Validação dos parâmetros obrigatórios (subid, status, payout) funcionando corretamente
- Mapeamento de status externos para internos configurável via `settings.json`
- Log detalhado de postbacks recebidos em `/pblogs/[data].pb.log`
- Atualização do status do lead na coleção `leads` do banco de dados
- Envio automático de postbacks S2S quando o status do lead é alterado
- Substituição de macros na URL do postback S2S ({subid}, {status}, etc.)
- Suporte a múltiplos webhooks configuráveis via `settings.json`
- Logs detalhados para facilitar o debugging dos postbacks
- Tratamento de erros com mensagens claras no caso de parâmetros faltantes
- Configuração flexível dos nomes de status via `settings.json`
- Envio de dados adicionais nos postbacks S2S (nome, email, telefone, etc.)
- Suporte a métodos GET e POST para o envio de postbacks S2S 