<!-- SN_excluir exclui um sensor -->
<?php
require_once __DIR__ . '/../config.php';
include BASE_PATH . 'db.php';

if (!isset($_SESSION['user_id']) || !$_SESSION['user_admin']) {
    header('Location: ' . BASE_URL . 'index.php?erro=admin');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['cod_sensor'])) {
    if (!isset($_GET['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_GET['csrf_token'])) {
        header("Location: " . BASE_URL . "SN/Sensores.php?erro=csrf");
        exit();
    }
    $cod_sensor = intval($_GET['cod_sensor']);

    $stmt = $conn->prepare("DELETE FROM sensores WHERE cod_sensor = ?");
    $stmt->bind_param("i", $cod_sensor);
    
    if ($stmt->execute()) {
        header("Location: " . BASE_URL . "SN/Sensores.php?msg=excluido");
        exit();
    } else {
        header("Location: " . BASE_URL . "SN/Sensores.php?erro=bd");
        exit();
    }
    $stmt->close();
} else {
    header("Location: " . BASE_URL . "SN/Sensores.php");
    exit();
}
?>