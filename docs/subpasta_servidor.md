# Configuração e Funcionamento em Subpastas

## Problema Identificado

Quando o sistema é instalado em uma subpasta do servidor web (não na raiz), ocorrem os seguintes problemas:

1. **Redirecionamentos Incorretos**: Ao salvar configurações (através de `/admin/savesettings.php`), o sistema redireciona para a raiz do servidor e não para a raiz da pasta do projeto, o que faz com que a URL resultante seja incorreta.

   Exemplo:
   - URL acessada: `http://localhost:8003/volumex/admin/editsettings.php?password=12345`
   - Após salvar, redireciona para: `http://localhost:8003/admin/editsettings.php?password=12345` (incorreto)
   - URL correta seria: `http://localhost:8003/volumex/admin/editsettings.php?password=12345`

2. **Carregamento Incorreto de Resources**: Arquivos estáticos (CSS, JavaScript, imagens) e links internos não funcionam corretamente, pois não consideram o caminho base da aplicação.

3. **Exibição de Páginas**: As páginas prelanding, white e black não são exibidas corretamente na URL, considerando o caminho até o index.php.

4. **Formulários com Caminhos Absolutos**: Os formulários que utilizam caminhos absolutos (iniciando com `/`) não incluem o caminho base do projeto, resultando em requisições para URLs incorretas.

   Exemplo nos logs:
   ```
   [Wed Mar 19 11:46:44 2025] [::1]:59621 [404]: POST /admin/savesettings.php?password=12345 - No such file or directory
   ```

5. **Redirecionamento Incorreto nos Cliques de Botões**: Ao clicar em botões nas páginas, o sistema redireciona para URLs sem considerar o caminho base.

   Exemplo:
   - URL atual: `http://localhost:8003/volumex/`
   - Ao clicar no botão, redireciona para: `http://localhost:8003/offer2/` (incorreto)
   - URL correta seria: `http://localhost:8003/volumex/offer2/`

## Solução Implementada

A solução para estes problemas foi implementada através das seguintes modificações:

### 1. Detecção do Caminho Base

Foi adicionada uma função `get_base_path()` no arquivo `redirect.php` para detectar automaticamente o caminho base do projeto em relação ao servidor:

```php
/**
 * Determina o caminho base do projeto em relação ao servidor
 * 
 * @return string O caminho base, por exemplo: /volumex
 */
function get_base_path() {
    $script_name = $_SERVER['SCRIPT_NAME'] ?? '';
    $script_filename = $_SERVER['SCRIPT_FILENAME'] ?? '';
    
    if (empty($script_name) || empty($script_filename)) {
        return '';
    }
    
    // Remover o nome do arquivo do caminho
    $script_dir = dirname($script_name);
    
    // Se estiver na raiz, retorna uma string vazia
    if ($script_dir == '/' || $script_dir == '\\') {
        return '';
    }
    
    return $script_dir;
}
```

### 2. Funções de Redirecionamento

As funções `redirect()` e `jsredirect()` foram modificadas para usar o caminho base ao construir URLs:

```php
function redirect($url, $redirect_type = 302, $add_querystring = true)
{
    // Verifica se a URL começa com http:// ou https:// (URL absoluta)
    if (!preg_match('/^https?:\/\//', $url)) {
        // URL relativa, precisamos adicionar o caminho base
        $base_path = get_base_path();
        
        // Se a URL não começa com /, adiciona
        if (substr($url, 0, 1) !== '/') {
            $url = '/' . $url;
        }
        
        // Se a URL começa com o caminho base, não adiciona novamente
        if (substr($url, 0, strlen($base_path)) !== $base_path) {
            $url = $base_path . $url;
        }
    }
    
    // Resto da função...
}
```

### 3. Processamento de URLs nas Páginas

A função `serve_file()` em `index.php` foi modificada para remover o caminho base das URLs solicitadas:

```php
// Obter o arquivo solicitado da URL
$requested_file = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remover o caminho base do projeto, se necessário
$base_path = '';
if (function_exists('get_base_path')) {
    $base_path = get_base_path();
    if (!empty($base_path) && strpos($requested_file, $base_path) === 0) {
        $requested_file = substr($requested_file, strlen($base_path));
    }
}
```

### 4. Carregamento de Conteúdo

A função `load_white_content()` em `htmlprocessing.php` foi adaptada para considerar o caminho base ao construir links para arquivos estáticos:

```php
// Obter o caminho base do projeto
$base_path = '';
if (function_exists('get_base_path')) {
    $base_path = get_base_path();
}

// O base URL deve incluir o caminho base do projeto
$base_url = $base_path . "/{$folder_name}/";
```

### 5. Correção de Formulários

Formulários que usavam caminhos absolutos foram modificados para usar caminhos relativos:

**Antes:**
```html
<form action="/admin/savesettings.php?password=<?=$log_password?>" method="post">
```

