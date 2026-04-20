<?php
require_once __DIR__ . '/../config.php';
include BASE_PATH . 'db.php';

if (isset($_GET['cod_leituras'])) {
    $cod_leituras = $_GET['cod_leituras'];
    $sql = "DELETE FROM leituras WHERE cod_leituras = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cod_leituras);
    if ($stmt->execute()) {
        header("Location: " . BASE_URL . "LT/Leituras.php");
        exit();
    } else {
        echo "Erro ao excluir leitura: " . $conn->error;
    }
    $stmt->close();
} else {
    echo "Código de leitura não fornecido";
}
?>