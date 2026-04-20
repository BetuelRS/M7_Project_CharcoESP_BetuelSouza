<!-- Pagina de administração Gerir usuarios -->
<?php
require_once __DIR__ . '/../config.php';
include BASE_PATH . 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administração - Gerir Utilizadores</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600&display=swap" rel="stylesheet">
</head>
<body>
    <header class="sidebar">
        <?php include '../struct/header.php'; ?>
    </header>
    <main class="conteudo">
        <h1 class="admin-title">Gerir Utilizadores</h1>
        <a href="<?= BASE_URL ?>Admin/utilizadores_add.php" class="admin-action-btn">Adicionar Novo Utilizador</a>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Codigo</th>
                    <th>Username</th>
                    <th>Password</th>
                    <th>Email</th>
                    <th>Nome Completo</th>
                    <th>Admin</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php
                include BASE_PATH . 'db.php';
                $result = $conn->query("SELECT cod_utilizador, username, password, email, nome_completo, ADMIN FROM utilizadores");
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>{$row['cod_utilizador']}</td>";
                    echo "<td>{$row['username']}</td>";
                    echo "<td>{$row['password']}</td>";
                    echo "<td>{$row['email']}</td>";
                    echo "<td>{$row['nome_completo']}</td>";
                    echo "<td>{$row['ADMIN']}</td>";
                    echo "<td><a href='" . BASE_URL . "Admin/utilizadores_edit.php?id={$row['cod_utilizador']}' class='admin-action-btn'>Editar</a> ";
                    echo "<a href='" . BASE_URL . "Admin/utilizadores_delete.php?id={$row['cod_utilizador']}' class='admin-action-btn btn-danger' onclick='return confirm(\"Tem certeza que deseja excluir este utilizador?\")'>Excluir</a></td>";
                    echo "</tr>";
                }
                $conn->close();
                ?>
            </tbody>
        </table>

    </main>
    <footer>
        <?php include '../struct/footer.php'; ?>
    </footer>
</body>
</html>