**Depois:**
```html
<form action="savesettings.php?password=<?=$log_password?>" method="post">
```

Esta alteração foi feita no arquivo `volumex/admin/editsettings.php` e deve ser aplicada a todos os formulários que utilizem caminhos absolutos para envio de dados.

### 6. Correção de Redirecionamento nos Cliques de Botões

O JavaScript que gerencia os cliques nos botões foi modificado para considerar o caminho base ao redirecionar para novas páginas:

**Antes:**
```javascript
// Redirecionar para o URL original definido pelo usuário
window.location.href = originalHref;
```

**Depois:**
```javascript
// Verificar se a URL é relativa e adicionar o caminho base se necessário
if (originalHref && !originalHref.startsWith('http') && !originalHref.startsWith('//') && !originalHref.startsWith('/')) {
    // É uma URL relativa sem barra inicial, adicionar caminho base
    window.location.href = "' . $base_path . '/" + originalHref;
} else if (originalHref && !originalHref.startsWith('http') && !originalHref.startsWith('//') && originalHref.startsWith('/')) {
    // É uma URL relativa com barra inicial, substituir pela URL com caminho base
    window.location.href = "' . $base_path . '" + originalHref;
} else {
    // URL absoluta ou com protocolo, usar como está
    window.location.href = originalHref;
}
```

Esta alteração foi feita no arquivo `volumex/index.php` no script que gerencia os cliques em botões.

## Como Testar a Instalação em Subpastas

Para testar a aplicação como se estivesse instalada em uma subpasta, use um dos seguintes métodos:

### Método 1: Definindo a Pasta Raiz do Servidor

```bash
# Inicia o servidor PHP com a raiz no diretório atual (projeto inteiro)
php -S localhost:8003 -t .
```

Acesse:
- `http://localhost:8003/volumex/` (página principal)
- `http://localhost:8003/volumex/admin/index.php?password=12345` (admin)

### Método 2: Usando um Servidor Web Real (Apache/Nginx)

Configure o servidor web para servir a aplicação a partir de uma subpasta específica. Por exemplo, em Apache:

```apache
Alias /minha-aplicacao /caminho/para/o/projeto/volumex
<Directory /caminho/para/o/projeto/volumex>
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
```

## Considerações Importantes

1. **Compatibilidade com Versões Anteriores**: Esta solução mantém a compatibilidade com instalações na raiz do servidor.

2. **Caminho para Recursos Estáticos**: Todos os recursos estáticos (CSS, JavaScript, imagens) agora utilizam caminhos relativos considerando o caminho base do projeto.

3. **Formulários**: Os formulários POST também foram ajustados para funcionar corretamente com a instalação em subpastas.

4. **Logs**: Em caso de problemas, verifique o arquivo de log em `admin/save_debug.log` que contém informações detalhadas sobre o processo de salvamento das configurações.

5. **Evite Caminhos Absolutos**: Sempre use caminhos relativos nos formulários e links internos para garantir a compatibilidade com instalações em subpastas.

6. **Atributos href**: Recomenda-se usar caminhos relativos nos atributos href de links e botões. Caso seja necessário usar caminhos absolutos (começando com `/`), o JavaScript de redirecionamento agora adiciona o caminho base corretamente.

## Testando o Funcionamento

Execute as seguintes ações para verificar se a instalação em subpasta está funcionando corretamente:

1. Acesse a página principal: `http://seuservidor/subpasta/`
2. Verifique se todas as imagens e recursos são carregados corretamente
3. Acesse o painel admin: `http://seuservidor/subpasta/admin/index.php?password=12345`
4. Faça alterações nas configurações e salve
5. Verifique se você é redirecionado de volta para a página de configurações (com o caminho base correto)
6. Teste o registro de cliques em botões e verifique nas estatísticas
7. **Teste de navegação por botões**: Clique nos botões CTA nas páginas e verifique se você é redirecionado corretamente mantendo o caminho base

## Logs de Erros Comuns e Soluções

### Erro: POST para caminho incorreto
```
[Wed Mar 19 11:46:44 2025] [::1]:59621 [404]: POST /admin/savesettings.php?password=12345 - No such file or directory
```

**Solução**: Verifique e corrija o atributo `action` do formulário em `editsettings.php`, removendo a barra inicial:
```html
<!-- Incorreto -->
<form action="/admin/savesettings.php?password=<?=$log_password?>" method="post">

<!-- Correto -->
<form action="savesettings.php?password=<?=$log_password?>" method="post">
```

### Erro: Redirecionamento incorreto após clique em botão
```
Ao clicar em botão em http://localhost:8003/volumex/ redireciona para http://localhost:8003/offer2/ em vez de http://localhost:8003/volumex/offer2/
```

**Solução**: Verifique o JavaScript que gerencia os cliques em botões e adicione a lógica para considerar o caminho base ao redirecionar: 