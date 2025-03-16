# Análise do Problema: TDS no Modo "Full" Não Redirecionando para White

## Resumo do Problema

O sistema foi configurado com `tds_mode="full"` no arquivo settings.json, o que
deveria enviar todo o tráfego para o conteúdo white. No entanto, pelos logs
analisados, isso não está acontecendo como esperado, e o conteúdo de `/offer4/`
está sendo servido em vez do conteúdo de `/white2/`.

## Achados da Investigação

1. **Configuração Correta**:
   - No arquivo `settings.json`, a configuração está definida corretamente como
     `"tds": { "mode": "full" }` na linha 117-119.
   - No arquivo `settings.php`, a variável `$tds_mode` está sendo corretamente
     carregada da configuração.

2. **Lógica de Redirecionamento**:
   - No arquivo `index.php`, há uma verificação clara para
     `if ($tds_mode=='full')` nas linhas 103-106.
   - Quando verdadeiro, ele chama `add_white_click()` seguido por `white(false)`
     e `return`, o que deveria encerrar o processamento.

3. **Logs de Requisição**:
   - Os logs mostram requisições bem-sucedidas para
     `/offer4/assets/css/styles.css` e outros recursos de `/offer4/`.
   - Não há evidências de chamadas para recursos em `/white2/` que seria
     esperado se o modo TDS estivesse funcionando.

4. **Admin** e **Configurações**:
   - Nos logs, há acessos a `/admin/savesettings.php?password=12345`, o que
     indica que as configurações podem ter sido alteradas.
   - Logo após, há requisições para `/`, que retornam conteúdo de `/offer4/` em
     vez de `/white2/`.

## Possíveis Causas

### 1. Configuração Não Atualizada no Servidor

O arquivo `settings.json` pode ter sido modificado pela interface de
administração, mas as alterações não estão sendo carregadas em tempo real. O
modo TDS pode ter sido alterado no banco de dados, mas o arquivo PHP ainda está
usando a configuração antiga em memória.

### 2. Problema com a Função `white()`

A função `white()` em `main.php` pode não estar funcionando corretamente quando
chamada com o parâmetro `false`. É possível que haja algum problema na seleção
da pasta ou na forma como o conteúdo é servido.

### 3. Cookie ou Estado de Sessão

Cookies ou estados de sessão anteriores podem estar afetando o comportamento do
sistema. Talvez um cookie esteja forçando a exibição do conteúdo de `/offer4/`
em vez de `/white2/`.

### 4. Sobrescrita de Configuração

A configuração `tds_mode` pode estar sendo sobrescrita em algum outro lugar do
código que não foi identificado na análise.

### 5. Problema com `serve_file()`

A função `serve_file()` pode estar sendo chamada incorretamente ou com
parâmetros incorretos quando no modo "full".

## Soluções Recomendadas

### 1. Forçar Recarregamento de Configurações

Adicione código para forçar o recarregamento das configurações do arquivo
`settings.json` em cada requisição, ou implemente um sistema de cache com
invalidação apropriada.

```php
// No início do index.php ou no settings.php
clearstatcache(true, __DIR__.'/settings.json');
$conf = Config::load(__DIR__.'/settings.json');
```

### 2. Depuração da Função `white()`

Adicione logs detalhados na função `white()` para verificar quais caminhos de
código estão sendo executados:

```php
function white($use_js_checks) {
    error_log("Função white() chamada com parâmetro: " . ($use_js_checks ? "true" : "false"));
    // ... resto do código
}
```

### 3. Limpar Cookies e Sessão

Adicione código para limpar cookies específicos que podem estar interferindo:

```php
// No início do index.php
if ($tds_mode=='full') {
    // Limpar cookies que podem estar afetando o comportamento
    setcookie('black', '', time() - 3600, '/');
    // ... outros cookies problematicos
}
```

### 4. Verificação em Tempo de Execução

Adicione verificações em pontos críticos do código para garantir que as
configurações estão sendo aplicadas:

```php
// Antes de servir qualquer conteúdo
if ($tds_mode == 'full' && strpos($file_path, 'white') === false) {
    error_log("AVISO: Tentativa de servir conteúdo não-white no modo full: " . $file_path);
    // Redirecionar para white ou mostrar erro
}
```

### 5. Logs Extensivos

Ative logs extensivos para depurar o problema:

```php
// No início do index.php
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_error.log');
error_log("Requisição para: " . $_SERVER['REQUEST_URI'] . " | tds_mode: " . $tds_mode);
```

## Solução Recomendada

Com base na análise, a abordagem mais promissora seria:

1. Verificar o código que manipula as configurações após alterações via admin.
2. Adicionar logs detalhados em pontos críticos do fluxo.
3. Implementar uma verificação adicional antes de servir qualquer conteúdo que
   não seja white quando `tds_mode=='full'`.
4. Verificar e limpar cookies e estados de sessão que podem estar interferindo.

A solução mais imediata seria adicionar esta verificação em `index.php` logo
após a verificação do `tds_mode`:

```php
if ($tds_mode=='full') {
    // Garantir que qualquer cookie ou estado anterior seja limpo
    if (isset($_COOKIE['black'])) {
        setcookie('black', '', time() - 3600, '/');
    }
    
    // Registrar o clique e redirecionar para white
    add_white_click($cloaker->detect, ['fullcloak']);
    white(false);
    exit; // Garantir que nenhum código adicional seja executado
}
```

Esta alteração garantiria que quando `tds_mode=='full'`, nenhum código após a
chamada `white(false)` seria executado, e quaisquer cookies anteriores que
pudessem estar influenciando o comportamento seriam removidos.
