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

function serve_file($folder, $requested_file) {
    // Normaliza o path e previne directory traversal
    $requested_file = str_replace('..', '', $requested_file);
    $requested_file = ltrim($requested_file, '/');
    
    // Se não houver arquivo especificado, usa index.html
    if (empty($requested_file)) {
        $requested_file = 'index.html';
    }
    
    $file_path = $folder . '/' . $requested_file;
    $folder_name = basename($folder);
    
    // Se for diretório, procura por index
    if (is_dir($file_path)) {
        if (file_exists($file_path . '/index.html')) {
            $file_path = $file_path . '/index.html';
        } elseif (file_exists($file_path . '/index.php')) {
            $file_path = $file_path . '/index.php';
        } else {
            return false;
        }
    }
    
    if (file_exists($file_path)) {
        $ext = pathinfo($file_path, PATHINFO_EXTENSION);
        $mime_types = [
            'html' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'eot' => 'application/vnd.ms-fontobject',
            'ico' => 'image/x-icon',
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav'
        ];
        
        if (isset($mime_types[$ext])) {
            header('Content-Type: ' . $mime_types[$ext]);
        }
        
        // Preserva cookies e headers existentes
        if (isset($_COOKIE)) {
            foreach ($_COOKIE as $key => $value) {
                setcookie($key, $value);
            }
        }
        
        // Cache control para arquivos estáticos
        if ($ext !== 'php' && $ext !== 'html') {
            header('Cache-Control: public, max-age=31536000');
        } else {
            header('Cache-Control: no-store, no-cache, must-revalidate');
        }
        
        if ($ext === 'php') {
            include($file_path);
        } else if ($ext === 'html') {
            // Ajusta caminhos relativos no HTML
            $content = file_get_contents($file_path);
            $base_path = '/' . $folder_name . '/';
            
            // Ajusta src e href
            $content = preg_replace('/\ssrc=[\'\"](?!http|\/\/|data:|\/|#)([^\'\"]+)[\'\"]/', " src=\"$base_path\\1\"", $content);
            $content = preg_replace('/\shref=[\'\"](?!http|\/\/|data:|\/|#|mailto:|tel:)([^\'\"]+)[\'\"]/', " href=\"$base_path\\1\"", $content);
            
            echo $content;
        } else {
            readfile($file_path);
        }
        return true;
    }
    return false;
}

//передаём все параметры в кло
$cloaker = new Cloaker($os_white,$country_white,$lang_white,$ip_black_filename,$ip_black_cidr,$tokens_black,$url_should_contain,$ua_black,$isp_black,$block_without_referer,$referer_stopwords,$block_vpnandtor);

//если включен full_cloak_on, то шлём всех на white page, полностью набрасываем плащ)
if ($tds_mode=='full') {
    add_white_click($cloaker->detect, ['fullcloak']);
    white(false);
    return;
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
		if ($black_land_action === 'folder') {
			$folder = select_item($black_land_folder_names, $save_user_flow, 'black', true)[0];
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
	}
}