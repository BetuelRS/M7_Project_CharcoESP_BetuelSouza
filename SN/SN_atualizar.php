<!-- SN_atualizar.php realiza a alteração -->
<?php
require_once __DIR__ . '/../config.php';
include BASE_PATH . 'db.php';
?>
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
        echo "Erro ao atualizar sensor: " . $conn->error;
    }
    $stmt->close();
} else {
    echo "Método de requisição inválido";
}
?>
