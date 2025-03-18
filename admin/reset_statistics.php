<?php
// Habilitando informações de debug
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', 1);

// Incluir arquivos necessários
require_once '../settings.php';
require_once 'password.php';
check_password();
require_once '../db/SleekDB.php';
require_once '../db/Store.php';
require_once '../db/QueryBuilder.php';
require_once '../db/Query.php';
require_once '../db/Cache.php';

// Verificar se o usuário confirmou a ação
$confirmed = isset($_GET['confirm']) && $_GET['confirm'] === 'yes';

// Função para limpar todos os dados de uma store SleekDB
function clearStore($storeName) {
    $dataDir = __DIR__ . "/../logs";
    
    try {
        // Verificar se o diretório existe
        if (!file_exists($dataDir . '/' . $storeName)) {
            return "O diretório $dataDir/$storeName não existe ou não pode ser acessado.";
        }
        
        // Criar a store
        $store = new \SleekDB\Store($storeName, $dataDir);
        
        // Obter todos os documentos
        $documents = $store->findAll();
        
        // Excluir cada documento
        foreach ($documents as $document) {
            if (isset($document['_id'])) {
                $store->deleteById($document['_id']);
            }
        }
        
        return "Todos os documentos da store '$storeName' foram removidos com sucesso.";
    } catch (Exception $e) {
        return "Erro ao limpar a store '$storeName': " . $e->getMessage();
    }
}

// Limpar o arquivo de logs de cliques em botões
function clearLogFile($logFile) {
    $filePath = __DIR__ . "/../logs/" . $logFile;
    
    if (file_exists($filePath)) {
        // Limpar o conteúdo do arquivo
        file_put_contents($filePath, "");
        return "Arquivo $logFile foi limpo com sucesso.";
    }
    
    return "Arquivo $logFile não encontrado.";
}

// Arrays para armazenar resultados
$resultMessages = [];

// Processar apenas se o usuário tiver confirmado
if ($confirmed) {
    // Limpar todas as stores
    $stores = ['blackclicks', 'whiteclicks', 'leads', 'lpctr'];
    
    foreach ($stores as $store) {
        $resultMessages[] = clearStore($store);
    }
    
    // Limpar arquivo de log de cliques em botões
    $resultMessages[] = clearLogFile('button_clicks.log');
}
?>

<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <title>Limpar Estatísticas - YCP2</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" type="image/x-icon" href="img/favicon.png" />
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,700,900" rel="stylesheet" />
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="stylesheet" href="css/nalika-icon.css" />
    <link rel="stylesheet" href="css/main.css" />
    <link rel="stylesheet" href="css/metisMenu/metisMenu.min.css" />
    <link rel="stylesheet" href="css/metisMenu/metisMenu-vertical.css" />
    <link rel="stylesheet" href="css/style.css" />
    <style>
        .confirmation-box {
            background-color: #343a40;
            color: #fff;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .result-box {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
        }
        .success-message {
            background-color: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 3px;
            margin-bottom: 5px;
            display: block;
        }
        .warning-message {
            background-color: #ffc107;
            color: black;
            padding: 5px 10px;
            border-radius: 3px;
            margin-bottom: 5px;
            display: block;
        }
        .error-message {
            background-color: #dc3545;
            color: white;
            padding: 5px 10px;
            border-radius: 3px;
            margin-bottom: 5px;
            display: block;
        }
    </style>
</head>

