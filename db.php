<?php
//Включение отладочной информации
ini_set('display_errors', '1');
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//Конец включения отладочной информации
date_default_timezone_set('UTC');

// Carregar configurações do settings.json
$settingsJson = file_get_contents(__DIR__ . '/settings.json');
$settings = json_decode($settingsJson, true);
$s2s_postbacks = isset($settings['postback']['s2s']) ? $settings['postback']['s2s'] : [];

require_once __DIR__ . "/db/Exceptions/IOException.php";
require_once __DIR__ . "/db/Exceptions/JsonException.php";
require_once __DIR__ . "/db/Classes/IoHelper.php";
require_once __DIR__ . "/db/SleekDB.php";
require_once __DIR__ . "/db/Store.php";
require_once __DIR__ . "/db/QueryBuilder.php";
require_once __DIR__ . "/db/Query.php";
require_once __DIR__ . "/db/Cache.php";
require_once __DIR__ . "/cookies.php";
require_once __DIR__ . "/requestfunc.php";

use SleekDB\Store;

// Função para acionamento de postbacks S2S
function trigger_s2s_postback($subid, $status, $name = '', $email = '', $phone = '') {
    global $s2s_postbacks;
    
    error_log("==== TENTANDO ENVIAR POSTBACK S2S ====");
    error_log("SubID: $subid, Status: $status, Nome: $name, Email: $email");
    
    // Verificar se existem configurações de postback
    if (empty($s2s_postbacks)) {
        error_log("Nenhuma configuração de postback S2S encontrada");
        return false;
    }
    
    // Criar diretório de logs se não existir
    if (!file_exists(__DIR__ . "/pblogs")) {
        mkdir(__DIR__ . "/pblogs");
        error_log("Diretório de logs de postback criado: " . __DIR__ . "/pblogs");
    }
    
    $success = false;
    
    // Obter informações do prelanding e landing
    $landing = get_cookie('landing');
    if (empty($landing)) $landing = 'unknown';
    $prelanding = get_cookie('prelanding');
    if (empty($prelanding)) $prelanding = 'unknown';
    
    // Para cada configuração de postback
    foreach ($s2s_postbacks as $s2s) {
        // Verificar se este postback deve ser enviado para este status
        if (!isset($s2s['events']) || !in_array($status, $s2s['events'])) {
            continue;
        }
        
        // Verificar se a URL está definida
        if (empty($s2s['url'])) {
            continue;
        }
        
        // Substituir as macros na URL
        $domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
        $final_url = $s2s['url'];
        $final_url = str_replace('{subid}', urlencode($subid), $final_url);
        $final_url = str_replace('{prelanding}', urlencode($prelanding), $final_url);
        $final_url = str_replace('{landing}', urlencode($landing), $final_url);
        $final_url = str_replace('{domain}', urlencode($domain), $final_url);
        $final_url = str_replace('{status}', urlencode($status), $final_url);
        
        // Preparar os dados para o postback
        $postData = [
            'subid' => $subid,
            'status' => $status,
            'prelanding' => $prelanding,
            'landing' => $landing
        ];
        
        // Adicionar dados adicionais se disponíveis
        if (!empty($name)) $postData['name'] = $name;
        if (!empty($email)) $postData['email'] = $email;
        if (!empty($phone)) $postData['phone'] = $phone;
        
        $method = isset($s2s['method']) ? $s2s['method'] : 'POST';
        
        // Registrar dados que serão enviados no log detalhado
        $payload_log = json_encode($postData, JSON_PRETTY_PRINT);
        $log_file = __DIR__ . "/pblogs/" . date("d.m.y") . ".pb.detailed.log";
        $log_entry = "==== " . date("Y-m-d H:i:s") . " ENVIANDO POSTBACK ====\n";
        $log_entry .= "URL: $final_url\n";
        $log_entry .= "METHOD: $method\n";
        $log_entry .= "PAYLOAD:\n$payload_log\n\n";
        file_put_contents($log_file, $log_entry, FILE_APPEND);
        
        try {
            // Enviar o postback
            $response = null;
            if ($method === 'GET') {
                // Para GET, adicionar parâmetros na URL
                $params = http_build_query($postData);
                $url = $final_url . (strpos($final_url, '?') !== false ? '&' : '?') . $params;
                $response = get($url);
            } else {
                // Para POST, enviar os dados no corpo
                $urlParts = explode('?', $final_url);
                $url = $urlParts[0];
                $existingParams = [];
                
                if (count($urlParts) > 1) {
                    parse_str($urlParts[1], $existingParams);
                }
                
                $params = array_merge($existingParams, $postData);
                $response = post($url, $params);
            }
            
            // Registrar o resultado no log
            $log_message = date("Y-m-d H:i:s") . " $method $final_url $status ";
            if ($response && isset($response['info']['http_code'])) {
                $log_message .= "HTTP " . $response['info']['http_code'];
                
                // Registrar a resposta completa no log detalhado
                $log_entry = "==== " . date("Y-m-d H:i:s") . " RESPOSTA DO WEBHOOK ====\n";
                $log_entry .= "HTTP CODE: " . $response['info']['http_code'] . "\n";
                $log_entry .= "CONTEÚDO: " . ($response['html'] ? $response['html'] : "Sem conteúdo") . "\n";
                $log_entry .= "ERRO: " . ($response['error'] ? $response['error'] : "Nenhum erro") . "\n\n";
                file_put_contents($log_file, $log_entry, FILE_APPEND);
                
                // Considerar como sucesso códigos 2xx
                if (intval($response['info']['http_code']) >= 200 && intval($response['info']['http_code']) < 300) {
                    $success = true;
                }
            } else {
                $log_message .= "FALHA - Sem resposta";
                
                // Registrar erro no log detalhado
                $log_entry = "==== " . date("Y-m-d H:i:s") . " FALHA NO ENVIO ====\n";
                $log_entry .= "ERRO: Sem resposta do servidor\n\n";
                file_put_contents($log_file, $log_entry, FILE_APPEND);
            }
            
            // Salvar no log
            $log_file_basic = __DIR__ . "/pblogs/" . date("d.m.y") . ".pb.log";
            file_put_contents($log_file_basic, $log_message . "\n", FILE_APPEND);
            
            error_log("Postback enviado: " . $log_message);
        } catch (Exception $e) {
            $error_message = date("Y-m-d H:i:s") . " ERRO ao enviar postback: " . $e->getMessage();
            $log_file_basic = __DIR__ . "/pblogs/" . date("d.m.y") . ".pb.log";
            file_put_contents($log_file_basic, $error_message . "\n", FILE_APPEND);
            
            // Registrar exceção no log detalhado
            $log_entry = "==== " . date("Y-m-d H:i:s") . " EXCEÇÃO NO ENVIO ====\n";
            $log_entry .= "ERRO: " . $e->getMessage() . "\n";
            $log_entry .= "TRACE: " . $e->getTraceAsString() . "\n\n";
            file_put_contents($log_file, $log_entry, FILE_APPEND);
            
            error_log("Erro ao enviar postback: " . $e->getMessage());
        }
    }
    
    return $success;
}

