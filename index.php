<?php
//Включение отладочной информации
ini_set('display_errors', '1');
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//Конец включения отладочной информации

require_once 'core.php';
require_once 'settings.php';
require_once 'db.php';
require_once 'main.php';
require_once 'serve_file.php'; // Incluir o novo arquivo com a função melhorada

function serve_file($folder, $requested_file) {
    // Normaliza o path e previne directory traversal
    $requested_file = str_replace('..', '', $requested_file);
    $requested_file = ltrim($requested_file, '/');
    
    // Obter o nome da pasta atual dinamicamente
    $dir_name = basename(dirname(__FILE__));
    $path = __DIR__ . '/' . $folder . '/' . $requested_file;
    
    error_log("serve_file - Path: $path");
    error_log("serve_file - Folder: $folder, File: $requested_file");
    
    if (file_exists($path)) {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        
        if ($ext === 'html' || $ext === 'htm') {
            $html = file_get_contents($path);
            
            // Substituir URLs de scripts, CSS e imagens
            $base_url = "/$dir_name/$folder/";
            $html = preg_replace('/(src|href)=(["\'])(?!https?:\/\/|\/\/|\/|#)([^"\']+)(["\'])/i', '$1=$2' . $base_url . '$3$4', $html);
            
            echo $html;
            return true;
        } else {
            // Para outros tipos de arquivo, servir diretamente
            $mime_types = [
                'css' => 'text/css',
                'js' => 'application/javascript',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'svg' => 'image/svg+xml',
                'ico' => 'image/x-icon',
                'ttf' => 'font/ttf',
                'woff' => 'font/woff',
                'woff2' => 'font/woff2',
                'eot' => 'application/vnd.ms-fontobject',
                'mp4' => 'video/mp4',
                'webm' => 'video/webm',
                'ogv' => 'video/ogg',
                'json' => 'application/json',
                'xml' => 'application/xml',
                'pdf' => 'application/pdf',
                'txt' => 'text/plain'
            ];
            
            $mime = $mime_types[$ext] ?? 'application/octet-stream';
            header('Content-Type: ' . $mime);
            readfile($path);
            return true;
        }
    }
    
    return false;
}

// Forçar recarregamento das configurações para evitar problemas de cache
clearstatcache(true, __DIR__.'/settings.json');

// Incluir o arquivo redirect.php para usar a função get_base_path()
require_once 'redirect.php';

// Definir a variável global para o caminho base
$base_path = get_base_path();

//передаём все параметры в кло
$cloaker = new Cloaker($os_white,$country_white,$lang_white,$ip_black_filename,$ip_black_cidr,$tokens_black,$url_should_contain,$ua_black,$isp_black,$block_without_referer,$referer_stopwords,$block_vpnandtor);

// Debug para entender o que está acontecendo
error_log("Processando requisição: " . $_SERVER['REQUEST_URI']);

// Obter o nome da pasta atual dinamicamente
$dir_name = basename(dirname(__FILE__));
error_log("Nome da pasta atual: $dir_name");

