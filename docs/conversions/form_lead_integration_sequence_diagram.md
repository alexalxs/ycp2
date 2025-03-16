# Diagrama de Sequência para Integração de Formulário na Landing Page

```mermaid
sequenceDiagram
    participant Usuario as Usuário
    participant LP as Landing Page
    participant Sistema as Sistema
    participant BD as Banco de Dados/JSON Mockup
    participant Form as Formulário Original
    participant PP as Provedor de Pagamento/Programa de Parceiros

    %% Carregamento inicial da landing page
    Usuario->>Sistema: Acessa a landing page
    Sistema->>Sistema: Verifica se é usuário ou bot (TDS)
    alt É usuário real
        Sistema->>LP: Carrega landing page com formulário
        Note over Sistema,LP: O sistema carrega a landing page sem modificar os elementos do formulário
    else É bot/moderador
        Sistema->>Usuario: Redireciona para white page
    end
    
    %% Interação do usuário com o formulário
    Usuario->>LP: Preenche formulário (nome, telefone, etc.)
    Usuario->>LP: Clica no botão de envio
    
    %% Processamento do envio do formulário
    LP->>Sistema: Intercepta o envio do formulário (send.php)
    Sistema->>Sistema: Extrai dados do formulário (nome, telefone)
    Sistema->>Sistema: Gera/Recupera subid para rastreamento
    Sistema->>Sistema: Verifica duplicidade via cookies
    
    alt Não é duplicado
        Sistema->>BD: Registra lead (subid, nome, telefone, timestamp)
        Sistema->>Sistema: Define cookies de conversão (nome, telefone, timestamp)
        
        alt Formulário com URL externa
            Sistema->>Form: Envia dados para script original do formulário
            Form->>PP: Transmite dados para o provedor de pagamento
            PP-->>Form: Responde com URL de redirecionamento ou página de agradecimento
            
            alt Resposta com redirecionamento (302)
                Form-->>Sistema: Retorna URL de redirecionamento
                alt Usa página de agradecimento personalizada
                    Sistema->>Usuario: Redireciona para página de agradecimento personalizada
                else Usa redirecionamento padrão do PP
                    Sistema->>Usuario: Redireciona para URL fornecida pelo PP
                end
            else Resposta com HTML (200)
                Form-->>Sistema: Retorna HTML da página de agradecimento
                alt Usa página de agradecimento personalizada
                    Sistema->>Usuario: Carrega página de agradecimento personalizada
                else Usa HTML de agradecimento do PP
                    Sistema->>Usuario: Exibe HTML de agradecimento do PP
                end
            end
            
        else Formulário com script local
            Sistema->>Form: Constrói caminho para o script local de conversão
            Sistema->>Form: Envia dados para script local
            Form->>PP: Transmite dados para o provedor de pagamento
            PP-->>Form: Responde com status
            Form-->>Sistema: Retorna resultado do processamento
            Sistema->>Usuario: Redireciona para página de agradecimento personalizada
        end
    else É duplicado
        Sistema->>Usuario: Redireciona para página de agradecimento (sem ativar pixels)
    end
    
    %% Mudança de estado: Usuário => Lead registrado
    Note over Usuario,BD: Estado muda de "Usuário visitante" para "Lead registrado"
```

## Documentação de Suporte

### Papéis e Responsabilidades

- **Usuário**: Preenche o formulário na landing page para registrar seu
  interesse.
- **Sistema**: Responsável por interceptar o envio do formulário, processar os
  dados e garantir que sejam enviados ao script original sem modificar os
  elementos do formulário.
- **Landing Page (LP)**: Página contendo o formulário de captura de leads.
- **Formulário Original**: O formulário original que existe na landing page,
  cujos elementos não devem ser alterados.
- **Banco de Dados/JSON Mockup (BD)**: Armazena os dados de leads para
  referência futura.
- **Provedor de Pagamento/Programa de Parceiros (PP)**: Sistema externo que
  recebe os dados do formulário.

### Detalhes das Requisições e Respostas

#### Acesso à Landing Page:

- **Requisição**: O usuário acessa a URL da landing page.
- **Processamento**: O sistema verifica se é um usuário real ou bot através do
  TDS (Traffic Distribution System).
- **Resposta**: Para usuários reais, carrega a landing page com o formulário
  intacto. Para bots, redireciona para white page.

#### Envio do Formulário:

- **Requisição**: Usuário submete o formulário com dados como nome e telefone.
- **Dados**:
  ```
  {
    "name": "Nome do Usuário",
    "phone": "+55123456789",
    ... (outros campos do formulário)
  }
  ```
- **Processamento**:
  1. O sistema intercepta o envio através do arquivo `send.php`
  2. Extrai dados como nome e telefone de diferentes possíveis campos do
     formulário
  3. Gera/recupera o subid para rastreamento
  4. Verifica se o lead já foi registrado anteriormente usando cookies

#### Registro de Lead (para leads não duplicados):

- **Dados**:
  ```
  {
    "subid": "abc123xyz789",
    "name": "Nome do Usuário",
    "phone": "+55123456789",
    "timestamp": 1647432871
  }
  ```
- **Cookies Definidos**:
  - `name`: Nome do usuário
  - `phone`: Telefone do usuário
  - `ctime`: Timestamp da conversão

#### Processamento Baseado no Tipo de Formulário:

- **Para formulários com URL externa**:
  - **Requisição para PP**: Envia todos os dados do formulário para a URL
    externa
  - **Resposta possível (302)**: PP responde com URL de redirecionamento
  - **Resposta possível (200)**: PP responde com HTML da página de agradecimento

- **Para formulários com script local**:
  - **Requisição para script local**: Constrói caminho relativo e envia dados
  - **Processamento**: Script local processa os dados e comunica com o PP

### Configuração e Requisitos

1. **Não alteração dos elementos do formulário**:
   - O sistema deve processar o formulário sem modificar seus campos ou
     atributos visuais
   - A integração ocorre pela interceptação do envio, não pela modificação do
     HTML

2. **Reutilização do script original**:
   - O sistema deve continuar usando o script original de processamento do
     formulário
   - Se o action do formulário for uma URL externa, os dados são enviados para
     esta URL
   - Se for um script local, o sistema constrói o caminho completo e envia os
     dados para ele

3. **Tratamento de duplicidades**:
   - O sistema identifica leads duplicados através de cookies
   - Leads duplicados são redirecionados para a página de agradecimento sem
     reenvio ao PP

4. **Rastreamento**:
   - O subid é usado para rastrear a origem do lead
   - O sistema verifica se o subid está presente nos dados do formulário antes
     do envio

```
```
