<?php
use Noodlehaus\Config;
use Noodlehaus\Writer\Json;
require_once '../config/ConfigInterface.php';
require_once '../config/AbstractConfig.php';
require_once '../config/Config.php';
require_once '../config/Parser/ParserInterface.php';
require_once '../config/Parser/Json.php';
require_once '../config/Writer/WriterInterface.php';
require_once '../config/Writer/AbstractWriter.php';
require_once '../config/Writer/Json.php';
require_once '../config/ErrorException.php';
require_once '../config/Exception.php';
require_once '../config/Exception/ParseException.php';
require_once '../config/Exception/FileNotFoundException.php';
require_once '../redirect.php';

require_once 'password.php';
check_password();

// Cria um arquivo de log dedicado para diagnósticos
$log_file = __DIR__ . '/save_debug.log';
file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "--- INÍCIO DO PROCESSAMENTO ---\n", FILE_APPEND);
file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "POST recebido: " . json_encode($_POST) . "\n", FILE_APPEND);

try {
    // Verificar permissões do arquivo settings.json
    $settings_file = __DIR__ . '/../settings.json';
    $permissions = fileperms($settings_file);
    $owner = posix_getpwuid(fileowner($settings_file))['name'];
    $current_user = exec('whoami');
    
    file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "Arquivo: $settings_file\n", FILE_APPEND);
    file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "Permissões: " . substr(sprintf('%o', $permissions), -4) . "\n", FILE_APPEND);
    file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "Proprietário: $owner\n", FILE_APPEND);
    file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "Usuário atual: $current_user\n", FILE_APPEND);
    
    // Verificar se o arquivo é gravável
    if (!is_writable($settings_file)) {
        file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "ERRO: Arquivo settings.json não é gravável!\n", FILE_APPEND);
        // Tentar corrigir as permissões
        if (!chmod($settings_file, 0666)) {
            file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "ERRO: Falha ao tentar alterar permissões do arquivo!\n", FILE_APPEND);
        } else {
            file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "Permissões alteradas para 0666\n", FILE_APPEND);
        }
    }
    
    // Carregar configurações
    $conf = new Config($settings_file);
    file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "Configuração carregada com sucesso\n", FILE_APPEND);
    
    // Processar cada campo do formulário
    foreach($_POST as $key => $value) {
        // Verificar se o nome do campo contém pontos
        if (strpos($key, '.') !== false) {
            // Se já tem ponto, usa a chave como está
            $confkey = $key;
        } else {
            // Se não tem ponto, faz a conversão de underscore para ponto
            $confkey = str_replace('_', '.', $key);
        }
        
        // Log detalhado
        file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "Processando campo: $key => $confkey com valor: " . (is_array($value) ? json_encode($value) : $value) . "\n", FILE_APPEND);
        
        // Casos especiais para campos com problemas
        if ($key === "black_landing_folder_redirect_url") {
            $confkey = "black.landing.folder.redirect_url";
            file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "Caso especial: Corrigindo chave para $confkey\n", FILE_APPEND);
        }
        
        // Tratamento especial para campos específicos
        if ($confkey === 'black.landing.folder.customthankyoupage.use') {
            // Garantir que este valor seja boolean
            if ($value === '1' || $value === 1 || $value === 'true' || $value === true) {
                $value = true;
            } else {
                $value = false;
            }
            file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "Campo crítico $confkey definido como: " . ($value ? 'true' : 'false') . "\n", FILE_APPEND);
            $conf[$confkey] = $value;
            continue;
        }
        
        // Para campos de tipo "array" que vêm como string
        if (is_string($value) && is_array($conf[$confkey])) {
            if ($value === '') {
                $value = [];
            } else {
                $value = explode(',', $value);
                // Remover espaços em branco
                $value = array_map('trim', $value);
            }
            $conf[$confkey] = $value;
        }
        // Para valores booleanos
        else if ($value === 'false' || $value === 'true') {
            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            $conf[$confkey] = $value;
        }
        // Para outros valores
        else {
            $conf[$confkey] = $value;
        }
    }
    
    // Verificar valores críticos antes de salvar
    $black_redirect_url = $conf['black.landing.folder.redirect_url'] ?? null;
    file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "Valor de black.landing.folder.redirect_url: " . ($black_redirect_url ?? 'não definido') . "\n", FILE_APPEND);
    
    // Salvar configurações
    file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "Tentando salvar configurações...\n", FILE_APPEND);
    
    // Fazer um backup do arquivo original por segurança
    $backup_file = $settings_file . '.bak';
    if (!copy($settings_file, $backup_file)) {
        file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "AVISO: Não foi possível criar backup!\n", FILE_APPEND);
    } else {
        file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "Backup criado em $backup_file\n", FILE_APPEND);
    }
    
    // Salvar configuração usando o método da biblioteca
    $conf->toFile($settings_file, new Json());
    file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "Configuração salva com sucesso usando toFile()\n", FILE_APPEND);
    
    // Verificar se o arquivo foi realmente salvo
    $new_conf = new Config($settings_file);
    $new_redirect_url = $new_conf['black.landing.folder.redirect_url'] ?? null;
    file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "Verificação pós-salvamento: black.landing.folder.redirect_url = " . ($new_redirect_url ?? 'não definido') . "\n", FILE_APPEND);
    
    // Também salvar usando file_put_contents para garantir
    $json_content = json_encode($conf->all(), JSON_PRETTY_PRINT);
    if (file_put_contents($settings_file, $json_content) === false) {
        file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "ERRO: Falha ao salvar usando file_put_contents()\n", FILE_APPEND);
    } else {
        file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "Configuração também salva usando file_put_contents()\n", FILE_APPEND);
    }
    
    file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "--- FIM DO PROCESSAMENTO COM SUCESSO ---\n\n", FILE_APPEND);
    
    // Redirecionar de volta para a página de configurações
    redirect('editsettings.php?password=' . $log_password . '&saved=true', 302, false);
} catch (Exception $e) {
    // Registrar o erro no log
    file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "ERRO: " . $e->getMessage() . "\n", FILE_APPEND);
    file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "Trace: " . $e->getTraceAsString() . "\n", FILE_APPEND);
    file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "--- FIM DO PROCESSAMENTO COM ERRO ---\n\n", FILE_APPEND);
    
    // Redirecionar com mensagem de erro
    redirect('editsettings.php?password=' . $log_password . '&error=' . urlencode($e->getMessage()), 302, false);
}
?>