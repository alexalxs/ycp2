<?php
// Habilitar o registro de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Carregar dependências necessárias
require_once 'settings.php';
require_once 'abtest.php';
require_once 'cookies.php';
require_once 'db.php';

// Verificar se a pasta logs existe, caso contrário, criá-la
if (!file_exists(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0777, true);
}

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obter dados do corpo da requisição
    $raw_data = file_get_contents('php://input');
    $data = json_decode($raw_data, true);
    
    // Verificar se os dados são válidos
    if ($data && isset($data['event']) && isset($data['prelanding'])) {
        // Garantir que temos um subid válido para rastreamento
        $subid = isset($_COOKIE['subid']) ? $_COOKIE['subid'] : set_subid();
        
        // Verificar se já houve um clique recente para evitar duplicação
        $last_click_time = isset($_COOKIE['last_click_time']) ? $_COOKIE['last_click_time'] : 0;
        $current_time = time();
        
        // Se o último clique foi há menos de 2 segundos, não registrar novamente
        if ($current_time - $last_click_time < 2) {
            echo json_encode(['success' => true, 'duplicate' => true]);
            return;
        }
        
        // Registrar o tempo deste clique
        ywbsetcookie('last_click_time', $current_time, '/');
        
        // Formatar os dados para o log
        $timestamp = date('Y-m-d H:i:s');
        $event = $data['event'];
        $prelanding = $data['prelanding'];
        $client_timestamp = isset($data['timestamp']) ? $data['timestamp'] : '';
        
        // Criar a entrada de log
        $log_entry = "{$timestamp} | SubID: {$subid} | Event: {$event} | Prelanding: {$prelanding} | Timestamp: {$client_timestamp}\n";
        
        // Escrever no arquivo de log
        $log_file = __DIR__ . '/logs/button_clicks.log';
        file_put_contents($log_file, $log_entry, FILE_APPEND);
        
        // Registrar o clique nas estatísticas do sistema
        // Primeiro, obter informações básicas do visitor para estatísticas
        $ip = $_SERVER['REMOTE_ADDR'];
        $ua = $_SERVER['HTTP_USER_AGENT'];
        $detect_data = [
            'ip' => $ip,
            'ua' => $ua,
            'country' => 'unknown', // Pode ser melhorado com geolocalização
            'os' => 'unknown',      // Pode ser melhorado com detecção de OS
            'isp' => 'unknown'      // Pode ser melhorado com detecção de ISP
        ];
        
        // Para redirecionar o usuário para a landing page após o clique
        if (isset($black_land_folder_names) && !empty($black_land_folder_names)) {
            // Verificar se a landing foi especificada no payload ou usar cookie existente
            $landing = '';
            
            // Primeiro verificar se devemos usar a landing conforme configuração em settings.json
            if ($black_land_action === 'folder') {
                // Verificar se há apenas uma landing em settings.json (caso de offer2 como no erro reportado)
                if (count($black_land_folder_names) === 1) {
                    $landing = $black_land_folder_names[0];
                } else {
                    // Opção 1: Landing especificada diretamente no JSON
                    if (isset($data['landing']) && in_array($data['landing'], $black_land_folder_names)) {
                        $landing = $data['landing'];
                    } 
                    // Opção 2: Se save_user_flow está ativado E temos um cookie 'landing'
                    else if ($save_user_flow && isset($_COOKIE['landing']) && in_array($_COOKIE['landing'], $black_land_folder_names)) {
                        $landing = $_COOKIE['landing'];
                    } 
                    // Opção 3: Selecionar uma landing aleatoriamente para teste A/B
                    else {
                        $index = rand(0, count($black_land_folder_names) - 1);
                        $landing = $black_land_folder_names[$index];
                    }
                }
            }
            
            // Verificar se a landing ainda é válida
            if (empty($landing) || !in_array($landing, $black_land_folder_names)) {
                $index = rand(0, count($black_land_folder_names) - 1);
                $landing = $black_land_folder_names[$index];
            }
            
            // Definir ou atualizar o cookie de landing
            ywbsetcookie('landing', $landing, '/');
            
            // Registrar a landing corretamente para estatísticas
            add_lpctr($subid, $prelanding, $landing);
            
            // Não retornamos mais um redirecionamento, apenas confirmamos que o clique foi registrado
            echo json_encode(['success' => true]);
        } else {
            // Se não houver landing pages configuradas, registrar o clique sem landing
            add_lpctr($subid, $prelanding);
            echo json_encode(['success' => true]);
        }
    } else {
        // Dados inválidos
        http_response_code(400);
        echo json_encode(['error' => 'Dados inválidos']);
    }
} else {
    // Método não permitido
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
}
?>