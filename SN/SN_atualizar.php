<!-- SN_atualizar.php realiza a alteração -->
<?php
require_once __DIR__ . '/../config.php';
include BASE_PATH . 'db.php';
?>
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cod_sensor = $_POST['cod_sensor'];
    $nome = $_POST['nome'];
    $tipo = $_POST['tipo'];
    $descricao = $_POST['descricao'];
    $modelo = $_POST['modelo'];
    $fabricante = $_POST['fabricante'];
    $localizacao = $_POST['localizacao'];
    $ativo = $_POST['ativo'];

    $sql = "UPDATE sensores SET nome='$nome', tipo='$tipo', descricao='$descricao', modelo='$modelo', fabricante='$fabricante', localizacao='$localizacao', ativo=$ativo WHERE cod_sensor=$cod_sensor";

    if ($conn->query($sql) === TRUE) {
        echo "Sensor atualizado com sucesso";
        header("Location: Sensores.php");
        header("Location: " . BASE_URL . "SN/Sensores.php");
exit();
        exit();
    } else {
        echo "Erro ao atualizar sensor: " . $conn->error;
    }
} else {
    echo "Método de requisição inválido";
}
?>
