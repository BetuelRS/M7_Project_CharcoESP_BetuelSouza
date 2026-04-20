<?php
require_once __DIR__ . '/../config.php';
include BASE_PATH . 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'LT/Leituras.php');
    exit();
}

// Recebe e valida dados
$cod_sensor = isset($_POST['cod_sensor']) ? (int)$_POST['cod_sensor'] : 0;
$valor = isset($_POST['valor']) ? floatval($_POST['valor']) : 0;
$unidade = trim($_POST['unidade'] ?? '');
$data_hora = $_POST['data_hora'] ?? '';
$observacoes = trim($_POST['observacoes'] ?? '');

if ($cod_sensor <= 0 || empty($unidade) || empty($data_hora)) {
    // Dados inválidos, redireciona para o formulário com erro
    header('Location: ' . BASE_URL . 'LT/leituras_add.php?erro=1');
    exit();
}

// Prepared statement
$stmt = $conn->prepare("INSERT INTO leituras (cod_sensor, valor, unidade, data_hora, observacoes) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("idsss", $cod_sensor, $valor, $unidade, $data_hora, $observacoes);

if ($stmt->execute()) {
    // Sucesso
    header('Location: ' . BASE_URL . 'LT/Leituras.php?msg=adicionado');
} else {
    // Erro
    header('Location: ' . BASE_URL . 'LT/leituras_add.php?erro=bd');
}
$stmt->close();
$conn->close();