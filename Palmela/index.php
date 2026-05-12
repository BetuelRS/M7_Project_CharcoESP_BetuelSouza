<?php
require_once __DIR__ . '/../config.php';

$palma_lat = 38.53;
$palma_lon = -8.62;

$api_url = "https://api.open-meteo.com/v1/forecast?" . http_build_query([
    'latitude' => $palma_lat,
    'longitude' => $palma_lon,
    'current' => 'temperature_2m,relative_humidity_2m,apparent_temperature,precipitation,weather_code,cloud_cover,wind_speed_10m,wind_direction_10m,wind_gusts_10m,surface_pressure,uv_index',
    'hourly' => 'temperature_2m,relative_humidity_2m,precipitation_probability,precipitation,weather_code,cloud_cover,wind_speed_10m,wind_direction_10m,wind_gusts_10m,uv_index,dew_point_2m',
    'daily' => 'weather_code,temperature_2m_max,temperature_2m_min,precipitation_sum,precipitation_probability_max,sunrise,sunset,wind_speed_10m_max,uv_index_max,wind_gusts_10m_max',
    'timezone' => 'Europe/Lisbon',
    'forecast_days' => 14
]);

$air_api_url = "https://air-quality-api.open-meteo.com/v1/air-quality?" . http_build_query([
    'latitude' => $palma_lat,
    'longitude' => $palma_lon,
    'current' => 'european_aqi,pm10,pm2_5,nitrogen_dioxide,sulphur_dioxide,ozone,dust,carbon_monoxide,ammonia',
    'hourly' => 'european_aqi,pm10,pm2_5,nitrogen_dioxide,sulphur_dioxide,ozone',
    'timezone' => 'Europe/Lisbon'
]);

$weather_data = null;
$air_data = null;
$error = null;

$context = stream_context_create(['http' => ['timeout' => 15]]);

$response = @file_get_contents($api_url, false, $context);
if ($response !== false) {
    $weather_data = json_decode($response, true);
}

$response_air = @file_get_contents($air_api_url, false, $context);
if ($response_air !== false) {
    $air_data = json_decode($response_air, true);
}

if (!$weather_data) {
    $error = "Não foi possível obter os dados meteorológicos.";
}

function getWeatherDescription($code) {
    $codes = [
        0 => ['desc' => 'Céu limpo', 'icon' => 'fa-sun'],
        1 => ['desc' => 'Maioritariamente limpo', 'icon' => 'fa-sun'],
        2 => ['desc' => 'Parcialmente nublado', 'icon' => 'fa-cloud-sun'],
        3 => ['desc' => 'Nublado', 'icon' => 'fa-cloud'],
        45 => ['desc' => 'Nevoeiro', 'icon' => 'fa-smog'],
        48 => ['desc' => 'Nevoeiro gelado', 'icon' => 'fa-smog'],
        51 => ['desc' => 'Garoa leve', 'icon' => 'fa-cloud-drizzle'],
        53 => ['desc' => 'Garoa moderada', 'icon' => 'fa-cloud-drizzle'],
        55 => ['desc' => 'Garoa intensa', 'icon' => 'fa-cloud-drizzle'],
        61 => ['desc' => 'Chuva leve', 'icon' => 'fa-cloud-rain'],
        63 => ['desc' => 'Chuva moderada', 'icon' => 'fa-cloud-rain'],
        65 => ['desc' => 'Chuva intensa', 'icon' => 'fa-cloud-rain'],
        71 => ['desc' => 'Neve leve', 'icon' => 'fa-snowflake'],
        73 => ['desc' => 'Neve moderada', 'icon' => 'fa-snowflake'],
        75 => ['desc' => 'Neve intensa', 'icon' => 'fa-snowflake'],
        80 => ['desc' => 'Aguaceiros leves', 'icon' => 'fa-cloud-showers-heavy'],
        81 => ['desc' => 'Aguaceiros moderados', 'icon' => 'fa-cloud-showers-heavy'],
        82 => ['desc' => 'Aguaceiros violentos', 'icon' => 'fa-cloud-showers-heavy'],
        95 => ['desc' => 'Trovoada', 'icon' => 'fa-bolt'],
        96 => ['desc' => 'Trovoada com granizo', 'icon' => 'fa-bolt'],
        99 => ['desc' => 'Trovoada com granizo', 'icon' => 'fa-bolt']
    ];
    return $codes[$code] ?? ['desc' => 'Desconhecido', 'icon' => 'fa-cloud'];
}

