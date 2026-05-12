<?php
require_once __DIR__ . '/../config.php';
include BASE_PATH . 'db.php';

// Períodos disponíveis
$periods = [
    'today' => 'Hoje',
    '7days' => 'Últimos 7 dias',
    '30days' => 'Últimos 30 dias',
    '90days' => 'Últimos 90 dias',
    'custom' => 'Personalizado'
];

// Obter filtros
$period = $_GET['period'] ?? '7days';
$sensor_filter = isset($_GET['sensor']) && $_GET['sensor'] !== '' ? (int)$_GET['sensor'] : null;
$date_start = $_GET['date_start'] ?? null;
$date_end = $_GET['date_end'] ?? null;

// Calcular intervalo de datas baseado no período
$now = new DateTime();
switch ($period) {
    case 'today':
        $start_date = $now->format('Y-m-d');
        $end_date = $now->format('Y-m-d');
        break;
    case '7days':
        $start_date = (clone $now)->modify('-6 days')->format('Y-m-d');
        $end_date = $now->format('Y-m-d');
        break;
    case '30days':
        $start_date = (clone $now)->modify('-29 days')->format('Y-m-d');
        $end_date = $now->format('Y-m-d');
        break;
    case '90days':
        $start_date = (clone $now)->modify('-89 days')->format('Y-m-d');
        $end_date = $now->format('Y-m-d');
        break;
    case 'custom':
        $start_date = $date_start ?: (clone $now)->modify('-6 days')->format('Y-m-d');
        $end_date = $date_end ?: $now->format('Y-m-d');
        break;
}

// Buscar sensores para o filtro
$sensors = [];
$sensor_result = $conn->query("SELECT cod_sensor, nome, tipo FROM sensores ORDER BY tipo, nome");
while ($row = $sensor_result->fetch_assoc()) {
    $sensors[] = $row;
}

// Se um sensor foi selecionado, obter o seu tipo
$filter_tipo = null;
if ($sensor_filter) {
    $stmt = $conn->prepare("SELECT tipo FROM sensores WHERE cod_sensor = ?");
    $stmt->bind_param('i', $sensor_filter);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $filter_tipo = $row['tipo'] ?? null;
    $stmt->close();
}

// Buscar tipos de sensores
$tipos = [];
$tipos_result = $conn->query("SELECT DISTINCT tipo FROM sensores ORDER BY tipo");
while ($row = $tipos_result->fetch_assoc()) {
    $tipos[] = $row['tipo'];
}

// Inicializar arrays
$chart_data = [];
$stats = [];

// Buscar dados - se filtro de sensor, só buscar para esse tipo
$tipos_to_query = $filter_tipo ? [$filter_tipo] : $tipos;

