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
    header('Location: ' . BASE_URL . 'LT/leituras_editar.php?cod_leituras=' . ($_POST['cod_leituras'] ?? 0) . '&erro=validacao');
    exit();
}

// Validação básica
$cod_leituras = isset($_POST['cod_leituras']) ? (int)$_POST['cod_leituras'] : 0;
$cod_sensor = isset($_POST['cod_sensor']) ? (int)$_POST['cod_sensor'] : 0;
$valor_str = $_POST['valor'] ?? '';
$valor = floatval($valor_str);
$unidade = trim($_POST['unidade'] ?? '');
$data_hora = $_POST['data_hora'] ?? '';
$observacoes = trim($_POST['observacoes'] ?? '');

// Validações server-side
$erros = [];

if ($cod_leituras <= 0) {
    $erros[] = "Leitura inválida";
}
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
    header('Location: ' . BASE_URL . 'LT/leituras_editar.php?cod_leituras=' . $cod_leituras . '&erro=validacao');
    exit();
}

// Prepared statement para atualizar
$stmt = $conn->prepare("UPDATE leituras SET cod_sensor = ?, valor = ?, unidade = ?, data_hora = ?, observacoes = ? WHERE cod_leituras = ?");
$stmt->bind_param("idsssi", $cod_sensor, $valor, $unidade, $data_hora, $observacoes, $cod_leituras);

if ($stmt->execute()) {
    registrar_auditoria($conn, $_SESSION['user_id'], 'editar', 'leitura', $cod_leituras, "Leitura ID: $cod_leituras");
    header('Location: ' . BASE_URL . 'LT/Leituras.php?msg=atualizado');
} else {
    // Erro
    header('Location: ' . BASE_URL . 'LT/leituras_editar.php?cod_leituras=' . $cod_leituras . '&erro=bd');
}
$stmt->close();
$conn->close();