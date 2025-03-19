<?php
// Arquivo de teste para verificar permissões de escrita em settings.json
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', 1);

use Noodlehaus\Config;
use Noodlehaus\Writer\Json;
require_once 'config/ConfigInterface.php';
require_once 'config/AbstractConfig.php';
require_once 'config/Config.php';
require_once 'config/Parser/ParserInterface.php';
require_once 'config/Parser/Json.php';
require_once 'config/Writer/WriterInterface.php';
require_once 'config/Writer/AbstractWriter.php';
require_once 'config/Writer/Json.php';
require_once 'config/ErrorException.php';
require_once 'config/Exception.php';
require_once 'config/Exception/ParseException.php';
require_once 'config/Exception/FileNotFoundException.php';

echo "<h1>Teste de Permissões de Escrita</h1>";
echo "<p>Arquivo: " . realpath('settings.json') . "</p>";
echo "<p>Permissões: " . substr(sprintf('%o', fileperms('settings.json')), -4) . "</p>";
echo "<p>Usuário PHP: " . exec('whoami') . "</p>";
echo "<p>Proprietário do arquivo: " . posix_getpwuid(fileowner('settings.json'))['name'] . "</p>";

// Testar escrita com file_put_contents
echo "<h2>Teste 1: file_put_contents</h2>";
try {
    // Fazer backup do arquivo original
    $original_content = file_get_contents('settings.json');
    
    // Tentar escrever (sem modificar o conteúdo)
    $result = file_put_contents('settings.json', $original_content);
    echo $result ? "<p style='color:green'>Sucesso! Bytes escritos: $result</p>" : "<p style='color:red'>Falha na escrita</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>Erro: " . $e->getMessage() . "</p>";
}

// Testar escrita com a biblioteca Noodlehaus/Config
echo "<h2>Teste 2: Noodlehaus/Config</h2>";
try {
    $conf = new Config('settings.json');
    // Ler um valor sem modificar
    $test_value = $conf->get('black.landing.folder.redirect_url');
    echo "<p>Valor atual: $test_value</p>";
    
    // Salvar de volta sem modificar (apenas para testar a escrita)
    $conf->toFile('settings.json', new Json());
    echo "<p style='color:green'>Config salva com sucesso!</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>Erro: " . $e->getMessage() . "</p>";
}

// Verificar logs de erro
echo "<h2>Últimas linhas do log de erro PHP</h2>";
echo "<pre>";
$error_log = ini_get('error_log');
if (!empty($error_log) && file_exists($error_log)) {
    echo "Log encontrado: $error_log\n";
    echo htmlspecialchars(shell_exec("tail -n 20 " . escapeshellarg($error_log)));
} else {
    echo "Log não encontrado ou não configurado\n";
}
echo "</pre>";
?> 