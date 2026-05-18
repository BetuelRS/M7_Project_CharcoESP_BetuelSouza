<!-- dashboard.php - Página principal do dashboard com cards de resumo e gráficos -->
<?php
require_once __DIR__ . '/config.php';
include BASE_PATH . 'db.php';
require_once BASE_PATH . 'includes/functions.php';

// Estatísticas dos últimos 7 dias
$stats = [];
$sql_stats = "SELECT s.tipo, AVG(l.valor) as media, MIN(l.valor) as min_val, MAX(l.valor) as max_val
              FROM leituras l
              INNER JOIN sensores s ON l.cod_sensor = s.cod_sensor
              WHERE l.data_hora >= DATE_SUB(NOW(), INTERVAL 7 DAY)
              GROUP BY s.tipo";
$result_stats = $conn->query($sql_stats);
if ($result_stats && $result_stats->num_rows > 0) {
    while ($row = $result_stats->fetch_assoc()) {
        $stats[$row['tipo']] = [
            'media' => round($row['media'], 1),
            'min' => round($row['min_val'], 1),
            'max' => round($row['max_val'], 1)
        ];
    }
}

// Últimas leituras por tipo
$ultimas_leituras = [];
$sql_ultimas = "SELECT s.tipo, l.valor, l.unidade, l.data_hora
                FROM leituras l
                INNER JOIN sensores s ON l.cod_sensor = s.cod_sensor
                INNER JOIN (
                    SELECT cod_sensor, MAX(data_hora) as max_data
                    FROM leituras
                    GROUP BY cod_sensor
                ) ultimas ON l.cod_sensor = ultimas.cod_sensor AND l.data_hora = ultimas.max_data";
$result_ultimas = $conn->query($sql_ultimas);
if ($result_ultimas && $result_ultimas->num_rows > 0) {
    while ($row = $result_ultimas->fetch_assoc()) {
        $ultimas_leituras[$row['tipo']] = $row;
    }
}

// Contagens
$sql = "SELECT COUNT(*) AS total_sensores FROM sensores";
$result = $conn->query($sql);
$total_sensores = ($result->num_rows > 0) ? $result->fetch_assoc()['total_sensores'] : 0;

$sql = "SELECT COUNT(*) AS todays_leituras FROM leituras WHERE DATE(data_hora) = CURDATE()";
$result = $conn->query($sql);
$todays_leituras = ($result->num_rows > 0) ? $result->fetch_assoc()['todays_leituras'] : 0;

$sql = "SELECT COUNT(*) AS total_leituras FROM leituras";
$result = $conn->query($sql);
$total_leituras = ($result->num_rows > 0) ? $result->fetch_assoc()['total_leituras'] : 0;
?>

<div class="dashboard">
    <h2 class="dashboard-title">Visão Geral</h2>

    <!-- Resumo -->
    <div class="dashboard-section">
        <div class="summary-cards">
            <div class="dashboard-card">
                <div class="card-icon">
                    <i class="fas fa-microchip"></i>
                </div>
                <div class="card-content">
                    <h3>Total de Sensores</h3>
                    <p class="card-value"><?= $total_sensores ?></p>
                </div>
            </div>

            <div class="dashboard-card">
                <div class="card-icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="card-content">
                    <h3>Leituras Hoje</h3>
                    <p class="card-value"><?= $todays_leituras ?></p>
                </div>
            </div>

            <div class="dashboard-card">
                <div class="card-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="card-content">
                    <h3>Total de Leituras</h3>
                    <p class="card-value"><?= $total_leituras ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Estatísticas -->
    <?php if (!empty($stats)): ?>
    <div class="dashboard-section">
        <h2 class="dashboard-section-title">Estatísticas (Últimos 7 dias)</h2>
        <div class="stats-grid">
            <?php foreach ($stats as $tipo => $dados): ?>
            <div class="dashboard-card stat-card">
                <div class="card-icon">
                    <i class="fas <?= tipo_para_icone($tipo) ?>"></i>
                </div>
                <div class="card-content">
                    <h3><?= htmlspecialchars($tipo) ?></h3>
                    <div class="stat-values">
                        <span><strong>Média:</strong> <?= $dados['media'] ?></span>
                        <span><strong>Mín:</strong> <?= $dados['min'] ?></span>
                        <span><strong>Máx:</strong> <?= $dados['max'] ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Últimas Leituras -->
    <div class="dashboard-section">
        <h2 class="dashboard-section-title">Última Leitura por Sensor</h2>
        <div class="last-readings-grid">
            <?php foreach (tipos_ordenados() as $tipo):
                $leitura = $ultimas_leituras[$tipo] ?? null;
            ?>
            <div class="dashboard-card last-reading-card">
                <div class="card-icon">
                    <i class="fas <?= tipo_para_icone($tipo) ?>"></i>
                </div>
                <div class="card-content">
                    <h3><?= htmlspecialchars($tipo) ?></h3>
                    <p class="card-value">
                        <?= $leitura ? htmlspecialchars($leitura['valor']) . ' ' . htmlspecialchars($leitura['unidade']) : 'Nenhuma leitura' ?>
                    </p>
                    <?php if ($leitura): ?>
                    <span class="card-time"><?= date('d/m H:i', strtotime($leitura['data_hora'])) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Tabela de Leituras Recentes -->
    <div class="dashboard-section">
        <h2 class="dashboard-section-title">Leituras Recentes</h2>
        <div class="dashboard-card card-table">
            <div class="card-header">
                <div class="card-icon">
                    <i class="fas fa-list"></i>
                </div>
                <h3>Últimas 10 Leituras</h3>
            </div>
            <div class="table-responsive">
                <table class="readings-table">
                    <thead>
                        <tr>
                            <th>Sensor</th>
                            <th>Valor</th>
                            <th>Data/Hora</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT s.tipo AS metrica, l.valor, l.unidade, l.data_hora 
                                FROM leituras l
                                INNER JOIN sensores s ON l.cod_sensor = s.cod_sensor
                                ORDER BY l.data_hora DESC 
                                LIMIT 10";
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0):
                            while ($row = $result->fetch_assoc()):
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($row['metrica']) ?></td>
                                <td><?= htmlspecialchars($row['valor']) ?> <?= htmlspecialchars($row['unidade']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($row['data_hora'])) ?></td>
                            </tr>
                        <?php
                            endwhile;
                        else:
                        ?>
                            <tr>
                                <td colspan="3" class="no-data">Nenhuma leitura encontrada</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>