foreach ($tipos_to_query as $tipo) {
    // Query para gráficos
    $sql = "SELECT l.valor, l.data_hora, l.unidade, s.nome
            FROM leituras l
            INNER JOIN sensores s ON l.cod_sensor = s.cod_sensor
            WHERE s.tipo = ?
            AND DATE(l.data_hora) BETWEEN ? AND ?";

    if ($sensor_filter) {
        $sql .= " AND l.cod_sensor = ?";
    }

    $sql .= " ORDER BY l.data_hora ASC";

    $params = [$tipo, $start_date, $end_date];
    $types = 'sss';

    if ($sensor_filter) {
        $params[] = $sensor_filter;
        $types .= 'i';
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'x' => $row['data_hora'],
            'y' => (float)$row['valor'],
            'sensor' => $row['nome'],
            'unidade' => $row['unidade']
        ];
    }
    $stmt->close();

    if (!empty($data)) {
        $chart_data[$tipo] = [
            'data' => $data,
            'unidade' => $data[0]['unidade'],
            'count' => count($data)
        ];
    } else {
        // Criar entrada vazia para manter a estrutura
        $chart_data[$tipo] = [
            'data' => [],
            'unidade' => '',
            'count' => 0
        ];
    }

    // Query para estatísticas
    $sql = "SELECT
                AVG(l.valor) as media,
                MIN(l.valor) as min_val,
                MAX(l.valor) as max_val,
                COUNT(*) as total
            FROM leituras l
            INNER JOIN sensores s ON l.cod_sensor = s.cod_sensor
            WHERE s.tipo = ?
            AND DATE(l.data_hora) BETWEEN ? AND ?";

    if ($sensor_filter) {
        $sql .= " AND l.cod_sensor = ?";
    }

    $params2 = [$tipo, $start_date, $end_date];
    $types2 = 'sss';

    if ($sensor_filter) {
        $params2[] = $sensor_filter;
        $types2 .= 'i';
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types2, ...$params2);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $stats[$tipo] = [
        'media' => round($row['media'] ?? 0, 2),
        'min' => round($row['min_val'] ?? 0, 2),
        'max' => round($row['max_val'] ?? 0, 2),
        'total' => $row['total'] ?? 0
    ];
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gráficos - Sistema de Monitorização</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3.0.0/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
</head>
<body>
    <header>
        <?php include '../struct/header.php'; ?>
    </header>

    <main class="charts-container">
        <h1 class="page-title">
            <i class="fas fa-chart-line" style="margin-right: 0.5rem;"></i>
            Gráficos de Monitorização
        </h1>

        <!-- Filtros -->
        <div class="filters-card">
            <form method="GET" action="">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label><i class="fas fa-microchip"></i> Sensor</label>
                        <select name="sensor" id="sensorSelect">
                            <option value="">Todos os sensores</option>
                            <?php foreach ($sensors as $sensor): ?>
                                <option value="<?= $sensor['cod_sensor'] ?>" <?= ($sensor_filter == $sensor['cod_sensor']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($sensor['tipo']) ?> - <?= htmlspecialchars($sensor['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label><i class="fas fa-clock"></i> Período</label>
                        <select name="period" id="periodSelect">
                            <?php foreach ($periods as $key => $label): ?>
                                <option value="<?= $key ?>" <?= ($period === $key) ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group date-field" id="customDates" style="<?= $period !== 'custom' ? 'display: none;' : '' ?>">
                        <label><i class="fas fa-calendar-alt"></i> Data início</label>
                        <input type="date" name="date_start" value="<?= htmlspecialchars($start_date) ?>">
                    </div>

                    <div class="filter-group date-field" id="customDateEnd" style="<?= $period !== 'custom' ? 'display: none;' : '' ?>">
                        <label><i class="fas fa-calendar-alt"></i> Data fim</label>
                        <input type="date" name="date_end" value="<?= htmlspecialchars($end_date) ?>">
                    </div>

                    <div class="filter-actions">
                        <button type="submit" class="btn-filter">
                            <i class="fas fa-search"></i> Atualizar
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Resumo do período -->
        <div class="period-summary">
            <i class="fas fa-calendar"></i>
            Período: <?= date('d/m/Y', strtotime($start_date)) ?> a <?= date('d/m/Y', strtotime($end_date)) ?>
            | <?php
                        $withData = count(array_filter($chart_data, function($d) {
                            return !empty($d['data']);
                        }));
                        echo $withData;
                    ?> gráfico(s) com dados
            <?php if ($sensor_filter): ?>
                | Filtrado por: <?= htmlspecialchars($sensors[array_search($sensor_filter, array_column($sensors, 'cod_sensor'))]['nome'] ?? 'Sensor') ?>
            <?php endif; ?>
        </div>

        <!-- Cartões de estatísticas -->
        <div class="stats-summary">
            <?php foreach ($stats as $tipo => $data): ?>
            <?php
                $iconClass = 'fa-chart-line';
                if ($tipo === 'Temperatura') $iconClass = 'fa-thermometer-half';
                elseif ($tipo === 'Humidade') $iconClass = 'fa-tint';
                elseif ($tipo === 'Luminosidade') $iconClass = 'fa-lightbulb';
                elseif ($tipo === 'Qualidade do Ar') $iconClass = 'fa-wind';
                elseif ($tipo === 'Nível da Água') $iconClass = 'fa-water';
            ?>
            <div class="stat-card-mini">
                <div class="stat-card-mini-header">
                    <i class="fas <?= $iconClass ?>"></i>
                    <span><?= htmlspecialchars($tipo) ?></span>
                </div>
                <div class="stat-card-mini-values">
                    <div><strong>Média:</strong> <?= $data['media'] ?> <?= htmlspecialchars($chart_data[$tipo]['unidade'] ?? '') ?></div>
                    <div><strong>Mín:</strong> <?= $data['min'] ?> | <strong>Máx:</strong> <?= $data['max'] ?></div>
                    <div class="stat-card-mini-count"><?= $data['total'] ?> leituras</div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Gráficos -->
        <div class="charts-grid">
            <?php foreach ($chart_data as $tipo => $data): ?>
            <?php
                $chartIcon = 'fa-chart-line';
                if ($tipo === 'Temperatura') $chartIcon = 'fa-thermometer-half';
                elseif ($tipo === 'Humidade') $chartIcon = 'fa-tint';
                elseif ($tipo === 'Luminosidade') $chartIcon = 'fa-lightbulb';
                elseif ($tipo === 'Qualidade do Ar') $chartIcon = 'fa-wind';
                elseif ($tipo === 'Nível da Água') $chartIcon = 'fa-water';
            ?>
            <div class="chart-card">
                <div class="chart-header">
                    <h3>
                        <i class="fas <?= $chartIcon ?>"></i>
                        <?= htmlspecialchars($tipo) ?>
                    </h3>
                    <span class="chart-unit">Unidade: <?= htmlspecialchars($data['unidade'] ?: '-') ?></span>
                </div>
                <div class="chart-body">
                    <?php if (!empty($data['data'])): ?>
                        <canvas id="chart-<?= htmlspecialchars($tipo) ?>"></canvas>
                    <?php else: ?>
                        <div class="chart-empty">
                            <i class="fas fa-chart-line"></i>
                            <p>Sem dados para este período</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Legenda de cores dos sensores -->
        <div class="charts-legend" id="chartsLegend" style="display: none;">
            <h4><i class="fas fa-info-circle"></i> Legenda dos Sensores</h4>
            <div class="legend-items" id="legendItems"></div>
        </div>

        <div class="action-buttons" style="margin-top: 2rem;">
            <a href="Leituras.php" class="btn btn-water">
                <i class="fas fa-arrow-left"></i> Voltar às Leituras
            </a>
            <a href="leituras_todas.php" class="btn btn-secondary">
                <i class="fas fa-list"></i> Ver Todas as Leituras
            </a>
        </div>
    </main>

    <footer>
        <?php include '../struct/footer.php'; ?>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mostrar/ocultar campos de data personalizada
        const periodSelect = document.getElementById('periodSelect');
        const customDates = document.getElementById('customDates');
        const customDateEnd = document.getElementById('customDateEnd');

        periodSelect.addEventListener('change', function() {
            const dateFields = document.querySelectorAll('.date-field');
            if (this.value === 'custom') {
                dateFields.forEach(f => f.style.display = 'flex');
            } else {
                dateFields.forEach(f => f.style.display = 'none');
            }
        });

        // Cores para os sensores
        const sensorColors = [
            { bg: 'rgba(46, 125, 50, 0.1)', border: 'rgba(46, 125, 50, 1)' },
            { bg: 'rgba(79, 195, 247, 0.1)', border: 'rgba(79, 195, 247, 1)' },
            { bg: 'rgba(255, 152, 0, 0.1)', border: 'rgba(255, 152, 0, 1)' },
            { bg: 'rgba(156, 39, 176, 0.1)', border: 'rgba(156, 39, 176, 1)' },
            { bg: 'rgba(233, 30, 99, 0.1)', border: 'rgba(233, 30, 99, 1)' }
        ];

        // Dados dos gráficos
        const chartData = <?= json_encode($chart_data) ?>;
        const uniqueSensors = {};
        let colorIndex = 0;

        // Coletar sensores únicos
        Object.keys(chartData).forEach(function(tipo) {
            const data = chartData[tipo].data;
            if (!data || data.length === 0) return;

            data.forEach(function(point) {
                if (!uniqueSensors[point.sensor]) {
                    uniqueSensors[point.sensor] = {
                        color: sensorColors[colorIndex % sensorColors.length],
                        index: colorIndex
                    };
                    colorIndex++;
                }
            });
        });

        // Mostrar legenda se houver múltiplos sensores
        const legendContainer = document.getElementById('chartsLegend');
        const legendItems = document.getElementById('legendItems');

        if (Object.keys(uniqueSensors).length > 1) {
            legendContainer.style.display = 'block';
            Object.keys(uniqueSensors).forEach(function(sensor) {
                const item = document.createElement('div');
                item.className = 'legend-item';
                item.innerHTML = '<span class="legend-color" style="background-color: ' + uniqueSensors[sensor].color.border + '"></span><span class="legend-label">' + sensor + '</span>';
                legendItems.appendChild(item);
            });
        }

        // Criar gráficos para cada tipo
        Object.keys(chartData).forEach(function(tipo) {
            const data = chartData[tipo].data;
            if (!data || data.length === 0) return;

            const canvas = document.getElementById('chart-' + tipo);
            if (!canvas) return;

            // Agrupar dados por sensor
            const sensorDatasets = {};
            data.forEach(function(point) {
                if (!sensorDatasets[point.sensor]) {
                    const color = uniqueSensors[point.sensor] ? uniqueSensors[point.sensor].color : sensorColors[0];
                    sensorDatasets[point.sensor] = {
                        label: point.sensor,
                        data: [],
                        borderColor: color.border,
                        backgroundColor: color.bg,
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true,
                        pointRadius: data.length > 100 ? 0 : 3,
                        pointHoverRadius: 5
                    };
                }
                sensorDatasets[point.sensor].data.push({ x: point.x, y: point.y });
            });

            new Chart(canvas, {
                type: 'line',
                data: {
                    datasets: Object.values(sensorDatasets)
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            display: Object.keys(sensorDatasets).length > 1,
                            position: 'top',
                            labels: {
                                font: { size: 11 },
                                boxWidth: 12
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleFont: { size: 12 },
                            bodyFont: { size: 11 },
                            padding: 10,
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.parsed.y.toFixed(2);
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            type: 'time',
                            time: {
                                unit: data.length > 200 ? 'day' : 'hour',
                                displayFormats: {
                                    hour: 'dd HH:mm',
                                    day: 'dd/MM'
                                }
                            },
                            grid: {
                                display: false
                            },
                            ticks: {
                                maxRotation: 45,
                                font: { size: 10 }
                            }
                        },
                        y: {
                            beginAtZero: false,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                font: { size: 11 }
                            }
                        }
                    }
                }
            });
        });
    });
    </script>

    <style>
    .charts-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 2rem;
    }

    .period-summary {
        background: linear-gradient(135deg, var(--light-green), var(--primary-green));
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .period-summary i {
        font-size: 1.1rem;
    }

    .stats-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-card-mini {
        background: white;
        border-radius: 12px;
        padding: 1rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        border-left: 4px solid var(--primary-green);
    }

    .stat-card-mini-header {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.75rem;
        color: var(--primary-green);
        font-weight: 600;
    }

    .stat-card-mini-header i {
        font-size: 1.1rem;
    }

    .stat-card-mini-values {
        font-size: 0.9rem;
        color: #555;
    }

    .stat-card-mini-values strong {
        color: #333;
    }

    .stat-card-mini-count {
        margin-top: 0.5rem;
        padding-top: 0.5rem;
        border-top: 1px solid #eee;
        font-size: 0.85rem;
        color: #888;
    }

    .charts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
        gap: 1.5rem;
    }

    .chart-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .chart-header {
        padding: 1.25rem 1.5rem;
        background: linear-gradient(135deg, var(--primary-green), #1b5e20);
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .chart-header h3 {
        margin: 0;
        font-size: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .chart-header h3 i {
        color: var(--water-blue);
    }

    .chart-unit {
        font-size: 0.8rem;
        opacity: 0.9;
        background: rgba(255, 255, 255, 0.2);
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
    }

    .chart-body {
        padding: 1.5rem;
        min-height: 300px;
        position: relative;
    }

    .chart-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 250px;
        color: #aaa;
    }

    .chart-empty i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.3;
    }

    .chart-empty p {
        margin: 0;
        font-size: 0.95rem;
    }

    .charts-legend {
        background: white;
        border-radius: 12px;
        padding: 1.25rem;
        margin-top: 2rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .charts-legend h4 {
        margin: 0 0 1rem 0;
        color: var(--primary-green);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .legend-items {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
    }

    .legend-color {
        width: 16px;
        height: 4px;
        border-radius: 2px;
    }

    .legend-label {
        color: #555;
    }

    @media (max-width: 768px) {
        .charts-container {
            padding: 1rem;
        }

        .charts-grid {
            grid-template-columns: 1fr;
        }

        .chart-body {
            min-height: 250px;
        }

        .stats-summary {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 480px) {
        .stats-summary {
            grid-template-columns: 1fr;
        }

        .chart-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }
    }
    </style>
</body>
</html>