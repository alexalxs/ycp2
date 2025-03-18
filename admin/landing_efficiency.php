<?php
// Habilitando informações de debug
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', 1);

require_once '../settings.php';
require_once 'password.php';
check_password();

require_once 'db.php';
require_once '../abtests/Calculator/SplitTestAnalyzer.php';
require_once '../abtests/Calculator/Variation.php';
use BenTools\SplitTestAnalyzer\SplitTestAnalyzer;
use BenTools\SplitTestAnalyzer\Variation;

date_default_timezone_set($stats_timezone);
$startdate = isset($_GET['startdate']) ?
    DateTime::createFromFormat('d.m.y', $_GET['startdate'], new DateTimeZone($stats_timezone)) :
    new DateTime("now - 30 days", new DateTimeZone($stats_timezone)); // Por padrão, mostra os últimos 30 dias
$enddate = isset($_GET['enddate']) ?
    DateTime::createFromFormat('d.m.y', $_GET['enddate'], new DateTimeZone($stats_timezone)) :
    new DateTime("now", new DateTimeZone($stats_timezone));

$date_str = '';
if (isset($_GET['startdate']) && isset($_GET['enddate'])) {
    $startstr = $_GET['startdate'];
    $endstr = $_GET['enddate'];
    $date_str = "&startdate={$startstr}&enddate={$endstr}";
}

// Obter período selecionado
$period = isset($_GET['period']) ? $_GET['period'] : 'custom';
switch ($period) {
    case 'today':
        $startdate = new DateTime("today", new DateTimeZone($stats_timezone));
        $enddate = new DateTime("now", new DateTimeZone($stats_timezone));
        break;
    case 'yesterday':
        $startdate = new DateTime("yesterday", new DateTimeZone($stats_timezone));
        $enddate = new DateTime("yesterday 23:59:59", new DateTimeZone($stats_timezone));
        break;
    case 'week':
        $startdate = new DateTime("this week monday", new DateTimeZone($stats_timezone));
        $enddate = new DateTime("now", new DateTimeZone($stats_timezone));
        break;
    case 'month':
        $startdate = new DateTime("first day of this month", new DateTimeZone($stats_timezone));
        $enddate = new DateTime("now", new DateTimeZone($stats_timezone));
        break;
    case 'last30':
        $startdate = new DateTime("now - 30 days", new DateTimeZone($stats_timezone));
        $enddate = new DateTime("now", new DateTimeZone($stats_timezone));
        break;
}

// Buscar dados do período selecionado
$dataDir = __DIR__ . "/../logs";
$lpctrStore = new \SleekDB\Store("lpctr", $dataDir);
$all_lpctr = $lpctrStore->findAll(["time" => "desc"]);

// Filtrar por intervalo de datas
$filtered_lpctr = [];
foreach ($all_lpctr as $ctr_record) {
    $record_time = $ctr_record['time'];
    if ($record_time >= $startdate->getTimestamp() && $record_time <= $enddate->getTimestamp()) {
        $filtered_lpctr[] = $ctr_record;
    }
}

// Processar dados para análise de landing pages
$landing_clicks = [];
$landing_leads = [];
$landing_data = [];

// 1. Contar cliques por landing
foreach ($filtered_lpctr as $ctr_record) {
    if (!empty($ctr_record['land'])) {
        $land_name = $ctr_record['land'];
        if (!isset($landing_clicks[$land_name])) {
            $landing_clicks[$land_name] = 0;
            $landing_leads[$land_name] = [
                'total' => 0,
                'Lead' => 0,
                'Purchase' => 0,
                'Reject' => 0,
                'Trash' => 0
            ];
        }
        $landing_clicks[$land_name]++;
    }
}

// 2. Obter leads associados a cada landing
$leads = get_leads($startdate->getTimestamp(), $enddate->getTimestamp());
foreach ($leads as $lead) {
    $land_name = $lead['land'];
    if (!empty($land_name) && isset($landing_clicks[$land_name])) {
        $status = $lead['status'];
        $landing_leads[$land_name]['total']++;
        
        if (isset($landing_leads[$land_name][$status])) {
            $landing_leads[$land_name][$status]++;
        }
    }
}

