<?php
/**
 * Arquivo proxy para redirecionar as requisições de order.php para form-processor.php
 * 
 * Este arquivo foi criado para manter a compatibilidade com formulários existentes
 * que ainda apontam para o antigo processador order.php em vez do novo form-processor.php
 * centralizado na raiz.
 * 
 * Todas as requisições são redirecionadas preservando todos os parâmetros.
 */

// Habilitar exibição de erros para depuração
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Registrar o acesso para depuração
error_log("[PROXY] Requisição para offer2/order.php recebida. Método: " . $_SERVER['REQUEST_METHOD']);
error_log("[PROXY] Redirecionando para form-processor.php com os mesmos parâmetros");

// Determinar o caminho absoluto para form-processor.php
$form_processor_path = realpath(__DIR__ . '/../form-processor.php');

// Verificar se o arquivo form-processor.php existe
if (!file_exists($form_processor_path)) {
    error_log("[PROXY] ERRO: O arquivo form-processor.php não foi encontrado em: " . $form_processor_path);
    http_response_code(500);
    echo "Erro interno do servidor: processador de formulário não encontrado.";
    exit;
}

// Preservar todos os parâmetros da requisição original
$_REQUEST['_proxy_source'] = 'offer2/order.php';

// Se for uma requisição AJAX, indicar isso
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    $_REQUEST['ajax'] = '1';
}

// Incluir o form-processor.php para processar a requisição
// Isso evita um redirecionamento HTTP e preserva todos os parâmetros
require_once $form_processor_path;

// Este código não será executado se form-processor.php terminar com exit()
// O que é o comportamento esperado do processador
echo "AVISO: Se você está vendo esta mensagem, o form-processor.php não terminou a execução corretamente.";
exit; 