<!-- processamento de adição de sensores -->

<?php
require_once __DIR__ . '/../config.php';
include BASE_PATH . 'db.php';

if (!isset($_SESSION['user_id']) || !$_SESSION['user_admin']) {
    header('Location: ' . BASE_URL . 'index.php?erro=admin');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        header("Location: " . BASE_URL . "SN/SN_add.php?erro=csrf");
        exit();
    }
    $nome = $_POST["nome"];
    $tipo = $_POST["tipo"];
    $descricao = $_POST["descricao"] ?? '';
    $modelo = $_POST["modelo"] ?? '';
    $fabricante = $_POST["fabricante"] ?? '';
    $localizacao = $_POST["localizacao"] ?? '';
    $data_instalacao = $_POST["data_instalacao"] ?? null;

    $stmt = $conn->prepare("INSERT INTO sensores (nome, tipo, descricao, localizacao, modelo, fabricante, data_instalacao) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $nome, $tipo, $descricao, $localizacao, $modelo, $fabricante, $data_instalacao);

if ($stmt->execute()) {
        header("Location: " . BASE_URL . "SN/Sensores.php?msg=adicionado");
        exit();
    } else {
        header("Location: " . BASE_URL . "SN/SN_add.php?erro=bd");
        exit();
    }
    $stmt->close();
}
$conn->close();
?>