function add_white_click($data, $reason)
{
    $dataDir = __DIR__ . "/logs";
    $wclicksStore = new \SleekDB\Store("whiteclicks", $dataDir);

    $calledIp = $data->ip;
    $country = $data->country;
    $dt = new DateTime();
    $time = $dt->getTimestamp();
    $os = $data->os;
    $isp = str_replace(',', ' ', $data->isp);
    $user_agent = str_replace(',', ' ', $data->ua);

    $queryarr = [];
    if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '') {
        parse_str($_SERVER['QUERY_STRING'], $queryarr);
    }

    $click = [
        "time" => $time,
        "ip" => $calledIp,
        "country" => $country,
        "os" => $os,
        "isp" => $isp,
        "ua" => $user_agent,
        "reason" => $reason,
        "subs" => $queryarr
    ];

    try {
        $wclicksStore->insert($click);
    } catch (Exception $e) {
        error_log("Error in add_white_click: " . $e->getMessage());
        throw $e;
    }
}

function add_black_click($subid, $data, $preland, $land)
{
    $dataDir = __DIR__ . "/logs";
    $bclicksStore = new \SleekDB\Store("blackclicks", $dataDir);

    $calledIp = is_object($data) ? $data->ip : $data['ip'];
    $country = is_object($data) ? $data->country : $data['country'];
    $dt = new DateTime();
    $time = $dt->getTimestamp();
    $os = is_object($data) ? $data->os : $data['os'];
    $isp = is_object($data) ? str_replace(',', ' ', $data->isp) : str_replace(',', ' ', $data['isp']);
    $user_agent = is_object($data) ? str_replace(',', ' ', $data->ua) : str_replace(',', ' ', $data['ua']);
    $prelanding = empty($preland) ? 'unknown' : $preland;
    $landing = empty($land) ? 'unknown' : $land;

    $queryarr = [];
    if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '') {
        parse_str($_SERVER['QUERY_STRING'], $queryarr);
    }

    $click = [
        "subid" => $subid,
        "time" => $time,
        "ip" => $calledIp,
        "country" => $country,
        "os" => $os,
        "isp" => $isp,
        "ua" => $user_agent,
        "subs" => $queryarr,
        "preland" => $prelanding,
        "land" => $landing
    ];

    try {
        $bclicksStore->insert($click);
    } catch (Exception $e) {
        error_log("Error in add_black_click: " . $e->getMessage());
        throw $e;
    }
}

