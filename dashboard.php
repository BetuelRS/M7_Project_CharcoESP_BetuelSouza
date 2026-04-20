<!-- dashboard.php - Página principal do dashboard com cards de resumo e gráficos -->
<!-- Cards de consultas: quantos sensores tem, quantas leituras tem, últimas leituras, etc -->
<?php
require_once __DIR__ . '/config.php';
include BASE_PATH . 'db.php';
?>

<div class="dashboard-grid">
    <!-- Card Total de Sensores -->
    <div class="dashboard-card card-sensors">
        <div class="card-icon">
            <i class="fas fa-microchip"></i>
        </div>
        <div class="card-content">
            <h3>Total de Sensores</h3>
            <?php
            $sql = "SELECT COUNT(*) AS total_sensores FROM sensores";
            $result = $conn->query($sql);
            $total_sensores = ($result->num_rows > 0) ? $result->fetch_assoc()['total_sensores'] : 0;
            ?>
            <p class="card-value"><?= $total_sensores ?></p>
        </div>
    </div>

    <!-- Card Total de Leituras -->
    <div class="dashboard-card card-readings">
        <div class="card-icon">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="card-content">
            <h3>Total de Leituras</h3>
            <?php
            $sql = "SELECT COUNT(*) AS total_leituras FROM leituras";
            $result = $conn->query($sql);
            $total_leituras = ($result->num_rows > 0) ? $result->fetch_assoc()['total_leituras'] : 0;
            ?>
            <p class="card-value"><?= $total_leituras ?></p>
        </div>
    </div>

    <!-- Card Últimas Leituras (tabela) -->
    <div class="dashboard-card card-recent-readings full-width">
        <div class="card-header">
            <div class="card-icon">
                <i class="fas fa-water"></i>
            </div>
            <h3>Últimas Leituras</h3>
        </div>
        <div class="card-table">
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

    <!-- Card Gráfico de Leituras filtrado por sensor dos últimos 30 dias -->
    <div class="dashboard-card card-readings-chart full-width">
        <div class="card-header">
            <div class="card-icon">
                <i class="fas fa-chart-bar"></i>
            </div>
            <h3>Leituras Últimos 30 Dias - Filtrado por Sensor</h3>
            
            <!-- Filtro de Sensor -->
            <div class="sensor-filter">
                <label for="sensorFilter">Selecione o Sensor:</label>
                <select id="sensorFilter" class="sensor-select">
                    <option value="">-- Escolha um sensor --</option>
                    <?php
                    $sql = "SELECT cod_sensor, nome, tipo FROM sensores WHERE ativo = 1 ORDER BY nome";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0):
                        while ($row = $result->fetch_assoc()):
                    ?>
                    <option value="<?= $row['cod_sensor'] ?>">
                        <?= htmlspecialchars($row['nome']) ?> (<?= htmlspecialchars($row['tipo']) ?>)
                    </option>
                    <?php
                        endwhile;
                    endif;
                    ?>
                </select>
            </div>
        </div>

        <div class="card-chart">
            <div id="chartInfo" class="chart-info">
                <p>Selecione um sensor para visualizar os dados dos últimos 30 dias</p>
            </div>
            <canvas id="readingsChart" style="display: none;"></canvas>
        </div>
    </div>
</div>

<!-- Script para renderizar o gráfico com Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script> let chart = null;

document.getElementById('sensorFilter').addEventListener('change', function() {
    const sensorId = this.value;
    
    if (!sensorId) {
        document.getElementById('chartInfo').style.display = 'flex';
        document.getElementById('readingsChart').style.display = 'none';
        if (chart) {
            chart.destroy();
            chart = null;
        }
        return;
    }

    fetch(`<?= BASE_URL ?>LT/api_chart_readings.php?sensor_id=${sensorId}`)
        .then(response => response.json())
        .then(data => {
            if (data.erro) {
                document.getElementById('chartInfo').innerHTML = `<p style="color: red;">Erro: ${data.erro}</p>`;
                document.getElementById('chartInfo').style.display = 'flex';
                document.getElementById('readingsChart').style.display = 'none';
                return;
            }

            if (chart) {
                chart.destroy();
            }

            // Se não houver dados, exibe mensagem
            if (data.labels.length === 0) {
                document.getElementById('chartInfo').innerHTML = '<p>Nenhuma leitura encontrada para este sensor nos últimos 30 dias.</p>';
                document.getElementById('chartInfo').style.display = 'flex';
                document.getElementById('readingsChart').style.display = 'none';
                return;
            }

            document.getElementById('chartInfo').style.display = 'none';
            document.getElementById('readingsChart').style.display = 'block';

            const cores = {
                'Temperatura': '#FF6384',
                'Humidade': '#36A2EB',
                'Luminosidade': '#FFCE56',
                'Qualidade do Ar': '#4BC0C0',
                'Nível da Água': '#9966FF'
            };
            const corGrafico = cores[data.sensor_tipo] || '#2E7D32';

            const ctx = document.getElementById('readingsChart').getContext('2d');
            chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: `${data.sensor_nome} (${data.sensor_tipo}) - ${data.unidade}`,
                        data: data.valores,
                        borderColor: corGrafico,
                        backgroundColor: corGrafico + '20',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        pointBackgroundColor: corGrafico,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: { usePointStyle: true, padding: 15 }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            backgroundColor: 'rgba(0,0,0,0.8)',
                            titleColor: '#fff',
                            bodyColor: '#ddd',
                            borderColor: corGrafico,
                            borderWidth: 1
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            title: { display: true, text: data.unidade },
                            grid: { color: 'rgba(0,0,0,0.05)' }
                        },
                        x: {
                            grid: { display: false },
                            ticks: {
                                maxRotation: 45,
                                minRotation: 30,
                                maxTicksLimit: 12 // Evita aglomeração de labels
                            }
                        }
                    },
                    elements: { line: { borderJoinStyle: 'round' } }
                }
            });
        })
        .catch(error => {
            console.error('Erro:', error);
            document.getElementById('chartInfo').innerHTML = '<p style="color: red;">Erro ao carregar os dados</p>';
            document.getElementById('chartInfo').style.display = 'flex';
            document.getElementById('readingsChart').style.display = 'none';
        });
});
</script>