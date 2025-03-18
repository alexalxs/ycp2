<?php
require_once __DIR__ . "/db.php";

// Obter o subid do lead mais recente
$dataDir = __DIR__ . "/logs";
$leadsStore = new \SleekDB\Store("leads", $dataDir);
$leads = $leadsStore->findAll(["time" => "desc"], 1);

if (!empty($leads)) {
    $lead = $leads[0];
    $subid = $lead['subid'];
    $old_email = isset($lead['email']) ? $lead['email'] : 'não definido';
    $new_email = 'novo.email.teste@exemplo.com.br';
    
    echo "Adicionando/atualizando email do lead:<br>";
    echo "SubID: " . $subid . "<br>";
    echo "Email Antigo: " . $old_email . "<br>";
    echo "Novo Email: " . $new_email . "<br><br>";
    
    // Adicionar ou atualizar o email
    add_email($subid, $new_email);
    
    // Verificar se o email foi atualizado
    $lead_updated = $leadsStore->findOneBy([["subid", "=", $subid]]);
    
    if ($lead_updated && isset($lead_updated['email']) && $lead_updated['email'] === $new_email) {
        echo "Email atualizado com sucesso!<br>";
        echo "Um novo postback deve ter sido enviado. Verifique a pasta /pblogs/<br>";
    } else {
        echo "Falha ao atualizar o email do lead.<br>";
    }
} else {
    echo "Nenhum lead encontrado no banco de dados.";
} 