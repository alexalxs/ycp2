<?php
// Script para testar o salvamento do campo redirect_url
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', 1);

// Incluir dependências necessárias
use Noodlehaus\Config;
use Noodlehaus\Writer\Json;
require_once __DIR__.'/../config/ConfigInterface.php';
require_once __DIR__.'/../config/AbstractConfig.php';
require_once __DIR__.'/../config/Config.php';
require_once __DIR__.'/../config/Parser/ParserInterface.php';
require_once __DIR__.'/../config/Parser/Json.php';
require_once __DIR__.'/../config/Writer/WriterInterface.php';
require_once __DIR__.'/../config/Writer/AbstractWriter.php';
require_once __DIR__.'/../config/Writer/Json.php';
require_once __DIR__.'/../config/ErrorException.php';
require_once __DIR__.'/../config/Exception.php';
require_once __DIR__.'/../config/Exception/ParseException.php';
require_once __DIR__.'/../config/Exception/FileNotFoundException.php';

require_once __DIR__.'/../settings.php';
require_once 'password.php';
check_password();

// Salvar o valor enviado via POST
if (isset($_POST['submit'])) {
    // Obter o valor do campo
    $new_value = $_POST['redirect_url'];
    
    // Mostrar o que foi recebido
    echo "<h3>Dados recebidos:</h3>";
    echo "<pre>";
    echo "Valor enviado: " . htmlspecialchars($new_value) . "\n";
    echo "Tipo: " . gettype($new_value) . "\n";
    echo "</pre>";
    
    try {
        // Carregar o arquivo de configuração
        $config = new Config(__DIR__.'/../settings.json');
        
        // Verificar o conteúdo antes da modificação
        echo "<h3>Configuração antes da modificação:</h3>";
        echo "<pre>";
        echo "black.landing.folder.redirect_url = " . json_encode($config->get('black.landing.folder.redirect_url', 'NÃO DEFINIDO')) . "\n";
        echo "</pre>";
        
        // Atualizar o valor diretamente
        $config['black.landing.folder.redirect_url'] = $new_value;
        
        // Verificar o conteúdo após a modificação na memória
        echo "<h3>Configuração após modificação (em memória):</h3>";
        echo "<pre>";
        echo "black.landing.folder.redirect_url = " . json_encode($config->get('black.landing.folder.redirect_url', 'NÃO DEFINIDO')) . "\n";
        echo "</pre>";
        
        // Salvar as alterações no arquivo
        $config->toFile(__DIR__.'/../settings.json', new Json());
        
        // Verificar se o arquivo foi salvo
        echo "<h3>Conteúdo do arquivo após salvar:</h3>";
        echo "<pre>";
        $saved_config = json_decode(file_get_contents(__DIR__.'/../settings.json'), true);
        echo "black.landing.folder.redirect_url = " . json_encode($saved_config['black']['landing']['folder']['redirect_url'] ?? 'NÃO ENCONTRADO') . "\n";
        echo "</pre>";
        
        echo "<p style='color:green'>✓ O valor foi atualizado com sucesso para: " . htmlspecialchars($new_value) . "</p>";
    } catch (Exception $e) {
        echo "<p style='color:red'>Erro ao atualizar o valor: " . $e->getMessage() . "</p>";
    }
}

// Carregar o valor atual
$current_value = $black_land_redirect_url;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Teste do Campo Redirect URL</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"] {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .info-box {
            background-color: #f8f8f8;
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        pre {
            background-color: #f5f5f5;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <h1>Teste do Campo Redirect URL</h1>
    
    <div class="info-box">
        <h3>Informações Atuais</h3>
        <p><strong>Valor atual no settings.php:</strong> "<?= htmlspecialchars($current_value) ?>"</p>
        <p><strong>Tipo de dados:</strong> <?= gettype($current_value) ?></p>
        
        <?php
        $settings_json = json_decode(file_get_contents(__DIR__.'/../settings.json'), true);
        $json_value = $settings_json['black']['landing']['folder']['redirect_url'] ?? 'não encontrado';
        ?>
        
        <p><strong>Valor no arquivo settings.json:</strong> "<?= htmlspecialchars($json_value) ?>"</p>
    </div>
    
    <form method="post" action="">
        <div class="form-group">
            <label for="redirect_url">Redirect URL after form submission:</label>
            <input type="text" id="redirect_url" name="redirect_url" value="<?= htmlspecialchars($current_value) ?>" placeholder="Ex: /thankyou.html">
        </div>
        
        <button type="submit" name="submit">Atualizar Valor</button>
    </form>
    
    <p><a href="editsettings.php?password=<?= $log_password ?>">Voltar para a página de configurações</a></p>
</body>
</html> 