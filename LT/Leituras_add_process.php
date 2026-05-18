<?php
require_once __DIR__ . '/../config.php';
include BASE_PATH . 'db.php';
require_once BASE_PATH . 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'LT/Leituras.php');
    exit();
}

if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    $_SESSION['erros_validacao'] = ['Erro de validação do formulário. Tente novamente.'];
    header('Location: ' . BASE_URL . 'LT/Leituras_add.php?erro=validacao');
    exit();
}

// Recebe e valida dados
$cod_sensor = isset($_POST['cod_sensor']) ? (int)$_POST['cod_sensor'] : 0;
$valor_str = $_POST['valor'] ?? '';
$valor = floatval($valor_str);
$unidade = trim($_POST['unidade'] ?? '');
$data_hora = $_POST['data_hora'] ?? '';
$observacoes = trim($_POST['observacoes'] ?? '');

// Validações server-side
$erros = [];

if ($cod_sensor <= 0) {
    $erros[] = "Sensor inválido";
}
if ($valor_str === '' || $valor_str === null) {
    $erros[] = "Valor é obrigatório";
} elseif (!is_numeric($valor_str)) {
    $erros[] = "Valor deve ser um número";
} else {
    $erros = array_merge($erros, validar_valor_unidade($valor, $unidade));
}
if (empty($unidade)) {
    $erros[] = "Unidade é obrigatória";
}
if (empty($data_hora)) {
    $erros[] = "Data/Hora é obrigatória";
} else {
    $date_check = DateTime::createFromFormat('Y-m-d\TH:i', $data_hora);
    if (!$date_check) {
        $erros[] = "Data/Hora inválida";
    }
}

if (!empty($erros)) {
    $_SESSION['erros_validacao'] = $erros;
    header('Location: ' . BASE_URL . 'LT/Leituras_add.php?erro=validacao');
    exit();
}

// Prepared statement
$stmt = $conn->prepare("INSERT INTO leituras (cod_sensor, valor, unidade, data_hora, observacoes) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("idsss", $cod_sensor, $valor, $unidade, $data_hora, $observacoes);

if ($stmt->execute()) {
    $new_id = $stmt->insert_id;
    registrar_auditoria($conn, $_SESSION['user_id'], 'criar', 'leitura', $new_id, "Leitura: $valor $unidade (sensor $cod_sensor)");
    header('Location: ' . BASE_URL . 'LT/Leituras.php?msg=adicionado');
} else {
    // Erro
    header('Location: ' . BASE_URL . 'LT/leituras_add.php?erro=bd');
}
$stmt->close();
$conn->close();