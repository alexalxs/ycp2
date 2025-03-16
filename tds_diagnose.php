<?php
// Script para diagnóstico e correção de problemas no TDS
// Coloque este arquivo na raiz do site e acesse via navegador

// Habilitar exibição de erros para diagnóstico
ini_set('display_errors', '1');
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Função para verificar se um arquivo existe e tem permissão de leitura
function check_file($file) {
    if (!file_exists($file)) {
        return "<span style='color:red'>ERRO: Arquivo não encontrado</span>";
    } elseif (!is_readable($file)) {
        return "<span style='color:orange'>AVISO: Arquivo não pode ser lido</span>";
    }
    return "<span style='color:green'>OK</span>";
}

// Função para exibir cookies atuais
function display_cookies() {
    $output = "<ul>";
    if (empty($_COOKIE)) {
        $output .= "<li>Nenhum cookie definido</li>";
    } else {
        foreach ($_COOKIE as $name => $value) {
            $output .= "<li><strong>$name</strong>: " . htmlspecialchars($value) . "</li>";
        }
    }
    $output .= "</ul>";
    return $output;
}

// Função para limpar cookies
function clear_cookies() {
    foreach ($_COOKIE as $name => $value) {
        setcookie($name, '', time() - 3600, '/');
    }
    return "<span style='color:green'>Cookies limpos com sucesso!</span>";
}

// Carregar configurações
$settings_json = __DIR__ . '/settings.json';
$json_status = check_file($settings_json);

// Verifica se o arquivo existe antes de tentar carregar
$config = null;
$tds_mode = "Desconhecido";
$white_action = "Desconhecido";
$white_folder = "Desconhecido";
$save_user_flow = "Desconhecido";

if (file_exists($settings_json) && is_readable($settings_json)) {
    $config = json_decode(file_get_contents($settings_json), true);
    if ($config) {
        $tds_mode = $config['tds']['mode'] ?? "Não definido";
        $white_action = $config['white']['action'] ?? "Não definido";
        $white_folder = is_array($config['white']['folder']['names'] ?? null) ? 
            implode(', ', $config['white']['folder']['names']) : "Não definido";
        $save_user_flow = $config['tds']['saveuserflow'] ? "Sim" : "Não";
    } else {
        $json_error = json_last_error_msg();
    }
}

// Verifica diretórios principais
$white_folders_status = "";
if (is_array($config['white']['folder']['names'] ?? null)) {
    foreach ($config['white']['folder']['names'] as $folder) {
        $folder_path = __DIR__ . '/' . $folder;
        $folder_status = file_exists($folder_path) ? 
            (is_readable($folder_path) ? "<span style='color:green'>OK</span>" : 
            "<span style='color:orange'>AVISO: Sem permissão de leitura</span>") : 
            "<span style='color:red'>ERRO: Diretório não encontrado</span>";
        $white_folders_status .= "<li>$folder: $folder_status";
        
        // Verifica index.html no diretório white
        if (file_exists($folder_path)) {
            $index_path = $folder_path . '/index.html';
            $index_status = file_exists($index_path) ? 
                (is_readable($index_path) ? " (index.html encontrado)" : 
                " (index.html existe mas não pode ser lido)") : 
                " (index.html não encontrado)";
            $white_folders_status .= $index_status;
        }
        
        $white_folders_status .= "</li>";
    }
}

// Verificar configurações TDS
$tds_mode_diagnostic = "";
if ($tds_mode === "full") {
    $tds_mode_diagnostic = "<span style='color:green'>TDS está configurado no modo 'full' - Todo tráfego deve ser direcionado para white</span>";
} elseif ($tds_mode === "off") {
    $tds_mode_diagnostic = "<span style='color:orange'>TDS está desligado - Todo tráfego será direcionado para black</span>";
} else {
    $tds_mode_diagnostic = "<span style='color:blue'>TDS está em modo de detecção normal</span>";
}