// 3. Calcular métricas para cada landing
foreach ($landing_clicks as $land_name => $clicks) {
    $total_leads = $landing_leads[$land_name]['total'];
    $conversion_rate = $clicks > 0 ? ($total_leads / $clicks) * 100 : 0;
    
    $leads_without_trash = $total_leads - $landing_leads[$land_name]['Trash'];
    $purchases = $landing_leads[$land_name]['Purchase'];
    $approval_rate = $leads_without_trash > 0 ? ($purchases / $leads_without_trash) * 100 : 0;
    
    $landing_data[$land_name] = [
        'clicks' => $clicks,
        'leads' => $total_leads,
        'purchases' => $purchases,
        'conversion_rate' => $conversion_rate,
        'approval_rate' => $approval_rate,
        'holds' => $landing_leads[$land_name]['Lead'],
        'rejects' => $landing_leads[$land_name]['Reject'],
        'trash' => $landing_leads[$land_name]['Trash']
    ];
}

// 4. Classificar landing pages por taxa de conversão
if (isset($_GET['sort'])) {
    $sort_field = $_GET['sort'];
    $sort_dir = isset($_GET['dir']) && $_GET['dir'] == 'asc' ? 'asc' : 'desc';
    
    uasort($landing_data, function($a, $b) use ($sort_field, $sort_dir) {
        if ($sort_dir == 'asc') {
            return $a[$sort_field] <=> $b[$sort_field];
        } else {
            return $b[$sort_field] <=> $a[$sort_field];
        }
    });
} else {
    // Por padrão, ordenar por taxa de conversão decrescente
    uasort($landing_data, function($a, $b) {
        return $b['conversion_rate'] <=> $a['conversion_rate'];
    });
}

// 5. Verificar se tem pelo menos duas landing pages para comparação
$has_multiple_landings = count($landing_data) > 1;

// 6. Calcular probabilidade de melhor landing (teste A/B)
$landing_probability = [];
if ($has_multiple_landings) {
    $variations = [];
    foreach ($landing_data as $land_name => $metrics) {
        // Certifique-se de que o número de conversões nunca exceda o número total de cliques
        $clicks = max(1, $metrics['clicks']); // evitar divisão por zero
        $conversions = min($metrics['leads'], $clicks); // garantir que conversões <= clicks
        
        $variations[] = new Variation(
            $land_name, 
            $clicks, 
            $conversions
        );
    }
    
    if (count($variations) > 0) {
        $predictor = SplitTestAnalyzer::create()->withVariations(...$variations);
        $landing_probability = $predictor->getResult();
    }
}

// Calcular métricas gerais
$total_clicks = array_sum(array_column($landing_data, 'clicks'));
$total_leads = array_sum(array_column($landing_data, 'leads'));
$total_purchases = array_sum(array_column($landing_data, 'purchases'));
$overall_conversion_rate = $total_clicks > 0 ? ($total_leads / $total_clicks) * 100 : 0;

// Gerar o gráfico de dados em JSON para uso com Chart.js
$chart_labels = array_keys($landing_data);
$chart_conversion_rates = array_column($landing_data, 'conversion_rate');
$chart_leads = array_column($landing_data, 'leads');
$chart_clicks = array_column($landing_data, 'clicks');
$chart_data = [
    'labels' => $chart_labels,
    'conversion_rates' => $chart_conversion_rates,
    'leads' => $chart_leads,
    'clicks' => $chart_clicks
];
$chart_json = json_encode($chart_data);
?>

<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <title>Análise de Eficiência de Landing Pages | YCP2</title>
    <script src="https://cdn.jsdelivr.net/npm/litepicker/dist/litepicker.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="img/favicon.png" />
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,700,900" rel="stylesheet" />
    <!-- CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="stylesheet" href="css/nalika-icon.css" />
    <link rel="stylesheet" href="css/main.css" />
    <link rel="stylesheet" href="css/metisMenu/metisMenu.min.css" />
    <link rel="stylesheet" href="css/metisMenu/metisMenu-vertical.css" />
    <link rel="stylesheet" href="css/style.css" />

    <style>
        .metrics-card {
            background-color: #2c2c2c;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
            color: #fff;
        }
        .metrics-value {
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
        }
        .metrics-label {
            color: #999;
            font-size: 14px;
        }
        .chart-container {
            background-color: #2c2c2c;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .top-performer {
            border-left: 4px solid #28a745;
        }
        .period-filter {
            display: inline-block;
            margin: 0 5px;
            padding: 5px 15px;
            border-radius: 15px;
            background-color: #333;
            color: #fff;
            text-decoration: none;
        }
        .period-filter.active {
            background-color: #007bff;
        }
        .table-responsive {
            background-color: #2c2c2c;
            border-radius: 5px;
            padding: 10px;
        }
    </style>
