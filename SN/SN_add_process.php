<!-- processamento de adição de sensores -->

<?php
require_once __DIR__ . '/../config.php';
include BASE_PATH . 'db.php';
?>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST["nome"];
    $tipo = $_POST["tipo"];
    $descricao = $_POST["descricao"];
    $modelo = $_POST["modelo"];
    $fabricante = $_POST["fabricante"];
    $localizacao = $_POST["localizacao"];
    $data_instalacao = $_POST["data_instalacao"];

    $sql = "INSERT INTO sensores (nome, tipo, descricao, localizacao, modelo, fabricante,  data_instalacao) VALUES ('$nome', '$tipo', '$descricao', '$localizacao', '$modelo', '$fabricante', '$data_instalacao')";

    if ($conn->query($sql) === TRUE) {
        echo "Novo sensor adicionado com sucesso!";
        header("Location: " . BASE_URL . "SN/Sensores.php");
exit();
    } else {
        echo "Erro ao adicionar sensor: " . $conn->error;
    }

}
$conn->close();
?>