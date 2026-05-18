<?php
require_once __DIR__ . '/../config.php';
include BASE_PATH . 'db.php';

if (!isset($_SESSION['user_id']) || !$_SESSION['user_admin']) {
    header('Location: ' . BASE_URL . 'index.php?erro=admin');
    exit;
}

if (isset($_GET['cod_leituras'])) {
    if (!isset($_GET['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_GET['csrf_token'])) {
        header("Location: " . BASE_URL . "LT/Leituras.php?erro=csrf");
        exit();
    }
    $cod_leituras = (int)$_GET['cod_leituras'];
    $stmt = $conn->prepare("DELETE FROM leituras WHERE cod_leituras = ?");
    $stmt->bind_param("i", $cod_leituras);
    if ($stmt->execute()) {
        header("Location: " . BASE_URL . "LT/Leituras.php?msg=excluido");
        exit();
    } else {
        header("Location: " . BASE_URL . "LT/Leituras.php?erro=bd");
        exit();
    }
    $stmt->close();
} else {
    header("Location: " . BASE_URL . "LT/Leituras.php");
    exit();
}
?>