// Ação se foi solicitada limpeza de cookies
$clear_message = "";
if (isset($_GET['clear_cookies'])) {
    $clear_message = clear_cookies();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Ação se foi solicitada atualização de configuração
$update_message = "";
if (isset($_GET['update_tds_full']) && file_exists($settings_json) && is_writable($settings_json)) {
    $config['tds']['mode'] = "full";
    file_put_contents($settings_json, json_encode($config, JSON_PRETTY_PRINT));
    $update_message = "<span style='color:green'>TDS definido como 'full' com sucesso!</span>";
    $tds_mode = "full";
    $tds_mode_diagnostic = "<span style='color:green'>TDS está configurado no modo 'full' - Todo tráfego deve ser direcionado para white</span>";
}

// Função para verificar problema na chamada white() em index.php
function check_white_function() {
    $index_file = __DIR__ . '/index.php';
    if (!file_exists($index_file) || !is_readable($index_file)) {
        return "<span style='color:red'>Não foi possível ler index.php</span>";
    }
    
    $content = file_get_contents($index_file);
    
    // Verifica se há return após white(false) no modo TDS full
    if (preg_match('/if\s*\(\s*\$tds_mode\s*==\s*[\'"]full[\'"]\s*\)\s*{.*?white\s*\(\s*false\s*\)\s*;.*?return\s*;/s', $content)) {
        return "<span style='color:orange'>AVISO: Comando 'return' encontrado após white(false) - Sugerido substituir por 'exit'</span>";
    }
    
    // Verifica se há exit após white(false) no modo TDS full
    if (preg_match('/if\s*\(\s*\$tds_mode\s*==\s*[\'"]full[\'"]\s*\)\s*{.*?white\s*\(\s*false\s*\)\s*;.*?exit\s*;/s', $content)) {
        return "<span style='color:green'>OK: Comando 'exit' encontrado após white(false)</span>";
    }
    
    return "<span style='color:red'>ERRO: Não encontrado padrão adequado para encerramento após white(false)</span>";
}

// Verifica a função white() no arquivo main.php
function check_main_white_function() {
    $main_file = __DIR__ . '/main.php';
    if (!file_exists($main_file) || !is_readable($main_file)) {
        return "<span style='color:red'>Não foi possível ler main.php</span>";
    }
    
    $content = file_get_contents($main_file);
    
    // Verifica se a função white() está retornando corretamente
    if (preg_match('/function\s+white\s*\([^)]*\)\s*{.*?return\s*;.*?}/s', $content)) {
        return "<span style='color:green'>OK: Função white() retorna corretamente</span>";
    }
    
    return "<span style='color:orange'>AVISO: Possível problema na função white() em main.php</span>";
}

$white_function_status = check_white_function();
$main_white_function_status = check_main_white_function();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Diagnóstico do TDS</title>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1, h2 {
            color: #333;
        }
        .section {
            margin-bottom: 30px;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
        }
        .action-buttons {
            margin-top: 20px;
        }
        .btn {
            display: inline-block;
            padding: 8px 15px;
            margin-right: 10px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .btn:hover {
            background-color: #45a049;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Diagnóstico do TDS (Traffic Distribution System)</h1>
    
    <?php if (!empty($clear_message)): ?>
    <div class="section">
        <p><?php echo $clear_message; ?></p>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($update_message)): ?>
    <div class="section">
        <p><?php echo $update_message; ?></p>
    </div>
    <?php endif; ?>
    
    <div class="section">
        <h2>Status dos Arquivos do Sistema</h2>
        <table>
            <tr>
                <th>Arquivo</th>
                <th>Status</th>
            </tr>
            <tr>
                <td>settings.json</td>
                <td><?php echo $json_status; ?></td>
            </tr>
            <tr>
                <td>index.php</td>
                <td><?php echo check_file(__DIR__ . '/index.php'); ?></td>
            </tr>
            <tr>
                <td>main.php</td>
                <td><?php echo check_file(__DIR__ . '/main.php'); ?></td>
            </tr>
            <tr>
                <td>settings.php</td>
                <td><?php echo check_file(__DIR__ . '/settings.php'); ?></td>
            </tr>
            <tr>
                <td>db.php</td>
                <td><?php echo check_file(__DIR__ . '/db.php'); ?></td>
            </tr>
        </table>
    </div>
    
    <div class="section">
        <h2>Configurações do TDS</h2>
        <table>
            <tr>
                <th>Configuração</th>
                <th>Valor</th>
            </tr>
            <tr>
                <td>Modo TDS</td>
                <td><?php echo $tds_mode; ?></td>
            </tr>
            <tr>
                <td>Diagnóstico TDS</td>
                <td><?php echo $tds_mode_diagnostic; ?></td>
            </tr>
            <tr>
                <td>Ação White</td>
                <td><?php echo $white_action; ?></td>
            </tr>
            <tr>
                <td>Pasta White</td>
                <td><?php echo $white_folder; ?></td>
            </tr>
            <tr>
                <td>Salvar Fluxo de Usuário</td>
                <td><?php echo $save_user_flow; ?></td>
            </tr>
            <tr>
                <td>Index.php (white function)</td>
                <td><?php echo $white_function_status; ?></td>
            </tr>
            <tr>
                <td>Main.php (white function)</td>
                <td><?php echo $main_white_function_status; ?></td>
            </tr>
        </table>
    </div>
    
    <div class="section">
        <h2>Status dos Diretórios White</h2>
        <ul>
            <?php echo $white_folders_status; ?>
        </ul>
    </div>
    
    <div class="section">
        <h2>Cookies Atuais</h2>
        <?php echo display_cookies(); ?>
    </div>
    
    <div class="section">
        <h2>Ações de Diagnóstico</h2>
        <div class="action-buttons">
            <a href="?clear_cookies=1" class="btn">Limpar Todos os Cookies</a>
            <a href="?update_tds_full=1" class="btn">Definir TDS como 'full'</a>
            <a href="/" class="btn">Testar Página Inicial</a>
        </div>
    </div>
    
    <div class="section">
        <h2>Recomendações</h2>
        <ul>
            <?php if ($tds_mode !== "full"): ?>
            <li>Configure o TDS modo 'full' no settings.json ou use o botão acima.</li>
            <?php endif; ?>
            
            <?php if (strpos($white_function_status, "ERRO") !== false || strpos($white_function_status, "AVISO") !== false): ?>
            <li>Modifique o arquivo index.php para substituir 'return;' por 'exit;' após a chamada white(false).</li>
            <?php endif; ?>
            
            <li>Limpe os cookies do navegador ou use o botão acima para garantir que nenhum cookie anterior interfira.</li>
            
            <?php if ($white_action !== "folder"): ?>
            <li>Verifique se a ação white está configurada como 'folder' no settings.json.</li>
            <?php endif; ?>
            
            <li>Certifique-se de que os diretórios white listados existam e contenham um arquivo index.html.</li>
        </ul>
    </div>
</body>
</html> 