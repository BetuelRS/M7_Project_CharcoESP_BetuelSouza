<!-- SN_excluir exclui um sensor -->
<?php
require_once __DIR__ . '/../config.php';
include BASE_PATH . 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['cod_sensor'])) {
    $cod_sensor = intval($_GET['cod_sensor']);

    $sql = "DELETE FROM sensores WHERE cod_sensor = $cod_sensor";
    
    if ($conn->query($sql) === TRUE) {
        // Redireciona de volta para a lista
        header("Location: " . BASE_URL . "SN/Sensores.php");
        exit();
    } else {
        echo "Erro ao excluir sensor: " . $conn->error;
    }
} else {
    echo "Método de requisição inválido ou código não fornecido.";
}
?>