function add_lead($subid, $name, $phone, $status = 'Lead', $email = '')
{
    $dataDir = __DIR__ . "/logs";
    $leadsStore = new \SleekDB\Store("leads", $dataDir);

    $fbp = get_cookie('_fbp');
    $fbclid = get_cookie('fbclid');
    if ($fbclid === '') $fbclid = get_cookie('_fbc');

    if ($status == '') $status = 'Lead';

    $dt = new DateTime();
    $time = $dt->getTimestamp();

    $land = get_cookie('landing');
    if (empty($land)) $land = 'unknown';
    $preland = get_cookie('prelanding');
    if (empty($preland)) $preland = 'unknown';

    $lead = [
        "subid" => $subid,
        "time" => $time,
        "name" => $name,
        "phone" => $phone,
        "status" => $status,
        "fbp" => $fbp,
        "fbclid" => $fbclid,
        "preland" => $preland,
        "land" => $land
    ];
    
    // Adicionar o email se fornecido
    if (!empty($email)) {
        $lead["email"] = $email;
    }

    try {
        $leadsStore->insert($lead);
        
        // Enviar postback S2S para o evento 'Lead'
        error_log("Tentando enviar postback para lead: $subid, $name, $email");
        trigger_s2s_postback($subid, $status, $name, $email, $phone);
        
    } catch (Exception $e) {
        error_log("Error in add_lead: " . $e->getMessage());
        throw $e;
    }

    if (has_conversion_cookies($name, $phone)) {
        error_log("Conversion has already been done");
        return 0;
    }

    //добавляем в куку информацию о состоявшейся конверсии. Будем хранить 24 часа.
    ywbsetcookie('conversion',$name."||".$phone,(time()+86400));
    return 1;
}

function lead_exists($subid)
{
    $dataDir = __DIR__ . "/logs";
    $leadsStore = new \SleekDB\Store("leads", $dataDir);
    $bclicksStore = new \SleekDB\Store("blackclicks", $dataDir);

    $clkinfo = $bclicksStore->findBy([["subid","=",$subid]], ["time" => "desc"]);
    $leadinfo = $leadsStore->findBy([["subid","=",$subid]], ["time" => "desc"]);

    return (!empty($leadinfo)&&!empty($clkinfo));
}

function update_lead_status($subid, $status)
{
    $dataDir = __DIR__ . "/logs";
    $leadsStore = new \SleekDB\Store("leads", $dataDir);

    $lead = $leadsStore->findOneBy(["subid", "=", $subid]);

    if (!empty($lead)) {
        $old_status = $lead['status'];
        $lead['status'] = $status;
        $result = $leadsStore->update($lead);
        
        // Se o status mudou, enviar postback S2S
        if ($old_status !== $status) {
            $name = isset($lead['name']) ? $lead['name'] : '';
            $email = isset($lead['email']) ? $lead['email'] : '';
            $phone = isset($lead['phone']) ? $lead['phone'] : '';
            trigger_s2s_postback($subid, $status, $name, $email, $phone);
        }
        
        return $result;
    }
    return false;
}

