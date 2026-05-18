<!-- SN_atualizar.php realiza a alteração -->
<?php
require_once __DIR__ . '/../config.php';
include BASE_PATH . 'db.php';

if (!isset($_SESSION['user_id']) || !$_SESSION['user_admin']) {
    header('Location: ' . BASE_URL . 'index.php?erro=admin');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        header("Location: " . BASE_URL . "SN/Sensores.php?erro=csrf");
        exit();
    }
    $cod_sensor = (int)$_POST['cod_sensor'];
    $nome = $_POST['nome'];
    $tipo = $_POST['tipo'];
    $descricao = $_POST['descricao'] ?? '';
    $modelo = $_POST['modelo'] ?? '';
    $fabricante = $_POST['fabricante'] ?? '';
    $localizacao = $_POST['localizacao'] ?? '';
    $ativo = isset($_POST['ativo']) ? 1 : 0;

    $stmt = $conn->prepare("UPDATE sensores SET nome = ?, tipo = ?, descricao = ?, modelo = ?, fabricante = ?, localizacao = ?, ativo = ? WHERE cod_sensor = ?");
    $stmt->bind_param("ssssssii", $nome, $tipo, $descricao, $modelo, $fabricante, $localizacao, $ativo, $cod_sensor);

    if ($stmt->execute()) {
        header("Location: " . BASE_URL . "SN/Sensores.php?msg=atualizado");
        exit();
    } else {
        header("Location: " . BASE_URL . "SN/Sensores.php?erro=bd");
        exit();
    }
    $stmt->close();
} else {
    echo "Método de requisição inválido";
}
?>
