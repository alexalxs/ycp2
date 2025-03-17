<?php
// Habilitar exibição de erros
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Incluir dependências necessárias
require_once __DIR__."/db/SleekDB.php";
require_once __DIR__."/db/Store.php";
require_once __DIR__."/db/QueryBuilder.php";
require_once __DIR__."/db/Query.php";
require_once __DIR__."/db/Cache.php";

// Função para obter os registros LPCTR
function get_lpctr() {
    $dataDir = __DIR__ . "/logs";
    $lpctrStore = new \SleekDB\Store("lpctr", $dataDir);
    return $lpctrStore->findAll(["time" => "desc"]);
}

// Função para criar uma tabela HTML simples a partir de um array
function array_to_table($array) {
    if (empty($array)) {
        return "Sem dados";
    }
    
    $html = '<table border="1" cellpadding="5" cellspacing="0">';
    
    // Cabeçalho da tabela
    $keys = array_keys($array[0]);
    $html .= '<tr>';
    foreach ($keys as $key) {
        $html .= '<th>' . htmlspecialchars($key) . '</th>';
    }
    $html .= '</tr>';
    
    // Dados da tabela
    foreach ($array as $row) {
        $html .= '<tr>';
        foreach ($keys as $key) {
            $value = $row[$key];
            if ($key === 'time') {
                $value = date('Y-m-d H:i:s', $value);
            }
            $html .= '<td>' . htmlspecialchars($value) . '</td>';
        }
        $html .= '</tr>';
    }
    
    $html .= '</table>';
    return $html;
}

// Obter os registros LPCTR
$lpctr_records = get_lpctr();

// Contador de landing pages
$landing_counts = [];
foreach ($lpctr_records as $record) {
    $land = empty($record['land']) ? '(vazio)' : $record['land'];
    if (!isset($landing_counts[$land])) {
        $landing_counts[$land] = 0;
    }
    $landing_counts[$land]++;
}

// Contar por prelanding e landing
$prelanding_landing_counts = [];
foreach ($lpctr_records as $record) {
    $preland = empty($record['preland']) ? '(vazio)' : $record['preland'];
    $land = empty($record['land']) ? '(vazio)' : $record['land'];
    $key = $preland . ' -> ' . $land;
    
    if (!isset($prelanding_landing_counts[$key])) {
        $prelanding_landing_counts[$key] = 0;
    }
    $prelanding_landing_counts[$key]++;
}

// Exibir página HTML com os resultados
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug LPCTR</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1, h2 { color: #333; }
        table { border-collapse: collapse; margin-bottom: 30px; }
        th { background-color: #f2f2f2; }
        td, th { padding: 8px; text-align: left; }
        .section { margin-bottom: 40px; }
    </style>
</head>
<body>
    <h1>Debug LPCTR</h1>
    
    <div class="section">
        <h2>Contagem por Landing Page</h2>
        <table border="1">
            <tr>
                <th>Landing</th>
                <th>Contagem</th>
            </tr>
            <?php foreach ($landing_counts as $land => $count): ?>
            <tr>
                <td><?php echo htmlspecialchars($land); ?></td>
                <td><?php echo $count; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    
    <div class="section">
        <h2>Contagem por Prelanding -> Landing</h2>
        <table border="1">
            <tr>
                <th>Combinação</th>
                <th>Contagem</th>
            </tr>
            <?php foreach ($prelanding_landing_counts as $combination => $count): ?>
            <tr>
                <td><?php echo htmlspecialchars($combination); ?></td>
                <td><?php echo $count; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    
    <div class="section">
        <h2>Últimos 20 Registros LPCTR</h2>
        <?php 
        // Limitar a 20 registros para não sobrecarregar a página
        $last_records = array_slice($lpctr_records, 0, 20);
        echo array_to_table($last_records);
        ?>
    </div>
</body>
</html> 