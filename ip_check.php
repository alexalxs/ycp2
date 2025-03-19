<?php
require_once 'core.php';
require_once 'settings.php';
require_once 'bases/iputils.php';
require_once 'bases/ipcountry.php';

header('Content-Type: text/plain');

$current_ip = getip();
echo "IP Detectado: " . $current_ip . "\n\n";

// Verificar se o arquivo whitelist.txt existe
if (file_exists(__DIR__."/bases/whitelist.txt")) {
    echo "Arquivo whitelist.txt encontrado.\n";
    
    // Carregar a lista de IPs da whitelist
    $whitelist_ips = file(__DIR__."/bases/whitelist.txt", FILE_IGNORE_NEW_LINES);
    
    // Filtrar comentários e linhas vazias
    $whitelist_ips = array_filter($whitelist_ips, function($line) {
        return trim($line) !== '' && substr(trim($line), 0, 1) !== '#';
    });
    
    echo "Número de IPs na whitelist: " . count($whitelist_ips) . "\n";
    echo "IPs na whitelist:\n";
    foreach ($whitelist_ips as $ip) {
        echo "- " . $ip . "\n";
    }
    
    // Verificar se o IP atual está na whitelist
    $whitelist_match = IpUtils::checkIp($current_ip, $whitelist_ips);
    echo "\nIP está na whitelist? " . ($whitelist_match ? "SIM" : "NÃO") . "\n";
} else {
    echo "Arquivo whitelist.txt não encontrado.\n";
}

// Verificar IP no arquivo de bots
if (file_exists(__DIR__."/bases/bots.txt")) {
    echo "\nArquivo bots.txt encontrado.\n";
    $bots = file(__DIR__."/bases/bots.txt", FILE_IGNORE_NEW_LINES);
    $is_bot = IpUtils::checkIp($current_ip, $bots);
    echo "IP está na lista de bots? " . ($is_bot ? "SIM" : "NÃO") . "\n";
} else {
    echo "\nArquivo bots.txt não encontrado.\n";
}

echo "\nModo TDS: " . $tds_mode . "\n";
echo "Ação Prelanding: " . $black_preland_action . "\n";
echo "Pastas Prelanding: " . implode(", ", $black_preland_folder_names) . "\n";
echo "Ação White: " . $white_action . "\n";
echo "Pastas White: " . implode(", ", $white_folder_names) . "\n"; 