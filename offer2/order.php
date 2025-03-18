<?php
// Incluir arquivos necessários
require_once '../settings.php';
require_once '../db.php';

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
    
    // Obter landing e preland da sessão
    $landing = isset($_COOKIE['landing']) ? $_COOKIE['landing'] : 'offer2';
    $prelanding = isset($_COOKIE['prelanding']) ? $_COOKIE['prelanding'] : '';
    
    // Registrar lead com status 'Lead'
    add_lead($subid, $name, $email, $phone, $landing, 'Lead');
    
    // Redirecionar para a página de agradecimento
    if ($black_land_use_custom_thankyou_page) {
        // Verificar primeiro se thankyou.php existe antes de redirecionar
        if (file_exists(__DIR__ . '/../thankyou.php')) {
            header('Location: /thankyou.php');
        } else {
            // Fallback para thankyou.html se thankyou.php não existir
            header('Location: /thankyou.html');
        }
    } else {
        header('Location: /thankyou.html');
    }
    exit;
} else {
    // Se não for um POST request, redirecionar para a página principal
    header('Location: /');
    exit;
} 