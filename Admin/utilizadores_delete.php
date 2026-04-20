<!-- excluir utilizadores -->
<?php
require_once __DIR__ . '/../config.php';
include BASE_PATH . 'db.php';
if (!isset($_GET['id'])) {
    header('Location: ' . BASE_URL . 'Admin/admin.php');
    exit();
}
$id = (int)$_GET['id'];
$stmt = $conn->prepare("DELETE FROM utilizadores WHERE cod_utilizador = ?");
$stmt->bind_param("i", $id);
if ($stmt->execute()) {
    header('Location: ' . BASE_URL . 'Admin/admin.php?msg=excluido');
} else {
    header('Location: ' . BASE_URL . 'Admin/admin.php?msg=erro');
}   
$stmt->close();
$conn->close();
?>
