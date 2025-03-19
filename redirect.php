<?php
require_once 'url.php';

/**
 * Determina o caminho base do projeto em relação ao servidor
 * 
 * @return string O caminho base, por exemplo: /volume
 */
function get_base_path() {
    $script_name = $_SERVER['SCRIPT_NAME'] ?? '';
    $script_filename = $_SERVER['SCRIPT_FILENAME'] ?? '';
    
    if (empty($script_name) || empty($script_filename)) {
        return '';
    }
    
    // Remover o nome do arquivo do caminho
    $script_dir = dirname($script_name);
    
    // Se estiver na raiz, retorna uma string vazia
    if ($script_dir == '/' || $script_dir == '\\') {
        return '';
    }
    
    return $script_dir;
}

function redirect($url, $redirect_type = 302, $add_querystring = true)
{
    // Verifica se a URL começa com http:// ou https:// (URL absoluta)
    if (!preg_match('/^https?:\/\//', $url)) {
        // URL relativa, precisamos adicionar o caminho base
        $base_path = get_base_path();
        
        // Se a URL não começa com /, adiciona
        if (substr($url, 0, 1) !== '/') {
            $url = '/' . $url;
        }
        
        // Se a URL começa com o caminho base, não adiciona novamente
        if (substr($url, 0, strlen($base_path)) !== $base_path) {
            $url = $base_path . $url;
        }
    }
    
    if ($add_querystring === true) {
        $url = add_querystring($url);
    }
    
    if ($redirect_type === 302) {
        header('X-Robots-Tag: noindex, nofollow');
        header('Location: ' . $url);
    } else {
        header('X-Robots-Tag: noindex, nofollow');
        header('Location: ' . $url, true, $redirect_type);
    }
}

function jsredirect($url)
{
    // Verifica se a URL começa com http:// ou https:// (URL absoluta)
    if (!preg_match('/^https?:\/\//', $url)) {
        // URL relativa, precisamos adicionar o caminho base
        $base_path = get_base_path();
        
        // Se a URL não começa com /, adiciona
        if (substr($url, 0, 1) !== '/') {
            $url = '/' . $url;
        }
        
        // Se a URL começa com o caminho base, não adiciona novamente
        if (substr($url, 0, strlen($base_path)) !== $base_path) {
            $url = $base_path . $url;
        }
    }
    
    echo "<script type='text/javascript'> window.location='$url';</script>";
    return;
}
