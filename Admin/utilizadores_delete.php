<!-- excluir utilizadores -->
<?php
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id']) || !$_SESSION['user_admin']) {
    header('Location: ' . BASE_URL . 'index.php?erro=admin');
    exit();
}

include BASE_PATH . 'db.php';
if (!isset($_GET['id'])) {
    header('Location: ' . BASE_URL . 'Admin/admin.php');
    exit();
}
if (!isset($_GET['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_GET['csrf_token'])) {
    header('Location: ' . BASE_URL . 'Admin/admin.php?erro=csrf');
    exit();
}
$id = (int)$_GET['id'];
$stmt = $conn->prepare("DELETE FROM utilizadores WHERE cod_utilizador = ?");
$stmt->bind_param("i", $id);
if ($stmt->execute()) {
    header('Location: ' . BASE_URL . 'Admin/admin.php?msg=excluido');
} else {
    header('Location: ' . BASE_URL . 'Admin/admin.php?erro=bd');
}   
$stmt->close();
$conn->close();
?>
