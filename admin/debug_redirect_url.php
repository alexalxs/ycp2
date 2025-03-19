<?php
// Script de diagnóstico para o campo redirect_url
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', 1);

// Incluir dependências necessárias para todas as operações
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

// Informações sobre o arquivo settings.json
echo "<h2>Informações do arquivo settings.json</h2>";
echo "<p>Caminho do arquivo: " . realpath(__DIR__.'/../settings.json') . "</p>";
echo "<p>Tamanho do arquivo: " . filesize(__DIR__.'/../settings.json') . " bytes</p>";
echo "<p>Última modificação: " . date("Y-m-d H:i:s", filemtime(__DIR__.'/../settings.json')) . "</p>";

// Verificar permissões
echo "<h2>Permissões</h2>";
echo "<p>Permissões do arquivo: " . substr(sprintf('%o', fileperms(__DIR__.'/../settings.json')), -4) . "</p>";
echo "<p>Usuário proprietário: " . posix_getpwuid(fileowner(__DIR__.'/../settings.json'))['name'] . "</p>";
echo "<p>Grupo proprietário: " . posix_getgrgid(filegroup(__DIR__.'/../settings.json'))['name'] . "</p>";

// Exibir variáveis relevantes
echo "<h2>Valores das variáveis</h2>";
echo "<pre>";
echo "black_land_redirect_url = " . var_export($black_land_redirect_url, true) . "\n";
echo "Valor direto da configuração: " . var_export($conf->get('black.landing.folder.redirect_url', 'NÃO DEFINIDO'), true) . "\n";
echo "</pre>";

// Testar modificando e salvando a configuração
echo "<h2>Teste de modificação e salvamento</h2>";
echo "<form method='post'>";
echo "<input type='text' name='new_value' value='" . htmlspecialchars($black_land_redirect_url) . "'>";
echo "<input type='submit' name='submit' value='Atualizar campo'>";
echo "</form>";

// Processar se o formulário for enviado
if (isset($_POST['submit'])) {
    $new_value = $_POST['new_value'];
    
    // Carregar a configuração novamente para garantir que estamos usando a versão mais recente
    $config = new Config(__DIR__.'/../settings.json');
    
    // Atualizar o valor
    $config['black.landing.folder.redirect_url'] = $new_value;
    
    // Salvar as alterações
    $config->toFile(__DIR__.'/../settings.json', new Json());
    
    echo "<p style='color:green'>Valor atualizado para: " . htmlspecialchars($new_value) . "</p>";
    echo "<p>Recarregue a página para ver as alterações refletidas nas variáveis acima.</p>";
}

// Exibir o conteúdo atual do settings.json
echo "<h2>Conteúdo de settings.json (resumido)</h2>";
echo "<pre>";
$settings_json = json_decode(file_get_contents(__DIR__.'/../settings.json'), true);
// Mostrar apenas a parte relevante para economizar espaço
echo json_encode([
    'black' => [
        'landing' => [
            'folder' => [
                'redirect_url' => $settings_json['black']['landing']['folder']['redirect_url'] ?? 'não encontrado',
                // Mostrar outras configurações próximas para contexto
                'conversions' => $settings_json['black']['landing']['folder']['conversions'] ?? 'não encontrado',
                'customthankyoupage' => $settings_json['black']['landing']['folder']['customthankyoupage'] ?? 'não encontrado',
            ]
        ]
    ]
], JSON_PRETTY_PRINT);
echo "</pre>"; 