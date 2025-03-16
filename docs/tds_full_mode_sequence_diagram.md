# Diagrama de Sequência do TDS no Modo "Full"

Este diagrama ilustra o fluxo completo de processamento quando o TDS (Traffic
Distribution System) está configurado no modo "full", que deveria redirecionar
todo o tráfego para o conteúdo "white".

```mermaid
sequenceDiagram
    participant Cliente
    participant index.php
    participant settings.php
    participant ConfigFile as settings.json
    participant Cloaker
    participant Database as db.php
    participant WhiteFunction as white()
    
    Cliente->>index.php: Faz requisição HTTP para /
    
    index.php->>settings.php: Carrega configurações
    settings.php->>ConfigFile: Lê configurações
    ConfigFile-->>settings.php: Retorna configurações (tds.mode="full")
    settings.php-->>index.php: Retorna $tds_mode="full"
    
    index.php->>Cloaker: Inicializa Cloaker
    Cloaker-->>index.php: Retorna instância do cloaker
    
    index.php->>index.php: Verifica se $tds_mode == "full"
    
    index.php->>Database: add_white_click($cloaker->detect, ['fullcloak'])
    Database-->>index.php: Confirmação do registro do clique
    
    index.php->>WhiteFunction: white(false)
    
    WhiteFunction->>settings.php: Obtém configurações white_action, white_folder, etc.
    settings.php-->>WhiteFunction: Retorna configurações white_action="folder", white_folder="white2"
    
    alt white_action = "folder"
        WhiteFunction->>WhiteFunction: Processa conteúdo da pasta white_folder
        WhiteFunction->>Cliente: Serve conteúdo de white_folder (white2/index.html)
    else white_action = "curl"
        WhiteFunction->>WhiteFunction: Faz requisição para white_curl_url
        WhiteFunction->>Cliente: Serve conteúdo do cURL
    else white_action = "redirect"
        WhiteFunction->>Cliente: Redireciona para white_redirect_url (302)
    end
    
    Note right of index.php: return; impede execução<br/>do restante do script
```

## Explicação do Diagrama

### Participantes

1. **Cliente**: O usuário final ou navegador que acessa o sistema
2. **index.php**: O ponto de entrada principal da aplicação
3. **settings.php**: O arquivo que carrega e processa as configurações
4. **settings.json**: O arquivo de configuração que contém as definições do TDS
5. **Cloaker**: A classe/objeto responsável por detectar informações sobre o
   visitante
6. **db.php**: O componente que gerencia interações com o banco de dados
7. **white()**: A função que processa e serve o conteúdo "white"

### Estados

1. **Início**: Cliente faz uma requisição HTTP para a raiz do site
2. **Carregamento de Configurações**: O sistema carrega as configurações do
   arquivo JSON
3. **Verificação do TDS**: O sistema verifica se o modo TDS está configurado
   como "full"
4. **Registro de Clique**: O sistema registra um clique "white" no banco de
   dados
5. **Processamento White**: A função white() é chamada para processar o conteúdo
6. **Servir Conteúdo**: O conteúdo "white" é servido ao cliente de acordo com a
   configuração

### Fluxo de Dados

1. O Cliente faz uma requisição para "/"
2. index.php carrega as configurações via settings.php
3. settings.php lê o arquivo settings.json para obter as configurações
4. O valor de tds.mode="full" é lido e retornado como $tds_mode
5. index.php verifica se $tds_mode é "full"
6. Se for "full", index.php registra um clique "white" no banco de dados
7. index.php chama a função white() com o parâmetro false
8. white() obtém configurações específicas como white_action e white_folder
9. Dependendo do valor de white_action, a função white() realiza uma das
   seguintes ações:
   - Serve o conteúdo da pasta especificada em white_folder (padrão: "white2")
   - Faz uma requisição cURL para white_curl_url e serve o conteúdo resultante
   - Redireciona o cliente para white_redirect_url com um código HTTP 302
10. O comando return; em index.php impede que qualquer código adicional seja
    executado

### Problemas Potenciais

Se o sistema não estiver seguindo este fluxo quando configurado com
tds_mode="full", os problemas podem estar em:

1. **Carregamento de Configurações**: O valor de tds.mode pode não estar sendo
   carregado corretamente
2. **Persistência de Configurações**: As alterações na interface de
   administração podem não estar sendo salvas corretamente
3. **Execução Condicional**: A condição if ($tds_mode=='full') pode não estar
   sendo avaliada como verdadeira
4. **Terminação Prematura**: O comando return; após white(false) pode não estar
   funcionando corretamente
5. **Função white()**: A função white() pode estar com problemas na obtenção de
   configurações ou no processamento do conteúdo
6. **Cookies/Estado de Sessão**: Cookies anteriores podem estar afetando o
   comportamento do sistema
