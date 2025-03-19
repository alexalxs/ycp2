<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir a função get_base_path
require_once 'redirect.php';

// Exibir todas as variáveis de servidor importantes
echo "<h1>Debug das Variáveis de Servidor</h1>";
echo "<pre>";

// Informações de caminho
echo "<h2>Informações de Caminho</h2>";
echo "get_base_path(): " . get_base_path() . "\n";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "SCRIPT_FILENAME: " . $_SERVER['SCRIPT_FILENAME'] . "\n";
echo "PHP_SELF: " . $_SERVER['PHP_SELF'] . "\n";
echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "SERVER_NAME: " . $_SERVER['SERVER_NAME'] . "\n";
echo "HTTP_HOST: " . $_SERVER['HTTP_HOST'] . "\n";
echo "REMOTE_ADDR: " . $_SERVER['REMOTE_ADDR'] . "\n";
echo "HTTP_USER_AGENT: " . $_SERVER['HTTP_USER_AGENT'] . "\n";

// Exibir diretório atual
echo "<h2>Diretório Atual</h2>";
echo "Diretório atual: " . __DIR__ . "\n";
echo "Diretório atual (dirname): " . dirname(__FILE__) . "\n";

// Listar arquivos no diretório atual
echo "<h2>Arquivos no Diretório Atual</h2>";
$files = scandir(__DIR__);
print_r($files);

echo "</pre>"; 