# Diagrama de Sequência para Captura de Email e Integração com send.php

```mermaid
sequenceDiagram
    participant Usuario as Usuário
    participant LP as Landing Page
    participant Sistema as Sistema
    participant BD as Banco de Dados
    participant Webhook as Webhook Autonami

    %% Carregamento inicial da landing page
    Usuario->>Sistema: Acessa a landing page
    Sistema->>LP: Carrega landing page com formulário de email

    %% Interação do usuário com o formulário
    Usuario->>LP: Preenche formulário de email
    Usuario->>LP: Clica no botão de envio

    %% Processamento do envio do formulário
    LP->>Sistema: Intercepta o envio do formulário (send.php)
    Sistema->>Sistema: Extrai dados do formulário (email, nome)
    Sistema->>Sistema: Gera/Recupera subid para rastreamento

    alt Email válido
        Sistema->>BD: Registra lead (subid, nome, email)
        Sistema->>Sistema: Prepara dados para webhook
    Sistema->>Webhook: Envia dados para webhook
    alt Resposta do Webhook
        Webhook-->>Sistema: Responde com status
        Sistema->>Usuario: Redireciona para página de agradecimento
    else Sem resposta do Webhook
        Sistema->>Usuario: Redireciona para página de agradecimento
    end
    else Email inválido
        Sistema->>Usuario: Retorna erro de email inválido
    end

    %% Mudança de estado: Usuário => Lead registrado
    Note over Usuario,BD: Estado muda de "Usuário visitante" para "Lead registrado"
```

## Detalhes das Requisições e Respostas

### Acesso à Landing Page:

- **Requisição**: O usuário acessa a URL da landing page.
- **Processamento**: O sistema carrega a landing page com o formulário de email.
- **Resposta**: A landing page é exibida com o formulário de email.

### Envio do Formulário:

- **Requisição**: Usuário submete o formulário com dados como email e nome.
- **Dados**:
  ```
  {
    "email": "usuario@example.com",
    "name": "Nome do Usuário"
  }
  ```
- **Processamento**:
  1. O sistema intercepta o envio através do arquivo `send.php`.
  2. Extrai dados como email e nome do formulário.
  3. Gera/recupera o subid para rastreamento.

### Registro de Lead e Envio para Webhook:

- **Dados**:
  ```
  {
    "subid": "abc123xyz789",
    "email": "usuario@example.com",
    "name": "Nome do Usuário"
  }
  ```
- **Processamento**:
  1. Registra o lead no banco de dados.
  2. Prepara os dados para o webhook.
  3. Envia os dados para o webhook Autonami.
  4. Recebe a resposta do webhook e redireciona o usuário para a página de agradecimento.

### Configuração e Requisitos

1. **Validação de Email**:
   - O sistema deve validar o email antes de processar o lead.

2. **Integração com Webhook**:
   - O sistema deve enviar os dados do lead para o webhook especificado.

3. **Rastreamento**:
   - O subid é usado para rastrear a origem do lead.
