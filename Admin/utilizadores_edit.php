<!-- editar e atualizar utilizador -->
<?php
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id']) || !$_SESSION['user_admin']) {
    header('Location: ' . BASE_URL . 'index.php?erro=admin');
    exit();
}

include BASE_PATH . 'db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        header('Location: ' . BASE_URL . 'Admin/admin.php?erro=csrf');
        exit();
    }
    $id = (int)$_POST['id'];
    $username = trim($_POST['username']);
    $password_input = $_POST['password'];
    $email = trim($_POST['email']);
    $nome_completo = trim($_POST['nome_completo']);
    $admin = isset($_POST['admin']) ? 1 : 0;

    // Validação básica (password não é obrigatória no edit)
    if (empty($username) || empty($email) || empty($nome_completo)) {
        header('Location: ' . BASE_URL . 'Admin/utilizadores_edit.php?id=' . $id . '&erro=1');
        exit();
    }

    // Se password foi alterada, fazer hash; se vazia, manter a atual
    if (!empty($password_input)) {
        $password = password_hash($password_input, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE utilizadores SET username = ?, password = ?, email = ?, nome_completo = ?, ADMIN = ? WHERE cod_utilizador = ?");
        $stmt->bind_param("ssssii", $username, $password, $email, $nome_completo, $admin, $id);
    } else {
        $stmt = $conn->prepare("UPDATE utilizadores SET username = ?, email = ?, nome_completo = ?, ADMIN = ? WHERE cod_utilizador = ?");
        $stmt->bind_param("sssii", $username, $email, $nome_completo, $admin, $id);
    }

    if ($stmt->execute()) {
        header('Location: ' . BASE_URL . 'Admin/admin.php?msg=atualizado');
    } else {
        header('Location: ' . BASE_URL . 'Admin/utilizadores_edit.php?id=' . $id . '&erro=bd');
    }
    $stmt->close();
}
if (!isset($_GET['id'])) {
    header('Location: ' . BASE_URL . 'Admin/admin.php');
    exit();
}
$id = (int)$_GET['id'];
$stmt = $conn->prepare("SELECT cod_utilizador, username, password, email, nome_completo, ADMIN FROM utilizadores WHERE cod_utilizador = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    header('Location: ' . BASE_URL . 'Admin/admin.php');
    exit();
}
$user = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Utilizador</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600&display=swap" rel="stylesheet">
</head>
<body>
    <header class="sidebar">
        <?php include '../struct/header.php'; ?>
    </header>
    <main class="conteudo">
        <h1 class="admin-title">Editar Utilizador</h1>
        <form action="<?= BASE_URL ?>Admin/utilizadores_edit.php" method="POST" class="admin-form">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="id" value="<?= $user['cod_utilizador'] ?>">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" placeholder="Deixe em branco para manter a atual">
                <small style="color: #666;">Deixe em branco para manter a password atual</small>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>

            <div class="form-group">
                <label for="nome_completo">Nome Completo:</label>
                <input type="text" id="nome_completo" name="nome_completo" value="<?= htmlspecialchars($user['nome_completo']) ?>" required>
            </div>

            <div class="form-group">
                <label for="admin">
                    Administrador
                    <input type="checkbox" id="admin" name="admin" <?= $user['ADMIN'] ? 'checked' : '' ?>>
                </label>
            </div>

            <button type="submit" class="btn-submit">Salvar Alterações</button>
        </form>
    </main>
    <footer>
        <?php include '../struct/footer.php'; ?>
    </footer>
</body>
</html>