</head>

<body>
    <!-- Menu lateral (similar à página statistics.php) -->
    <div class="left-sidebar-pro">
        <nav id="sidebar">
            <div class="sidebar-header">
                <a href="/"><img src="img/logo/logo.png" alt="Logo" /></a>
            </div>
            <div class="left-custom-menu-adp-wrap">
                <nav class="sidebar-nav left-sidebar-menu-pro">
                    <ul class="metismenu" id="menu1">
                        <li>
                            <a class="has-arrow" href="index.php?password=<?=$_GET['password']?><?=$date_str!==''?$date_str:''?>" aria-expanded="false">
                                <i class="icon nalika-bar-chart icon-wrap"></i>
                                <span class="mini-click-non">Tráfego</span>
                            </a>
                            <ul class="submenu-angle" aria-expanded="false">
                                <li>
                                    <a title="Estatísticas" href="statistics.php?password=<?=$_GET['password']?><?=$date_str!==''?$date_str:''?>">
                                        <span class="mini-sub-pro">Estatísticas</span>
                                    </a>
                                </li>
                                <li>
                                    <a title="Permitidos" href="index.php?password=<?=$_GET['password']?><?=$date_str!==''?$date_str:''?>">
                                        <span class="mini-sub-pro">Permitidos</span>
                                    </a>
                                </li>
                                <li>
                                    <a title="Leads" href="index.php?filter=leads&password=<?=$_GET['password']?><?=$date_str!==''?$date_str:''?>">
                                        <span class="mini-sub-pro">Leads</span>
                                    </a>
                                </li>
                                <li>
                                    <a title="Bloqueados" href="index.php?filter=blocked&password=<?=$_GET['password']?><?=$date_str!==''?$date_str:''?>">
                                        <span class="mini-sub-pro">Bloqueados</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="active">
                            <a class="has-arrow" href="landing_efficiency.php?password=<?=$_GET['password']?><?=$date_str!==''?$date_str:''?>" aria-expanded="false">
                                <i class="icon nalika-pie-chart icon-wrap"></i>
                                <span class="mini-click-non">Análise</span>
                            </a>
                            <ul class="submenu-angle" aria-expanded="false">
                                <li class="active">
                                    <a title="Eficiência de Landing" href="landing_efficiency.php?password=<?=$_GET['password']?><?=$date_str!==''?$date_str:''?>">
                                        <span class="mini-sub-pro">Eficiência de Landing</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="editsettings.php?password=<?=$_GET['password']?><?=$date_str!==''?$date_str:''?>" aria-expanded="false">
                                <i class="icon nalika-settings icon-wrap"></i>
                                <span class="mini-click-non">Configurações</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </nav>
    </div>

    <!-- Conteúdo principal -->
    <div class="all-content-wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="logo-pro">
                        <a href="index.html"><img class="main-logo" src="img/logo/logo.png" alt="" /></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cabeçalho da página -->
        <div class="header-advance-area">
            <div class="header-top-area">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <div class="header-top-wraper">
                                <div class="row">
                                    <div class="col-lg-1 col-md-0 col-sm-1 col-xs-12">
                                        <div class="menu-switcher-pro">
                                            <button type="button" id="sidebarcollapse" class="btn bar-button-pro header-drl-controller-btn btn-info navbar-btn">
                                                <i class="icon nalika-menu-task"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-lg-11 col-md-1 col-sm-12 col-xs-12">
                                        <div class="header-right-info">
                                            <ul class="nav navbar-nav mai-top-nav header-right-menu">
                                                <li class="nav-item">
                                                    <a href="#" id="litepicker">Período: 
                                                        <?php
                                                        $startFormatted = $startdate->format('d.m.y');
                                                        $endFormatted = $enddate->format('d.m.y');
                                                        
                                                        if ($startFormatted === $endFormatted) {
                                                            echo $startFormatted;
                                                        } else {
                                                            echo "{$startFormatted} - {$endFormatted}";
                                                        }
                                                        ?>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Conteúdo do painel -->
        <div class="analytics-sparkle-area">
            <div class="container-fluid">
                <!-- Filtros de período -->
                <div class="row" style="margin-bottom: 20px;">
                    <div class="col-lg-12">
                        <div class="metrics-card">
                            <h4>Filtrar por período:</h4>
                            <div style="margin-top: 10px;">
                                <a href="?password=<?=$_GET['password']?>&period=today" class="period-filter <?= $period == 'today' ? 'active' : '' ?>">Hoje</a>
                                <a href="?password=<?=$_GET['password']?>&period=yesterday" class="period-filter <?= $period == 'yesterday' ? 'active' : '' ?>">Ontem</a>
                                <a href="?password=<?=$_GET['password']?>&period=week" class="period-filter <?= $period == 'week' ? 'active' : '' ?>">Esta Semana</a>
                                <a href="?password=<?=$_GET['password']?>&period=month" class="period-filter <?= $period == 'month' ? 'active' : '' ?>">Este Mês</a>
                                <a href="?password=<?=$_GET['password']?>&period=last30" class="period-filter <?= $period == 'last30' ? 'active' : '' ?>">Últimos 30 dias</a>
                                <a href="#" class="period-filter" id="customPeriod">Personalizado</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resumo geral -->
                <div class="row">
                    <div class="col-lg-4">
                        <div class="metrics-card">
                            <div class="metrics-label">Total de Cliques</div>
                            <div class="metrics-value"><?= $total_clicks ?></div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="metrics-card">
                            <div class="metrics-label">Total de Leads</div>
                            <div class="metrics-value"><?= $total_leads ?></div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="metrics-card">
                            <div class="metrics-label">Taxa de Conversão Geral</div>
                            <div class="metrics-value"><?= number_format($overall_conversion_rate, 2) ?>%</div>
                        </div>
                    </div>
                </div>

                <!-- Gráficos -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="chart-container">
                            <h4>Comparação de Taxa de Conversão entre Landing Pages</h4>
                            <canvas id="conversionChart" height="100"></canvas>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="chart-container">
                            <h4>Total de Leads por Landing Page</h4>
                            <canvas id="leadsChart" height="150"></canvas>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="chart-container">
                            <h4>Total de Cliques por Landing Page</h4>
                            <canvas id="clicksChart" height="150"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Tabela de landing pages -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="table-responsive">
                            <h4>Desempenho Detalhado por Landing Page</h4>
                            <table class="table table-striped table-dark">
                                <thead>
                                    <tr>
                                        <th>Landing Page</th>
                                        <th>
                                            <a href="?password=<?=$_GET['password']?>&period=<?=$period?>&sort=clicks&dir=<?= isset($_GET['sort']) && $_GET['sort'] == 'clicks' && $_GET['dir'] == 'desc' ? 'asc' : 'desc' ?>">
                                                Cliques
                                            </a>
                                        </th>
                                        <th>
                                            <a href="?password=<?=$_GET['password']?>&period=<?=$period?>&sort=leads&dir=<?= isset($_GET['sort']) && $_GET['sort'] == 'leads' && $_GET['dir'] == 'desc' ? 'asc' : 'desc' ?>">
                                                Leads
                                            </a>
                                        </th>
                                        <th>
                                            <a href="?password=<?=$_GET['password']?>&period=<?=$period?>&sort=conversion_rate&dir=<?= isset($_GET['sort']) && $_GET['sort'] == 'conversion_rate' && $_GET['dir'] == 'desc' ? 'asc' : 'desc' ?>">
                                                Taxa de Conversão
                                            </a>
                                        </th>
                                        <th>Leads em Hold</th>
                                        <th>Compras</th>
                                        <th>Rejeições</th>
                                        <th>Lixeira</th>
                                        <th>Taxa de Aprovação</th>
                                        <?php if ($has_multiple_landings): ?>
                                        <th>Probabilidade de Ser Melhor</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($landing_data as $land_name => $metrics): ?>
                                    <tr <?= array_key_first($landing_data) === $land_name ? 'class="top-performer"' : '' ?>>
                                        <td><?= $land_name ?></td>
                                        <td><?= $metrics['clicks'] ?></td>
                                        <td><?= $metrics['leads'] ?></td>
                                        <td><?= number_format($metrics['conversion_rate'], 2) ?>%</td>
                                        <td><?= $metrics['holds'] ?></td>
                                        <td><?= $metrics['purchases'] ?></td>
                                        <td><?= $metrics['rejects'] ?></td>
                                        <td><?= $metrics['trash'] ?></td>
                                        <td><?= number_format($metrics['approval_rate'], 2) ?>%</td>
                                        <?php if ($has_multiple_landings): ?>
                                        <td><?= isset($landing_probability[$land_name]) ? $landing_probability[$land_name] : '0' ?></td>
                                        <?php endif; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Botão para exportar -->
                <div class="row" style="margin-top: 20px; margin-bottom: 40px;">
                    <div class="col-lg-12 text-center">
                        <button class="btn btn-primary" id="exportBtn">Exportar Dados (CSV)</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/plugins.js"></script>
    <script src="js/main.js"></script>
    <script src="js/metisMenu/metisMenu.min.js"></script>
    <script src="js/metisMenu/metisMenu-active.js"></script>

    <script>
        // Inicializar o date picker
        const picker = new Litepicker({
            element: document.getElementById('litepicker'),
            format: 'DD.MM.YY',
            singleMode: false,
            numberOfMonths: 2,
            numberOfColumns: 2,
            startDate: '<?= $startdate->format('Y-m-d') ?>',
            endDate: '<?= $enddate->format('Y-m-d') ?>',
            onSelect: function(date1, date2) {
                const startDate = date1.format('DD.MM.YY');
                const endDate = date2.format('DD.MM.YY');
                window.location.href = `?password=<?=$_GET['password']?>&startdate=${startDate}&enddate=${endDate}`;
            }
        });

        // Evento para o link de período personalizado
        document.getElementById('customPeriod').addEventListener('click', function(e) {
            e.preventDefault();
            picker.show();
        });

        // Inicializar gráficos
        document.addEventListener('DOMContentLoaded', function() {
            const chartData = <?= $chart_json ?>;
            
            // Gráfico de Taxa de Conversão
            const conversionCtx = document.getElementById('conversionChart').getContext('2d');
            new Chart(conversionCtx, {
                type: 'bar',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Taxa de Conversão (%)',
                        data: chartData.conversion_rates,
                        backgroundColor: 'rgba(75, 192, 192, 0.6)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Taxa de Conversão (%)'
                            }
                        }
                    }
                }
            });
            
            // Gráfico de Leads
            const leadsCtx = document.getElementById('leadsChart').getContext('2d');
            new Chart(leadsCtx, {
                type: 'pie',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        data: chartData.leads,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.6)',
                            'rgba(54, 162, 235, 0.6)',
                            'rgba(255, 206, 86, 0.6)',
                            'rgba(75, 192, 192, 0.6)',
                            'rgba(153, 102, 255, 0.6)',
                            'rgba(255, 159, 64, 0.6)'
                        ],
                        borderWidth: 1
                    }]
                }
            });
            
            // Gráfico de Cliques
            const clicksCtx = document.getElementById('clicksChart').getContext('2d');
            new Chart(clicksCtx, {
                type: 'doughnut',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        data: chartData.clicks,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.6)',
                            'rgba(54, 162, 235, 0.6)',
                            'rgba(255, 206, 86, 0.6)',
                            'rgba(75, 192, 192, 0.6)',
                            'rgba(153, 102, 255, 0.6)',
                            'rgba(255, 159, 64, 0.6)'
                        ],
                        borderWidth: 1
                    }]
                }
            });
            
            // Função para exportar dados para CSV
            document.getElementById('exportBtn').addEventListener('click', function() {
                let csvContent = "data:text/csv;charset=utf-8,";
                csvContent += "Landing Page,Cliques,Leads,Taxa de Conversão,Leads em Hold,Compras,Rejeições,Lixeira,Taxa de Aprovação\n";
                
                <?php foreach ($landing_data as $land_name => $metrics): ?>
                csvContent += "<?= $land_name ?>,<?= $metrics['clicks'] ?>,<?= $metrics['leads'] ?>,<?= number_format($metrics['conversion_rate'], 2) ?>,<?= $metrics['holds'] ?>,<?= $metrics['purchases'] ?>,<?= $metrics['rejects'] ?>,<?= $metrics['trash'] ?>,<?= number_format($metrics['approval_rate'], 2) ?>\n";
                <?php endforeach; ?>
                
                const encodedUri = encodeURI(csvContent);
                const link = document.createElement("a");
                link.setAttribute("href", encodedUri);
                link.setAttribute("download", "landing_efficiency_data_<?= date('Y-m-d') ?>.csv");
                document.body.appendChild(link);
                link.click();
            });
        });
    </script>
</body>
</html> 