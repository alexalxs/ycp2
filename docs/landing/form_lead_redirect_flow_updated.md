# Diagrama de Sequência para Registro de Lead e Redirecionamento após Captura (Atualizado)

```mermaid
sequenceDiagram
    participant Usuario as Usuário
    participant LP as Landing Page (offer2)
    participant FormProc as Processador de Formulário (form-processor.php)
    participant Sistema as Sistema
    participant BD as Banco de Dados JSON
    participant AdminUI as Interface Administrativa
    participant Webhook as Webhook Externo
    participant Redir as URL de Redirecionamento

    %% Carregamento inicial da landing page
    Usuario->>LP: Acessa a landing page (/offer2/)
    Note over LP: Estado: "Página inicial carregada"
    LP->>Usuario: Exibe formulário de captura de email

    %% Interação do usuário com o formulário
    Usuario->>LP: Preenche email e outros dados
    Usuario->>LP: Clica no botão de envio
    Note over LP: Estado: "Formulário submetido"
    
    %% Processamento do formulário no servidor
    LP->>FormProc: Submete formulário para /form-processor.php (POST)<br>Payload: {name, email, phone}
    Note over FormProc: Estado: "Processando dados do formulário"
    
    %% Processamento no servidor
    FormProc->>Sistema: Recebe e valida dados (email, nome, etc.)
    Sistema->>Sistema: Gera/recupera subid do cookie
    
    alt Dados válidos
        %% Registro do lead
        Sistema->>BD: Registra lead (subid, nome, email, etc.)
        Note over BD: Estado: "Lead registrado"
        BD-->>Sistema: Confirmação de registro
        
        %% Verificação da URL de redirecionamento
        Sistema->>Sistema: Verifica black_land_redirect_url
        
        alt black_land_redirect_url está definido
            Sistema->>Redir: Redireciona para URL personalizada<br>Ex: "https://dekoola.com/ch/hack/"
            Note over Usuario: Estado: "Redirecionado para URL externa"
        else black_land_redirect_url não definido
            %% Com a correção, não precisamos mais verificar thankyou.php
            Sistema->>Redir: Redireciona para /thankyou.html
            Note over Usuario: Estado: "Página de agradecimento padrão exibida"
        end
        
        %% Processamento assíncrono opcional
        par Envio para webhook externo
            Sistema->>Webhook: Envia dados para webhook externo (se configurado)
            Note over Webhook: Estado: "Processando dados externos"
            Webhook-->>Sistema: Resposta (sucesso/erro)
        end
    else Dados inválidos
        Sistema-->>Usuario: Retorna mensagem de erro
        Note over Usuario: Estado: "Erro de validação exibido"
    end
    
    %% Processo de edição da URL de redirecionamento (área administrativa)
    rect rgb(240, 240, 255)
    note right of AdminUI: Problema identificado: Não é possível editar<br>a URL de redirecionamento na interface
    
    Usuario->>AdminUI: Acessa página de configurações (/admin/editsettings.php)
    AdminUI->>Usuario: Exibe formulário com campo black.landing.folder.redirect_url
    Usuario->>AdminUI: Edita URL e submete formulário
    AdminUI->>Sistema: Envia requisição para salvar configurações (/admin/savesettings.php)
    
    Note over Sistema: Problema: Os parâmetros POST são transformados<br>incorretamente (pontos convertidos para underscores)
    
    Sistema->>Sistema: Erro ao processar 'black.landing.folder.redirect_url'<br>Procura por 'black_landing_folder_redirect_url'
    Sistema-->>AdminUI: Redireciona sem aplicar alterações
    AdminUI->>Usuario: Exibe página de configurações sem alterações
    end
```

## Solução Implementada e Recomendações

### 1. Problema identificado na edição da URL de redirecionamento

**Causa raiz**: O código em `admin/savesettings.php` está convertendo pontos para underscores ao processar os dados do formulário, mas depois tenta acessar as chaves com pontos na configuração.

```php
foreach($_POST as $key=>$value){
    $confkey=str_replace('_','.',$key); // Converte underscores para pontos, mas precisa ser o contrário para funcionar
```

**Correção recomendada**: Modificar o código para que ele trate corretamente os nomes dos campos com pontos ou ajustar o nome do campo no formulário.

### 2. Simplificação do fluxo de redirecionamento

**Problema**: A lógica atual verifica desnecessariamente a existência de `thankyou.php`, criando complexidade.

**Correção**: Eliminar a lógica relacionada ao `thankyou.php` e utilizar apenas o redirecionamento direto:
- Se `black_land_redirect_url` estiver definido: redirecionar para essa URL
- Caso contrário: redirecionar para `thankyou.html`

## Análise do Estado de Transição Atualizado

| Estado | Descrição | Evento de Transição | Próximo Estado |
|--------|-----------|---------------------|----------------|
| Página inicial carregada | Usuário visualiza o formulário | Preenchimento e envio do formulário | Formulário submetido |
| Formulário submetido | Dados enviados ao servidor | Processamento pelo servidor | Processando dados do formulário |
| Processando dados do formulário | Sistema valida e registra o lead | Redirecionamento | Redirecionado para URL externa ou Página de agradecimento padrão exibida |
| Lead registrado | Dados armazenados no banco de dados | N/A | N/A |
| Redirecionado para URL externa | Usuário é enviado para URL definida em `black_land_redirect_url` | N/A | N/A |
| Página de agradecimento padrão exibida | Usuário visualiza thankyou.html | N/A | N/A |

## Fontes de Dados Envolvidas

1. **Banco de Dados JSON**
   - Localização: `/logs/leads/` - Armazena informações dos leads registrados
   - Campos: subid, nome, email, timestamp, status, etc.

2. **Arquivos de Configuração**
   - `settings.json` - Define configurações do sistema incluindo URL de redirecionamento
   - Parâmetros relevantes:
     - `black.landing.folder.redirect_url: "https://dekoola.com/ch/hack/"` - URL para redirecionamento após submissão do formulário

## Detalhes das Requisições e Respostas

### Submissão do Formulário:
- **Requisição**: 
  - Método: `POST`
  - URL: `/form-processor.php`
  - Dados: `email=usuario@exemplo.com&name=Nome&phone=Telefone`
  
- **Processamento**:
  1. Validação de dados
  2. Geração/recuperação de subid do cookie
  3. Registro no banco de dados
  4. Verificação da URL de redirecionamento
  
- **Resposta**: 
  - Redirecionamento para URL configurada em `black_land_redirect_url` ou para `/thankyou.html` como fallback 