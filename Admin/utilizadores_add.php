<!-- Add Utilizador, formulário e processamento -->
<?php
require_once __DIR__ . '/../config.php';
include BASE_PATH . 'db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $email = trim($_POST['email']);
    $nome_completo = trim($_POST['nome_completo']);
    $admin = isset($_POST['admin']) ? 1 : 0;

    // Validação básica
    if (empty($username) || empty($password) || empty($email) || empty($nome_completo)) {
        header('Location: ' . BASE_URL . 'Admin/utilizadores_add.php?erro=1');
        exit();
    }

    // Prepared statement 
    $stmt = $conn->prepare("INSERT INTO utilizadores (username, password, email, nome_completo, ADMIN) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $username, $password, $email, $nome_completo, $admin);

    if ($stmt->execute()) {
        header('Location: ' . BASE_URL . 'Admin/admin.php?msg=adicionado');
    } else {
        header('Location: ' . BASE_URL . 'Admin/utilizadores_add.php?erro=bd');
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Utilizador</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600&display=swap" rel="stylesheet">
</head>
<body>
    <header class="sidebar">
        <?php include '../struct/header.php'; ?>
    </header>
    <main class="conteudo">
        <h1 class="admin-title">Adicionar Novo Utilizador</h1>
        <form action="<?= BASE_URL ?>Admin/utilizadores_add.php" method="POST" class="admin-form">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="nome_completo">Nome Completo:</label>
                <input type="text" id="nome_completo" name="nome_completo" required>
            </div>

            <div class="form-group">
                <label for="admin">
                    
                    Administrador
                    <input type="checkbox" id="admin" name="admin">
                </label>
            </div>

            <button type="submit" class="btn-submit">Adicionar Utilizador</button>

        </form>
    </main>
    <footer>
        <?php include '../struct/footer.php'; ?>
    </footer>
</body>
</html> 
