# Documentação: Sistema de Postback e Integração com Plataformas Externas

## Visão Geral

O sistema de postback implementado permite a comunicação entre o sistema de captura de leads e plataformas externas de pagamento ou gerenciamento, como o WooCommerce. Essa integração é essencial para sincronizar dados de leads e compras entre diferentes sistemas.

## Arquivo `postback.php`

### Descrição
O arquivo `postback.php` é responsável por receber notificações de plataformas externas e atualizar o status dos leads no sistema interno.

### Parâmetros de Entrada

#### Método: GET ou POST

| Parâmetro | Tipo     | Obrigatório | Descrição                                                            |
|-----------|----------|-------------|----------------------------------------------------------------------|
| subid     | string   | Sim         | Identificador único do lead no sistema                               |
| status    | string   | Sim         | Novo status do lead (Lead, Purchase, Reject, Trash)                  |
| payout    | float    | Sim         | Valor do pagamento (relevante para status Purchase)                  |

### Valores de Status

O sistema mapeia os valores de status externos para status internos da seguinte forma:

```php
switch ($status) {
    case $lead_status_name:       // Configurável em settings.json
        $inner_status = 'Lead';
        break;
    case $purchase_status_name:   // Configurável em settings.json
        $inner_status = 'Purchase';
        break;
    case $reject_status_name:     // Configurável em settings.json
        $inner_status = 'Reject';
        break;
    case $trash_status_name:      // Configurável em settings.json
        $inner_status = 'Trash';
        break;
    default:
        // Case-insensitive matching (implementação melhorada)
        if (strcasecmp($status, "lead") === 0 || strcasecmp($status, $lead_status_name) === 0) {
            $inner_status = 'Lead';
        } else if (strcasecmp($status, "purchase") === 0 || strcasecmp($status, $purchase_status_name) === 0) {
            $inner_status = 'Purchase';
        } else if (strcasecmp($status, "reject") === 0 || strcasecmp($status, $reject_status_name) === 0) {
            $inner_status = 'Reject';
        } else if (strcasecmp($status, "trash") === 0 || strcasecmp($status, $trash_status_name) === 0) {
            $inner_status = 'Trash';
        } else {
            // Usa o status original se não houver correspondência
            $inner_status = $status;
        }
        break;
}
```

### Processamento Inteligente de Status

O sistema realiza um processamento inteligente dos status recebidos:

1. **Correspondência exata**: Primeiro tenta corresponder exatamente com os valores configurados
2. **Correspondência case-insensitive**: Se não encontrar, verifica ignorando maiúsculas/minúsculas
3. **Fallback para o valor original**: Se ainda não encontrar correspondência, usa o valor original recebido

Este comportamento garante que o sistema funcione mesmo com pequenas variações nos valores de status enviados por diferentes plataformas.

### Exemplo de Uso com cURL

```bash
# Exemplo de notificação de compra
curl -X POST "https://seu-dominio.com/postback.php" \
  -d "subid=abc123xyz456" \
  -d "status=purchase" \
  -d "payout=97.50"

# Exemplo de notificação de rejeição
curl -X GET "https://seu-dominio.com/postback.php?subid=abc123xyz456&status=reject&payout=0"
```

### Exemplo para Postman

**POST Request**:
- URL: `https://seu-dominio.com/postback.php`
- Method: POST
- Body (form-data):
  - subid: abc123xyz456
  - status: purchase
  - payout: 97.50

**GET Request**:
- URL: `https://seu-dominio.com/postback.php?subid=abc123xyz456&status=reject&payout=0`
- Method: GET

## Envio de Postbacks para Plataformas Externas

### Função `trigger_s2s_postback()`

O sistema também envia notificações para webhooks externos quando um lead é registrado ou seu status é alterado, através da função `trigger_s2s_postback()`.

#### Parâmetros Enviados

| Parâmetro   | Tipo   | Descrição                                   |
|-------------|--------|---------------------------------------------|
| subid       | string | Identificador único do lead                 |
| status      | string | Status atual do lead (Lead, Purchase, etc.) |
| prelanding  | string | Página de pré-landing visitada              |
| landing     | string | Página de landing visitada                  |
| name        | string | Nome do lead (se disponível)                |
| email       | string | Email do lead (se disponível)               |
| phone       | string | Telefone do lead (se disponível)            |

### Configuração de Webhooks

Os webhooks são configurados no arquivo `settings.json`, na seção `postback.s2s`:

```json
"postback": {
  "s2s": [
    {
      "url": "https://webhook-externo.com/api/callback",
      "method": "POST",
      "events": ["Lead", "Purchase", "Reject", "Trash"]
    }
  ]
}
```

Para cada webhook configurado, o sistema:
1. Verifica se o evento atual (status) está na lista de eventos do webhook
2. Substitui macros como `{subid}`, `{status}`, etc. na URL
3. Envia os dados usando o método especificado (GET ou POST)
4. Registra o resultado em arquivos de log

### Exemplo de Dados Enviados (POST)

```json
{
  "subid": "abc123xyz456",
  "status": "Lead",
  "prelanding": "preland1",
  "landing": "offer2",
  "name": "João Silva",
  "email": "joao@example.com",
  "phone": "5511999887766"
}
```

## Integração com WooCommerce

Para integrar com o WooCommerce, configure um webhook na seção "Webhooks" do WooCommerce com:

1. Nome: Notificação de Status do Pedido
2. Status: Ativo
3. Tópico: Pedido atualizado
4. URL de entrega: `https://seu-dominio.com/postback.php`
5. Segredo: (opcional, mas recomendado)

Observe que para o WooCommerce, você precisará também implementar um mapeamento entre os status de pedidos do WooCommerce e os status do seu sistema. Por exemplo:

- WooCommerce "processing" → status "lead"
- WooCommerce "completed" → status "purchase"
- WooCommerce "cancelled" → status "reject"
- WooCommerce "failed" → status "trash"

## Logs e Depuração

Os logs de postback são armazenados em:

- Log básico: `/pblogs/DD.MM.YY.pb.log`
- Log detalhado: `/pblogs/DD.MM.YY.pb.detailed.log`

O log detalhado inclui:
- Dados enviados/recebidos
- Respostas HTTP completas
- Mensagens de erro quando aplicável

## Resolução de Problemas

1. **Postback não recebido**: Verifique os logs do servidor web para confirmar se a requisição chegou.
2. **Status não atualizado**: Verifique se o `subid` está correto e existe no sistema.
3. **Webhook não acionado**: Verifique os logs em `/pblogs/` para identificar possíveis erros.
4. **Erro de formato**: Confirme que todos os parâmetros obrigatórios estão sendo enviados. 