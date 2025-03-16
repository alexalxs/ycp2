# Diagrama de Sequência: Exibição de Conteúdo de Pastas na URL Raiz

Este diagrama de sequência ilustra o fluxo de processamento quando a opção
"pasta" está selecionada nas configurações, permitindo que o sistema exiba o
conteúdo das pastas white ou black diretamente na URL raiz
(http://localhost:8003/) sem redirecionamento.

```mermaid
sequenceDiagram
    participant Cliente as Cliente (Navegador)
    participant Index as index.php
    participant Core as core.php
    participant Settings as settings.php
    participant Cloaker as Cloaker (main.php)
    participant DB as db.php
    participant Cookies as cookies.php
    participant ServeFile as serve_file()
    participant FS as Sistema de Arquivos

    Cliente->>Index: GET http://localhost:8003/
    activate Index
    Index->>Settings: Carrega configurações
    activate Settings
    Settings->>Settings: Lê settings.json
    Settings-->>Index: Retorna configurações
    deactivate Settings
    
    Index->>Core: Inicializa funções core
    activate Core
    Core-->>Index: Funções core carregadas
    deactivate Core
    
    Index->>Cloaker: Cria instância do Cloaker
    activate Cloaker
    Cloaker-->>Index: Instância criada
    deactivate Cloaker
    
    Index->>Cloaker: check()
    activate Cloaker
    Cloaker-->>Index: Resultado da verificação (0 ou 1)
    deactivate Cloaker
    
    alt Resultado = 0 (Usuário normal) e black_land_action = 'folder'
        Index->>ServeFile: serve_file(folder, requested_file)
        activate ServeFile
        ServeFile->>FS: Verifica existência do arquivo
        activate FS
        FS-->>ServeFile: Arquivo existe
        deactivate FS
        
        ServeFile->>ServeFile: Define Content-Type
        ServeFile->>ServeFile: Ajusta caminhos relativos (se HTML)
        
        alt Arquivo é PHP
            ServeFile->>FS: include(file_path)
            activate FS
            FS-->>ServeFile: Resultado da execução
            deactivate FS
        else Arquivo é HTML
            ServeFile->>FS: file_get_contents(file_path)
            activate FS
            FS-->>ServeFile: Conteúdo do arquivo
            deactivate FS
            ServeFile->>ServeFile: Ajusta links relativos
        else Outros tipos
            ServeFile->>FS: readfile(file_path)
            activate FS
            FS-->>ServeFile: Conteúdo do arquivo
            deactivate FS
        end
        
        ServeFile-->>Index: true (arquivo servido)
        deactivate ServeFile
    else Resultado = 1 (Bot/Moderador) e white_action = 'folder'
        Index->>DB: add_white_click(detect, result)
        activate DB
        
        DB->>Cookies: get_cookie()
        activate Cookies
        Cookies-->>DB: Valor do cookie
        deactivate Cookies
        
        DB->>DB: Armazena dados no SleekDB
        DB-->>Index: Clique registrado
        deactivate DB
        
        Index->>ServeFile: serve_file(folder, requested_file)
        activate ServeFile
        ServeFile->>FS: Verifica existência do arquivo
        activate FS
        FS-->>ServeFile: Arquivo existe
        deactivate FS
        
        ServeFile->>ServeFile: Define Content-Type
        ServeFile->>ServeFile: Ajusta caminhos relativos (se HTML)
        
        alt Arquivo é PHP
            ServeFile->>FS: include(file_path)
            activate FS
            FS-->>ServeFile: Resultado da execução
            deactivate FS
        else Arquivo é HTML
            ServeFile->>FS: file_get_contents(file_path)
            activate FS
            FS-->>ServeFile: Conteúdo do arquivo
            deactivate FS
            ServeFile->>ServeFile: Ajusta links relativos
        else Outros tipos
            ServeFile->>FS: readfile(file_path)
            activate FS
            FS-->>ServeFile: Conteúdo do arquivo
            deactivate FS
        end
        
        ServeFile-->>Index: true (arquivo servido)
        deactivate ServeFile
    end
    
    Index-->>Cliente: Conteúdo da pasta solicitada
    deactivate Index
```

## Detalhamento do Fluxo

### Participantes

1. **Cliente (Navegador)**: Inicia a requisição para a URL raiz.
2. **index.php**: Ponto de entrada da aplicação que coordena o fluxo.
3. **core.php**: Contém funções essenciais do sistema.
4. **settings.php**: Carrega as configurações do arquivo settings.json.
5. **Cloaker (main.php)**: Responsável por detectar bots, moderadores e usuários
   normais.
6. **db.php**: Gerencia o armazenamento de dados usando SleekDB.
7. **cookies.php**: Gerencia cookies e sessões.
8. **serve_file()**: Função que serve arquivos estáticos e dinâmicos.
9. **Sistema de Arquivos**: Representa o sistema de arquivos do servidor.

### Fluxo de Dados

1. **Requisição Inicial**:
   - O cliente faz uma requisição GET para http://localhost:8003/
   - O index.php é acionado e carrega as configurações e dependências

2. **Verificação do Usuário**:
   - O Cloaker verifica se o usuário é um bot/moderador ou um usuário normal
   - Retorna 0 para usuário normal, 1 para bot/moderador

3. **Servindo Conteúdo da Pasta**:
   - Se o usuário for normal e black_land_action for 'folder':
     - A função serve_file() é chamada com a pasta black selecionada
   - Se o usuário for bot/moderador e white_action for 'folder':
     - O clique é registrado no banco de dados
     - A função serve_file() é chamada com a pasta white selecionada

4. **Processamento do Arquivo**:
   - A função serve_file() verifica a existência do arquivo
   - Define o Content-Type apropriado
   - Para arquivos PHP: inclui o arquivo para execução
   - Para arquivos HTML: ajusta os caminhos relativos e serve o conteúdo
   - Para outros tipos: serve o conteúdo diretamente

5. **Resposta ao Cliente**:
   - O conteúdo processado é enviado de volta ao cliente

### Estados do Sistema

1. **Estado Inicial**: Sistema aguardando requisição
2. **Carregamento de Configurações**: Sistema carrega configurações e
   dependências
3. **Verificação de Usuário**: Sistema determina o tipo de usuário
4. **Seleção de Pasta**: Sistema seleciona a pasta apropriada (white ou black)
5. **Processamento de Arquivo**: Sistema processa o arquivo solicitado
6. **Resposta**: Sistema envia a resposta ao cliente

Este fluxo garante que o conteúdo das pastas seja servido diretamente na URL
raiz, sem necessidade de redirecionamento, mantendo a funcionalidade de filtrar
entre conteúdo white e black com base no tipo de usuário.
