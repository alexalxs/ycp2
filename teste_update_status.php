<?php
require_once __DIR__ . "/db.php";

// Obter o subid do lead mais recente
$dataDir = __DIR__ . "/logs";
$leadsStore = new \SleekDB\Store("leads", $dataDir);
$leads = $leadsStore->findAll(["time" => "desc"], 1);

if (!empty($leads)) {
    $lead = $leads[0];
    $subid = $lead['subid'];
    $old_status = $lead['status'];
    $new_status = 'Purchase'; // Alterando para Purchase
    
    echo "Atualizando status do lead:<br>";
    echo "SubID: " . $subid . "<br>";
    echo "Status Antigo: " . $old_status . "<br>";
    echo "Novo Status: " . $new_status . "<br><br>";
    
    // Atualizar o status
    $result = update_lead_status($subid, $new_status);
    
    if ($result) {
        echo "Status atualizado com sucesso!<br>";
        echo "Um novo postback deve ter sido enviado. Verifique a pasta /pblogs/<br>";
    } else {
        echo "Falha ao atualizar o status do lead.<br>";
    }
} else {
    echo "Nenhum lead encontrado no banco de dados.";
} 