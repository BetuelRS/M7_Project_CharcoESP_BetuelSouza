<?php
require_once __DIR__ . '/../config.php';
include BASE_PATH . 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'LT/Leituras.php');
    exit();
}

// Validação básica
$cod_leituras = isset($_POST['cod_leituras']) ? (int)$_POST['cod_leituras'] : 0;
$cod_sensor = isset($_POST['cod_sensor']) ? (int)$_POST['cod_sensor'] : 0;
$valor = isset($_POST['valor']) ? floatval($_POST['valor']) : 0;
$unidade = trim($_POST['unidade'] ?? '');
$data_hora = $_POST['data_hora'] ?? '';
$observacoes = trim($_POST['observacoes'] ?? '');

if ($cod_leituras <= 0 || $cod_sensor <= 0 || empty($unidade) || empty($data_hora)) {
    // Dados inválidos, redireciona com erro
    header('Location: ' . BASE_URL . 'LT/leituras_editar.php?cod_leituras=' . $cod_leituras . '&erro=1');
    exit();
}

// Prepared statement para atualizar
$stmt = $conn->prepare("UPDATE leituras SET cod_sensor = ?, valor = ?, unidade = ?, data_hora = ?, observacoes = ? WHERE cod_leituras = ?");
$stmt->bind_param("idsssi", $cod_sensor, $valor, $unidade, $data_hora, $observacoes, $cod_leituras);

if ($stmt->execute()) {
    // Sucesso
    header('Location: ' . BASE_URL . 'LT/Leituras.php?msg=atualizado');
} else {
    // Erro
    header('Location: ' . BASE_URL . 'LT/leituras_editar.php?cod_leituras=' . $cod_leituras . '&erro=bd');
}
$stmt->close();
$conn->close();