function add_lpctr($subid, $preland, $land = '')
{
    $dataDir = __DIR__ . "/logs";
    $lpctrStore = new \SleekDB\Store("lpctr", $dataDir);
    $dt = new DateTime();
    $time = $dt->getTimestamp();

    $lpctr = [
        "time" => $time,
        "subid" => $subid,
        "preland" => $preland,
        "land" => $land
    ];
    $lpctrStore->insert($lpctr);
}

function update_lead($subid, $status, $payout = '')
{
    $dataDir = __DIR__ . "/logs";
    $leadsStore = new \SleekDB\Store("leads", $dataDir);
    $lead = $leadsStore->findOneBy([["subid", "=", $subid]]);
    if ($lead === null) {
        $bclicksStore = new \SleekDB\Store("blackclicks", $dataDir);
        $click = $bclicksStore->findOneBy([["subid", "=", $subid]]);
        if ($click === null) return false;
        $lead = add_lead($subid, '', '');
    }

    $old_status = $lead["status"];
    $lead["status"] = $status;
    if ($payout !== '') {
        $lead["payout"] = $payout;
    }
    
    try {
        $leadsStore->update($lead);
        
        // Se o status mudou, enviar postback S2S
        if ($old_status !== $status) {
            $name = isset($lead['name']) ? $lead['name'] : '';
            $email = isset($lead['email']) ? $lead['email'] : '';
            $phone = isset($lead['phone']) ? $lead['phone'] : '';
            trigger_s2s_postback($subid, $status, $name, $email, $phone);
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Error in update_lead: " . $e->getMessage());
        return false;
    }
}

function email_exists_for_subid($subid)
{
    $dataDir = __DIR__ . "/logs";
    $leadsStore = new \SleekDB\Store("leads", $dataDir);
    $lead = $leadsStore->findOneBy([["subid", "=", $subid]]);
    if ($lead === null) return false;
    if (array_key_exists("email", $lead)) return true;
    return false;
}

function add_email($subid, $email)
{
    $dataDir = __DIR__ . "/logs";
    $leadsStore = new \SleekDB\Store("leads", $dataDir);
    $lead = $leadsStore->findOneBy([["subid", "=", $subid]]);
    if ($lead === null) return;
    
    // Se o email mudou, atualizar o lead e enviar postback
    if (!isset($lead["email"]) || $lead["email"] !== $email) {
        $lead["email"] = $email;
        $leadsStore->update($lead);
        
        // Enviar postback com o novo email
        $name = isset($lead['name']) ? $lead['name'] : '';
        $phone = isset($lead['phone']) ? $lead['phone'] : '';
        $status = isset($lead['status']) ? $lead['status'] : 'Lead';
        
        trigger_s2s_postback($subid, $status, $name, $email, $phone);
    }
}

//проверяем, есть ли в файле лидов subid текущего пользователя
//если есть, и также есть такой же номер - значит ЭТО ДУБЛЬ!
//И нам не нужно слать его в ПП и не нужно показывать пиксель ФБ!!
function lead_is_duplicate($subid, $phone)
{
    $dataDir = __DIR__ . "/logs";
    $leadsStore = new \SleekDB\Store("leads", $dataDir);
    if ($subid != '') {
        $lead = $leadsStore->findOneBy([["subid", "=", $subid]]);
        if ($lead === null) return false;
        header("YWBDuplicate: We have this sub!");
        $phoneexists = ($lead["phone"] === $phone);
        if ($phoneexists) {
            header("YWBDuplicate: We have this phone!");
            return true;
        } else {
            return false;
        }
    } else {
        //если куки c subid у нас почему-то нет, то проверяем по номеру телефона
        $lead = $leadsStore->findOneBy([["phone", "=", $phone]]);
        if ($lead === null) return false;
        return true;
    }
}