// Verificar se estamos acessando diretamente a pasta /$dir_name/
if ($_SERVER['REQUEST_URI'] === "/$dir_name/" || $_SERVER['REQUEST_URI'] === "/$dir_name") {
    error_log("Acesso direto à pasta /$dir_name/ detectado");
    
    // Verificar se TDS está ativado
    $is_tds_enabled = $tds_mode !== 'off';
    error_log("TDS Status: " . ($is_tds_enabled ? 'Ativado' : 'Desativado'));
    
    // Se TDS estiver desativado ou se o IP do usuário estiver na whitelist
    if (!$is_tds_enabled || (isset($cloaker) && $cloaker->whitelist_match)) {
        error_log("TDS desativado ou IP na whitelist - servindo conteúdo black page");
        
        // Prioridade 1: Verificar se prelanding está ativada (conforme o diagrama)
        if ($black_preland_action === 'folder' && !empty($black_preland_folder_names)) {
            error_log("Prelanding habilitada com action=folder e pastas: " . implode(", ", $black_preland_folder_names));
            
            // Selecionar uma prelanding conforme as regras de AB testing
            $index = rand(0, count($black_preland_folder_names) - 1);
            $folder = $black_preland_folder_names[$index];
            
            error_log("Prelanding ativada - servindo pasta: $folder");
            ywbsetcookie('prelanding', $folder, '/');
            
            // Registro de visualização para estatísticas
            $cursubid = set_subid();
            $cookie_name = 'visited_' . $folder;
            if (!isset($_COOKIE[$cookie_name])) {
                add_black_click($cursubid, $cloaker->detect, $folder, '');
                ywbsetcookie($cookie_name, '1', '/');
            }
            
            // Verificar se o arquivo existe antes de tentar servi-lo
            $file_path = __DIR__ . '/' . $folder . '/index.html';
            error_log("Verificando existência do arquivo: $file_path - Existe: " . (file_exists($file_path) ? "Sim" : "Não"));
            
            // Usar a função serve_file_enhanced no lugar de serve_file
            if (serve_file_enhanced($folder, 'index.html')) {
                exit;
            } else {
                error_log("Falha ao servir o arquivo da prelanding: $folder/index.html");
            }
        }
        
        // Prioridade 2: Se prelanding não estiver ativada ou falhou, servir landing
        if ($black_land_action === 'folder' && !empty($black_land_folder_names)) {
            // Selecionar uma landing conforme as regras de AB testing
            $folder = select_item($black_land_folder_names, $save_user_flow, 'black', true)[0];
            
            error_log("Servindo landing diretamente - pasta: $folder");
            
            // Registro de visualização para estatísticas
            $cursubid = set_subid();
            $cookie_name = 'visited_landing_' . $folder;
            if (!isset($_COOKIE[$cookie_name])) {
                add_black_click($cursubid, $cloaker->detect, '', $folder);
                ywbsetcookie($cookie_name, '1', '/');
            }
            
            // Verificar se o arquivo existe antes de tentar servi-lo
            $file_path = __DIR__ . '/' . $folder . '/index.html';
            error_log("Verificando existência do arquivo: $file_path - Existe: " . (file_exists($file_path) ? "Sim" : "Não"));
            
            // Usar a função serve_file_enhanced no lugar de serve_file
            if (serve_file_enhanced($folder, 'index.html')) {
                exit;
            } else {
                error_log("Falha ao servir o arquivo da landing: $folder/index.html");
            }
        }
    } else {
        // Se TDS estiver ativado e o IP não estiver na whitelist
        // Verificar se white action está configurada como folder
        if ($white_action === 'folder' && !empty($white_folder_names)) {
            $folder = $white_folder_names[0]; // Pega a primeira pasta white
            error_log("TDS ativado - servindo white page: $folder");
            
            if (serve_file($folder, 'index.html')) {
                exit;
            } else {
                error_log("Falha ao servir o arquivo white/index.html");
            }
        }
    }
    
    // Se tudo falhar, exibir uma mensagem de erro
    header("HTTP/1.0 404 Not Found");
    echo "<h1>Site em Manutenção</h1>";
    echo "<p>Tente novamente mais tarde.</p>";
    exit;
}

// Verificar se é uma requisição para um recurso estático (CSS, JS, imagem, etc.)
elseif (strpos($_SERVER['REQUEST_URI'], "/$dir_name/assets/") !== false ||
        strpos($_SERVER['REQUEST_URI'], "/$dir_name/css/") !== false ||
        strpos($_SERVER['REQUEST_URI'], "/$dir_name/js/") !== false ||
        strpos($_SERVER['REQUEST_URI'], "/$dir_name/img/") !== false ||
        strpos($_SERVER['REQUEST_URI'], "/$dir_name/images/") !== false) {
            
    error_log("Requisição para recurso estático detectada: " . $_SERVER['REQUEST_URI']);
    
    // Tentar servir o recurso estático das pastas de prelanding ou landing
    if (serve_static_resource($_SERVER['REQUEST_URI'])) {
        exit; // Recurso servido com sucesso
    }
    
    // Se o recurso não foi encontrado, continuar com o fluxo normal
    error_log("Recurso estático não encontrado em nenhuma pasta conhecida");
}

// Limpar cookies que possam estar interferindo quando no modo full
if ($tds_mode=='full') {
    if (isset($_COOKIE['black'])) {
        setcookie('black', '', time() - 3600, '/');
    }
    if (isset($_COOKIE['landing'])) {
        setcookie('landing', '', time() - 3600, '/');
    }
    if (isset($_COOKIE['prelanding'])) {
        setcookie('prelanding', '', time() - 3600, '/');
    }
    
    error_log("TDS Mode Full: Redirecionando para white");
    add_white_click($cloaker->detect, ['fullcloak']);
    white(false);
    exit; // Garantir que nenhum código adicional seja executado
}