<body>
    <div class="left-sidebar-pro">
        <nav id="sidebar" class="">
            <div class="sidebar-header">
                <a href="/admin/index.php?password=<?=$_GET['password']?>">
                    <img class="main-logo" src="img/logo/logo.png" alt="" />
                </a>
                <strong>
                    <img src="img/favicon.png" alt="" style="width:50px" />
                </strong>
            </div>
            <div class="nalika-profile">
                <div class="profile-dtl">
                    <a href="https://t.me/yellow_web">
                        <img src="img/notification/4.jpg" alt="" />
                    </a>
                    <?php include "version.php" ?>
                </div>
            </div>
            <div class="left-custom-menu-adp-wrap comment-scrollbar">
                <nav class="sidebar-nav left-sidebar-menu-pro">
                    <ul class="metismenu" id="menu1">
                        <li class="active">
                            <a class="has-arrow" href="index.php?password=<?=$_GET['password']?>" aria-expanded="false">
                                <i class="icon nalika-bar-chart icon-wrap"></i>
                                <span class="mini-click-non">Traffic</span>
                            </a>
                            <ul class="submenu-angle" aria-expanded="false">
                                <li>
                                    <a title="Estatísticas" href="statistics.php?password=<?=$_GET['password']?>">
                                        <span class="mini-sub-pro">Estatísticas</span>
                                    </a>
                                </li>
                                <li>
                                    <a title="Análise de Landing" href="landing_efficiency.php?password=<?=$_GET['password']?>">
                                        <span class="mini-sub-pro">Eficiência de Landing</span>
                                    </a>
                                </li>
                                <li>
                                    <a title="Resetar Estatísticas" href="reset_statistics.php?password=<?=$_GET['password']?>">
                                        <span class="mini-sub-pro">Limpar Estatísticas</span>
                                    </a>
                                </li>
                                <li>
                                    <a title="Allowed" href="index.php?password=<?=$_GET['password']?>">
                                        <span class="mini-sub-pro">Allowed</span>
                                    </a>
                                </li>
                                <li>
                                    <a title="Leads" href="index.php?filter=leads&password=<?=$_GET['password']?>">
                                        <span class="mini-sub-pro">Leads</span>
                                    </a>
                                </li>
                                <li>
                                    <a title="Blocked" href="index.php?filter=blocked&password=<?=$_GET['password']?>">
                                        <span class="mini-sub-pro">Blocked</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="editsettings.php?password=<?=$_GET['password']?>" aria-expanded="false">
                                <i class="icon nalika-settings icon-wrap"></i>
                                <span class="mini-click-non">Configurações</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </nav>
    </div>
    
    <div class="all-content-wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="logo-pro">
                        <a href="index.php?password=<?=$_GET['password']?>">
                            <img class="main-logo" src="img/logo/logo.png" alt="" />
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="header-advance-area">
            <div class="header-top-area">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <div class="header-top-wraper">
                                <div class="row">
                                    <div class="col-lg-1 col-md-0 col-sm-1 col-xs-12">
                                        <div class="menu-switcher-pro">
                                            <button type="button" id="sidebarCollapse" class="btn bar-button-pro header-drl-controller-btn btn-info navbar-btn">
                                                <i class="icon nalika-menu-task"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="product-status mg-b-30 mg-t-15">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <div class="product-status-wrap">
                            <h4>Limpar Dados Estatísticos</h4>
                            
                            <?php if (!$confirmed): ?>
                            <div class="confirmation-box">
                                <div class="alert alert-warning">
                                    <h4><i class="icon nalika-warning"></i> Atenção!</h4>
                                    <p>Esta ação irá limpar <strong>TODOS</strong> os dados estatísticos do sistema, incluindo:</p>
                                    <ul>
                                        <li>Todos os cliques registrados</li>
                                        <li>Todos os leads cadastrados</li>
                                        <li>Todas as estatísticas de conversão</li>
                                        <li>Todos os registros de tráfego bloqueado</li>
                                        <li>Todas as métricas de desempenho de landing pages</li>
                                    </ul>
                                    <p><strong>Esta ação não pode ser desfeita!</strong> Todos os dados serão permanentemente excluídos.</p>
                                </div>
                                
                                <div class="text-center">
                                    <a href="reset_statistics.php?password=<?=$_GET['password']?>&confirm=yes" class="btn btn-danger btn-lg">
                                        Sim, quero limpar todos os dados estatísticos
                                    </a>
                                    <a href="statistics.php?password=<?=$_GET['password']?>" class="btn btn-default btn-lg">
                                        Cancelar e voltar
                                    </a>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="result-box">
                                <h4>Resultado da operação:</h4>
                                
                                <?php foreach ($resultMessages as $message): ?>
                                    <span class="success-message"><?= $message ?></span>
                                <?php endforeach; ?>
                                
                                <div class="text-center" style="margin-top: 20px;">
                                    <a href="statistics.php?password=<?=$_GET['password']?>" class="btn btn-primary btn-lg">
                                        Voltar para Estatísticas
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="js/metisMenu/metisMenu.min.js"></script>
    <script src="js/metisMenu/metisMenu-active.js"></script>
    <script src="js/plugins.js"></script>
    <script src="js/main.js"></script>
</body>
</html> 