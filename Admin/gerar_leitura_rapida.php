<?php
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id']) || !$_SESSION['user_admin']) {
    header('Location: ' . BASE_URL . 'index.php?erro=admin');
    exit();
}

if (!isset($_GET['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_GET['csrf_token'])) {
    $_SESSION['admin_msg'] = 'Erro de validação. Tente novamente.';
    header('Location: ' . BASE_URL . 'Admin/admin.php');
    exit();
}

include BASE_PATH . 'db.php';

$result = $conn->query("SELECT cod_sensor, nome, tipo FROM sensores ORDER BY cod_sensor");

if (!$result || $result->num_rows === 0) {
    $_SESSION['admin_msg'] = 'Nenhum sensor encontrado.';
    header('Location: ' . BASE_URL . 'Admin/admin.php');
    exit();
}

$stmt = $conn->prepare("INSERT INTO leituras (cod_sensor, valor, unidade, data_hora, observacoes) VALUES (?, ?, ?, ?, ?)");
$now = date('Y-m-d H:i:s');
$count = 0;

while ($sensor = $result->fetch_assoc()) {
    $cod_sensor = (int)$sensor['cod_sensor'];
    $tipo = $sensor['tipo'];

    switch ($tipo) {
        case 'Temperatura':
            $valor = round(15 + rand(-120, 150) / 10, 1);
            $unidade = '°C';
            $obs = $valor < 10 ? 'Água muito fria' : ($valor > 30 ? 'Água muito quente' : null);
            break;
        case 'Humidade':
            $valor = round(65 + rand(-400, 300) / 10, 1);
            $unidade = '%';
            $obs = $valor > 90 ? 'Humidade muito alta' : ($valor < 30 ? 'Ar muito seco' : null);
            break;
        case 'Luminosidade':
            $valor = round(rand(-100, 1500), 0);
            $unidade = 'lux';
            $obs = $valor > 1000 ? 'Pico solar intenso' : ($valor < 5 ? 'Noite' : null);
            break;
        case 'Qualidade do Ar':
            $valor = round(rand(-50, 600) / 10, 1);
            $unidade = 'µg/m3';
            $obs = $valor > 40 ? 'Ar de má qualidade' : ($valor < 10 ? 'Ar muito limpo' : null);
            break;
        case 'Nível da Água':
            $valor = round(35 + rand(-200, 150) / 10, 1);
            $unidade = 'cm';
            $obs = $valor > 48 ? 'Nível muito alto' : ($valor < 25 ? 'Nível muito baixo' : null);
            break;
        default:
            $valor = round(rand(-500, 5000) / 10, 1);
            $unidade = '';
            $obs = null;
    }

    $stmt->bind_param("idsss", $cod_sensor, $valor, $unidade, $now, $obs);
    if ($stmt->execute()) {
        $count++;
    }
}

$stmt->close();
$conn->close();

$_SESSION['admin_msg'] = "Geradas $count leituras com sucesso.";
header('Location: ' . BASE_URL . 'Admin/admin.php');
exit();
