<?php
// Inclusão de arquivos necessários
require_once 'settings.php';
require_once 'db.php';

// Log de acesso para depuração
error_log("==== ACESSO AO PROCESSADOR DE FORMULÁRIO ====");
error_log("Método: " . $_SERVER['REQUEST_METHOD']);
error_log("Headers: " . json_encode(getallheaders()));
error_log("GET: " . json_encode($_GET));
error_log("POST: " . json_encode($_POST));
error_log("Origem: " . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'Desconhecida'));

// Verificar se é um POST request (envio de formulário)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Garantir que temos um subid válido para rastreamento
    $subid = isset($_COOKIE['subid']) ? $_COOKIE['subid'] : '';
    
    // Se não houver subid, tentar criar um novo
    if (empty($subid)) {
        $subid = 'subid_' . bin2hex(random_bytes(10));
        ywbsetcookie('subid', $subid, '/');
    }
    
    // Obter informações do formulário
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $phone = isset($_POST['phone']) ? $_POST['phone'] : '';
    
    // Adiciona o subid se for passado via POST (para casos de uso específicos)
    if (isset($_POST['subid']) && !empty($_POST['subid'])) {
        $subid = $_POST['subid'];
    }
    
    // Obter landing e preland da sessão
    $landing = isset($_COOKIE['landing']) ? $_COOKIE['landing'] : '';
    $prelanding = isset($_COOKIE['prelanding']) ? $_COOKIE['prelanding'] : '';
    
    // Validar os dados
    $errors = [];
    
    // Validação básica - mais validações podem ser adicionadas conforme necessário
    if (empty($name) && isset($_POST['name'])) {
        $errors[] = "O nome é obrigatório";
    }
    
    if (empty($email) && isset($_POST['email'])) {
        $errors[] = "O email é obrigatório";
    } else if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "O email fornecido não é válido";
    }
    
    if (empty($phone) && isset($_POST['phone'])) {
        $errors[] = "O telefone é obrigatório";
    }
    
    // Verificar se há erros de validação
    if (!empty($errors)) {
        // Registrar os erros de validação
        error_log("==== ERROS DE VALIDAÇÃO ====");
        error_log(json_encode($errors));
        
        // Redirecionar de volta para o formulário com erros
        header('HTTP/1.1 422 Unprocessable Entity');
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }
    
    // Registrar lead com status 'Lead'
    // Utilizando a ordem correta de parâmetros: subid, name, phone, status, email
    error_log("Tentando enviar postback para lead: $subid, $name, $email");
    add_lead($subid, $name, $phone, $lead_status_name, $email);
    error_log("Lead registrado com sucesso: $name, $email, $phone (subid: $subid)");
    
    // Verificar se existe uma URL de redirecionamento personalizada nas configurações
    $redirect_url = './thankyou.html'; // URL padrão
    
    // Obter URL personalizada das configurações
    $custom_redirect_url = $black_land_redirect_url;
    
    if (!empty($custom_redirect_url)) {
        $redirect_url = $custom_redirect_url;
        error_log("Usando URL de redirecionamento personalizada: $redirect_url");
    }
    
    // Enviar postback S2S, se configurado
    if (function_exists('trigger_s2s_postback')) {
        trigger_s2s_postback($subid, $lead_status_name, $name, $email, $phone);
    }
    
    // Redirecionar para a página de agradecimento ou URL personalizado
    if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
        // Resposta para requisições AJAX
        echo json_encode(['success' => true, 'redirect' => $redirect_url]);
    } else {
        // Redirecionamento padrão
        header("Location: $redirect_url");
    }
    
    exit;
} else {
    // Se não for um POST request, redirecionar para a página principal
    header('Location: ./');
    exit;
} 