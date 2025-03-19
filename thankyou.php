<?php
// Inclusão de arquivos necessários
require_once 'settings.php';
require_once 'db.php';

// Registrar acesso à página de agradecimento
error_log("==== ACESSO À PÁGINA THANKYOU.PHP ====");
error_log("Método: " . $_SERVER['REQUEST_METHOD']);
error_log("Headers: " . json_encode(getallheaders()));
error_log("GET: " . json_encode($_GET));

// Recuperar subid dos cookies
$subid = isset($_COOKIE['subid']) ? $_COOKIE['subid'] : '';
$landing = isset($_COOKIE['landing']) ? $_COOKIE['landing'] : '';
$prelanding = isset($_COOKIE['prelanding']) ? $_COOKIE['prelanding'] : '';

// Registrar acesso à página de agradecimento (para estatísticas)
if (!empty($subid)) {
    error_log("Registrando acesso à thankyou para subid: $subid");
    
    // Aqui você pode adicionar código para registrar a conversão completa se necessário
    // Por exemplo: update_lead_status($subid, 'Converted');
}

// Determinar o idioma da página de agradecimento (usando a configuração de settings.php)
$thankyou_language = $black_land_thankyou_page_language;

// Verificar se é necessário exibir upsell na página
$show_upsell = $thankyou_upsell;

// Se for um POST e tiver email (formulário de email na página de agradecimento)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = $_POST['email'];
    
    if (!empty($subid) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Registrar o email para o lead
        add_email($subid, $email);
        error_log("Email adicionado ao lead: $email (subid: $subid)");
    }
}

// Caminho para o template da página de agradecimento
$template_path = __DIR__ . "/thankyou/templates/{$thankyou_language}.html";

// Verificar se o template existe, caso contrário usar o padrão ou thankyou.html
if (!file_exists($template_path)) {
    error_log("Template de thankyou não encontrado: $template_path. Usando thankyou.html padrão.");
    
    // Redirecionar para thankyou.html como fallback
    if (file_exists(__DIR__ . '/thankyou.html')) {
        header('Location: /thankyou.html');
        exit;
    }
    
    // Se nenhum arquivo de thankyou for encontrado, mostrar mensagem padrão
    echo "<html><body><h1>Obrigado pelo seu cadastro!</h1><p>Seu cadastro foi realizado com sucesso.</p></body></html>";
    exit;
}

// Ler o template
$template = file_get_contents($template_path);

// Substituir variáveis no template
// Recuperar informações do lead do banco de dados se necessário
$lead_name = "Cliente";
$lead_phone = "";

// Se temos um subid, tentar recuperar os dados do lead
if (!empty($subid)) {
    // Aqui você pode adicionar código para recuperar os dados do lead
    // Por exemplo: $lead_data = get_lead_data($subid);
    // E depois usar os dados para personalizar a página
}

// Substituir variáveis no template
$template = str_replace('{NAME}', $lead_name, $template);
$template = str_replace('{PHONE}', $lead_phone, $template);

// Adicionar conteúdo de upsell se configurado
if ($show_upsell) {
    $upsell_content = '<div class="upsell-section">';
    $upsell_content .= '<h3>' . $thankyou_upsell_header . '</h3>';
    $upsell_content .= '<p>' . $thankyou_upsell_text . '</p>';
    $upsell_content .= '<a href="' . $thankyou_upsell_url . '" class="upsell-button">Confira esta oferta especial</a>';
    $upsell_content .= '</div>';
    
    $template = str_replace('{UPSELL}', $upsell_content, $template);
} else {
    $template = str_replace('{UPSELL}', '', $template);
}

// Adicionar formulário de email se necessário
if (strpos($template, '{EMAIL}') !== false) {
    $email_form_path = __DIR__ . "/thankyou/templates/email/{$thankyou_language}fill.html";
    $email_form = file_exists($email_form_path) ? file_get_contents($email_form_path) : '';
    $template = str_replace('{EMAIL}', $email_form, $template);
}

// Exibir a página finalizada
echo $template; 