function getWindDirection($deg) {
    $dirs = ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'];
    return $dirs[round($deg / 45) % 8];
}

function getAQIDescription($aqi) {
    if ($aqi <= 20) return ['label' => 'Bom', 'color' => '#4caf50', 'icon' => 'fa-smile'];
    if ($aqi <= 40) return ['label' => 'Razoável', 'color' => '#ffeb3b', 'icon' => 'fa-meh'];
    if ($aqi <= 60) return ['label' => 'Moderado', 'color' => '#ff9800', 'icon' => 'fa-meh'];
    if ($aqi <= 80) return ['label' => 'Fraco', 'color' => '#f44336', 'icon' => 'fa-frown'];
    return ['label' => 'Mau', 'color' => '#9c27b0', 'icon' => 'fa-tired'];
}

function getUVDescription($uv) {
    if ($uv < 3) return ['label' => 'Baixo', 'color' => '#4caf50'];
    if ($uv < 6) return ['label' => 'Moderado', 'color' => '#ffeb3b'];
    if ($uv < 8) return ['label' => 'Alto', 'color' => '#ff9800'];
    if ($uv < 11) return ['label' => 'Muito Alto', 'color' => '#f44336'];
    return ['label' => 'Extremo', 'color' => '#9c27b0'];
}

$current = $weather_data['current'] ?? [];
$daily = $weather_data['daily'] ?? [];
$hourly = $weather_data['hourly'] ?? [];
$current_aq = $air_data['current'] ?? [];
$hourly_aq = $air_data['hourly'] ?? [];
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clima Palmela - Monitorização Meteorológica</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/styles.css">
    <link rel="stylesheet" href="palmela.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <header>
        <?php include '../struct/header.php'; ?>
    </header>

    <main class="palmela-container">
        <div class="palmela-header">
            <div class="location-info">
                <i class="fas fa-map-marker-alt"></i>
                <div>
                    <h1>Palmela</h1>
                    <span>Setúbal, Portugal</span>
                </div>
            </div>
            <div class="update-time">
                <i class="fas fa-sync-alt"></i>
                <span>Atualizado: <?= date('d/m/Y H:i') ?></span>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php else: ?>
            <div class="current-weather">
                <div class="current-main">
                    <?php
                    $weather = getWeatherDescription($current['weather_code'] ?? 0);
                    ?>
                    <div class="weather-icon-large">
                        <i class="fas <?= $weather['icon'] ?>"></i>
                    </div>
                    <div class="current-temp">
                        <span class="temp-value"><?= round($current['temperature_2m'] ?? 0) ?></span>
                        <span class="temp-unit">°C</span>
                    </div>
                    <div class="weather-desc"><?= $weather['desc'] ?></div>
                    <div class="feels-like">Sensação térmica: <?= round($current['apparent_temperature'] ?? 0) ?>°C</div>
                </div>
                <div class="current-details">
                    <div class="detail-item">
                        <i class="fas fa-tint"></i>
                        <div>
                            <span class="detail-label">Humidade</span>
                            <span class="detail-value"><?= $current['relative_humidity_2m'] ?? 0 ?>%</span>
                        </div>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-wind"></i>
                        <div>
                            <span class="detail-label">Vento</span>
                            <span class="detail-value"><?= round($current['wind_speed_10m'] ?? 0) ?> km/h <?= getWindDirection($current['wind_direction_10m'] ?? 0) ?></span>
                        </div>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-cloud"></i>
                        <div>
                            <span class="detail-label">Nuvens</span>
                            <span class="detail-value"><?= $current['cloud_cover'] ?? 0 ?>%</span>
                        </div>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-umbrella"></i>
                        <div>
                            <span class="detail-label">Precipitação</span>
                            <span class="detail-value"><?= $current['precipitation'] ?? 0 ?> mm</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="extra-weather-grid">
                <div class="extra-card">
                    <div class="extra-icon">
                        <i class="fas fa-gem"></i>
                    </div>
                    <div class="extra-content">
                        <span class="extra-label">Pressão Atmosférica</span>
                        <span class="extra-value"><?= round($current['surface_pressure'] ?? 0) ?></span>
                        <span class="extra-unit">hPa</span>
                    </div>
                </div>
                <div class="extra-card">
                    <div class="extra-icon gusts">
                        <i class="fas fa-meteor"></i>
                    </div>
                    <div class="extra-content">
                        <span class="extra-label">Rajada de Vento</span>
                        <span class="extra-value"><?= round($current['wind_gusts_10m'] ?? 0) ?></span>
                        <span class="extra-unit">km/h</span>
                    </div>
                </div>
                <div class="extra-card">
                    <div class="extra-icon uv">
                        <i class="fas fa-sun"></i>
                    </div>
                    <div class="extra-content">
                        <span class="extra-label">Índice UV</span>
                        <span class="extra-value"><?= round($current['uv_index'] ?? 0, 1) ?></span>
                        <span class="extra-unit uv-tag" style="background: <?= getUVDescription($current['uv_index'] ?? 0)['color'] ?>"><?= getUVDescription($current['uv_index'] ?? 0)['label'] ?></span>
                    </div>
                </div>
                <div class="extra-card">
                    <div class="extra-icon dew">
                        <i class="fas fa-dewpoint"></i>
                    </div>
                    <div class="extra-content">
                        <span class="extra-label">Ponto de Orvalho</span>
                        <span class="extra-value"><?= round($hourly['dew_point_2m'][0] ?? 0, 1) ?></span>
                        <span class="extra-unit">°C</span>
                    </div>
                </div>
            </div>

            <?php if ($current_aq): ?>
            <div class="air-quality-section">
                <h2 class="section-title">
                    <i class="fas fa-smog"></i>
                    Qualidade do Ar
                    <?php $aq_info = getAQIDescription($current_aq['european_aqi'] ?? 0); ?>
                    <span class="aq-badge" style="background: <?= $aq_info['color'] ?>">
                        <i class="fas <?= $aq_info['icon'] ?>"></i>
                        <?= $aq_info['label'] ?> (<?= $current_aq['european_aqi'] ?? 0 ?>)
                    </span>
                </h2>
                <div class="aq-grid">
                    <div class="aq-card">
                        <div class="aq-icon pm25">
                            <i class="fas fa-fire"></i>
                        </div>
                        <div class="aq-info">
                            <span class="aq-label">PM2.5</span>
                            <span class="aq-value"><?= round($current_aq['pm2_5'] ?? 0, 1) ?></span>
                            <span class="aq-unit">µg/m³</span>
                        </div>
                    </div>
                    <div class="aq-card">
                        <div class="aq-icon pm10">
                            <i class="fas fa-smog"></i>
                        </div>
                        <div class="aq-info">
                            <span class="aq-label">PM10</span>
                            <span class="aq-value"><?= round($current_aq['pm10'] ?? 0, 1) ?></span>
                            <span class="aq-unit">µg/m³</span>
                        </div>
                    </div>
                    <div class="aq-card">
                        <div class="aq-icon o3">
                            <i class="fas fa-wind"></i>
                        </div>
                        <div class="aq-info">
                            <span class="aq-label">Ozono (O₃)</span>
                            <span class="aq-value"><?= round($current_aq['ozone'] ?? 0, 1) ?></span>
                            <span class="aq-unit">µg/m³</span>
                        </div>
                    </div>
                    <div class="aq-card">
                        <div class="aq-icon no2">
                            <i class="fas fa-cloud"></i>
                        </div>
                        <div class="aq-info">
                            <span class="aq-label">NO₂</span>
                            <span class="aq-value"><?= round($current_aq['nitrogen_dioxide'] ?? 0, 1) ?></span>
                            <span class="aq-unit">µg/m³</span>
                        </div>
                    </div>
                    <div class="aq-card">
                        <div class="aq-icon so2">
                            <i class="fas fa-industry"></i>
                        </div>
                        <div class="aq-info">
                            <span class="aq-label">SO₂</span>
                            <span class="aq-value"><?= round($current_aq['sulphur_dioxide'] ?? 0, 1) ?></span>
                            <span class="aq-unit">µg/m³</span>
                        </div>
                    </div>
                    <div class="aq-card">
                        <div class="aq-icon co">
                            <i class="fas fa-cloud-meatball"></i>
                        </div>
                        <div class="aq-info">
                            <span class="aq-label">CO</span>
                            <span class="aq-value"><?= round($current_aq['carbon_monoxide'] ?? 0, 1) ?></span>
                            <span class="aq-unit">µg/m³</span>
                        </div>
                    </div>
                </div>
                <div class="aq-chart-wrapper">
                    <canvas id="aqChart"></canvas>
                </div>
            </div>
            <?php endif; ?>

            <div class="charts-section">
                <h2 class="section-title">
                    <i class="fas fa-chart-line"></i>
                    Previsão Semanal
                </h2>
                <div class="chart-card">
                    <div class="chart-tabs">
                        <button class="chart-tab active" data-chart="temperature">Temperatura</button>
                        <button class="chart-tab" data-chart="precipitation">Precipitação</button>
                        <button class="chart-tab" data-chart="humidity">Humidade</button>
                        <button class="chart-tab" data-chart="wind">Vento</button>
                        <button class="chart-tab" data-chart="uv">UV</button>
                    </div>
                    <div class="chart-container">
                        <canvas id="weeklyChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="daily-forecast">
                <h2 class="section-title">
                    <i class="fas fa-calendar-week"></i>
                    Previsão Diária (14 dias)
                </h2>
                <div class="forecast-grid extended">
                    <?php for ($i = 0; $i < 14; $i++): ?>
                        <?php
                        $dayDate = date('d/m', strtotime($daily['time'][$i] ?? 'today'));
                        $dayName = $i === 0 ? 'Hoje' : ($i === 1 ? 'Amanhã' : date('D', strtotime($daily['time'][$i] ?? 'today')));
                        $weather = getWeatherDescription($daily['weather_code'][$i] ?? 0);
                        $uv_info = getUVDescription($daily['uv_index_max'][$i] ?? 0);
                        ?>
                        <div class="forecast-card <?= $i === 0 ? 'today' : '' ?>">
                            <div class="forecast-day"><?= $dayName ?></div>
                            <div class="forecast-date"><?= $dayDate ?></div>
                            <div class="forecast-icon">
                                <i class="fas <?= $weather['icon'] ?>"></i>
                            </div>
                            <div class="forecast-desc"><?= $weather['desc'] ?></div>
                            <div class="forecast-temps">
                                <span class="temp-max"><?= round($daily['temperature_2m_max'][$i] ?? 0) ?>°</span>
                                <span class="temp-min"><?= round($daily['temperature_2m_min'][$i] ?? 0) ?>°</span>
                            </div>
                            <div class="forecast-uv" style="background: <?= $uv_info['color'] ?>">
                                <i class="fas fa-sun"></i> <?= round($daily['uv_index_max'][$i] ?? 0, 1) ?>
                            </div>
                            <div class="forecast-precip">
                                <i class="fas fa-tint"></i>
                                <?= $daily['precipitation_probability_max'][$i] ?? 0 ?>%
                            </div>
                            <div class="forecast-wind">
                                <i class="fas fa-wind"></i>
                                <?= round($daily['wind_speed_10m_max'][$i] ?? 0) ?> km/h
                            </div>
                            <div class="forecast-gust">
                                <i class="fas fa-meteor"></i>
                                <?= round($daily['wind_gusts_10m_max'][$i] ?? 0) ?> km/h
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <div class="hourly-section">
                <h2 class="section-title">
                    <i class="fas fa-clock"></i>
                    Próximas 48 Horas
                </h2>
                <div class="hourly-table-wrapper">
                    <table class="hourly-table">
                        <thead>
                            <tr>
                                <th>Hora</th>
                                <th>Condição</th>
                                <th>Temp</th>
                                <th>Humidade</th>
                                <th>Ponto Orvalho</th>
                                <th>Vento</th>
                                <th>Rajada</th>
                                <th>UV</th>
                                <th>Prob. Chuva</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($i = 0; $i < 48; $i++): ?>
                                <?php
                                $hourTime = date('H:i', strtotime($hourly['time'][$i] ?? 'now'));
                                $weather = getWeatherDescription($hourly['weather_code'][$i] ?? 0);
                                ?>
                                <tr class="<?= $i === 0 ? 'current-hour' : '' ?>">
                                    <td><?= $hourTime ?></td>
                                    <td>
                                        <div class="hourly-condition">
                                            <i class="fas <?= $weather['icon'] ?>"></i>
                                            <span><?= $weather['desc'] ?></span>
                                        </div>
                                    </td>
                                    <td><?= round($hourly['temperature_2m'][$i] ?? 0) ?>°C</td>
                                    <td><?= $hourly['relative_humidity_2m'][$i] ?? 0 ?>%</td>
                                    <td><?= round($hourly['dew_point_2m'][$i] ?? 0, 1) ?>°C</td>
                                    <td><?= round($hourly['wind_speed_10m'][$i] ?? 0) ?> km/h</td>
                                    <td><?= round($hourly['wind_gusts_10m'][$i] ?? 0) ?> km/h</td>
                                    <td><?= round($hourly['uv_index'][$i] ?? 0, 1) ?></td>
                                    <td>
                                        <div class="precip-bar">
                                            <div class="precip-fill" style="width: <?= $hourly['precipitation_probability'][$i] ?? 0 ?>%"></div>
                                        </div>
                                        <span class="precip-text"><?= $hourly['precipitation_probability'][$i] ?? 0 ?>%</span>
                                    </td>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="sun-info">
                <h2 class="section-title">
                    <i class="fas fa-sun"></i>
                    Nascer e Pôr do Sol
                </h2>
                <div class="sun-cards">
                    <?php for ($i = 0; $i < 5; $i++): ?>
                        <div class="sun-card">
                            <div class="sun-day"><?= $i === 0 ? 'Hoje' : date('d/m', strtotime($daily['time'][$i] ?? 'today')) ?></div>
                            <div class="sun-times">
                                <div class="sun-item sunrise">
                                    <i class="fas fa-sun"></i>
                                    <span>Nascer</span>
                                    <strong><?= date('H:i', strtotime($daily['sunrise'][$i] ?? '06:00')) ?></strong>
                                </div>
                                <div class="sun-item sunset">
                                    <i class="fas fa-moon"></i>
                                    <span>Pôr</span>
                                    <strong><?= date('H:i', strtotime($daily['sunset'][$i] ?? '20:00')) ?></strong>
                                </div>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <div class="stats-cards">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-temperature-high"></i>
                    </div>
                    <div class="stats-content">
                        <span class="stats-label">Temp. Máxima da Semana</span>
                        <span class="stats-value"><?= round(max($daily['temperature_2m_max'] ?? [0])) ?>°C</span>
                    </div>
                </div>
                <div class="stats-card">
                    <div class="stats-icon cold">
                        <i class="fas fa-temperature-low"></i>
                    </div>
                    <div class="stats-content">
                        <span class="stats-label">Temp. Mínima da Semana</span>
                        <span class="stats-value"><?= round(min($daily['temperature_2m_min'] ?? [0])) ?>°C</span>
                    </div>
                </div>
                <div class="stats-card">
                    <div class="stats-icon rain">
                        <i class="fas fa-cloud-rain"></i>
                    </div>
                    <div class="stats-content">
                        <span class="stats-label">Precipitação Total</span>
                        <span class="stats-value"><?= array_sum($daily['precipitation_sum'] ?? [0]) ?> mm</span>
                    </div>
                </div>
                <div class="stats-card">
                    <div class="stats-icon wind">
                        <i class="fas fa-wind"></i>
                    </div>
                    <div class="stats-content">
                        <span class="stats-label">Vento Máximo</span>
                        <span class="stats-value"><?= round(max($daily['wind_speed_10m_max'] ?? [0])) ?> km/h</span>
                    </div>
                </div>
                <div class="stats-card">
                    <div class="stats-icon gust">
                        <i class="fas fa-meteor"></i>
                    </div>
                    <div class="stats-content">
                        <span class="stats-label">Rajada Máxima</span>
                        <span class="stats-value"><?= round(max($daily['wind_gusts_10m_max'] ?? [0])) ?> km/h</span>
                    </div>
                </div>
                <div class="stats-card">
                    <div class="stats-icon uv-stat">
                        <i class="fas fa-sun"></i>
                    </div>
                    <div class="stats-content">
                        <span class="stats-label">UV Máximo</span>
                        <span class="stats-value"><?= round(max($daily['uv_index_max'] ?? [0]), 1) ?></span>
                    </div>
                </div>
            </div>

            <div class="api-credit">
                <i class="fas fa-database"></i>
                Dados fornecidos por <a href="https://open-meteo.com/" target="_blank">Open-Meteo API</a> · <a href="https://open-meteo.com/en/docs/air-quality-api" target="_blank">Air Quality API</a>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <?php include '../struct/footer.php'; ?>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabs = document.querySelectorAll('.chart-tab');
        let currentChartType = 'temperature';

        const dailyData = <?= json_encode($daily) ?>;
        const hourlyData = <?= json_encode($hourly) ?>;
        const hourlyAQ = <?= json_encode($hourly_aq) ?>;

        const labels = dailyData.time.map(d => {
            const date = new Date(d);
            return date.toLocaleDateString('pt-PT', { weekday: 'short', day: 'numeric', month: 'short' });
        });

        const chartConfig = {
            temperature: {
                label: 'Temperatura Máx (°C)',
                data: dailyData.temperature_2m_max,
                color: 'rgba(255, 152, 0, 1)',
                bg: 'rgba(255, 152, 0, 0.1)'
            },
            precipitation: {
                label: 'Precipitação (mm)',
                data: dailyData.precipitation_sum,
                color: 'rgba(33, 150, 243, 1)',
                bg: 'rgba(33, 150, 243, 0.3)'
            },
            humidity: {
                label: 'Prob. Chuva (%)',
                data: dailyData.precipitation_probability_max,
                color: 'rgba(33, 150, 243, 1)',
                bg: 'rgba(33, 150, 243, 0.2)'
            },
            wind: {
                label: 'Vento Máx (km/h)',
                data: dailyData.wind_speed_10m_max,
                color: 'rgba(76, 175, 80, 1)',
                bg: 'rgba(76, 175, 80, 0.2)'
            },
            uv: {
                label: 'UV Index',
                data: dailyData.uv_index_max,
                color: 'rgba(255, 235, 59, 1)',
                bg: 'rgba(255, 235, 59, 0.3)'
            }
        };

        let weeklyChart;

        function createChart(type) {
            const config = chartConfig[type];
            const ctx = document.getElementById('weeklyChart').getContext('2d');

            if (weeklyChart) {
                weeklyChart.destroy();
            }

            weeklyChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: config.label,
                        data: config.data,
                        backgroundColor: config.bg,
                        borderColor: config.color,
                        borderWidth: 2,
                        borderRadius: 8,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: { size: 14 },
                            bodyFont: { size: 13 }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 11 } }
                        },
                        y: {
                            beginAtZero: type === 'precipitation' || type === 'wind' || type === 'uv',
                            grid: { color: 'rgba(0, 0, 0, 0.05)' },
                            ticks: { font: { size: 11 } }
                        }
                    }
                }
            });
        }

        createChart('temperature');

        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                tabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                currentChartType = this.dataset.chart;
                createChart(currentChartType);
            });
        });

        <?php if ($hourly_aq && !empty($hourly_aq['time'])): ?>
        const aqLabels = hourlyAQ.time.slice(0, 24).map(t => {
            return new Date(t).toLocaleTimeString('pt-PT', { hour: '2-digit', minute: '2-digit' });
        });

        const aqCtx = document.getElementById('aqChart').getContext('2d');
        new Chart(aqCtx, {
            type: 'line',
            data: {
                labels: aqLabels,
                datasets: [
                    {
                        label: 'PM2.5',
                        data: hourlyAQ.pm2_5.slice(0, 24),
                        borderColor: 'rgba(244, 67, 54, 1)',
                        backgroundColor: 'rgba(244, 67, 54, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'PM10',
                        data: hourlyAQ.pm10.slice(0, 24),
                        borderColor: 'rgba(255, 152, 0, 1)',
                        backgroundColor: 'rgba(255, 152, 0, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'O₃',
                        data: hourlyAQ.ozone.slice(0, 24),
                        borderColor: 'rgba(33, 150, 243, 1)',
                        backgroundColor: 'rgba(33, 150, 243, 0.1)',
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    x: {
                        grid: { display: false }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0, 0, 0, 0.05)' }
                    }
                }
            }
        });
        <?php endif; ?>
    });
    </script>
</body>
</html>
