<?php
//Включение отладочной информации
ini_set('display_errors', '1');
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//Конец включения отладочной информации

require_once 'settings.php';
require_once 'db.php';
require_once 'cookies.php';
require_once 'redirect.php';
require_once 'requestfunc.php';

// Habilitar CORS para permitir solicitações do formulário
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Registrar todas as requisições para depuração
error_log("==== REQUISIÇÃO PARA SEND.PHP ====");
error_log("Método: " . $_SERVER['REQUEST_METHOD']);
error_log("Headers: " . json_encode(getallheaders()));
error_log("POST: " . json_encode($_POST));
error_log("GET: " . json_encode($_GET));
error_log("RAW: " . file_get_contents('php://input'));

// Se for uma requisição OPTIONS (preflight CORS), responder imediatamente
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Verifica se é uma ação de captura de email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'email') {
    error_log("==== PROCESSANDO CAPTURA DE EMAIL ====");
    
    // Verificar e processar o email
    if (!isset($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        error_log("ERRO: Email inválido ou ausente: " . (isset($_POST['email']) ? $_POST['email'] : 'não definido'));
        http_response_code(400);
        echo json_encode(['error' => 'Email inválido']);
        exit;
    }

    $email = $_POST['email'];
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $subid = isset($_POST['subid']) ? $_POST['subid'] : (isset($_COOKIE['subid']) ? $_COOKIE['subid'] : '');

    // Debug detalhado
    error_log("Email capturado: $email");
    error_log("Nome capturado: $name");
    error_log("SubID: $subid");

    if (empty($subid)) {
        // Se não tiver subid, gerar um novo
        $subid = md5(uniqid(rand(), true));
        ywbsetcookie('subid', $subid, '/');
        error_log("Novo SubID gerado: $subid");
    }

    // Registrar o lead no banco de dados (incluindo o nome)
    // Usar o nome padrão se não for fornecido
    $user_name = !empty($name) ? $name : 'Lead de Email';
    error_log("Nome final para registro: $user_name");
    
    // Registrar o lead (com um número de telefone fictício para compatibilidade)
    $phone = '00000000000'; // Telefone fictício para manter compatibilidade
    
    // TRATAMENTO DE ERROS COMPLETO
    try {
        // Verificar permissões e existência de diretórios
        $dataDir = __DIR__ . "/logs";
        if (!is_dir($dataDir)) {
            error_log("ERRO CRÍTICO: Diretório logs não existe: $dataDir");
            throw new Exception("Diretório logs não existe");
        }
        
        if (!is_writable($dataDir)) {
            error_log("ERRO CRÍTICO: Diretório logs sem permissão de escrita: $dataDir");
            throw new Exception("Diretório logs sem permissão de escrita");
        }
        
        // Verificar diretório de leads especificamente
        $leadsDir = $dataDir . "/leads";
        if (!is_dir($leadsDir)) {
            error_log("ERRO CRÍTICO: Diretório de leads não existe: $leadsDir");
            throw new Exception("Diretório de leads não existe");
        }
        
        if (!is_writable($leadsDir)) {
            error_log("ERRO CRÍTICO: Diretório de leads sem permissão de escrita: $leadsDir");
            throw new Exception("Diretório de leads sem permissão de escrita");
        }
        
        error_log("TENTANDO: add_lead($subid, $user_name, $phone, 'Lead')");
        
        // Testar o add_lead com log detalhado
        $lead_result = add_lead($subid, $user_name, $phone, 'Lead');
        error_log("Resultado add_lead: " . ($lead_result ? "Sucesso" : "Falha"));
        
        // Registrar o email no banco de dados
        error_log("TENTANDO: add_email($subid, $email)");
        add_email($subid, $email);
        error_log("Email registrado com sucesso para subid: $subid");
        
        // Responder com sucesso
        http_response_code(200);
        echo json_encode([
            'success' => true, 
            'message' => 'Lead registrado com sucesso',
            'subid' => $subid,
            'name' => $user_name,
            'email' => $email
        ]);
    } catch (Exception $e) {
        // Registrar erro detalhado
        error_log("ERRO CRÍTICO ao registrar lead: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        
        http_response_code(500); // Mudar para erro real para facilitar debug
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage(),
            'details' => 'Erro ao processar o lead. Por favor contate o suporte.'
        ]);
    }
    exit;
}

