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

// Log para debug
error_log("POST recebido: " . json_encode($_POST));

$conf = new Config('../settings.json');
foreach($_POST as $key=>$value){
    // Verificar se o nome do campo contém pontos
    if (strpos($key, '.') !== false) {
        // Se já tem ponto, usa a chave como está
        $confkey = $key;
    } else {
        // Se não tem ponto, faz a conversão como antes
        $confkey = str_replace('_', '.', $key);
    }
    
    error_log("Processando campo: $key => $confkey com valor: $value");
    
    if (is_string($value) && is_array($conf[$confkey])){
        if ($value === ''){
            $value = [];
        }
        else{
            $value = explode(',', $value);
        }
        $conf[$confkey] = $value;
    }
    else if ($value === 'false' || $value === 'true'){
        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        $conf[$confkey] = $value;
    }
    else{
        $conf[$confkey] = $value;
    }
}

// Log do objeto de configuração antes de salvar
error_log("Configuração a ser salva: " . json_encode($conf->all()));

$conf->toFile('../settings.json', new Json());
redirect('editsettings.php?password=' . $log_password, 302, false);
?>