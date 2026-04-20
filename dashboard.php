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
    <!-- Card da quantidade de leituras hoje -->
    <div class="dashboard-card card-todays-readings">
        <div class="card-icon">
            <i class="fas fa-calendar-day"></i>
        </div>
        <div class="card-content">
            <h3>Leituras Hoje</h3>
            <?php
            $sql = "SELECT COUNT(*) AS todays_leituras 
                    FROM leituras 
                    WHERE DATE(data_hora) = CURDATE()";
            $result = $conn->query($sql);
            $todays_leituras = ($result->num_rows > 0) ? $result->fetch_assoc()['todays_leituras'] : 0;
            ?>
            <p class="card-value"><?= $todays_leituras ?></p>
        </div>
    </div>
        <!-- Card da ultima leitura -->
    <div class="dashboard-card card-last-reading">
        <div class="card-icon">
            <i class="fas fa-clock"></i>
        </div>
        <div class="card-content">
            <h3>Última Leitura</h3>
            <?php
            $sql = "SELECT s.tipo AS metrica, l.valor, l.unidade, l.data_hora 
                    FROM leituras l
                    INNER JOIN sensores s ON l.cod_sensor = s.cod_sensor
                    ORDER BY l.data_hora DESC 
                    LIMIT 1";
            $result = $conn->query($sql);
            if ($result->num_rows > 0):
                $last_reading = $result->fetch_assoc();
                $last_reading_value = htmlspecialchars($last_reading['valor']) . ' ' . htmlspecialchars($last_reading['unidade']);
                $last_reading_time = date('d/m/Y H:i', strtotime($last_reading['data_hora']));
            else:
                $last_reading_value = 'Nenhuma leitura';
                $last_reading_time = '';
            endif;
            ?>
            <p class="card-value"><?= $last_reading_value ?></p>
            <p class="card-subvalue"><?= $last_reading_time ?></p>
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
</div>