//если используются js-проверки, то сначала используются они
//проверка же обычная идёт далее в файле js/jsprocessing.php
if ($use_js_checks===true) {
	white(true);
}
else{
	//Проверяем зашедшего пользователя
	$check_result = $cloaker->check();

	if ($check_result == 0 || $tds_mode==='off') { //Обычный юзверь или отключена фильтрация
		// Verificar se a whitelist de IPs foi validada, o que evita verificação das demais condições
		if ($cloaker->whitelist_match) {
			// Se o IP está na whitelist, vamos diretamente para a black page
			if ($black_land_action === 'folder') {
				$folder = select_item($black_land_folder_names, $save_user_flow, 'black', true)[0];
				$cursubid = set_subid();
				add_black_click($cursubid, $cloaker->detect, '', $folder);
				
				$requested_file = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
				if ($requested_file === '/' || $requested_file === '') {
					$requested_file = '/index.html';
				}
				
				// Servir os arquivos diretamente da pasta black
				if (serve_file($folder, $requested_file)) {
					exit;
				}
			}
		}
		
		// Verificar primeiro se usa prelanding
		if ($black_preland_action === 'folder') {
			// Garantir que temos um subid válido para rastreamento
			$cursubid = set_subid();
			
			// Verificar se há um parâmetro key na URL que deve forçar uma prelanding específica
			$force_prelanding = null;
			if (isset($_GET['key']) && is_numeric($_GET['key']) && $_GET['key'] >= 1 && $_GET['key'] <= count($black_preland_folder_names)) {
				$force_prelanding = $black_preland_folder_names[$_GET['key'] - 1];
			}
			
			// Selecionar prelanding com base nas regras de teste A/B
			if ($force_prelanding !== null) {
				// Forçar uma prelanding específica pelo parâmetro key
				$folder = $force_prelanding;
				ywbsetcookie('prelanding', $folder, '/');
			} else if ($save_user_flow && isset($_COOKIE['prelanding']) && in_array($_COOKIE['prelanding'], $black_preland_folder_names)) {
				// Usar prelanding salva no cookie se save_user_flow estiver ativado
				$folder = $_COOKIE['prelanding'];
			} else {
				// Selecionar aleatoriamente para teste A/B
				$index = rand(0, count($black_preland_folder_names) - 1);
				$folder = $black_preland_folder_names[$index];
				ywbsetcookie('prelanding', $folder, '/');
			}
			
			// Registrar visualização para estatísticas apenas para visitantes que não acessaram
			// esta prelanding antes (evita contar duas vezes o mesmo visitante no Traffic)
			$cookie_name = 'visited_' . $folder;
			if (!isset($_COOKIE[$cookie_name])) {
				// Primeiro acesso a esta prelanding
				add_black_click($cursubid, $cloaker->detect, $folder, '');
				// Definir cookie para marcar que o usuário já acessou esta prelanding
				ywbsetcookie($cookie_name, '1', '/');
			}
			
			// Obter o arquivo solicitado da URL
			$requested_file = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
			
			// Remover o caminho base do projeto, se necessário
			$base_path = '';
			if (function_exists('get_base_path')) {
				$base_path = get_base_path();
				if (!empty($base_path) && strpos($requested_file, $base_path) === 0) {
					$requested_file = substr($requested_file, strlen($base_path));
				}
			}
			
			if ($requested_file === '/' || $requested_file === '') {
				$requested_file = '/index.html';
			}
			
			// Servir o arquivo da pasta prelanding diretamente (sem redirecionamento)
			if (serve_file($folder, $requested_file)) {
				exit;
			} else {
				header("HTTP/1.0 404 Not Found");
				echo "<h1>404 Not Found</h1>";
				exit;
			}
		}
		// Se não usar prelanding, então processar a landing conforme o original
		else if ($black_land_action === 'folder') {
			$folder = select_item($black_land_folder_names, $save_user_flow, 'black', true)[0];
			$requested_file = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
			if ($requested_file === '/' || $requested_file === '') {
				$requested_file = '/index.html';
			}
			
			// Registrar acesso à landing apenas para visitantes que não acessaram
			// esta landing antes (evita contar duas vezes o mesmo visitante nos Clicks)
			$cookie_name = 'visited_landing_' . $folder;
			$cursubid = set_subid();
			if (!isset($_COOKIE[$cookie_name])) {
				// Primeiro acesso a esta landing
				add_black_click($cursubid, $cloaker->detect, '', $folder);
				// Definir cookie para marcar que o usuário já acessou esta landing
				ywbsetcookie($cookie_name, '1', '/');
			}
			
			// Servir o arquivo da pasta landing diretamente (sem redirecionamento)
			if (serve_file($folder, $requested_file)) {
				exit;
			} else {
				header("HTTP/1.0 404 Not Found");
				echo "<h1>404 Not Found</h1>";
				exit;
			}
		}
		// Se não usar folder para landing, usar black() para outros casos
		black($cloaker->detect);
	} else { //Обнаружили бота или модера
		if ($white_action === 'folder') {
			$folder = select_item($white_folder_names, $save_user_flow, 'white', true)[0];
			$requested_file = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
			if ($requested_file === '/' || $requested_file === '') {
				$requested_file = '/index.html';
			}
			
			if (!serve_file($folder, $requested_file)) {
				header("HTTP/1.0 404 Not Found");
				echo "<h1>404 Not Found</h1>";
				exit;
			}
			exit;
		}
		add_white_click($cloaker->detect, $cloaker->result);
		white(false);
		exit; // Garantir que nenhum código adicional seja executado
	}
}