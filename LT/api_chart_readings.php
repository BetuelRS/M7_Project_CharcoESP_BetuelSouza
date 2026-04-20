<?php
//api_chart_readings.php - API para fornecer dados de leituras dos últimos 30 dias filtrado por sensor
require_once __DIR__ . '/../config.php';
include BASE_PATH . 'db.php';

header('Content-Type: application/json');

// Validar e sanitizar sensor_id
$sensor_id = isset($_GET['sensor_id']) ? (int)$_GET['sensor_id'] : 0;

if (!$sensor_id) {
    http_response_code(400);
    echo json_encode(['erro' => 'sensor_id é obrigatório']);
    exit;
}

// Usar prepared statement
$sql = "SELECT 
            l.data_hora,
            l.valor,
            s.tipo,
            s.nome,
            l.unidade
        FROM leituras l
        INNER JOIN sensores s ON l.cod_sensor = s.cod_sensor
        WHERE l.cod_sensor = ? 
          AND l.data_hora >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ORDER BY l.data_hora ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $sensor_id);
$stmt->execute();
$result = $stmt->get_result();

$dados = [];
$labels = [];
$valores = [];
$sensor_tipo = 'Sensor';
$sensor_nome = 'N/A';
$unidade = '';

if ($row = $result->fetch_assoc()) {
    // Primeira linha contém os dados constantes do sensor
    $sensor_tipo = $row['tipo'] ?? 'Sensor';
    $sensor_nome = $row['nome'] ?? 'N/A';
    $unidade = $row['unidade'] ?? '';

    // Preencher arrays com a primeira linha
    $labels[] = date('d/m H:i', strtotime($row['data_hora']));
    $valores[] = (float)$row['valor'];

    // Continuar preenchendo com as demais linhas
    while ($row = $result->fetch_assoc()) {
        $labels[] = date('d/m H:i', strtotime($row['data_hora']));
        $valores[] = (float)$row['valor'];
    }
}

$stmt->close();
$conn->close();

echo json_encode([
    'labels' => $labels,
    'valores' => $valores,
    'sensor_tipo' => $sensor_tipo,
    'sensor_nome' => $sensor_nome,
    'unidade' => $unidade
]);
?>