```mermaid
sequenceDiagram
    participant Usuario as Usuário
    participant Navegador as Navegador
    participant Index as index.php
    participant Landingpage as Landing Page HTML
    participant Formulario as Formulário
    participant Order as order.php
    participant FormProcessor as form-processor.php
    participant Settings as settings.php
    participant SettingsJSON as settings.json
    participant Thankyou as thankyou.php
    
    Usuario->>Navegador: Acessa a URL raiz
    Navegador->>Index: GET /
    Index->>Settings: Carrega configurações
    Settings->>SettingsJSON: Lê settings.json
    SettingsJSON-->>Settings: Retorna configurações
    Settings-->>Index: Configurações carregadas
    
    Note over Index: Verifica configurações e<br>decide qual conteúdo servir
    Index->>Landingpage: Serve página diretamente<br>(após reverter mudanças)
    Landingpage-->>Navegador: Exibe landing page
    
    Usuario->>Formulario: Preenche e envia formulário
    
    Note over Formulario: O formulário está apontando<br>para order.php em vez de<br>form-processor.php
    
    Formulario->>Navegador: Submete formulário<br>para order.php
    Navegador->>+Index: POST /offer2/order.php
    
    Note over Index: Quando as mudanças foram revertidas<br>o campo redirect_url nas configurações<br>ainda existe, mas não é usado<br>pelo código antigo
    
    Index->>+Settings: Carrega configurações
    Settings->>SettingsJSON: Lê settings.json
    SettingsJSON-->>Settings: Retorna configurações<br>(incluindo redirect_url)
    Settings-->>-Index: Configurações carregadas
    
    Note over Index: O código antigo não verifica<br>o campo redirect_url
    
    Index->>+Thankyou: Redireciona para thankyou.php<br>ignorando o campo redirect_url
    Thankyou-->>-Navegador: Exibe página de agradecimento
    
    Note over Navegador: O usuário é sempre redirecionado para<br>thankyou.php independentemente da<br>configuração de redirect_url

    rect rgb(200, 230, 200)
    Note right of FormProcessor: Como deveria funcionar<br>Se o formulário apontasse para form-processor.php
    
    Usuario->>Formulario: Preenche e envia formulário (caminho correto)
    Formulario->>Navegador: Submete formulário para form-processor.php
    Navegador->>+FormProcessor: POST /form-processor.php
    FormProcessor->>+Settings: Carrega configurações
    Settings->>SettingsJSON: Lê settings.json
    SettingsJSON-->>Settings: Retorna configurações<br>(incluindo redirect_url)
    Settings-->>-FormProcessor: Configurações carregadas
    
    FormProcessor->>FormProcessor: Verifica redirect_url nas configurações
    
    alt black_land_redirect_url está definido
        FormProcessor-->>Navegador: Redireciona para URL personalizada<br>black_land_redirect_url: "https://dekoola.com/ch/hack/"
        Navegador->>Usuario: Navega para a URL personalizada
    else black_land_redirect_url não definido
        FormProcessor-->>Navegador: Redireciona para thankyou.php/html padrão
        Navegador->>Usuario: Exibe página de agradecimento padrão
    end
    end
```

# Análise do Problema: Redirecionamento Incorreto após Submissão de Formulário

## Problema

O sistema não está redirecionando os usuários para a URL personalizada configurada em `black.landing.folder.redirect_url` no arquivo `settings.json` após o preenchimento do formulário. Em vez disso, os usuários são sempre redirecionados para `/thankyou.php`.

## Causa Raiz Identificada

1. **Conflito de Rotas**: Os formulários nas landing pages estão apontando para o antigo arquivo `order.php` em vez do novo processador centralizado `form-processor.php`.

2. **Código Legado**: O arquivo `order.php` não foi atualizado para verificar o campo `redirect_url` nas configurações, então ele sempre redireciona para `/thankyou.php`.

3. **Configuração Incorreta**: Há uma duplicidade na configuração de redirecionamento:
   - `black.landing.folder.redirect_url` (usado pelo novo processador)
   - `black.landing.folder.redirect.url` (definido no JSON, mas não utilizado)

## Solução Proposta

1. **Atualizar a Configuração**:
   - Remover a duplicidade no arquivo `settings.json`
   - Unificar todas as configurações de redirecionamento em um único caminho

2. **Atualizar os Formulários**:
   - Modificar os formulários HTML nas landing pages para apontar para `/form-processor.php` em vez de `order.php`
   - Esse é o passo mais importante, pois só assim o processador centralizado será usado

3. **Proxy Temporário**:
   - Implementar um proxy em `offer2/order.php` que redirecione para `form-processor.php` 
   - Isso evitará a necessidade de alterar todos os formulários imediatamente

## Monitoramento e Verificação

Para verificar se a solução está funcionando:

1. Verificar os logs de acesso para confirmar que o `form-processor.php` está sendo chamado
2. Monitorar os redirecionamentos para garantir que os usuários estão sendo enviados para a URL correta
3. Testar diferentes configurações para validar o comportamento correto em todos os cenários

## Estado das Transições

| Estado | Evento | Próximo Estado | Condição |
|--------|--------|----------------|----------|
| Exibindo landing page | Submissão do formulário | Processando formulário | - |
| Processando formulário | Validação bem-sucedida | Redirecionando | Dados válidos |
| Redirecionando | Verificação de configuração | Exibindo URL personalizada | `black_land_redirect_url` não vazio |
| Redirecionando | Verificação de configuração | Exibindo página de agradecimento padrão | `black_land_redirect_url` vazio |
``` 