// Processamento de leads normais
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar parâmetros necessários
    if (!isset($_POST['name']) || !isset($_POST['phone'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Parâmetros obrigatórios ausentes']);
        exit;
    }

    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $subid = isset($_POST['subid']) ? $_POST['subid'] : (isset($_COOKIE['subid']) ? $_COOKIE['subid'] : '');

    if (empty($subid)) {
        // Se não tiver subid, gerar um novo
        $subid = md5(uniqid(rand(), true));
        ywbsetcookie('subid', $subid, '/');
    }

    // Registrar o lead no banco de dados
    add_lead($subid, $name, $phone);

    // Se tiver email, registrá-lo também
    if (isset($_POST['email']) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        add_email($subid, $_POST['email']);
    }

    // Redirecionamento após o formulário ser processado
    if (isset($_POST['oferta'])) {
        $landing = $_POST['oferta'];
        if (file_exists(__DIR__ . '/' . $landing . '/thankyou.html')) {
            header('Location: /' . $landing . '/thankyou.html');
            exit;
        }
    }

    // Responder com sucesso se não houver redirecionamento
    echo json_encode(['success' => true, 'message' => 'Lead registrado com sucesso']);
    exit;
}

// Se não for um método suportado
http_response_code(405);
echo json_encode(['error' => 'Método não permitido']);

$name = '';
if (isset($_POST['name']))
    $name=$_POST['name'];
else if (isset($_POST['fio']))
    $name=$_POST['fio'];
else if (isset($_POST['first_name'])&&isset($_POST['last_name']))
    $name = $_POST['first_name'].' '.$_POST['last_name'];
else if (isset($_POST['firstname'])&&isset($_POST['lastname']))
    $name = $_POST['firstname'].' '.$_POST['lastname'];

$phone='';
if (isset($_POST['phone']))
    $phone=$_POST['phone'];
else if (isset($_POST['tel']))
    $phone=$_POST['tel'];

$subid = get_subid();
if ($subid==='' && isset($_POST['subid']))
    $subid=$_POST['subid'];

//если юзверь каким-то чудом отправил пустые поля в форме
if ($name===''||$phone===''){
    redirect('thankyou/thankyou.php?nopixel=1');
    return;
}

$date = new DateTime();
$ts = $date->getTimestamp();

$is_duplicate=has_conversion_cookies($name,$phone);
//устанавливаем пользователю в куки его имя и телефон, чтобы показать их на стр Спасибо
//также ставим куки даты конверсии
ywbsetcookie('name',$name,'/');
ywbsetcookie('phone',$phone,'/');
ywbsetcookie('ctime',$ts,'/');


//шлём в ПП только если это не дубль
if (!$is_duplicate){
    $fullpath='';
    //если у формы прописан в action адрес, а не локальный скрипт, то шлём все данные формы на этот адрес
    if (substr($black_land_conversion_script, 0, 4 ) === "http"){
        $fullpath=$black_land_conversion_script;
    }
    //иначе составляем полный адрес до скрипта отправки ПП
    else{
        $url= get_cookie('landing').'/'.$black_land_conversion_script;
        $fullpath = get_abs_from_rel($url);
    }

    //на всякий случай, перед отправкой чекаем, установлен ли subid
    $sub_rewrites=array_column($sub_ids,'rewrite','name');
    if (array_key_exists('subid',$sub_rewrites)){
        if (!isset($_POST[$sub_rewrites['subid']])||
            $_POST[$sub_rewrites['subid']]!==$subid)
            $_POST[$sub_rewrites['subid']]=$subid;
    }

    $res=post($fullpath,http_build_query($_POST));

    //в ответе должен быть редирект, если его нет - грузим обычную страницу Спасибо кло
    switch($res["info"]["http_code"]){
        case 302:
            add_lead($subid,$name,$phone);
            if ($black_land_use_custom_thankyou_page ){
                redirect("thankyou/thankyou.php?".http_build_query($_GET),302,false);
            }
            else{
                redirect($res["info"]["redirect_url"]);
            }
            break;
        case 200:
            add_lead($subid,$name,$phone);
            if ($black_land_use_custom_thankyou_page ){
                jsredirect("thankyou/thankyou.php?".http_build_query($_GET));
            }
            else{
                echo $res["html"];
            }
            break;
        default:
            var_dump($res["error"]);
            var_dump($res["info"]);
            exit();
            break;
    }
}
else
{
    redirect('thankyou/thankyou.php?nopixel=1');
}

// Nota: A integração com webhook externo foi removida deste arquivo.
// Agora o navegador/cliente envia os dados diretamente para o webhook,
// enquanto este script (send.php) apenas registra os leads no banco de dados local.
// Esta arquitetura reduz a carga no servidor e elimina pontos de falha.

?>