<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido']);
    exit();
}

// Auth via Bearer token
$auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
if (!$auth) {
    http_response_code(401);
    echo json_encode(['erro' => 'Token de autenticação necessário. Use: Authorization: Bearer <api_key>']);
    exit();
}

$token = str_replace('Bearer ', '', $auth);
include BASE_PATH . 'db.php';

$stmt = $conn->prepare("SELECT id, utilizador_id FROM api_keys WHERE `key` = ? AND ativo = 1");
$stmt->bind_param("s", $token);
$stmt->execute();
$key_row = $stmt->get_result()->fetch_assoc();

if (!$key_row) {
    http_response_code(403);
    echo json_encode(['erro' => 'API Key inválida ou desativada']);
    exit();
}

// Atualizar último uso
$stmt2 = $conn->prepare("UPDATE api_keys SET ultimo_uso = NOW() WHERE id = ?");
$stmt2->bind_param("i", $key_row['id']);
$stmt2->execute();
$stmt2->close();

// Router
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base = rtrim(parse_url(env('APP_URL', 'http://localhost/M7_Project/'), PHP_URL_PATH), '/');
$route = str_replace($base . '/api', '', $path);
$route = trim($route, '/');

try {
    switch (true) {
        case $route === 'sensores':
            $result = $conn->query("SELECT cod_sensor, nome, tipo, descricao, localizacao, modelo, fabricante, data_instalacao, ativo FROM sensores ORDER BY nome");
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $row['cod_sensor'] = (int)$row['cod_sensor'];
                $row['ativo'] = (bool)$row['ativo'];
                $data[] = $row;
            }
            responder($data);
            break;

        case preg_match('#^sensores/(\d+)$#', $route, $m) === 1:
            $stmt = $conn->prepare("SELECT cod_sensor, nome, tipo, descricao, localizacao, modelo, fabricante, data_instalacao, ativo FROM sensores WHERE cod_sensor = ?");
            $stmt->bind_param("i", $m[1]);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            if ($row) {
                $row['cod_sensor'] = (int)$row['cod_sensor'];
                $row['ativo'] = (bool)$row['ativo'];
                responder($row);
            } else {
                http_response_code(404);
                responder(['erro' => 'Sensor não encontrado']);
            }
            break;

        case $route === 'leituras':
            $where = [];
            $params = [];
            $types = '';

            if (isset($_GET['sensor'])) {
                $where[] = "l.cod_sensor = ?";
                $params[] = (int)$_GET['sensor'];
                $types .= 'i';
            }
            if (isset($_GET['unidade'])) {
                $where[] = "l.unidade = ?";
                $params[] = $_GET['unidade'];
                $types .= 's';
            }
            if (isset($_GET['data_inicio'])) {
                $where[] = "l.data_hora >= ?";
                $params[] = $_GET['data_inicio'];
                $types .= 's';
            }
            if (isset($_GET['data_fim'])) {
                $where[] = "l.data_hora <= ?";
                $params[] = $_GET['data_fim'] . ' 23:59:59';
                $types .= 's';
            }

            $where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
            $limite = min((int)($_GET['limite'] ?? 100), 1000);
            $offset = max(0, (int)($_GET['offset'] ?? 0));

            $sql = "SELECT l.cod_leituras, l.cod_sensor, l.valor, l.unidade, l.data_hora, l.observacoes, s.nome AS sensor_nome, s.tipo AS sensor_tipo
                    FROM leituras l JOIN sensores s ON l.cod_sensor = s.cod_sensor
                    $where_sql ORDER BY l.data_hora DESC LIMIT ? OFFSET ?";

            $params[] = $limite;
            $params[] = $offset;
            $types .= 'ii';

            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $row['cod_leituras'] = (int)$row['cod_leituras'];
                $row['cod_sensor'] = (int)$row['cod_sensor'];
                $row['valor'] = (float)$row['valor'];
                $data[] = $row;
            }
            responder($data);
            break;

        case preg_match('#^leituras/(\d+)$#', $route, $m) === 1:
            $stmt = $conn->prepare("SELECT l.cod_leituras, l.cod_sensor, l.valor, l.unidade, l.data_hora, l.observacoes, s.nome AS sensor_nome, s.tipo AS sensor_tipo
                                    FROM leituras l JOIN sensores s ON l.cod_sensor = s.cod_sensor WHERE l.cod_leituras = ?");
            $stmt->bind_param("i", $m[1]);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            if ($row) {
                $row['cod_leituras'] = (int)$row['cod_leituras'];
                $row['cod_sensor'] = (int)$row['cod_sensor'];
                $row['valor'] = (float)$row['valor'];
                responder($row);
            } else {
                http_response_code(404);
                responder(['erro' => 'Leitura não encontrada']);
            }
            break;

        case $route === 'estatisticas':
            $dias = min((int)($_GET['dias'] ?? 7), 365);
            $result = $conn->query("
                SELECT s.tipo, s.nome,
                       COUNT(l.cod_leituras) AS total,
                       ROUND(AVG(l.valor), 2) AS media,
                       ROUND(MIN(l.valor), 2) AS minimo,
                       ROUND(MAX(l.valor), 2) AS maximo
                FROM sensores s
                LEFT JOIN leituras l ON s.cod_sensor = l.cod_sensor AND l.data_hora >= DATE_SUB(NOW(), INTERVAL $dias DAY)
                GROUP BY s.cod_sensor
                ORDER BY s.nome
            ");
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $row['total'] = (int)$row['total'];
                $row['media'] = $row['media'] ? (float)$row['media'] : null;
                $row['minimo'] = $row['minimo'] ? (float)$row['minimo'] : null;
                $row['maximo'] = $row['maximo'] ? (float)$row['maximo'] : null;
                $data[] = $row;
            }
            responder($data);
            break;

        default:
            http_response_code(404);
            responder([
                'disponivel' => [
                    '/api/sensores',
                    '/api/sensores/{id}',
                    '/api/leituras',
                    '/api/leituras/{id}',
                    '/api/estatisticas?dias=7'
                ]
            ]);
    }
} catch (\Throwable $e) {
    http_response_code(500);
    responder(['erro' => 'Erro interno do servidor']);
}

function responder($data) {
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit();
}
