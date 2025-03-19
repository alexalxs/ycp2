<?php
/**
 * Função especializada para servir arquivos das pastas de prelanding e landing
 * Inclui processamento de HTML para adicionar elementos de rastreamento e
 * modificar os links para usar buttonlog.php
 *
 * @param string $folder Nome da pasta a ser servida (ex: "preland", "landing")
 * @param string $requested_file Nome do arquivo solicitado
 * @return bool True se o arquivo foi servido com sucesso, False caso contrário
 */
function serve_file_enhanced($folder, $requested_file) {
    // Normaliza o path e previne directory traversal
    $requested_file = str_replace('..', '', $requested_file);
    $requested_file = ltrim($requested_file, '/');
    
    // Obter o nome da pasta atual dinamicamente
    $dir_name = basename(dirname(__FILE__));
    $path = __DIR__ . '/' . $folder . '/' . $requested_file;
    
    error_log("serve_file_enhanced - Path: $path");
    error_log("serve_file_enhanced - Folder: $folder, File: $requested_file");
    
    // Se nenhum arquivo especificado, assume index.html
    if (empty($requested_file)) {
        $requested_file = 'index.html';
        $path = __DIR__ . '/' . $folder . '/index.html';
    }
    
    // Se for um diretório, procura por index.html ou index.php
    if (is_dir($path)) {
        if (file_exists($path . '/index.html')) {
            $path = $path . '/index.html';
            $requested_file = 'index.html';
        } elseif (file_exists($path . '/index.php')) {
            $path = $path . '/index.php';
            $requested_file = 'index.php';
        } else {
            return false;
        }
    }
    
    if (file_exists($path)) {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        
        // Definir cabeçalhos MIME com base na extensão
        $mime_types = [
            'html' => 'text/html',
            'htm' => 'text/html',
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
        
        // Processamento especial para arquivos HTML
        if ($ext === 'html' || $ext === 'htm') {
            $html = file_get_contents($path);
            
            // Adicionar tag <base> para garantir que links relativos funcionem
            $base_url = "/$dir_name/$folder/";
            $base_tag = "<base href=\"$base_url\">";
            $html = preg_replace('/<head>/i', '<head>' . $base_tag, $html);
            
            // Substituir URLs relativas por URLs absolutas para recursos estáticos
            // Isso garante que links CSS, JS e imagens funcionem corretamente 
            $html = preg_replace('/(src|href)=(["\'])(?!(https?:\/\/|\/\/|\/|#))([^"\']+)(["\'])/i', 
                                '$1=$2' . $base_url . '$4$5', $html);
            
            // Substituir links para botões por chamadas ao buttonlog.php
            $html = preg_replace_callback(
                '/<a\s+([^>]*)(href=["\'])([^"\'>]+)(["\'])([^>]*)>/i',
                function($matches) use ($folder, $dir_name) {
                    $attrs = $matches[1];
                    $href_start = $matches[2];
                    $href = $matches[3];
                    $href_end = $matches[4];
                    $trailing = $matches[5];
                    
                    // Ignorar links que começam com # ou contém "mailto:"
                    if (strpos($href, '#') === 0 || strpos($href, 'mailto:') !== false) {
                        return $matches[0];
                    }
                    
                    // Adicionar id="ctaButton" se não existir e se parece um botão CTA
                    // (identificados por classes ou textos comuns em CTAs)
                    $is_button_like = (
                        strpos($attrs . $trailing, 'class="') !== false && 
                        (strpos($attrs . $trailing, 'btn') !== false || 
                         strpos($attrs . $trailing, 'button') !== false || 
                         strpos($attrs . $trailing, 'cta') !== false)
                    ) || (
                        preg_match('/(comprar|compra|solicitar|clique|click|saiba mais|continue|enviar|submit)/i', $attrs . $trailing . $href)
                    );
                    
                    if ($is_button_like && strpos($attrs . $trailing, 'id=') === false) {
                        $trailing .= ' id="ctaButton"';
                    }
                    
                    // Adicionar evento onclick para rastrear o clique se parece um botão CTA
                    if ($is_button_like) {
                        $onclick = ' onclick="trackButtonClick(event, \'' . $folder . '\', \'' . $href . '\')"';
                        return '<a ' . $attrs . $href_start . $href . $href_end . $trailing . $onclick . '>';
                    }
                    
                    // Retornar o link original se não for um botão CTA
                    return $matches[0];
                },
                $html
            );
            
            // Inserir script de rastreamento antes do </body>
            $tracking_script = <<<EOT
<script>
function trackButtonClick(event, prelanding, destination) {
    event.preventDefault();
    
    // Preparar dados para enviar
    var data = {
        event: 'lead_click',
        prelanding: prelanding,
        timestamp: new Date().toISOString()
    };
    
    console.log('Enviando dados para buttonlog.php:', data);
    
    // Enviar dados para buttonlog.php
    fetch('/$dir_name/buttonlog.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        console.log('Resposta recebida:', data);
        if (data.success && data.redirect) {
            console.log('Redirecionando para:', data.redirect);
            // Redirecionar para a URL indicada
            window.location.href = data.redirect;
        } else {
            console.log('Fallback para URL original:', destination);
            // Fallback para a URL original
            window.location.href = destination;
        }
    })
    .catch(error => {
        console.error('Erro ao processar clique:', error);
        // Em caso de erro, redirecionar para a URL original
        window.location.href = destination;
    });
}
</script>
EOT;
            
            $html = str_replace('</body>', $tracking_script . '</body>', $html);
            
            echo $html;
        } elseif ($ext === 'php') {
            // Se for um arquivo PHP, incluí-lo para execução
            include($path);
        } else {
            // Outros tipos de arquivo são servidos diretamente
            readfile($path);
        }
        
        return true;
    }
    
    return false;
}

/**
 * Procura e serve recursos estáticos (CSS, JS, imagens, etc) das pastas
 * prelanding ou landing mesmo quando requisitados via URL incorreta
 * 
 * @param string $requested_path Caminho solicitado
 * @return bool True se o arquivo foi servido, False caso contrário
 */
function serve_static_resource($requested_path) {
    // Remover o caminho base e barras iniciais
    $dir_name = basename(dirname(__FILE__));
    $path_without_base = preg_replace("#^/$dir_name/#", "", $requested_path);
    
    error_log("serve_static_resource - Path: $requested_path, Clean path: $path_without_base");
    
    // Lista de pastas para procurar recursos
    $folders_to_check = [
        'preland',
        'preland2',
        'landing'
    ];
    
    // Verificar cada pasta
    foreach ($folders_to_check as $folder) {
        $full_path = __DIR__ . '/' . $folder . '/' . $path_without_base;
        error_log("serve_static_resource - Verificando: $full_path");
        
        if (file_exists($full_path)) {
            error_log("serve_static_resource - Encontrado em: $folder");
            $ext = strtolower(pathinfo($full_path, PATHINFO_EXTENSION));
            
            // Definir cabeçalhos MIME com base na extensão
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
            readfile($full_path);
            return true;
        }
    }
    
    return false;
} 