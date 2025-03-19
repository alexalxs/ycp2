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
    
    // Caminho completo do arquivo
    $file_path = $folder . '/' . $requested_file;
    
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
    
    // Verifica se o arquivo existe
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
        
        // Define o tipo MIME apropriado
        if (!headers_sent()) {
            if (isset($mime_types[$ext])) {
                header('Content-Type: ' . $mime_types[$ext]);
            }
            
            // Define cabeçalhos de cache adequados
            if ($ext !== 'php' && $ext !== 'html') {
                header('Cache-Control: public, max-age=31536000');
            } else {
                header('Cache-Control: no-store, no-cache, must-revalidate');
                header('Pragma: no-cache');
                header('Expires: Thu, 19 Nov 1981 08:52:00 GMT');
            }
        }
        
        // Para arquivos PHP, inclui o arquivo
        if ($ext === 'php') {
            include($file_path);
        } 
        // Para arquivos HTML, adiciona o base path e serve
        else if ($ext === 'html') {
            $folder_name = basename($folder);
            $content = file_get_contents($file_path);
            
            // Adiciona tag base para garantir que os links relativos funcionem
            // Agora considerando o caminho base do projeto
            $base_path = '';
            if (function_exists('get_base_path')) {
                $base_path = get_base_path();
            }
            
            // O base URL deve incluir o caminho base do projeto
            $base_url = $base_path . "/{$folder_name}/";
            $content = preg_replace('/<head([^>]*)>/', '<head$1><base href="' . $base_url . '">', $content);
            
            // Adiciona atributo data-prelanding ao botão para identificar a prelanding de origem
            // Não modificaremos o href original do botão, respeitando o link definido pelo usuário
            $content = str_replace('id="ctaButton"', 'id="ctaButton" data-prelanding="' . $folder_name . '"', $content);
            
            // Adiciona script para registrar cliques
            $buttonlog_script = '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    const ctaButton = document.getElementById("ctaButton");
                    if (ctaButton) {
                        ctaButton.addEventListener("click", function(e) {
                            // Registrar o clique
                            const prelanding = this.getAttribute("data-prelanding");
                            
                            // Desabilitar o botão para evitar cliques múltiplos
                            this.disabled = true;
                            
                            // Obter o URL de destino original definido pelo usuário
                            const originalHref = this.getAttribute("href");
                            
                            // Registrar o clique via buttonlog.php
                            fetch("' . $base_path . '/buttonlog.php", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                },
                                body: JSON.stringify({
                                    event: "lead_click",
                                    prelanding: prelanding,
                                    timestamp: new Date().toISOString()
                                }),
                            })
                            .then(response => response.json())
                            .then(data => {
                                console.log("Lead registrado com sucesso");
                                // Redirecionar para o URL original definido pelo usuário
                                window.location.href = originalHref;
                            })
                            .catch(error => {
                                console.error("Erro ao registrar lead:", error);
                                // Re-habilitar o botão em caso de erro
                                this.disabled = false;
                            });
                            
                            // Prevenir navegação padrão para permitir o processamento assíncrono
                            e.preventDefault();
                        });
                    }
                });
            </script>';
            
            $content = str_replace('</body>', $buttonlog_script . '</body>', $content);
            
            echo $content;
        } 
        // Para outros tipos de arquivo, serve diretamente
        else {
            readfile($file_path);
        }
        
        return true;
    }
    
    return false;
}

// Forçar recarregamento das configurações para evitar problemas de cache
clearstatcache(true, __DIR__.'/settings.json');

//передаём все параметры в кло
$cloaker = new Cloaker($os_white,$country_white,$lang_white,$ip_black_filename,$ip_black_cidr,$tokens_black,$url_should_contain,$ua_black,$isp_black,$block_without_referer,$referer_stopwords,$block_vpnandtor);

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