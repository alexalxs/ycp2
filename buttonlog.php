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
        
        // Se for um evento de clique na prelanding, preparar redirecionamento para landing
        if ($event === 'lead_click') {
            // Registrar que este usuário clicou na prelanding
            ywbsetcookie('black', 'landing', '/');
            
            // Selecionar uma landing page de acordo com a configuração
            // Primeiro carregamos as configurações necessárias
            if (!isset($black_land_folder_names) && file_exists(__DIR__ . '/settings.php')) {
                include_once __DIR__ . '/settings.php';
            }
            
            // Selecionar uma landing aleatória da lista de landing pages
            if (isset($black_land_folder_names) && !empty($black_land_folder_names)) {
                if (function_exists('select_item')) {
                    $landing = select_item($black_land_folder_names, $save_user_flow, 'landing', true)[0];
                } else {
                    // Fallback se a função select_item não estiver disponível
                    $index = rand(0, count($black_land_folder_names) - 1);
                    $landing = $black_land_folder_names[$index];
                }
                ywbsetcookie('landing', $landing, '/');
            } else {
                // Se não houver landing pages configuradas, usar 'landing' como padrão
                $landing = 'landing';
                ywbsetcookie('landing', $landing, '/');
            }
            
            // Obter o nome da pasta dinamicamente
            $dir_name = basename(dirname(__FILE__));
            
            // Construir a URL de redirecionamento para a landing selecionada
            $landing_redirect = "/{$dir_name}/{$landing}/";
            
            // Incluir informações necessárias para o redirecionamento
            $response = [
                'success' => true,
                'redirect' => $landing_redirect,
                'landing' => $landing,
                'status' => 'clicked'
            ];
            
            // Registrar o clique no banco de dados para estatísticas
            try {
                // Registrar o clique
                $click_id = add_black_click($subid, isset($cloaker) ? $cloaker->detect : [], $prelanding, $landing, 'Clicked');
                $response['click_id'] = $click_id;
            } catch (Exception $e) {
                error_log("Erro ao registrar clique: " . $e->getMessage());
            }
            
            // Registrar informações de debug
            error_log("Redirecionamento para landing após clique na prelanding: $landing_redirect");
            
            // Enviar a resposta como JSON
            header('Content-Type: application/json');
            echo json_encode($response);
            return;
        } else {
            // Se não for um evento de clique de lead, registrar o clique sem landing
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